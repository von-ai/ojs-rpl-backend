<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Models\FileEntry;
use App\Models\Revision;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Services\PortalAccess;
use App\Services\PrivateFileService;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
        return DB::transaction(function () use ($submission, $actor, $manuscriptFile, $responseLetterFile, $note) {
            $submission = PortalAccess::lockSubmission($submission, $actor, 'author');

            // Validasi hak akses kepemilikan naskah
            if ($submission->author_id && $submission->author_id !== $actor->id) {
                $isContributor = $submission->contributors()->where('user_id', $actor->id)->exists();
                if (! $isContributor) {
                    throw new DomainException('Anda tidak memiliki hak akses untuk mengajukan revisi pada naskah ini.');
                }
            }

            // Validasi status naskah
            $allowedStatuses = ['revisions_requested'];
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
                $storageDisk = config('filesecurity.disk', 'local');
                $disk = Storage::disk($storageDisk);

                // 1. Simpan berkas revisi manuskrip
                $manuscriptPath = $manuscriptFile->store("submissions/{$submission->id}/revisions", $storageDisk);
                $manuscriptSha256 = hash('sha256', (string) $disk->get($manuscriptPath));

                $manuscriptEntry = FileEntry::create([
                    'storage_disk' => $storageDisk,
                    'storage_path' => $manuscriptPath,
                    'original_name' => $manuscriptFile->getClientOriginalName(),
                    'mime_type' => $manuscriptFile->getMimeType() ?: 'application/pdf',
                    'byte_size' => (int) $disk->size($manuscriptPath),
                    'checksum_sha256' => $manuscriptSha256,
                    'virus_scan_status' => 'pending',
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
                        'mime_type' => $responseLetterFile->getMimeType() ?: 'application/pdf',
                        'byte_size' => (int) $disk->size($responseLetterPath),
                        'checksum_sha256' => $responseLetterSha256,
                        'virus_scan_status' => 'pending',
                        'created_by' => $actor->id,
                    ]);
                }

                // 3. Tentukan nomor putaran revisi berikutnya
                $currentRound = (int) ($submission->revisions()->max('round') ?? 0);
                $nextRound = $currentRound + 1;

                // 4. Catat record revisi pada tabel revisions
                $latestDecision = $submission->editorialDecisions()
                    ->whereIn('decision', ['revisions_required', 'revise', 'resubmit'])
                    ->latest('created_at')
                    ->first();

                $revision = Revision::create([
                    'submission_id' => $submission->id,
                    'decision_id' => $latestDecision?->id,
                    'round' => $nextRound,
                    'status' => 'submitted',
                    'manuscript_file_id' => $manuscriptEntry->id,
                    'response_letter_file_id' => $responseLetterEntry?->id,
                    'due_at' => $latestDecision?->revision_due_at,
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

                // 6. Tentukan status naskah: jika revisi berasal dari permintaan perbaikan format pada skrining awal (desk review), kembalikan ke submitted untuk diverifikasi redaksi. Jika pasca telaah sejawat, kembalikan ke in_review.
                $hasPeerReviewRound = $submission->reviewRounds()->exists();
                $hasEditorialDecision = $latestDecision !== null;
                $hasDeskRevisionRequired = $submission->deskReviews()
                    ->where('status', 'revision_required')
                    ->exists();

                $isDeskFormatRevision = $hasDeskRevisionRequired && ! $hasPeerReviewRound && ! $hasEditorialDecision;

                $submission->status = $isDeskFormatRevision ? 'submitted' : 'in_review';
                $submission->version = (int) $submission->version + 1;
                $submission->save();

                return $revision;
            });
        });
    }

    /**
     * Memverifikasi hak akses dan menyajikan unduhan berkas naskah privat (UC-06).
     */
    public function downloadFile(Submission $submission, string $fileId, User $actor): StreamedResponse|BinaryFileResponse
    {
        abort_unless($submission->author_id === $actor->id || $submission->contributors()->where('user_id', $actor->id)->exists(), 404);
        $link = $submission->files()->where('file_id', $fileId)->firstOrFail();

        return app(PrivateFileService::class)->stream($link->file);
    }
}
