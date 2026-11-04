<?php

declare(strict_types=1);

namespace App\Http\Controllers\SysAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use App\Services\SysAdmin\SysAdminService;
use App\ViewModels\SysAdminAuditLogsViewModel;
use App\ViewModels\SysAdminJournalsViewModel;
use App\ViewModels\SysAdminSettingsViewModel;
use App\ViewModels\SysAdminUsersViewModel;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SysAdminController extends Controller
{
    public function __construct(
        protected SysAdminService $sysAdminService
    ) {}

    /**
     * Resolusi aktor pelaksana (User yang login atau SysAdmin aktif).
     */
    protected function resolveActor(): User
    {
        $user = Auth::user();
        abort_unless($user?->isSysAdmin(), 403);

        return $user;
    }

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
        $actor = $this->resolveActor();
        $name = trim((string) $request->input('name', 'Jurnal Ilmiah Baru'));
        $slug = trim((string) $request->input('slug', Str::slug($name)));
        $primaryContact = trim((string) $request->input('primary_contact', $actor->name));
        $issn = $request->input('issn');
        $eissn = $request->input('eissn');
        $description = $request->input('description');
        $managerUserId = $request->input('manager_user_id');

        try {
            $journal = $this->sysAdminService->createJournal([
                'name' => $name,
                'slug' => $slug,
                'primary_contact' => $primaryContact,
                'issn' => $issn ? (string) $issn : null,
                'eissn' => $eissn ? (string) $eissn : null,
                'description' => $description ? (string) $description : null,
                'manager_user_id' => $managerUserId ? (string) $managerUserId : null,
            ], $actor);

            return redirect()->route('sysadmin.journals.index')
                ->with('status', "Instansi jurnal '{$journal->name}' berhasil didaftarkan dan diinisialisasi.");
        } catch (DomainException|\InvalidArgumentException $e) {
            return redirect()->route('sysadmin.journals.index')
                ->withErrors(['operation' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('sysadmin.journals.index')
                ->withErrors(['operation' => 'Operasi gagal diproses. Silakan coba kembali atau hubungi pengelola.']);
        }
    }

    /**
     * Mengubah status publikasi jurnal (active, archived, unlisted).
     */
    public function updateJournalStatus(Request $request, string $id): RedirectResponse
    {
        $actor = $this->resolveActor();
        $status = (string) $request->input('status', 'active');

        try {
            $journal = $this->sysAdminService->updateJournalStatus($id, $status, $actor);

            return redirect()->route('sysadmin.journals.index')
                ->with('status', "Status jurnal ID {$id} berhasil diperbarui menjadi {$status}.");
        } catch (DomainException|\InvalidArgumentException $e) {
            return redirect()->route('sysadmin.journals.index')
                ->withErrors(['operation' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('sysadmin.journals.index')
                ->withErrors(['operation' => 'Operasi gagal diproses. Silakan coba kembali atau hubungi pengelola.']);
        }
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
        $actor = $this->resolveActor();
        $userId = (string) $request->input('user_id', '');

        if ($userId === '') {
            return redirect()->route('sysadmin.users.index')
                ->with('status', 'ID Pengguna wajib diisi.');
        }

        try {
            $this->sysAdminService->assignSysAdmin($userId, $actor);

            return redirect()->route('sysadmin.users.index')
                ->with('status', 'Hak akses System Administrator berhasil diberikan kepada pengguna.');
        } catch (ModelNotFoundException) {
            return redirect()->route('sysadmin.users.index')
                ->withErrors(['user_id' => 'Pengguna tidak ditemukan.']);
        } catch (DomainException $e) {
            return redirect()->route('sysadmin.users.index')
                ->withErrors(['operation' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('sysadmin.users.index')
                ->withErrors(['operation' => 'Operasi gagal diproses. Silakan coba kembali atau hubungi pengelola.']);
        }
    }

    /**
     * Mencabut hak akses System Administrator dari pengguna.
     */
    public function revokeSysAdminRole(Request $request): RedirectResponse
    {
        $actor = $this->resolveActor();
        $userId = (string) $request->input('user_id', '');

        if ($userId === '') {
            return redirect()->route('sysadmin.users.index')
                ->with('status', 'ID Pengguna wajib diisi.');
        }

        try {
            $this->sysAdminService->revokeSysAdmin($userId, $actor);

            return redirect()->route('sysadmin.users.index')
                ->with('status', 'Hak akses System Administrator berhasil dicabut.');
        } catch (ModelNotFoundException) {
            return redirect()->route('sysadmin.users.index')
                ->withErrors(['user_id' => 'Pengguna tidak ditemukan.']);
        } catch (DomainException $e) {
            return redirect()->route('sysadmin.users.index')
                ->withErrors(['operation' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('sysadmin.users.index')
                ->withErrors(['operation' => 'Operasi gagal diproses. Silakan coba kembali atau hubungi pengelola.']);
        }
    }

    /**
     * Menetapkan peran pengguna pada jurnal tertentu dari dashboard SysAdmin.
     */
    public function assignJournalRole(Request $request): RedirectResponse
    {
        $actor = $this->resolveActor();
        $userId = (string) $request->input('user_id', '');
        $journalId = (string) $request->input('journal_id', '');
        $roleCode = (string) $request->input('role_code', 'manager');
        $expertise = $request->input('expertise_keywords');

        try {
            $this->sysAdminService->assignJournalRole(
                $userId,
                $journalId,
                $roleCode,
                $expertise ? (string) $expertise : null,
                $actor
            );

            $roleLabel = UserRole::roleLabel($roleCode);

            return redirect()->route('sysadmin.users.index')
                ->with('status', "Peran '{$roleLabel}' pada jurnal berhasil ditetapkan kepada pengguna.");
        } catch (DomainException $e) {
            return redirect()->route('sysadmin.users.index')
                ->withErrors(['role' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('sysadmin.users.index')
                ->withErrors(['role' => 'Gagal menetapkan peran jurnal. Silakan coba kembali.']);
        }
    }

    /**
     * Mencabut peran pengguna dari jurnal tertentu.
     */
    public function revokeJournalRole(Request $request): RedirectResponse
    {
        $actor = $this->resolveActor();
        $userId = (string) $request->input('user_id', '');
        $journalId = (string) $request->input('journal_id', '');
        $roleCode = (string) $request->input('role_code', '');

        try {
            $this->sysAdminService->revokeJournalRole($userId, $journalId, $roleCode, $actor);

            $roleLabel = UserRole::roleLabel($roleCode);

            return redirect()->route('sysadmin.users.index')
                ->with('status', "Peran '{$roleLabel}' pada jurnal berhasil dicabut.");
        } catch (DomainException $e) {
            return redirect()->route('sysadmin.users.index')
                ->withErrors(['operation' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('sysadmin.users.index')
                ->withErrors(['operation' => 'Operasi gagal diproses. Silakan coba kembali atau hubungi pengelola.']);
        }
    }

    /**
     * Mengunggah berkas CSV untuk pengimporan massal pengguna.
     */
    public function importUsers(Request $request): RedirectResponse
    {
        $actor = $this->resolveActor();
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:500'], 'journal_id' => ['nullable', 'exists:journals,id']]);
        $journalId = $request->input('journal_id');
        $sendInvitation = $request->boolean('send_invitation_email');

        if ($request->hasFile('file')) {
            try {
                $import = $this->sysAdminService->importUsersFromCsv(
                    $request->file('file'),
                    $journalId ? (string) $journalId : null,
                    $sendInvitation,
                    $actor
                );

                return redirect()->route('sysadmin.users.index')
                    ->with('status', "Berkas CSV impor pengguna '{$import->file_name}' masuk antrean pemindaian dan impor.");
            } catch (\Throwable $e) {
                report($e);

                return redirect()->route('sysadmin.users.index')
                    ->withErrors(['operation' => 'Operasi gagal diproses. Silakan coba kembali atau hubungi pengelola.']);
            }
        }

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
        $actor = $this->resolveActor();
        $data = $request->validate(['site_name' => ['sometimes', 'required', 'string', 'max:191'], 'site_description' => ['nullable', 'string', 'max:4000'], 'maintenance_message' => ['nullable', 'string', 'max:4000'], 'max_upload_size_mb' => ['sometimes', 'required', 'integer', 'min:5', 'max:100'], 'allowed_file_extensions' => ['sometimes', 'required', 'string', 'max:200'], 'session_lifetime_minutes' => ['sometimes', 'required', 'integer', 'min:15', 'max:120']]);

        // Handle checkbox boolean
        if ($request->has('maintenance_mode')) {
            $data['maintenance_mode'] = $request->boolean('maintenance_mode');
        }

        try {
            $this->sysAdminService->updatePlatformSettings($data, $actor);

            return redirect()->route('sysadmin.settings.index')
                ->with('status', 'Konfigurasi platform SIRIDA berhasil diperbarui dan disinkronkan ke cache sistem.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('sysadmin.settings.index')
                ->withErrors(['operation' => 'Operasi gagal diproses. Silakan coba kembali atau hubungi pengelola.']);
        }
    }

    /**
     * Mengosongkan cache aplikasi dan view terkompilasi.
     */
    public function clearCache(): RedirectResponse
    {
        $actor = $this->resolveActor();

        try {
            $this->sysAdminService->clearSystemCache($actor);

            return redirect()->route('sysadmin.settings.index')
                ->with('status', 'Cache sistem, rute, konfigurasi, dan view berhasil dibersihkan.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('sysadmin.settings.index')
                ->withErrors(['operation' => 'Operasi gagal diproses. Silakan coba kembali atau hubungi pengelola.']);
        }
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
