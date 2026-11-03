<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_assignments', fn (Blueprint $table) => $table->text('response_reason')->nullable());
        $unsafe = DB::table('review_rounds')->where('status', 'active')->where(function ($query) {
            $query->whereNull('anonymized_file_id')->orWhereNull('anonymized_checksum')->orWhereNull('rubric_snapshot')->orWhereColumn('anonymized_file_id', 'manuscript_file_id');
        })->pluck('id');
        foreach ($unsafe as $id) {
            DB::table('review_assignments')->where('review_round_id', $id)->whereIn('status', ['invited', 'accepted', 'overdue'])->update(['status' => 'cancelled', 'version' => DB::raw('version + 1'), 'updated_at' => now()]);
            DB::table('review_rounds')->where('id', $id)->update(['status' => 'closed', 'closed_at' => now(), 'version' => DB::raw('version + 1'), 'updated_at' => now()]);
            DB::table('audit_logs')->insert(['id' => (string) Str::ulid(), 'actor_type' => 'system', 'system_actor' => 'security_migration', 'action' => 'review.unsafe_legacy_round.closed', 'resource_type' => 'review_round', 'resource_id' => $id, 'details' => json_encode(['reason' => 'Anonymized file/checksum or frozen rubric unavailable']), 'occurred_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('review_assignments', fn (Blueprint $table) => $table->dropColumn('response_reason'));
        // Historical unsafe assignments remain closed on rollback.
    }
};
