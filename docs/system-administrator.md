# Spesifikasi Teknis & Desain Arsitektur Backend: System Administrator

Dokumen ini memuat spesifikasi teknis resmi, kontrak data, aturan bisnis domain (*domain invariants*), dan arsitektur backend untuk peran **System Administrator** (SysAdmin) pada platform OJS BRIDA Kota Makassar (SIRIDA). Dokumen ini menjadi rujukan baku bagi pengembang backend agar implementasi logika bisnis selaras dengan antarmuka pengguna tanpa pengerjaan ulang (*rework*).

---

## 1. Ikhtisar Peran & Hak Istimewa

**System Administrator** adalah pemegang otoritas teknis tertinggi pada level platform global (*global system-level authority*):
* Hak akses diberikan melalui tabel `system_role_grants` dengan `role_code = 'sysadmin'`.
* Beroperasi secara lintas-jurnal (*multi-tenant global governance*), berbeda dengan peran `Journal Manager` yang dibatasi oleh keanggotaan jurnal tunggal (`journal_memberships`).
* Bertanggung jawab atas ketersediaan platform, tata kelola instansi jurnal, pengawasan akun pengguna global, pengaturan situs, jejak audit keamanan (*audit trail*), serta pemeliharaan server.

---

## 2. Pemetaan Skema Database (Mengacu pada `docs/erd.md`)

### A. Tabel 5: `system_role_grants`
Menyimpan pemberian peran level sistem global kepada pengguna terverifikasi.
* `id`: `CHAR(26)` NOT NULL — Primary Key berbasis ULID.
* `user_id`: `CHAR(26)` NOT NULL — Foreign Key ke `users.id`.
* `role_code`: `VARCHAR(191)` NOT NULL — Kode peran sistem (`sysadmin`).
* `revoked_at`: `DATETIME(6)` NULL — Waktu pencabutan hak akses sistem.
* `live_slot`: `TINYINT` GENERATED ALWAYS AS (`IF(revoked_at IS NULL, 1, NULL)`) STORED.
* **Invarian:** `UNIQUE KEY uq_sysrole_live (user_id, role_code, live_slot)`.

### B. Tabel 9: `journals`
Menyimpan identitas entitas jurnal ilmiah yang beroperasi pada platform.
* `id`: `CHAR(26)` NOT NULL — Primary Key berbasis ULID.
* `slug`: `VARCHAR(191)` NOT NULL — URL identifier unik untuk jurnal (misal: `jtiik`, `sirida-inovasi`).
* `name`: `TEXT` NOT NULL — Nama lengkap jurnal ilmiah.
* `issn`: `TEXT` NULL — Nomor cetak ISSN.
* `eissn`: `TEXT` NULL — Nomor elektronik E-ISSN.
* `status`: `VARCHAR(191)` NOT NULL — Status publikasi jurnal (`active`, `archived`, `unlisted`).
* `current_issue_id`: `CHAR(26)` NULL — Foreign Key ke `issues.id` (edisi terbitan aktif berjalan).
* `version`: `INT` NOT NULL DEFAULT 1 — Kontrol konkurensi optimistik.
* `created_at`: `DATETIME(6)` NOT NULL (UTC).
* `updated_at`: `DATETIME(6)` NOT NULL (UTC).
* **Invarian:** `UNIQUE KEY uq_journals_slug (slug)`.

### C. Tabel 54: `user_imports`
Mencatat proses pengimporan massal pengguna ke platform melalui berkas CSV.
* `id`: `CHAR(26)` NOT NULL — Primary Key berbasis ULID.
* `journal_id`: `CHAR(26)` NOT NULL — Foreign Key ke `journals.id`.
* `file_id`: `CHAR(26)` NOT NULL — Foreign Key ke `files.id` (berkas CSV sumber).
* `created_by`: `CHAR(26)` NOT NULL — Foreign Key ke `users.id` (administrator pengunggah).
* `default_roles`: `JSON` NOT NULL — Daftar peran awal yang diberikan.
* `send_invitation_email`: `BOOLEAN` NOT NULL DEFAULT FALSE — Opsi kirim email aktivasi.
* `status`: `VARCHAR(191)` NOT NULL — `pending`, `processing`, `completed`, `failed`.
* `total_rows`: `INT` NOT NULL DEFAULT 0 — Total target baris dalam CSV.
* `processed_rows`: `INT` NOT NULL DEFAULT 0 — Jumlah baris sukses diproses.
* `failed_rows`: `INT` NOT NULL DEFAULT 0 — Jumlah baris gagal.
* `errors`: `JSON` NULL — Rincian error validasi per baris CSV.
* `completed_at`: `DATETIME(6)` NULL — Waktu penyelesaian impor.

### D. Tabel 55: `system_settings`
Tabel konfigurasi tingkat global platform jurnal SIRIDA (key-value store terstruktur).
* `key`: `VARCHAR(191)` NOT NULL — Primary Key identifikasi pengaturan.
* `value`: `JSON` NOT NULL — Nilai konfigurasi terstruktur.
* `updated_by`: `CHAR(26)` NOT NULL — Foreign Key ke `users.id`.
* `created_at`: `DATETIME(6)` NOT NULL (UTC).
* `updated_at`: `DATETIME(6)` NOT NULL (UTC).
* **Kunci Pengaturan Baku:**
  - `site_name`: Nama resmi portal (default: `SIRIDA — Jurnal BRIDA Kota Makassar`).
  - `site_description`: Deskripsi singkat portal repositori dan penerbitan ilmiah.
  - `maintenance_mode`: Boolean flag mode pemeliharaan (`true`/`false`).
  - `maintenance_message`: Pesan pemeliharaan saat sistem dikunci sementara.
  - `max_upload_size_mb`: Batas maksimum unggahan berkas per request (default: `32`).
  - `allowed_file_extensions`: Array ekstensi berkas yang diizinkan (`pdf`, `docx`, `odt`, `jpg`, `png`).
  - `session_lifetime_minutes`: Batas durasi sesi pengguna aktif (default: `120`).

### E. Tabel 57: `audit_logs`
Catatan jejak audit komprehensif atas seluruh tindakan pengguna dan proses sistem (*append-only*).
* `id`: `CHAR(26)` NOT NULL — Primary Key berbasis ULID.
* `journal_id`: `CHAR(26)` NULL — FK ke `journals.id` (NULL jika mutasi level sistem).
* `actor_id`: `CHAR(26)` NULL — FK ke `users.id` (Wajib terisi jika `actor_type = 'user'`).
* `actor_type`: `VARCHAR(191)` NOT NULL — `user` atau `system`.
* `system_actor`: `VARCHAR(191)` NULL — Nama komponen latar belakang (misal: `cron_worker`, `malware_scanner`).
* `effective_role`: `TEXT` NULL — Peran aktif pengguna saat melakukan tindakan.
* `action`: `TEXT` NOT NULL — Nama aksi terstruktur (misal: `journal.created`, `system_settings.updated`, `user.sysadmin_granted`).
* `resource_type`: `VARCHAR(191)` NOT NULL — Tipe entitas yang dimutasi (`journal`, `user`, `setting`).
* `resource_id`: `TEXT` NOT NULL — ID entitas target.
* `before_after`: `JSON` NULL — Diff data sebelum dan sesudah mutasi.
* `reason`: `TEXT` NULL — Justifikasi administratif atas tindakan.
* `request_id`: `TEXT` NULL — Correlation ID request HTTP untuk tracing log.
* `occurred_at`: `DATETIME(6)` NOT NULL — Waktu riil kejadian (UTC).

---

## 3. Aturan Bisnis & Invarian Domain

1. **Aturan Tunggal SysAdmin Terakhir (*Last SysAdmin Guard*):**
   Sistem menolak mutasi pencabutan peran atau penonaktifan akun pengguna jika pengguna tersebut adalah satu-satunya System Administrator aktif yang tersisa pada platform.
2. **Keunikan Global Slug Jurnal (*Unique Slug Enforcement*):**
   Slug instansi jurnal ilmiah harus berupa format *URL-safe alphanumeric* dan unik secara global di seluruh sistem.
3. **Integritas Jejak Audit (*Append-Only Audit Trail*):**
   Tabel `audit_logs` tidak menyediakan perintah `UPDATE` maupun `DELETE`. Setiap perubahan konfigurasi sistem (`system_settings`) atau perubahan hak istimewa pengguna wajib menghasilkan rekaman audit baru yang mencatat ID aktor dan diff data `before_after`.
4. **Isolasi Mode Pemeliharaan (*Maintenance Mode Protection*):**
   Saat `maintenance_mode = true`, seluruh request dari pengguna publik, penulis, reviewer, dan editor diblokir dengan respons HTTP 503 Service Unavailable, kecuali pengguna dengan peran `sysadmin` aktif yang tetap diizinkan masuk ke panel administratif.

---

## 4. Pemetaan Rute Web & Kontrak API

Prefix rute: `/sysadmin`

| Metode | URL | Nama Rute | Action Controller | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/sysadmin` | `sysadmin.dashboard` | Redirect | Dialihkan ke `sysadmin.journals.index` |
| `GET` | `/sysadmin/journals` | `sysadmin.journals.index` | `SysAdminController@journals` | Daftar seluruh instansi jurnal ilmiah |
| `POST` | `/sysadmin/journals` | `sysadmin.journals.store` | `SysAdminController@storeJournal` | Mendaftarkan instansi jurnal ilmiah baru |
| `POST` | `/sysadmin/journals/{id}/status` | `sysadmin.journals.update-status` | `SysAdminController@updateJournalStatus` | Mengubah status jurnal (active/archived/unlisted) |
| `GET` | `/sysadmin/users` | `sysadmin.users.index` | `SysAdminController@users` | Direktori pengguna global & peran sistem |
| `POST` | `/sysadmin/users/assign-sysadmin` | `sysadmin.users.assign-sysadmin` | `SysAdminController@assignSysAdminRole` | Memberikan hak akses System Administrator |
| `POST` | `/sysadmin/users/revoke-sysadmin` | `sysadmin.users.revoke-sysadmin` | `SysAdminController@revokeSysAdminRole` | Mencabut hak akses System Administrator |
| `POST` | `/sysadmin/users/import` | `sysadmin.users.import` | `SysAdminController@importUsers` | Mengunggah dan memproses impor massal CSV |
| `GET` | `/sysadmin/settings` | `sysadmin.settings.index` | `SysAdminController@settings` | Pengaturan platform & status kesehatan server |
| `POST` | `/sysadmin/settings` | `sysadmin.settings.update` | `SysAdminController@updateSettings` | Memperbarui pengaturan situs dan pemeliharaan |
| `POST` | `/sysadmin/settings/clear-cache` | `sysadmin.settings.clear-cache` | `SysAdminController@clearCache` | Mengosongkan cache aplikasi dan view terkompilasi |
| `GET` | `/sysadmin/audit-logs` | `sysadmin.audit-logs.index` | `SysAdminController@auditLogs` | Penelusuran jejak audit komprehensif sistem |

---

## 5. Kontrak Data ViewModel (Tanpa Hardcode)

### A. Kontrak Data Manajemen Jurnal (`SysAdminJournalsViewModel`)
```php
[
    'userProfile' => [
        'name' => 'Fahri Azis, S.T.',
        'role_label' => 'System Administrator',
        'initials' => 'FA',
    ],
    'metrics' => [
        ['title' => 'Total Jurnal Ilmiah', 'value' => 4, 'badge_text' => '4 Instansi', 'color_class' => string],
        ['title' => 'Jurnal Aktif', 'value' => 3, 'badge_text' => 'Aktif Publikasi', 'color_class' => string],
        ['title' => 'Edisi Terbit Berjalan', 'value' => 6, 'badge_text' => 'Volume 2026', 'color_class' => string],
        ['title' => 'Jurnal Diarsipkan', 'value' => 1, 'badge_text' => 'Arsip Historis', 'color_class' => string],
    ],
    'journals' => [
        [
            'id' => string,             // 'JOURNAL-01JARK-01'
            'slug' => string,           // 'jtiik-makassar'
            'name' => string,           // 'Jurnal Teknologi Informasi dan Inovasi Perkotaan'
            'issn' => ?string,          // '2655-9012'
            'eissn' => ?string,         // '2722-4567'
            'status' => string,         // 'active' | 'archived' | 'unlisted'
            'status_label' => string,
            'status_badge_class' => string,
            'total_submissions' => int,
            'current_issue_title' => ?string,
            'url_public' => string,
        ],
    ],
]
```

### B. Kontrak Data Pengguna Sistem (`SysAdminUsersViewModel`)
```php
[
    'metrics' => [
        ['title' => 'Total Akun Pengguna', 'value' => 142, 'badge_text' => 'Terverifikasi', 'color_class' => string],
        ['title' => 'System Administrator', 'value' => 2, 'badge_text' => 'Superadmin', 'color_class' => string],
        ['title' => 'Dewan Redaksi Aktif', 'value' => 18, 'badge_text' => 'EIC / Editor', 'color_class' => string],
        ['title' => 'Reviewer Terdaftar', 'value' => 64, 'badge_text' => 'Mitra Bestari', 'color_class' => string],
    ],
    'users' => [
        [
            'id' => string,
            'name' => string,
            'email' => string,
            'affiliation' => string,
            'is_sysadmin' => bool,
            'journal_memberships_count' => int,
            'status' => string,
            'created_at' => string,
        ],
    ],
    'recent_imports' => [
        [
            'id' => string,
            'journal_name' => string,
            'file_name' => string,
            'total_rows' => int,
            'processed_rows' => int,
            'failed_rows' => int,
            'status' => string,
            'created_at' => string,
        ],
    ],
]
```

### C. Kontrak Data Pengaturan & Kesehatan Server (`SysAdminSettingsViewModel`)
```php
[
    'settings' => [
        'site_name' => string,
        'site_description' => string,
        'maintenance_mode' => bool,
        'maintenance_message' => string,
        'max_upload_size_mb' => int,
        'allowed_file_extensions' => string,
        'session_lifetime_minutes' => int,
    ],
    'server_health' => [
        'php_version' => string,        // 'PHP 8.2.28'
        'database_driver' => string,    // 'SQLite (3.46.1)'
        'environment' => string,        // 'production' / 'local'
        'disk_usage_formatted' => string, // '4.2 GB / 20 GB (21%)'
        'cache_driver' => string,       // 'file'
        'malware_scanner_status' => string, // 'Active (ClamAV)'
    ],
]
```

### D. Kontrak Data Jejak Audit (`SysAdminAuditLogsViewModel`)
```php
[
    'logs' => [
        [
            'id' => string,
            'action' => string,
            'action_label' => string,
            'actor_name' => string,
            'actor_type' => string,     // 'user' | 'system'
            'resource_type' => string,
            'resource_id' => string,
            'occurred_at' => string,
            'request_id' => string,
            'diff' => ?array,
            'reason' => ?string,
        ],
    ],
]
```
