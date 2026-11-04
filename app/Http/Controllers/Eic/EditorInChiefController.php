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
use App\Services\PrivateFileService;
use App\ViewModels\EicSubmissionDetailViewModel;
use App\ViewModels\EicSubmissionsViewModel;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $viewModel = new EicSubmissionDetailViewModel($submission, $this->eicService);

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

        return app(PrivateFileService::class)->stream($subFile->file);
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
            $user = $request->user();

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

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $query = trim($filters['q'] ?? '');
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 15;

        $builder = $this->eicService->eligibleSectionEditors($submission)
            ->select(['id', 'full_name', 'email'])
            ->withCount([
                'editorAssignments as active_assignments_count' => fn ($q) => $q->whereNull('revoked_at'),
            ]);

        if ($query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('full_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%");
            });
        }

        $paginator = $builder
            ->orderBy('full_name')
            ->orderBy('id')
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
            $eic = $request->user();
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
            $eic = $request->user();

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
            $eic = $request->user();

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

        return app(PrivateFileService::class)->stream($payment->fileEntry);
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
        $verifier = $request->user();

        $this->articlePaymentService->verifyPayment($payment, $verifier, 'editor_in_chief', $notes);

        return back()
            ->with('status', "Pembayaran untuk naskah '{$submission->title}' berhasil diverifikasi oleh Pemimpin Redaksi.")
            ->with('success', "Pembayaran untuk naskah '{$submission->title}' berhasil diverifikasi oleh Pemimpin Redaksi.");
    }

    /**
     * Menolak bukti pembayaran naskah oleh Pemimpin Redaksi (EIC).
     */
    public function rejectPayment(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Alasan penolakan bukti pembayaran wajib diisi.',
            'reason.max' => 'Alasan penolakan tidak boleh melebihi 1000 karakter.',
        ]);

        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->firstOrFail();

        $payment = $submission->latestPayment;
        if (! $payment) {
            return back()->with('error', 'Catatan tagihan pembayaran untuk naskah ini tidak ditemukan.');
        }

        $reason = (string) $request->input('reason');
        $verifier = $request->user() ?? User::firstOrFail();

        $this->articlePaymentService->rejectPayment($payment, $verifier, 'editor_in_chief', $reason);

        return back()
            ->with('status', "Bukti transfer naskah '{$submission->title}' berhasil ditolak. Penulis telah diinstruksikan untuk mengirimkan berkas bukti yang valid.")
            ->with('success', "Bukti transfer naskah '{$submission->title}' berhasil ditolak.");
    }

    /**
     * Memberikan pembebasan biaya publikasi (Waiver) oleh Pemimpin Redaksi (EIC).
     */
    public function waivePayment(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'waiver_reason' => ['required', 'string', 'max:1000'],
        ], [
            'waiver_reason.required' => 'Alasan pembebasan biaya wajib diisi.',
            'waiver_reason.max' => 'Alasan pembebasan biaya tidak boleh melebihi 1000 karakter.',
        ]);

        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->firstOrFail();

        $payment = $submission->latestPayment;
        if (! $payment) {
            $payment = $this->articlePaymentService->resolveOrCreatePayment($submission, $request->user());
        }

        if (! $payment) {
            return back()->with('error', 'Gagal menginisialisasi catatan pembayaran naskah.');
        }

        $reason = (string) $request->input('waiver_reason');
        $verifier = $request->user() ?? User::firstOrFail();

        $this->articlePaymentService->waivePayment($payment, $verifier, 'editor_in_chief', $reason);

        return back()
            ->with('status', "Biaya pemrosesan naskah '{$submission->title}' berhasil dibebaskan (Waiver).")
            ->with('success', "Biaya pemrosesan naskah '{$submission->title}' berhasil dibebaskan (Waiver).");
    }
}
