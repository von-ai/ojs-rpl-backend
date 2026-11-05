<?php

declare(strict_types=1);

namespace App\Http\Controllers\LayoutEditor;

use App\Http\Controllers\Controller;
use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Services\LayoutEditor\LayoutEditorService;
use App\Services\PrivateFileService;
use App\ViewModels\LayoutEditorDetailViewModel;
use App\ViewModels\LayoutEditorSubmissionsViewModel;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LayoutEditorSubmissionController extends Controller
{
    public function __construct(
        protected LayoutEditorService $layoutEditorService
    ) {}

    /**
     * Menampilkan daftar penugasan tata letak galai publikasi.
     */
    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new LayoutEditorSubmissionsViewModel($tab, $search);

        return view('layouteditor.submissions.index', compact('viewModel'));
    }

    /**
     * Menampilkan ruang kerja produksi galai naskah spesifik.
     */
    public function show(string $id, Request $request): View
    {
        $submissionModel = $this->findSubmission($id);
        if (! $submissionModel) {
            abort(404, "Naskah penugasan tata letak '{$id}' tidak ditemukan.");
        }
        $this->authorizeLayoutEditorForSubmission($submissionModel);

        $viewModel = new LayoutEditorDetailViewModel($id);
        $submission = $viewModel->submission();

        if (! $submission) {
            abort(404, "Naskah penugasan tata letak '{$id}' tidak ditemukan.");
        }

        return view('layouteditor.submissions.show', compact('submission', 'id'));
    }

    /**
     * Menampilkan halaman panduan tata letak format galai PDF/HTML.
     */
    public function guidelines(): View
    {
        return view('layouteditor.guidelines');
    }

    /**
     * Aksi unggah draf berkas galai baru atau revisi.
     */
    public function uploadGalley(Request $request): RedirectResponse
    {
        $submissionId = (string) ($request->input('submission_id') ?? '');
        $submission = $this->findSubmission($submissionId);
        $this->authorizeLayoutEditorForSubmission($submission);

        $data = $request->validate([
            'submission_id' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,html', 'max:20480'],
            'galley_label' => ['required', 'string', 'max:191'],
            'format' => ['required', 'in:pdf,html'],
            'change_summary' => ['nullable', 'string', 'max:4000'],
        ]);
        try {
            $this->layoutEditorService->uploadGalley($submission, $request->file('file'), $data['galley_label'], $data['format'], Auth::user(), $data['change_summary'] ?? null);
        } catch (DomainException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect()->route('layouteditor.submissions.show', $data['submission_id'])->with('status', 'Galai diunggah dan menunggu pemindaian keamanan.');
    }

    /**
     * Aksi menandai catatan koreksi proofreading telah diselesaikan.
     */
    public function resolveNote(Request $request): RedirectResponse
    {
        $data = $request->validate(['note_id' => ['required', 'string'], 'resolution' => ['required', 'string', 'max:4000']]);
        $note = ProofreadingNote::with('galley.submission')->findOrFail($data['note_id']);
        $submission = $note->galley?->submission;
        $this->authorizeLayoutEditorForSubmission($submission);
        $this->layoutEditorService->resolveProofreadingNote($note, $data['resolution'], Auth::user());

        return redirect()->route('layouteditor.submissions.show', $request->input('submission_id', $note->galley->submission_id))->with('status', 'Catatan koreksi ditandai selesai dan menunggu verifikasi proofreader.');
    }

    /**
     * Aksi mengajukan berkas galai ke putaran proofreading.
     */
    public function requestProofread(Request $request): RedirectResponse
    {
        $submissionParam = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $submission = $this->findSubmission($submissionParam);
        $this->authorizeLayoutEditorForSubmission($submission);

        $actor = Auth::user();

        if ($submission && $actor) {
            try {
                $this->layoutEditorService->requestProofreading($submission, $actor);
            } catch (DomainException $e) {
                return redirect()->route('layouteditor.submissions.show', $submissionParam)
                    ->withErrors(['general' => $e->getMessage()]);
            }
        }

        return redirect()->route('layouteditor.submissions.show', $submissionParam)
            ->with('status', 'Berkas galai berhasil dikirim ke putaran pemeriksaan Proofreader.');
    }

    /**
     * Aksi eskalasi kendala tata letak ke Section Editor.
     */
    public function submitEscalation(Request $request): RedirectResponse
    {
        $submissionParam = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $reason = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:4000']])['reason'];

        $submission = $this->findSubmission($submissionParam);
        $this->authorizeLayoutEditorForSubmission($submission);

        $actor = Auth::user();

        if ($submission && $actor) {
            $this->layoutEditorService->submitEscalation($submission, $actor, $reason);
        }

        return redirect()->route('layouteditor.submissions.show', $submissionParam)
            ->with('status', 'Tiket eskalasi kendala tata letak berhasil diterbitkan ke Section Editor.');
    }

    /**
     * Aksi permohonan persetujuan final Editor in Chief untuk publikasi.
     */
    public function requestFinalApproval(Request $request): RedirectResponse
    {
        $submissionParam = (string) $request->validate(['submission_id' => ['required', 'string']])['submission_id'];
        $submission = $this->findSubmission($submissionParam);
        $this->authorizeLayoutEditorForSubmission($submission);

        $actor = Auth::user();

        if ($submission && $actor) {
            try {
                $this->layoutEditorService->requestFinalApproval($submission, $actor);
            } catch (DomainException $e) {
                return redirect()->route('layouteditor.submissions.show', $submissionParam)
                    ->withErrors(['general' => $e->getMessage()]);
            }
        }

        if ($submission && $actor) {
            try {
                $this->layoutEditorService->requestFinalApproval($submission, $actor);
            } catch (DomainException $e) {
                return redirect()->route('layouteditor.submissions.show', $submissionParam)
                    ->withErrors(['general' => $e->getMessage()]);
            }
        }

        return redirect()->route('layouteditor.submissions.show', $submissionParam)
            ->with('status', 'Permohonan persetujuan akhir galai berhasil diajukan ke Editor in Chief.');
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

    private function findSubmission(string $identifier): ?Submission
    {
        try {
            if (class_exists(Submission::class) && Schema::hasTable('submissions')) {
                return Submission::withoutGlobalScope('portal_access')
                    ->where(function ($q) use ($identifier) {
                        $q->where('submission_number', $identifier)
                            ->orWhere('id', $identifier);
                    })
                    ->first();
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * Otorisasi ketat hak akses ruang kerja penugasan layout editor.
     */
    private function authorizeLayoutEditorForSubmission(?Submission $submission): void
    {
        $userId = Auth::id();
        if (! $userId) {
            abort(401);
        }

        if (! $submission) {
            abort(404, 'Naskah tidak ditemukan.');
        }

        $isAssigned = $submission->layoutAssignments()
            ->where('layout_editor_id', $userId)
            ->whereNull('revoked_at')
            ->exists();

        if (! $isAssigned) {
            abort(403, 'Anda tidak ditugaskan sebagai Layout Editor pada naskah ini.');
        }
    }
}
