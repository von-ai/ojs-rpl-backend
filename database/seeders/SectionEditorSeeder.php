<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DeskReview;
use App\Models\EditorAssignment;
use App\Models\EditorialDecision;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Review;
use App\Models\ReviewAssignment;
use App\Models\ReviewExtension;
use App\Models\ReviewRound;
use App\Models\Revision;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionContributor;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserRole;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SectionEditorSeeder extends Seeder
{
    /**
     * Jalankan seeding data komprehensif alur kerja Editor Bagian (Section Editor).
     *
     * Menyediakan data realistis untuk semua skenario:
     * 1. Sedang Ditelaah (Putaran telaah aktif dengan mitra bestari).
     * 2. Permintaan Perpanjangan Waktu (Review extension berstatus pending yang dapat disetujui/ditolak).
     * 3. Review Selesai (Kuorum telaah 2/2 terpenuhi, siap diajukan rekomendasi ke Pemimpin Redaksi).
     * 4. Menunggu Revisi (Keputusan revisi telah dikirim dan berkas revisi diunggah oleh penulis).
     * 5. Putaran Kedua (Multi-round review pasca revisi).
     * 6. Rekomendasi Terkirim (Rekomendasi Section Editor sudah diajukan ke Pemimpin Redaksi).
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo seeder tidak boleh dijalankan pada production.');
        }

        // 1. Inisialisasi Jurnal Default SIRIDA Makassar
        $journal = Journal::firstOrCreate(
            ['slug' => 'sirida-makassar'],
            [
                'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'issn' => '2985-7821',
                'eissn' => '2985-7821',
                'status' => 'active',
                'version' => 1,
            ]
        );

        // 2. Inisialisasi Seksi Jurnal
        $sections = Section::where('journal_id', $journal->id)->get()->keyBy('slug');
        $secRiset = $sections->get('sec-riset-orisinil') ?? Section::first();
        $secKebijakan = $sections->get('sec-kajian-kebijakan') ?? $secRiset;
        $secInovasi = $sections->get('sec-studi-kasus') ?? $secRiset;

        // 3. Inisialisasi Seluruh Akun Section Editor & Mitra Bestari
        $seNasaruddin = User::firstOrCreate(
            ['email' => 'nasaruddin.eic@unhas.ac.id'],
            [
                'username' => 'nasaruddin.eic',
                'name' => 'Prof. Dr. Ir. H. Nasaruddin, M.T.',
                'full_name' => 'Prof. Dr. Ir. H. Nasaruddin, M.T.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $seHamka = User::firstOrCreate(
            ['email' => 'prof.hamka@unhas.ac.id'],
            [
                'username' => 'prof.hamka',
                'name' => 'Prof. Hamka, Ph.D.',
                'full_name' => 'Prof. Hamka, Ph.D.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $seRohani = User::firstOrCreate(
            ['email' => 'rohani.editor@unm.ac.id'],
            [
                'username' => 'rohani.editor',
                'name' => 'Dr. Hj. St. Rohani, M.Si.',
                'full_name' => 'Dr. Hj. St. Rohani, M.Si.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $revBudi = User::firstOrCreate(
            ['email' => 'budi.hartono@eng.unhas.ac.id'],
            [
                'username' => 'budi.hartono',
                'name' => 'Dr. Ir. Budi Hartono, M.Eng.',
                'full_name' => 'Dr. Ir. Budi Hartono, M.Eng.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $revNurfadillah = User::firstOrCreate(
            ['email' => 'nurfadillah.bahar@unhas.ac.id'],
            [
                'username' => 'nurfadillah.bahar',
                'name' => 'Dr. Nurfadillah Bahar, M.Kes.',
                'full_name' => 'Dr. Nurfadillah Bahar, M.Kes.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $authorIlyas = User::firstOrCreate(
            ['email' => 'andi.ilyas@unhas.ac.id'],
            [
                'username' => 'andi.ilyas',
                'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                'full_name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $authorNurul = User::firstOrCreate(
            ['email' => 'dr.nurul.author@unhas.ac.id'],
            [
                'username' => 'dr.nurul.author',
                'name' => 'Dr. Nurul Fadli, S.T., M.T.',
                'full_name' => 'Dr. Nurul Fadli, S.T., M.T.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        UserProfile::firstOrCreate(
            ['user_id' => $authorNurul->id],
            [
                'affiliation' => 'Fakultas Teknik Industri Universitas Hasanuddin',
                'country_code' => 'ID',
            ]
        );

        // Pastikan hak akses peran (UserRole) terdaftar untuk semua aktor
        $roleAssignments = [
            $seNasaruddin->id => ['editor_in_chief', 'section_editor', 'author'],
            $seHamka->id => ['section_editor', 'reviewer'],
            $seRohani->id => ['section_editor', 'author'],
            $revBudi->id => ['reviewer'],
            $revNurfadillah->id => ['reviewer', 'author'],
            $authorIlyas->id => ['author'],
            $authorNurul->id => ['author'],
        ];

        foreach ($roleAssignments as $userId => $roles) {
            foreach ($roles as $roleCode) {
                UserRole::firstOrCreate([
                    'user_id' => $userId,
                    'journal_id' => $journal->id,
                    'role_code' => $roleCode,
                ]);
            }
        }

        // =========================================================================
        // SKENARIO 1: SUB-2026-SE-001 — Sedang Ditelaah (In Review - Normal Aktif)
        // Mitra bestari 1 telah menerima tugas, mitra bestari 2 baru saja diundang.
        // =========================================================================
        $sub001 = $this->createOrUpdateSubmission(
            journal: $journal,
            sectionId: $secRiset->id,
            author: $authorIlyas,
            number: 'SUB-2026-SE-001',
            status: 'in_review',
            title: 'Optimalisasi Algoritma Machine Learning untuk Prediksi Genangan Air Hujan di Wilayah Panakkukang Makassar',
            abstract: 'Penelitian ini mengembangkan model prediksi genangan presisi tinggi berbasis Random Forest dan data curah hujan real-time stasiun hidrometeorologi Panakkukang untuk mitigasi genangan perkotaan.',
            keywords: ['Machine Learning', 'Genangan Air Hujan', 'Panakkukang', 'Drainase Perkotaan', 'Smart City Makassar'],
            subjectArea: 'Teknologi Informasi & Mitigasi Kebencanaan Daerah',
            submittedAt: now()->subDays(6)
        );

        DeskReview::updateOrCreate(
            ['submission_id' => $sub001->id],
            [
                'assessed_by' => $seNasaruddin->id,
                'scope_fit' => true,
                'files_complete' => true,
                'guidelines_compliant' => true,
                'similarity_score' => 9.8,
                'notes' => 'Topik prediksi hidrologi perkotaan berbasis machine learning sesuai fokus edisi SIRIDA.',
                'status' => 'passed',
                'version' => 1,
            ]
        );

        $this->assignSectionEditors($sub001, [$seNasaruddin, $seHamka], 'Tugaskan penelaah bidang hidrologi komputasi.');

        $round001 = ReviewRound::updateOrCreate(
            ['submission_id' => $sub001->id, 'round' => 1],
            [
                'minimum_completed_reviews' => 2,
                'status' => 'active',
                'review_type' => 'double_blind',
                'version' => 1,
            ]
        );

        ReviewAssignment::updateOrCreate(
            ['review_round_id' => $round001->id, 'reviewer_id' => $revBudi->id],
            [
                'status' => 'accepted',
                'response_due_at' => now()->subDays(3),
                'review_due_at' => now()->addDays(7),
                'responded_at' => now()->subDays(4),
                'invitation_message' => 'Yth. Dr. Budi, mohon kesediaannya menelaah kelayakan metode komputasi naskah ini.',
            ]
        );

        ReviewAssignment::updateOrCreate(
            ['review_round_id' => $round001->id, 'reviewer_id' => $revNurfadillah->id],
            [
                'status' => 'invited',
                'response_due_at' => now()->addDays(3),
                'review_due_at' => now()->addDays(14),
                'invitation_message' => 'Yth. Dr. Nurfadillah, kami mengundang Anda menelaah dampak drainase perkotaan pada naskah ini.',
            ]
        );

        // =========================================================================
        // SKENARIO 2: SUB-2026-SE-002 — Permintaan Perpanjangan Waktu Telaah (Pending)
        // Penelaah meminta dispensasi waktu 7 hari, muncul di Dashboard Section Editor.
        // =========================================================================
        $sub002 = $this->createOrUpdateSubmission(
            journal: $journal,
            sectionId: $secInovasi->id,
            author: $authorNurul,
            number: 'SUB-2026-SE-002',
            status: 'in_review',
            title: 'Evaluasi Kualitas Udara Ambien Berbasis Sensor IoT di Koridor Jalan A.P. Pettarani Makassar',
            abstract: 'Pengukuran konsentrasi partikulat PM2.5 dan gas buang emisi kendaraan pada jam sibuk di ruas jalan layang Pettarani menggunakan sensor nirkabel berdaya rendah.',
            keywords: ['Kualitas Udara', 'Sensor IoT', 'PM2.5', 'Jalan Pettarani', 'Transportasi Rendah Emisi'],
            subjectArea: 'Teknologi Lingkungan & Manajemen Udara Perkotaan',
            submittedAt: now()->subDays(12)
        );

        DeskReview::updateOrCreate(
            ['submission_id' => $sub002->id],
            [
                'assessed_by' => $seNasaruddin->id,
                'scope_fit' => true,
                'files_complete' => true,
                'guidelines_compliant' => true,
                'similarity_score' => 12.1,
                'notes' => 'Data empiris sensor IoT sangat berharga untuk perumusan zonasi emisi bersih Pemkot Makassar.',
                'status' => 'passed',
                'version' => 1,
            ]
        );

        $this->assignSectionEditors($sub002, [$seNasaruddin, $seHamka], 'Pantau respons telaah sensorik emisi.');

        $round002 = ReviewRound::updateOrCreate(
            ['submission_id' => $sub002->id, 'round' => 1],
            [
                'minimum_completed_reviews' => 2,
                'status' => 'active',
                'review_type' => 'double_blind',
                'version' => 1,
            ]
        );

        $asg2Budi = ReviewAssignment::updateOrCreate(
            ['review_round_id' => $round002->id, 'reviewer_id' => $revBudi->id],
            [
                'status' => 'accepted',
                'response_due_at' => now()->subDays(8),
                'review_due_at' => now()->subDay(),
                'responded_at' => now()->subDays(9),
                'invitation_message' => 'Mohon evaluasi kalibrasi sensor IoT dan validitas pengujian statistik.',
            ]
        );

        // Buat permintaan perpanjangan waktu telaah yang sedang menunggu persetujuan Editor Bagian
        ReviewExtension::updateOrCreate(
            ['assignment_id' => $asg2Budi->id, 'status' => 'pending'],
            [
                'requested_due_at' => now()->addDays(7),
                'reason' => 'Dibutuhkan waktu tambahan 7 hari untuk kalibrasi silang dataset sensor gas NO2 dan PM2.5 dengan data resmi stasiun pantau BMKG Paotere.',
                'version' => 1,
            ]
        );

        ReviewAssignment::updateOrCreate(
            ['review_round_id' => $round002->id, 'reviewer_id' => $revNurfadillah->id],
            [
                'status' => 'accepted',
                'response_due_at' => now()->subDays(7),
                'review_due_at' => now()->addDays(4),
                'responded_at' => now()->subDays(8),
                'invitation_message' => 'Mohon evaluasi aspek kesehatan lingkungan dan paparan polusi terhadap warga.',
            ]
        );

        // =========================================================================
        // SKENARIO 3: SUB-2026-SE-003 — Review Selesai (Reviewed - Siap Diberi Rekomendasi)
        // Kedua mitra bestari telah mengirimkan ulasan lengkap. Tombol "Rekomendasikan ke EIC" aktif!
        // =========================================================================
        $sub003 = $this->createOrUpdateSubmission(
            journal: $journal,
            sectionId: $secKebijakan->id,
            author: $authorIlyas,
            number: 'SUB-2026-SE-003',
            status: 'in_review',
            title: 'Kajian Efektivitas Program Lorong Wisata (Longwis) Terhadap Ketahanan Ekonomi Rumah Tangga di Kota Makassar',
            abstract: 'Studi komparasi pendapatan keluarga dan penciptaan lapangan kerja mikro di 15 lorong percontohan wilayah Kecamatan Rappocini dan Makassar sebelum dan sesudah intervensi program.',
            keywords: ['Lorong Wisata', 'Pemberdayaan Ekonomi', 'UMKM', 'Ketahanan Rumah Tangga', 'Kebijakan Makassar'],
            subjectArea: 'Pemberdayaan Masyarakat & Perekonomian Daerah',
            submittedAt: now()->subDays(18)
        );

        DeskReview::updateOrCreate(
            ['submission_id' => $sub003->id],
            [
                'assessed_by' => $seNasaruddin->id,
                'scope_fit' => true,
                'files_complete' => true,
                'guidelines_compliant' => true,
                'similarity_score' => 8.4,
                'notes' => 'Tema prioritas strategis RPJMD Kota Makassar.',
                'status' => 'passed',
                'version' => 1,
            ]
        );

        $this->assignSectionEditors($sub003, [$seNasaruddin, $seRohani], 'Telaah tuntas kuorum 2 penelaah independen.');

        $round003 = ReviewRound::updateOrCreate(
            ['submission_id' => $sub003->id, 'round' => 1],
            [
                'minimum_completed_reviews' => 2,
                'status' => 'active',
                'review_type' => 'double_blind',
                'se_recommendation' => null, // Belum direkomendasikan -> Section Editor siap merekomendasikan!
                'version' => 1,
            ]
        );

        $asg3Budi = ReviewAssignment::updateOrCreate(
            ['review_round_id' => $round003->id, 'reviewer_id' => $revBudi->id],
            [
                'status' => 'completed',
                'response_due_at' => now()->subDays(14),
                'review_due_at' => now()->subDays(2),
                'responded_at' => now()->subDays(15),
                'completed_at' => now()->subDays(2),
                'invitation_message' => 'Mohon evaluasi metodologi sampling survei ekonomi rumah tangga.',
            ]
        );

        Review::updateOrCreate(
            ['assignment_id' => $asg3Budi->id],
            [
                'scores' => ['originalitas' => 4, 'metodologi' => 4, 'signifikansi' => 5, 'kejelasan' => 4],
                'comments_for_author' => 'Metodologi survei kuantitatif sudah baik. Mohon tambahkan uji beda paired t-test untuk memperkuat bukti peningkatan omzet UMKM lorong.',
                'confidential_comments_for_editor' => 'Artikel sangat layak terbit dengan revisi minor metodologi statistik.',
                'recommendation' => 'revisions_required',
                'certify_no_conflict' => true,
                'status' => 'submitted',
                'submitted_at' => now()->subDays(2),
            ]
        );

        $asg3Nur = ReviewAssignment::updateOrCreate(
            ['review_round_id' => $round003->id, 'reviewer_id' => $revNurfadillah->id],
            [
                'status' => 'completed',
                'response_due_at' => now()->subDays(14),
                'review_due_at' => now()->subDays(2),
                'responded_at' => now()->subDays(15),
                'completed_at' => now()->subDay(),
                'invitation_message' => 'Mohon telaah dampak pemberdayaan sosial dan partisipasi wanita.',
            ]
        );

        Review::updateOrCreate(
            ['assignment_id' => $asg3Nur->id],
            [
                'scores' => ['originalitas' => 5, 'metodologi' => 5, 'signifikansi' => 5, 'kejelasan' => 5],
                'comments_for_author' => 'Ulasan dinamika sosial lorong wisata sangat hidup dan kaya data lapangan. Format tabel data sudah jelas.',
                'confidential_comments_for_editor' => 'Rekomendasi Diterima (Accept). Karya ini memberikan kontribusi nyata bagi literatur kebijakan berbasis lorong.',
                'recommendation' => 'accept',
                'certify_no_conflict' => true,
                'status' => 'submitted',
                'submitted_at' => now()->subDay(),
            ]
        );

        // =========================================================================
        // SKENARIO 4: SUB-2026-SE-004 — Menunggu Revisi Penulis (Revisions Requested)
        // Penelaah dan EIC meminta revisi, penulis telah mengunggah draf revisi.
        // =========================================================================
        $sub004 = $this->createOrUpdateSubmission(
            journal: $journal,
            sectionId: $secKebijakan->id,
            author: $authorNurul,
            number: 'SUB-2026-SE-004',
            status: 'revisions_requested',
            title: 'Implementasi Integrasi Pembayaran Digital QRIS pada Retribusi Pelayanan Pasar Tradisional Terong Makassar',
            abstract: 'Penelitian evaluasi adopsi transaksi non-tunai pada pedagang basah dan kering pasar tradisional Terong serta dampaknya terhadap transparansi kebocoran penerimaan retribusi daerah.',
            keywords: ['QRIS', 'Pasar Tradisional', 'Retribusi Pelayanan', 'Digitalisasi Daerah', 'Pasar Terong'],
            subjectArea: 'Keuangan Daerah & Transformasi Digital Publik',
            submittedAt: now()->subDays(24)
        );

        DeskReview::updateOrCreate(
            ['submission_id' => $sub004->id],
            [
                'assessed_by' => $seNasaruddin->id,
                'scope_fit' => true,
                'files_complete' => true,
                'guidelines_compliant' => true,
                'similarity_score' => 11.0,
                'notes' => 'Kajian tata kelola keuangan pasar tradisional sangat relevan.',
                'status' => 'passed',
                'version' => 1,
            ]
        );

        $this->assignSectionEditors($sub004, [$seNasaruddin, $seHamka], 'Pantau perbaikan naskah dari penulis.');

        $round004 = ReviewRound::updateOrCreate(
            ['submission_id' => $sub004->id, 'round' => 1],
            [
                'minimum_completed_reviews' => 2,
                'status' => 'active',
                'review_type' => 'double_blind',
                'se_recommendation' => 'revisions_required',
                'se_recommendation_note' => 'Penulis perlu melengkapi analisis hambatan literasi digital pedagang lansia.',
                'se_recommended_at' => now()->subDays(5),
                'version' => 1,
            ]
        );

        $asg4Budi = ReviewAssignment::updateOrCreate(
            ['review_round_id' => $round004->id, 'reviewer_id' => $revBudi->id],
            [
                'status' => 'completed',
                'response_due_at' => now()->subDays(20),
                'review_due_at' => now()->subDays(6),
                'responded_at' => now()->subDays(21),
                'completed_at' => now()->subDays(6),
            ]
        );

        Review::updateOrCreate(
            ['assignment_id' => $asg4Budi->id],
            [
                'scores' => ['originalitas' => 4, 'metodologi' => 3, 'signifikansi' => 4, 'kejelasan' => 4],
                'comments_for_author' => 'Perlu penambahan data perbandingan retribusi sebelum dan sesudah QRIS diterapkan secara menyeluruh.',
                'confidential_comments_for_editor' => 'Perlu revisi minor pada bagian data historis pendapatan pasar.',
                'recommendation' => 'revisions_required',
                'certify_no_conflict' => true,
                'status' => 'submitted',
                'submitted_at' => now()->subDays(6),
            ]
        );

        $dec004 = EditorialDecision::updateOrCreate(
            ['submission_id' => $sub004->id, 'decision' => 'revisions_required'],
            [
                'review_round_id' => $round004->id,
                'decided_by' => $seNasaruddin->id,
                'note_for_author' => 'Mohon lakukan revisi sesuai masukan kedua mitra bestari, khususnya penyajian data pembanding retribusi pasar.',
                'internal_note' => 'Batas waktu revisi 14 hari kerja.',
                'status' => 'sent',
                'revision_due_at' => now()->addDays(9),
                'sent_at' => now()->subDays(4),
                'version' => 1,
            ]
        );

        // Buat rekaman berkas revisi yang diunggah oleh penulis
        $revFile = FileEntry::firstOrCreate(
            ['original_name' => 'SUB2026-SE-004_Revisi_Putaran1.docx'],
            [
                'storage_disk' => 'azure',
                'storage_path' => 'revisions/SUB2026-SE-004_Revisi_Putaran1.docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'byte_size' => 184500,
                'checksum_sha256' => hash('sha256', 'SUB2026-SE-004_Revisi_Putaran1.docx'),
                'virus_scan_status' => 'clean',
                'created_by' => $authorNurul->id,
            ]
        );

        Revision::updateOrCreate(
            ['submission_id' => $sub004->id, 'round' => 1],
            [
                'decision_id' => $dec004->id,
                'status' => 'submitted',
                'manuscript_file_id' => $revFile->id,
                'due_at' => now()->addDays(9),
                'submitted_at' => now()->subDay(),
                'note' => 'Kami telah merevisi naskah dengan melampirkan tabel data realisasi pendapatan retribusi Pasar Terong 2023-2025.',
                'version' => 1,
            ]
        );

        // =========================================================================
        // SKENARIO 5: SUB-2026-SE-005 — Putaran Kedua Aktif (Round 2 In Review)
        // Putaran 1 telah selesai dan ditutup, naskah kini sedang dievaluasi pada Round 2.
        // =========================================================================
        $sub005 = $this->createOrUpdateSubmission(
            journal: $journal,
            sectionId: $secRiset->id,
            author: $authorIlyas,
            number: 'SUB-2026-SE-005',
            status: 'in_review',
            title: 'Pemodelan Spasial Dinamika Intrusi Air Asin pada Akuifer Dangkal di Wilayah Pesisir Mariso Makassar',
            abstract: 'Kajian pemetaan hidrokimia isotop stabil untuk menentukan batas sebaran intrusi air laut ke dalam sumur gali warga di sepanjang pesisir Pantai Losari dan Mariso pasca reklamasi.',
            keywords: ['Intrusi Air Asin', 'Akuifer Dangkal', 'Pesisir Mariso', 'Hidrokimia', 'Dampak Reklamasi'],
            subjectArea: 'Geologi Pesisir, Sanitasi Air Bersih, & Mitigasi Lingkungan',
            submittedAt: now()->subDays(35)
        );

        DeskReview::updateOrCreate(
            ['submission_id' => $sub005->id],
            [
                'assessed_by' => $seNasaruddin->id,
                'scope_fit' => true,
                'files_complete' => true,
                'guidelines_compliant' => true,
                'similarity_score' => 7.2,
                'notes' => 'Riset geologi lingkungan yang sangat mendalam dan berbobot ilmiah tinggi.',
                'status' => 'passed',
                'version' => 1,
            ]
        );

        $this->assignSectionEditors($sub005, [$seNasaruddin, $seRohani], 'Kelola telaah lanjutan putaran 2.');

        // Round 1 (Telah selesai dan ditutup)
        ReviewRound::updateOrCreate(
            ['submission_id' => $sub005->id, 'round' => 1],
            [
                'minimum_completed_reviews' => 2,
                'status' => 'completed',
                'review_type' => 'double_blind',
                'se_recommendation' => 'revisions_required',
                'se_recommendation_note' => 'Diperlukan tambahan titik sampling sumur gali di kawasan permukiman padat.',
                'se_recommended_at' => now()->subDays(15),
                'closed_at' => now()->subDays(10),
                'version' => 1,
            ]
        );

        // Round 2 (Sedang berjalan aktif)
        $round005R2 = ReviewRound::updateOrCreate(
            ['submission_id' => $sub005->id, 'round' => 2],
            [
                'minimum_completed_reviews' => 1,
                'status' => 'active',
                'review_type' => 'double_blind',
                'note' => 'Putaran 2 untuk memverifikasi penambahan 10 titik sampling air sumur gali.',
                'version' => 1,
            ]
        );

        ReviewAssignment::updateOrCreate(
            ['review_round_id' => $round005R2->id, 'reviewer_id' => $revBudi->id],
            [
                'status' => 'accepted',
                'response_due_at' => now()->subDays(3),
                'review_due_at' => now()->addDays(8),
                'responded_at' => now()->subDays(4),
                'invitation_message' => 'Yth. Dr. Budi, mohon kesediaan menelaah revisi putaran kedua naskah hidrokimia pesisir Mariso.',
            ]
        );

        // =========================================================================
        // SKENARIO 6: SUB-2026-SE-006 — Rekomendasi Terkirim ke EIC (Rekomendasi Diterima)
        // Section Editor sudah mengirim rekomendasi "Terima Naskah", muncul di Dashboard.
        // =========================================================================
        $sub006 = $this->createOrUpdateSubmission(
            journal: $journal,
            sectionId: $secInovasi->id,
            author: $authorIlyas,
            number: 'SUB-2026-SE-006',
            status: 'in_review',
            title: 'Strategi Pengelolaan Sampah Makanan (Food Waste) di Kawasan Wisata Bahari Pantai Losari Makassar',
            abstract: 'Penerapan konsep circular economy pada limbah sisa makanan restoran dan kuliner terapung di sepanjang anjungan Losari menggunakan teknologi biokonversi larva Black Soldier Fly (BSF).',
            keywords: ['Food Waste', 'Biokonversi BSF', 'Pantai Losari', 'Ekonomi Sirkular', 'Wisata Berkelanjutan'],
            subjectArea: 'Pengelolaan Persampahan & Inovasi Lingkungan Wisata',
            submittedAt: now()->subDays(20)
        );

        DeskReview::updateOrCreate(
            ['submission_id' => $sub006->id],
            [
                'assessed_by' => $seNasaruddin->id,
                'scope_fit' => true,
                'files_complete' => true,
                'guidelines_compliant' => true,
                'similarity_score' => 6.5,
                'notes' => 'Solusi konkret persampahan anjungan ikonik Makassar.',
                'status' => 'passed',
                'version' => 1,
            ]
        );

        $this->assignSectionEditors($sub006, [$seNasaruddin, $seHamka], 'Rekomendasi tuntas diajukan ke Pemimpin Redaksi.');

        $round006 = ReviewRound::updateOrCreate(
            ['submission_id' => $sub006->id, 'round' => 1],
            [
                'minimum_completed_reviews' => 2,
                'status' => 'active',
                'review_type' => 'double_blind',
                'se_recommendation' => 'accept',
                'se_recommendation_note' => 'Kedua mitra bestari menilai kualitas artikel sangat tinggi dan siap dilanjutkan ke tahap penyuntingan teks (Copyediting).',
                'se_recommended_at' => now()->subDay(),
                'version' => 1,
            ]
        );

        $asg6Budi = ReviewAssignment::updateOrCreate(
            ['review_round_id' => $round006->id, 'reviewer_id' => $revBudi->id],
            [
                'status' => 'completed',
                'response_due_at' => now()->subDays(15),
                'review_due_at' => now()->subDays(3),
                'responded_at' => now()->subDays(16),
                'completed_at' => now()->subDays(3),
            ]
        );

        Review::updateOrCreate(
            ['assignment_id' => $asg6Budi->id],
            [
                'scores' => ['originalitas' => 5, 'metodologi' => 5, 'signifikansi' => 5, 'kejelasan' => 5],
                'comments_for_author' => 'Pengujian biokonversi BSF sangat aplikatif dan ekonomis untuk diterapkan oleh DLH Kota Makassar.',
                'confidential_comments_for_editor' => 'Rekomendasi Terima (Accept) tanpa syarat.',
                'recommendation' => 'accept',
                'certify_no_conflict' => true,
                'status' => 'submitted',
                'submitted_at' => now()->subDays(3),
            ]
        );

        $asg6Nur = ReviewAssignment::updateOrCreate(
            ['review_round_id' => $round006->id, 'reviewer_id' => $revNurfadillah->id],
            [
                'status' => 'completed',
                'response_due_at' => now()->subDays(15),
                'review_due_at' => now()->subDays(2),
                'responded_at' => now()->subDays(16),
                'completed_at' => now()->subDays(2),
            ]
        );

        Review::updateOrCreate(
            ['assignment_id' => $asg6Nur->id],
            [
                'scores' => ['originalitas' => 5, 'metodologi' => 4, 'signifikansi' => 5, 'kejelasan' => 5],
                'comments_for_author' => 'Analisis reduksi timbulan sampah makanan disajikan sistematis dengan bukti empiris kuat.',
                'confidential_comments_for_editor' => 'Artikel sangat memuaskan, siap terbit.',
                'recommendation' => 'accept',
                'certify_no_conflict' => true,
                'status' => 'submitted',
                'submitted_at' => now()->subDays(2),
            ]
        );
    }

    /**
     * Buat atau perbarui Submission beserta file manuskrip pendukung.
     *
     * @param  array<string>  $keywords
     */
    protected function createOrUpdateSubmission(
        Journal $journal,
        string $sectionId,
        User $author,
        string $number,
        string $status,
        string $title,
        string $abstract,
        array $keywords,
        string $subjectArea,
        CarbonInterface $submittedAt
    ): Submission {
        $submission = Submission::updateOrCreate(
            ['submission_number' => $number],
            [
                'journal_id' => $journal->id,
                'section_id' => $sectionId,
                'author_id' => $author->id,
                'title' => $title,
                'abstract' => $abstract,
                'keywords' => $keywords,
                'subject_area' => $subjectArea,
                'status' => $status,
                'submitted_at' => $submittedAt,
                'version' => 1,
            ]
        );

        SubmissionContributor::firstOrCreate(
            ['submission_id' => $submission->id, 'email' => $author->email],
            [
                'given_name' => $author->name,
                'family_name' => '',
                'affiliation' => 'Universitas Hasanuddin Makassar',
                'is_corresponding' => true,
                'position' => 1,
            ]
        );

        $fileEntry = FileEntry::firstOrCreate(
            ['original_name' => "{$number}_Manuskrip_Utama.pdf"],
            [
                'storage_disk' => 'azure',
                'storage_path' => "submissions/{$number}_Manuskrip_Utama.pdf",
                'mime_type' => 'application/pdf',
                'byte_size' => 245000,
                'checksum_sha256' => hash('sha256', "{$number}_Manuskrip_Utama.pdf"),
                'virus_scan_status' => 'clean',
                'created_by' => $author->id,
            ]
        );

        SubmissionFile::firstOrCreate(
            [
                'submission_id' => $submission->id,
                'file_id' => $fileEntry->id,
            ],
            [
                'genre' => 'manuscript',
                'version' => 1,
            ]
        );

        return $submission;
    }

    /**
     * Tugaskan Section Editors ke Submission yang ditentukan.
     *
     * @param  array<User>  $editors
     */
    protected function assignSectionEditors(Submission $submission, array $editors, string $note): void
    {
        foreach ($editors as $editor) {
            EditorAssignment::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'editor_id' => $editor->id,
                    'role' => 'section_editor',
                ],
                [
                    'assigned_at' => $submission->submitted_at ?? now()->subDays(5),
                    'revoked_at' => null,
                    'note' => $note,
                ]
            );
        }
    }
}
