# Naskah presentasi kepatuhan backend SIRIDA

Durasi sekitar 3 menit. Teks kutipan dibacakan. Rujukan di bawahnya bisa ditaruh di bagian bawah slide.

## Slide 1 — Dari pendaftaran sampai artikel terbit

> Bayangkan seorang penulis baru mendaftar di SIRIDA dan mengirim naskahnya ke jurnal. Sebelum artikel itu muncul di portal publik, ada dua pertanyaan yang perlu dijawab. Apa dasar kita memproses data akun penulis? Dan atas izin siapa jurnal menerbitkan artikelnya? Saya akan menunjukkan bagaimana backend SIRIDA mencatat bukti untuk kedua keputusan itu, lalu bagaimana portal membatasi penyimpanan pengenal pengunjung.

## Slide 2 — Persetujuan saat mendaftar

> Ketika penulis mendaftar, backend menyimpan akun dan bukti persetujuannya dalam satu transaksi. Catatan audit menyimpan siapa yang menyetujui, kapan, beserta tujuan, teks, dan versi pemberitahuan yang berlaku saat itu. Kalau isi pemberitahuan berubah bulan depan, pengelola masih bisa menelusuri teks yang disetujui penulis saat mendaftar. Kontrol ini mendukung pembuktian persetujuan untuk tujuan yang memang menggunakan persetujuan sebagai dasar pemrosesan.

**Di slide:** UU PDP Pasal 20 ayat (2) huruf a, Pasal 21–22, dan Pasal 24. [Naskah resmi UU No. 27 Tahun 2022](https://peraturan.bpk.go.id/Home/Download/224884/UU%20Nomor%2027%20Tahun%202022.pdf) · [Kode registrasi](../app/Services/Identity/RegistrationService.php) · [Tes registrasi](../tests/Feature/RegistrationTest.php)

## Slide 3 — Izin sebelum naskah diterbitkan

> Setelah naskah siap terbit, penulis utama dapat menyetujui lisensi yang dipakai jurnal. Jika izin diberikan melalui perjanjian tertulis, Pemimpin Redaksi dapat mencatat referensi dokumen yang sudah diperiksa. Backend membandingkan bukti terbaru dengan lisensi jurnal. Jika bukti belum ada atau lisensinya berubah, penerbitan tertahan sampai bukti yang sesuai tercatat. Ketika artikel terbit, sistem menyimpan lisensi yang berlaku saat itu. Pengelola tetap perlu memastikan pihak yang memberi izin memang berwenang dan menyimpan perjanjian aslinya.

**Di slide:** UU Hak Cipta Pasal 9 ayat (1) huruf a dan ayat (2) tentang penerbitan sebagai hak ekonomi dan izin. Pasal 80 ayat (1) mengatur lisensi berdasarkan perjanjian tertulis, kecuali diperjanjikan lain. [Naskah resmi UU No. 28 Tahun 2014](https://peraturan.bpk.go.id/Download/28018/UU%20Nomor%2028%20Tahun%202014.pdf) · [Pencatatan izin](../app/Services/PublicationPermissionService.php) · [Pemeriksaan saat terbit](../app/Services/PublicationService.php)

## Slide 4 — Statistik pengunjung dan penutup

> Jurnal perlu tahu berapa kali artikelnya dilihat. Untuk kebutuhan statistik publik, SIRIDA menggunakan pengenal berkunci per jam. Tugas terjadwal mengosongkan pengenal dari jendela waktu yang sudah berakhir, sementara jumlah kunjungannya tetap tersimpan. Satu jam adalah keputusan desain SIRIDA. Pengelola perlu memastikan tugas terjadwal berjalan dan menetapkan retensi log akses serta cadangan secara tersendiri.
>
> Jadi, yang bisa saya tunjukkan dari kode adalah bukti persetujuan akun, pemeriksaan izin terbit, dan pembatasan penyimpanan pengenal statistik. Penilaian kepatuhan secara menyeluruh juga memerlukan bukti operasional, misalnya bagaimana pengelola melayani permintaan hak pengguna dan menangani insiden data pribadi.

**Di slide:** UU PDP Pasal 16 ayat (2) dan Pasal 27 tentang pembatasan dan tujuan pemrosesan. Pasal 46 untuk pemberitahuan kegagalan pelindungan data pribadi. [Naskah resmi UU No. 27 Tahun 2022](https://peraturan.bpk.go.id/Home/Download/224884/UU%20Nomor%2027%20Tahun%202022.pdf) · [Jadwal pembersihan](../routes/console.php) · [Pemetaan operasional SIRIDA](legal-compliance.md)

## Jika penguji bertanya

- **“Kalau pengguna sudah mencentang kotaknya, apakah semua pemrosesan sah?”** Bukti persetujuan itu terkait tujuan yang disampaikan. Dasar pemrosesan harus diperiksa untuk tiap tujuan menurut Pasal 20 UU PDP. [Naskah resmi UU PDP](https://peraturan.bpk.go.id/Home/Download/224884/UU%20Nomor%2027%20Tahun%202022.pdf)
- **“Kalau penulis utama setuju, apakah semua hak cipta otomatis pindah ke jurnal?”** Kontrol ini mencatat izin atas lisensi yang akan dipakai. Kewenangan pemberi izin dan cakupan hak diperiksa dari perjanjian serta fakta kepemilikan. Pengalihan hak dan lisensi diatur masing-masing dalam Pasal 16 dan Pasal 80 UU Hak Cipta. [Naskah resmi UU Hak Cipta](https://peraturan.bpk.go.id/Download/28018/UU%20Nomor%2028%20Tahun%202014.pdf)
