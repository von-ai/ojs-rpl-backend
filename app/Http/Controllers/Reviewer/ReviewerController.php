<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Services\Reviewer\ReviewerService;
use App\ViewModels\ReviewerAssignmentsViewModel;
use App\ViewModels\ReviewerWorkspaceViewModel;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;

class ReviewerController extends Controller
{
    public function __construct(
        protected readonly ReviewerService $reviewerService
    ) {}

    /**
     * Menampilkan daftar penugasan telaah untuk Mitra Bestari.
     */
    public function index(Request $request): View
    {
        /** @var User $reviewer */
        $reviewer = Auth::user();

        $filters = [
            'status' => $request->query('status', 'all'),
            'search' => $request->query('search', ''),
        ];

        $assignments = $this->reviewerService->getAssignmentsForReviewer($reviewer, $filters, 10);
        $viewModel = new ReviewerAssignmentsViewModel($reviewer, $assignments, $filters);

        return view('reviewer.assignments.index', compact('viewModel'));
    }

    /**
     * Menampilkan ruang kerja penelaahan ilmiah (Double-Blind Workspace).
     */
    public function show(string $id): View
    {
        /** @var User $reviewer */
        $reviewer = Auth::user();

        $assignment = ReviewAssignment::with([
            'reviewRound.submission.section',
            'reviewRound.submission.files',
            'review.annotations.file',
            'extensions',
            'pendingExtension',
        ])->where('reviewer_id', $reviewer->id)
            ->findOrFail($id);

        $rubricCriteria = $this->reviewerService->getDefaultRubricCriteria();
        $viewModel = new ReviewerWorkspaceViewModel($assignment, $reviewer, $rubricCriteria);

        return view('reviewer.assignments.show', compact('viewModel'));
    }

    /**
     * Menanggapi surat undangan penugasan reviewer (UC-08: Accept / Decline).
     */
    public function respond(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'response' => ['required', 'string', 'in:accepted,declined'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var User $reviewer */
        $reviewer = Auth::user();
        $assignment = ReviewAssignment::where('reviewer_id', $reviewer->id)->findOrFail($id);

        try {
            $this->reviewerService->respondToInvitation(
                $assignment,
                $validated['response'],
                $validated['reason'] ?? null,
                $reviewer
            );

            $msg = $validated['response'] === 'accepted'
                ? 'Terima kasih atas kesediaan Anda menelaah naskah ini. Ruang kerja telaah telah aktif.'
                : 'Tanggapan Anda telah dicatat. Terima kasih telah mengonfirmasi.';

            return redirect()->route('reviewer.assignments.show', $assignment->id)
                ->with('success', $msg);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('reviewer.assignments.show', $assignment->id)
                ->withErrors(['response' => $e->getMessage()]);
        }
    }

    /**
     * Mengajukan permohonan perpanjangan waktu telaah (UC-08: Extension Request).
     */
    public function requestExtension(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'requested_due_at' => ['required', 'date', 'after:today'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        /** @var User $reviewer */
        $reviewer = Auth::user();
        $assignment = ReviewAssignment::where('reviewer_id', $reviewer->id)->findOrFail($id);

        try {
            $this->reviewerService->requestExtension(
                $assignment,
                Carbon::parse($validated['requested_due_at']),
                $validated['reason'],
                $reviewer
            );

            return redirect()->route('reviewer.assignments.show', $assignment->id)
                ->with('success', 'Permohonan perpanjangan batas waktu berhasil diajukan kepada dewan editor.');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('reviewer.assignments.show', $assignment->id)
                ->withErrors(['extension' => $e->getMessage()]);
        }
    }

    /**
     * Menyimpan draf penilaian evaluasi rubrik sebelum submit resmi (UC-09: Save Draft).
     */
    public function saveDraft(Request $request, string $id): RedirectResponse
    {
        /** @var User $reviewer */
        $reviewer = Auth::user();
        $assignment = ReviewAssignment::where('reviewer_id', $reviewer->id)->findOrFail($id);

        try {
            $this->reviewerService->saveDraft($assignment, $request->all(), $reviewer);

            return redirect()->route('reviewer.assignments.show', $assignment->id)
                ->with('success', 'Draf hasil evaluasi telaah berhasil disimpan.');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('reviewer.assignments.show', $assignment->id)
                ->withErrors(['review' => $e->getMessage()]);
        }
    }

    /**
     * Mengirimkan hasil evaluasi telaah sejawat secara resmi (UC-09: Submit Review).
     */
    public function submitReview(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'scores' => ['nullable', 'array'],
            'comments_for_author' => ['required', 'string', 'min:10'],
            'confidential_comments_for_editor' => ['nullable', 'string'],
            'recommendation' => ['required', 'string', 'in:accept,revisions_required,resubmit,decline'],
            'certify_no_conflict' => ['required', 'accepted'],
            'annotated_file' => ['nullable', 'file', 'mimes:pdf,docx', 'max:20480'],
        ]);

        /** @var User $reviewer */
        $reviewer = Auth::user();
        $assignment = ReviewAssignment::where('reviewer_id', $reviewer->id)->findOrFail($id);

        try {
            $this->reviewerService->submitReview(
                $assignment,
                $validated,
                $request->file('annotated_file'),
                $reviewer
            );

            return redirect()->route('reviewer.assignments.show', $assignment->id)
                ->with('success', 'Hasil penelaahan ilmiah berhasil dikirimkan secara resmi. Terima kasih atas dedikasi dan kontribusi keilmuan Anda!');
        } catch (InvalidArgumentException $e) {
            return redirect()->route('reviewer.assignments.show', $assignment->id)
                ->withErrors(['submit' => $e->getMessage()]);
        }
    }
}
