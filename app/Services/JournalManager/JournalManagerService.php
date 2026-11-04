<?php

declare(strict_types=1);

namespace App\Services\JournalManager;

use App\Mail\JournalManager\IssuePublishedMail;
use App\Mail\JournalManager\RoleAssignedMail;
use App\Models\Issue;
use App\Models\IssueArticle;
use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserRole;
use App\Services\LayoutEditor\LayoutEditorService;
use App\Services\PortalAccess;
use App\Services\PublicationService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class JournalManagerService
{
    /**
     * Membuat edisi nomor terbitan baru sebagai draf.
     */
    public function createIssue(Journal $journal, array $data, ?User $actor = null): Issue
    {
        $volume = trim((string) ($data['volume'] ?? ''));
        $number = trim((string) ($data['number'] ?? ''));
        $year = (int) ($data['year'] ?? date('Y'));

        if ($volume === '' || $number === '') {
            throw new DomainException('Volume dan nomor terbitan wajib diisi.');
        }

        // Invariant Guard: Nomor terbitan tidak boleh duplikat pada tahun yang sama
        $exists = Issue::where('journal_id', $journal->id)
            ->where('volume', $volume)
            ->where('number', $number)
            ->where('year', $year)
            ->exists();

        if ($exists) {
            throw new DomainException("Edisi terbitan Vol. {$volume} No. {$number} ({$year}) sudah terdaftar.");
        }

        $titleInput = trim((string) ($data['title'] ?? ''));
        $title = $titleInput !== ''
            ? "Vol. {$volume} No. {$number} ({$year}): {$titleInput}"
            : "Vol. {$volume} No. {$number} ({$year})";

        $doiPrefix = ! empty($data['doi_prefix']) ? trim($data['doi_prefix']) : null;

        return DB::transaction(function () use ($journal, $volume, $number, $year, $title, $data, $doiPrefix) {
            return Issue::create([
                'journal_id' => $journal->id,
                'volume' => $volume,
                'number' => $number,
                'year' => $year,
                'title' => $title,
                'description' => $data['description'] ?? null,
                'status' => 'draft',
                'is_current' => false,
                'doi_prefix' => $doiPrefix,
                'version' => 1,
            ]);
        });
    }

    /**
     * Memasukkan naskah siap terbit ke dalam daftar isi edisi (Table of Contents).
     */
    public function addArticleToIssue(Issue $issue, Submission $submission, array $options = [], ?User $actor = null): IssueArticle
    {
        return DB::transaction(function () use ($issue, $submission, $options, $actor) {
            if ($actor) {
                abort_unless($actor->userRoles()->where('journal_id', $issue->journal_id)->where('role_code', 'manager')->exists(), 403);
            }
            $issue = Issue::query()->lockForUpdate()->findOrFail($issue->id);
            $submission = Submission::query()->lockForUpdate()->findOrFail($submission->id);
            if ($submission->journal_id !== $issue->journal_id || ! app(LayoutEditorService::class)->checkPublicationReadiness($submission)['is_ready']) {
                throw new DomainException('Naskah belum siap terbit atau berasal dari jurnal lain.');
            }

            if ($issue->isPublished()) {
                throw new DomainException('Tidak dapat menambahkan naskah ke edisi yang sudah berstatus terbit resmi.');
            }

            if ($issue->issueArticles()->where('submission_id', $submission->id)->exists()) {
                throw new DomainException("Naskah '{$submission->title}' sudah terdaftar di dalam edisi ini.");
            }

            return DB::transaction(function () use ($issue, $submission, $options) {
                $nextPosition = ($issue->issueArticles()->max('position') ?? 0) + 1;

                // Hitung rentang halaman naskah otomatis jika tidak ditentukan
                if (! empty($options['pages'])) {
                    $pages = (string) $options['pages'];
                } else {
                    $lastArticle = $issue->issueArticles()->orderByDesc('position')->first();
                    $startPage = 1;
                    if ($lastArticle && ! empty($lastArticle->pages) && preg_match('/-(\d+)$/', $lastArticle->pages, $matches)) {
                        $startPage = ((int) $matches[1]) + 1;
                    }
                    $endPage = $startPage + 13;
                    $pages = "{$startPage}-{$endPage}";
                }

                $doi = ! empty($issue->doi_prefix)
                    ? "{$issue->doi_prefix}.".str_pad((string) $nextPosition, 2, '0', STR_PAD_LEFT)
                    : null;

                return IssueArticle::create([
                    'issue_id' => $issue->id,
                    'submission_id' => $submission->id,
                    'position' => $nextPosition,
                    'pages' => $pages,
                    'doi' => $doi,
                    'version' => 1,
                ]);
            });
        });
    }

    /**
     * Mempublikasikan edisi terbitan resmi secara instan.
     */
    public function publishIssue(Issue $issue, ?User $actor = null): Issue
    {
        return DB::transaction(function () use ($issue, $actor) {
            if ($actor) {
                abort_unless($actor->userRoles()->where('journal_id', $issue->journal_id)->where('role_code', 'manager')->exists(), 403);
            }
            $issue = Issue::query()->lockForUpdate()->findOrFail($issue->id);
            foreach ($issue->issueArticles()->orderBy('submission_id')->get() as $entry) {
                app(PublicationService::class)->publish($issue, $entry);
            }

            if ($issue->isPublished()) {
                throw new DomainException('Edisi terbitan ini sudah berstatus publikasi resmi.');
            }

            // Invariant Guard: Edisi wajib memiliki minimal 1 naskah sebelum dipublikasikan
            $articlesCount = $issue->issueArticles()->count();
            if ($articlesCount === 0) {
                throw new DomainException('Edisi wajib memiliki minimal 1 naskah sah sebelum dapat dipublikasikan.');
            }

            return DB::transaction(function () use ($issue) {
                $issue->update([
                    'status' => 'published',
                    'published_at' => now(),
                    'publication_date' => now()->toDateString(),
                ]);

                // Mutasikan status seluruh naskah di dalam edisi menjadi published
                $submissionIds = $issue->issueArticles()->pluck('submission_id');
                Submission::whereIn('id', $submissionIds)->update([
                    'status' => 'published',
                ]);

                // Jika jurnal belum memiliki current issue, tetapkan edisi ini sebagai current
                $journal = $issue->journal;
                if ($journal && empty($journal->current_issue_id)) {
                    $journal->update(['current_issue_id' => $issue->id]);
                    $issue->update(['is_current' => true]);
                }

                // Kirim notifikasi email publikasi terbitan
                try {
                    $recipients = UserRole::where('journal_id', $issue->journal_id)
                        ->whereIn('role_code', ['manager', 'editor_in_chief', 'section_editor'])
                        ->with('user')
                        ->get()
                        ->pluck('user.email')
                        ->filter()
                        ->push('jurnal.brida@makassar.go.id')
                        ->unique();

                    foreach ($recipients as $email) {
                        Mail::to($email)->send(new IssuePublishedMail($issue));
                    }
                } catch (\Throwable) {
                    // Silently bypass delivery exceptions during offline operations
                }

                return $issue->fresh();
            });
        });
    }

    /**
     * Menetapkan edisi tertentu sebagai edisi berjalan aktif (Current Issue).
     */
    public function setCurrentIssue(Issue $issue, ?User $actor = null): Issue
    {
        return DB::transaction(function () use ($issue, $actor) {
            if ($actor) {
                abort_unless($actor->userRoles()->where('journal_id', $issue->journal_id)->where('role_code', 'manager')->exists(), 403);
            }
            $issue = Issue::query()->lockForUpdate()->findOrFail($issue->id);

            if (! $issue->isPublished()) {
                throw new DomainException('Hanya edisi yang sudah berstatus terbit resmi yang dapat ditetapkan sebagai Current Issue.');
            }

            return DB::transaction(function () use ($issue) {
                // Nonaktifkan is_current pada seluruh edisi jurnal terkait
                Issue::where('journal_id', $issue->journal_id)->update(['is_current' => false]);

                $issue->update(['is_current' => true]);

                if ($issue->journal) {
                    $issue->journal->update(['current_issue_id' => $issue->id]);
                }

                return $issue->fresh();
            });
        });
    }

    /**
     * Menambahkan seksi / bidang naskah baru ke jurnal.
     */
    public function createSection(Journal $journal, array $data, ?User $actor = null): Section
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new DomainException('Nama bidang riset / seksi wajib diisi.');
        }

        $baseSlug = Str::slug($name);
        if ($baseSlug === '') {
            $baseSlug = 'sec-'.Str::random(6);
        }

        $slug = $baseSlug;
        $counter = 1;
        while (Section::where('journal_id', $journal->id)->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $nextPosition = (Section::where('journal_id', $journal->id)->max('position') ?? 0) + 1;
        $acceptingSubmissions = isset($data['accepting_submissions'])
            ? (bool) $data['accepting_submissions']
            : true;

        return Section::create([
            'journal_id' => $journal->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'policy' => $data['policy'] ?? null,
            'accepting_submissions' => $acceptingSubmissions,
            'position' => $nextPosition,
            'version' => 1,
        ]);
    }

    /**
     * Memperbarui pengaturan identitas dan kebijakan editorial jurnal.
     */
    public function updateJournalSettings(Journal $journal, array $data, ?User $actor = null): Journal
    {
        $updateData = [];

        $fields = [
            'name',
            'initials',
            'publisher',
            'editorial_email',
            'contact_phone',
            'focus_scope',
            'frequency',
            'apc_fee',
            'apc_enabled',
            'apc_amount',
            'payment_bank_name',
            'payment_account_number',
            'payment_account_holder',
            'payment_instructions',
            'open_access_policy',
            'peer_review_policy',
            'license',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $val = $data[$field];
                if ($field === 'apc_enabled') {
                    $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                }
                $updateData[$field] = $val;
            }
        }

        if (array_key_exists('issn_print', $data)) {
            $updateData['issn'] = $data['issn_print'];
        }
        if (array_key_exists('issn_online', $data)) {
            $updateData['eissn'] = $data['issn_online'];
        }

        $journal->update($updateData);

        return $journal->fresh();
    }

    /**
     * Menetapkan atau memperbarui peran pengguna dalam jurnal.
     */
    public function assignUserRole(User $user, Journal $journal, string $roleCode, ?string $expertiseKeywords = null, ?User $actor = null): UserRole
    {
        $validRoles = [
            'manager',
            'editor_in_chief',
            'section_editor',
            'copyeditor',
            'layout_editor',
            'proofreader',
            'reviewer',
            'author',
        ];

        if (! in_array($roleCode, $validRoles, true)) {
            throw new DomainException("Kode peran '{$roleCode}' tidak valid.");
        }

        $keywords = null;
        if ($expertiseKeywords !== null && trim($expertiseKeywords) !== '') {
            $keywords = array_values(array_filter(array_map('trim', explode(',', $expertiseKeywords))));
        }

        $userRole = UserRole::updateOrCreate(
            [
                'user_id' => $user->id,
                'journal_id' => $journal->id,
                'role_code' => $roleCode,
            ],
            [
                'expertise_keywords' => $keywords,
            ]
        );

        try {
            Mail::to($user->email)->send(new RoleAssignedMail($user, $userRole));
        } catch (\Throwable) {
            // Silently bypass delivery exceptions during offline operations
        }

        return $userRole;
    }

    /**
     * Mengirimkan tautan reset kata sandi ke email pengguna.
     */
    public function requestPasswordReset(string $email, ?User $actor = null): void
    {
        $normalizedEmail = strtolower(trim($email));
        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (! $user) {
            throw new DomainException("Pengguna dengan alamat email '{$email}' tidak ditemukan di sistem.");
        }

        if ($actor && ! $actor->isSysAdmin()) {
            $journals = PortalAccess::journalIds($actor, 'manager');
            if (! $user->userRoles()->whereIn('journal_id', $journals)->exists()) {
                throw new DomainException('Pengguna tidak termasuk jurnal yang Anda kelola.');
            }
        }
        Password::sendResetLink(['email' => $user->email]);
    }
}
