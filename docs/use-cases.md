# SIRIDA — use case dan penerimaan kontrak 1.0.0

**Update implementasi 8 Oktober 2026:** register, verifikasi email dan kirim ulang
sudah tersedia sebagai MVP. Lihat [panduan registrasi](registration.md). Pernyataan
planned/scaffold dalam baseline historis di bawah tetap berlaku untuk fitur lain;
login, membership dan workflow editorial belum dibangun.

Ini baseline kriteria penerimaan (acceptance criteria) use case untuk implementasi Modular Monolith Laravel 13.
Sumber UC adalah [proposal](https://docs.google.com/document/d/1bWldGLdB9Ek2bgmJtyupGi5yA7C1E409w6Gs65p__OM/)
tab Proposal. Aturan bisnis domain mengacu pada [aturan domain](contract-decisions.md).

Istilah: EIC = Editor in Chief, SE = Section Editor, JM = Journal Manager.
Semua aktor privat harus memenuhi verifikasi, membership aktif dan object policy;
platform administrator memakai system role. Reviewer hanya melihat proyeksi blind.
Setiap aksi mengubah state harus memeriksa ulang akses di server, bukan berdasarkan
status tombol UI. Skenario gagal tidak boleh meninggalkan perubahan domain parsial.

| UC / aktor / prasyarat | Alur berhasil dan hasil | Alternatif/gagal yang harus diuji |
| --- | --- | --- |
| UC-01 Registrasi — calon pengguna, journal terbuka | Form pendaftaran tanpa pilihan role → verifikasi email → akses dasar Reader/Author; gabung jurnal untuk enrollment jurnal tambahan. Reviewer direkrut melalui undangan editor untuk naskah dan putaran tertentu. | Duplikasi email/username ditolak; token verifikasi kedaluwarsa tidak aktif; pendaftaran menolak pemilihan role dan journal; role direktori reviewer tidak memberi akses naskah tanpa assignment yang diterima. |
| UC-02 Login — identity terdaftar | Login web form menghasilkan sesi stateful pengguna; pemilihan konteks jurnal/role yang sudah dimiliki. Logout membatalkan sesi dan regenerasi token CSRF. | Lima kali kegagalan mengunci akun 15 menit lintas alias email/username dan menerbitkan outbox email keamanan; sesi kadaluwarsa diarahkan ke login; konteks jurnal palsu ditolak. |
| UC-03 Membaca/mengunduh — pembaca | Daftar journal/current issue → artikel published → citation/galley, download/view dicatat dengan deduplication. | Draft/tidak berhak/missing file tidak bocor; DOI pending tetap menampilkan artikel terbit; file milik artikel lain ditolak. |
| UC-04 Pencarian — pembaca | Pencarian publik memfilter metadata artikel published dengan pagination dan pencarian aman. | Hasil kosong menampilkan pesan informatif, bukan error; query tidak membuka naskah privat atau menjalankan SQL mentah. |
| UC-05 Submission baru — author aktif | Policy/section → draft parsial → contributors/metadata lengkap → upload clean → `submitSubmission`, status submitted dan notifikasi EIC. | Draft tetap tersimpan saat metadata belum lengkap; file terlalu besar/scan gagal/salah journal ditolak; author guidelines wajib; similarity opsional tidak menghalangi saat nonaktif. |
| UC-06 Tracking — author pemilik | `listSubmissions`, detail, timeline dan file history menampilkan progres yang aman untuk author. | Author lain mendapat 404; timeline/notifikasi tidak memuat identitas reviewer atau catatan rahasia; pagination tidak mencampur journal. |
| UC-07 Revisi — author dengan keputusan revisi terkirim | Baca `listAuthorDecisions`, buat revision, upload manuskrip/response letter, submit revisi yang terikat keputusan; editor menerima notifikasi. | Revision draft sebelum kirim boleh belum lengkap; deadline/grace sesuai snapshot; stale job tidak menarik revisi yang sudah masuk; major revision dapat membuka round baru. |
| UC-08 Undangan — reviewer yang ditugaskan | Preview aman → accept/decline sebelum response deadline; accept membuka workspace; extension diminta dan diputuskan editor. | Saat deadline tercapai menjadi no_response dan eskalasi SE; undangan lama terminal; request extension sendiri tidak memperpanjang waktu. |
| UC-09 Evaluasi — reviewer accepted, round terbuka | Baca pinned manuscript/rubric → save draft parsial sebelum deadline → `submitReview` lengkap; review immutable, assignment completed, notifikasi EIC. | Overdue mempertahankan draft read-only, eskalasi SE, menunggu approved extension; nilai tidak valid/konflik kepentingan ditolak; author tidak membaca confidential comments. |
| UC-10 Desk review — EIC, submission submitted | Buat assessment → pass dan assign SE, atau reject, atau minta completion. Ethics escalation diteruskan ke JM untuk investigasi. | Author melengkapi record incomplete melalui command khusus; history dipertahankan; ethics hanya aktor terkait, bukan author/reviewer umum. |
| UC-11 Assign reviewer — SE/EIC, round eligible | Pilih reviewer aktif tanpa konflik → pinned round → batch invitation dengan tanggal valid; submission under_review. | Satu invitation invalid menggagalkan batch; duplicate live assignment/self-review ditolak; cancellation hanya invited; pengganti no_response mendapat ID baru. |
| UC-12 Keputusan — EIC | Ringkasan review → draft decision → send setelah threshold terpenuhi; author menerima feedback anonim dan deadline bila revisi. | Override minimum review/keputusan memerlukan reason dan audit; sent decision tidak diedit biasa; decision revisi tanpa due date ditolak; accept/reject tidak memerlukan due date revisi. |
| UC-13 Copyediting — Copyeditor ditunjuk EIC | Buat/update output set → request author approval → author approve checksum seluruh set; lanjut layout. | Author meminta changes dengan alasan; perubahan bytes membatalkan approval; discrepancy/mediation lewat production escalation, tanpa melompati persetujuan. |
| UC-14 Layout — Layout Editor ditunjuk EIC | Buat galley draft → proof cycle UC-18 → final version dengan predecessor → EIC final approval; ready article ID tersedia. | Final tanpa proof predecessor ditolak; file/checksum berubah membatalkan approval; upload galley memakai submission yang sudah ada. |
| UC-15 Publikasi — editor yang diizinkan policy | Susun non-empty issue dari ready articles satu journal → publish sekarang/jadwalkan → atomic published + outbox, pilih current issue eksplisit. | Jadwal recheck gagal menjaga unpublished dengan diagnosis; Crossref retry memakai DOI sama; issue kosong/galley belum approved/duplicate article tidak diterbitkan. |
| UC-16 Pengguna — JM; global deletion system admin | Membership CRUD/roles/import, pengelolaan direktori reviewer dan reset email dilakukan dalam scope journal. | JM tidak mengubah identity global/password/system role; active work dan last-admin/EIC guards berlaku; import per-row tidak menyelundupkan privilege; deletion berupa tombstone dengan history retained. |
| UC-17 Konfigurasi — system admin/JM sesuai resource | Read version → update If-Match; integration candidate immutable → test → apply; rollback non-secret menjadi version baru. | Candidate kedaluwarsa/belum passed/tidak sama credential version ditolak; arbitrary host/raw secret ditolak; rollback tidak menghidupkan credential lama; JM tidak mengakses system config. |
| UC-18 Proofreading — Proofreader ditugaskan | Review galley version/checksum → notes → Layout Editor koreksi → Proofreader resolve → approve proof. | Hanya proofreader ditugaskan menyelesaikan notes; mismatch copyedit dieskalasi untuk klarifikasi; return ke copyediting membatalkan downstream approval dan memerlukan siklus baru. |

Scope administrasi tambahan §1.3: CRUD section, halaman journal, announcement dan
email template dengan draft/published projection; template hanya event/placeholder
allowlist, tidak mengubah pesan autentikasi/security. OAI GET/form POST, Dublin Core,
ORCID, audit dan notifikasi mendukung UC/NF yang dipetakan dalam setiap operation.
Pembayaran/APC tetap di luar lingkup.

## Mesin waktu review (normatif)

`now` diambil dari database UTC **setelah memperoleh lock**. Bandingkan instant,
bukan string/timezone. Pada equality deadline sudah berakhir. Jadwal worker setiap
60 detik mematerialisasi expiry; write guard dan read projection wajib menghormati
waktu walau worker macet. Jangan menyimpan `accepted` ke response ketika secara
efektif sudah overdue.

| Kondisi terkunci | Hasil atomik | Notifikasi / izin berikutnya |
| --- | --- | --- |
| invited dan now ≥ response_due_at | no_response; increment version; audit + outbox | SE penanggung jawab, fallback EIC; accept/decline ditolak. Undangan baru memakai ID baru. |
| accepted, belum completed, now ≥ review_due_at | overdue; increment version; audit + outbox | SE dan reviewer; fallback editor EIC. Draft/manuscript retained read-only; dapat request extension. |
| Extension pending, assignment accepted/overdue, round terbuka, requested date > now dan current deadline | approve → new due_at, accepted; bump assignment dan extension version; audit old/new | Reviewer diberi tahu; draft/save/submit dapat dilanjutkan sebelum deadline baru. |
| Extension declined | deadline tetap, status efektif tetap dihitung | Reviewer diberi alasan; tidak memperoleh tambahan waktu. |
| Review complete sebelum deadline, payload valid | immutable review + completed assignment + audit/outbox | EIC diberi tahu; job deadline lama menjadi no-op. |

Lock assignment terlebih dahulu lalu child draft/extension; transaksi lain memakai
urutan yang sama. Outbox unique `(assignment_id, event, deadline_version)` mencegah
eskalasi ganda. Worker lama memeriksa versi/deadline terkini dan tidak menimpa hasil
extension/submit. Round yang telah ditutup menolak draft/submit/extension walau
assignment status belum terminal; expired/no_response/declined/cancelled tidak
memperoleh akses manuskrip baru. Tidak ada HTTP endpoint publik untuk menjalankan cron.

Notifikasi durable dibuat satu kali; kegagalan SMTP diretry worker dan tidak
membatalkan transisi. UI dapat membaca notifikasi melalui query database / Livewire selama email tertunda.
Kebijakan deadline ditegakkan pada lapisan Application Service dan background worker scheduler.

## Tes penerimaan wajib saat backend dibangun

- Fake clock: sebelum/tepat/setelah kedua deadline, termasuk timezone offset ekuivalen.
- Worker tertunda: GET tetap melaporkan effective expired/overdue; late write ditolak.
- Race: accept vs expiry, submit vs expiry, extension vs expiry/submit; tepat satu
  hasil domain, tidak ada double notification atau draft hilang.
- Replay setelah sukses: key/payload sama tidak menghasilkan efek ganda; authorization
  yang sudah dicabut tidak bisa membaca replay; key berbeda payload menghasilkan conflict.
- Multi-journal dan multi-role: uji object ID, file download, list, history, SSE dan
  email agar scope/privacy sama; role gabungan tidak membocorkan reviewer ke author.
- PostgreSQL: foreign key, unique constraint, transaction rollback, ETag concurrent
  update. SQLite scaffold saja tidak memadai untuk membuktikan perilaku ini.
- Integration: storage/scan/SMTP failure, ORCID denial/state replay, Crossref retry,
  OAI XML harvesting; setiap provider mempunyai sandbox/fixture.

CI pipeline sekarang memvalidasi kualitas kode (Pint style checker) dan feature test suite PHPUnit.
Tes integrasi penuh di atas merupakan pekerjaan implementasi berikutnya seiring pembangunan modul.
