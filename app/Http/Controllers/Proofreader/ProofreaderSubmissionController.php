<?php

declare(strict_types=1);

namespace App\Http\Controllers\Proofreader;

use App\Http\Controllers\Controller;
use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\User;
use App\Services\PrivateFileService;
use App\Services\Proofreader\ProofreaderService;
use App\ViewModels\ProofreaderDetailViewModel;
use App\ViewModels\ProofreaderSubmissionsViewModel;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProofreaderSubmissionController extends Controller
{
    public function __construct(
        protected ProofreaderService $proofreaderService
    ) {}

    /**
     * Menampilkan daftar penugasan pemeriksaan bukti baca (proofreading).
     */
    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new ProofreaderSubmissionsViewModel($tab, $search);

        return view('proofreader.submissions.index', compact('viewModel'));
    }

    /**
     * Menampilkan ruang kerja pemeriksaan galai & catatan koreksi.
     */
    public function show(string $id, Request $request): View
    {
        $submissionModel = $this->findSubmission($id);
        if (! $submissionModel) {
            abort(404, "Naskah penugasan pemeriksaan bukti baca '{$id}' tidak ditemukan.");
        }
        $this->authorizeProofreaderForSubmission($submissionModel);

        $viewModel = new ProofreaderDetailViewModel($id);
        $submission = $viewModel->submission();

        if (! $submission) {
            abort(404, "Naskah penugasan pemeriksaan bukti baca '{$id}' tidak ditemukan.");
        }

        return view('proofreader.submissions.show', compact('viewModel', 'submission', 'id'));
    }

    /**
     * Menampilkan halaman panduan bukti baca dan standar EYD.
     */
    public function guidelines(): View
    {
        return view('proofreader.guidelines');
    }

    /**
     * Menambahkan catatan koreksi (proofreading note) pada halaman galai.
     */
    public function addNote(Request $request): RedirectResponse
    {
        $submissionId = (string) ($request->input('submission_id') ?? '');
        $submission = $this->findSubmission($submissionId);
        $this->authorizeProofreaderForSubmission($submission);

        $request->validate([
            'submission_id' => ['required', 'string'],
            'category' => ['required', 'in:typo,layout,metadata,reference'],
            'severity' => ['nullable', 'in:minor,major,critical'],
            'page' => ['nullable', 'integer', 'min:1'],
            'description' => ['required', 'string', 'max:8000'],
        ]);
        $category = (string) $request->input('category', 'typo');
        $page = $request->input('page');
        $severity = (string) $request->input('severity', 'minor');
        $description = (string) $request->input('description', 'Catatan koreksi galai');

        $actor = $this->resolveActor();
        if ($submission && $actor) {
            try {
                $this->proofreaderService->addCorrectionNote(
                    $submission,
                    [
                        'category' => $category,
                        'page' => $page !== null && $page !== '' ? (int) $page : null,
                        'severity' => $severity,
                        'description' => $description,
                    ],
                    $actor
                );
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionId)
                    ->withErrors(['general' => $e->getMessage()]);
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', "Catatan koreksi ({$category}".($page ? " Hal. {$page}" : '').') berhasil ditambahkan.');
    }

    /**
     * Memverifikasi resolusi perbaikan dari layout editor.
     */
    public function verifyNote(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $noteId = (string) $request->input('note_id', '');

        $submission = $this->findSubmission($submissionId);
        $this->authorizeProofreaderForSubmission($submission);

        $actor = $this->resolveActor();

        if ($noteId !== '' && $actor) {
            try {
                $note = null;
                if (class_exists(ProofreadingNote::class) && Schema::hasTable('proofreading_notes')) {
                    $note = ProofreadingNote::findOrFail($noteId);
                }

                if ($note) {
                    $this->proofreaderService->verifyCorrectionNote($note, $actor);
                }
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionId)
                    ->withErrors(['general' => $e->getMessage()]);
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', 'Perbaikan galai berhasil diverifikasi.');
    }

    /**
     * Memberikan persetujuan akhir (Proofreader Sign-off).
     */
    public function completeProofread(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $submission = $this->findSubmission($submissionId);
        $this->authorizeProofreaderForSubmission($submission);

        $actor = $this->resolveActor();

        if ($submission && $actor) {
            try {
                $this->proofreaderService->completeSignoff($submission, $actor);
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionId)
                    ->withErrors(['general' => $e->getMessage()])
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', 'Pemeriksaan bukti baca selesai. Galai disetujui untuk tahap persetujuan akhir.');
    }

    /**
     * Mengajukan eskalasi ketidaksesuaian naskah kepada editor naskah.
     */
    public function submitEscalation(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $reason = (string) $request->input('reason', 'Ketidaksesuaian naskah dengan hasil copyediting.');
        $submission = $this->findSubmission($submissionId);
        $this->authorizeProofreaderForSubmission($submission);

        $actor = $this->resolveActor();

        if ($submission && $actor) {
            try {
                $this->proofreaderService->submitEscalation($submission, $reason, $actor);
            } catch (DomainException $e) {
                return redirect()->route('proofreader.submissions.show', $submissionId)
                    ->withErrors(['general' => $e->getMessage()])
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', 'Tiket eskalasi ketidaksesuaian naskah berhasil dikirimkan ke Editor.');
    }

    /**
     * Mengunduh berkas galai publikasi resmi secara aman.
     */
    public function downloadGalley(string $id): StreamedResponse
    {
        $galley = Galley::with('file')->findOrFail($id);
        abort_unless($galley->file, 404);

        return app(PrivateFileService::class)->download($galley->file, Auth::user());
    }

    /**
     * Mengunduh berkas naskah acuan copyediting atau berkas pendukung layout.
     */
    public function downloadFile(string $id): StreamedResponse
    {
        $file = FileEntry::find($id);
        if (! $file) {
            $submission = Submission::where('submission_number', $id)->firstOrFail();
            $file = $submission->finalCopyeditFile();
        }
        abort_unless($file, 404);

        return app(PrivateFileService::class)->download($file, Auth::user());
    }

    /**
     * Mencari submission berdasarkan ID atau nomor naskah.
     */
    protected function findSubmission(string $identifier): ?Submission
    {
        if (! class_exists(Submission::class) || ! Schema::hasTable('submissions')) {
            return null;
        }

        return Submission::withoutGlobalScope('portal_access')
            ->where(function ($q) use ($identifier) {
                $q->where('id', $identifier)
                    ->orWhere('submission_number', $identifier);
            })
            ->first();
    }

    /**
     * Mendapatkan aktor proofreader aktif.
     */
    protected function resolveActor(): User
    {
        return Auth::user();
    }

    /**
     * Otorisasi ketat hak akses ruang kerja penugasan proofreading.
     */
    private function authorizeProofreaderForSubmission(?Submission $submission): void
    {
        $userId = Auth::id();
        if (! $userId) {
            abort(401);
        }

        if (! $submission) {
            abort(404, 'Naskah tidak ditemukan.');
        }

        $isAssigned = ProofAssignment::where('proofreader_id', $userId)
            ->whereHas('galley', function ($q) use ($submission) {
                $q->where('submission_id', $submission->id);
            })
            ->exists();

        if (! $isAssigned) {
            abort(403, 'Anda tidak ditugaskan sebagai Proofreader pada naskah ini.');
        }
    }
}
