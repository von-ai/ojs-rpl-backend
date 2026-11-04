<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\Journal;
use App\Models\LayoutAssignment;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionContributor;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProofreaderSeeder extends Seeder
{
    /**
     * Menjalankan seeding basis data realistis untuk Portal Proofreader.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo seeder tidak boleh dijalankan pada production.');
        }

        // 1. Proofreader Utama: Dra. Sitti Aminah, M.Hum.
        $proofreader1 = User::firstOrCreate(
            ['email' => 'sitti.aminah@brida.makassar.go.id'],
            [
                'username' => 'sitti.aminah',
                'name' => 'Dra. Sitti Aminah, M.Hum.',
                'full_name' => 'Dra. Sitti Aminah, M.Hum.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Proofreader Pendamping: Drs. H. Anwar Kasim, M.Pd.
        $proofreader2 = User::firstOrCreate(
            ['email' => 'anwar.kasim@brida.makassar.go.id'],
            [
                'username' => 'anwar.kasim',
                'name' => 'Drs. H. Anwar Kasim, M.Pd.',
                'full_name' => 'Drs. H. Anwar Kasim, M.Pd.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Layout Editor & Section Editor
        $layoutEditor = User::firstOrCreate(
            ['email' => 'rahmat.hidayat@brida.makassar.go.id'],
            [
                'username' => 'rahmat.hidayat',
                'name' => 'Rahmat Hidayat, S.Kom.',
                'full_name' => 'Rahmat Hidayat, S.Kom.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $sectionEditor = User::firstOrCreate(
            ['email' => 'prof.hamka@unhas.ac.id'],
            [
                'username' => 'prof.hamka',
                'name' => 'Prof. Hamka, Ph.D.',
                'full_name' => 'Prof. Hamka, Ph.D.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $journal = Journal::first() ?? Journal::create([
            'slug' => 'sirida-makassar',
            'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
            'issn' => '2985-7821',
            'eissn' => '2985-7821',
            'status' => 'active',
            'version' => 1,
        ]);

        foreach ([[$proofreader1, 'proofreader'], [$proofreader2, 'proofreader'], [$layoutEditor, 'layout_editor'], [$sectionEditor, 'editor_in_chief']] as [$user, $role]) {
            UserRole::firstOrCreate(['user_id' => $user->id, 'journal_id' => $journal->id, 'role_code' => $role]);
        }

        $section = Section::firstOrCreate(
            ['slug' => 'sec-inovasi-pelayanan'],
            [
                'journal_id' => $journal->id,
                'name' => 'Inovasi Pelayanan Publik & Pariwisata',
                'description' => 'Inovasi tata kelola dan pariwisata daerah',
                'policy' => 'Relevan dengan pembangunan daerah',
                'position' => 1,
            ]
        );

        // Helper closures to ensure author and layout assignment
        $ensureAuthor = function (Submission $sub, string $name, string $email, string $affiliation) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'username' => explode('@', $email)[0],
                    'name' => $name,
                    'full_name' => $name,
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );
            $sub->author_id = $user->id;
            $sub->save();

            SubmissionContributor::updateOrCreate(
                ['submission_id' => $sub->id, 'email' => $email],
                [
                    'given_name' => $name,
                    'family_name' => '',
                    'affiliation' => $affiliation,
                    'is_corresponding' => true,
                    'position' => 1,
                ]
            );
        };

        $ensureLayout = function (Submission $sub) use ($layoutEditor, $sectionEditor) {
            LayoutAssignment::firstOrCreate(
                ['submission_id' => $sub->id, 'layout_editor_id' => $layoutEditor->id],
                [
                    'assigned_by' => $sectionEditor->id,
                    'assigned_at' => now()->subDays(5),
                    'due_at' => now()->addDays(5),
                    'version' => 1,
                ]
            );
        };

        $isStandalone = ! LayoutAssignment::whereHas('submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00106'))->exists();

        if ($isStandalone) {
            // =========================================================================
            // Skenario 1: SUB-2026-00106 — Review Galai v2 (Benteng Somba Opu)
            // =========================================================================
            $sub106 = Submission::firstOrNew(['submission_number' => 'SUB-2026-00106']);
            $sub106->journal_id = $journal->id;
            $sub106->section_id = $section->id;
            $sub106->title = 'Strategi Pengembangan Destinasi Wisata Sejarah Benteng Somba Opu Berbasis Smart Tourism';
            $sub106->abstract = 'Kajian pengembangan pariwisata sejarah Benteng Somba Opu berbasis teknologi smart tourism di Kota Makassar.';
            $sub106->status = 'in_production';
            $sub106->submitted_at = now()->subDays(30);
            $sub106->save();

            $ensureAuthor($sub106, 'Dr. Andi Nurul Hidayah, M.Si.', 'andi.nurul@unm.ac.id', 'Fakultas Ilmu Sosial dan Politik, Universitas Negeri Makassar');
            $ensureLayout($sub106);

            $docx106 = $this->createPhysicalFile(
                'SUB-2026-00106_Copyedited_Final.docx',
                "submissions/{$sub106->id}/SUB-2026-00106_Copyedited_Final.docx",
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                $sectionEditor->id,
                657408,
                'docx'
            );

            $pdf106 = $this->createPhysicalFile(
                'SUB-2026-00106_Galley_v2.pdf',
                "galleys/{$sub106->id}/SUB-2026-00106_Galley_v2.pdf",
                'application/pdf',
                $layoutEditor->id,
                3984588,
                'pdf'
            );

            $galley106 = Galley::updateOrCreate(
                ['submission_id' => $sub106->id, 'version' => 2],
                [
                    'file_id' => $pdf106->id,
                    'supersedes_galley_id' => null,
                    'format' => 'pdf',
                    'stage' => 'proofreading',
                    'status' => 'active',
                    'checksum_sha256' => $pdf106->checksum_sha256,
                    'approved_checksum' => null,
                    'label' => 'PDF Full Text (v2 Revisi)',
                ]
            );

            $assign106 = ProofAssignment::updateOrCreate(
                ['galley_id' => $galley106->id, 'proofreader_id' => $proofreader1->id],
                [
                    'file_id' => $pdf106->id,
                    'due_at' => now()->addDays(2),
                    'status' => 'in_progress',
                    'assigned_at' => now()->subDays(3),
                    'checksum_sha256' => substr($pdf106->checksum_sha256, 0, 64),
                    'message' => 'Mohon periksa ulang penataan gambar dan kelengkapan daftar pustaka.',
                ]
            );

            // Note 1: Minor Typo - Resolved & Verified
            ProofreadingNote::updateOrCreate(
                ['id' => 'NOTE-01JARK-01'],
                [
                    'proof_assignment_id' => $assign106->id,
                    'galley_id' => $galley106->id,
                    'file_id' => $pdf106->id,
                    'checksum_sha256' => $pdf106->checksum_sha256,
                    'page' => 2,
                    'category' => 'typo',
                    'severity' => 'minor',
                    'description' => 'Kesalahan penulisan kata "pariwista" seharusnya "pariwisata" pada alinea kedua paragraf pembuka.',
                    'resolved' => true,
                    'resolution' => 'Sudah diperbaiki pada draf galai v2 sesuai teks copyediting final.',
                    'resolved_at' => now()->subHours(4),
                    'verified' => true,
                    'verified_at' => now()->subHours(2),
                    'verified_by' => $proofreader1->id,
                    'version' => 1,
                ]
            );

            // Note 2: Major Layout - Resolved, Siap Diverifikasi Proofreader
            ProofreadingNote::updateOrCreate(
                ['id' => 'NOTE-01JARK-02'],
                [
                    'proof_assignment_id' => $assign106->id,
                    'galley_id' => $galley106->id,
                    'file_id' => $pdf106->id,
                    'checksum_sha256' => $pdf106->checksum_sha256,
                    'page' => 4,
                    'category' => 'layout',
                    'severity' => 'major',
                    'description' => 'Keterangan Gambar 3 (Peta Kawasan Benteng Somba Opu) terpotong margin bawah kolom sebelah kanan.',
                    'resolved' => true,
                    'resolution' => 'Ukuran skala gambar 3 diperkecil 10% dan posisi keterangan dipindahkan ke baris penuh.',
                    'resolved_at' => now()->subHours(3),
                    'verified' => false,
                    'verified_at' => null,
                    'verified_by' => null,
                    'version' => 1,
                ]
            );

            // Note 3: Critical Reference - Belum Selesai (Menahan Signoff Guard)
            ProofreadingNote::updateOrCreate(
                ['id' => 'NOTE-01JARK-03'],
                [
                    'proof_assignment_id' => $assign106->id,
                    'galley_id' => $galley106->id,
                    'file_id' => $pdf106->id,
                    'checksum_sha256' => $pdf106->checksum_sha256,
                    'page' => 7,
                    'category' => 'reference',
                    'severity' => 'critical',
                    'description' => 'Sitasi (BPS Kota Makassar, 2025) pada halaman 3 belum tercantum dalam daftar pustaka.',
                    'resolved' => false,
                    'resolution' => null,
                    'resolved_at' => null,
                    'verified' => false,
                    'verified_at' => null,
                    'verified_by' => null,
                    'version' => 1,
                ]
            );

            // =========================================================================
            // Skenario 2: SUB-2026-00107 — Bersih & Siap Disetujui Sign-off (Sentinel-1A)
            // =========================================================================
            $sub107 = Submission::firstOrNew(['submission_number' => 'SUB-2026-00107']);
            $sub107->journal_id = $journal->id;
            $sub107->section_id = $section->id;
            $sub107->title = 'Analisis Spasial Kerentanan Banjir Rob Kawasan Pesisir Kota Makassar Menggunakan Sentinel-1A';
            $sub107->abstract = 'Analisis spasial dinamika pasang surut dan kerentanan banjir rob pesisir Makassar.';
            $sub107->status = 'in_production';
            $sub107->submitted_at = now()->subDays(25);
            $sub107->save();

            $ensureAuthor($sub107, 'Prof. Dr. Ir. M. Saleh Pallu, M.Eng.', 'saleh.pallu@unhas.ac.id', 'Departemen Teknik Sipil, Universitas Hasanuddin');
            $ensureLayout($sub107);

            $docx107 = $this->createPhysicalFile(
                'SUB-2026-00107_Copyedited_Final.docx',
                "submissions/{$sub107->id}/SUB-2026-00107_Copyedited_Final.docx",
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                $sectionEditor->id,
                820000,
                'docx'
            );

            $pdf107 = $this->createPhysicalFile(
                'SUB-2026-00107_Galley_v1.pdf',
                "galleys/{$sub107->id}/SUB-2026-00107_Galley_v1.pdf",
                'application/pdf',
                $layoutEditor->id,
                5200000,
                'pdf'
            );

            $galley107 = Galley::updateOrCreate(
                ['submission_id' => $sub107->id, 'version' => 1],
                [
                    'file_id' => $pdf107->id,
                    'supersedes_galley_id' => null,
                    'format' => 'pdf',
                    'stage' => 'proofreading',
                    'status' => 'active',
                    'checksum_sha256' => $pdf107->checksum_sha256,
                    'approved_checksum' => null,
                    'label' => 'PDF Full Text (v1 Draf Awal)',
                ]
            );

            ProofAssignment::updateOrCreate(
                ['galley_id' => $galley107->id, 'proofreader_id' => $proofreader1->id],
                [
                    'file_id' => $pdf107->id,
                    'due_at' => now()->addDays(3),
                    'status' => 'in_progress',
                    'assigned_at' => now()->subDays(1),
                    'checksum_sha256' => substr($pdf107->checksum_sha256, 0, 64),
                    'message' => 'Naskah bersih untuk pemeriksaan akhir sebelum sign-off.',
                ]
            );
        }

        // =========================================================================
        // Skenario 3: SUB-2026-00105 — Menunggu Revisi Layout
        // =========================================================================
        $sub105 = Submission::firstOrNew(['submission_number' => 'SUB-2026-00105']);
        $sub105->journal_id = $journal->id;
        $sub105->section_id = $section->id;
        $sub105->title = 'Optimasi Pola Tanam Padi Sawah Irigasi Teknis di Daerah Aliran Sungai Saddang';
        $sub105->abstract = 'Kajian optimasi pola tanam padi sawah irigasi teknis di DAS Saddang.';
        $sub105->status = 'in_production';
        $sub105->save();
        $ensureAuthor($sub105, 'Dr. Ir. Muh. Asdar, M.Sc.', 'asdar@unhas.ac.id', 'Fakultas Pertanian, Universitas Hasanuddin');
        $ensureLayout($sub105);

        $docx105 = $this->createPhysicalFile(
            'SUB-2026-00105_Copyedited_Final.docx',
            "submissions/{$sub105->id}/SUB-2026-00105_Copyedited_Final.docx",
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            $sectionEditor->id,
            657408,
            'docx'
        );

        $pdf105 = $this->createPhysicalFile(
            'SUB-2026-00105_Galley_v2.pdf',
            "galleys/{$sub105->id}/SUB-2026-00105_Galley_v2.pdf",
            'application/pdf',
            $layoutEditor->id,
            3984588,
            'pdf'
        );

        $galley105 = Galley::updateOrCreate(
            ['submission_id' => $sub105->id, 'version' => 2],
            [
                'file_id' => $pdf105->id,
                'supersedes_galley_id' => null,
                'format' => 'pdf',
                'stage' => 'proofreading',
                'status' => 'active',
                'checksum_sha256' => $pdf105->checksum_sha256,
                'approved_checksum' => null,
                'label' => 'PDF Full Text (v2 Revisi)',
            ]
        );

        $assign105 = ProofAssignment::updateOrCreate(
            ['galley_id' => $galley105->id, 'proofreader_id' => $proofreader1->id],
            [
                'file_id' => $pdf105->id,
                'due_at' => now()->addDays(2),
                'status' => 'in_progress',
                'assigned_at' => now()->subDays(3),
                'checksum_sha256' => substr($pdf105->checksum_sha256, 0, 64),
                'message' => 'Mohon periksa ulang penataan gambar dan kelengkapan daftar pustaka.',
            ]
        );

        ProofreadingNote::updateOrCreate(
            ['proof_assignment_id' => $assign105->id, 'page' => 2, 'category' => 'typo'],
            [
                'galley_id' => $galley105->id,
                'file_id' => $pdf105->id,
                'checksum_sha256' => $pdf105->checksum_sha256,
                'severity' => 'minor',
                'description' => 'Kesalahan penulisan kata irigasi teknis pada judul subbab.',
                'resolved' => true,
                'resolution' => 'Sudah diperbaiki pada draf galai v2 sesuai teks copyediting final.',
                'resolved_at' => now()->subHours(4),
                'verified' => true,
                'verified_at' => now()->subHours(2),
                'verified_by' => $proofreader1->id,
                'version' => 1,
            ]
        );

        ProofreadingNote::updateOrCreate(
            ['proof_assignment_id' => $assign105->id, 'page' => 4, 'category' => 'layout'],
            [
                'galley_id' => $galley105->id,
                'file_id' => $pdf105->id,
                'checksum_sha256' => $pdf105->checksum_sha256,
                'severity' => 'major',
                'description' => 'Keterangan Gambar 2 terpotong margin bawah kolom sebelah kanan.',
                'resolved' => true,
                'resolution' => 'Ukuran skala gambar 2 diperkecil 10% dan posisi keterangan dipindahkan ke baris penuh.',
                'resolved_at' => now()->subHours(3),
                'verified' => false,
                'verified_at' => null,
                'verified_by' => null,
                'version' => 1,
            ]
        );

        ProofreadingNote::updateOrCreate(
            ['proof_assignment_id' => $assign105->id, 'page' => 7, 'category' => 'reference'],
            [
                'galley_id' => $galley105->id,
                'file_id' => $pdf105->id,
                'checksum_sha256' => $pdf105->checksum_sha256,
                'severity' => 'critical',
                'description' => 'Sitasi pada halaman 3 belum tercantum dalam daftar pustaka.',
                'resolved' => false,
                'resolution' => null,
                'resolved_at' => null,
                'verified' => false,
                'verified_at' => null,
                'verified_by' => null,
                'version' => 1,
            ]
        );

        // =========================================================================
        // Skenario 4: SUB-2026-00104 — Menunggu Revisi Layout
        // =========================================================================
        $sub104 = Submission::firstOrNew(['submission_number' => 'SUB-2026-00104']);
        $sub104->journal_id = $journal->id;
        $sub104->section_id = $section->id;
        $sub104->title = 'Evaluasi Efektivitas Layanan Pajak Daerah Online Sipapa di Bapenda Kota Makassar';
        $sub104->abstract = 'Evaluasi efektivitas layanan perpajakan online Sipapa Pemerintah Kota Makassar.';
        $sub104->status = 'in_production';
        $sub104->save();
        $ensureAuthor($sub104, 'Dr. Fatmawati, S.E., M.Si.', 'fatmawati@unhas.ac.id', 'Departemen Ilmu Ekonomi, Universitas Hasanuddin');
        $ensureLayout($sub104);

        $pdf104 = $this->createPhysicalFile(
            'SUB-2026-00104_Galley_v1.pdf',
            "galleys/{$sub104->id}/SUB-2026-00104_Galley_v1.pdf",
            'application/pdf',
            $layoutEditor->id,
            2450000,
            'pdf'
        );

        $galley104 = Galley::updateOrCreate(
            ['submission_id' => $sub104->id, 'version' => 1],
            [
                'file_id' => $pdf104->id,
                'format' => 'pdf',
                'stage' => 'proofreading',
                'status' => 'active',
                'checksum_sha256' => $pdf104->checksum_sha256,
                'label' => 'PDF Full Text (v1)',
            ]
        );

        $assign104 = ProofAssignment::updateOrCreate(
            ['galley_id' => $galley104->id, 'proofreader_id' => $proofreader1->id],
            [
                'file_id' => $pdf104->id,
                'due_at' => now()->addDays(3),
                'status' => 'in_progress',
                'assigned_at' => now()->subDays(2),
                'checksum_sha256' => substr($pdf104->checksum_sha256, 0, 64),
                'message' => 'Periksa ejaan dan konsistensi tabel.',
            ]
        );

        ProofreadingNote::updateOrCreate(
            ['proof_assignment_id' => $assign104->id, 'page' => 5, 'category' => 'layout'],
            [
                'galley_id' => $galley104->id,
                'file_id' => $pdf104->id,
                'checksum_sha256' => $pdf104->checksum_sha256,
                'severity' => 'major',
                'description' => 'Tabel terpotong pada margin kanan.',
                'resolved' => false,
                'version' => 1,
            ]
        );

        // =========================================================================
        // Skenario 5: SUB-2026-00103 — Proofreading Selesai & Disetujui (Completed)
        // =========================================================================
        $sub103 = Submission::firstOrNew(['submission_number' => 'SUB-2026-00103']);
        $sub103->journal_id = $journal->id;
        $sub103->section_id = $section->id;
        $sub103->title = 'Persepsi Masyarakat Terhadap Penerapan Transportasi Publik Teman Bus di Kawasan Aglomerasi Mamminasata';
        $sub103->abstract = 'Studi persepsi masyarakat kawasan Mamminasata terhadap layanan transportasi Teman Bus.';
        $sub103->status = 'in_production';
        $sub103->save();
        $ensureAuthor($sub103, 'Ir. Muhammad Arsyad, Ph.D.', 'arsyad@unhas.ac.id', 'Fakultas Teknik, Universitas Hasanuddin');
        $ensureLayout($sub103);

        $docx103 = $this->createPhysicalFile(
            'SUB-2026-00103_Copyedited_Final.docx',
            "submissions/{$sub103->id}/SUB-2026-00103_Copyedited_Final.docx",
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            $sectionEditor->id,
            727040,
            'docx'
        );

        $pdf103 = $this->createPhysicalFile(
            'SUB-2026-00103_Galley_v2_Approved.pdf',
            "galleys/{$sub103->id}/SUB-2026-00103_Galley_v2_Approved.pdf",
            'application/pdf',
            $layoutEditor->id,
            4299161,
            'pdf'
        );

        $galley103 = Galley::updateOrCreate(
            ['submission_id' => $sub103->id, 'version' => 2],
            [
                'file_id' => $pdf103->id,
                'format' => 'pdf',
                'stage' => 'approved',
                'status' => 'active',
                'checksum_sha256' => $pdf103->checksum_sha256,
                'approved_checksum' => $pdf103->checksum_sha256,
                'label' => 'PDF Full Text (v2 Disetujui)',
            ]
        );

        $assign103 = ProofAssignment::updateOrCreate(
            ['galley_id' => $galley103->id, 'proofreader_id' => $proofreader1->id],
            [
                'file_id' => $pdf103->id,
                'due_at' => now()->subDays(5),
                'status' => 'completed',
                'assigned_at' => now()->subDays(8),
                'checksum_sha256' => substr($pdf103->checksum_sha256, 0, 64),
                'message' => 'Pemeriksaan bukti baca telah tuntas dan disetujui.',
            ]
        );

        ProofreadingNote::updateOrCreate(
            ['proof_assignment_id' => $assign103->id, 'page' => 1, 'category' => 'typo'],
            [
                'galley_id' => $galley103->id,
                'file_id' => $pdf103->id,
                'checksum_sha256' => $pdf103->checksum_sha256,
                'severity' => 'minor',
                'description' => 'Perbaikan penulisan singkatan BRT Mamminasata pada kata kunci.',
                'resolved' => true,
                'resolution' => 'Sudah diperbaiki pada draf galai v2.',
                'resolved_at' => now()->subDays(6),
                'verified' => true,
                'verified_at' => now()->subDays(6),
                'verified_by' => $proofreader1->id,
                'version' => 1,
            ]
        );

        // =========================================================================
        // Skenario 6: SUB-2026-00102 — Proofreading Selesai & Disetujui (Completed)
        // =========================================================================
        $sub102 = Submission::firstOrNew(['submission_number' => 'SUB-2026-00102']);
        $sub102->journal_id = $journal->id;
        $sub102->section_id = $section->id;
        $sub102->title = 'Kajian Daya Dukung Lingkungan Hidup Berbasis Kemampuan Lahan di Pulau Lae-Lae Makassar';
        $sub102->abstract = 'Kajian daya dukung lingkungan berbasis kemampuan lahan Pulau Lae-Lae.';
        $sub102->status = 'in_production';
        $sub102->save();
        $ensureAuthor($sub102, 'Dr. Firman, S.Kel., M.Si.', 'firman@unhas.ac.id', 'Fakultas Ilmu Kelautan dan Perikanan, Universitas Hasanuddin');
        $ensureLayout($sub102);

        $pdf102 = $this->createPhysicalFile(
            'SUB-2026-00102_Galley_v1.pdf',
            "galleys/{$sub102->id}/SUB-2026-00102_Galley_v1.pdf",
            'application/pdf',
            $layoutEditor->id,
            3100000,
            'pdf'
        );

        $galley102 = Galley::updateOrCreate(
            ['submission_id' => $sub102->id, 'version' => 1],
            [
                'file_id' => $pdf102->id,
                'format' => 'pdf',
                'stage' => 'approved',
                'status' => 'active',
                'checksum_sha256' => $pdf102->checksum_sha256,
                'approved_checksum' => $pdf102->checksum_sha256,
                'label' => 'PDF Full Text (v1 Disetujui)',
            ]
        );

        ProofAssignment::updateOrCreate(
            ['galley_id' => $galley102->id, 'proofreader_id' => $proofreader1->id],
            [
                'file_id' => $pdf102->id,
                'due_at' => now()->subDays(9),
                'status' => 'completed',
                'assigned_at' => now()->subDays(12),
                'checksum_sha256' => substr($pdf102->checksum_sha256, 0, 64),
                'message' => 'Pemeriksaan bukti baca selesai.',
            ]
        );
    }

    /**
     * Membuat berkas fisik pada storage disk local dan mencatat entitas FileEntry.
     */
    protected function createPhysicalFile(
        string $originalName,
        string $storagePath,
        string $mimeType,
        string $createdBy,
        int $targetBytes = 1024,
        string $type = 'pdf'
    ): FileEntry {
        $disk = Storage::disk('local');

        $content = $type === 'pdf'
            ? $this->generateValidPdfBinary($originalName, $targetBytes)
            : $this->generateValidDocxBinary($originalName, $targetBytes);

        $disk->put($storagePath, $content);
        $size = (int) $disk->size($storagePath);
        $checksum = hash('sha256', (string) $disk->get($storagePath));

        $entry = FileEntry::updateOrCreate(
            ['storage_disk' => 'local', 'storage_path' => $storagePath],
            [
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'byte_size' => $size,
                'checksum_sha256' => $checksum,
                'virus_scan_status' => 'clean',
                'created_by' => $createdBy,
            ]
        );
        if ($type !== 'pdf' && preg_match('~^submissions/([^/]+)/~', $storagePath, $match)) {
            SubmissionFile::firstOrCreate(['submission_id' => $match[1], 'file_id' => $entry->id], ['genre' => 'copyedit_final', 'version' => 1]);
        }

        return $entry;
    }

    /**
     * Menghasilkan biner berkas PDF standar yang valid.
     */
    protected function generateValidPdfBinary(string $filename, int $targetBytes): string
    {
        $body = "Galai Publikasi Resmi SIRIDA Kota Makassar - {$filename}";
        $pdf = "%PDF-1.4\n"
            ."1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n"
            ."2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n"
            ."3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n"
            .'4 0 obj << /Length '.strlen($body)." >> stream\n"
            ."BT /F1 12 Tf 50 750 Td ({$body}) Tj ET\n"
            ."endstream\nendobj\n"
            ."5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n"
            ."xref\n0 6\n0000000000 65535 f \n"
            ."0000000009 00000 n \n0000000056 00000 n \n0000000111 00000 n \n0000000228 00000 n \n0000000320 00000 n \n"
            ."trailer << /Size 6 /Root 1 0 R >>\nstartxref\n390\n%%EOF\n";

        if ($targetBytes > strlen($pdf)) {
            $pdf .= '%'.str_repeat('0', $targetBytes - strlen($pdf));
        }

        return $pdf;
    }

    /**
     * Menghasilkan biner berkas DOCX valid berbasis ZipArchive.
     */
    protected function generateValidDocxBinary(string $filename, int $targetBytes): string
    {
        if (class_exists(\ZipArchive::class)) {
            $temp = tempnam(sys_get_temp_dir(), 'docx_proof');
            $zip = new \ZipArchive;
            if ($zip->open($temp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                    .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                    .'<Default Extension="xml" ContentType="application/xml"/>'
                    .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
                    .'</Types>';
                $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
                    .'</Relationships>';
                $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
                    .'<w:body>'
                    .'<w:p><w:r><w:t>Naskah Acuan Proofreading: '.htmlspecialchars($filename, ENT_XML1).'</w:t></w:r></w:p>'
                    .'</w:body>'
                    .'</w:document>';

                $zip->addFromString('[Content_Types].xml', $contentTypes);
                $zip->addFromString('_rels/.rels', $rels);
                $zip->addFromString('word/document.xml', $document);
                $zip->close();

                $binary = (string) file_get_contents($temp);
                @unlink($temp);

                return $binary;
            }
        }

        return "Naskah Acuan Proofreading BRIDA Makassar: {$filename}\n";
    }
}
