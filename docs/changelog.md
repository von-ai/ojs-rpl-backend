# Perubahan kontrak

## 1.0.0 — 7 Oktober 2026

Final baseline untuk implementasi, 195 operasi planned. Persetujuan stakeholder
mengikuti review PR; versi kontrak tidak berarti implementasi telah dirilis.

- **Perubahan registrasi sebelum implementasi:** hapus `requested_role` dan `journal_id`
  dari `RegisterRequest`; kedua field lama sekarang ditolak. `username` tetap wajib.
  Hapus enam operasi reviewer application, schema terkait dan parameter `application_id`.
  Reviewer direkrut melalui undangan editor per naskah/putaran; direktori reviewer tetap
  dikelola melalui membership/role jurnal dan tidak memberi akses naskah secara otomatis.
  Client harus menghapus pilihan role dari form register dan halaman pengajuan reviewer,
  lalu memperbarui SDK/mock serta memakai alur assignment yang sudah tersedia.
- Tetapkan stack, batas modul, keputusan bisnis dan acceptance gate dalam architecture.md.
- Tambahkan skenario berhasil, alternatif/gagal dan kriteria penerimaan UC-01–18.
- Tetapkan UTC database clock, expiry saat `now >= due_at`, worker per menit,
  eskalasi/deduplication dan guards yang tetap berlaku saat worker terlambat.
- **Breaking dari draft.3:** late draft/save/submit review harus menunggu extension;
  `submitReview` hanya mengizinkan state accepted sebelum deadline, bukan overdue.
- Tegaskan race handling extension, invitation replacement, draft/assignment ETag,
  replay idempotency dan kode error deadline/precondition.
- Settings menolak auto-withdraw aktif tanpa grace integer eksplisit pada resource lengkap.
- Ganti hostname produksi/staging contoh dengan same-origin `/`; actual origin
  merupakan konfigurasi deployment.
- Tambahkan panduan integrasi dan contoh request tervalidasi. Regenerasi mock/SDK.

## 1.0.0-draft.3

201 operasi; penutupan gap workflow/admin/upload/integrasi. Upload-session pindah ke
`/api/v1/upload-sessions`, ORCID authorize menjadi POST, approval mengikat seluruh
output file, proyeksi publik dipisahkan dan konfigurasi integrasi memakai candidate.

## 1.0.0-draft.2

114 operasi; pemisahan global/journal role, privacy blind review, author-safe feedback,
ETag/412/428, OAI POST dan pipeline validasi. Detail pada historical review-srs.md.

## Kebijakan kompatibilitas

OpenAPI `info.version` adalah versi kontrak; `/api/v1` adalah major wire API.
Perubahan path, field wajib, enum tertutup, otorisasi atau perilaku yang mematahkan
client memerlukan review compatibility, migration notes dan versi mayor API baru
setelah v1 diimplementasikan. Penambahan yang kompatibel memakai minor; koreksi
penjelasan tanpa perubahan wire memakai patch. Perubahan keamanan mendesak tetap
memerlukan catatan migrasi/komunikasi. Tidak mengubah arti status lama diam-diam.
