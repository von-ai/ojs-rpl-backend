<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('virus_scan_status', 64)->default('pending')->change();
        });
        // Older upload handlers asserted "clean" without a scanner. Recheck those files.
        DB::table('files')->where('virus_scan_status', 'clean')->update(['virus_scan_status' => 'pending']);
    }

    public function down(): void
    {
        // Preserve quarantine: rolling back must never mark unscanned files clean.
    }
};
