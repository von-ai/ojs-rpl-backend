# Audit keamanan, integrasi tampilan, dan basis data — 4 November 2026

## Ruang lingkup

Perbaikan dibangun dari `main` 5c563c4, termasuk pembaruan UI 0c6a5c7 (2 November), untuk SIRIDA dan backend Laravel yang melayani seluruh tampilan saat ini. Aplikasi menggunakan Blade dan sesi web; integrasi tidak membutuhkan backend REST terpisah. Branch hasil: `adrian`.

Acuan data dibaca langsung dari [SIRIDA ERD di FigJam](https://www.figma.com/board/ZaFQP8FTOMqsGWdDZBqxOU/SIRIDA-ERD?node-id=0-1), bagian aktif **SIRIDA ERD — MySQL + Redis — 2026-10-14**, termasuk aturan persistensi dan keamanan. Bagian ERD arsip tidak dipakai sebagai kontrak aktif.

## Temuan yang diperbaiki

| Area | Temuan dan hasil perbaikan |
| --- | --- |
| Otorisasi objek | Peran tanpa lingkup penugasan/jurnal dapat menjangkau objek lain. Filter dan penguncian layanan kini memeriksa jurnal, pemilik, penugasan aktif, dan konflik penulis. Sysadmin global harus memiliki grant dengan journal_id NULL. |
| Sesi dan akun | Status lockout dapat menimpa status nonaktif. Akun diperiksa ulang setiap permintaan; registry MySQL mendukung pencabutan, fingerprint password, dan kedaluwarsa absolut 24 jam. Batas idle maksimum 120 menit. Remember token disimpan sebagai HMAC dengan expiry server. |
| Pemulihan akun | Tautan reset manajer sebelumnya bukan token broker yang dapat digunakan. Pemulihan kini memakai token hash, expiry, konsumsi sekali, notifikasi antrean terenkripsi, dan pencabutan sesi. Reset tidak mengaktifkan akun nonaktif. Host/proxy dipercaya secara eksplisit. |
| Berkas privat | Unduh berdasarkan nama/fallback dokumen dapat mengabaikan identitas atau keberadaan objek. Unduh sekarang memerlukan relasi objek, hasil scan bersih, objek fisik, dan checksum yang cocok. Berkas tidak ditemukan menghasilkan 404; karantina 423; integritas gagal 409. |
| Pemindaian | Upload tidak lagi langsung ditandai bersih. Job persisten memverifikasi ukuran/hash dan meminta scanner (Microsoft Defender / Azurite mock); scanner gagal atau belum dikonfigurasi mempertahankan karantina. |
| Double blind | Reviewer hanya menerima file anonim terpisah dari snapshot putaran yang dibekukan dan penugasannya sendiri. Judul/abstrak asli dan filename asli tidak digunakan sebagai fallback. Undangan ditolak/tidak diterima tidak memberikan akses file atau penyuntingan review. |
| Keputusan editorial | Kuorum menghitung reviewer berbeda dengan hasil review benar-benar disubmit. Keputusan final menutup putaran; penerimaan membuat penugasan copyeditor jurnal yang sah. Hasil telaah final tidak dapat ditimpa. |
| Produksi | Persetujuan penulis tersedia di portal author dan membekukan checksum output. Handoff membuat penugasan layout. Menyelesaikan catatan tidak menyelesaikan pemeriksaan proofreader. Versi galai baru memerlukan penugasan/pemeriksaan baru; catatan yang belum diverifikasi dibawa ke versi baru. Layout hanya meminta persetujuan; EIC yang mengesahkan. Permintaan ulang tidak mencabut persetujuan final. |
| Publikasi | Publikasi edisi sebelumnya tidak membentuk data artikel publik. Layanan kini membuat Article, snapshot kontributor, relasi galai, dan pengaitan daftar isi, setelah readiness dan pembayaran diperiksa. Unduh publik hanya menyediakan galai aktif milik artikel terbit. |
| Impor dan pengaturan | CSV dipindai lalu diproses job persisten, dibatasi ukuran/jumlah baris/peran, dan menghormati opsi undangan. Pengaturan pemeliharaan, unggahan dan sesi benar-benar diterapkan. Pembersihan cache tidak menghapus pembatas keamanan. |
| Basis data | Nomor submission dialokasikan melalui sequence terkunci dan unique index. Foreign key komposit mencegah section lintas jurnal. Slot aktif peran, layout, reviewer dan extension dilindungi constraint. Audit log menolak UPDATE/DELETE pada MySQL/SQLite. |
| Integrasi UI | Panel persetujuan author, mediasi editor, persiapan file anonim, dan publikasi dihubungkan ke layanan nyata. Tautan reset/panduan/navigasi/unduh publik diperbaiki. Permintaan penetapan peran tanpa pengguna tidak memilih akun pertama. Error tak terduga admin dilaporkan tanpa membocorkan detail database. |
| Konsistensi UI | Token merah/navy/canvas dan radius pembaruan 2 November dipertahankan. Inter dibundel lokal di seluruh portal; sisa serif/justify dibersihkan. Dropdown bersama mendukung keyboard, sinkronisasi form/reset, disabled, validasi dan fallback native tanpa JavaScript. |
| Dependensi | Symfony Mime diperbarui ke 8.1.10. Audit Composer dan npm pada saat pemeriksaan melaporkan nol advisory. |

## Kecocokan dengan ERD

MySQL tetap sumber otoritatif. Antrean dipersistenkan di database; Redis dipakai untuk sesi/cache/limiter ketika dikonfigurasi, dengan AOF dan noeviction pada Compose. Revocation sesi tetap dapat diperiksa lewat MySQL.

Skema aplikasi yang sudah ada memakai `user_roles`, bukan seluruh rancangan membership/grant dari ERD 70 tabel. Perubahan ini mempertahankan skema tersebut dan menegakkan jurnal serta penugasan pada fitur yang sudah memiliki layar. Seluruh rancangan 201 endpoint, upload-session API, dan modul masa depan bukan bagian implementasi ini. Dokumen spesifikasi tidak boleh dianggap daftar endpoint yang sudah dapat dipanggil.

Pemeriksaan read-only database lokal sebelum perubahan menemukan 52 tabel, 11 submissions, tanpa duplikasi nomor, mismatch section/jurnal, atau slot layout/copyedit/global-grant ganda pada pemeriksaan yang dilakukan. Ada 28 file berlabel clean dari alur lama; label itu tidak menjadi bukti antivirus. Migrasi mengarantina ulang file lama untuk pemeriksaan sebenarnya. Data aplikasi pengguna tidak dimutasi dalam audit ini.

## Bukti verifikasi

Hasil lokal final: **331 test / 2.560 assertion lulus pada MySQL 8.4**, termasuk scanner asli. SQLite: **331 test / 2.558 assertion**, satu uji scanner eksternal dilewati sesuai konfigurasi. Pint, lint 120 file PHP, Composer validate, build Vite dan `docker compose config --quiet` lulus. Composer/npm audit melaporkan nol advisory. Smoke HTTP dengan middleware CSRF aktif menghasilkan **419** untuk POST tanpa token dan **400** untuk Host tidak dipercaya.

Pengujian meliputi:

- Suite fitur dan unit lengkap pada SQLite serta MySQL 8.4 dengan schema pengujian terpisah.
- Pemindaian malware untuk konten bersih dan EICAR melalui job aplikasi (didukung Microsoft Defender for Storage dan mock Azurite).
- Alur formulir HTTP author → desk review/SE → dua reviewer → EIC → copyeditor → persetujuan author → layout → proofreader → EIC → manager → edisi/artikel/galai publik. Fixture scan pada alur workflow disimulasikan secara eksplisit; uji pemindai keamanan dijalankan terpisah.
- Regresi otorisasi lintas jurnal/penugasan, unduh privat, file hilang/karantina/tamper, review anonim, sesi dicabut, reset sekali pakai, host tak dipercaya, retensi limiter dan audit append-only.
- Kontrak seluruh route literal yang dirujuk Blade, termasuk tampilan daftar isi publik berisi artikel nyata.
- Build Vite, Composer validate/audit, npm audit, Pint dan pemeriksaan whitespace/sintaks.
- Browser lokal dengan CSRF aktif, login, modal impor, dropdown lewat keyboard, dan viewport 390 px; suite halaman menguji portal tiap peran. Ini bukan klaim seluruh kombinasi perangkat/peramban telah diuji.

## Batas verifikasi dan operasi

Audit dan test mengurangi risiko; hasil ini bukan jaminan tidak ada celah. Tidak ada pengujian penetrasi terhadap deployment publik, migrasi database produksi, deploy cloud, atau pengiriman email eksternal. Pengujian lokal memakai mail fake/array dan database/berkas terisolasi. Beberapa notifikasi produksi lama masih mengirim mail secara sinkron dan mencatat kegagalan; outbox universal belum diimplementasikan.

CSP yang ditambahkan membatasi frame, object, base dan target form; aplikasi masih memiliki script inline. Jangan menganggapnya CSP script ketat. TLS, proxy, SMTP, backup, hak akun database, penyimpanan privat Azure Blob, dan pemantauan worker harus dikonfigurasi pada deployment. [Panduan upgrade](security-upgrade.md) menjelaskan dampak migrasi dan konfigurasi yang diperlukan.
