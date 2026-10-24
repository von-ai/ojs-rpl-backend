<?php

declare(strict_types=1);

namespace App\Http\Controllers\JournalManager;

use App\Http\Controllers\Controller;
use App\ViewModels\JournalManagerIssueDetailViewModel;
use App\ViewModels\JournalManagerIssuesViewModel;
use App\ViewModels\JournalManagerSectionsViewModel;
use App\ViewModels\JournalManagerSettingsViewModel;
use App\ViewModels\JournalManagerUsersViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalManagerController extends Controller
{
    /**
     * Menampilkan katalog seluruh edisi terbitan jurnal (Draf, Terjadwal, dan Terbit).
     */
    public function issues(Request $request): View
    {
        $tab = (string) $request->query('tab', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new JournalManagerIssuesViewModel($tab, $search);

        return view('journalmanager.issues.index', compact('viewModel'));
    }

    /**
     * Menyimpan pembuatan edisi nomor terbitan baru.
     */
    public function storeIssue(Request $request): RedirectResponse
    {
        $volume = (string) $request->input('volume', '6');
        $number = (string) $request->input('number', '2');
        $year = (string) $request->input('year', '2026');

        return redirect()->route('journalmanager.issues.index')
            ->with('status', "Edisi terbitan Vol. {$volume} No. {$number} ({$year}) berhasil dibuat sebagai draf.");
    }

    /**
     * Menampilkan ruang kerja penyusunan daftar isi edisi (Table of Contents).
     */
    public function showIssue(string $id, Request $request): View
    {
        $viewModel = new JournalManagerIssueDetailViewModel($id);
        $issue = $viewModel->issue();

        return view('journalmanager.issues.show', compact('issue', 'id'));
    }

    /**
     * Memasukkan artikel berstatus siap terbit ke dalam daftar isi edisi.
     */
    public function addArticleToIssue(string $id, Request $request): RedirectResponse
    {
        $articleTitle = (string) $request->input('article_title', 'Artikel');

        return redirect()->route('journalmanager.issues.show', $id)
            ->with('status', "Naskah '{$articleTitle}' berhasil ditambahkan ke daftar isi edisi.");
    }

    /**
     * Mempublikasikan edisi terbitan resmi secara instan.
     */
    public function publishIssue(string $id, Request $request): RedirectResponse
    {
        return redirect()->route('journalmanager.issues.show', $id)
            ->with('status', 'Edisi terbitan berkala resmi dipublikasikan ke pembaca dan katalog indeks.');
    }

    /**
     * Menetapkan edisi tertentu sebagai edisi berjalan aktif (Current Issue).
     */
    public function setCurrentIssue(string $id, Request $request): RedirectResponse
    {
        return redirect()->route('journalmanager.issues.index')
            ->with('status', 'Edisi berhasil ditetapkan sebagai Terbitan Berjalan (Current Issue) jurnal.');
    }

    /**
     * Menampilkan manajemen pengguna, tim redaksi, dan direktori penelaah (Reviewer Directory).
     */
    public function users(Request $request): View
    {
        $role = (string) $request->query('role', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new JournalManagerUsersViewModel($role, $search);

        return view('journalmanager.users.index', compact('viewModel'));
    }

    /**
     * Menetapkan peran jurnal baru kepada pengguna.
     */
    public function assignRole(Request $request): RedirectResponse
    {
        $roleName = (string) $request->input('role_code', 'Section Editor');

        return redirect()->route('journalmanager.users.index')
            ->with('status', "Peran '{$roleName}' berhasil ditetapkan kepada pengguna.");
    }

    /**
     * Memicu pengiriman tautan reset kata sandi ke email pengguna.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $email = (string) $request->input('email', 'pengguna@brida.makassar.go.id');

        return redirect()->route('journalmanager.users.index')
            ->with('status', "Tautan reset kata sandi berhasil dikirimkan ke alamat email {$email}.");
    }

    /**
     * Menampilkan daftar dan pengelolaan bidang naskah (Sections).
     */
    public function sections(Request $request): View
    {
        $viewModel = new JournalManagerSectionsViewModel;

        return view('journalmanager.sections.index', compact('viewModel'));
    }

    /**
     * Menyimpan penambahan seksi / bidang naskah baru.
     */
    public function storeSection(Request $request): RedirectResponse
    {
        $name = (string) $request->input('name', 'Bidang Riset Baru');

        return redirect()->route('journalmanager.sections.index')
            ->with('status', "Bidang naskah '{$name}' berhasil ditambahkan ke jurnal.");
    }

    /**
     * Menampilkan halaman konfigurasi identitas jurnal dan kebijakan editorial.
     */
    public function settings(Request $request): View
    {
        $viewModel = new JournalManagerSettingsViewModel;

        return view('journalmanager.settings.index', compact('viewModel'));
    }

    /**
     * Memperbarui pengaturan identitas dan kebijakan jurnal.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        return redirect()->route('journalmanager.settings.index')
            ->with('status', 'Pengaturan identitas dan kebijakan jurnal berhasil disimpan.');
    }
}
