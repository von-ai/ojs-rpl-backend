<?php

declare(strict_types=1);

namespace App\Http\Controllers\SectionEditor;

use App\Http\Controllers\Controller;
use App\Models\FileEntry;
use App\Models\ReviewAssignment;
use App\Models\ReviewExtension;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\User;
use App\Services\Payment\ArticlePaymentService;
use App\Services\PrivateFileService;
use App\Services\SectionEditor\SectionEditorService;
use App\ViewModels\SectionEditorSubmissionsViewModel;
use App\ViewModels\SectionEditorWorkspaceViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SectionEditorController extends Controller
{
    public function __construct(
        protected readonly SectionEditorService $sectionEditorService,
        protected readonly ArticlePaymentService $articlePaymentService
    ) {}

    /**
     * Menampilkan dashboard ringkasan eksekutif untuk Section Editor.
     */
    public function dashboard(): View
    {
        /** @var User $editor */
        $editor = Auth::user();

        $submissions = $this->sectionEditorService->getAssignedSubmissions($editor, ['status' => 'all'], 5);
        $viewModel = new SectionEditorSubmissionsViewModel($editor, $submissions, ['status' => 'all']);

        $prioritySubmissions = Submission::query()
            ->whereHas('editorAssignments', function ($q) use ($editor) {
                $q->where('editor_id', $editor->id)->whereNull('revoked_at');
            })
            ->with([
                'author',
                'section',
                'currentReviewRound.assignments.reviewer',
                'currentReviewRound.assignments.review',
                'currentReviewRound.assignments.extensions',
            ])
            ->whereIn('status', ['in_review', 'under_review', 'revisions_requested'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $pendingExtensions = ReviewExtension::query()
            ->where('status', 'pending')
            ->whereHas('assignment.reviewRound.submission.editorAssignments', function ($q) use ($editor) {
                $q->where('editor_id', $editor->id)->whereNull('revoked_at');
            })
            ->with(['assignment.reviewer', 'assignment.reviewRound.submission'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $recentRecommendations = ReviewRound::query()
            ->whereNotNull('se_recommendation')
            ->whereHas('submission.editorAssignments', function ($q) use ($editor) {
                $q->where('editor_id', $editor->id)->whereNull('revoked_at');
            })
            ->with(['submission.author'])
            ->latest('se_recommended_at')
            ->limit(5)
            ->get();

        return view('section-editor.dashboard', compact(
            'viewModel',
            'prioritySubmissions',
            'pendingExtensions',
            'recentRecommendations'
        ));
    }

    /**
     * Menampilkan pedoman telaah & etika penugasan mitra bestari bagi Section Editor.
     */
    public function guidelines(): View
    {
        return view('section-editor.guidelines');
    }

    /**
     * Menampilkan daftar naskah yang ditugaskan kepada Section Editor.
     */
    public function index(Request $request): View
    {
        /** @var User $editor */
        $editor = Auth::user();

        $filters = [
            'status' => $request->query('status', 'all'),
            'search' => $request->query('search', ''),
        ];

        $submissions = $this->sectionEditorService->getAssignedSubmissions($editor, $filters, 10);
        $viewModel = new SectionEditorSubmissionsViewModel($editor, $submissions, $filters);

        return view('section-editor.submissions.index', compact('viewModel'));
    }

    /**
     * Menampilkan workspace penelaahan dan manajemen putaran naskah.
     */
    public function show(string $id): View
    {
        /** @var User $editor */
        $editor = Auth::user();

        $submission = Submission::with([
            'journal',
            'author',
            'contributors',
            'section',
            'files.file',
            'revisions.manuscriptFile',
            'revisions.responseLetterFile',
            'deskReviews.assessor',
            'editorialDecisions.decider',
            'reviewRounds.assignments.reviewer',
            'reviewRounds.assignments.review',
            'reviewRounds.assignments.extensions',
            'reviewRounds.assignments.reminders',
        ])
            ->where('id', $id)
            ->orWhere('submission_number', $id)
            ->firstOrFail();

        $this->authorizeSectionEditorForSubmission($submission, $editor);

        $activeRound = $submission->currentReviewRound ?? new ReviewRound(['submission_id' => $submission->id, 'round' => (int) $submission->reviewRounds()->max('round') + 1, 'status' => 'not_started', 'minimum_completed_reviews' => 2]);
        $activeRound->load([
            'assignments.reviewer',
            'assignments.review',
            'assignments.extensions',
            'assignments.reminders',
        ]);

        $availableReviewers = $this->sectionEditorService->getAvailableReviewers($submission);
        $viewModel = new SectionEditorWorkspaceViewModel($submission, $activeRound, $editor, $availableReviewers);

        return view('section-editor.submissions.show', compact('viewModel', 'submission'));
    }

    /**
     * Mengunduh berkas manuskrip atau lampiran naskah dengan header keamanan privat.
     */
    public function downloadFile(string $id, string $fileId): StreamedResponse|RedirectResponse
    {
        /** @var User $editor */
        $editor = Auth::user();

        $submission = Submission::with('files.file')
            ->where('id', $id)
            ->orWhere('submission_number', $id)
            ->firstOrFail();

        $this->authorizeSectionEditorForSubmission($submission, $editor);

        $subFile = $submission->files->first(function ($item) use ($fileId): bool {
            return $item->id === $fileId || $item->file_id === $fileId;
        });

        if (! $subFile || ! $subFile->file) {
            return redirect()
                ->route('section-editor.submissions.show', $submission->id)
                ->with('error', 'Berkas manuskrip tidak ditemukan pada naskah ini.');
        }

        return app(PrivateFileService::class)->stream($subFile->file);
    }

    /**
     * Membuka putaran telaah baru (Round 2 pasca revisi dsb).
     */
    public function storeRound(Request $request, string $id): RedirectResponse
    {
        /** @var User $editor */
        $editor = Auth::user();
        $submission = Submission::where('id', $id)->orWhere('submission_number', $id)->firstOrFail();
        $this->authorizeSectionEditorForSubmission($submission, $editor);

        $data = $request->validate([
            'anonymous_file' => ['required', 'file', 'mimes:pdf,docx', 'max:20480'],
            'reviewer_title' => ['required', 'string', 'max:500'],
            'reviewer_abstract' => ['required', 'string', 'max:20000'],
            'anonymity_confirmed' => ['required', 'accepted'],
            'note' => ['nullable', 'string', 'max:4000'],
        ]);
        $upload = $request->file('anonymous_file');
        $disk = (string) config('filesecurity.disk', 'azure');
        $path = $upload->store('review-anonymous/'.$submission->id, $disk);
        try {
            DB::transaction(function () use ($submission, $upload, $disk, $path, $data) {
                $entry = FileEntry::create([
                    'storage_disk' => $disk, 'storage_path' => $path, 'original_name' => 'manuskrip-anonim.'.$upload->extension(),
                    'mime_type' => $upload->getMimeType(), 'byte_size' => (int) Storage::disk($disk)->size($path),
                    'checksum_sha256' => hash_file('sha256', $upload->getRealPath()), 'virus_scan_status' => 'pending', 'created_by' => Auth::id(),
                ]);
                $this->sectionEditorService->ensureActiveReviewRound($submission, $data['note'] ?? null, $entry, $data['reviewer_title'], $data['reviewer_abstract']);
            });
        } catch (InvalidArgumentException $e) {
            Storage::disk($disk)->delete($path);

            return back()->withErrors(['round' => $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            Storage::disk($disk)->delete($path);
            throw $e;
        }

        return redirect()->route('section-editor.submissions.show', $submission->id)->with('success', 'Putaran disiapkan. Undangan tersedia setelah berkas anonim lulus pemindaian.');
    }

    /**
     * Menugaskan / mengundang mitra bestari (Reviewer) ke naskah (UC-11).
     */
    public function assignReviewer(Request $request, string $roundId): RedirectResponse
    {
        $validated = $request->validate([
            'reviewer_id' => ['required', 'string', 'exists:users,id'],
            'response_due_at' => ['required', 'date', 'after:today'],
            'review_due_at' => ['required', 'date', 'after:response_due_at'],
            'invitation_message' => ['nullable', 'string', 'max:2000'],
        ], [
            'reviewer_id.required' => 'Mitra bestari wajib dipilih dari direktori.',
            'reviewer_id.exists' => 'Mitra bestari yang dipilih tidak terdaftar di sistem.',
            'response_due_at.required' => 'Batas waktu respon konfirmasi wajib diisi.',
            'response_due_at.after' => 'Batas waktu respon konfirmasi harus setelah hari ini.',
            'review_due_at.required' => 'Batas waktu penyelesaian telaah wajib diisi.',
            'review_due_at.after' => 'Batas waktu penyelesaian telaah harus setelah batas waktu respon.',
            'invitation_message.max' => 'Pesan pengantar undangan maksimal 2000 karakter.',
        ]);

        /** @var User $editor */
        $editor = Auth::user();
        $round = ReviewRound::with('submission')->findOrFail($roundId);
        $this->authorizeSectionEditorForSubmission($round->submission, $editor);

        $reviewer = User::findOrFail($validated['reviewer_id']);

        try {
            $this->sectionEditorService->assignReviewer(
                $round,
                $reviewer,
                [
                    'response_due_at' => $validated['response_due_at'],
                    'review_due_at' => $validated['review_due_at'],
                    'invitation_message' => $validated['invitation_message'] ?? null,
                ],
                $editor
            );

            return redirect()->route('section-editor.submissions.show', $round->submission_id)
                ->with('success', "Undangan mitra bestari berhasil dikirimkan kepada {$reviewer->name}.");
        } catch (InvalidArgumentException $e) {
            return redirect()->route('section-editor.submissions.show', $round->submission_id)
                ->withErrors(['reviewer_id' => $e->getMessage()]);
        }
    }

    /**
     * Membatalkan undangan mitra bestari yang belum direspon.
     */
    public function cancelAssignment(string $assignmentId): RedirectResponse
    {
        /** @var User $editor */
        $editor = Auth::user();
        $assignment = ReviewAssignment::with('reviewRound.submission')->findOrFail($assignmentId);
        $submission = $assignment->reviewRound->submission;
        $this->authorizeSectionEditorForSubmission($submission, $editor);

        $submissionId = $submission->id;

        try {
            $this->sectionEditorService->cancelInvitation($assignment, $editor);

            return redirect()->route('section-editor.submissions.show', $submissionId)
                ->with('success', 'Undangan penelaah berhasil dibatalkan.');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('section-editor.submissions.show', $submissionId)
                ->withErrors(['assignment' => $e->getMessage()]);
        }
    }

    /**
     * Mengirimkan surat pengingat kepada mitra bestari.
     */
    public function sendReminder(Request $request, string $assignmentId): RedirectResponse
    {
        /** @var User $editor */
        $editor = Auth::user();
        $assignment = ReviewAssignment::with('reviewRound.submission')->findOrFail($assignmentId);
        $submission = $assignment->reviewRound->submission;
        $this->authorizeSectionEditorForSubmission($submission, $editor);

        $message = $request->input('message');

        $this->sectionEditorService->sendReminder($assignment, $editor, $message);

        return redirect()->route('section-editor.submissions.show', $submission->id)
            ->with('success', "Surat pengingat telaah berhasil dikirimkan kepada {$assignment->reviewer->name}.");
    }

    /**
     * Memutuskan permohonan perpanjangan waktu telaah reviewer (UC-08).
     */
    public function decideExtension(Request $request, string $extensionId): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:approved,rejected'],
            'decision_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var User $editor */
        $editor = Auth::user();
        $extension = ReviewExtension::with('assignment.reviewRound.submission')->findOrFail($extensionId);
        $submission = $extension->assignment->reviewRound->submission;
        $this->authorizeSectionEditorForSubmission($submission, $editor);

        $submissionId = $submission->id;

        try {
            $this->sectionEditorService->decideExtension(
                $extension,
                $validated['decision'],
                $validated['decision_reason'] ?? null,
                $editor
            );

            $msg = $validated['decision'] === 'approved'
                ? 'Permohonan perpanjangan waktu telaah berhasil disetujui.'
                : 'Permohonan perpanjangan waktu telaah ditolak.';

            return redirect()->route('section-editor.submissions.show', $submissionId)
                ->with('success', $msg);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('section-editor.submissions.show', $submissionId)
                ->withErrors(['extension' => $e->getMessage()]);
        }
    }

    /**
     * Mengirimkan rekomendasi editorial resmi Section Editor ke Pemimpin Redaksi (EIC).
     */
    public function submitRecommendation(Request $request, string $roundId): RedirectResponse
    {
        $validated = $request->validate([
            'recommendation' => ['required', 'string', 'in:accept,revisions_required,resubmit,decline'],
            'note' => ['required', 'string', 'min:10', 'max:5000'],
            'is_override' => ['nullable', 'boolean'],
            'override_reason' => ['nullable', 'string', 'min:10', 'max:2000'],
        ], [
            'recommendation.required' => 'Rekomendasi keputusan editorial wajib dipilih.',
            'recommendation.in' => 'Pilihan rekomendasi keputusan tidak valid.',
            'note.required' => 'Catatan evaluasi substantif untuk EIC wajib diisi.',
            'note.min' => 'Catatan evaluasi substantif untuk EIC wajib diisi minimal 10 karakter.',
            'note.max' => 'Catatan evaluasi substantif untuk EIC maksimal 5000 karakter.',
            'override_reason.min' => 'Alasan pengabaian kuorum minimum wajib diisi minimal 10 karakter.',
            'override_reason.max' => 'Alasan pengabaian kuorum minimum maksimal 2000 karakter.',
        ]);

        /** @var User $editor */
        $editor = Auth::user();
        $round = ReviewRound::with('submission')->findOrFail($roundId);
        $this->authorizeSectionEditorForSubmission($round->submission, $editor);

        try {
            $overrideReason = $request->filled('override_reason') ? (string) $request->input('override_reason') : null;

            $this->sectionEditorService->submitRecommendation(
                $round,
                $validated['recommendation'],
                $validated['note'],
                $editor,
                $overrideReason
            );

            $msg = ! empty($overrideReason)
                ? 'Rekomendasi editorial berhasil dikirimkan ke Pemimpin Redaksi (EIC) dengan dispensasi pengabaian kuorum.'
                : 'Rekomendasi editorial berhasil dikirimkan ke Pemimpin Redaksi (EIC).';

            return redirect()->route('section-editor.submissions.show', $round->submission_id)
                ->with('success', $msg);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('section-editor.submissions.show', $round->submission_id)
                ->withErrors(['recommendation' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Memastikan bahwa editor yang sedang login secara sah ditugaskan ke naskah terkait.
     */
    private function authorizeSectionEditorForSubmission(Submission $submission, User $editor): void
    {
        if ($editor->isSysAdmin() || in_array('editor_in_chief', $editor->availableRoleCodes(), true)) {
            return;
        }

        $isAssigned = $submission->editorAssignments()
            ->where('editor_id', $editor->id)
            ->whereNull('revoked_at')
            ->exists();

        if (! $isAssigned) {
            abort(403, 'Anda tidak ditugaskan sebagai Editor Bagian untuk naskah ini.');
        }
    }

    /**
     * Mengunduh berkas bukti transfer pembayaran APC untuk ditinjau Editor Bagian.
     */
    public function downloadPaymentProof(Request $request, string $id): StreamedResponse|BinaryFileResponse
    {
        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->firstOrFail();

        /** @var User $editor */
        $editor = Auth::user();
        $this->authorizeSectionEditorForSubmission($submission, $editor);

        $payment = $submission->latestPayment;
        if (! $payment || ! $payment->fileEntry) {
            abort(404, 'Berkas bukti pembayaran tidak ditemukan.');
        }

        return app(PrivateFileService::class)->stream($payment->fileEntry);
    }

    /**
     * Memverifikasi pembayaran oleh Editor Bagian.
     */
    public function verifyPayment(Request $request, string $id): RedirectResponse
    {
        $submission = Submission::where(function ($query) use ($id): void {
            $query->where('id', $id)
                ->orWhere('submission_number', $id);
        })->firstOrFail();

        /** @var User $editor */
        $editor = Auth::user();
        $this->authorizeSectionEditorForSubmission($submission, $editor);

        $payment = $submission->latestPayment;
        if (! $payment) {
            return back()->with('error', 'Catatan tagihan pembayaran untuk naskah ini tidak ditemukan.');
        }

        $notes = (string) $request->input('notes', 'Pembayaran diverifikasi sah oleh Editor Bagian.');
        $this->articlePaymentService->verifyPayment($payment, $editor, 'section_editor', $notes);

        return back()
            ->with('status', "Pembayaran untuk naskah '{$submission->title}' berhasil diverifikasi oleh Editor Bagian.")
            ->with('success', "Pembayaran untuk naskah '{$submission->title}' berhasil diverifikasi oleh Editor Bagian.");
    }

    /**
     * Menolak bukti pembayaran naskah oleh Editor Bagian.
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

        /** @var User $editor */
        $editor = Auth::user();
        $this->authorizeSectionEditorForSubmission($submission, $editor);

        $payment = $submission->latestPayment;
        if (! $payment) {
            return back()->with('error', 'Catatan tagihan pembayaran untuk naskah ini tidak ditemukan.');
        }

        $reason = (string) $request->input('reason');
        $this->articlePaymentService->rejectPayment($payment, $editor, 'section_editor', $reason);

        return back()
            ->with('status', "Bukti transfer naskah '{$submission->title}' berhasil ditolak. Penulis telah diinstruksikan untuk mengirimkan berkas bukti yang valid.")
            ->with('success', "Bukti transfer naskah '{$submission->title}' berhasil ditolak.");
    }

    /**
     * Memberikan pembebasan biaya publikasi (Waiver) oleh Editor Bagian.
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

        /** @var User $editor */
        $editor = Auth::user();
        $this->authorizeSectionEditorForSubmission($submission, $editor);

        $payment = $submission->latestPayment;
        if (! $payment) {
            $payment = $this->articlePaymentService->resolveOrCreatePayment($submission, $editor);
        }

        if (! $payment) {
            return back()->with('error', 'Gagal menginisialisasi catatan pembayaran naskah.');
        }

        $reason = (string) $request->input('waiver_reason');
        $this->articlePaymentService->waivePayment($payment, $editor, 'section_editor', $reason);

        return back()
            ->with('status', "Biaya pemrosesan naskah '{$submission->title}' berhasil dibebaskan (Waiver).")
            ->with('success', "Biaya pemrosesan naskah '{$submission->title}' berhasil dibebaskan (Waiver).");
    }
}
