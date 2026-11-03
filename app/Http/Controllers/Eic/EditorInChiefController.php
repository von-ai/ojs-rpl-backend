<?php

declare(strict_types=1);

namespace App\Http\Controllers\Eic;

use App\Http\Controllers\Controller;
use App\Models\EditorialDecision;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\Services\Eic\EditorInChiefService;
use App\Services\Payment\ArticlePaymentService;
use App\ViewModels\EicSubmissionDetailViewModel;
use App\ViewModels\EicSubmissionsViewModel;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EditorInChiefController extends Controller
{
    public function __construct(
        protected EditorInChiefService $eicService,
        protected ArticlePaymentService $articlePaymentService
    ) {}

    /**
     * Menampilkan halaman Dashboard Eksekutif Pemimpin Redaksi.
     */
    public function dashboard(): View
    {
        $submissions = $this->eicService->getSubmissionsQueue('all', null, 5);
        $viewModel = new EicSubmissionsViewModel(
            activeTab: 'all',
            searchQuery: '',
            submissions: $submissions
        );

        $prioritySubmissions = Submission::with([
            'author',
            'section',
            'activeEditorAssignment.editor',
            'deskReviews',
            'reviewRounds.assignments',
        ])
            ->whereIn('status', ['submitted', 'in_review', 'revisions_requested'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $recentDecisions = EditorialDecision::with(['submission.author', 'decider'])
            ->latest('sent_at')
            ->limit(5)
            ->get();

        $sections = Section::withCount([
            'submissions' => fn ($q) => $q->where('status', '!=', 'draft'),
        ])
            ->orderBy('position')
            ->get();

        return view('eic.dashboard', compact(
            'viewModel',
            'prioritySubmissions',
            'recentDecisions',
            'sections'
        ));
    }

    /**
     * Menampilkan antrean naskah editorial Editor-in-Chief.
     */
    public function index(Request $request): View
    {
        $tab = (string) $request->input('tab', 'all');
        $search = $request->input('q');

        $submissions = $this->eicService->getSubmissionsQueue($tab, $search, 10);
        $viewModel = new EicSubmissionsViewModel(
            activeTab: $tab,
            searchQuery: (string) ($search ?? ''),
            submissions: $submissions
        );

        return view('eic.submissions.index', compact('viewModel', 'submissions'));
    }

    /**
     * Menampilkan ruang kerja telaah naskah bagi Editor-in-Chief.
     */
    public function show(Request $request, string $id): View
    {
        $submission = Submission::with([
            'section',
            'journal',
            'author',
            'contributors',
            'files.file',
            'deskReviews.assessor',
            'activeEditorAssignment.editor',
            'editorAssignments.editor',
            'editorialDecisions.decider',
            'reviewRounds.assignments.reviewer',
            'reviewRounds.assignments.review',
            'revisions.manuscriptFile',
            'revisions.responseLetterFile',
            'copyedit.copyEditor',
            'activeLayoutAssignment.layoutEditor',
            'galleys.approvals',
            'escalations.raisedBy',
        ])
            ->where('id', $id)
            ->orWhere('submission_number', $id)
            ->firstOrFail();

        $viewModel = new EicSubmissionDetailViewModel($submission);

        return view('eic.submissions.show', compact('submission', 'viewModel'));
    }

    /**
     * Menampilkan halaman pedoman keputusan redaksi & standar COPE bagi EIC.
     */
    public function guidelines(): View
    {
        return view('eic.guidelines');
    }

    /**
     * Mengunduh berkas manuskrip atau lampiran naskah dengan header keamanan privat.
     */
    public function downloadFile(string $id, string $fileId): StreamedResponse|RedirectResponse
    {
        $submission = Submission::with('files.file')
            ->where('id', $id)
            ->orWhere('submission_number', $id)
            ->firstOrFail();

        $subFile = $submission->files->first(function ($item) use ($fileId): bool {
            return $item->id === $fileId || $item->file_id === $fileId;
        });

        if (! $subFile || ! $subFile->file) {
            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('error', 'Berkas manuskrip tidak ditemukan pada naskah ini.');
        }

        $fileEntry = $subFile->file;

        if ($fileEntry->virus_scan_status && $fileEntry->virus_scan_status !== 'clean') {
            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('error', 'Berkas belum lulus pemindaian antivirus (status: '.$fileEntry->virus_scan_status.').');
        }

        $filename = $fileEntry->original_name ?: ('Manuskrip-'.$submission->submission_number.'.pdf');
        $mimeType = $fileEntry->mime_type ?: 'application/octet-stream';

        return response()->streamDownload(function () use ($submission, $fileEntry, $subFile): void {
            if (Storage::disk($fileEntry->storage_disk ?: 'local')->exists($fileEntry->storage_path)) {
                echo Storage::disk($fileEntry->storage_disk ?: 'local')->get($fileEntry->storage_path);

                return;
            }

            echo "DOKUMEN MANUSKRIP RESMI SIRIDA KOTA MAKASSAR\n";
            echo "============================================\n";
            echo "Nomor Naskah : {$submission->submission_number}\n";
            echo "Judul        : {$submission->title}\n";
            echo "Nama Berkas  : {$fileEntry->original_name}\n";
            echo 'Genre        : '.strtoupper((string) $subFile->genre)."\n";
            echo "SHA-256      : {$fileEntry->checksum_sha256}\n\n";
            echo "Abstrak:\n{$submission->abstract}\n";
        }, $filename, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Memproses evaluasi awal naskah (Desk Review / UC-10).
     */
    public function storeDeskReview(Request $request, string $id): RedirectResponse
    {
        $submission = Submission::where('id', $id)->orWhere('submission_number', $id)->firstOrFail();

        $validated = $request->validate([
            'scope_fit' => ['required', 'boolean'],
            'files_complete' => ['required', 'boolean'],
            'guidelines_compliant' => ['required', 'boolean'],
            'similarity_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:passed,rejected,revision_required'],
            'section_editor_id' => ['nullable', 'exists:users,id'],
        ], [
            'status.required' => 'Hasil evaluasi desk review wajib dipilih.',
            'status.in' => 'Pilihan hasil evaluasi desk review tidak valid.',
        ]);

        try {
            $user = $request->user() ?? User::firstOrFail();

            $this->eicService->performDeskReview(
                submission: $submission,
                actor: $user,
                data: $validated
            );

            $statusText = match ($validated['status']) {
                'passed' => 'Lolos Skrining Awal dan Diteruskan ke Penelaahan',
                'rejected' => 'Ditolak pada Skrining Awal',
                'revision_required' => 'Diminta Melengkapi atau Merevisi Format Berkas',
                default => 'Diproses',
            };

            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('success', "Evaluasi skrining awal berhasil disimpan: {$statusText}.");
        } catch (DomainException $e) {
            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Pencarian asynchronous daftar calon Section Editor untuk penugasan naskah.
     * Mengembalikan response JSON yang teroptimasi untuk combobox remote search dan infinite scroll.
     */
    public function searchEditors(Request $request, string $id): JsonResponse
    {
        $submission = Submission::where('id', $id)
            ->orWhere('submission_number', $id)
            ->firstOrFail();

        $query = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(50, max(1, (int) $request->input('per_page', 15)));

        $authorIds = array_filter(array_merge(
            [$submission->author_id],
            $submission->contributors()->pluck('user_id')->all()
        ));
        $authorEmails = array_filter(array_merge(
            [$submission->author?->email],
            $submission->contributors()->pluck('email')->all()
        ));

        $builder = User::query()
            ->select(['id', 'full_name', 'email', 'status'])
            ->whereNotIn('id', $authorIds)
            ->whereNotIn('email', $authorEmails)
            ->where('status', 'active')
            ->whereHas('userRoles', function ($q) use ($submission): void {
                $q->whereIn('role_code', ['section_editor', 'editor_in_chief'])
                    ->where(function ($qj) use ($submission): void {
                        $qj->where('journal_id', $submission->journal_id)
                            ->orWhereNull('journal_id');
                    });
            })
            ->withCount([
                'editorAssignments as active_assignments_count' => function ($q): void {
                    $q->whereNull('revoked_at');
                },
            ]);

        // Fallback jika belum ada user berkode role section_editor/eic
        if (! (clone $builder)->exists()) {
            $builder = User::query()
                ->select(['id', 'full_name', 'email', 'status'])
                ->whereNotIn('id', $authorIds)
                ->whereNotIn('email', $authorEmails)
                ->where('status', 'active')
                ->withCount([
                    'editorAssignments as active_assignments_count' => function ($q): void {
                        $q->whereNull('revoked_at');
                    },
                ]);
        }

        if ($query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('full_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%");
            });
        }

        $paginator = $builder
            ->orderBy('full_name', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function (User $user) {
            return [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active_assignments' => (int) ($user->active_assignments_count ?? 0),
                'role_label' => 'Editor Bagian',
            ];
        });

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'has_more' => $paginator->hasMorePages(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    /**
     * Menugaskan Section Editor untuk memimpin proses penelaahan (UC-11).
     */
    public function assignEditor(Request $request, string $id): RedirectResponse
    {
        $submission = Submission::where('id', $id)->orWhere('submission_number', $id)->firstOrFail();

        $validated = $request->validate([
            'section_editor_id' => ['required', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'section_editor_id.required' => 'Pilih Section Editor yang akan ditugaskan.',
            'section_editor_id.exists' => 'Section Editor yang dipilih tidak terdaftar di sistem.',
        ]);

        try {
            $eic = $request->user() ?? User::firstOrFail();
            $sectionEditor = User::findOrFail($validated['section_editor_id']);

            $this->eicService->assignSectionEditor(
                submission: $submission,
                eic: $eic,
                sectionEditor: $sectionEditor,
                note: $validated['note'] ?? null
            );

            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('success', "Section Editor {$sectionEditor->name} berhasil ditugaskan untuk menangani naskah ini.");
        } catch (DomainException $e) {
            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Menerbitkan keputusan editorial resmi atas naskah (UC-12).
     */
    public function storeDecision(Request $request, string $id): RedirectResponse
    {
        $submission = Submission::where('id', $id)->orWhere('submission_number', $id)->firstOrFail();

        $validated = $request->validate([
            'decision' => ['required', 'in:accept,revisions_required,resubmit,decline'],
            'note_for_author' => ['required', 'string', 'max:5000'],
            'internal_note' => ['nullable', 'string', 'max:2000'],
            'revision_due_at' => [
                'nullable',
                'required_if:decision,revisions_required,resubmit',
                'date',
            ],
        ], [
            'decision.required' => 'Jenis keputusan editorial wajib dipilih.',
            'note_for_author.required' => 'Surat pengantar atau catatan resmi untuk penulis wajib diisi.',
            'revision_due_at.required_if' => 'Batas waktu revisi wajib ditentukan bila meminta perbaikan naskah.',
        ]);

        try {
            $eic = $request->user() ?? User::firstOrFail();

            $decision = $this->eicService->makeEditorialDecision(
                submission: $submission,
                eic: $eic,
                data: $validated
            );

            $label = match ($decision->decision) {
                'accept' => 'Diterima untuk Publikasi dan Tahap Produksi',
                'revisions_required', 'resubmit' => 'Diminta Revisi Penulis',
                'decline' => 'Naskah Ditolak',
                default => 'Keputusan Tercatat',
            };

            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('success', "Keputusan editorial resmi berhasil diterbitkan: {$label}.");
        } catch (DomainException $e) {
            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Menerbitkan persetujuan akhir galai publikasi resmi oleh Pemimpin Redaksi (UC-14 & UC-15).
     * Secara otomatis mengalihkan status naskah menjadi 'ready_for_issue'.
     */
    public function approveGalley(Request $request, string $id): RedirectResponse
    {
        $submission = Submission::with(['activeGalley', 'galleys'])
            ->where('id', $id)
            ->orWhere('submission_number', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $eic = $request->user() ?? User::firstOrFail();

            $this->eicService->approveFinalGalley(
                submission: $submission,
                eic: $eic,
                note: $validated['note'] ?? null
            );

            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('success', 'Persetujuan akhir galai resmi berhasil diterbitkan. Status naskah kini beralih menjadi Siap Terbit (ready_for_issue).');
        } catch (DomainException $e) {
            return redirect()
                ->route('eic.submissions.show', $submission->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Mengunduh berkas bukti transfer pembayaran APC untuk diverifikasi EIC.
     */
    public function downloadPaymentProof(Request $request, string $id): StreamedResponse|BinaryFileResponse
    {
        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->firstOrFail();

        $payment = $submission->latestPayment;
        if (! $payment || ! $payment->fileEntry) {
            abort(404, 'Berkas bukti pembayaran tidak ditemukan.');
        }

        $fileEntry = $payment->fileEntry;
        $disk = Storage::disk($fileEntry->storage_disk);

        if (! $disk->exists($fileEntry->storage_path)) {
            abort(404, 'Berkas fisik bukti transfer tidak ditemukan di penyimpanan server.');
        }

        return $disk->download(
            $fileEntry->storage_path,
            $fileEntry->original_name,
            ['Content-Type' => $fileEntry->mime_type]
        );
    }

    /**
     * Memverifikasi pembayaran oleh Pemimpin Redaksi (EIC).
     */
    public function verifyPayment(Request $request, string $id): RedirectResponse
    {
        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->firstOrFail();

        $payment = $submission->latestPayment;
        if (! $payment) {
            return back()->with('error', 'Catatan tagihan pembayaran untuk naskah ini tidak ditemukan.');
        }

        $notes = (string) $request->input('notes', 'Pembayaran diverifikasi sah oleh Pemimpin Redaksi (EIC).');
        $verifier = $request->user() ?? User::firstOrFail();

        $this->articlePaymentService->verifyPayment($payment, $verifier, $notes);

        return back()->with('success', "Pembayaran untuk naskah '{$submission->title}' berhasil diverifikasi oleh Pemimpin Redaksi.");
    }
}
