<?php

declare(strict_types=1);

namespace App\Http\Controllers\Copyeditor;

use App\Http\Controllers\Controller;
use App\ViewModels\CopyeditorSubmissionDetailViewModel;
use App\ViewModels\CopyeditorSubmissionsViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CopyeditorSubmissionController extends Controller
{
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
     * Aksi unggah berkas baru ke Output Set naskah.
     */
    public function uploadFile(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Berkas naskah hasil suntingan berhasil diunggah. Persetujuan penulis otomatis diperbarui untuk meninjau berkas terbaru.');
    }

    /**
     * Permohonan persetujuan penulis atas berkas hasil suntingan.
     */
    public function requestApproval(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Permintaan persetujuan telah dikirimkan ke Penulis Korespondensi (Dr. Nurfadillah Bahar). Notifikasi telah masuk ke antrean email.');
    }

    /**
     * Pengajuan mediasi ke Section Editor / EIC.
     */
    public function submitEscalation(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Tiket mediasi berhasil diajukan ke Section Editor (Prof. Hamka, Ph.D.). Tim redaksi akan membantu penyelesaian catatan.');
    }

    /**
     * Penyelesaian copyediting dan serah terima ke Layout Editor.
     */
    public function completeHandoff(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('copyeditor.submissions.show', $submissionId)
            ->with('status', 'Naskah dan berkas final berhasil diserahkan ke Layout Editor. Tahap copyediting untuk naskah ini telah selesai.');
    }
}
