<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DeskReview;
use App\Models\EditorAssignment;
use App\Models\EditorialDecision;
use App\Models\FileEntry;
use App\Models\Review;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\Revision;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EicSeeder extends Seeder
{
    /**
     * Menjalankan seeding komprehensif berbagai skenario alur kerja Editor-in-Chief (UC-10, UC-11, UC-12).
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo seeder tidak boleh dijalankan pada production.');
        }

        // 1. Pastikan aktor EIC, Section Editors, dan Reviewers tersedia
        $eic = User::firstOrCreate(
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

        $submissions = Submission::whereIn('submission_number', [
            'SUB-2026-00102',
            'SUB-2026-00103',
            'SUB-2026-00104',
            'SUB-2026-00105',
            'SUB-2026-00106',
            'SUB-2026-00107',
            'SUB-2026-00108',
            'SUB-2026-00110',
            'SUB-2026-00111',
        ])->get()->keyBy('submission_number');

        // SKENARIO A (SUB-2026-00102): Baru Masuk (status: submitted) -> Siap dieksekusi Desk Review (UC-10) oleh EIC
        // (Dibiarkan belum memiliki DeskReview agar EIC bisa langsung mencoba form Desk Review dari nol)

        // SKENARIO B (SUB-2026-00103): Dalam Penelaahan (in_review) -> Lolos Desk Review, SE Ditugaskan, 2 Reviewer Selesai & Rekomendasi SE Siap Diputuskan EIC (UC-12)!
        if ($sub103 = $submissions->get('SUB-2026-00103')) {
            DeskReview::updateOrCreate(
                ['submission_id' => $sub103->id],
                [
                    'assessed_by' => $eic->id,
                    'scope_fit' => true,
                    'files_complete' => true,
                    'guidelines_compliant' => true,
                    'similarity_score' => 11.4,
                    'notes' => 'Naskah pemodelan GIS dan Sentinel-1 sangat relevan dengan fokus mitigasi bencana pesisir Makassar. Indeks kemiripan 11.4% (aman).',
                    'status' => 'passed',
                    'version' => 1,
                ]
            );

            EditorAssignment::updateOrCreate(
                ['submission_id' => $sub103->id, 'editor_id' => $seHamka->id, 'role' => 'section_editor'],
                [
                    'assigned_at' => now()->subDays(10),
                    'revoked_at' => null,
                    'note' => 'Mohon tugaskan 2 reviewer kepakaran geospasial pesisir dan hidrodinamika.',
                ]
            );

            $round103 = ReviewRound::updateOrCreate(
                ['submission_id' => $sub103->id, 'round' => 1],
                [
                    'minimum_completed_reviews' => 2,
                    'status' => 'active',
                    'review_type' => 'double_blind',
                    'se_recommendation' => 'accept',
                    'se_recommendation_note' => 'Kedua mitra bestari memberikan penilaian sangat baik (skor 4-5/5) dan merekomendasikan naskah diterima karena ketepatan validasi lapangan.',
                    'se_recommended_at' => now()->subDay(),
                    'version' => 1,
                ]
            );

            $asg1 = ReviewAssignment::updateOrCreate(
                ['review_round_id' => $round103->id, 'reviewer_id' => $revBudi->id],
                [
                    'status' => 'completed',
                    'response_due_at' => now()->subDays(7),
                    'review_due_at' => now()->addDays(5),
                    'responded_at' => now()->subDays(9),
                    'completed_at' => now()->subDays(2),
                    'invitation_message' => 'Mohon telaah metodologi pemodelan spasial Sentinel-1.',
                ]
            );

            Review::updateOrCreate(
                ['assignment_id' => $asg1->id],
                [
                    'scores' => ['originalitas' => 5, 'metodologi' => 4, 'signifikansi' => 5, 'kejelasan' => 4],
                    'comments_for_author' => 'Pemodelan SAR Sentinel-1 dan integrasi DEM sangat solid. Peta kerentanan infrastruktur pesisir Kecamatan Tamalate dan Mariso disajikan dengan akurasi tinggi.',
                    'confidential_comments_for_editor' => 'Naskah layak langsung diterima (Accept). Validasi ground-truth sudah sangat memadai.',
                    'recommendation' => 'accept',
                    'certify_no_conflict' => true,
                    'status' => 'submitted',
                    'submitted_at' => now()->subDays(2),
                ]
            );

            $asg2 = ReviewAssignment::updateOrCreate(
                ['review_round_id' => $round103->id, 'reviewer_id' => $revNurfadillah->id],
                [
                    'status' => 'completed',
                    'response_due_at' => now()->subDays(7),
                    'review_due_at' => now()->addDays(5),
                    'responded_at' => now()->subDays(8),
                    'completed_at' => now()->subDay(),
                    'invitation_message' => 'Mohon evaluasi aspek dampak kerentanan wilayah pesisir.',
                ]
            );

            Review::updateOrCreate(
                ['assignment_id' => $asg2->id],
                [
                    'scores' => ['originalitas' => 4, 'metodologi' => 5, 'signifikansi' => 5, 'kejelasan' => 5],
                    'comments_for_author' => 'Kontribusi empiris sangat kuat bagi kebijakan adaptasi perubahan iklim Pemerintah Kota Makassar. Tabel klasifikasi risiko sudah jelas.',
                    'confidential_comments_for_editor' => 'Sangat direkomendasikan untuk diterbitkan pada edisi berjalan.',
                    'recommendation' => 'accept',
                    'certify_no_conflict' => true,
                    'status' => 'submitted',
                    'submitted_at' => now()->subDay(),
                ]
            );
        }

        // SKENARIO C (SUB-2026-00104): Revisi Minor Diminta (revisions_requested) -> Sudah ada Desk Review, Putaran Review #1, Keputusan Revisi EIC, dan Unggahan Revisi Penulis
        if ($sub104 = $submissions->get('SUB-2026-00104')) {
            DeskReview::updateOrCreate(
                ['submission_id' => $sub104->id],
                [
                    'assessed_by' => $eic->id,
                    'scope_fit' => true,
                    'files_complete' => true,
                    'guidelines_compliant' => true,
                    'similarity_score' => 14.8,
                    'notes' => 'Topik evaluasi program Lorong Wisata sangat relevan dengan inovasi daerah Makassar.',
                    'status' => 'passed',
                    'version' => 1,
                ]
            );

            EditorAssignment::updateOrCreate(
                ['submission_id' => $sub104->id, 'editor_id' => $seRohani->id, 'role' => 'section_editor'],
                [
                    'assigned_at' => now()->subDays(18),
                    'revoked_at' => null,
                    'note' => 'Mohon dikawal telaah aspek pemberdayaan ekonomi kreatif masyarakat.',
                ]
            );

            $round104 = ReviewRound::updateOrCreate(
                ['submission_id' => $sub104->id, 'round' => 1],
                [
                    'minimum_completed_reviews' => 2,
                    'status' => 'completed',
                    'review_type' => 'double_blind',
                    'se_recommendation' => 'revisions_required',
                    'se_recommendation_note' => 'Mitra bestari meminta penguatan uji regresi pada sub-bab 4.2 dan pembaruan rujukan 5 tahun terakhir.',
                    'se_recommended_at' => now()->subDays(8),
                    'closed_at' => now()->subDays(7),
                    'version' => 1,
                ]
            );

            $dec104 = EditorialDecision::updateOrCreate(
                ['submission_id' => $sub104->id, 'decision' => 'revisions_required'],
                [
                    'review_round_id' => $round104->id,
                    'decided_by' => $eic->id,
                    'note_for_author' => 'Dewan redaksi mengapresiasi temuan empiris evaluasi Lorong Wisata. Mohon lengkapi analisis statistik inferensial pada sub-bab 4.2 sesuai masukan mitra bestari sebelum batas waktu revisi.',
                    'internal_note' => 'Revisi minor 14 hari; dapat langsung dievaluasi EIC/SE setelah penulis mengunggah perbaikan.',
                    'status' => 'sent',
                    'revision_due_at' => now()->addDays(7),
                    'sent_at' => now()->subDays(7),
                    'version' => 1,
                ]
            );

            $revFile = FileEntry::updateOrCreate(
                ['original_name' => 'SUB-2026-00104_Manuskrip_Revisi_Putaran1.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'manuscripts/sub104_revisi_r1.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 1984512,
                    'checksum_sha256' => hash('sha256', 'sub104_revisi_r1'),
                    'virus_scan_status' => 'clean',
                    'created_by' => $sub104->author_id,
                ]
            );

            $respLetterFile = FileEntry::updateOrCreate(
                ['original_name' => 'SUB-2026-00104_Response_to_Reviewers.pdf'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'manuscripts/sub104_response_letter.pdf',
                    'mime_type' => 'application/pdf',
                    'byte_size' => 452100,
                    'checksum_sha256' => hash('sha256', 'sub104_response_letter'),
                    'virus_scan_status' => 'clean',
                    'created_by' => $sub104->author_id,
                ]
            );

            Revision::updateOrCreate(
                ['submission_id' => $sub104->id, 'round' => 1],
                [
                    'decision_id' => $dec104->id,
                    'manuscript_file_id' => $revFile->id,
                    'response_letter_file_id' => $respLetterFile->id,
                    'status' => 'submitted',
                    'due_at' => now()->addDays(7),
                    'submitted_at' => now()->subDays(1),
                    'note' => 'Kami telah menambahkan tabel uji t berpasangan pada sub-bab 4.2 dan memperbarui 12 sitasi jurnal terbaru (2022-2026).',
                    'version' => 1,
                ]
            );
        }

        // SKENARIO D (SUB-2026-00105): Revisi Mayor (revisions_requested) -> Menunggu perbaikan penulis
        if ($sub105 = $submissions->get('SUB-2026-00105')) {
            DeskReview::updateOrCreate(
                ['submission_id' => $sub105->id],
                [
                    'assessed_by' => $eic->id,
                    'scope_fit' => true,
                    'files_complete' => true,
                    'guidelines_compliant' => true,
                    'similarity_score' => 16.2,
                    'notes' => 'Kajian sistematis PRISMA memenuhi standar awal.',
                    'status' => 'passed',
                    'version' => 1,
                ]
            );

            EditorAssignment::updateOrCreate(
                ['submission_id' => $sub105->id, 'editor_id' => $seHamka->id, 'role' => 'section_editor'],
                [
                    'assigned_at' => now()->subDays(22),
                    'revoked_at' => null,
                    'note' => 'Evaluasi kelengkapan protokol PRISMA 2020.',
                ]
            );

            EditorialDecision::updateOrCreate(
                ['submission_id' => $sub105->id, 'decision' => 'resubmit'],
                [
                    'decided_by' => $eic->id,
                    'note_for_author' => 'Mohon tambahkan matriks penilaian risiko bias (Risk of Bias Assessment) pada 34 artikel inklusi dan perluas cakupan database literatur.',
                    'internal_note' => 'Perlu dibuka putaran review ke-2 setelah revisi mayor masuk.',
                    'status' => 'sent',
                    'revision_due_at' => now()->addDays(14),
                    'sent_at' => now()->subDays(5),
                    'version' => 1,
                ]
            );
        }

        // SKENARIO E (SUB-2026-00106, SUB-2026-00107, SUB-2026-00110, SUB-2026-00111): Naskah Diterima (Accepted / In Production / Published)
        foreach (['SUB-2026-00106', 'SUB-2026-00107', 'SUB-2026-00110', 'SUB-2026-00111'] as $num) {
            if ($subAcc = $submissions->get($num)) {
                DeskReview::updateOrCreate(
                    ['submission_id' => $subAcc->id],
                    [
                        'assessed_by' => $eic->id,
                        'scope_fit' => true,
                        'files_complete' => true,
                        'guidelines_compliant' => true,
                        'similarity_score' => 9.5,
                        'notes' => 'Memenuhi seluruh kriteria kelayakan ilmiah dan kesesuaian skop jurnal SIRIDA.',
                        'status' => 'passed',
                        'version' => 1,
                    ]
                );

                EditorAssignment::updateOrCreate(
                    ['submission_id' => $subAcc->id, 'editor_id' => $seHamka->id, 'role' => 'section_editor'],
                    [
                        'assigned_at' => now()->subDays(30),
                        'revoked_at' => null,
                        'note' => 'Penugasan penelaahan hingga tahap produksi.',
                    ]
                );

                EditorialDecision::updateOrCreate(
                    ['submission_id' => $subAcc->id, 'decision' => 'accept'],
                    [
                        'decided_by' => $eic->id,
                        'note_for_author' => 'Selamat! Naskah ilmiah Anda telah dinyatakan DITERIMA (Accepted) oleh Dewan Redaksi SIRIDA dan dilanjutkan ke tahap Copyediting serta produksi Galai.',
                        'internal_note' => 'Telah memenuhi kuorum 2 reviewer independen dengan hasil memuaskan.',
                        'status' => 'sent',
                        'sent_at' => now()->subDays(18),
                        'version' => 1,
                    ]
                );
            }
        }

        // SKENARIO F (SUB-2026-00108): Naskah Ditolak pada Tahap Desk Review / Desk Reject (declined)
        if ($sub108 = $submissions->get('SUB-2026-00108')) {
            DeskReview::updateOrCreate(
                ['submission_id' => $sub108->id],
                [
                    'assessed_by' => $eic->id,
                    'scope_fit' => false,
                    'files_complete' => true,
                    'guidelines_compliant' => false,
                    'similarity_score' => 38.5,
                    'notes' => 'Fokus kajian makroekonomi moneter berada di luar ruang lingkup inovasi daerah, serta indeks kemiripan teks 38.5% melebihi batas maksimum 20%.',
                    'status' => 'rejected',
                    'version' => 1,
                ]
            );

            EditorialDecision::updateOrCreate(
                ['submission_id' => $sub108->id, 'decision' => 'decline'],
                [
                    'decided_by' => $eic->id,
                    'note_for_author' => 'Berdasarkan pemeriksaan awal (Desk Review), topik naskah berada di luar fokus dan ruang lingkup Jurnal SIRIDA Makassar serta memiliki indeks kesamaan di atas ambang batas.',
                    'internal_note' => 'Desk reject pada tahap skrining awal (UC-10).',
                    'status' => 'sent',
                    'sent_at' => now()->subDays(12),
                    'version' => 1,
                ]
            );
        }
    }
}
