# EVALUASI & PEMETAAN KEPATUHAN ASPEK LEGAL (HUKUM) SISTEM
## Platform SIRIDA (Sistem Informasi Riset dan Inovasi Daerah — OJS Modern)
**Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar**

---

> **Status Dokumen**: Pemetaan teknis awal untuk validasi pengelola
> **Klasifikasi Sistem**: Penyelenggara Sistem Elektronik (PSE) Lingkup Publik / Sistem Publikasi Ilmiah  
> **Penyusun**: Tim Rekayasa Perangkat Lunak SIRIDA  
> **Tujuan**: Memenuhi Standar Evaluasi Kelaikan Hukum Perangkat Lunak & Menjawab Kerangka Acuan Dosen Penguji

---

## DAFTAR ISI
1. [Eksekutif & Filosofi Pendekatan Legal](#1-eksekutif--filosofi-pendekatan-legal)
   - 1.1 [Dekonstruksi Kekeliruan Pemikiran (Addressing The Common Fallacy)](#11-dekonstruksi-kekeliruan-pemikiran-addressing-the-common-fallacy)
   - 1.2 [Metodologi Scoping: Applicable vs. Non-Applicable Framework](#12-metodologi-scoping-applicable-vs-non-applicable-framework)
2. [Profil Sistem & Klasifikasi Yuridis SIRIDA](#2-profil-sistem--klasifikasi-yuridis-sirida)
   - 2.1 [Entitas Pemilik & Subjek Hukum](#21-entitas-pemilik--subjek-hukum)
   - 2.2 [Alur & Tipologi Objek Hukum yang Dikelola](#22-alur--tipologi-objek-hukum-yang-dikelola)
3. [Analisis Komprehensif Hukum yang BERHUBUNGAN LANGSUNG (In-Scope / Mandatory)](#3-analisis-komprehensif-hukum-yang-berhubungan-langsung-in-scope--mandatory)
   - 3.1 [UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)](#31-uu-no-27-tahun-2022-tentang-pelindungan-data-pribadi-uu-pdp)
   - 3.2 [UU ITE (UU No. 11/2008 jo. UU No. 19/2016 jo. UU No. 1/2024) & PP No. 71/2019 (PSTE)](#32-uu-ite-uu-no-112008-jo-uu-no-192016-jo-uu-no-12024--pp-no-712019-pste)
   - 3.3 [UU No. 28 Tahun 2014 tentang Hak Cipta (Hak Cipta Karya Ilmiah)](#33-uu-no-28-tahun-2014-tentang-hak-cipta-hak-cipta-karya-ilmiah)
   - 3.4 [UU No. 11 Tahun 2019 tentang Sistem Nasional Iptek & Regulasi Akreditasi Jurnal (ARJUNA)](#34-uu-no-11-tahun-2019-tentang-sistem-nasional-iptek--regulasi-akreditasi-jurnal-arjuna)
   - 3.5 [Perpres No. 95 Tahun 2018 (SPBE) & Standar Keamanan Informasi BSSN No. 4/2021](#35-perpres-no-95-tahun-2018-spbe--standar-keamanan-informasi-bssn-no-42021)
   - 3.6 [UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik (UU KIP)](#36-uu-no-14-tahun-2008-tentang-keterbukaan-informasi-publik-uu-kip)
   - 3.7 [UU No. 43 Tahun 2009 tentang Kearsipan](#37-uu-no-43-tahun-2009-tentang-kearsipan)
4. [Analisis Regulasi yang BERHUBUNGAN KONDISIONAL / TERBATAS (Conditional Scope)](#4-analisis-regulasi-yang-berhubungan-kondisional--terbatas-conditional-scope)
   - 4.1 [Regulasi Transaksi Keuangan & Biaya Publikasi (APC)](#41-regulasi-transaksi-keuangan--biaya-publikasi-apc)
   - 4.2 [Etika Riset Kesehatan & Izin Komite Etik (Ethical Clearance)](#42-etika-riset-kesehatan--izin-komite-etik-ethical-clearance)
5. [Analisis Komprehensif Hukum yang TIDAK BERHUBUNGAN (Out-of-Scope) & Justifikasi Yuridis](#5-analisis-komprehensif-hukum-yang-tidak-berhubungan-out-of-scope--justifikasi-yuridis)
   - 5.1 [UU No. 8 Tahun 1999 tentang Perlindungan Konsumen (UUPK)](#51-uu-no-8-tahun-1999-tentang-perlindungan-konsumen-uupk)
   - 5.2 [UU No. 30 Tahun 2000 tentang Rahasia Dagang (Trade Secret)](#52-uu-no-30-tahun-2000-tentang-rahasia-dagang-trade-secret)
   - 5.3 [UU Perbankan, UU P2SK, & Regulasi Fintech Lending (OJK/BI)](#53-uu-perbankan-uu-p2sk--regulasi-fintech-lending-ojkbi)
   - 5.4 [UU Penyiaran No. 32 Tahun 2002 & UU Pers No. 40 Tahun 1999](#54-uu-penyiaran-no-32-tahun-2002--uu-pers-no-40-tahun-1999)
   - 5.5 [Regulasi Perlindungan Data Asing (EU GDPR, California CCPA, HIPAA)](#55-regulasi-perlindungan-data-asing-eu-gdpr-california-ccpa-hipaa)
6. [Matriks Ketertelusuran Regulasi ke Arsitektur Teknis (Traceability Matrix)](#6-matriks-ketertelusuran-regulasi-ke-arsitektur-teknis-traceability-matrix)
7. [Dilema & Resolusi Hukum dalam Arsitektur Sistem (Case Studies)](#7-dilema--resolusi-hukum-dalam-arsitektur-sistem-case-studies)
   - 7.1 [Konflik Hak Hapus Data (Right to be Forgotten) vs. Integritas Arsip Karya Ilmiah](#71-konflik-hak-hapus-data-right-to-be-forgotten-vs-integritas-arsip-karya-ilmiah)
   - 7.2 [Prinsip Kerahasiaan Peer-Review vs. Keterbukaan Informasi Publik](#72-prinsip-kerahasiaan-peer-review-vs-keterbukaan-informasi-publik)
   - 7.3 [Tanggung Jawab Hukum Terhadap Naskah Plagiasi / Pelanggaran Hak Cipta](#73-tanggung-jawab-hukum-terhadap-naskah-plagiasi--pelanggaran-hak-cipta)
8. [Panduan Strategis Menjawab Evaluasi & Ujian Dosen (Q&A Defense Kit)](#8-panduan-strategis-menjawab-evaluasi--ujian-dosen-qa-defense-kit)
9. [Checklist Kesiapan Kepatuhan (Compliance Action Plan)](#9-checklist-kesiapan-kepatuhan-compliance-action-plan)

---

## Bukti implementasi dan keputusan pengelola

Daftar topik di atas merupakan ruang lingkup evaluasi. Pengesahan regulasi yang berlaku dan keputusan operasional berada pada pengelola.

| Alur | Kontrol dan bukti | Verifikasi pengelola |
|---|---|---|
| Pendaftaran | Formulir menampilkan pemberitahuan dari `config/registration.php`. Transaksi registrasi menyimpan teks, tujuan, versi, waktu, dan pengguna di audit `account_registered`. Lihat `tests/Feature/RegistrationTest.php`. | Sahkan isi pemberitahuan dan dasar pemrosesan per tujuan, naikkan versi setiap kali teks berubah, tetapkan akses serta retensi audit. Bukti historis sebelum perubahan perlu dinilai tersendiri. |
| Penerbitan | `PublicationService` memeriksa lisensi jurnal saat publikasi. Artikel menyimpan dan menampilkan lisensi saat terbit. Lihat `tests/Feature/JournalManager/JournalManagerEndToEndIntegrationTest.php` dan `tests/Feature/Public/PublicJournalPortalTest.php`. | Di halaman detail naskah, penulis utama dapat menyetujui lisensi atau Pemimpin Redaksi jurnal mencatat referensi perjanjian tertulis yang telah diperiksa. Bukti terbaru harus sesuai dengan lisensi ketika artikel diterbitkan. Pengelola menyimpan dokumen perjanjian di arsip terbatas dan meninjau artikel lama yang pernah menerima nilai bawaan. |
| Statistik publik | Pengenal berkunci berlaku untuk satu jam UTC. Scheduler menjalankan `metrics:forget-visitors` tiap jam untuk mengosongkan pengenal dari jendela tertutup dengan mempertahankan jumlah. Lihat `routes/console.php` dan tes portal publik. | Pantau eksekusi scheduler dan tetapkan retensi terpisah untuk access log, cadangan, dan data statistik. |

### Hak pengguna dan insiden

Pengelola perlu menetapkan kanal permintaan, petugas, dan cara memverifikasi identitas pemohon. Untuk tiap permintaan, catat waktu penerimaan, cakupan data, dasar keputusan (termasuk kewajiban arsip), tindakan, waktu penyelesaian dan pemberitahuan kepada pemohon.

Untuk kegagalan pelindungan data pribadi, Pasal 46 mengatur pemberitahuan tertulis kepada subjek data dan lembaga paling lambat 3 × 24 jam. Pengelola perlu menunjuk penanggung jawab dan menguji prosedur pencatatan waktu, identifikasi data terdampak, penjelasan kejadian, penanganan dan pemulihan, serta penyimpanan bukti pengiriman. [UU Nomor 27 Tahun 2022](https://peraturan.bpk.go.id/Download/224884/UU%20Nomor%2027%20Tahun%202022.pdf)

Status operasional kedua prosedur memerlukan SOP yang disahkan dan bukti pelaksanaan atau simulasi.

### Draf prosedur layanan hak pengguna

1. Petugas penerima membuat nomor kasus dan mencatat waktu, jenis permintaan, serta kanal balasan. Simpan rincian pribadi dalam register terbatas. Gunakan nomor kasus ketika membahasnya dengan tim teknis.
2. Verifikasi identitas melalui kanal akun yang sudah terdaftar. Untuk perwakilan, periksa kewenangannya. Catat hasil verifikasi dan batasi pengumpulan dokumen identitas pada kebutuhan kasus.
3. Penanggung jawab menelusuri data akun, profil, naskah, artikel, dan salinan terkait. Untuk artikel terbit, libatkan editor dan petugas arsip sebelum memutuskan koreksi metadata atau tindakan terhadap rekam publikasi.
4. Catat keputusan, dasar, tenggat yang berlaku untuk jenis permintaan, dan pihak yang menyetujui. Pelaksana mencatat perubahan dan pemeriksa memverifikasi hasil sebelum balasan dikirim.
5. Simpan waktu penyelesaian, bukti pengiriman jawaban, serta tindak lanjut terhadap salinan cadangan bila data dipulihkan. Pengelola menetapkan masa simpan register.

### Draf prosedur kegagalan pelindungan data

1. Petugas mencatat waktu kejadian bila diketahui, waktu deteksi, sistem terdampak, dan nomor insiden. Eskalasikan segera kepada penanggung jawab pengendali data.
2. Tim teknis membatasi akses yang terdampak, mengamankan bukti dalam penyimpanan terbatas, dan mencatat tindakan pemulihan. Penanggung jawab mengidentifikasi subjek, jenis data dan dampak, serta mencatat dasar penilaian insiden.
3. Untuk kejadian yang memenuhi Pasal 46, penanggung jawab mengoordinasikan pemberitahuan tertulis sesuai tenggat. Catat data yang terungkap, kapan dan bagaimana pengungkapan terjadi, serta upaya penanganan dan pemulihan. Simpan waktu pengiriman dan tanda terima dari setiap tujuan. Nilai juga kebutuhan pemberitahuan kepada masyarakat sesuai ketentuan yang berlaku.
4. Setelah pemulihan, periksa kembali akses dan layanan terdampak. Catat penyebab, tindakan korektif, pemilik tindakan, dan bukti penyelesaiannya.

Sebelum prosedur diberlakukan, pengelola perlu mengisi kanal resmi, nama serta pengganti petugas, kontak eskalasi, lokasi register terbatas, dan tanggal pengesahan. Simulasikan satu permintaan koreksi profil dan satu insiden menggunakan data fiktif. Simpan hasil simulasi beserta waktu respons aktual.

### Audit lisensi artikel terbit

Jalankan `php artisan publications:audit-licenses` pada database yang akan diperiksa. Keluaran merinci ID jurnal, artikel, dan naskah untuk kasus yang perlu ditinjau. Simpan hasil dalam register terbatas.

- `matched` berarti lisensi artikel cocok dengan bukti terakhir yang tercatat saat terbit. Untuk perjanjian tertulis, pengelola mencocokkan referensi dengan dokumen asli.
- `missing_evidence` mencakup artikel yang belum memiliki bukti digital sebelum atau saat terbit. Jika tabel bukti belum tersedia, semua artikel bertanggal terbit masuk kategori ini. Cocokkan dengan arsip penulis, termasuk artikel yang dilabeli CC BY 4.0.
- `license_mismatch` dan `missing_publication_date` perlu diperiksa oleh pengelola bersama redaksi sebelum memutuskan koreksi.

Pemeriksaan baca-saja pada MySQL Docker lokal pada 19 November 2026 menghasilkan nol artikel berstatus `published`. Tes alur editorial dengan data sintetis menghasilkan satu artikel dengan bukti cocok. Pemeriksaan katalog produksi dan perjanjian aslinya menunggu akses pengelola.

### Hasil simulasi meja insiden SIM-2026-01

Simulasi meja 19 November 2026 memakai skenario fiktif tentang tautan lampiran editorial yang sempat terbuka. Dua profil sintetis terdampak. Peran petugas penerima, tim teknis, dan penanggung jawab dimainkan dalam skenario. Waktu berikut adalah waktu latihan yang dimodelkan dalam WITA.

| Waktu simulasi | Tindakan dan hasil latihan |
|---|---|
| 19 November 09.00 | Petugas menerima laporan dan membuka kasus SIM-2026-01. Untuk latihan, kejadian dan deteksi dianggap bertepatan. Batas 72 jam jatuh pada 22 November pukul 09.00 WITA. |
| 19 November 09.15 | Tim teknis menandai tautan untuk ditutup, membatasi akses, dan mencatat lokasi log serta pengaturan akses yang perlu disimpan di arsip insiden. |
| 19 November 10.00 | Penanggung jawab menetapkan dua subjek fiktif dan data terdampak berupa nama dan email. Dalam skenario, ia memutuskan menyiapkan pemberitahuan kepada subjek dan lembaga dan menandai keputusan pemberitahuan publik sebagai menunggu hasil penelusuran log karena jangkauan akses belum diketahui. |
| 19 November 11.00 | Latihan mensimulasikan dua pengiriman kepada subjek dan satu kepada lembaga. Waktu simulasi 2 jam sejak deteksi, dengan sisa 70 jam. Referensi simulasi dicatat sebagai SIM-N01, SIM-N02, dan SIM-N03. |
| 19 November 11.30 | Tim menutup skenario setelah mencatat pemeriksaan ulang akses dan menugaskan peran teknis sebagai pemilik perbaikan uji akses lampiran. |

Contoh inti pemberitahuan dalam latihan. “Pada 19 November pukul 09.00 WITA, pengaturan tautan lampiran memungkinkan akses tanpa autentikasi terhadap nama dan email. Dalam skenario ini akses ditutup pada pukul 09.15 WITA. Tim memeriksa log dan konfigurasi untuk menilai cakupan kejadian. Hubungi petugas pelindungan data melalui kanal resmi pengelola untuk informasi dan tindak lanjut.”

Latihan selesai pada tingkat simulasi meja. Waktu respons aktual, pengiriman, tanda terima, dan pemulihan sistem perlu dibuktikan dalam latihan bersama petugas yang ditunjuk. Pengelola mengisi kanal, pemilik tindakan, dan lokasi arsip sebelum menjalankannya.
