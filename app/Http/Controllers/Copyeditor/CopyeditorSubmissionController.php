<?php

declare(strict_types=1);

namespace App\Http\Controllers\Copyeditor;

use App\Http\Controllers\Controller;
use App\Models\Copyedit;
use App\Models\FileEntry;
use App\Models\User;
use App\Services\Copyeditor\CopyeditorService;
use App\ViewModels\CopyeditorSubmissionDetailViewModel;
use App\ViewModels\CopyeditorSubmissionsViewModel;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CopyeditorSubmissionController extends Controller
{
    public function __construct(
        protected CopyeditorService $copyeditorService
    ) {}

    /**
     * Menampilkan daftar penugasan copyediting naskah.
     */
    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new CopyeditorSubmissionsViewModel($tab, $search);

        return view('copyeditor.submissions.index', compact('viewModel'));
    }

    /**
     * Menampilkan ruang kerja naskah copyedit spesifik.
     */
    public function show(string $id, Request $request): View
    {
        $viewModel = new CopyeditorSubmissionDetailViewModel($id);
        $submission = $viewModel->submission();

        if (! $submission) {
            abort(404, "Naskah penugasan copyediting '{$id}' tidak ditemukan.");
        }

        return view('copyeditor.submissions.show', compact('submission'));
    }

    /**
     * Menampilkan halaman dedikasi Panduan Gaya Selingkung & Format Naskah BRIDA.
     */
    public function guidelines(): View
    {
        return view('copyeditor.guidelines');
    }

    /**
     * Tautan kompatibilitas atau shortcut ke ruang kerja naskah utama.
     */
    public function catalog(Request $request, string $id = 'SUB-2026-00106'): View|RedirectResponse
    {
        return $this->show($id, $request);
    }

    /**
     * Mengunduh berkas fisik (naskah acuan atau berkas hasil copyediting).
     */
    public function downloadFile(string $id): StreamedResponse
    {
        $file = null;
        if (Schema::hasTable('files')) {
            $file = FileEntry::where('id', $id)
                ->orWhere('original_name', $id)
                ->first();
        }

        if (! $file) {
            abort(404, 'Data berkas tidak ditemukan di basis data.');
        }

        $disk = Storage::disk($file->storage_disk ?: 'local');

        if (! $disk->exists($file->storage_path)) {
            abort(404, 'Berkas fisik tidak ditemukan di penyimpanan server.');
        }

        return $disk->download($file->storage_path, $file->original_name);
    }

    /**
     * Aksi unggah berkas baru ke Output Set naskah.
     */
    public function uploadFile(Request $request): RedirectResponse
    {
        $request->validate([
            'submission_id' => 'required|string',
            'copyedit_file' => 'nullable|file|mimes:docx,doc,pdf|max:20480',
        ]);

        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $copyedit = $this->findCopyedit($submissionId);

        if ($copyedit && $request->hasFile('copyedit_file') && $request->file('copyedit_file')->isValid()) {
            $user = Auth::user() ?? $copyedit->copyEditor;
            $this->copyeditorService->uploadOutputFile($copyedit, $request->file('copyedit_file'), $user);
        }

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Berkas naskah hasil suntingan berhasil diunggah. Persetujuan penulis otomatis diperbarui untuk meninjau berkas terbaru.');
    }

    /**
     * Permohonan persetujuan penulis atas berkas hasil suntingan.
     */
    public function requestApproval(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $copyedit = $this->findCopyedit($submissionId);

        if ($copyedit) {
            try {
                $this->copyeditorService->requestAuthorApproval($copyedit, $request->input('note'));
            } catch (DomainException $e) {
                return redirect()->route('copyeditor.submissions.show', $submissionId)
                    ->with('error', $e->getMessage());
            }
        }

        $authorName = $copyedit?->submission?->author?->name ?? 'Dr. Nurfadillah Bahar';

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', "Permintaan persetujuan telah dikirimkan ke Penulis Korespondensi ({$authorName}). Notifikasi telah masuk ke antrean email.");
    }

    /**
     * Pengajuan mediasi ke Section Editor / EIC.
     */
    public function submitEscalation(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $copyedit = $this->findCopyedit($submissionId);

        if ($copyedit) {
            $user = Auth::user() ?? $copyedit->copyEditor;
            $reason = (string) $request->input('reason', 'Keberatan terhadap hasil suntingan teks');
            $category = $request->input('category');

            $this->copyeditorService->submitEscalation(
                $copyedit,
                $user,
                $reason,
                $category ? (string) $category : null
            );
        }

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Tiket mediasi berhasil diajukan ke Section Editor (Prof. Hamka, Ph.D.). Tim redaksi akan membantu penyelesaian catatan.');
    }

    /**
     * Penyelesaian copyediting dan serah terima ke Layout Editor.
     */
    public function completeHandoff(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $copyedit = $this->findCopyedit($submissionId);

        if ($copyedit) {
            $user = Auth::user() ?? $copyedit->copyEditor;
            $layoutEditor = User::whereHas('layoutAssignments')->first()
                ?? User::where('id', '!=', $copyedit->copy_editor_id)->first()
                ?? $user;

            try {
                $this->copyeditorService->completeHandoff(
                    $copyedit,
                    $user,
                    $layoutEditor->id,
                    $request->input('notes') ? (string) $request->input('notes') : null
                );
            } catch (DomainException $e) {
                return redirect()->route('copyeditor.submissions.show', $submissionId)
                    ->with('error', $e->getMessage());
            }
        }

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Naskah dan berkas final berhasil diserahkan ke Layout Editor. Tahap copyediting untuk naskah ini telah selesai.');
    }

    /**
     * Aksi persetujuan atau permintaan perbaikan dari Penulis atas naskah copyediting.
     */
    public function authorDecision(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $decision = (string) $request->input('decision', 'approved');
        $copyedit = $this->findCopyedit($submissionId);

        if ($copyedit) {
            $author = Auth::user() ?? $copyedit->submission?->author ?? $copyedit->copyEditor;
            $approval = $copyedit->approvals()->whereNull('invalidated_at')->first();

            if ($approval) {
                if ($decision === 'approved') {
                    $this->copyeditorService->approveByAuthor($approval, $author, $request->input('note'));
                } else {
                    $reason = (string) $request->input('reason', 'Penulis meminta perubahan hasil suntingan.');
                    $this->copyeditorService->requestChangesByAuthor($approval, $author, $reason);
                }
            }
        }

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Respon penulis atas hasil copyediting berhasil dicatat.');
    }

    /**
     * Resolusi mediasi eskalasi oleh Section Editor / EIC.
     */
    public function resolveEscalation(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $copyedit = $this->findCopyedit($submissionId);

        if ($copyedit) {
            $activeEscalation = $copyedit->escalations()->whereIn('status', ['open', 'investigating'])->first();
            if ($activeEscalation) {
                $resolution = (string) $request->input('resolution', 'Mediasi diselesaikan oleh Dewan Redaksi.');
                $this->copyeditorService->resolveEscalation($activeEscalation, $resolution);
            }
        }

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Tiket mediasi eskalasi berhasil diselesaikan.');
    }

    /**
     * Mencari model Copyedit berdasarkan submission ID atau copyedit ID.
     */
    private function findCopyedit(string $identifier): ?Copyedit
    {
        try {
            if (! Schema::hasTable('copyedits')) {
                return null;
            }

            return Copyedit::with(['submission.author', 'submission.contributors', 'copyEditor', 'outputs.file'])
                ->where('id', $identifier)
                ->orWhereHas('submission', function ($q) use ($identifier) {
                    $q->where('submission_number', $identifier)
                        ->orWhere('id', $identifier);
                })
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }
}
