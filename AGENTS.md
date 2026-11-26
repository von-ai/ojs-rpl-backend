# Instruksi untuk agent SIRIDA

## Pemakaian skill

Sebelum mulai, cocokkan tugas dengan katalog skill yang tersedia di agent. Baca `SKILL.md` untuk setiap skill yang diminta pengguna atau relevan dengan pekerjaan. Gabungkan skill menurut tahap, misalnya diagnosis untuk menemukan penyebab, Ponytail untuk implementasi, lalu PR untuk menulis deskripsi. Terapkan petunjuk pengguna dan aturan repo pada hasilnya.

Gunakan nama skill dari katalog agent. Jika ada alias, periksa deskripsinya. Jika skill tidak tersedia, sebutkan keterbatasannya dan gunakan aturan repo di bawah untuk pekerjaan yang bisa dilanjutkan. Gunakan skill dokumen, spreadsheet, Figma, riset akademik, atau alat lain yang tersedia saat format atau tugasnya sesuai.

### Matt Pocock

| Pekerjaan | Skill |
| --- | --- |
| Bug, CI gagal, atau regresi performa | `diagnosing-bugs` |
| Implementasi dari spesifikasi atau tiket | `implement-spec` atau `implement` bila tersedia |
| Pengembangan test-first atau tes integrasi | `tdd` |
| Review branch atau PR terhadap standar dan spesifikasi | `code-review` |
| Desain modul, batas interface, atau perbaikan arsitektur | `codebase-design` atau `improve-codebase-architecture` |
| Istilah domain, glossary, atau keputusan arsitektur | `domain-modeling` |
| Uji asumsi rencana atau spesifikasi dengan pengguna | `grilling`, `grill-me`, atau `grill-with-docs` sesuai tugas |
| Prototipe untuk menjawab pertanyaan desain | `prototype` |
| Riset teknis berbasis sumber primer | `research` |
| Susun spesifikasi, pecah pekerjaan, atau triase isu | `to-spec`, `to-tickets`, atau `triage` sesuai hasil yang diminta |
| Langkah manual yang hanya bisa dilakukan pengguna | `wizard` |
| Handoff, panduan agent, atau deskripsi PR | `handoff`, `writing-for-agents`, atau `pr` sesuai hasil yang diminta |

### Ponytail dan antislop

- **Ponytail.** Gunakan untuk menulis atau mengubah kode jika tersedia. Pilih solusi paling sederhana yang memenuhi kebutuhan dan menangani jalur gagal. Utamakan pola repo, alur kontrol langsung, dan nama yang bisa dipahami. Tambah helper, layer, atau dependensi jika ada kebutuhan konkret yang dapat dijelaskan dari pemanggilnya.
- **Unslop dan antislop.** Gunakan skill anti-slop sesuai jenis hasilnya. Pada katalog antislop, mulai dari `antislop`. Gunakan `antislop-code` untuk meninjau komentar pada kode yang disentuh, dan `antislop-copywriting` untuk teks pengguna. Untuk UI, gunakan `antislop-ui`, `antislop-human`, dan `antislop-layoutmobile` sesuai cakupan perubahan.
- **Saat skill tidak tersedia.** Pertahankan komentar yang menjelaskan alasan, batasan, atau perilaku yang tidak terlihat dari kode. Gunakan label UI yang membantu pengguna bertindak. Periksa keyboard, fokus, tampilan ponsel, serta keadaan loading, kosong, dan gagal pada alur yang diubah.

## Audit dependensi dan CI

1. Buka log job yang gagal. Catat langkah pertama yang gagal, exit code, dan pesan lengkap. Pisahkan langkah yang gagal dari langkah yang dilewati. Gunakan `.github/workflows/validate.yml` sebagai acuan perintah dan lingkungan CI.
2. Untuk temuan Composer atau npm, catat paket, versi di lockfile, ID advisory, serta versi patch dari sumber resmi. Bandingkan lockfile branch dengan `origin/main` untuk menentukan apakah temuan diwarisi atau diperkenalkan oleh perubahan baru.
3. Perbarui hanya paket terdampak dan dependensi yang diperlukan untuk resolusi. Mulai dengan pembaruan terarah. Jika versi patch terhalang constraint, gunakan `composer why-not <paket> <versi>` atau `npm explain <paket>` untuk mencari penyebabnya. Periksa diff manifest dan lockfile sebelum melanjutkan.
4. Pertahankan gate audit, batas coverage, dan tes yang berlaku. Jika audit perlu dipisah agar hasil tes tetap tersedia saat audit gagal, pastikan keduanya tetap wajib lulus sebelum merge. Pertahankan kredensial produksi di penyimpanan rahasia dan gunakan nilai khusus tes untuk fixture.
5. Jalankan ulang audit terkait dan verifikasi jalur yang terdampak. Sebagai contoh, audit pada 26 November 2026 menandai `symfony/http-kernel`, `symfony/mailer`, dan `symfony/routing` pada lini 8.1 di bawah 8.1.12. Untuk run lain, ikuti advisory dan lockfile yang berlaku saat itu.

## Konsistensi kode

- Baca `docs/architecture.md` untuk struktur aplikasi, `docs/style_guide.md` untuk konvensi antarmuka, dan `docs/ci-cd.md` untuk alur rilis. Mulai dari implementasi dan tes fitur sejenis di modul yang sama.
- Gunakan kembali model, service, ViewModel, middleware, dan komponen yang sudah memenuhi kebutuhan. Ikuti penamaan dan pola validasi yang berlaku di modul tersebut.
- Untuk perubahan portal, periksa konteks jurnal, role aktif, kepemilikan naskah, dan status workflow di entry point normal. Jika lookup perlu keluar dari scope jurnal aktif, pastikan akses ke jurnal tujuan tetap diperiksa sebelum data ditampilkan atau diubah.
- Batasi diff pada tujuan tugas. Pertahankan perilaku pemanggil dan data lama. Tambahkan tes regresi bila perubahan belum tercakup, terutama untuk akses lintas jurnal, transisi status, dan jalur gagal.
- Ketika membuat branch baru untuk dipush atau membuka PR, gunakan `<jenis>/<deskripsi-singkat>` sesuai perubahan. Contoh `fix/portal-cache`, `feat/journal-export`, `docs/agent-workflow`, atau `chore/update-dependencies`. Jangan gunakan awalan `codex/` atau `agent/`.
- Pakai branch kerja terpisah ketika menyiapkan PR. Periksa commit dan file terhadap `origin/main` agar perubahan lokal yang tidak terkait, kredensial, dan seeder akun produksi tidak terbawa.
- Pecah pekerjaan menjadi commit kecil yang terfokus (*atomic commits*) untuk setiap perubahan yang berarti—misalnya satu fitur kecil, satu perbaikan bug, satu penyesuaian komponen UI, penambahan tes regresi, atau pembaruan dependensi secara terpisah—jangan menumpuk beberapa tujuan berbeda ke dalam satu commit besar. Sebaliknya, jangan membuat commit untuk perubahan remeh yang belum berarti (seperti perubahan sementara, eksperimen setengah jadi, atau kode yang gagal lolos lint/tes). Setiap commit harus berdiri sendiri dalam kondisi rapi dan memakai pesan konvensional yang jelas (`feat(...)`, `fix(...)`, `refactor(...)`, `test(...)`, `docs(...)`, `chore(...)`).

## Verifikasi dan laporan

- Untuk PHP, jalankan `composer validate --strict`, `vendor/bin/pint --test`, dan `composer test` sesuai lingkup perubahan. Untuk frontend, jalankan `npm ci --ignore-scripts` dan `npm run build`. Untuk dependensi, jalankan `composer audit` dan/atau `npm audit --omit=dev` sebagaimana berlaku di CI.
- Untuk perubahan DB, penyimpanan, atau image, ikuti job MySQL/Azurite atau validasi container di workflow. Laporkan tahap yang belum bisa dijalankan beserta alasannya.
- Sebelum selesai, cek `git diff --check` dan diff terhadap base. Laporkan penyebab, perubahan, hasil verifikasi aktual, dan status CI. Sertakan tautan PR jika sudah dibuka.

## Pull request

- Saat menyiapkan PR, baca skill `pr`. Tulis judul dan deskripsi PR dalam bahasa Indonesia. Isi deskripsi PR cukup dengan ringkasan singkat perubahan dan tujuannya.
- Pertahankan nama paket, identifier kode, nama branch, perintah, dan kutipan log sesuai aslinya.
