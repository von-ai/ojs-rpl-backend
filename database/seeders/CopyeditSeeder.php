<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Copyedit;
use App\Models\CopyeditApproval;
use App\Models\CopyeditApprovedFile;
use App\Models\CopyeditOutput;
use App\Models\FileEntry;
use App\Models\LayoutAssignment;
use App\Models\ProductionEscalation;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CopyeditSeeder extends Seeder
{
    /**
     * Seed penugasan copyediting realistis untuk 6 skenario siklus hidup OJS.
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
            'SUB-2026-00111',
        ])->get()->keyBy('submission_number');

        // Skenario 1: SUB-2026-00106 — Aktif & Menunggu Respon Penulis
        if ($sub106 = $submissions->get('SUB-2026-00106')) {
            $srcFile = FileEntry::updateOrCreate(
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

            $outFile1 = FileEntry::updateOrCreate(
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

            $outFile2 = FileEntry::updateOrCreate(
                ['original_name' => 'SUB2026-00106_Supplementary_Data.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'copyedits/SUB2026-00106_Supplementary_Data.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 1174405,
                    'checksum_sha256' => hash('sha256', 'output_106_2'),
                    'created_by' => $copyEditor->id,
                ]
            );

            CopyeditOutput::firstOrCreate(
                ['copyedit_id' => $copyedit106->id, 'file_id' => $outFile2->id],
                ['position' => 2]
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
            $srcFile107 = FileEntry::updateOrCreate(
                ['original_name' => 'SUB2026-00107_Source.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'submissions/SUB2026-00107_Source.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 2044723,
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
            $srcFile108 = FileEntry::updateOrCreate(
                ['original_name' => 'SUB2026-00108_Source.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'submissions/SUB2026-00108_Source.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 3565158,
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
            $srcFile109 = FileEntry::updateOrCreate(
                ['original_name' => 'SUB2026-00109_Source.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'submissions/SUB2026-00109_Source.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 1887436,
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

            $outFile109 = FileEntry::updateOrCreate(
                ['original_name' => 'SUB2026-00109_Copyedit_Approved.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'copyedits/SUB2026-00109_Copyedit_Approved.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 1835008,
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

        // Skenario 5: SUB-2026-00110 — Selesai Diserahterimakan (Mode Arsip / Completed)
        if ($sub110 = $submissions->get('SUB-2026-00110')) {
            $srcFile110 = FileEntry::updateOrCreate(
                ['original_name' => 'SUB2026-00110_Accepted_Sampah_Tallo.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'submissions/SUB2026-00110_Accepted_Sampah_Tallo.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 2621440,
                    'checksum_sha256' => hash('sha256', 'source_110'),
                    'created_by' => $sub110->author_id,
                ]
            );

            $copyedit110 = Copyedit::firstOrCreate(
                ['submission_id' => $sub110->id],
                [
                    'copy_editor_id' => $copyEditor->id,
                    'source_file_id' => $srcFile110->id,
                    'status' => 'completed',
                    'due_at' => now()->subDays(5),
                    'notes' => 'Telah diserahkan ke tim tata letak dengan instruksi template 2 kolom.',
                ]
            );

            $outFile110 = FileEntry::updateOrCreate(
                ['original_name' => 'SUB2026-00110_Copyedit_Final_Approved.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'copyedits/SUB2026-00110_Copyedit_Final_Approved.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 2537553,
                    'checksum_sha256' => hash('sha256', 'output_110'),
                    'created_by' => $copyEditor->id,
                ]
            );

            CopyeditOutput::firstOrCreate(
                ['copyedit_id' => $copyedit110->id, 'file_id' => $outFile110->id],
                ['position' => 1]
            );

            $approval110 = CopyeditApproval::firstOrCreate(
                ['copyedit_id' => $copyedit110->id, 'output_version' => 1],
                [
                    'author_id' => $sub110->author_id ?? $copyEditor->id,
                    'decision' => 'approved',
                    'approved_at' => now()->subDays(4),
                    'note' => 'Disetujui untuk diteruskan ke tata letak.',
                ]
            );

            CopyeditApprovedFile::firstOrCreate(
                ['approval_id' => $approval110->id, 'file_id' => $outFile110->id],
                ['checksum_sha256' => $outFile110->checksum_sha256]
            );

            LayoutAssignment::firstOrCreate(
                ['submission_id' => $sub110->id],
                [
                    'layout_editor_id' => $layoutEditor->id,
                    'assigned_by' => $copyEditor->id,
                    'assigned_at' => now()->subDays(3),
                    'due_at' => now()->addDays(7),
                    'reason' => 'Tahap copyediting selesai, siap untuk drafting layout.',
                    'version' => 1,
                ]
            );
        }

        // Skenario 6: SUB-2026-00111 — Penulis Mengajukan Catatan Revisi (Revisions Requested / Siap Mediasi)
        if ($sub111 = $submissions->get('SUB-2026-00111')) {
            $srcFile111 = FileEntry::updateOrCreate(
                ['original_name' => 'SUB2026-00111_Source_Accepted.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'submissions/SUB2026-00111_Source_Accepted.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 2450000,
                    'checksum_sha256' => hash('sha256', 'source_111'),
                    'created_by' => $sub111->author_id,
                ]
            );

            $copyedit111 = Copyedit::firstOrCreate(
                ['submission_id' => $sub111->id],
                [
                    'copy_editor_id' => $copyEditor->id,
                    'source_file_id' => $srcFile111->id,
                    'status' => 'in_progress',
                    'due_at' => now()->addDays(4),
                    'notes' => 'Telah dilakukan perapian sitasi artikel teknik kepulauan.',
                ]
            );

            $outFile111 = FileEntry::updateOrCreate(
                ['original_name' => 'SUB2026-00111_Copyedit_Draft1.docx'],
                [
                    'storage_disk' => 'local',
                    'storage_path' => 'copyedits/SUB2026-00111_Copyedit_Draft1.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => 2380000,
                    'checksum_sha256' => hash('sha256', 'output_111'),
                    'created_by' => $copyEditor->id,
                ]
            );

            CopyeditOutput::firstOrCreate(
                ['copyedit_id' => $copyedit111->id, 'file_id' => $outFile111->id],
                ['position' => 1]
            );

            CopyeditApproval::firstOrCreate(
                ['copyedit_id' => $copyedit111->id, 'output_version' => 1],
                [
                    'author_id' => $sub111->author_id ?? $copyEditor->id,
                    'decision' => 'revisions_requested',
                    'approved_at' => now()->subDay(),
                    'reason' => 'Penulis meminta terminologi "Hub-and-Spoke Maritim" pada Gambar 4 dan Bab 3 tetap dipertahankan sesuai standar internasional IMO tanpa diterjemahkan secara harfiah.',
                    'note' => 'Penulis meminta terminologi "Hub-and-Spoke Maritim" pada Gambar 4 dan Bab 3 tetap dipertahankan sesuai standar internasional IMO tanpa diterjemahkan secara harfiah.',
                ]
            );
        }

        // Daftarkan berkas pendukung dan berkas fallback demo jika belum ada
        $additionalDemoFiles = [
            [
                'original_name' => 'SUB2026-00107_Source_Digital_Service.docx',
                'storage_path' => 'submissions/SUB2026-00107_Source_Digital_Service.docx',
                'byte_size' => 2044723,
            ],
            [
                'original_name' => 'SUB2026-00107_Copyedit_Draft1.docx',
                'storage_path' => 'copyedits/SUB2026-00107_Copyedit_Draft1.docx',
                'byte_size' => 1992294,
            ],
            [
                'original_name' => 'SUB2026-00108_Accepted_Geoteknik.docx',
                'storage_path' => 'submissions/SUB2026-00108_Accepted_Geoteknik.docx',
                'byte_size' => 3565158,
            ],
            [
                'original_name' => 'SUB2026-00108_Copyedit_Geoteknik_v1.docx',
                'storage_path' => 'copyedits/SUB2026-00108_Copyedit_Geoteknik_v1.docx',
                'byte_size' => 3407872,
            ],
            [
                'original_name' => 'SUB2026-00109_Source_Ekonomi.docx',
                'storage_path' => 'submissions/SUB2026-00109_Source_Ekonomi.docx',
                'byte_size' => 1887436,
            ],
            [
                'original_name' => 'SUB2026-00109_Copyedit_Final.docx',
                'storage_path' => 'copyedits/SUB2026-00109_Copyedit_Final.docx',
                'byte_size' => 1835008,
            ],
            [
                'original_name' => 'SUB2026-00110_Accepted_Sampah_Tallo.docx',
                'storage_path' => 'submissions/SUB2026-00110_Accepted_Sampah_Tallo.docx',
                'byte_size' => 2621440,
            ],
            [
                'original_name' => 'SUB2026-00110_Copyedit_Final_Approved.docx',
                'storage_path' => 'copyedits/SUB2026-00110_Copyedit_Final_Approved.docx',
                'byte_size' => 2537553,
            ],
            [
                'original_name' => 'SUB2026-00111_Source_Accepted.docx',
                'storage_path' => 'submissions/SUB2026-00111_Source_Accepted.docx',
                'byte_size' => 2450000,
            ],
            [
                'original_name' => 'SUB2026-00111_Copyedit_Draft1.docx',
                'storage_path' => 'copyedits/SUB2026-00111_Copyedit_Draft1.docx',
                'byte_size' => 2380000,
            ],
        ];

        foreach ($additionalDemoFiles as $demoFile) {
            FileEntry::updateOrCreate(
                ['original_name' => $demoFile['original_name']],
                [
                    'storage_disk' => 'local',
                    'storage_path' => $demoFile['storage_path'],
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'byte_size' => $demoFile['byte_size'],
                    'checksum_sha256' => hash('sha256', $demoFile['original_name']),
                    'created_by' => $copyEditor->id,
                ]
            );
        }

        // Pastikan seluruh berkas fisik untuk demo naskah tersedia di storage lokal
        $seededFiles = FileEntry::where('storage_path', 'like', 'submissions/%')
            ->orWhere('storage_path', 'like', 'copyedits/%')
            ->get();

        foreach ($seededFiles as $file) {
            $this->ensureFileOnDisk($file);
        }
    }

    private function ensureFileOnDisk(FileEntry $file): void
    {
        $disk = Storage::disk($file->storage_disk ?: 'local');
        $targetBytes = (int) ($file->byte_size ?: 1024);

        if (! $disk->exists($file->storage_path) || (int) $disk->size($file->storage_path) !== $targetBytes) {
            $content = $this->generateSampleDocx($file->original_name, $targetBytes);
            $disk->put($file->storage_path, $content);
        }

        $actualBytes = (int) $disk->size($file->storage_path);
        $actualSha256 = hash('sha256', (string) $disk->get($file->storage_path));

        if ($file->byte_size !== $actualBytes || $file->checksum_sha256 !== $actualSha256) {
            $file->byte_size = $actualBytes;
            $file->checksum_sha256 = $actualSha256;
            $file->save();

            CopyeditApprovedFile::where('file_id', $file->id)
                ->update(['checksum_sha256' => $actualSha256]);
        }
    }

    private function generateSampleDocx(string $filename, int $targetBytes = 0): string
    {
        if (class_exists(\ZipArchive::class)) {
            $temp = tempnam(sys_get_temp_dir(), 'docx_seeder');
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
                    .'<w:p><w:r><w:t>Dokumen Naskah: '.htmlspecialchars($filename, ENT_XML1).'</w:t></w:r></w:p>'
                    .'<w:p><w:r><w:t>Naskah ini telah melalui proses penyuntingan bahasa dan format selingkung jurnal sesuai kaidah EYD V dan APA Style 7th Edition.</w:t></w:r></w:p>'
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
            $header = "Dokumen Naskah Jurnal BRIDA Makassar: {$filename}\n";
            $padding = max(0, $targetBytes - strlen($header));

            return $header.str_repeat(' ', $padding);
        }

        return "Dokumen Naskah Jurnal BRIDA Makassar: {$filename}\n";
    }
}
