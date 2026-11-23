<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicationPermissionService
{
    public function record(User $user, ?string $activeJournalId, string $id, bool $editor, array $data): void
    {
        DB::transaction(function () use ($user, $activeJournalId, $id, $editor, $data) {
            $submission = Submission::query()->lockForUpdate()->findOrFail($id);
            abort_if($activeJournalId !== null && $activeJournalId !== $submission->journal_id, 404);
            if ($editor) {
                abort_unless($user->userRoles()->where('journal_id', $submission->journal_id)->where('role_code', 'editor_in_chief')->exists(), 403);
            } else {
                abort_unless($user->id === $submission->author_id, 403);
            }
            abort_if($submission->status === 'published', 409);
            $license = trim((string) $submission->journal()->lockForUpdate()->value('license'));
            if ($license === '' || $license !== $data['license']) {
                throw ValidationException::withMessages(['license' => 'Lisensi berubah. Muat ulang halaman dan periksa lisensi jurnal.']);
            }
            $evidence = [
                'submission_id' => $submission->id, 'actor_id' => $user->id,
                'license' => $license, 'source' => $editor ? 'written_agreement' : 'author',
                'agreement_reference' => $data['agreement_reference'] ?? null,
            ];
            $latest = DB::table('publication_permissions')->where('submission_id', $id)->latest('id')->first();
            if (! $latest || $latest->actor_id !== $user->id || $latest->license !== $license || $latest->agreement_reference !== $evidence['agreement_reference'] || $latest->source !== $evidence['source']) {
                DB::table('publication_permissions')->insert($evidence + ['recorded_at' => now()]);
            }
        });
    }
}
