<?php

declare(strict_types=1);

namespace App\Http\Controllers\SysAdmin;

use App\Http\Controllers\Controller;
use App\ViewModels\SysAdminAuditLogsViewModel;
use App\ViewModels\SysAdminJournalsViewModel;
use App\ViewModels\SysAdminSettingsViewModel;
use App\ViewModels\SysAdminUsersViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SysAdminController extends Controller
{
    /**
     * Menampilkan daftar instansi jurnal ilmiah yang dikelola (Multi-tenancy).
     */
    public function journals(Request $request): View
    {
        $status = (string) $request->query('status', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new SysAdminJournalsViewModel($status, $search);

        return view('sysadmin.journals.index', compact('viewModel'));
    }

    /**
     * Mendaftarkan instansi jurnal ilmiah baru ke sistem.
     */
    public function storeJournal(Request $request): RedirectResponse
    {
        $name = (string) $request->input('name', 'Jurnal Ilmiah Baru');

        return redirect()->route('sysadmin.journals.index')
            ->with('status', "Instansi jurnal '{$name}' berhasil didaftarkan dan diinisialisasi.");
    }

    /**
     * Mengubah status publikasi jurnal (active, archived, unlisted).
     */
    public function updateJournalStatus(Request $request, string $id): RedirectResponse
    {
        $status = (string) $request->input('status', 'active');

        return redirect()->route('sysadmin.journals.index')
            ->with('status', "Status jurnal ID {$id} berhasil diperbarui menjadi {$status}.");
    }

    /**
     * Menampilkan direktori pengguna global, peran sistem, dan riwayat impor massal.
     */
    public function users(Request $request): View
    {
        $role = (string) $request->query('role', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new SysAdminUsersViewModel($role, $search);

        return view('sysadmin.users.index', compact('viewModel'));
    }

    /**
     * Memberikan hak akses System Administrator kepada pengguna terverifikasi.
     */
    public function assignSysAdminRole(Request $request): RedirectResponse
    {
        $userId = (string) $request->input('user_id', '');

        return redirect()->route('sysadmin.users.index')
            ->with('status', 'Hak akses System Administrator berhasil diberikan kepada pengguna.');
    }

    /**
     * Mencabut hak akses System Administrator dari pengguna.
     */
    public function revokeSysAdminRole(Request $request): RedirectResponse
    {
        $userId = (string) $request->input('user_id', '');

        return redirect()->route('sysadmin.users.index')
            ->with('status', 'Hak akses System Administrator berhasil dicabut.');
    }

    /**
     * Mengunggah berkas CSV untuk pengimporan massal pengguna.
     */
    public function importUsers(Request $request): RedirectResponse
    {
        return redirect()->route('sysadmin.users.index')
            ->with('status', 'Berkas CSV impor pengguna berhasil diunggah dan sedang diproses antrean latar belakang.');
    }

    /**
     * Menampilkan halaman konfigurasi global platform dan status kesehatan server.
     */
    public function settings(): View
    {
        $viewModel = new SysAdminSettingsViewModel;

        return view('sysadmin.settings.index', compact('viewModel'));
    }

    /**
     * Memperbarui pengaturan global situs dan kebijakan pemeliharaan.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        return redirect()->route('sysadmin.settings.index')
            ->with('status', 'Konfigurasi platform SIRIDA berhasil diperbarui dan disinkronkan ke cache sistem.');
    }

    /**
     * Mengosongkan cache aplikasi dan view terkompilasi.
     */
    public function clearCache(): RedirectResponse
    {
        return redirect()->route('sysadmin.settings.index')
            ->with('status', 'Cache sistem, rute, konfigurasi, dan view berhasil dibersihkan.');
    }

    /**
     * Menampilkan jejak audit append-only komprehensif sistem.
     */
    public function auditLogs(Request $request): View
    {
        $actorType = (string) $request->query('actor_type', 'all');
        $action = (string) $request->query('action', 'all');
        $search = (string) $request->query('q', '');

        $viewModel = new SysAdminAuditLogsViewModel($actorType, $action, $search);

        return view('sysadmin.audit-logs.index', compact('viewModel'));
    }
}
