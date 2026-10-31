# Dokumentasi Sistem Desain & Style Guide UI/UX
## Platform SIRIDA — Portal Penulis & Sistem Manajemen Jurnal Ilmiah
**Pemerintah Kota Makassar — Badan Riset dan Inovasi Daerah (BRIDA)**

Dokumen ini berisi spesifikasi sistem desain definitif, tokens CSS tersentralisasi, palet warna semantik alur naskah, tipografi dual-font, serta panduan implementasi komponen antarmuka pengguna (*UI/UX Components*) untuk seluruh ekosistem SIRIDA (`ojs-rpl-backend`).

---

## 1. Arsitektur Tokens & Variabel Desain

Seluruh token warna dan tipografi dikelola secara tersentralisasi pada berkas `resources/css/app.css` memanfaatkan direktif `@theme` Tailwind CSS v4. Hal ini menjamin konsistensi identitas visual dari satu titik kebenaran (*Single Source of Truth*).

### A. Palet Warna Merek Utama (Brand Palette)

| Nama Token Tailwind v4 | Variabel CSS | Nilai Hex | Peruntukan & Hierarki Visual |
| :--- | :--- | :--- | :--- |
| `bg-brand-maroon` | `--color-brand-maroon` | `#6A020A` | **Warna Primer Merek.** Digunakan pada tombol CTA utama, tombol submit naskah, active state sidebar, dan ring fokus formulir. |
| `hover:bg-brand-maroon-hover` | `--color-brand-maroon-hover` | `#520208` | State *hover* interaktif pada tombol primer dan tautan penting. |
| `active:bg-brand-maroon-active` | `--color-brand-maroon-active` | `#3d0106` | State *active / pressed* saat elemen diklik. |
| `bg-brand-maroon-light` | `--color-brand-maroon-light` | `#FFDAD6` | Latar belakang aksen halus untuk notifikasi error validasi dan container penolakan. |
| `text-brand-navy` | `--color-brand-navy` | `#002E72` | **Warna Sekunder Merek.** Identitas aksen publikasi akademik dan header portal OJS. |
| `hover:text-brand-navy-hover` | `--color-brand-navy-hover` | `#001F54` | State *hover* untuk tautan beraksen navy. |
| `bg-canvas` | `--color-canvas` | `#F8F9FA` | Latar belakang canvas utama seluruh halaman aplikasi. |
| `bg-surface-subtle` | `--color-surface-subtle` | `#F7F7F8` | Latar belakang lembut untuk panel informasi dan kolom formulir non-aktif. |
| `bg-white` | Neutral Surface | `#FFFFFF` | Latar belakang kartu kontainer (*card container*), tabel, dan bidang input. |

---

### B. Palet Status Alur Naskah (9 Canonical Submission Statuses)

Setiap tahapan alur penerbitan naskah ilmiah memiliki kode warna semantik terstandardisasi untuk memudahkan penulis mengenali status naskah secara instan:

| Status ERD | Tahap Alur | Utility Badge Tailwind | Hex Preview | Representasi Makna Bisnis |
| :--- | :--- | :--- | :--- | :--- |
| `draft` | Tahap 1 | `bg-gray-100 text-gray-700 border-gray-200` | `#374151` | Naskah tersimpan parsial, belum diserahkan ke dewan editor. |
| `submitted` | Tahap 2 | `bg-amber-50 text-amber-800 border-amber-200` | `#92400E` | Menunggu antrean pemeriksaan awal (*Desk Review*) & Turnitin. |
| `in_review` | Tahap 3 | `bg-sky-50 text-sky-800 border-sky-200` | `#075985` | Sedang dalam proses telaah anonim oleh mitra bestari (*Double-Blind*). |
| `revisions_requested` | Tahap 4 | `bg-orange-50 text-orange-800 border-orange-200` | `#9A3412` | Naskah membutuhkan revisi minor/mayor dan respon dari penulis. |
| `accepted` | Tahap 5 | `bg-emerald-50 text-emerald-800 border-emerald-200` | `#065F46` | Naskah diterima resmi oleh dewan editor untuk diterbitkan. |
| `in_production` | Tahap 5 | `bg-purple-50 text-purple-800 border-purple-200` | `#6B21A8` | Proses tata letak (*layout setting*), proofreading, & pembuatan galai. |
| `published` | Tahap 6 | `bg-green-50 text-green-800 border-green-200` | `#166534` | Artikel terbit daring resmi (*Open Access*) dengan DOI Crossref aktif. |
| `declined` | Terminasi | `bg-red-50 text-red-800 border-red-200` | `#991B1B` | Naskah tidak dapat diterima (*Desk Reject* atau evaluasi reviewer). |
| `archived` | Terminasi | `bg-zinc-100 text-zinc-700 border-zinc-300` | `#3F3F46` | Naskah dibekukan/ditarik (*Withdrawn*) dari alur kerja editorial. |

---

### C. Palet Status Keamanan Berkas & Antivirus (Tabel 56/57 ERD)

| Status Pemindaian | Utility Badge Tailwind | Representasi |
| :--- | :--- | :--- |
| `clean` | `bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200` | Berkas lolos uji pemindaian Microsoft Defender for Storage dan aman diunduh. |
| `pending_scan` | `bg-amber-50 text-amber-700 ring-1 ring-amber-200` | Berkas baru diunggah dan berada dalam antrean pemindaian otomatis. |
| `infected` | `bg-red-50 text-red-700 ring-1 ring-red-200` | Berkas terdeteksi malware/virus, dikarantina otomatis oleh sistem. |

---

## 2. Tipografi & Hierarki Teks (Typography)

Sistem menggunakan konsep **Dual-Font Typography** yang menggabungkan presisi fungsional modern dengan keanggunan publikasi akademik:

```html
<!-- Google Fonts Embed di resources/views/layouts/author.blade.php -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
```

### A. Panduan Penggunaan Font:
1. **Inter (`font-sans`):**
   * Digunakan secara seragam untuk seluruh antarmuka aplikasi: judul artikel ilmiah, paragraf abstrak, label formulir, tombol aksi, badge status, tabel data, navigasi sidebar, breadcrumb, dan notifikasi.
   * Memberikan keterbacaan tinggi, konsistensi modern yang bersih (*clean*), serta bebas disparitas gaya antar-elemen.
   * Dilengkapi optimasi OpenType: `font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11', 'tnum'`. Angka tabular (`tnum`) wajib digunakan pada tanggal, nomor naskah, volume jurnal, counter, dan hash SHA-256 agar tersusun sejajar tanpa pergeseran lebar karakter.
2. **Lora (`font-serif`):**
   * Bersifat spesifik untuk blok sitasi gaya cetak APA (`citation-content-apa`).

### B. Skala Tipografi:
- **Judul Artikel Ilmiah (H1 - Inter)**: `font-sans text-2xl sm:text-[26px] lg:text-[28px] font-bold text-gray-900 leading-tight tracking-tight`
- **Sub-heading Panel (H2 - Inter)**: `font-sans text-base sm:text-lg font-bold text-gray-900 leading-tight`
- **Label Form / Header Kolom**: `text-xs font-bold text-gray-700 uppercase tracking-wider`
- **Paragraf Abstrak & Body Umum**: `font-sans text-xs sm:text-sm text-gray-700 leading-relaxed`
- **Teks Kode / Hash SHA-256**: `font-mono text-[11px] sm:text-xs text-gray-500 tabular-nums`

---

## 3. Komponen Antarmuka Reusable (Blade Components)

Komponen antarmuka dikelompokkan secara hierarkis: **Komponen Global** (dapat dipakai di seluruh peran sistem: Admin, Editor, Reviewer, Author, Reader) diletakkan langsung di `resources/views/components/`, sedangkan **Komponen Spesifik Peran** dikelompokkan ke dalam subdirektori perannya masing-masing (misal `components/author/`):

### A. Komponen Global Aplikasi (`resources/views/components/`)

#### 1. Sistem Notifikasi Toast Global (`<x-toast>`)
- **Lokasi:** `resources/views/components/toast.blade.php`
- **Sintaks Penggunaan di Layout:**
  ```blade
  <x-toast />
  ```
- **Fitur Utama:**
  - Posisi melayang tetap di pojok kanan atas (`fixed top-5 right-5 z-100 pointer-events-none`).
  - Mendukung 4 jenis umpan balik: `success`, `warning`, `error`, dan `info`.
  - Fungsi JavaScript terpusat di objek `window`:
    ```javascript
    window.showToast('Naskah berhasil disimpan sebagai draf.', 'success', 'Berhasil');
    // Atau helper ringkas:
    window.toast.success('Naskah tersimpan.');
    window.toast.error('Gagal memproses data.');
    window.toast.warning('Periksa batas waktu revisi.');
    window.toast.info('Memulai pengunduhan berkas...');
    ```
  - Auto-dismiss cerdas setelah 4 detik dengan animasi *slide-in* dan *fade-out*, serta jeda otomatis saat kursor mouse berada di atas toast (*hover pause*).

#### 2. Modal Konfirmasi Tindakan Global (`<x-action-modal>`)
- **Lokasi:** `resources/views/components/action-modal.blade.php`
- **Sintaks Penggunaan di Layout:**
  ```blade
  <x-action-modal />
  ```
- **Fitur Utama:**
  - Dialog konfirmasi interaktif modular berbasis *Promise* (`async/await`).
  - Latar belakang kabur (`backdrop-blur-xs bg-gray-950/45`) dengan animasi *scale-in*.
  - Aksesibilitas: Menangkap fokus keyboard, menutup pada tombol `Esc`, klik di luar kartu (*click outside*), dan mengembalikan fokus elemen sebelumnya.
  - Sintaks pemanggilan JavaScript:
    ```javascript
    const confirmed = await window.showConfirmModal({
        title: 'Konfirmasi Penarikan Naskah',
        message: 'Apakah Anda yakin ingin menarik naskah ini dari proses telaah editorial?',
        confirmText: 'Ya, Tarik Naskah',
        cancelText: 'Batal',
        type: 'danger' // 'danger' | 'warning' | 'primary' | 'logout'
    });

    if (confirmed) {
        // Eksekusi mutasi / pengiriman formulir
    }
    ```

#### 3. Modal Dialog Kontainer Global (`<x-modal>`)
- **Lokasi:** `resources/views/components/modal.blade.php`
- **Sintaks Penggunaan:**
  ```blade
  <x-modal name="edit-coauthor-modal" title="Edit Data Kontributor" maxWidth="lg">
      <form id="edit-author-form">
          <!-- Bidang isian form kustom -->
      </form>

      <x-slot:footer>
          <button type="button" class="btn-cancel">Batal</button>
          <x-button type="submit">Simpan Perubahan</x-button>
      </x-slot:footer>
  </x-modal>
  ```
- **Props:** `name` (ID unik), `title` (opsional), `maxWidth` (`sm`, `md`, `lg`, `xl`, `2xl`), `show` (boolean).

#### 4. Label Formulir (`<x-label>`)
- **Lokasi:** `resources/views/components/label.blade.php`
- **Sintaks:**
  ```blade
  <x-label for="title" value="Judul Manuskrip Riset" :required="true" />
  ```

#### 5. Input Teks & Formulir (`<x-input>`)
- **Lokasi:** `resources/views/components/input.blade.php`
- **Sintaks:**
  ```blade
  <x-input type="text" name="title" id="title" placeholder="Masukkan judul..." :has-error="$errors->has('title')" />
  ```
- **Fitur:** Ring fokus otomatis menggunakan warna Maroon `focus:ring-brand-maroon focus:border-brand-maroon`.

#### 6. Tombol Utama CTA (`<x-button>`)
- **Lokasi:** `resources/views/components/button.blade.php`
- **Sintaks:**
  ```blade
  <x-button type="submit">
      <svg class="w-4 h-4 mr-2" ...></svg> Kirim Naskah
  </x-button>
  ```
- **Fitur:** Latar Maroon default, transisi hover, state disabled anti-spam submit.

#### 7. Banner Notifikasi & Peringatan (`<x-alert>`)
- **Lokasi:** `resources/views/components/alert.blade.php`
- **Sintaks:**
  ```blade
  <x-alert type="error" title="Validasi Gagal">
      Harap periksa kembali berkas yang Anda unggah.
  </x-alert>
  ```


---

### B. Komponen Khusus Portal Penulis (`resources/views/components/author/`)

Komponen dalam folder ini dikhususkan untuk struktur tata letak (*layout shell*) antarmuka penulis:

#### 1. Sidebar Navigasi Responsif (`<x-author.sidebar>`)
- **Lokasi:** `resources/views/components/author/sidebar.blade.php`
- **Fitur Utama:**
  - **Desktop Collapsible Mode:** Mendukung penciutan sidebar dari lebar 240px (`w-60`) menjadi 68px (`w-17` icon-only) via tombol minimize. Menggunakan transisi CSS halus `transition-all duration-300` dan menyimpan status collapse di `localStorage`.
  - **Mobile Drawer:** Tersembunyi otomatis di layar kecil (`-translate-x-full lg:translate-x-0`) dan muncul sebagai drawer overlay ketika ikon menu ditekan.
  - **Indikator Menu Aktif:** Menu "Naskah Saya" ditandai dengan latar `bg-brand-maroon text-white shadow-2xs`.
  - **Scrollbar Halus:** Menggunakan utility kustom `.sidebar-scroll` dengan lebar 4px transparan.

#### 2. Top Navigation Bar (`<x-author.header>`)
- **Lokasi:** `resources/views/components/author/header.blade.php`
- **Fitur Utama:**
  - Sticky header (`sticky top-0 z-20`) dengan batas pemisah `border-b border-gray-200`.
  - Tombol hamburger mobile dengan target sentuh aman (44 × 44 piksel).
  - Breadcrumb navigasi hierarkis.
  - Lonceng notifikasi dengan titik merah (*badge indicator*) untuk pemberitahuan yang belum dibaca.
  - User profile pill dengan avatar inisial berwarna maroon dan afiliasi institusi.

---

## 4. Standar UI/UX Defensif & Aksesibilitas

Setiap antarmuka naskah wajib menerapkan aturan defensif berikut:

1. **Pencegahan Teks Meluap (Anti-Broken Layout):**
   * Seluruh judul naskah, abstrak, dan email panjang wajib menggunakan kombinasi:
     ```html
     class="break-words [overflow-wrap:anywhere]"
     ```
2. **Elemen yang Tidak Boleh Menciut (No-Shrink Protection):**
   * Seluruh badge status, tombol download berkas, dan ikon status wajib diberi class `shrink-0` agar bentuknya tetap sempurna di layar sempit.
3. **Ukuran Target Sentuh (Touch Target Size):**
   * Seluruh tombol, switch, dan tautan interaktif memiliki ukuran minimal **44 × 44 piksel** (memenuhi kriteria WCAG 2.1 Success Criterion 2.5.5).
4. **Sanitasi Konten Dinamis (Zero-XSS):**
   * Catatan editor dan teks umpan balik disanitasi menggunakan helper bawaan:
     ```php
     {!! nl2br(e(str_replace(['<br>', '<br/>', '<br />'], "\n", $comment))) !!}
     ```
5. **Privasi Double-Blind:**
   * Identitas peninjau, email reviewer, dan skor evaluasi rahasia diisolasi di sisi server pada ViewModel dan tidak pernah dirender ke DOM HTML penulis.
