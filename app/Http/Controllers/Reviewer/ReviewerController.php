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
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReviewerController extends Controller
{
    public function __construct(
        protected readonly ReviewerService $reviewerService
    ) {}

    /**
     * Menampilkan Dashboard utama Mitra Bestari.
     */
    public function dashboard(Request $request): View
    {
        /** @var User $reviewer */
        $reviewer = Auth::user();

        $assignments = $this->reviewerService->getAssignmentsForReviewer($reviewer, [], 5);
        $viewModel = new ReviewerAssignmentsViewModel($reviewer, $assignments);

        // Naskah Prioritas yang sedang ditelaah dengan tenggat waktu terdekat
        $priorityAssignments = ReviewAssignment::query()
            ->where('reviewer_id', $reviewer->id)
            ->whereIn('status', ['accepted', 'overdue'])
            ->with([
                'reviewRound.submission.section',
                'reviewRound.anonymizedFile',
                'pendingExtension',
            ])
            ->orderByRaw('review_due_at IS NULL, review_due_at ASC')
            ->take(5)
            ->get();

        // Undangan telaah yang menunggu respon
        $pendingInvitations = ReviewAssignment::query()
            ->where('reviewer_id', $reviewer->id)
            ->where('status', 'invited')
            ->with([
                'reviewRound.submission.section',
            ])
            ->orderByRaw('response_due_at IS NULL, response_due_at ASC')
            ->take(5)
            ->get();

        // Telaah yang baru diselesaikan
        $recentCompleted = ReviewAssignment::query()
            ->where('reviewer_id', $reviewer->id)
            ->where('status', 'completed')
            ->with([
                'reviewRound.submission.section',
                'review',
            ])
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('reviewer.dashboard', compact(
            'viewModel',
            'priorityAssignments',
            'pendingInvitations',
            'recentCompleted'
        ));
    }

    /**
     * Menampilkan Pedoman & Etika Telaah Sejawat (Double-Blind Peer Review Ethics).
     */
    public function guidelines(): View
    {
        return view('reviewer.guidelines');
    }

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
            'reviewRound.submission.files.file',
            'reviewRound.anonymizedFile',
            'reviewRound.manuscriptFile',
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
     * Mengunduh berkas manuskrip anonim secara aman.
     */
    public function downloadFile(Request $request, string $id, string $fileId): StreamedResponse|RedirectResponse
    {
        /** @var User $reviewer */
        $reviewer = Auth::user();

        $assignment = ReviewAssignment::with([
            'reviewRound.submission.files.file',
            'reviewRound.anonymizedFile',
            'reviewRound.manuscriptFile',
        ])->where('reviewer_id', $reviewer->id)
            ->findOrFail($id);

        // Guard ERD baris 1571: Undangan reviewer yang belum direspon tidak memberikan hak akses unduh manuskrip
        if (! in_array($assignment->status, ['accepted', 'overdue', 'completed'], true)) {
            return redirect()
                ->route('reviewer.assignments.show', $assignment->id)
                ->with('error', 'Anda harus menerima surat undangan telaah terlebih dahulu sebelum dapat mengunduh naskah.');
        }

        /** @var ?File $fileEntry */
        $fileEntry = null;

        // Cari di anonymizedFile atau manuscriptFile pada reviewRound
        if ($assignment->reviewRound?->anonymizedFile && ($assignment->reviewRound->anonymizedFile->id === $fileId || $assignment->reviewRound->anonymized_file_id === $fileId)) {
            $fileEntry = $assignment->reviewRound->anonymizedFile;
        } elseif ($assignment->reviewRound?->manuscriptFile && ($assignment->reviewRound->manuscriptFile->id === $fileId || $assignment->reviewRound->manuscript_file_id === $fileId)) {
            $fileEntry = $assignment->reviewRound->manuscriptFile;
        } else {
            // Cari di submission files
            $subFile = $assignment->reviewRound?->submission?->files->first(function ($item) use ($fileId): bool {
                return $item->id === $fileId || $item->file_id === $fileId;
            });
            if ($subFile && $subFile->file) {
                $fileEntry = $subFile->file;
            }
        }

        if (! $fileEntry) {
            return redirect()
                ->route('reviewer.assignments.show', $assignment->id)
                ->with('error', 'Berkas naskah tidak ditemukan.');
        }

        if ($fileEntry->virus_scan_status && $fileEntry->virus_scan_status !== 'clean') {
            return redirect()
                ->route('reviewer.assignments.show', $assignment->id)
                ->with('error', 'Berkas belum lulus pemindaian antivirus (status: '.$fileEntry->virus_scan_status.').');
        }

        $submission = $assignment->reviewRound->submission;
        $filename = $fileEntry->original_name ?: ('Manuskrip-Anonim-'.$submission->submission_number.'.pdf');
        $mimeType = $fileEntry->mime_type ?: 'application/octet-stream';

        return response()->streamDownload(function () use ($submission, $fileEntry): void {
            if (Storage::disk($fileEntry->storage_disk ?: 'local')->exists($fileEntry->storage_path)) {
                echo Storage::disk($fileEntry->storage_disk ?: 'local')->get($fileEntry->storage_path);

                return;
            }

            echo "DOKUMEN MANUSKRIP RESMI SIRIDA KOTA MAKASSAR (DOUBLE-BLIND)\n";
            echo "========================================================\n";
            echo "Nomor Naskah : {$submission->submission_number}\n";
            echo "Judul Naskah : {$submission->title}\n";
            echo "Nama Berkas  : {$fileEntry->original_name}\n";
            echo "SHA-256      : {$fileEntry->checksum_sha256}\n\n";
            echo "Abstrak:\n{$submission->abstract}\n";
        }, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
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
