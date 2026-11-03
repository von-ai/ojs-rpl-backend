<?php

declare(strict_types=1);

namespace App\Services\SysAdmin;

use App\Jobs\ProcessUserImport;
use App\Mail\SysAdmin\JournalStatusChangedMail;
use App\Mail\SysAdmin\SysAdminRoleAssignedMail;
use App\Mail\SysAdmin\SysAdminRoleRevokedMail;
use App\Mail\SysAdmin\UserImportCompletedMail;
use App\Models\AuditLog;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Section;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserImport;
use App\Models\UserProfile;
use App\Models\UserRole;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SysAdminService
{
    /**
     * Mendaftarkan instansi jurnal ilmiah baru ke sistem multi-tenancy SIRIDA.
     *
     * @param array{
     *     name: string,
     *     slug: string,
     *     primary_contact: string,
     *     issn?: ?string,
     *     eissn?: ?string,
     *     description?: ?string
     * } $data
     */
    public function createJournal(array $data, ?User $actor = null): Journal
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = Str::slug((string) ($data['slug'] ?? ''));
        $primaryContact = trim((string) ($data['primary_contact'] ?? ''));

        if ($name === '') {
            throw new InvalidArgumentException('Nama instansi jurnal wajib diisi.');
        }

        if ($slug === '') {
            $slug = Str::slug($name);
        }

        if ($primaryContact === '') {
            throw new InvalidArgumentException('Nama kontak utama / PIC jurnal wajib diisi.');
        }

        // Invariant Guard: Slug harus unik antar jurnal
        if (Journal::where('slug', $slug)->exists()) {
            throw new DomainException("Slug URL '{$slug}' sudah digunakan oleh instansi jurnal lain.");
        }

        $issn = ! empty($data['issn']) ? trim((string) $data['issn']) : null;
        $eissn = ! empty($data['eissn']) ? trim((string) $data['eissn']) : null;
        $description = ! empty($data['description']) ? trim((string) $data['description']) : null;

        return DB::transaction(function () use ($name, $slug, $primaryContact, $issn, $eissn, $description, $actor, $data) {
            $journal = Journal::create([
                'name' => $name,
                'slug' => $slug,
                'publisher' => $primaryContact,
                'issn' => $issn,
                'eissn' => $eissn,
                'focus_scope' => $description,
                'status' => 'active',
                'version' => 1,
            ]);

            // Jika akun PIC / Journal Manager ditentukan, kaitkan peran 'manager' pada jurnal baru
            $managerUserId = ! empty($data['manager_user_id']) ? (string) $data['manager_user_id'] : null;
            if ($managerUserId) {
                $managerUser = User::find($managerUserId);
                if ($managerUser) {
                    UserRole::firstOrCreate([
                        'user_id' => $managerUser->id,
                        'journal_id' => $journal->id,
                        'role_code' => 'manager',
                    ]);

                    AuditLog::record([
                        'journal_id' => $journal->id,
                        'actor_id' => $actor?->id,
                        'user_id' => $managerUser->id,
                        'actor_type' => 'user',
                        'effective_role' => 'System Administrator',
                        'action' => 'user.role_assigned',
                        'resource_type' => 'user_role',
                        'reason' => "Penunjukan PIC sebagai Journal Manager untuk jurnal {$journal->name} kepada {$managerUser->name}.",
                    ]);
                }
            }

            // Buat seksi bawaan (Default Section) untuk jurnal baru
            Section::create([
                'journal_id' => $journal->id,
                'name' => 'Artikel Riset',
                'slug' => 'artikel-riset',
                'description' => 'Bagian naskah artikel riset dan hasil penelitian empiris.',
                'accepting_submissions' => true,
                'position' => 1,
                'version' => 1,
            ]);

            // Catat rekam jejak audit append-only
            AuditLog::record([
                'journal_id' => $journal->id,
                'actor_id' => $actor?->id,
                'actor_type' => 'user',
                'effective_role' => 'System Administrator',
                'action' => 'journal.created',
                'resource_type' => 'journal',
                'resource_id' => $journal->id,
                'reason' => "Pendaftaran instansi jurnal ilmiah baru '{$journal->name}'.",
                'details' => [
                    'name' => $journal->name,
                    'slug' => $journal->slug,
                    'issn' => $journal->issn,
                    'eissn' => $journal->eissn,
                    'primary_contact' => $primaryContact,
                ],
            ]);

            return $journal;
        });
    }

    /**
     * Memperbarui status publikasi operasional jurnal (active, archived, unlisted).
     */
    public function updateJournalStatus(string $journalId, string $status, ?User $actor = null): Journal
    {
        $allowedStatuses = ['active', 'archived', 'unlisted'];
        if (! in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException("Status jurnal '{$status}' tidak valid. Pilihan: ".implode(', ', $allowedStatuses));
        }

        $journal = Journal::findOrFail($journalId);
        $oldStatus = (string) $journal->status;

        if ($oldStatus === $status) {
            return $journal;
        }

        return DB::transaction(function () use ($journal, $oldStatus, $status, $actor) {
            $journal->status = $status;
            $journal->save();

            // Kirim notifikasi surel jika ada kontak editorial
            $recipientEmail = $journal->editorial_email ?? (filter_var($journal->publisher, FILTER_VALIDATE_EMAIL) ? $journal->publisher : null);
            if ($recipientEmail) {
                try {
                    Mail::to($recipientEmail)->send(new JournalStatusChangedMail($journal, $oldStatus, $status, $actor));
                } catch (\Throwable) {
                    // Suppress mail error to not abort transaction in offline/test environment
                }
            }

            // Catat log audit
            AuditLog::record([
                'journal_id' => $journal->id,
                'actor_id' => $actor?->id,
                'actor_type' => 'user',
                'effective_role' => 'System Administrator',
                'action' => 'journal.status_updated',
                'resource_type' => 'journal',
                'resource_id' => $journal->id,
                'reason' => "Pembaruan status publikasi jurnal '{$journal->name}' dari {$oldStatus} ke {$status}.",
                'before_after' => [
                    'before' => ['status' => $oldStatus],
                    'after' => ['status' => $status],
                ],
            ]);

            return $journal;
        });
    }

    /**
     * Memberikan hak istimewa System Administrator kepada pengguna terverifikasi.
     */
    public function assignSysAdmin(string $userId, ?User $actor = null): UserRole
    {
        $user = User::findOrFail($userId);

        $existing = UserRole::where('user_id', $user->id)
            ->where('role_code', 'sysadmin')->whereNull('journal_id')
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $actor) {
            $userRole = UserRole::create([
                'user_id' => $user->id,
                'journal_id' => null,
                'role_code' => 'sysadmin',
                'expertise_keywords' => ['Platform Superadmin', 'Sistem Infrastruktur IT'],
            ]);

            // Kirim surel notifikasi
            if (! empty($user->email)) {
                try {
                    Mail::to($user->email)->send(new SysAdminRoleAssignedMail($user, $actor));
                } catch (\Throwable) {
                    // Suppress mail failure
                }
            }

            // Catat rekam audit
            AuditLog::record([
                'actor_id' => $actor?->id,
                'user_id' => $user->id,
                'actor_type' => 'user',
                'effective_role' => 'System Administrator',
                'action' => 'user.sysadmin_assigned',
                'resource_type' => 'user',
                'resource_id' => $user->id,
                'reason' => "Pemberian hak akses System Administrator kepada {$user->name} ({$user->email}).",
                'before_after' => [
                    'before' => ['is_sysadmin' => false],
                    'after' => ['is_sysadmin' => true],
                ],
            ]);

            return $userRole;
        });
    }

    /**
     * Mencabut hak istimewa System Administrator dari pengguna.
     */
    public function revokeSysAdmin(string $userId, ?User $actor = null): bool
    {
        return DB::transaction(function () use ($userId, $actor) {
            $user = User::findOrFail($userId);
            $admins = UserRole::where('role_code', 'sysadmin')->whereNull('journal_id')->orderBy('id')->lockForUpdate()->get();

            // Invariant Guard: Cegah pencabutan jika hanya tersisa 1 System Administrator
            $totalSysadmins = $admins->count();
            if ($totalSysadmins <= 1) {
                throw new DomainException('Tidak dapat mencabut peran System Administrator terakhir pada sistem.');
            }

            $sysadminRole = UserRole::where('user_id', $user->id)
                ->where('role_code', 'sysadmin')->whereNull('journal_id')
                ->first();

            if ($sysadminRole === null) {
                return false;
            }

            return DB::transaction(function () use ($user, $sysadminRole, $actor) {
                $sysadminRole->delete();

                // Kirim notifikasi surel pencabutan
                if (! empty($user->email)) {
                    try {
                        Mail::to($user->email)->send(new SysAdminRoleRevokedMail($user, $actor));
                    } catch (\Throwable) {
                        // Suppress mail failure
                    }
                }

                // Catat rekam jejak audit
                AuditLog::record([
                    'actor_id' => $actor?->id,
                    'user_id' => $user->id,
                    'actor_type' => 'user',
                    'effective_role' => 'System Administrator',
                    'action' => 'user.sysadmin_revoked',
                    'resource_type' => 'user',
                    'resource_id' => $user->id,
                    'reason' => "Pencabutan hak akses System Administrator dari {$user->name} ({$user->email}).",
                    'before_after' => [
                        'before' => ['is_sysadmin' => true],
                        'after' => ['is_sysadmin' => false],
                    ],
                ]);

                return true;
            });
        }, 3);
    }

    /**
     * Menetapkan peran pengguna pada jurnal tertentu.
     */
    public function assignJournalRole(
        string $userId,
        string $journalId,
        string $roleCode,
        ?string $expertiseKeywords = null,
        ?User $actor = null
    ): UserRole {
        $user = User::findOrFail($userId);
        $journal = Journal::findOrFail($journalId);

        $validRoles = [
            'manager',
            'editor_in_chief',
            'section_editor',
            'copyeditor',
            'layout_editor',
            'proofreader',
            'reviewer',
            'author',
        ];

        if (! in_array($roleCode, $validRoles, true)) {
            throw new DomainException("Kode peran '{$roleCode}' tidak valid.");
        }

        $keywords = null;
        if ($expertiseKeywords !== null && trim($expertiseKeywords) !== '') {
            $keywords = array_values(array_filter(array_map('trim', explode(',', $expertiseKeywords))));
        }

        return DB::transaction(function () use ($user, $journal, $roleCode, $keywords, $actor) {
            $userRole = UserRole::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'journal_id' => $journal->id,
                    'role_code' => $roleCode,
                ],
                [
                    'expertise_keywords' => $keywords,
                ]
            );

            // Catat log audit
            AuditLog::record([
                'journal_id' => $journal->id,
                'actor_id' => $actor?->id,
                'user_id' => $user->id,
                'actor_type' => 'user',
                'effective_role' => 'System Administrator',
                'action' => 'user.role_assigned',
                'resource_type' => 'user_role',
                'resource_id' => $userRole->id,
                'reason' => "Penetapan peran '".UserRole::roleLabel($roleCode)."' untuk jurnal {$journal->name} kepada {$user->name}.",
                'before_after' => [
                    'after' => [
                        'user_id' => $user->id,
                        'journal_id' => $journal->id,
                        'role_code' => $roleCode,
                    ],
                ],
            ]);

            return $userRole;
        });
    }

    /**
     * Mencabut peran pengguna dari jurnal tertentu.
     */
    public function revokeJournalRole(
        string $userId,
        string $journalId,
        string $roleCode,
        ?User $actor = null
    ): bool {
        $user = User::findOrFail($userId);
        $journal = Journal::findOrFail($journalId);

        $userRole = UserRole::where('user_id', $user->id)
            ->where('journal_id', $journal->id)
            ->where('role_code', $roleCode)
            ->first();

        if (! $userRole) {
            return false;
        }

        return DB::transaction(function () use ($user, $journal, $roleCode, $userRole, $actor) {
            $userRole->delete();

            // Catat log audit
            AuditLog::record([
                'journal_id' => $journal->id,
                'actor_id' => $actor?->id,
                'user_id' => $user->id,
                'actor_type' => 'user',
                'effective_role' => 'System Administrator',
                'action' => 'user.role_revoked',
                'resource_type' => 'user_role',
                'resource_id' => $userRole->id,
                'reason' => "Pencabutan peran '".UserRole::roleLabel($roleCode)."' dari jurnal {$journal->name} untuk {$user->name}.",
                'before_after' => [
                    'before' => [
                        'user_id' => $user->id,
                        'journal_id' => $journal->id,
                        'role_code' => $roleCode,
                    ],
                ],
            ]);

            return true;
        });
    }

    /**
     * Mengimpor pengguna secara massal dari berkas CSV dengan validasi dan alokasi peran.
     */
    public function importUsersFromCsv(UploadedFile|string $file, ?string $journalId = null, bool $sendInvitation = false, ?User $actor = null): UserImport
    {
        abort_unless($actor?->isSysAdmin(), 403);
        if ($journalId) {
            Journal::findOrFail($journalId);
        }
        $name = $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file);
        $content = file_get_contents($file instanceof UploadedFile ? $file->getRealPath() : $file);
        if ($content === false || strlen($content) > 512000) {
            throw new DomainException('Berkas impor maksimal 500 KB.');
        }
        $path = 'user-imports/'.Str::ulid().'.csv';
        Storage::disk('local')->put($path, $content);

        return DB::transaction(function () use ($name, $path, $content, $journalId, $sendInvitation, $actor) {
            $entry = FileEntry::create(['storage_disk' => 'local', 'storage_path' => $path, 'original_name' => $name, 'mime_type' => 'text/csv', 'byte_size' => strlen($content), 'checksum_sha256' => hash('sha256', $content), 'virus_scan_status' => 'pending', 'created_by' => $actor->id]);
            $import = UserImport::create(['journal_id' => $journalId, 'file_name' => $name, 'file_id' => $entry->id, 'send_invitation_email' => $sendInvitation, 'total_rows' => 0, 'processed_rows' => 0, 'failed_rows' => 0, 'status' => 'queued', 'imported_by' => $actor->id]);
            ProcessUserImport::dispatch($import->id);

            return $import;
        });
    }

    public function processUsersFromCsv(
        UploadedFile|string $file,
        ?string $journalId = null,
        bool $sendInvitation = false,
        ?User $actor = null,
        ?UserImport $existing = null
    ): UserImport {
        $filePath = is_string($file) ? $file : $file->getRealPath();
        $fileName = is_string($file) ? basename($file) : $file->getClientOriginalName();

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new InvalidArgumentException("Berkas CSV '{$fileName}' tidak dapat dibaca atau tidak ditemukan.");
        }

        $userImport = $existing ?? UserImport::create([
            'journal_id' => $journalId,
            'file_name' => $fileName,
            'total_rows' => 0,
            'processed_rows' => 0,
            'failed_rows' => 0,
            'status' => 'processing',
            'error_log' => [],
            'imported_by' => $actor?->id,
        ]);

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $userImport->update(['status' => 'failed', 'error_log' => ['Gagal membuka stream berkas CSV.']]);

            return $userImport;
        }

        $header = fgetcsv($handle);
        if ($header === false || empty($header)) {
            fclose($handle);
            $userImport->update(['status' => 'failed', 'error_log' => ['Berkas CSV kosong atau tidak memiliki baris tajuk (header).']]);

            return $userImport;
        }

        // Normalisasi nama kolom header
        $headerMap = [];
        foreach ($header as $index => $colName) {
            $cleaned = strtolower(trim((string) $colName));
            $headerMap[$cleaned] = $index;
        }

        $nameIdx = $headerMap['name'] ?? ($headerMap['nama'] ?? ($headerMap['full_name'] ?? null));
        $emailIdx = $headerMap['email'] ?? ($headerMap['surel'] ?? null);
        $affiliationIdx = $headerMap['affiliation'] ?? ($headerMap['afiliasi'] ?? ($headerMap['instansi'] ?? null));
        $roleIdx = $headerMap['role'] ?? ($headerMap['peran'] ?? ($headerMap['roles'] ?? null));

        if ($emailIdx === null) {
            fclose($handle);
            $userImport->update([
                'status' => 'failed',
                'error_log' => ['Berkas CSV harus memiliki kolom tajuk "email" atau "surel".'],
            ]);

            return $userImport;
        }

        $totalRows = 0;
        $processedRows = 0;
        $failedRows = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Abaikan baris kosong
                if (empty($row) || (count($row) === 1 && trim((string) $row[0]) === '')) {
                    continue;
                }

                $totalRows++;
                if ($totalRows > 200) {
                    throw new DomainException('Satu impor dibatasi 200 pengguna.');
                }
                $rowNumber = $totalRows + 1; // baris ke-1 adalah header

                $email = strtolower(trim((string) ($row[$emailIdx] ?? '')));
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $failedRows++;
                    $errors[] = "Baris {$rowNumber}: Alamat email '{$email}' tidak valid.";

                    continue;
                }

                $name = $nameIdx !== null && isset($row[$nameIdx]) && trim((string) $row[$nameIdx]) !== ''
                    ? trim((string) $row[$nameIdx])
                    : explode('@', $email)[0];

                $affiliation = $affiliationIdx !== null && isset($row[$affiliationIdx])
                    ? trim((string) $row[$affiliationIdx])
                    : null;

                $roleCode = $roleIdx !== null && isset($row[$roleIdx]) && trim((string) $row[$roleIdx]) !== ''
                    ? Str::slug(trim((string) $row[$roleIdx]), '_')
                    : 'author';

                if (! in_array($roleCode, ['manager', 'editor_in_chief', 'section_editor', 'copyeditor', 'layout_editor', 'proofreader', 'reviewer', 'author'], true)) {
                    $failedRows++;
                    $errors[] = "Baris {$rowNumber}: peran jurnal tidak valid.";

                    continue;
                }

                // Buat atau temukan user
                $user = User::where('email', $email)->first();
                if ($user === null) {
                    $username = Str::slug(explode('@', $email)[0], '.');
                    if (User::where('username', $username)->exists()) {
                        $username .= '.'.Str::lower(Str::random(4));
                    }

                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'username' => $username,
                        'password' => Hash::make(Str::random(16)),
                        'status' => 'active',
                        'email_verified_at' => now(),
                        'version' => 1,
                    ]);
                }

                // Perbarui afiliasi jika ada
                if ($affiliation !== null && $affiliation !== '') {
                    UserProfile::updateOrCreate(
                        ['user_id' => $user->id],
                        ['affiliation' => $affiliation]
                    );
                }

                // Tetapkan peran pada jurnal jika jurnal ditentukan
                if ($journalId !== null) {
                    UserRole::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'journal_id' => $journalId,
                            'role_code' => $roleCode,
                        ],
                        [
                            'expertise_keywords' => ['Diimpor via CSV'],
                        ]
                    );
                }

                if ($sendInvitation) {
                    Password::sendResetLink(['email' => $user->email]);
                }
                $processedRows++;
            }

            fclose($handle);

            $userImport->update([
                'total_rows' => $totalRows,
                'processed_rows' => $processedRows,
                'failed_rows' => $failedRows,
                'status' => $failedRows === $totalRows && $totalRows > 0 ? 'failed' : 'completed',
                'error_log' => $errors,
            ]);

            // Kirim email laporan ke admin
            if ($actor && ! empty($actor->email)) {
                try {
                    Mail::to($actor->email)->send(new UserImportCompletedMail($userImport));
                } catch (\Throwable) {
                    // Suppress mail error
                }
            }

            // Catat log audit
            AuditLog::record([
                'journal_id' => $journalId,
                'actor_id' => $actor?->id,
                'actor_type' => 'user',
                'effective_role' => 'System Administrator',
                'action' => 'user_imports.processed',
                'resource_type' => 'user_imports',
                'resource_id' => $userImport->id,
                'reason' => "Eksekusi impor massal pengguna dari berkas '{$fileName}'. Hasil: {$processedRows} berhasil, {$failedRows} gagal.",
                'details' => [
                    'file_name' => $fileName,
                    'total_rows' => $totalRows,
                    'processed_rows' => $processedRows,
                    'failed_rows' => $failedRows,
                ],
            ]);

            DB::commit();

            return $userImport;
        } catch (\Throwable $e) {
            DB::rollBack();
            if (is_resource($handle)) {
                fclose($handle);
            }
            $userImport->update([
                'status' => 'failed',
                'error_log' => array_merge($errors, [$e->getMessage()]),
            ]);
            throw $e;
        }
    }

    /**
     * Memperbarui konfigurasi global situs SIRIDA dan menyinkronkan ke cache platform.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updatePlatformSettings(array $data, ?User $actor = null): array
    {
        $currentSettings = SystemSetting::getAllSettings();
        $updatedSettings = [];

        DB::transaction(function () use ($data, $currentSettings, &$updatedSettings, $actor) {
            $diffBefore = [];
            $diffAfter = [];

            if (isset($data['site_name'])) {
                $siteName = trim((string) $data['site_name']);
                if ($siteName !== '') {
                    SystemSetting::setValue('site_name', $siteName, 'string', 'general');
                    $diffBefore['site_name'] = $currentSettings['site_name'];
                    $diffAfter['site_name'] = $siteName;
                }
            }

            if (isset($data['site_description'])) {
                $siteDesc = trim((string) $data['site_description']);
                SystemSetting::setValue('site_description', $siteDesc, 'string', 'general');
                $diffBefore['site_description'] = $currentSettings['site_description'];
                $diffAfter['site_description'] = $siteDesc;
            }

            if (array_key_exists('maintenance_mode', $data)) {
                $maintenanceMode = (bool) $data['maintenance_mode'];
                SystemSetting::setValue('maintenance_mode', $maintenanceMode, 'boolean', 'maintenance');
                $diffBefore['maintenance_mode'] = $currentSettings['maintenance_mode'];
                $diffAfter['maintenance_mode'] = $maintenanceMode;
            }

            if (isset($data['maintenance_message'])) {
                $maintenanceMsg = trim((string) $data['maintenance_message']);
                SystemSetting::setValue('maintenance_message', $maintenanceMsg, 'string', 'maintenance');
                $diffBefore['maintenance_message'] = $currentSettings['maintenance_message'];
                $diffAfter['maintenance_message'] = $maintenanceMsg;
            }

            if (isset($data['max_upload_size_mb'])) {
                $maxUpload = max(5, min(100, (int) $data['max_upload_size_mb']));
                SystemSetting::setValue('max_upload_size_mb', $maxUpload, 'integer', 'storage');
                $diffBefore['max_upload_size_mb'] = $currentSettings['max_upload_size_mb'];
                $diffAfter['max_upload_size_mb'] = $maxUpload;
            }

            if (isset($data['allowed_file_extensions'])) {
                $exts = implode(', ', array_values(array_intersect(['pdf', 'doc', 'docx', 'odt', 'jpg', 'jpeg', 'png', 'csv', 'html'], array_map(fn ($value) => strtolower(trim($value)), explode(',', (string) $data['allowed_file_extensions'])))));
                if ($exts === '') {
                    throw new DomainException('Pilih sedikitnya satu ekstensi dokumen yang diizinkan.');
                }
                SystemSetting::setValue('allowed_file_extensions', $exts, 'string', 'storage');
                $diffBefore['allowed_file_extensions'] = $currentSettings['allowed_file_extensions'];
                $diffAfter['allowed_file_extensions'] = $exts;
            }

            if (isset($data['session_lifetime_minutes'])) {
                $sessionLifetime = max(15, min(120, (int) $data['session_lifetime_minutes']));
                SystemSetting::setValue('session_lifetime_minutes', $sessionLifetime, 'integer', 'security');
                $diffBefore['session_lifetime_minutes'] = $currentSettings['session_lifetime_minutes'];
                $diffAfter['session_lifetime_minutes'] = $sessionLifetime;
            }

            // Catat ke audit log bila ada mutasi konfigurasi
            if (! empty($diffAfter)) {
                AuditLog::record([
                    'actor_id' => $actor?->id,
                    'actor_type' => 'user',
                    'effective_role' => 'System Administrator',
                    'action' => 'system_settings.updated',
                    'resource_type' => 'system_settings',
                    'resource_id' => 'site_configuration',
                    'reason' => 'Pembaruan konfigurasi global platform SIRIDA melalui portal System Administrator.',
                    'before_after' => [
                        'before' => $diffBefore,
                        'after' => $diffAfter,
                    ],
                ]);
            }

            $updatedSettings = SystemSetting::getAllSettings();
        });

        return $updatedSettings;
    }

    /**
     * Membersihkan seluruh cache aplikasi, rute, konfigurasi, dan view yang terkompilasi.
     *
     * @return array{status: string, message: string}
     */
    public function clearSystemCache(?User $actor = null): array
    {
        try {
            // Limiter and session keys must survive clearing compiled application caches.
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
        } catch (\Throwable) {
            // Abaikan kesalahan minor saat di lingkungan tes terisolasi
        }

        // Catat jejak audit
        AuditLog::record([
            'actor_id' => $actor?->id,
            'actor_type' => 'user',
            'effective_role' => 'System Administrator',
            'action' => 'system.cache_purged',
            'resource_type' => 'cache',
            'resource_id' => 'app_system_cache',
            'reason' => 'Pembersihan menyeluruh cache sistem, rute, konfigurasi, dan view terkompilasi.',
            'details' => [
                'flushed_at' => now()->toIso8601String(),
            ],
        ]);

        return [
            'status' => 'success',
            'message' => 'Cache sistem, rute, konfigurasi, dan view berhasil dibersihkan.',
        ];
    }
}
