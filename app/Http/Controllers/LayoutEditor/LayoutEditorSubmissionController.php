<?php

declare(strict_types=1);

namespace App\Http\Controllers\LayoutEditor;

use App\Http\Controllers\Controller;
use App\ViewModels\LayoutEditorDetailViewModel;
use App\ViewModels\LayoutEditorSubmissionsViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LayoutEditorSubmissionController extends Controller
{
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
        $viewModel = new LayoutEditorDetailViewModel($id);
        $submission = $viewModel->submission();

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
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('layouteditor.submissions.show', $submissionId)
            ->with('status', 'Berkas galai PDF berhasil diunggah dan diverifikasi checksum.');
    }

    /**
     * Aksi menandai catatan koreksi proofreading telah diselesaikan.
     */
    public function resolveNote(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('layouteditor.submissions.show', $submissionId)
            ->with('status', 'Catatan koreksi proofreading berhasil ditandai selesai.');
    }

    /**
     * Aksi mengajukan berkas galai ke putaran proofreading.
     */
    public function requestProofread(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('layouteditor.submissions.show', $submissionId)
            ->with('status', 'Berkas galai berhasil dikirim ke putaran pemeriksaan Proofreader.');
    }

    /**
     * Aksi eskalasi kendala tata letak ke Section Editor.
     */
    public function submitEscalation(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('layouteditor.submissions.show', $submissionId)
            ->with('status', 'Tiket eskalasi kendala tata letak berhasil diterbitkan ke Section Editor.');
    }

    /**
     * Aksi permohonan persetujuan final Editor in Chief untuk publikasi.
     */
    public function requestFinalApproval(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('layouteditor.submissions.show', $submissionId)
            ->with('status', 'Permohonan persetujuan akhir galai berhasil diajukan ke Editor in Chief.');
    }
}
