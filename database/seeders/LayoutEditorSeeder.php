<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\GalleyApproval;
use App\Models\LayoutAssignment;
use App\Models\ProductionEscalation;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LayoutEditorSeeder extends Seeder
{
    /**
     * Menjalankan seeding basis data realistis untuk Portal Layout Editor.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo seeder tidak boleh dijalankan pada production.');
        }

        if (Submission::count() === 0) {
            $this->call([
                JournalSeeder::class,
                SectionSeeder::class,
                SubmissionSeeder::class,
            ]);
        }

        // 1. User Layout Editor Utama
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

        // 2. User Proofreaders
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

        // 3. User Section Editor & EIC
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

        // Submisi
        $submissions = Submission::whereIn('submission_number', [
            'SUB-2026-00104',
            'SUB-2026-00105',
            'SUB-2026-00106',
            'SUB-2026-00107',
            'SUB-2026-00108',
            'SUB-2026-00109',
            'SUB-2026-00110',
            'SUB-2026-00111',
        ])->get()->keyBy('submission_number');

        foreach ($submissions->pluck('journal_id')->unique() as $journalId) {
            foreach ([[$layoutEditor, 'layout_editor'], [$proofreader1, 'proofreader'], [$proofreader2, 'proofreader'], [$sectionEditor, 'editor_in_chief']] as [$user, $role]) {
                UserRole::firstOrCreate(['user_id' => $user->id, 'journal_id' => $journalId, 'role_code' => $role]);
            }
        }

        // Skenario 1: SUB-2026-00106 — Drafting (Perlu Draf Galai Awal, belum ada PDF)
        if ($sub106 = $submissions->get('SUB-2026-00106')) {
            LayoutAssignment::updateOrCreate(
                ['submission_id' => $sub106->id, 'layout_editor_id' => $layoutEditor->id],
                [
                    'assigned_by' => $sectionEditor->id,
                    'assigned_at' => now()->subDays(3),
                    'due_at' => now()->addDays(6),
                    'revoked_at' => null,
                    'reason' => 'Susun tata letak 2-kolom format PDF standar dari naskah final copyeditor.',
                    'version' => 1,
                ]
            );

            // Berkas Word Acuan Copyeditor
            $this->createPhysicalFile(
                'SUB2026-00106_Copyedit_Final_Approved.docx',
                "submissions/{$sub106->id}/SUB2026-00106_Copyedit_Final_Approved.docx",
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                $sectionEditor->id,
                2149580,
                'docx'
            );
        }

        // Skenario 2: SUB-2026-00107 — Proofreading (v1 PDF terunggah, 2 Catatan Koreksi Proofreader)
        if ($sub107 = $submissions->get('SUB-2026-00107')) {
            LayoutAssignment::updateOrCreate(
                ['submission_id' => $sub107->id, 'layout_editor_id' => $layoutEditor->id],
                [
                    'assigned_by' => $sectionEditor->id,
                    'assigned_at' => now()->subDays(5),
                    'due_at' => now()->addDay(), // Mendekati batas waktu (is_urgent)
                    'revoked_at' => null,
                    'reason' => 'Periksa dan sesuaikan tata letak tabel dan referensi naskah.',
                    'version' => 1,
                ]
            );

            $this->createPhysicalFile(
                'SUB2026-00107_Copyedit_Final_Approved.docx',
                "submissions/{$sub107->id}/SUB2026-00107_Copyedit_Final_Approved.docx",
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                $sectionEditor->id,
                1890000,
                'docx'
            );

            $pdfFile107 = $this->createPhysicalFile(
                'SUB2026-00107_Galley_v1.pdf',
                "galleys/{$sub107->id}/SUB2026-00107_Galley_v1.pdf",
                'application/pdf',
                $layoutEditor->id,
                1420000,
                'pdf'
            );

            $galley107 = Galley::updateOrCreate(
                ['submission_id' => $sub107->id, 'version' => 1],
                [
                    'file_id' => $pdfFile107->id,
                    'supersedes_galley_id' => null,
                    'format' => 'pdf',
                    'stage' => 'proofreading',
                    'status' => 'active',
                    'checksum_sha256' => $pdfFile107->checksum_sha256,
                    'approved_checksum' => null,
                    'label' => 'PDF Full Text (v1)',
                ]
            );

            $proofAssign107 = ProofAssignment::updateOrCreate(
                ['galley_id' => $galley107->id, 'proofreader_id' => $proofreader1->id],
                [
                    'file_id' => $pdfFile107->id,
                    'due_at' => now()->addDays(2),
                    'status' => 'in_progress',
                    'assigned_at' => now()->subDays(2),
                    'checksum_sha256' => substr($pdfFile107->checksum_sha256, 0, 64),
                    'message' => 'Mohon periksa pemenggalan kata pada tabel dan kelengkapan daftar pustaka.',
                ]
            );

            ProofreadingNote::updateOrCreate(
                ['proof_assignment_id' => $proofAssign107->id, 'page' => 3, 'category' => 'layout'],
                [
                    'galley_id' => $galley107->id,
                    'file_id' => $pdfFile107->id,
                    'checksum_sha256' => $pdfFile107->checksum_sha256,
                    'severity' => 'critical',
                    'description' => 'Tabel 2 (Matriks Evaluasi Fitur Aplikasi Lorong) terpotong pada batas margin bawah halaman 3 dan perlu penataan ulang 2-kolom.',
                    'resolved' => false,
                    'resolution' => null,
                    'resolved_at' => null,
                    'version' => 1,
                ]
            );

            ProofreadingNote::updateOrCreate(
                ['proof_assignment_id' => $proofAssign107->id, 'page' => 8, 'category' => 'reference'],
                [
                    'galley_id' => $galley107->id,
                    'file_id' => $pdfFile107->id,
                    'checksum_sha256' => $pdfFile107->checksum_sha256,
                    'severity' => 'major',
                    'description' => 'Daftar Pustaka ke-12 belum mencantumkan tautan DOI yang valid sesuai standar APA 7th.',
                    'resolved' => false,
                    'resolution' => null,
                    'resolved_at' => null,
                    'version' => 1,
                ]
            );
        }

        // Skenario 3: SUB-2026-00108 — Escalated (Tiket Eskalasi Tata Letak Aktif)
        if ($sub108 = $submissions->get('SUB-2026-00108')) {
            LayoutAssignment::updateOrCreate(
                ['submission_id' => $sub108->id, 'layout_editor_id' => $layoutEditor->id],
                [
                    'assigned_by' => $sectionEditor->id,
                    'assigned_at' => now()->subDays(4),
                    'due_at' => now()->addDays(4),
                    'revoked_at' => null,
                    'reason' => 'Perhatikan kualitas gambar peta kontur.',
                    'version' => 1,
                ]
            );

            $this->createPhysicalFile(
                'SUB2026-00108_Copyedit_Final_Approved.docx',
                "submissions/{$sub108->id}/SUB2026-00108_Copyedit_Final_Approved.docx",
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                $sectionEditor->id,
                2450000,
                'docx'
            );

            $pdfFile108 = $this->createPhysicalFile(
                'SUB2026-00108_Galley_v1.pdf',
                "galleys/{$sub108->id}/SUB2026-00108_Galley_v1.pdf",
                'application/pdf',
                $layoutEditor->id,
                1650000,
                'pdf'
            );

            $galley108 = Galley::updateOrCreate(
                ['submission_id' => $sub108->id, 'version' => 1],
                [
                    'file_id' => $pdfFile108->id,
                    'supersedes_galley_id' => null,
                    'format' => 'pdf',
                    'stage' => 'draft',
                    'status' => 'active',
                    'checksum_sha256' => $pdfFile108->checksum_sha256,
                    'approved_checksum' => null,
                    'label' => 'PDF Full Text (v1)',
                ]
            );

            $proofAssign108 = ProofAssignment::updateOrCreate(
                ['galley_id' => $galley108->id, 'proofreader_id' => $proofreader2->id],
                [
                    'file_id' => $pdfFile108->id,
                    'due_at' => now()->addDays(3),
                    'status' => 'in_progress',
                    'assigned_at' => now()->subDays(2),
                    'checksum_sha256' => substr($pdfFile108->checksum_sha256, 0, 64),
                    'message' => 'Pemeriksaan resolusi gambar peta.',
                ]
            );

            ProofreadingNote::updateOrCreate(
                ['proof_assignment_id' => $proofAssign108->id, 'page' => 5, 'category' => 'layout'],
                [
                    'galley_id' => $galley108->id,
                    'file_id' => $pdfFile108->id,
                    'checksum_sha256' => $pdfFile108->checksum_sha256,
                    'severity' => 'major',
                    'description' => 'Gambar peta kontur batimetri pantai berdimensi lanskap sangat lebar, memerlukan persetujuan Section Editor untuk penataan lanskap.',
                    'resolved' => false,
                    'resolution' => null,
                    'resolved_at' => null,
                    'version' => 1,
                ]
            );

            ProductionEscalation::updateOrCreate(
                ['submission_id' => $sub108->id, 'status' => 'open'],
                [
                    'id' => 'ESC-2026-0071',
                    'copyedit_id' => null,
                    'galley_id' => $galley108->id,
                    'opened_by' => $layoutEditor->id,
                    'reason' => 'Gambar peta kontur batimetri pantai berdimensi lanskap sangat lebar, memerlukan persetujuan Section Editor untuk penambahan halaman lampiran peta berformat lanskap.',
                    'resolution' => null,
                    'resume_stage' => null,
                    'version' => 1,
                ]
            );
        }

        // Skenario 4: SUB-2026-00109 — Ready (v2 Galai Disetujui EIC, Siap Terbit)
        if ($sub109 = $submissions->get('SUB-2026-00109')) {
            LayoutAssignment::updateOrCreate(
                ['submission_id' => $sub109->id, 'layout_editor_id' => $layoutEditor->id],
                [
                    'assigned_by' => $sectionEditor->id,
                    'assigned_at' => now()->subDays(8),
                    'due_at' => now()->addDays(9),
                    'revoked_at' => null,
                    'reason' => 'Tata letak publikasi final nomor edisi.',
                    'version' => 2,
                ]
            );

            $this->createPhysicalFile(
                'SUB2026-00109_Copyedit_Final_Approved.docx',
                "submissions/{$sub109->id}/SUB2026-00109_Copyedit_Final_Approved.docx",
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                $sectionEditor->id,
                1980000,
                'docx'
            );

            $pdfFile109v1 = $this->createPhysicalFile(
                'SUB2026-00109_Galley_v1.pdf',
                "galleys/{$sub109->id}/SUB2026-00109_Galley_v1.pdf",
                'application/pdf',
                $layoutEditor->id,
                1350000,
                'pdf'
            );

            $galley109v1 = Galley::updateOrCreate(
                ['submission_id' => $sub109->id, 'version' => 1],
                [
                    'file_id' => $pdfFile109v1->id,
                    'supersedes_galley_id' => null,
                    'format' => 'pdf',
                    'stage' => 'proofreading',
                    'status' => 'superseded',
                    'checksum_sha256' => $pdfFile109v1->checksum_sha256,
                    'approved_checksum' => null,
                    'label' => 'PDF Full Text (v1 Draf Awal)',
                ]
            );

            $pdfFile109v2 = $this->createPhysicalFile(
                'SUB2026-00109_Galley_v2.pdf',
                "galleys/{$sub109->id}/SUB2026-00109_Galley_v2.pdf",
                'application/pdf',
                $layoutEditor->id,
                1365000,
                'pdf'
            );

            $galley109v2 = Galley::updateOrCreate(
                ['submission_id' => $sub109->id, 'version' => 2],
                [
                    'file_id' => $pdfFile109v2->id,
                    'supersedes_galley_id' => $galley109v1->id,
                    'format' => 'pdf',
                    'stage' => 'approved',
                    'status' => 'active',
                    'checksum_sha256' => $pdfFile109v2->checksum_sha256,
                    'approved_checksum' => $pdfFile109v2->checksum_sha256,
                    'label' => 'PDF Full Text (v2 Final Terbit)',
                ]
            );

            $proofAssign109 = ProofAssignment::updateOrCreate(
                ['galley_id' => $galley109v1->id, 'proofreader_id' => $proofreader1->id],
                [
                    'file_id' => $pdfFile109v1->id,
                    'due_at' => now()->subDays(2),
                    'status' => 'completed',
                    'assigned_at' => now()->subDays(6),
                    'checksum_sha256' => substr($pdfFile109v1->checksum_sha256, 0, 64),
                    'message' => 'Pemeriksaan final running DOI.',
                ]
            );

            ProofreadingNote::updateOrCreate(
                ['proof_assignment_id' => $proofAssign109->id, 'category' => 'metadata'],
                [
                    'galley_id' => $galley109v1->id,
                    'file_id' => $pdfFile109v1->id,
                    'checksum_sha256' => $pdfFile109v1->checksum_sha256,
                    'page' => 1,
                    'severity' => 'minor',
                    'description' => 'Penyesuaian running header DOI terbitan.',
                    'resolved' => true,
                    'resolution' => 'Header running DOI telah disesuaikan dengan nomor volume edisi terbitan pada berkas galai v2.',
                    'resolved_at' => now()->subDays(1),
                    'version' => 1,
                ]
            );

            GalleyApproval::updateOrCreate(
                ['galley_id' => $galley109v2->id, 'stage' => 'author_signoff'],
                [
                    'approved_by' => $sub109->author_id ?? $sectionEditor->id,
                    'checksum_sha256' => $galley109v2->checksum_sha256,
                    'approved_at' => now()->subHours(12),
                    'invalidated_at' => null,
                    'file_id' => $pdfFile109v2->id,
                    'note' => 'Tata letak sudah sempurna dan disetujui untuk terbit.',
                ]
            );

            GalleyApproval::updateOrCreate(
                ['galley_id' => $galley109v2->id, 'stage' => 'editor_final'],
                [
                    'approved_by' => $sectionEditor->id,
                    'checksum_sha256' => $galley109v2->checksum_sha256,
                    'approved_at' => now()->subHours(6),
                    'invalidated_at' => null,
                    'file_id' => $pdfFile109v2->id,
                    'note' => 'Disetujui untuk dipublikasikan pada Edisi Terbitan Volume 4 Nomor 2.',
                ]
            );
        }

        // Skenario 5: SUB-2026-00110 — Drafting (Baru diserahkan dari Copy Editor)
        if ($sub110 = $submissions->get('SUB-2026-00110')) {
            LayoutAssignment::updateOrCreate(
                ['submission_id' => $sub110->id, 'layout_editor_id' => $layoutEditor->id],
                [
                    'assigned_by' => $sectionEditor->id,
                    'assigned_at' => now()->subDay(),
                    'due_at' => now()->addDays(7),
                    'revoked_at' => null,
                    'reason' => 'Naskah baru diserahkan dari tahap copyediting.',
                    'version' => 1,
                ]
            );

            $this->createPhysicalFile(
                'SUB2026-00110_Copyedit_Final_Approved.docx',
                "submissions/{$sub110->id}/SUB2026-00110_Copyedit_Final_Approved.docx",
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                $sectionEditor->id,
                1750000,
                'docx'
            );
        }

        // Skenario 6: SUB-2026-00111 — Ready (Galai disetujui, siap terbit)
        if ($sub111 = $submissions->get('SUB-2026-00111')) {
            LayoutAssignment::updateOrCreate(
                ['submission_id' => $sub111->id, 'layout_editor_id' => $layoutEditor->id],
                [
                    'assigned_by' => $sectionEditor->id,
                    'assigned_at' => now()->subDays(6),
                    'due_at' => now()->addDay(),
                    'revoked_at' => null,
                    'reason' => 'Periksa galai final siap terbit.',
                    'version' => 2,
                ]
            );

            $pdfFile111 = $this->createPhysicalFile(
                'SUB2026-00111_Galley_v2.pdf',
                "galleys/{$sub111->id}/SUB2026-00111_Galley_v2.pdf",
                'application/pdf',
                $layoutEditor->id,
                1520000,
                'pdf'
            );

            $galley111 = Galley::updateOrCreate(
                ['submission_id' => $sub111->id, 'version' => 2],
                [
                    'file_id' => $pdfFile111->id,
                    'supersedes_galley_id' => null,
                    'format' => 'pdf',
                    'stage' => 'approved',
                    'status' => 'active',
                    'checksum_sha256' => $pdfFile111->checksum_sha256,
                    'approved_checksum' => $pdfFile111->checksum_sha256,
                    'label' => 'PDF Full Text (v2 Final)',
                ]
            );

            GalleyApproval::updateOrCreate(
                ['galley_id' => $galley111->id, 'stage' => 'editor_final'],
                [
                    'approved_by' => $sectionEditor->id,
                    'checksum_sha256' => $galley111->checksum_sha256,
                    'approved_at' => now()->subHours(2),
                    'invalidated_at' => null,
                    'file_id' => $pdfFile111->id,
                    'note' => 'Siap terbit.',
                ]
            );
        }

        // Skenario 7: SUB-2026-00104 — Ready
        if ($sub104 = $submissions->get('SUB-2026-00104')) {
            LayoutAssignment::updateOrCreate(
                ['submission_id' => $sub104->id, 'layout_editor_id' => $layoutEditor->id],
                [
                    'assigned_by' => $sectionEditor->id,
                    'assigned_at' => now()->subDays(10),
                    'due_at' => now()->addDays(5),
                    'revoked_at' => null,
                    'reason' => 'Tata letak naskah terbitan volume berjalan.',
                    'version' => 1,
                ]
            );

            $pdfFile104 = $this->createPhysicalFile(
                'SUB2026-00104_Galley_v1.pdf',
                "galleys/{$sub104->id}/SUB2026-00104_Galley_v1.pdf",
                'application/pdf',
                $layoutEditor->id,
                1280000,
                'pdf'
            );

            $galley104 = Galley::updateOrCreate(
                ['submission_id' => $sub104->id, 'version' => 1],
                [
                    'file_id' => $pdfFile104->id,
                    'supersedes_galley_id' => null,
                    'format' => 'pdf',
                    'stage' => 'approved',
                    'status' => 'active',
                    'checksum_sha256' => $pdfFile104->checksum_sha256,
                    'approved_checksum' => $pdfFile104->checksum_sha256,
                    'label' => 'PDF Full Text (v1)',
                ]
            );

            GalleyApproval::updateOrCreate(
                ['galley_id' => $galley104->id, 'stage' => 'editor_final'],
                [
                    'approved_by' => $sectionEditor->id,
                    'checksum_sha256' => $galley104->checksum_sha256,
                    'approved_at' => now()->subDays(1),
                    'invalidated_at' => null,
                    'file_id' => $pdfFile104->id,
                    'note' => 'Disetujui EIC.',
                ]
            );
        }

        // Skenario 8: SUB-2026-00105 — Drafting
        if ($sub105 = $submissions->get('SUB-2026-00105')) {
            LayoutAssignment::updateOrCreate(
                ['submission_id' => $sub105->id, 'layout_editor_id' => $layoutEditor->id],
                [
                    'assigned_by' => $sectionEditor->id,
                    'assigned_at' => now()->subDays(2),
                    'due_at' => now()->addDays(8),
                    'revoked_at' => null,
                    'reason' => 'Draf galai awal naskah baru.',
                    'version' => 1,
                ]
            );
        }
    }

    /**
     * Memastikan berkas fisik tersimpan pada disk lokal dengan byte size dan sha256 checksum yang presisi.
     */
    private function createPhysicalFile(
        string $originalName,
        string $storagePath,
        string $mimeType,
        string $createdBy,
        int $targetBytes = 0,
        string $type = 'pdf'
    ): FileEntry {
        $disk = Storage::disk('local');

        if (! $disk->exists($storagePath)) {
            $content = $type === 'pdf'
                ? $this->generateSamplePdf($originalName, $targetBytes)
                : $this->generateSampleDocx($originalName, $targetBytes);

            $disk->put($storagePath, $content);
        }

        $actualBytes = (int) $disk->size($storagePath);
        $actualSha256 = hash('sha256', (string) $disk->get($storagePath));

        $entry = FileEntry::updateOrCreate(
            ['original_name' => $originalName],
            [
                'storage_disk' => 'local',
                'storage_path' => $storagePath,
                'mime_type' => $mimeType,
                'byte_size' => $actualBytes,
                'checksum_sha256' => $actualSha256,
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
     * Menghasilkan berkas PDF yang valid sesuai standar Adobe PDF-1.4.
     */
    private function generateSamplePdf(string $title, int $targetBytes = 0): string
    {
        $stream = "BT /F1 20 Tf 50 770 Td (Jurnal BRIDA Kota Makassar) Tj ET\n"
            .'BT /F1 12 Tf 50 740 Td ('.addcslashes($title, '()\\').") Tj ET\n"
            ."BT /F1 10 Tf 50 710 Td (Format Galai Resmi Publikasi Ilmiah - Standar Dua Kolom) Tj ET\n";

        $len = strlen($stream);
        $pdf = "%PDF-1.4\n"
            ."1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n"
            ."2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n"
            ."3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n"
            ."4 0 obj << /Length {$len} >> stream\n{$stream}endstream\nendobj\n"
            ."5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n"
            ."xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000056 00000 n \n0000000111 00000 n \n0000000228 00000 n \n0000000".sprintf('%03d', 280 + $len)." 00000 n \n"
            ."trailer << /Size 6 /Root 1 0 R >>\nstartxref\n".(350 + $len)."\n%%EOF\n";

        if ($targetBytes > strlen($pdf)) {
            $padding = $targetBytes - strlen($pdf);
            $pdf .= '%'.str_repeat('0', max(0, $padding - 2))."\n";
        }

        return $pdf;
    }

    /**
     * Menghasilkan berkas Word DOCX yang valid untuk naskah acuan copyediting.
     */
    private function generateSampleDocx(string $filename, int $targetBytes = 0): string
    {
        if (class_exists(\ZipArchive::class)) {
            $temp = tempnam(sys_get_temp_dir(), 'docx_layout_seeder');
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
                    .'<w:p><w:r><w:rPr><w:b/><w:sz w:val="32"/></w:rPr><w:t>Jurnal Penelitian BRIDA Makassar</w:t></w:r></w:p>'
                    .'<w:p><w:r><w:t>Naskah Acuan Layout: '.htmlspecialchars($filename, ENT_XML1).'</w:t></w:r></w:p>'
                    .'</w:body>'
                    .'</w:document>';

                $zip->addFromString('[Content_Types].xml', $contentTypes);
                $zip->addFromString('_rels/.rels', $rels);
                $zip->addFromString('word/document.xml', $document);
                $zip->close();

                $baseSize = filesize($temp);
                if ($targetBytes > $baseSize) {
                    $overhead = 118;
                    $neededPayload = max(0, $targetBytes - $baseSize - $overhead);
                    if ($neededPayload > 0) {
                        $zip = new \ZipArchive;
                        $zip->open($temp);
                        $zip->addFromString('word/media/filler.bin', str_repeat('X', $neededPayload));
                        $zip->setCompressionName('word/media/filler.bin', \ZipArchive::CM_STORE);
                        $zip->close();
                    }
                }

                $binary = (string) file_get_contents($temp);
                @unlink($temp);

                return $binary;
            }
        }

        if ($targetBytes > 0) {
            $header = "Naskah Acuan Layout BRIDA Makassar: {$filename}\n";
            $padding = max(0, $targetBytes - strlen($header));

            return $header.str_repeat(' ', $padding);
        }

        return "Naskah Acuan Layout BRIDA Makassar: {$filename}\n";
    }
}
