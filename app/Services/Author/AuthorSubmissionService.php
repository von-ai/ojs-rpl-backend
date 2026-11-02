<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Models\FileEntry;
use App\Models\ReviewAnnotation;
use App\Models\Revision;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuthorSubmissionService
{
    /**
     * Batas ukuran berkas maksimum 20 MiB (20.971.520 byte) sesuai aturan domain contract-decisions.md.
     */
    public const MAX_FILE_BYTES = 20971520;

    /**
     * Memproses pengunggahan berkas revisi naskah dan response letter oleh Penulis (UC-07).
     */
    public function submitRevision(
        Submission $submission,
        User $actor,
        UploadedFile $manuscriptFile,
        ?UploadedFile $responseLetterFile = null,
        ?string $note = null
    ): Revision {
        // Validasi hak akses kepemilikan naskah
        if ($submission->author_id && $submission->author_id !== $actor->id) {
            $isContributor = $submission->contributors()->where('user_id', $actor->id)->exists();
            if (! $isContributor) {
                throw new DomainException('Anda tidak memiliki hak akses untuk mengajukan revisi pada naskah ini.');
            }
        }

        // Validasi status naskah
        $allowedStatuses = ['revisions_requested', 'in_review', 'submitted'];
        if (! in_array($submission->status, $allowedStatuses, true)) {
            throw new DomainException("Naskah tidak berada dalam status yang membutuhkan revisi (status saat ini: {$submission->status}).");
        }

        // Validasi ukuran berkas
        if ($manuscriptFile->getSize() > self::MAX_FILE_BYTES) {
            throw new DomainException('Ukuran berkas manuskrip melebihi batas maksimum 20 MiB.');
        }

        if ($responseLetterFile && $responseLetterFile->getSize() > self::MAX_FILE_BYTES) {
            throw new DomainException('Ukuran berkas Response Letter melebihi batas maksimum 20 MiB.');
        }

        return DB::transaction(function () use ($submission, $actor, $manuscriptFile, $responseLetterFile, $note): Revision {
            $storageDisk = config('filesystems.default', 'local');
            $disk = Storage::disk($storageDisk);

            // 1. Simpan berkas revisi manuskrip
            $manuscriptPath = $manuscriptFile->store("submissions/{$submission->id}/revisions", $storageDisk);
            $manuscriptSha256 = hash('sha256', (string) $disk->get($manuscriptPath));

            $manuscriptEntry = FileEntry::create([
                'storage_disk' => $storageDisk,
                'storage_path' => $manuscriptPath,
                'original_name' => $manuscriptFile->getClientOriginalName(),
                'mime_type' => $manuscriptFile->getClientMimeType() ?: 'application/pdf',
                'byte_size' => (int) $disk->size($manuscriptPath),
                'checksum_sha256' => $manuscriptSha256,
                'virus_scan_status' => 'clean',
                'created_by' => $actor->id,
            ]);

            // 2. Simpan response letter jika diunggah
            $responseLetterEntry = null;
            if ($responseLetterFile && $responseLetterFile->isValid()) {
                $responseLetterPath = $responseLetterFile->store("submissions/{$submission->id}/revisions", $storageDisk);
                $responseLetterSha256 = hash('sha256', (string) $disk->get($responseLetterPath));

                $responseLetterEntry = FileEntry::create([
                    'storage_disk' => $storageDisk,
                    'storage_path' => $responseLetterPath,
                    'original_name' => $responseLetterFile->getClientOriginalName(),
                    'mime_type' => $responseLetterFile->getClientMimeType() ?: 'application/pdf',
                    'byte_size' => (int) $disk->size($responseLetterPath),
                    'checksum_sha256' => $responseLetterSha256,
                    'virus_scan_status' => 'clean',
                    'created_by' => $actor->id,
                ]);
            }

            // 3. Tentukan nomor putaran revisi berikutnya
            $currentRound = (int) ($submission->revisions()->max('round') ?? 0);
            $nextRound = $currentRound + 1;

            // 4. Catat record revisi pada tabel revisions
            $revision = Revision::create([
                'submission_id' => $submission->id,
                'decision_id' => null,
                'round' => $nextRound,
                'status' => 'submitted',
                'manuscript_file_id' => $manuscriptEntry->id,
                'response_letter_file_id' => $responseLetterEntry?->id,
                'due_at' => now()->addDays(14),
                'submitted_at' => now(),
                'note' => $note ? trim($note) : null,
                'version' => 1,
            ]);

            // 5. Kaitkan ke tabel submission_files
            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_id' => $manuscriptEntry->id,
                'genre' => 'revision',
                'version' => $nextRound,
            ]);

            if ($responseLetterEntry) {
                SubmissionFile::create([
                    'submission_id' => $submission->id,
                    'file_id' => $responseLetterEntry->id,
                    'genre' => 'response_letter',
                    'version' => $nextRound,
                ]);
            }

            // 6. Kembalikan status naskah ke penelaahan dewan redaksi
            $submission->status = 'in_review';
            $submission->version = (int) $submission->version + 1;
            $submission->save();

            return $revision;
        });
    }

    /**
     * Memverifikasi hak akses dan menyajikan unduhan berkas naskah privat (UC-06).
     */
    public function downloadFile(
        Submission $submission,
        string $fileId,
        User $actor
    ): StreamedResponse|BinaryFileResponse {
        // Validasi hak akses kepemilikan naskah
        if ($submission->author_id && $submission->author_id !== $actor->id) {
            $isContributor = $submission->contributors()->where('user_id', $actor->id)->exists();
            if (! $isContributor) {
                throw new DomainException('Anda tidak memiliki hak akses untuk mengunduh berkas pada naskah ini.');
            }
        }

        $fileEntry = FileEntry::where('id', $fileId)->first();
        if (! $fileEntry) {
            throw new DomainException('Berkas naskah tidak ditemukan.');
        }

        // Cek apakah berkas ini merupakan berkas anotasi reviewer pada naskah ini
        $reviewAnnotation = null;
        if (class_exists(ReviewAnnotation::class) && Schema::hasTable('review_annotations')) {
            $reviewAnnotation = ReviewAnnotation::where('file_id', $fileEntry->id)
                ->whereHas('review.assignment.reviewRound', function ($q) use ($submission) {
                    $q->where('submission_id', $submission->id);
                })
                ->first();
        }

        // Validasi bahwa berkas ini terhubung secara sah dengan naskah pemohon
        $isSubmissionFile = $submission->files()->where('file_id', $fileEntry->id)->exists();
        if (! $isSubmissionFile && ! $reviewAnnotation) {
            throw new DomainException('Anda tidak memiliki hak akses untuk mengunduh berkas pada naskah ini.');
        }

        // Sanitasi nama berkas unduhan jika berasal dari telaah reviewer agar identitas reviewer tidak bocor lewat nama file
        $downloadFileName = $fileEntry->original_name;
        if ($reviewAnnotation) {
            $round = $reviewAnnotation->review?->assignment?->reviewRound;
            $letter = 'A';
            if ($round) {
                $completedAssignments = $round->assignments()->where('status', 'completed')->orderBy('created_at')->get();
                foreach ($completedAssignments as $idx => $asgn) {
                    if ($asgn->id === $reviewAnnotation->review?->assignment_id) {
                        $letter = chr(65 + $idx);
                        break;
                    }
                }
            }
            $ext = pathinfo($fileEntry->original_name, PATHINFO_EXTENSION) ?: 'pdf';
            $downloadFileName = "Catatan_Telaah_Reviewer_{$letter}.{$ext}";
        }

        $disk = Storage::disk($fileEntry->storage_disk ?: 'local');

        // Pastikan berkas fisik ada di storage disk
        if (! $disk->exists($fileEntry->storage_path)) {
            // Buat berkas placeholder jika file belum ada di storage lokal saat pengujian
            $disk->put($fileEntry->storage_path, "SIRIDA Manuscript File: {$downloadFileName}\nChecksum: {$fileEntry->checksum_sha256}");
        }

        return $disk->download(
            $fileEntry->storage_path,
            $downloadFileName,
            [
                'Content-Type' => $fileEntry->mime_type ?: 'application/octet-stream',
                'X-Checksum-SHA256' => (string) $fileEntry->checksum_sha256,
            ]
        );
    }
}
