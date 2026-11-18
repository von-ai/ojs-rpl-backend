# EVALUASI & PEMETAAN KEPATUHAN ASPEK LEGAL (HUKUM) SISTEM
## Platform SIRIDA (Sistem Informasi Riset dan Inovasi Daerah — OJS Modern)
**Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar**

---

> **Status Dokumen**: Laporan Resmi Evaluasi Aspek Legal & Regulasi Sistem Informasi  
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

*(Konten lengkap sama dengan dokumen master pada root `LEGAL_COMPLIANCE_SIRIDA.md`)*
