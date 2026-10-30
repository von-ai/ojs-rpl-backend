<?php

declare(strict_types=1);

namespace App\Services\Proofreader;

use App\Mail\Proofreader\CorrectionNotesAddedMail;
use App\Mail\Proofreader\ProofreaderEscalationSubmittedMail;
use App\Mail\Proofreader\ProofreadingNoteVerifiedMail;
use App\Mail\Proofreader\ProofreadingSignoffCompletedMail;
use App\Models\Galley;
use App\Models\LayoutAssignment;
use App\Models\ProductionEscalation;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProofreaderService
{
    /**
     * Menyelesaikan model Submission berdasarkan ID atau nomor naskah.
     */
    public function resolveSubmission(Submission|string $submission): Submission
    {
        if ($submission instanceof Submission) {
            return $submission;
        }

        return Submission::where('id', $submission)
            ->orWhere('submission_number', $submission)
            ->firstOrFail();
    }

    /**
     * Mengambil galai aktif dari sebuah naskah.
     */
    public function resolveActiveGalley(Submission $submission): Galley
    {
        $galley = Galley::where('submission_id', $submission->id)
            ->where('status', 'active')
            ->latest('version')
            ->first();

        if (! $galley) {
            throw new DomainException("Tidak ditemukan berkas galai aktif untuk naskah {$submission->submission_number}.");
        }

        return $galley;
    }

    /**
     * Menambahkan catatan koreksi (proofreading note) pada galai aktif.
     *
     * @param  array{category: string, page?: int|null, severity?: string, description: string}  $data
     */
    public function addCorrectionNote(
        Submission|string $submission,
        array $data,
        User $actor
    ): ProofreadingNote {
        $sub = $this->resolveSubmission($submission);

        if (empty(trim($data['description'] ?? ''))) {
            throw new DomainException('Deskripsi catatan koreksi tidak boleh kosong.');
        }

        return DB::transaction(function () use ($sub, $data, $actor): ProofreadingNote {
            $galley = $this->resolveActiveGalley($sub);

            // Cari atau buat penugasan proofreading untuk galai ini
            $assignment = ProofAssignment::where('galley_id', $galley->id)
                ->where('proofreader_id', $actor->id)
                ->latest()
                ->first();

            if (! $assignment) {
                $assignment = ProofAssignment::where('galley_id', $galley->id)->latest()->first();
            }

            if (! $assignment) {
                $assignment = ProofAssignment::create([
                    'galley_id' => $galley->id,
                    'proofreader_id' => $actor->id,
                    'file_id' => $galley->file_id,
                    'due_at' => now()->addDays(5),
                    'status' => 'in_progress',
                    'assigned_at' => now(),
                    'checksum_sha256' => (string) ($galley->checksum_sha256 ? substr((string) $galley->checksum_sha256, 0, 64) : hash('sha256', $galley->id)),
                ]);
            } elseif ($assignment->status === 'assigned') {
                $assignment->status = 'in_progress';
                $assignment->save();
            }

            $note = ProofreadingNote::create([
                'proof_assignment_id' => $assignment->id,
                'galley_id' => $galley->id,
                'file_id' => $galley->file_id,
                'checksum_sha256' => (string) $galley->checksum_sha256,
                'category' => $data['category'] ?? 'typo',
                'page' => ! empty($data['page']) ? (int) $data['page'] : null,
                'severity' => $data['severity'] ?? 'minor',
                'description' => trim($data['description']),
                'resolved' => false,
                'resolution' => null,
                'resolved_at' => null,
                'verified' => false,
                'verified_at' => null,
                'verified_by' => null,
                'version' => 1,
            ]);

            // Kirim notifikasi email ke Layout Editor yang bertanggung jawab
            $this->notifyCorrectionNotesAdded($sub, $note, $actor);

            return $note;
        });
    }

    /**
     * Memverifikasi catatan koreksi yang telah diselesaikan oleh Layout Editor.
     * Guard invariant: catatan harus berstatus resolved sebelum dapat diverifikasi.
     */
    public function verifyCorrectionNote(
        ProofreadingNote|string $note,
        User $actor
    ): ProofreadingNote {
        $noteModel = $note instanceof ProofreadingNote
            ? $note
            : ProofreadingNote::findOrFail($note);

        if (! $noteModel->resolved) {
            throw new DomainException('Catatan koreksi belum diselesaikan oleh Layout Editor dan tidak dapat diverifikasi.');
        }

        return DB::transaction(function () use ($noteModel, $actor): ProofreadingNote {
            $noteModel->verified = true;
            $noteModel->verified_at = now();
            $noteModel->verified_by = $actor->id;
            $noteModel->save();

            $sub = $noteModel->galley?->submission;
            if ($sub) {
                $this->notifyNoteVerified($sub, $noteModel, $actor);
            }

            return $noteModel;
        });
    }

    /**
     * Memberikan persetujuan akhir proofreading (Sign-off).
     * Menerapkan Signoff Guardrail: Seluruh catatan harus resolved & verified.
     */
    public function completeSignoff(
        Submission|string $submission,
        User $actor
    ): ProofAssignment {
        $sub = $this->resolveSubmission($submission);

        return DB::transaction(function () use ($sub, $actor): ProofAssignment {
            $galley = $this->resolveActiveGalley($sub);

            // Guardrail 1: Pastikan tidak ada catatan yang belum diselesaikan Layout Editor
            $unresolvedNotes = ProofreadingNote::where('galley_id', $galley->id)
                ->where('resolved', false)
                ->get();

            if ($unresolvedNotes->isNotEmpty()) {
                $count = $unresolvedNotes->count();
                $criticalCount = $unresolvedNotes->whereIn('severity', ['critical', 'major'])->count();
                $detail = $criticalCount > 0 ? " ({$criticalCount} berkategori Kritis/Major)" : '';

                throw new DomainException("Tidak dapat menyelesaikan proofreading karena masih terdapat {$count} catatan koreksi{$detail} yang belum diselesaikan oleh Layout Editor.");
            }

            // Guardrail 2: Pastikan seluruh perbaikan telah diverifikasi proofreader
            $unverifiedCount = ProofreadingNote::where('galley_id', $galley->id)
                ->where('resolved', true)
                ->where('verified', false)
                ->count();

            if ($unverifiedCount > 0) {
                throw new DomainException("Tidak dapat memberikan persetujuan sign-off karena terdapat {$unverifiedCount} perbaikan layout editor yang belum Anda verifikasi.");
            }

            // Tandai penugasan proofreading selesai
            $assignment = ProofAssignment::where('galley_id', $galley->id)->latest()->first();
            if (! $assignment) {
                $assignment = ProofAssignment::create([
                    'galley_id' => $galley->id,
                    'proofreader_id' => $actor->id,
                    'file_id' => $galley->file_id,
                    'due_at' => now(),
                    'status' => 'completed',
                    'assigned_at' => now(),
                    'checksum_sha256' => (string) ($galley->checksum_sha256 ? substr((string) $galley->checksum_sha256, 0, 64) : hash('sha256', $galley->id)),
                ]);
            } else {
                $assignment->status = 'completed';
                $assignment->save();
            }

            // Transisi tahap galai ke 'approved'
            if ($galley->stage === 'proofreading') {
                $galley->stage = 'approved';
                $galley->save();
            }

            // Kirim notifikasi email persetujuan signoff
            $this->notifySignoffCompleted($sub, $galley, $assignment, $actor);

            return $assignment;
        });
    }

    /**
     * Menerbitkan tiket eskalasi ketidaksesuaian naskah dari Proofreader ke Section Editor.
     * Menerapkan XOR constraint: copyedit_id IS NULL AND galley_id IS NOT NULL.
     */
    public function submitEscalation(
        Submission|string $submission,
        string $reason,
        User $actor
    ): ProductionEscalation {
        $sub = $this->resolveSubmission($submission);

        if (empty(trim($reason))) {
            throw new DomainException('Alasan eskalasi redaksi wajib diisi.');
        }

        return DB::transaction(function () use ($sub, $reason, $actor): ProductionEscalation {
            $activeGalley = Galley::where('submission_id', $sub->id)
                ->where('status', 'active')
                ->latest('version')
                ->first();

            // Cek apakah ada eskalasi aktif yang belum terselesaikan
            $existingActive = ProductionEscalation::where('submission_id', $sub->id)
                ->whereIn('status', ['open', 'investigating'])
                ->first();

            if ($existingActive) {
                throw new DomainException('Sudah terdapat tiket eskalasi aktif yang sedang ditindaklanjuti untuk naskah ini.');
            }

            $escalation = ProductionEscalation::create([
                'submission_id' => $sub->id,
                'copyedit_id' => null,
                'galley_id' => $activeGalley?->id,
                'opened_by' => $actor->id,
                'reason' => trim($reason),
                'status' => 'open',
                'resolution' => null,
                'resume_stage' => null,
                'version' => 1,
            ]);

            // Kirim notifikasi email eskalasi
            $this->notifyEscalationSubmitted($sub, $escalation, $actor);

            return $escalation;
        });
    }

    /**
     * Notifikasi email saat catatan koreksi baru ditambahkan.
     */
    protected function notifyCorrectionNotesAdded(Submission $submission, ProofreadingNote $note, User $actor): void
    {
        try {
            $layoutAssignment = LayoutAssignment::where('submission_id', $submission->id)->first();
            $layoutEditor = $layoutAssignment?->layoutEditor
                ?? User::where('email', 'rahmat.hidayat@brida.makassar.go.id')->first();

            if ($layoutEditor && filter_var($layoutEditor->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($layoutEditor->email)->send(
                    new CorrectionNotesAddedMail($submission, $note, $actor)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirimkan email catatan koreksi proofreading: '.$e->getMessage());
        }
    }

    /**
     * Notifikasi email saat catatan perbaikan diverifikasi sesuai.
     */
    protected function notifyNoteVerified(Submission $submission, ProofreadingNote $note, User $actor): void
    {
        try {
            $layoutAssignment = LayoutAssignment::where('submission_id', $submission->id)->first();
            $layoutEditor = $layoutAssignment?->layoutEditor
                ?? User::where('email', 'rahmat.hidayat@brida.makassar.go.id')->first();

            if ($layoutEditor && filter_var($layoutEditor->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($layoutEditor->email)->send(
                    new ProofreadingNoteVerifiedMail($submission, $note, $actor)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirimkan email verifikasi catatan proofreading: '.$e->getMessage());
        }
    }

    /**
     * Notifikasi email saat proofreading sign-off selesai.
     */
    protected function notifySignoffCompleted(
        Submission $submission,
        Galley $galley,
        ProofAssignment $assignment,
        User $actor
    ): void {
        try {
            $layoutAssignment = LayoutAssignment::where('submission_id', $submission->id)->first();
            $layoutEditor = $layoutAssignment?->layoutEditor
                ?? User::where('email', 'rahmat.hidayat@brida.makassar.go.id')->first();

            $sectionEditor = ($layoutAssignment?->assigned_by ? User::find($layoutAssignment->assigned_by) : null)
                ?? User::where('email', 'prof.hamka@unhas.ac.id')->first();

            $recipients = array_filter([$layoutEditor?->email, $sectionEditor?->email]);

            foreach ($recipients as $recipient) {
                if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($recipient)->send(
                        new ProofreadingSignoffCompletedMail($submission, $galley, $assignment, $actor)
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirimkan email signoff proofreading: '.$e->getMessage());
        }
    }

    /**
     * Notifikasi email saat eskalasi diajukan.
     */
    protected function notifyEscalationSubmitted(
        Submission $submission,
        ProductionEscalation $escalation,
        User $actor
    ): void {
        try {
            $layoutAssignment = LayoutAssignment::where('submission_id', $submission->id)->first();
            $sectionEditor = ($layoutAssignment?->assigned_by ? User::find($layoutAssignment->assigned_by) : null)
                ?? User::where('email', 'prof.hamka@unhas.ac.id')->first()
                ?? $actor;

            if ($sectionEditor && filter_var($sectionEditor->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($sectionEditor->email)->send(
                    new ProofreaderEscalationSubmittedMail($submission, $escalation, $actor)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirimkan email eskalasi proofreader: '.$e->getMessage());
        }
    }
}
