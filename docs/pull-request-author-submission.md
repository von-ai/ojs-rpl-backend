# 1. Title
feat(author): implement end-to-end manuscript submission, 6-stage workflow tracker & withdrawal lifecycle

---

# 2. Summary / Description
Pull Request ini menghadirkan portal pengajuan dan manajemen naskah ilmiah (*Author Submission Portal*) secara menyeluruh (*end-to-end*) untuk sistem Open Journal Systems (OJS) 3 berbasis Laravel 12 dan PHP 8.6.

Fitur utama yang diimplementasikan meliputi:
1. **Interactive 5-Step Submission Wizard:** Pengajuan naskah baru yang memandu penulis mulai dari kesepakatan etika publikasi, unggah berkas (naskah anonim, berkas pelengkap, cover letter), metadata ilmiah (judul bilingual, batasan abstrak 100–300 kata, 3–5 kata kunci terindeks, pendanaan riset, DOI referensi), multi-kontributor, hingga konfirmasi akhir.
2. **6-Stage Visual Workflow Tracker:** Pelacak tahapan editorial naskah yang interaktif (Draf $\rightarrow$ Pemeriksaan Awal $\rightarrow$ Proses Telaah $\rightarrow$ Perlu Revisi $\rightarrow$ Produksi $\rightarrow$ Diterbitkan) dengan visualisasi linimasa riwayat status dan tipografi bersih bebas dari *raw markdown asterisks*.
3. **COPE-Compliant Manuscript Withdrawal:** Siklus penarikan naskah dengan pembatasan hak berbasis status editorial (hanya diizinkan pada status `submitted` sebelum ditugaskan ke editor/reviewer).
4. **Absolute Double-Blind Privacy:** Proteksi privasi telaah tertutup di mana identitas mitra bestari (*reviewer*), catatan rahasia editor, dan skor evaluasi tidak pernah diekspos ke sisi penulis (*author*).
5. **Reusable Global Blade Components:** Promosi komponen `x-action-modal` dan `x-toast` menjadi komponen global yang dapat dipakai ulang di modul lain.

---

# 3. Motivation / Context
- **Kebutuhan Bisnis & Kepatuhan Etika Publikasi (COPE Guidelines):**
  Sebelumnya, portal penulis belum terhubung dengan siklus hidup editorial secara utuh dan belum memiliki mekanisme penarikan naskah (*withdrawal*). Sesuai pedoman etika publikasi ilmiah internasional (*Committee on Publication Ethics* - COPE), penarikan naskah sepihak oleh penulis hanya boleh dilakukan sebelum proses telaah sejawat dimulai untuk mencegah manipulasi pengajuan ganda (*duplicate submission*) dan pemborosan waktu reviewer independen.
- **Keselarasan Dokumen Arsitektur & ERD:**
  Menggantikan prototipe tampilan statis dengan arsitektur produksi yang patuh pada dokumen arsitektur (`docs/architecture.md`), keputusan kontrak (`docs/contract-decisions.md`), dan skema basis data 58 tabel ERD (`docs/erd.md`).
- **Penyempurnaan Pengalaman Pengguna (UX Polish):**
  Menghilangkan karakter *raw markdown asterisks* (`*Double-Blind*`) yang sebelumnya muncul secara literal pada teks antarmuka (*stepper active stage*) agar tampilan menjadi rapi dan ramah pengguna.

---

# 4. Changes Made

### A. Skema Basis Data & Model (Database & Eloquent)
- **Migrasi Tabel Inti:** Membuat 6 migrasi dengan integritas kunci primer `ulid('id')` dan kunci asing `foreignUlid(...)` yang selaras dengan ERD:
  - `journals`, `sections`, `submissions`, `submission_contributors`, `submission_files`, `file_entries`.
- **Factories & Seeders:** Menambahkan `SubmissionSeeder` yang menginjeksi **9 skenario alur kerja editorial realistis** (`SUB-2026-00101` s.d. `SUB-2026-00109`) mencakup seluruh kemungkinan status naskah.

### B. Lapisan Logika & Presentasi (Backend & ViewModels)
- **Controller Ramping:** [`AuthorSubmissionController`](app/Http/Controllers/Author/AuthorSubmissionController.php) mengelola 5 aksi rute: `index`, `create`, `store`, `show`, dan `withdraw`.
- **Validasi Multi-Tahap:** [`StoreSubmissionRequest`](app/Http/Requests/Author/StoreSubmissionRequest.php) memvalidasi berkas PDF/DOCX (maksimal 20 MB), batasan abstrak (100–300 kata), jumlah kata kunci (3–5 kata), dan kelengkapan kontributor korespondensi.
- **Dedicated ViewModels:**
  - [`AuthorSubmissionsViewModel`](app/ViewModels/AuthorSubmissionsViewModel.php): Menangani tab filter status (`all`, `active`, `review`, `revision`, `published`, `archived`), pencarian judul/abstrak, pengurutan, dan kalkulasi statistik ringkasan.
  - [`AuthorSubmissionDetailViewModel`](app/ViewModels/AuthorSubmissionDetailViewModel.php): Menangani mapping stepper 6 tahap, penegakan privasi Double-Blind, generator sitasi otomatis (format APA 7th, IEEE, BibTeX), serta *state-machine* penarikan naskah (`canWithdrawSubmission()` dan `cannotWithdrawReason()`).

### C. Antarmuka Pengguna (Frontend Blade + Tailwind + Alpine.js)
- `resources/views/author/submissions/index.blade.php`: Dasbor daftar pengajuan dengan tabel responsif, filter badge dinamis, dan penanda status berwarna.
- `resources/views/author/submissions/create.blade.php`: Antarmuka form wizard 5 langkah dengan penyimpanan draf lokal (*auto-draft*) dan navigasi interaktif.
- `resources/views/author/submissions/show.blade.php`: Halaman detail naskah dengan stepper alur kerja, panel metadata, unduhan berkas, linimasa aktivitas, dan modal aksi penarikan naskah.
- `resources/views/components/action-modal.blade.php` & `toast.blade.php`: Komponen modal dialog accessible dan sistem notifikasi toast mengambang.

---

# 5. Testing

### A. Pengujian Otomatis (Automated Test Suites)
Seluruh pengujian unit dan fitur telah dijalankan dan lulus 100%:
- **Total Test Suite:** `65 passed` (562 assertions)
- **Spesifik Author Submission:** `39 passed` (366 assertions) mencakup:
  - `AuthorNewSubmissionTest` (15 tests, 126 assertions): Validasi form, unggah berkas, multi-kontributor, submit sukses.
  - `AuthorSubmissionDetailTest` (12 tests, 108 assertions): Hak akses author, privasi Double-Blind, generator sitasi, logika izin penarikan naskah, dan penanganan submit penarikan via POST.
  - `AuthorSubmissionsPageTest` (12 tests, 132 assertions): Rendering dasbor, tab filter, pagination, pencarian.
- **Code Style (Linter):** Lulus uji Laravel Pint tanpa pelanggaran (`vendor/bin/pint --test` $\rightarrow$ `0 issues`).

### B. Pengujian Manual & Skenario Verifikasi Lokal (Reviewer Steps)
Reviewer dapat mereproduksi dan memvalidasi PR ini di lokal dengan langkah berikut:

```bash
# 1. Checkout branch & pasang dependensi
git fetch origin feat/author-submissions
git checkout feat/author-submissions
composer install
npm install && npm run build

# 2. Migrasi dan jalankan seeder pengujian
php artisan migrate:fresh --seed
php artisan serve
```

Buka URL `http://localhost:8000/author/submissions` dan verifikasi skenario pada tabel berikut:

| ID Naskah | Status Naskah | Uji Fungsionalitas | Hasil yang Diharapkan |
| :--- | :--- | :--- | :--- |
| `SUB-2026-00102` | **Pemeriksaan Awal** (`submitted`) | Klik "Ajukan Penarikan Naskah" | Tombol aktif. Modal konfirmasi terbuka; penarikan berhasil dan tercatat di linimasa. |
| `SUB-2026-00103` | **Proses Telaah** (`in_review`) | Periksa tombol penarikan & reviewer | Tombol penarikan terkunci (*disabled*) dengan kotak peringatan etika. Identitas reviewer anonim (Double-Blind terjamin). |
| `SUB-2026-00104` | **Perlu Revisi** (`revision_required`) | Buka tab keputusan redaksi | Catatan keputusan tampil, tenggat waktu revisi aktif, form unggah revisi tersedia. |
| `SUB-2026-00108` | **Diterbitkan** (`published`) | Klik tombol "Kutip Naskah" | Modal sitasi terbuka dengan format APA, IEEE, dan BibTeX yang siap disalin. |

---

# 6. Screenshots / Demo

### Alur Wizard Submisi (5 Langkah):
```text
[Langkah 1: Ketentuan] ➔ [Langkah 2: Unggah Berkas] ➔ [Langkah 3: Metadata] ➔ [Langkah 4: Penulis] ➔ [Langkah 5: Konfirmasi]
        ✓ Ketentuan Disetujui     ✓ PDF Naskah Anonim      ✓ Abstrak Bilingual      ✓ Multi-Author           ✓ Kirim Naskah
```

### Logika Tombol Penarikan Naskah (State-Machine Behavior):
| Kondisi Naskah | Tampilan UI | Perilaku Aksi |
| :--- | :--- | :--- |
| **Tahap Awal (`submitted`)** | <kbd>Ajukan Penarikan Naskah</kbd> *(Tombol Aktif - Merah Lembut)* | Membuka modal konfirmasi beralasan; mengubah status ke `withdrawn`. |
| **Dalam Telaah / Revisi (`in_review`)** | 🔒 *Naskah Sedang Dalam Proses Telaah* *(Terkunci)* | Menampilkan peringatan informatif bahwa penarikan wajib atas izin resmi Pemimpin Redaksi. |

### Perbaikan Tipografi Stepper (Before vs After):
- **Sebelum:** `Tahap Aktif: Proses Telaah — Proses penelaahan tertutup (*Double-Blind*) oleh minimal 2 mitra bestari.`
- **Sesudah:** `Tahap Aktif: Proses Telaah — Proses penelaahan tertutup (Double-Blind) oleh minimal 2 mitra bestari.`

---

# 7. Risks, Migration & Notes

- **Migrasi Basis Data:**
  PR ini menyertakan migrasi tabel baru (`journals`, `sections`, `submissions`, dll.). Jalankan `php artisan migrate` saat deployment ke staging/production.
- **Dampak Data yang Ada (Breaking Changes):**
  **Nol (Zero Breaking Changes).** Tidak ada tabel atau kolom lama di `main` yang dihapus atau diubah tipenya.
- **Pertimbangan Keamanan & Otorisasi:**
  - Endpoint `POST /author/submissions/{id}/withdraw` dilindungi oleh otorisasi kepemilikan naskah (`author_id === auth()->id()`) dan proteksi token CSRF untuk mencegah serangan IDOR maupun *cross-site request forgery*.
  - Penyaringan data sensitif dilakukan di lapisan ViewModel sebelum dikirim ke Blade, menjamin kerahasiaan proses *peer-review*.
- **Konfigurasi Lingkungan (.env):**
  Tidak ada penambahan variabel `.env` baru yang wajib; menggunakan konfigurasi database dan session default yang sudah ada.
