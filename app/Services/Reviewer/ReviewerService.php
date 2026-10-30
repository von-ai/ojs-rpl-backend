<?php

declare(strict_types=1);

namespace App\Services\Reviewer;

use App\Models\FileEntry;
use App\Models\Review;
use App\Models\ReviewAnnotation;
use App\Models\ReviewAssignment;
use App\Models\ReviewExtension;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReviewerService
{
    /**
     * Mendapatkan daftar penugasan telaah untuk reviewer yang login.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getAssignmentsForReviewer(User $reviewer, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = ReviewAssignment::query()
            ->where('reviewer_id', $reviewer->id)
            ->with([
                'reviewRound.submission.section',
                'review',
                'extensions',
                'pendingExtension',
            ])
            ->latest('created_at');

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $status = $filters['status'];
            if ($status === 'active') {
                $query->whereIn('status', ['invited', 'accepted', 'overdue']);
            } elseif ($status === 'completed') {
                $query->where('status', 'completed');
            } else {
                $query->where('status', $status);
            }
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->whereHas('reviewRound.submission', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('submission_number', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Menanggapi surat undangan penugasan reviewer (UC-08: Accept / Decline).
     */
    public function respondToInvitation(ReviewAssignment $assignment, string $response, ?string $reason, User $reviewer): ReviewAssignment
    {
        // 1. Guard otorisasi
        if ($assignment->reviewer_id !== $reviewer->id) {
            throw new InvalidArgumentException('Anda tidak memiliki hak akses atas undangan telaah ini.');
        }

        // 2. Guard status
        if ($assignment->status !== 'invited') {
            throw new InvalidArgumentException('Undangan ini sudah pernah ditanggapi sebelumnya.');
        }

        // 3. Guard Mesin Waktu (Normatif UC-08): Jika melewati response_due_at, undangan kadaluwarsa (no_response)
        $now = Carbon::now('UTC');
        if ($assignment->response_due_at && $now->greaterThanOrEqualTo($assignment->response_due_at)) {
            $assignment->update([
                'status' => 'no_response',
                'version' => $assignment->version + 1,
            ]);

            throw new InvalidArgumentException('Batas waktu konfirmasi undangan telaah telah berakhir.');
        }

        if (! in_array($response, ['accepted', 'declined'], true)) {
            throw new InvalidArgumentException('Tanggapan undangan tidak valid (hanya accepted atau declined).');
        }

        return DB::transaction(function () use ($assignment, $response): ReviewAssignment {
            $assignment->update([
                'status' => $response,
                'responded_at' => Carbon::now('UTC'),
                'version' => $assignment->version + 1,
            ]);

            return $assignment;
        });
    }

    /**
     * Mengajukan permohonan perpanjangan waktu telaah (UC-08: Extension Request).
     */
    public function requestExtension(ReviewAssignment $assignment, Carbon $requestedDueAt, string $reason, User $reviewer): ReviewExtension
    {
        if ($assignment->reviewer_id !== $reviewer->id) {
            throw new InvalidArgumentException('Anda tidak berhak mengajukan perpanjangan untuk penugasan ini.');
        }

        if (! in_array($assignment->status, ['accepted', 'overdue'], true)) {
            throw new InvalidArgumentException('Permohonan perpanjangan hanya dapat diajukan untuk penugasan yang sedang berjalan.');
        }

        $now = Carbon::now('UTC');
        if ($requestedDueAt->lessThanOrEqualTo($now) || $requestedDueAt->lessThanOrEqualTo($assignment->review_due_at)) {
            throw new InvalidArgumentException('Tanggal perpanjangan harus lebih lama dari batas waktu telaah saat ini.');
        }

        // Cek jika sudah ada permohonan pending
        if ($assignment->pendingExtension()->exists()) {
            throw new InvalidArgumentException('Anda masih memiliki permohonan perpanjangan waktu yang menunggu persetujuan editor.');
        }

        return ReviewExtension::create([
            'assignment_id' => $assignment->id,
            'requested_due_at' => $requestedDueAt->utc(),
            'reason' => $reason,
            'status' => 'pending',
            'version' => 1,
        ]);
    }

    /**
     * Menyimpan draf evaluasi rubrik sebelum submit (UC-09: Save Draft).
     *
     * @param  array{scores?: array, comments_for_author?: string, confidential_comments_for_editor?: ?string, recommendation?: string}  $data
     */
    public function saveDraft(ReviewAssignment $assignment, array $data, User $reviewer): Review
    {
        if ($assignment->reviewer_id !== $reviewer->id) {
            throw new InvalidArgumentException('Anda tidak berhak mengakses ruang telaah ini.');
        }

        if ($assignment->status === 'completed') {
            throw new InvalidArgumentException('Hasil telaah yang sudah di-submit bersifat tetap (immutable) dan tidak dapat diubah.');
        }

        return Review::updateOrCreate(
            ['assignment_id' => $assignment->id],
            [
                'scores' => $data['scores'] ?? [],
                'comments_for_author' => $data['comments_for_author'] ?? '',
                'confidential_comments_for_editor' => $data['confidential_comments_for_editor'] ?? null,
                'recommendation' => $data['recommendation'] ?? 'revisions_required',
                'certify_no_conflict' => true,
                'status' => 'draft',
                'version' => 1,
            ]
        );
    }

    /**
     * Mengirimkan hasil evaluasi telaah sejawat secara resmi (UC-09: Submit Review).
     *
     * @param  array{scores: array, comments_for_author: string, confidential_comments_for_editor?: ?string, recommendation: string, certify_no_conflict: bool}  $data
     */
    public function submitReview(ReviewAssignment $assignment, array $data, ?UploadedFile $annotatedFile, User $reviewer): Review
    {
        if ($assignment->reviewer_id !== $reviewer->id) {
            throw new InvalidArgumentException('Anda tidak berhak mengakses ruang telaah ini.');
        }

        if ($assignment->status === 'completed') {
            throw new InvalidArgumentException('Hasil telaah ini sudah pernah dikirimkan sebelumnya dan bersifat immutable.');
        }

        // Guard Mesin Waktu: Bila status efektif overdue dan belum ada perpanjangan yang disetujui
        if ($assignment->effective_status === 'overdue' && $assignment->status !== 'accepted') {
            throw new InvalidArgumentException('Batas waktu telaah telah berakhir. Silakan ajukan permohonan perpanjangan waktu ke editor.');
        }

        // Guard Pernyataan Bebas Konflik Kepentingan (Wajib True sesuai ERD Tabel 29)
        if (empty($data['certify_no_conflict'])) {
            throw new InvalidArgumentException('Anda wajib mencentang pernyataan integritas bebas dari konflik kepentingan.');
        }

        // Guard Rekomendasi
        $allowed = ['accept', 'revisions_required', 'resubmit', 'decline'];
        if (! in_array($data['recommendation'], $allowed, true)) {
            throw new InvalidArgumentException('Rekomendasi telaah tidak valid.');
        }

        if (empty(trim($data['comments_for_author']))) {
            throw new InvalidArgumentException('Masukan konstruktif untuk penulis wajib diisi.');
        }

        return DB::transaction(function () use ($assignment, $data, $annotatedFile, $reviewer): Review {
            $review = Review::updateOrCreate(
                ['assignment_id' => $assignment->id],
                [
                    'scores' => $data['scores'] ?? [],
                    'comments_for_author' => $data['comments_for_author'],
                    'confidential_comments_for_editor' => $data['confidential_comments_for_editor'] ?? null,
                    'recommendation' => $data['recommendation'],
                    'certify_no_conflict' => true,
                    'status' => 'submitted',
                    'submitted_at' => Carbon::now('UTC'),
                    'version' => 1,
                ]
            );

            // Jika ada berkas naskah beranotasi yang diunggah
            if ($annotatedFile && $annotatedFile->isValid()) {
                $storedPath = $annotatedFile->store('review-annotations', 'local');

                $fileEntry = FileEntry::create([
                    'storage_disk' => 'local',
                    'storage_path' => $storedPath,
                    'original_name' => $annotatedFile->getClientOriginalName(),
                    'mime_type' => $annotatedFile->getMimeType() ?? 'application/pdf',
                    'byte_size' => $annotatedFile->getSize(),
                    'checksum_sha256' => hash_file('sha256', $annotatedFile->getRealPath()),
                    'created_by' => $reviewer->id,
                ]);

                ReviewAnnotation::create([
                    'review_id' => $review->id,
                    'file_id' => $fileEntry->id,
                ]);
            }

            // Selesaikan penugasan reviewer
            $assignment->update([
                'status' => 'completed',
                'completed_at' => Carbon::now('UTC'),
                'version' => $assignment->version + 1,
            ]);

            return $review;
        });
    }

    /**
     * Kriteria rubrik standar jika belum ada versi rubrik kustom yang dikonfigurasi.
     *
     * @return array<string, array{label: string, weight: int, description: string}>
     */
    public function getDefaultRubricCriteria(): array
    {
        return [
            'originality' => [
                'label' => 'Orisinalitas & Kebaruan Gagasan Ilmiah',
                'weight' => 25,
                'description' => 'Tingkat kebaruan kontribusi ilmiah serta kejelasan posisi riset dibanding literatur yang ada.',
            ],
            'methodology' => [
                'label' => 'Rigoritas & Ketepatan Metodologi',
                'weight' => 25,
                'description' => 'Ketepatan desain penelitian, teknik pengumpulan data, sampling, dan validitas analisis empiris.',
            ],
            'presentation' => [
                'label' => 'Kejelasan Penyajian & Struktur Naskah',
                'weight' => 25,
                'description' => 'Konsistensi alur argumen, kualitas grafik/tabel, dan kepatuhan terhadap gaya selingkung SIRIDA.',
            ],
            'policy_relevance' => [
                'label' => 'Relevansi & Dampak Terhadap Pembangunan Daerah',
                'weight' => 25,
                'description' => 'Penerapan praktis hasil riset dalam menyelesaikan persoalan perkotaan dan inovasi daerah.',
            ],
        ];
    }
}
