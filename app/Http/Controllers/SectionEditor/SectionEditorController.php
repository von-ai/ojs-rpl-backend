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
use Symfony\Component\HttpFoundation\StreamedResponse;

class SectionEditorController extends Controller
{
    public function __construct(
        protected readonly SectionEditorService $sectionEditorService
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
            ->latest()
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
        $submission = Submission::with('files.file')
            ->where('id', $id)
            ->orWhere('submission_number', $id)
            ->firstOrFail();

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
        $data = $request->validate([
            'anonymous_file' => ['required', 'file', 'mimes:pdf,docx', 'max:20480'],
            'reviewer_title' => ['required', 'string', 'max:500'],
            'reviewer_abstract' => ['required', 'string', 'max:20000'],
            'anonymity_confirmed' => ['required', 'accepted'],
            'note' => ['nullable', 'string', 'max:4000'],
        ]);
        $submission = Submission::where('id', $id)->orWhere('submission_number', $id)->firstOrFail();
        $upload = $request->file('anonymous_file');
        $disk = config('filesecurity.disk', 'local');
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
        ]);

        /** @var User $editor */
        $editor = Auth::user();
        $round = ReviewRound::findOrFail($roundId);
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
        $assignment = ReviewAssignment::findOrFail($assignmentId);
        $submissionId = $assignment->reviewRound->submission_id;

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
        $assignment = ReviewAssignment::findOrFail($assignmentId);
        $message = $request->input('message');

        $this->sectionEditorService->sendReminder($assignment, $editor, $message);

        return redirect()->route('section-editor.submissions.show', $assignment->reviewRound->submission_id)
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
        $extension = ReviewExtension::findOrFail($extensionId);
        $submissionId = $extension->assignment->reviewRound->submission_id;

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
        ]);

        /** @var User $editor */
        $editor = Auth::user();
        $round = ReviewRound::findOrFail($roundId);

        try {
            $this->sectionEditorService->submitRecommendation(
                $round,
                $validated['recommendation'],
                $validated['note'],
                $editor
            );

            return redirect()->route('section-editor.submissions.show', $round->submission_id)
                ->with('success', 'Rekomendasi editorial berhasil dikirimkan ke Pemimpin Redaksi (EIC).');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('section-editor.submissions.show', $round->submission_id)
                ->withErrors(['recommendation' => $e->getMessage()]);
        }
    }
}
