# Panduan integrasi kontrak SIRIDA 1.0.0

**Update implementasi 8 Oktober 2026:** register, verifikasi email dan kirim ulang
sudah tersedia sebagai MVP. Lihat [panduan registrasi](registration.md). Pernyataan
planned/scaffold dalam baseline historis di bawah tetap berlaku untuk fitur lain;
login, membership dan workflow editorial belum dibangun.

Gunakan [OpenAPI](openapi.yaml) untuk tipe dan path, [use case](use-cases.md) untuk
skenario penerimaan, dan [keputusan kontrak](contract-decisions.md) untuk invariants.
API domain masih planned. Contoh bukan instruksi untuk mengirim request ke server
produksi yang sudah tersedia. Origin default `/` berarti same-origin; isi actual
HTTPS origin lewat konfigurasi deployment, bukan hostname `.example`.

## Alur berhasil: penulis mengirim naskah

1. `registerUser` → `verifyEmail` → `login`. Pendaftaran menerima data akun tanpa
   `requested_role` atau `journal_id`; `username` tetap wajib. Verifikasi mengaktifkan
   akses dasar Reader/Author, bukan akses reviewer atau editorial. Untuk jurnal tambahan,
   gunakan `joinJournalAsAuthor`. Simpan token hanya di tempat yang sesuai arsitektur
   browser/BFF pada [stack](architecture.md).
2. `getCurrentUser` → `getActiveContext`/`setActiveContext`. Konteks memilih hak yang
   sudah dimiliki. Ambil `getSubmissionPolicy` dan `listPublicSections` sebelum form.
3. `createSubmission` dengan metadata parsial, lalu simpan ID. `getSubmission`
   mengembalikan ETag; kirim nilai persis termasuk tanda kutip saat `updateSubmission`
   atau `replaceSubmissionContributors`. Minimal satu corresponding contributor,
   section valid, metadata dan persetujuan guideline harus lengkap sebelum submit.
4. Hitung SHA-256 byte berkas. `createUploadSession` mengikat file ke submission
   yang sudah ada. PUT byte ke URL signed dengan `required_headers` persis; jangan
   meneruskan bearer API ke host storage. Jika URL kedaluwarsa, baca/renew session
   sesuai mode; jangan membuat versi berkas diam-diam berubah.
5. `completeUploadSession` memakai payload single/multipart yang sesuai schema dan
   ETag yang diwajibkan. Completion mengonfirmasi transfer, bukan scan bersih.
   Poll `getFile` dengan backoff hingga `status=clean`; failure/karantina tidak boleh
   dipakai dalam submission. Jangan menunggu koneksi HTTP terbuka selama scanning.
6. Baca ulang submission untuk ETag terbaru. `submitSubmission` dengan file ID bersih,
   `If-Match` dan `Idempotency-Key` baru. Simpan respons dan baca `getSubmissionTimeline`.
7. Jika editor meminta revisi: `listAuthorDecisions` → `createRevision` → upload
   manuskrip revisi/response letter → `updateRevision` → `submitRevision`. Jangan
   membaca endpoint keputusan internal melalui role author.

[Contoh body JSON](request-examples.json) divalidasi oleh CI terhadap operation
schema yang sebenarnya. ID, checksum dan tanggal contoh bersifat sintetis;
ganti dengan resource, hash byte dan deadline nyata. Schema-valid tidak berarti
lolos pemeriksaan ownership, timestamp atau file pada server.

Contoh header untuk command yang memang mewajibkan keduanya:

```http
Authorization: Bearer <access-token>
Content-Type: application/json
If-Match: "<etag-dari-get>"
Idempotency-Key: <uuid-baru-per-aksi>
```

Jangan membentuk ETag dari angka `version`. GET sumber validator ditunjukkan oleh
`x-concurrency-resource`. `saveReviewDraft` berbeda: pertama memakai
`If-None-Match: *`; penggantian memakai `If-Match` dari GET draft, jangan keduanya.

## Alur berhasil: reviewer sampai publikasi

Journal Manager mengelola direktori reviewer melalui administrasi membership/role jurnal.
Keanggotaan direktori hanya menentukan kelayakan undangan, tidak membuka akses naskah.
Pengguna tidak mengajukan role reviewer saat register dan tidak melalui reviewer application.
Editor membuka round dengan file anonim dan rubric snapshot, kemudian `assignReviewers`
dengan `now < response_due_at < review_due_at`. Reviewer membaca undangan melalui
`listReviewAssignments`/`getReviewAssignment`, menerima sebelum tenggat, membaca
workspace hanya untuk assignment naskah/putaran tersebut, menyimpan draft dan mengirim
evaluasi lengkap sebelum review deadline. Pengguna yang sama dapat menjadi author pada
naskah lain; role reviewer tidak memberi akses ke seluruh submission jurnal.
Atau ajukan extension dan tunggu keputusan editor; permohonan tidak menghentikan jam.

EIC menilai ringkasan, membuat dan mengirim keputusan. Acceptance dilanjutkan dengan
copyedit dan persetujuan author atas seluruh output checksum; Layout Editor membuat
galley; Proofreader memeriksa versi spesifik; EIC menyetujui final PDF. Daftar
`listReadyArticles` menyediakan ID untuk komposisi issue. `publishIssue` atau jadwal
memeriksa ulang kesiapan, mempublikasikan atomik, lalu mengirim notifikasi/Crossref
melalui outbox. Kegagalan Crossref tidak menghilangkan artikel yang sudah terbit.

## Halaman informasi jurnal publik

1. Panggil `listPublicJournalPages` tanpa login untuk mendapatkan metadata halaman
   (`id`, `type`, `attributes.slug`, `attributes.title`). Ikuti `links.next` sampai
   seluruh daftar didapat untuk membangun navigasi; daftar tidak memuat `body_html`.
2. Bentuk tautan frontend dari slug jurnal dan slug halaman. Saat tautan dibuka,
   panggil `getPublicJournalPage` melalui
   `GET /api/v1/public/journals/{journal_slug}/pages/{page_slug}`.
   Tautan langsung/bookmark dapat mengambil detail tanpa lebih dulu mengambil daftar.
3. Render `attributes.title` dan `attributes.body_html` yang telah disanitasi server
   dalam layout frontend. HTML adalah konten halaman, bukan kode yang dieksekusi.
4. Tampilkan halaman tidak ditemukan untuk `404`, termasuk page yang dihapus atau
   tidak lagi published dan jurnal yang inactive. Detail selalu memeriksa publikasi
   terkini meskipun tautan berasal dari daftar sebelumnya.

Penyimpanan tetap menggunakan `journal_pages`; daftar administratif tetap menyediakan
konten lengkap bagi journal manager. API domain ini masih planned.

## Pemulihan dan error

| Respons | Tindakan client |
| --- | --- |
| 400 | Perbaiki request/header yang tidak valid; jangan retry tanpa perubahan. |
| 401 | Refresh satu kali secara serial jika session masih berlaku; gagal/revoked berarti login ulang. Jangan loop. |
| 403/404 | Jangan mencoba ID/journal lain untuk melewati batas akses. Tampilkan akses tidak tersedia. |
| 409 `REVIEW_INVITATION_EXPIRED` | Undangan terminal; editor perlu mengirim assignment baru. |
| 409 `REVIEW_DEADLINE_EXCEEDED` | Draft dipertahankan read-only; ajukan extension, lanjut setelah disetujui. |
| 409 `IDEMPOTENCY_IN_PROGRESS` | Tunggu dan retry dengan key serta payload identik. |
| 409 `IDEMPOTENCY_KEY_REUSED` | Key pernah dipakai untuk payload berbeda; jangan replay. Aksi baru harus memakai key baru. |
| 409 state lain | Baca ulang aggregate dan tampilkan perubahan workflow; jangan retry buta. |
| 412 | GET ulang; bandingkan perubahan dan minta pengguna menyelesaikan konflik, lalu command baru. |
| 422 | Tampilkan `errors` per field; payload/relasi/policy harus diperbaiki. |
| 428 | Ambil validator GET lalu kirim precondition yang diwajibkan. |
| 429/503 | Hormati `Retry-After` bila tersedia; exponential backoff berjitter dan batas percobaan. |
| Timeout/5xx setelah mutation | Hasil mungkin sudah committed. Untuk operasi idempotency, ulangi key/payload identik; jangan langsung membuat key baru. |

REST error menggunakan `Problem` dengan `request_id`, `code`, `errors`; tampilkan
pesan aman dan correlation ID untuk bantuan, jangan raw stack trace. OAI-PMH adalah
pengecualian: protocol error berupa XML dengan HTTP 200. Download/SSE juga memiliki
media/transport berbeda; ikuti response schema per operation.

Key idempotency disimpan minimal 24 jam, scoped actor/journal/operation. Fingerprint
mencakup path target, payload dan precondition command asli. Replay yang telah
selesai hanya diberikan setelah pemeriksaan akses terkini, sebelum memeriksa state
versi yang sudah berubah; key dengan fingerprint berbeda ditolak. Jika hasil tidak
jelas setelah masa retensi, baca resource/history dan rekonsiliasi sebelum mengirim
aksi baru. Tidak ada jaminan retry unlimited.

Untuk daftar: gunakan pagination metadata/links, filter dan sort hanya yang
dideklarasikan. Untuk notifikasi: mulai polling 30 detik saat halaman aktif, hentikan
saat tersembunyi dan backoff pada error. Ini interval client baseline, bukan SLA.
SSE opsional memakai Authorization melalui fetch streaming/BFF; saat
`resync_required`, muat ulang daftar REST. Jangan menaruh token di query string.
