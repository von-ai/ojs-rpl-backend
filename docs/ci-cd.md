# CI/CD dan kecocokan kebutuhan SIRIDA

Pemeriksaan 5 November 2026 mencakup workflow GitHub Actions, image produksi, suite aplikasi, serta katalog MySQL hasil migrasi pada database pengujian terpisah.

Sumber kebutuhan dibaca dari bagian UC-01–18 dan NF-01–10 pada dokumen RPL. Acuan ERD adalah bagian aktif **SIRIDA ERD — MySQL + Redis — 2026-10-14**, yang memuat 70 tabel domain. Bagian arsip pada board dikecualikan. [SRS RPL](https://docs.google.com/document/d/1bWldGLdB9Ek2bgmJtyupGi5yA7C1E409w6Gs65p__OM/edit), [ERD FigJam](https://www.figma.com/board/ZaFQP8FTOMqsGWdDZBqxOU/SIRIDA-ERD?node-id=83-6095).

## Alur pemeriksaan dan penerbitan image

- Pull request menjalankan `validate.yml`. Workflow yang sama dapat dipanggil ulang melalui `workflow_call`.
- Push ke `main`, tag `v*.*.*`, dan pemicu manual penerbitan menjalankan `docker-publish.yml`. Job publikasi memiliki `needs: validate`, sehingga seluruh validasi harus berhasil sebelum image dikirim ke GHCR.
- Validasi mencakup Composer, Pint, build Vite, audit dependensi, suite SQLite, suite MySQL 8.4, ClamAV asli, dan Azurite privat.
- JUnit dan laporan coverage Clover disimpan sebagai artifact `test-results` selama tujuh hari, termasuk ketika tes gagal. Coverage mencakup direktori `app` sebagaimana dikonfigurasi dalam `phpunit.xml`.
- Job `container` membangun Dockerfile produksi, memeriksa kebutuhan ekstensi PHP, dan mencoba boot Laravel pada image yang dihasilkan.
- Hak token default hanya membaca isi repo. Hak menulis packages hanya diberikan pada job penerbitan. Actions dikunci ke commit SHA dan kredensial checkout tidak dipertahankan.
- Tag `latest` hanya diperbarui dari branch default. Build manual dari branch lain memakai tag branch/SHA. Dependency host, data storage, konfigurasi Terraform, dan laporan pengujian dikecualikan dari konteks image. Direktori storage kosong dibuat ketika image dibangun.

Pemanggilan workflow yang sama menghindari perbedaan rangkaian tes antara PR dan rilis. Pengaturan izin per job mengikuti panduan keamanan GitHub. [Reusable workflows](https://docs.github.com/en/actions/how-tos/reuse-automations/reuse-workflows), [Secure use](https://docs.github.com/en/actions/reference/security/secure-use).

Pipeline ini menghasilkan image. Penerapan ke Azure, eksekusi migrasi produksi, pemeriksaan kesiapan layanan, dan rollback masih merupakan langkah operator dalam [panduan upgrade](security-upgrade.md).

## Pemetaan kebutuhan ke pengujian

| Kebutuhan | Bukti dalam repo | Batas verifikasi |
| --- | --- | --- |
| UC-01–02, autentikasi dan peran | RegistrationTest, Auth, PortalSecurityTest | Provider email memakai pengganti lokal pada suite |
| UC-03–04, artikel publik dan pencarian | PublicJournalPortalTest, PublicPagesTest | Beban pengguna serentak belum diukur |
| UC-05–07, pengajuan dan revisi | Author, DirectBlobUploadTest | Suite upload memakai fake storage untuk variasi kasus, adapter Azure juga diuji terhadap Azurite |
| UC-08–12, review dan keputusan | ReviewerWorkflowTest, SectionEditorWorkflowTest, EditorInChiefWorkflowTest | Integrasi editorial memakai fixture terisolasi |
| UC-13–14 dan UC-18, produksi | Copyeditor, LayoutEditor, Proofreader, EditorialPipelineTest | Pengeditan dokumen tetap dikerjakan di luar aplikasi |
| UC-15–17, publikasi dan administrasi | JournalManager, SysAdmin, EditorialPipelineTest | Integrasi provider eksternal belum dibuktikan oleh tes ini |
| NF-01 dan NF-03, performa dan skalabilitas | Belum ada suite beban k6/JMeter | Target 500/1.000 pengguna dan p95 memerlukan pengukuran staging |
| NF-02, keamanan | Tes otorisasi objek, sesi, lockout, CSRF, file anonim, karantina, checksum, serta audit dependensi | TLS deployment dan penetration test lingkungan publik perlu pemeriksaan tersendiri |
| NF-04 dan NF-06, usability dan kompatibilitas | Tes respons halaman serta verifikasi browser lokal | Belum ada matriks browser otomatis, audit WCAG AA, atau hasil SUS |
| NF-05, availability | Konfigurasi health dan monitoring infrastruktur | CI tidak membuktikan uptime bulanan atau waktu failover |
| NF-07, maintainability | Pint, suite aplikasi, laporan coverage CI | Target coverage 70% perlu dinilai dari laporan, bukan jumlah tes |
| NF-08, backup dan recovery | DatabaseBackupTest, retensi, restore SQLite, dan uji adapter backup Azure | Drill restore MySQL dan storage produksi masih perlu dijadwalkan |
| NF-09, interoperabilitas | Metadata DOI/ORCID tersedia pada model dan formulir | Belum ditemukan integrasi provider Crossref/ORCID maupun endpoint OAI-PMH yang teruji |
| NF-10, audit | PortalSecurityTest dan SysAdminEndToEndIntegrationTest menguji audit append-only | Bukti kepatuhan operasional perlu pemeriksaan deployment dan proses organisasi |

## Perbedaan antara sumber kebutuhan dan implementasi

SRS masih menyebut Next.js/REST/JWT, AWS/S3, dan pembayaran APC di luar cakupan pada beberapa bagian. Bagian teknologi lain sudah menyebut Blade dan MySQL. Implementasi repo memakai Blade dengan sesi web, adapter Azure, dan APC manual. Perubahan desain tersebut dicatat dalam [arsitektur](architecture.md). Kesepakatan tim dan pembaruan SRS diperlukan agar kriteria penerimaan memiliki satu acuan.

Target unggah 20 MB dalam kurang dari sepuluh detik pada koneksi 1 Mbps juga perlu dikoreksi. Pengiriman payload saja memerlukan sekitar 160 detik. Ukuran MiB memerlukan waktu sedikit lebih lama.

## Perbandingan skema dengan ERD

Database MySQL hasil migrasi pada branch perbaikan memiliki **55 tabel dan 92 constraint foreign key**, termasuk tabel operasional Laravel. Sebanyak **42 nama tabel** sama dengan 70 tabel domain pada ERD. Kesamaan nama tidak membuktikan kesamaan seluruh kolom dan aturan bisnis.

| Area | Rancangan FigJam | Implementasi repo |
| --- | --- | --- |
| Role jurnal dan sistem | journal_memberships, journal_role_grants, system_role_grants | user_roles dengan lingkup jurnal dan pemeriksaan penugasan |
| Sesi dan pemulihan | auth_sessions, refresh_tokens, oauth_states | auth_sessions, sessions, password_reset_tokens, identity_challenges |
| Unggahan dan scan | upload_sessions, upload_parts, file_scopes, scan_attempts | manuscript_upload_sessions, files, submission_files dan job scan |
| Notifikasi | notifications, outbox_events | outbox_messages serta jobs untuk pengiriman |
| Pembayaran | Tidak ada tabel APC pada baseline board | article_payments |
| Konfigurasi dan integrasi | journal_settings, integration_profiles, integration_configurations dan tabel provider | Sebagian pengaturan berada pada journals/system_settings, integrasi provider belum lengkap |

Nama tabel rancangan yang belum ada pada migrasi adalah `journal_memberships`, `journal_role_grants`, `system_role_grants`, `refresh_tokens`, `orcid_connections`, `oauth_states`, `journal_settings`, `journal_pages`, `email_templates`, `ethics_cases`, `ethics_evidence`, `similarity_checks`, `submission_events`, `doi_registrations`, `crossref_deposits`, `file_scopes`, `upload_sessions`, `upload_parts`, `scan_attempts`, `operations`, `notifications`, `outbox_events`, `idempotency_records`, `system_settings_versions`, `integration_profiles`, `integration_candidates`, `integration_configurations`, dan `integration_tests`.

Sebagian perbedaan merupakan penggantian representasi atau penghapusan rancangan API lama. Sebagian lagi menunjukkan modul yang belum diimplementasikan. Skema produksi harus mengikuti migrasi yang ditinjau dan diuji. Penyelarasan fisik ke seluruh tabel rancangan memerlukan keputusan desain serta rencana migrasi data.

## Reproduksi pengujian

```sh
composer validate --strict
vendor/bin/pint --test
PAO_DISABLE=1 composer test
npm run build
composer audit
npm audit
```

Mode MySQL memakai skema tetap `sirida_latest_test` pada server pengujian terpisah. Aktifkan dengan `RPL_TEST_MYSQL=1` serta port/password uji yang sesuai. `RPL_TEST_CLAMD_HOST` dan `RPL_TEST_AZURITE_URL` mengaktifkan pengujian layanan asli. Konfigurasi dan persiapan container CI tersedia di `validate.yml`.

Regresi paginasi pengguna direproduksi dengan timestamp yang sama dan ID yang urutan insert-nya berbeda. Tes baru gagal sebelum penambahan pengurutan ID, lalu lulus setelah perbaikan. Suite lokal menjalankan 397 tes pada SQLite dan MySQL. Jumlah tes yang dilewati bergantung pada layanan integrasi yang diaktifkan.
