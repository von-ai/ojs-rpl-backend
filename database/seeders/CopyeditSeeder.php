<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Copyedit;
use App\Models\CopyeditApproval;
use App\Models\CopyeditApprovedFile;
use App\Models\CopyeditOutput;
use App\Models\FileEntry;
use App\Models\ProductionEscalation;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CopyeditSeeder extends Seeder
{
    /**
     * Seed penugasan copyediting realistis untuk 5 skenario siklus hidup OJS.
     */
    public function run(): void
    {
        // 1. User Copy Editor Utama
        $copyEditor = User::firstOrCreate(
            ['email' => 'andi.kurniawan@brida.makassar.go.id'],
            [
                'username' => 'andi.kurniawan',
                'name' => 'Andi Kurniawan, S.Hum., M.Pd.',
                'full_name' => 'Andi Kurniawan, S.Hum., M.Pd.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. User Layout Editor
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

        // 3. User Section Editor / EIC
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

        // Pastikan submission dasar ada
        $submissions = Submission::whereIn('submission_number', [
            'SUB-2026-00106',
            'SUB-2026-00107',
            'SUB-2026-00108',
            'SUB-2026-00109',
            'SUB-2026-00110',
        ])->get()->keyBy('submission_number');

        // Skenario 1: SUB-2026-00106 — Aktif & Menunggu Respon Penulis
        if ($sub106 = $submissions->get('SUB-2026-00106')) {
            $srcFile = FileEntry::firstOrCreate(
                ['original_name' => 'SUB2026-00106_Source_Accepted.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'submissions/SUB2026-00106_Source_Accepted.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 2244000,
                    'checksum_sha256' => hash('sha256', 'source_106'),
                    'created_by' => $sub106->author_id,
                ]
            );

            $copyedit106 = Copyedit::firstOrCreate(
                ['submission_id' => $sub106->id],
                [
                    'copy_editor_id' => $copyEditor->id,
                    'source_file_id' => $srcFile->id,
                    'status' => 'in_progress',
                    'due_at' => now()->addDays(3),
                    'notes' => 'Telah dilakukan standardisasi sitasi APA 7th.',
                ]
            );

            $outFile1 = FileEntry::firstOrCreate(
                ['original_name' => 'SUB2026-00106_Copyedit_Final_Clean.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'copyedits/SUB2026-00106_Copyedit_Final_Clean.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 2149580,
                    'checksum_sha256' => hash('sha256', 'output_106_1'),
                    'created_by' => $copyEditor->id,
                ]
            );

            CopyeditOutput::firstOrCreate(
                ['copyedit_id' => $copyedit106->id, 'file_id' => $outFile1->id],
                ['position' => 1]
            );

            CopyeditApproval::firstOrCreate(
                ['copyedit_id' => $copyedit106->id, 'output_version' => 1],
                [
                    'author_id' => $sub106->author_id ?? $copyEditor->id,
                    'decision' => 'requested',
                    'approved_at' => now(),
                    'note' => 'Mohon diperiksa kembali format penulisan tabel.',
                ]
            );
        }

        // Skenario 2: SUB-2026-00107 — Resolusi Mediasi Selesai Diputuskan
        if ($sub107 = $submissions->get('SUB-2026-00107')) {
            $srcFile107 = FileEntry::firstOrCreate(
                ['original_name' => 'SUB2026-00107_Source.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'submissions/SUB2026-00107_Source.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 1800000,
                    'checksum_sha256' => hash('sha256', 'source_107'),
                    'created_by' => $sub107->author_id,
                ]
            );

            $copyedit107 = Copyedit::firstOrCreate(
                ['submission_id' => $sub107->id],
                [
                    'copy_editor_id' => $copyEditor->id,
                    'source_file_id' => $srcFile107->id,
                    'status' => 'in_progress',
                    'due_at' => now()->addDays(7),
                ]
            );

            ProductionEscalation::firstOrCreate(
                ['copyedit_id' => $copyedit107->id],
                [
                    'submission_id' => $sub107->id,
                    'opened_by' => $copyEditor->id,
                    'reason' => '[Ketidaksesuaian Terminologi] Penulis meminta penggunaan istilah lokal tanpa padanan baku.',
                    'status' => 'resolved',
                    'resolution' => 'Gunakan padanan istilah baku sesuai KBBI V dengan catatan kaki penjelasan.',
                    'resume_stage' => 'in_progress',
                ]
            );
        }

        // Skenario 3: SUB-2026-00108 — Eskalasi Mediasi Redaksi Aktif
        if ($sub108 = $submissions->get('SUB-2026-00108')) {
            $srcFile108 = FileEntry::firstOrCreate(
                ['original_name' => 'SUB2026-00108_Source.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'submissions/SUB2026-00108_Source.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 1950000,
                    'checksum_sha256' => hash('sha256', 'source_108'),
                    'created_by' => $sub108->author_id,
                ]
            );

            $copyedit108 = Copyedit::firstOrCreate(
                ['submission_id' => $sub108->id],
                [
                    'copy_editor_id' => $copyEditor->id,
                    'source_file_id' => $srcFile108->id,
                    'status' => 'in_progress',
                    'due_at' => now()->addDays(4),
                ]
            );

            ProductionEscalation::firstOrCreate(
                ['copyedit_id' => $copyedit108->id],
                [
                    'submission_id' => $sub108->id,
                    'opened_by' => $copyEditor->id,
                    'reason' => '[Perbedaan Pendapat Substansi] Ketidaksepakatan format tabel statistik multivariat.',
                    'status' => 'open',
                ]
            );
        }

        // Skenario 4: SUB-2026-00109 — Disetujui Penulis & Siap Serah Terima
        if ($sub109 = $submissions->get('SUB-2026-00109')) {
            $srcFile109 = FileEntry::firstOrCreate(
                ['original_name' => 'SUB2026-00109_Source.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'submissions/SUB2026-00109_Source.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 2050000,
                    'checksum_sha256' => hash('sha256', 'source_109'),
                    'created_by' => $sub109->author_id,
                ]
            );

            $copyedit109 = Copyedit::firstOrCreate(
                ['submission_id' => $sub109->id],
                [
                    'copy_editor_id' => $copyEditor->id,
                    'source_file_id' => $srcFile109->id,
                    'status' => 'in_progress',
                    'due_at' => now()->addDays(5),
                ]
            );

            $outFile109 = FileEntry::firstOrCreate(
                ['original_name' => 'SUB2026-00109_Copyedit_Approved.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'copyedits/SUB2026-00109_Copyedit_Approved.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 2010000,
                    'checksum_sha256' => hash('sha256', 'output_109'),
                    'created_by' => $copyEditor->id,
                ]
            );

            CopyeditOutput::firstOrCreate(
                ['copyedit_id' => $copyedit109->id, 'file_id' => $outFile109->id],
                ['position' => 1]
            );

            $approval109 = CopyeditApproval::firstOrCreate(
                ['copyedit_id' => $copyedit109->id, 'output_version' => 1],
                [
                    'author_id' => $sub109->author_id ?? $copyEditor->id,
                    'decision' => 'approved',
                    'approved_at' => now(),
                    'note' => 'Disetujui tanpa koreksi lanjutan.',
                ]
            );

            CopyeditApprovedFile::firstOrCreate(
                ['approval_id' => $approval109->id, 'file_id' => $outFile109->id],
                ['checksum_sha256' => $outFile109->checksum_sha256]
            );
        }
    }
}
