<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FileEntry;
use App\Models\Galley;
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
        ])->get()->keyBy('submission_number');

        // Skenario 1: SUB-2026-00105 — Review Galai v2 dengan Catatan Terbuka & Verifikasi
        if ($sub105 = $submissions->get('SUB-2026-00105')) {
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

            // Note 1: Minor Typo - Resolved & Verified
            ProofreadingNote::updateOrCreate(
                ['proof_assignment_id' => $assign105->id, 'page' => 2, 'category' => 'typo'],
                [
                    'galley_id' => $galley105->id,
                    'file_id' => $pdf105->id,
                    'checksum_sha256' => $pdf105->checksum_sha256,
                    'severity' => 'minor',
                    'description' => 'Kesalahan penulisan kata "irigasi" seharusnya "irigasi teknis" pada judul subbab.',
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

            // Note 3: Critical Reference - Belum Selesai (Menahan Signoff Guard)
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
        }

        // Skenario 2: SUB-2026-00104 — Menunggu Tindak Lanjut Layout
        if ($sub104 = $submissions->get('SUB-2026-00104')) {
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
                    'supersedes_galley_id' => null,
                    'format' => 'pdf',
                    'stage' => 'proofreading',
                    'status' => 'active',
                    'checksum_sha256' => $pdf104->checksum_sha256,
                    'approved_checksum' => null,
                    'label' => 'PDF Full Text (v1 Draf Awal)',
                ]
            );

            ProofAssignment::updateOrCreate(
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
