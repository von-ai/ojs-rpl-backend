<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\ProductionEscalation;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\User;
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

        $submissions = Submission::whereIn('submission_number', [
            'SUB-2026-00102',
            'SUB-2026-00103',
            'SUB-2026-00104',
            'SUB-2026-00105',
            'SUB-2026-00106',
            'SUB-2026-00107',
            'SUB-2026-00108',
        ])->get()->keyBy('submission_number');

        // Skenario 1: SUB-2026-00106 — Review Galai v2 dengan Catatan Terbuka & Verifikasi
        if ($sub106 = $submissions->get('SUB-2026-00106')) {
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
                ['proof_assignment_id' => $assign106->id, 'page' => 2, 'category' => 'typo'],
                [
                    'galley_id' => $galley106->id,
                    'file_id' => $pdf106->id,
                    'checksum_sha256' => $pdf106->checksum_sha256,
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
                ['proof_assignment_id' => $assign106->id, 'page' => 4, 'category' => 'layout'],
                [
                    'galley_id' => $galley106->id,
                    'file_id' => $pdf106->id,
                    'checksum_sha256' => $pdf106->checksum_sha256,
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
                ['proof_assignment_id' => $assign106->id, 'page' => 7, 'category' => 'reference'],
                [
                    'galley_id' => $galley106->id,
                    'file_id' => $pdf106->id,
                    'checksum_sha256' => $pdf106->checksum_sha256,
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
        }

        // Skenario 2: SUB-2026-00107 — Naskah Bersih Siap Sign-off
        if ($sub107 = $submissions->get('SUB-2026-00107')) {
            $docx107 = $this->createPhysicalFile(
                'SUB-2026-00107_Copyedited_Final.docx',
                "submissions/{$sub107->id}/SUB-2026-00107_Copyedited_Final.docx",
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                $sectionEditor->id,
                839680,
                'docx'
            );

            $pdf107 = $this->createPhysicalFile(
                'SUB-2026-00107_Galley_v1.pdf',
                "galleys/{$sub107->id}/SUB-2026-00107_Galley_v1.pdf",
                'application/pdf',
                $layoutEditor->id,
                5452595,
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
                    'due_at' => now()->addDay(),
                    'status' => 'assigned',
                    'assigned_at' => now()->subDay(),
                    'checksum_sha256' => substr($pdf107->checksum_sha256, 0, 64),
                    'message' => 'Naskah siap diperiksa bukti baca sebelum diajukan persetujuan akhir.',
                ]
            );
        }

        // Skenario 3: SUB-2026-00103 — Proofreading Selesai & Telah Disetujui (Sign-off)
        if ($sub103 = $submissions->get('SUB-2026-00103')) {
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
                    'supersedes_galley_id' => null,
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
        }

        // Skenario 4: SUB-2026-00108 — Naskah dengan Tiket Eskalasi Terbuka
        if ($sub108 = $submissions->get('SUB-2026-00108')) {
            $pdf108 = $this->createPhysicalFile(
                'SUB-2026-00108_Galley_v1.pdf',
                "galleys/{$sub108->id}/SUB-2026-00108_Galley_v1.pdf",
                'application/pdf',
                $layoutEditor->id,
                1650000,
                'pdf'
            );

            $galley108 = Galley::updateOrCreate(
                ['submission_id' => $sub108->id, 'version' => 1],
                [
                    'file_id' => $pdf108->id,
                    'supersedes_galley_id' => null,
                    'format' => 'pdf',
                    'stage' => 'proofreading',
                    'status' => 'active',
                    'checksum_sha256' => $pdf108->checksum_sha256,
                    'approved_checksum' => null,
                    'label' => 'PDF Full Text (v1)',
                ]
            );

            ProofAssignment::updateOrCreate(
                ['galley_id' => $galley108->id, 'proofreader_id' => $proofreader2->id],
                [
                    'file_id' => $pdf108->id,
                    'due_at' => now()->addDays(3),
                    'status' => 'in_progress',
                    'assigned_at' => now()->subDays(2),
                    'checksum_sha256' => substr($pdf108->checksum_sha256, 0, 64),
                    'message' => 'Periksa konsistensi penomoran rumus pada subbab 3.',
                ]
            );

            ProductionEscalation::updateOrCreate(
                ['submission_id' => $sub108->id, 'opened_by' => $proofreader2->id],
                [
                    'copyedit_id' => null,
                    'galley_id' => $galley108->id,
                    'reason' => 'Terdapat bagian teks yang terpotong dari versi copyediting asli pada rumus matematis halaman 5.',
                    'status' => 'open',
                    'resolution' => null,
                    'resume_stage' => null,
                    'version' => 1,
                ]
            );
        }
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

        return FileEntry::updateOrCreate(
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
