<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy production roles were stored globally. Preserve only journals with explicit assignments.
        foreach (DB::table('user_roles')->whereNull('journal_id')->whereIn('role_code', ['copyeditor', 'layout_editor', 'proofreader', 'reviewer'])->get() as $grant) {
            $query = match ($grant->role_code) {
                'copyeditor' => DB::table('copyedits as a')->join('submissions as s', 's.id', '=', 'a.submission_id')->where('a.copy_editor_id', $grant->user_id),
                'layout_editor' => DB::table('layout_assignments as a')->join('submissions as s', 's.id', '=', 'a.submission_id')->where('a.layout_editor_id', $grant->user_id),
                'proofreader' => DB::table('proof_assignments as a')->join('galleys as g', 'g.id', '=', 'a.galley_id')->join('submissions as s', 's.id', '=', 'g.submission_id')->where('a.proofreader_id', $grant->user_id),
                'reviewer' => DB::table('review_assignments as a')->join('review_rounds as r', 'r.id', '=', 'a.review_round_id')->join('submissions as s', 's.id', '=', 'r.submission_id')->where('a.reviewer_id', $grant->user_id),
            };
            foreach ($query->distinct()->pluck('s.journal_id') as $journalId) {
                DB::table('user_roles')->insertOrIgnore(['id' => (string) Str::ulid(), 'user_id' => $grant->user_id, 'journal_id' => $journalId, 'role_code' => $grant->role_code, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Keep explicit historical entitlements rather than restoring unrestricted global access.
    }
};
