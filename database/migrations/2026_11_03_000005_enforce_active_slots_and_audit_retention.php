<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->string('journal_scope', 26)->virtualAs("COALESCE(journal_id, '__system__')");
            $table->unique(['user_id', 'journal_scope', 'role_code'], 'user_roles_scope_unique');
        });
        Schema::table('layout_assignments', function (Blueprint $table) {
            $table->unsignedTinyInteger('live_slot')->virtualAs('CASE WHEN revoked_at IS NULL THEN 1 ELSE NULL END');
            $table->unique(['submission_id', 'live_slot'], 'layout_assignments_live_unique');
        });
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->unsignedTinyInteger('live_slot')->virtualAs("CASE WHEN status IN ('invited', 'accepted', 'overdue') THEN 1 ELSE NULL END");
            $table->unique(['review_round_id', 'reviewer_id', 'live_slot'], 'review_assignments_live_unique');
        });
        Schema::table('review_extensions', function (Blueprint $table) {
            $table->unsignedTinyInteger('pending_slot')->virtualAs("CASE WHEN status = 'pending' THEN 1 ELSE NULL END");
            $table->unique(['assignment_id', 'pending_slot'], 'review_extensions_pending_unique');
        });
        foreach (['UPDATE', 'DELETE'] as $action) {
            $name = 'audit_logs_forbid_'.strtolower($action);
            if (DB::getDriverName() === 'mysql') {
                DB::unprepared("CREATE TRIGGER {$name} BEFORE {$action} ON audit_logs FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Audit log is append-only'");
            } elseif (DB::getDriverName() === 'sqlite') {
                DB::unprepared("CREATE TRIGGER {$name} BEFORE {$action} ON audit_logs BEGIN SELECT RAISE(ABORT, 'Audit log is append-only'); END");
            }
        }
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_forbid_update');
        DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_forbid_delete');
        foreach ([['review_extensions', 'pending_slot', 'review_extensions_pending_unique'], ['review_assignments', 'live_slot', 'review_assignments_live_unique'], ['layout_assignments', 'live_slot', 'layout_assignments_live_unique'], ['user_roles', 'journal_scope', 'user_roles_scope_unique']] as [$name, $column, $index]) {
            Schema::table($name, function (Blueprint $table) use ($column, $index) {
                $table->dropUnique($index);
                $table->dropColumn($column);
            });
        }
    }
};
