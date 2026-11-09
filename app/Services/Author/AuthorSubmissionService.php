<?php

declare(strict_types=1);

namespace App\Services\Author;

use App\Models\FileEntry;
use App\Models\ReviewAnnotation;
use App\Models\Revision;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionContributor;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Services\PortalAccess;
use App\Services\PrivateFileService;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuthorSubmissionService
{
    public function __construct(private readonly DirectManuscriptUpload $directManuscriptUpload) {}

    /**
     * Batas ukuran berkas maksimum 20 MiB (20.971.520 byte) sesuai aturan domain contract-decisions.md.
     */
    public const MAX_FILE_BYTES = 20971520;

    public function saveSubmission(SubmissionIntakeInput $input, User $author): Submission
    {
        $data = $input->data;
        $isDraft = ($data['action'] ?? 'submit') === 'draft';

        $directFile = null;
        try {
            return DB::transaction(function () use ($input, $author, $data, $isDraft, &$directFile): Submission {
            $authorId = $author->id;
            $draftId = $data['draft_id'] ?? null;
            $existingDraft = null;
            if ($draftId !== null) {
                $existingDraft = Submission::whereKey($draftId)
                    ->where('author_id', $authorId)
                    ->where('status', 'draft')
                    ->lockForUpdate()
                    ->first();

                if (! $existingDraft) {
                    throw ValidationException::withMessages(['draft_id' => 'Draf tidak tersedia untuk diedit.']);
                }
            }

            if (! $isDraft && ! $input->manuscriptFile && ! $input->directBlobPath
                && (! $existingDraft || $input->removeManuscript || ! $existingDraft->files()->where('genre', 'manuscript')->exists())) {
                throw ValidationException::withMessages(['manuscript_file' => 'Berkas manuskrip wajib diunggah.']);
            }

            $sectionId = $data['section_id'] ?? $existingDraft?->section_id;
            $sectionQuery = Section::with('journal')
                ->where('accepting_submissions', true)
                ->whereHas('journal', fn ($query) => $query->where('status', 'active'));
            if ($sectionId !== null) {
                $sectionQuery->where(fn ($query) => $query->where('id', $sectionId)->orWhere('slug', $sectionId));
                $matchingSections = $sectionQuery->limit(2)->get();
                $section = $matchingSections->count() === 1 ? $matchingSections->first() : null;
            } else {
                $section = $sectionQuery->orderBy('position')->orderBy('id')->first();
            }

            if (! $section) {
                throw ValidationException::withMessages(['section_id' => 'Seksi tidak tersedia atau sedang ditutup untuk pengajuan.']);
            }
            $journal = $section->journal;

            $checklistItems = $input->checklistItems;
            $hasAccepted = ! empty($data['author_guidelines_accepted']) || count($checklistItems) >= 5;
            $guidelineData = $hasAccepted ? [
                'accepted' => true,
                'checklist' => $checklistItems,
                'timestamp' => now()->toIso8601String(),
            ] : (! empty($checklistItems) ? [
                'accepted' => false,
                'checklist' => $checklistItems,
                'timestamp' => now()->toIso8601String(),
            ] : null);

            // Draf menerima metadata parsial tanpa menghalangi penyimpanan.
            $titleInput = trim((string) ($data['title'] ?? ''));
            $title = $titleInput !== '' ? $titleInput : ($isDraft ? 'Draf Naskah Belum Diberi Judul' : 'Naskah Baru');
            $abstractInput = trim((string) ($data['abstract'] ?? ''));
            $abstract = $abstractInput !== '' ? $abstractInput : ($isDraft ? 'Draf naskah belum dilengkapi abstrak.' : null);

            if ($existingDraft) {
                $existingDraft->update([
                    'journal_id' => $journal->id,
                    'section_id' => $section->id,
                    'title' => $title,
                    'abstract' => $abstract,
                    'keywords' => $data['keywords'] ?? ($existingDraft->keywords ?? []),
                    'references' => $data['references'] ?? $existingDraft->references,
                    'subject_area' => $data['subject_area'] ?? $existingDraft->subject_area,
                    'comments_for_editor' => $data['comments_for_editor'] ?? $existingDraft->comments_for_editor,
                    'status' => $isDraft ? 'draft' : 'submitted',
                    'guideline_acceptance' => $guidelineData ?? $existingDraft->guideline_acceptance,
                    'submitted_at' => $isDraft ? null : now(),
                ]);
                $submission = $existingDraft;
            } else {
                // Nomor pengajuan unik berformat SUB-YYYY-XXXXX dengan sequence atomic.
                $year = (int) now()->format('Y');
                DB::table('submission_sequences')->insertOrIgnore(['year' => $year, 'last_number' => 0]);
                $sequence = DB::table('submission_sequences')->where('year', $year)->lockForUpdate()->first();
                $number = (int) $sequence->last_number + 1;
                DB::table('submission_sequences')->where('year', $year)->update(['last_number' => $number]);
                $submissionNumber = 'SUB-'.$year.'-'.str_pad((string) $number, 5, '0', STR_PAD_LEFT);

                $submission = Submission::create([
                    'journal_id' => $journal->id,
                    'section_id' => $section->id,
                    'author_id' => $authorId,
                    'submission_number' => $submissionNumber,
                    'title' => $title,
                    'abstract' => $abstract,
                    'keywords' => $data['keywords'] ?? [],
                    'references' => $data['references'] ?? null,
                    'subject_area' => $data['subject_area'] ?? null,
                    'comments_for_editor' => $data['comments_for_editor'] ?? null,
                    'status' => $isDraft ? 'draft' : 'submitted',
                    'guideline_acceptance' => $guidelineData,
                    'submitted_at' => $isDraft ? null : now(),
                    'version' => 1,
                ]);
            }

            $contributors = $data['contributors'] ?? [];
            if (! empty($contributors)) {
                if ($existingDraft) {
                    SubmissionContributor::where('submission_id', $submission->id)->delete();
                }

                foreach ($contributors as $index => $contributor) {
                    $isCorresponding = filter_var($contributor['corresponding'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $givenName = $contributor['given_name'] ?? ($contributor['name'] ?? 'Penulis');

                    SubmissionContributor::create([
                        'submission_id' => $submission->id,
                        'user_id' => ($isCorresponding && $authorId) ? $authorId : null,
                        'given_name' => $givenName,
                        'family_name' => $contributor['family_name'] ?? null,
                        'email' => $contributor['email'] ?? ($author->email ?? 'author@makassar.go.id'),
                        'affiliation' => $contributor['affiliation'] ?? 'BRIDA Kota Makassar',
                        'orcid' => $contributor['orcid_id'] ?? null,
                        'is_corresponding' => $isCorresponding,
                        'position' => (int) ($contributor['sequence'] ?? ($index + 1)),
                    ]);
                }
            }

            if ($input->directBlobPath) {
                $directFile = $this->directManuscriptUpload->claim($author, $input->directBlobPath);
                if ($existingDraft) {
                    $submission->files()->where('genre', 'manuscript')->delete();
                }

                SubmissionFile::create([
                    'submission_id' => $submission->id,
                    'file_id' => $directFile->id,
                    'genre' => 'manuscript',
                    'version' => 1,
                ]);
            } elseif ($input->manuscriptFile && $input->manuscriptFile->isValid()) {
                $file = $input->manuscriptFile;
                $storageDisk = (string) config('filesecurity.disk', 'azure');
                $path = $file->store('manuscripts', $storageDisk);
                $sha256 = hash_file('sha256', $file->getRealPath());

                $fileEntry = FileEntry::create([
                    'storage_disk' => $storageDisk,
                    'storage_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'byte_size' => (int) Storage::disk($storageDisk)->size($path),
                    'checksum_sha256' => $sha256,
                    'virus_scan_status' => 'pending',
                    'created_by' => $authorId,
                ]);

                if ($existingDraft) {
                    // Hapus kaitan manuskrip sebelumnya agar draf hanya memiliki satu manuskrip utama.
                    SubmissionFile::where('submission_id', $submission->id)
                        ->where('genre', 'manuscript')
                        ->delete();
                }

                SubmissionFile::create([
                    'submission_id' => $submission->id,
                    'file_id' => $fileEntry->id,
                    'genre' => 'manuscript',
                    'version' => 1,
                ]);
            } elseif ($existingDraft && $input->removeManuscript) {
                SubmissionFile::where('submission_id', $submission->id)
                    ->where('genre', 'manuscript')
                    ->delete();
            }

            return $submission;
            });
        } catch (\Throwable $exception) {
            if ($directFile !== null) {
                $this->directManuscriptUpload->discard($directFile->storage_path);
            }
            throw $exception;
        }
    }

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
                $storageDisk = (string) config('filesecurity.disk', 'azure');
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

        $file = $submission->files()->where('file_id', $fileId)->firstOrFail()->file;
        // Reviewer documents can disclose identities in metadata, comments, and tracked changes.
        abort_if(ReviewAnnotation::where('file_id', $fileId)->exists(), 404);

        return app(PrivateFileService::class)->stream($file);
    }
}
