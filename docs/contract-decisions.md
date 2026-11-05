# SIRIDA — Aturan Domain & Keputusan Alur Bisnis (Invariants)

Dokumen ini mendefinisikan aturan bisnis (*business invariants*), batasan privasi, otorisasi peran, dan alur kerja editorial jurnal ilmiah SIRIDA dalam arsitektur **Modular Monolith Laravel 13 (Blade + ViewModel Pattern OOP)**. Aturan ini melengkapi kebutuhan use case SRS (UC-01–18) dan wajib ditegakkan pada lapisan Eloquent Models, Form Requests, dan Application Services/Policies.

---

## 1. Otorisasi dan Batasan Peran Pengguna

1. **Pemisahan Peran Sistem dan Jurnal:**
   * **Peran Sistem (`SystemRole`):** `system_administrator` dikelola secara global dan tidak terikat pada satu jurnal tertentu. Administrator sistem tidak secara otomatis menjadi pengelola jurnal (*Journal Manager*) tanpa penugasan keanggotaan.
   * **Peran Jurnal (`JournalRole`):** `journal_manager`, `editor_in_chief`, `section_editor`, `copyeditor`, `layout_editor`, `proofreader`, `author`, `reader`. Hak akses jurnal hanya berlaku dalam konteks jurnal tempat keanggotaan (*journal membership*) terdaftar.
2. **Pendaftaran Akun (UC-01):**
   * Pengguna mendaftar tanpa memilih peran (*role*).
   * Verifikasi email mengaktifkan akun (`status = active`) dan memberikan akses dasar sebagai `reader` / `author`.
   * Penulis dapat bergabung ke jurnal yang membuka pendaftaran mandiri (*open enrollment*).
   * Direktori reviewer dikelola oleh *Journal Manager*; keanggotaan direktori hanya menentukan kelayakan diundang, bukan akses otomatis ke naskah jurnal.
3. **Perekrutan Reviewer (UC-08/11):**
   * Reviewer ditugaskan oleh Editor secara eksplisit per naskah dan per putaran (*round*) review.
   * Reviewer hanya memperoleh hak akses ke naskah anonim setelah secara tegas menerima undangan (*accept invitation*) sebelum batas waktu respon.
   * Tidak ada mekanisme pendaftaran mandiri menjadi reviewer pada formulir registrasi publik.
4. **Perlindungan Identitas:**
   * Journal Manager tidak diizinkan mengubah kata sandi, email, atau nama global pengguna lain. Fitur reset oleh manajer memicu email token reset standar ke email pemilik akun.

---

## 2. Batasan Privasi (Double-Blind Peer Review)

1. **Anonimitas Reviewer & Penulis:**
   * Alur penilaian menggunakan skema **Double-Blind Review**: penulis tidak mengetahui identitas reviewer, dan reviewer tidak mengetahui identitas penulis.
   * Reviewer hanya dapat mengakses berkas naskah yang telah dianonimkan (*anonymized manuscript file*).
   * Catatan internal editor dan komentar rahasia reviewer (*confidential comments for editor*) tidak boleh diproyeksikan ke penulis. Penulis hanya membaca keputusan resmi dan komentar publik yang telah disetujui editor.
2. **Visibilitas Berkas:**
   * Unduhan berkas dilindungi oleh authorization policy Laravel berdasarkan keterikatan penugasan pengguna pada naskah terkait.

---

## 3. Alur Kerja Editorial & Keadaan Transisi

1. **Rubrik Penilaian Review (UC-09):**
   * Rubrik penilaian menggunakan skala integer 1–5 dengan aspek wajib: **Originalitas** dan **Metodologi**, serta aspek pendukung opsional (Kejelasan, Signifikansi).
   * Formulir review mendukung penyimpanan draft sementara sebelum penyerahan final (*submit evaluation*). Setelah diserahkan, evaluasi bersifat *immutable* (tidak dapat diubah).
2. **Tenggat Waktu & Mesin Waktu Review:**
   * Batas waktu (*deadline*) dihitung berdasarkan jam database UTC setelah penguncian transaksi.
   * Jika tenggat waktu respon terlampaui (`now >= response_due_at`), status penugasan secara otomatis menjadi `no_response` dan dieskalasikan ke Editor.
   * Reviewer yang terlambat (`now >= review_due_at`) hanya memiliki akses *read-only* terhadap draft evaluasinya sampai permohonan perpanjangan waktu (*extension*) disetujui oleh Editor.
3. **Revisi Penulis (UC-07):**
   * Keputusan revisi dari EIC wajib menyertakan tenggat waktu pengumpulan revisi.
   * Sistem tidak melakukan penarikan naskah (*auto-withdraw*) otomatis kecuali manajer jurnal secara tegas mengaktifkan kebijakan tenggat masa tenggang (*grace period*).
4. **Copyediting, Layout, & Proofreading (UC-13, UC-14, UC-18):**
   * Copyeditor ditunjuk oleh EIC. Persetujuan penulis (*author approval*) mengunci seluruh berkas hasil copyedit berdasarkan checksum.
   * Layout Editor memproduksi galai (*galley*). Proofreader memeriksa galai final dan menyelesaikan catatan koreksi (*proofreading notes*).
   * EIC menyetujui galai final sebelum artikel dinyatakan siap terbit (*ready for publication*).
5. **Publikasi Isu Jurnal (UC-15):**
   * Penerbitan isu mewajibkan nomor isu memiliki minimal satu artikel siap terbit dengan berkas PDF final yang telah disetujui.
   * Penerbitan memicu pencatatan transaksi atomik dan mendaftarkan job antrean outbox untuk deposit DOI Crossref. Kegagalan pihak ketiga (Crossref) tidak membatalkan status publikasi artikel di portal jurnal.

---

## 4. Kebijakan Berkas & Unggahan

1. **Batas Ukuran:** Maksimal **20 MiB** (20.971.520 byte) per berkas.
2. **Format Berkas yang Diizinkan:**
   * Naskah utama dan bukti pendukung: PDF, DOC, DOCX.
   * Galai publikasi: PDF (wajib), HTML/XML (pendamping).
3. **Penyimpanan & Keamanan:**
   * Berkas disimpan pada Azure Blob Storage privat (atau Azurite pada dev lokal).
   * Pemindaian malware near real-time oleh Microsoft Defender for Storage. Berkas berstatus karantina dilarang diakses atau diproses lebih lanjut dalam alur editorial.
