# Panduan Integrasi Eksternal SIRIDA 1.0.0

Aplikasi SIRIDA mengadopsi arsitektur **Modular Monolith Laravel 13 (Blade + ViewModel Pattern OOP)**. Antarmuka pengguna internal (penyerahan naskah, review, copyedit, penerbitan) berkomunikasi langsung dengan server melalui sesi stateful web (`auth:web`) tanpa perantara REST API atau Bearer token.

Dokumen ini memuat panduan integrasi sistem terhadap antarmuka dan layanan eksternal:

---

## 1. Protokol OAI-PMH 2.0 (`/oai`)

Untuk mendukung pengindeksan artikel ilmiah otomatis oleh mesin perayap dan agregator nasional/internasional (seperti Google Scholar, SINTA, Garuda, Moraref, dan DOAJ), SIRIDA menyediakan antarmuka OAI-PMH 2.0 berbasis XML.

* **Format Transport:** HTTP GET dan form-encoded POST pada rute `/oai`.
* **Format Metadata:** Dublin Core (`oai_dc`) berbasis XML.
* **Kata Kerja (Verbs) Didukung:**
  * `Identify`: Informasi identitas repositori jurnal.
  * `ListMetadataFormats`: Mengembalikan format metadata yang didukung (`oai_dc`).
  * `ListSets`: Daftar set (jurnal / kategori publikasi).
  * `ListIdentifiers`: Daftar identifier naskah yang telah diterbitkan.
  * `ListRecords`: Daftar rekaman metadata lengkap artikel yang telah terbit.
  * `GetRecord`: Mengambil detail rekaman satu artikel berdasarkan identifier OAI.
* **Perilaku Error:** Sesuai spesifikasi OAI-PMH 2.0, kegagalan parameter atau data tidak ditemukan mengembalikan dokumen XML dengan tag `<error code="...">` dan status HTTP 200.

---

## 2. Autentikasi Eksternal ORCID (OAuth 2.0 Callback)

SIRIDA mendukung penautan profil peneliti dengan akun ORCID iD resmi menggunakan alur OAuth 2.0 Authorization Code:

* **Inisiasi Penautan:** Pengguna mengklik tombol "Tautkan Akun ORCID" di profil.
* **Redirect ke ORCID:** Sistem menghasilkan URL otorisasi ORCID dengan parameter `client_id`, `response_type=code`, `scope=/authenticate`, dan state CSRF acak yang disimpan di sesi pengguna.
* **Callback URL:** `/auth/orcid/callback`
  * Menerima parameter `code` dan `state`.
  * Sistem memverifikasi kecocokan `state` di sesi web untuk mencegah serangan CSRF.
  * Worker/controller menukarkan `code` ke server ORCID untuk memperoleh ORCID iD dan access token pengguna.
  * Rekaman disimpan pada tabel `orcid_connections` dan ditautkan ke akun pengguna yang sedang aktif.

---

## 3. Registrasi DOI Crossref (Transactional Outbox Worker)

Pendaftaran Digital Object Identifier (DOI) untuk artikel yang telah resmi diterbitkan dijalankan secara asinkron agar tidak membebani proses penerbitan isu:

* **Pemicu:** Ketika Editor in Chief menyetujui penerbitan isu (`publishIssue`), sistem mencatat event ke tabel `outbox_messages` dengan tipe `crossref.deposit`.
* **Worker Eksekusi:** ACA Dedicated Worker membaca outbox yang berstatus `pending` dan memformat data metadata artikel ke dalam XML skema Crossref.
* **Pengiriman:** Mengirimkan payload XML ke Crossref Deposit API via HTTP POST dengan kredensial institusi.
* **Idempotensi & Retry:** Jika server Crossref mengalami gangguan (timeout/5xx), worker melakukan *exponential backoff retry* dengan DOI yang tetap sama tanpa menduplikasi data. Kegagalan deposit Crossref tidak membatalkan status terbitnya artikel di portal.

---

## 4. Penyimpanan Berkas & Direct Upload Azure Blob Storage

Untuk efisiensi bandwidth server dan penanganan berkas manuskrip/galai hingga 20 MiB:

* **Generasi SAS URL:** Pengguna meminta izin upload untuk berkas naskah. Server memvalidasi hak akses dan menghasilkan Shared Access Signature (SAS) URL yang memiliki masa berlaku terbatas (15 menit) dan izin tulis privat (`sp=w`).
* **Direct Upload:** Browser mengunggah byte berkas langsung ke Azure Blob Storage melalui protokol HTTPS PUT ke SAS URL.
* **Validasi & Pemindaian:**
  * Microsoft Defender for Storage memindai malware secara near real-time di lingkungan Azure.
  * Server Laravel memverifikasi checksum SHA-256 dan ukuran berkas setelah unggahan selesai.
  * Berkas dengan deteksi ancaman diberi status `quarantine` dan dilarang masuk ke alur review.
* **Lingkungan Lokal:** Menggunakan emulator Azurite pada port 10000 dengan adapter Flysystem lokal.

---

## 5. Email Transaksional (SMTP)

* **Produksi:** Azure Communication Services (ACS) Email melalui protokol SMTP TLS Port 587.
* **Lokal / Pengujian:** Mailpit Container pada port SMTP 1025 dan Web UI port 8025 untuk inspeksi email registrasi dan verifikasi.
* **Ketahanan Antrean:** Email dimasukkan ke antrean database `identity` dalam satu transaksi pendaftaran (`beforeCommit`) sehingga kegagalan proses tidak menyebabkan data email hilang.
