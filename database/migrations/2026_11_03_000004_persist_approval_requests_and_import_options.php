<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['copyedit_approvals', 'galley_approvals'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dateTime('approved_at', 6)->nullable()->change();
                $table->dateTime('requested_at', 6)->nullable();
            });
        }
        DB::table('copyedit_approvals')->where('decision', 'requested')->update(['requested_at' => DB::raw('created_at'), 'approved_at' => null]);
        Schema::table('user_imports', function (Blueprint $table) {
            $table->boolean('send_invitation_email')->default(false);
            $table->foreignUlid('file_id')->nullable()->constrained('files')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_imports', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
            $table->dropColumn(['file_id', 'send_invitation_email']);
        });
        foreach (['copyedit_approvals', 'galley_approvals'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('requested_at'));
        }
    }
};
