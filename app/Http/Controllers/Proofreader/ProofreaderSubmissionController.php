<?php

declare(strict_types=1);

namespace App\Http\Controllers\Proofreader;

use App\Http\Controllers\Controller;
use App\ViewModels\ProofreaderDetailViewModel;
use App\ViewModels\ProofreaderSubmissionsViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProofreaderSubmissionController extends Controller
{
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
        $viewModel = new ProofreaderDetailViewModel($id);
        $submission = $viewModel->submission();

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
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $category = (string) $request->input('category', 'typo');
        $page = $request->input('page');

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', "Catatan koreksi ({$category}".($page ? " Hal. {$page}" : '').') berhasil ditambahkan.');
    }

    /**
     * Memverifikasi resolusi perbaikan dari layout editor.
     */
    public function verifyNote(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');
        $noteId = (string) $request->input('note_id', '');

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', 'Perbaikan galai berhasil diverifikasi.');
    }

    /**
     * Memberikan persetujuan akhir (Proofreader Sign-off).
     */
    public function completeProofread(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', 'Pemeriksaan bukti baca selesai. Galai disetujui untuk tahap persetujuan akhir.');
    }

    /**
     * Mengajukan eskalasi ketidaksesuaian naskah kepada editor naskah.
     */
    public function submitEscalation(Request $request): RedirectResponse
    {
        $submissionId = (string) $request->input('submission_id', 'SUB-2026-00106');

        return redirect()->route('proofreader.submissions.show', $submissionId)
            ->with('status', 'Tiket eskalasi ketidaksesuaian naskah berhasil dikirimkan ke Editor.');
    }
}
