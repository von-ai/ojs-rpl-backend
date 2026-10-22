# Dokumentasi Sistem Desain & Style Guide UI/UX
## Portal Penulis — BRIDA Kota Makassar (`ojs-rpl-backend`)

Dokumen ini berisi spesifikasi sistem desain, tokens CSS tersentralisasi, gaya visual, serta panduan penggunaan komponen antarmuka pengguna (UI/UX) pada aplikasi `ojs-rpl-backend`.

---

## 1. Arsitektur Tokens & Variabel Desain

Seluruh token warna dan tipografi dikelola secara tersentralisasi pada berkas [`resources/css/app.css`](../resources/css/app.css) memanfaatkan direktif `@theme` Tailwind CSS v4. Hal ini memungkinkan perubahan identitas visual dari 1 tempat (*Single Source of Truth*).

### A. Palet Warna (Color Palette Tokens)

| Nama Token Tailwind v4 | Variabel CSS | Nilai Hex | Peruntukan & Hierarki Visual |
| :--- | :--- | :--- | :--- |
| `bg-brand-maroon` | `--color-brand-maroon` | `#6A020A` | Warna utama aplikasi. Digunakan pada tombol CTA utama, teks header brand, dan batas fokus input. |
| `hover:bg-brand-maroon-hover` | `--color-brand-maroon-hover` | `#520208` | State hover pada tombol utama dan tautan penting. |
| `active:bg-brand-maroon-active` | `--color-brand-maroon-active` | `#3d0106` | State pressed/active saat tombol diklik. |
| `bg-brand-maroon-light` | `--color-brand-maroon-light` | `#FFDAD6` | Latar belakang notifikasi peringatan/error validasi. |
| `text-brand-navy` | `--color-brand-navy` | `#002E72` | Identitas aksen sekunder Portal Penulis (OJS). |
| `hover:text-brand-navy-hover` | `--color-brand-navy-hover` | `#001F54` | State hover untuk tautan beraksen navy. |
| `bg-canvas` | `--color-canvas` | `#F8F9FA` | Warna latar belakang canvas utama aplikasi. |
| `bg-surface-subtle` | `--color-surface-subtle` | `#F7F7F8` | Latar belakang lembut untuk panel informasi/sisi kiri. |
| `bg-white` | Surface Neutral | `#FFFFFF` | Latar belakang kartu kontainer dan bidang input. |

---

## 2. Tipografi & Hierarki Teks (Typography)

Aplikasi menggunakan font **Instrument Sans** dari Google Fonts yang diimpor pada layout utama [`resources/views/layouts/auth.blade.php`]:

```html
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
```

### Skala Tipografi:
- **Heading Utama (H1)**: `text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-gray-900`
- **Sub-heading (H2)**: `text-2xl sm:text-[26px] font-bold text-gray-900 leading-tight`
- **Label Form**: `text-xs font-bold text-gray-800 uppercase tracking-wider`
- **Teks Isian Input**: `text-xs sm:text-sm text-gray-900`
- **Pesan Error / Hint**: `text-xs text-red-600 font-medium`

---

## 3. Komponen UI Reusable (Blade Components)

Pengembangan antarmuka menggunakan komponen Blade modular di `resources/views/components/` untuk menjamin konsistensi dan kemudahan *maintenance*:

### A. Label Form (`<x-label>`)
- Berkas: [`resources/views/components/label.blade.php`]

### B. Input Field (`<x-input>`)
- Berkas: [`resources/views/components/input.blade.php`]

### C. Tombol Utama CTA (`<x-button>`)
- Berkas: [`resources/views/components/button.blade.php`]

### D. Banner Alert Notification (`<x-alert>`)
- Berkas: [`resources/views/components/alert.blade.php`]

---

## 4. Pola Interaksi Micro-UI (JavaScript & States)

1. **Toggle Password Visibility (Show/Hide Password)**
2. **Indikator Loading Submit (Anti-Double Submit)**
3. **Responsive Split-Layout Grid**
