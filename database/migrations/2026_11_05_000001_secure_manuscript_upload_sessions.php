<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manuscript_upload_sessions', function (Blueprint $table) {
            $table->string('path', 300)->primary();
            $table->foreignUlid('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('original_name');
            $table->unsignedInteger('byte_size');
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
        });

        // Legacy direct uploads did not record who owned the underlying Blob.
        DB::table('files')->where('storage_disk', 'azure')
            ->where('virus_scan_status', '!=', 'infected')
            ->whereIn('id', DB::table('submission_files')->select('file_id')->where('genre', 'manuscript'))
            ->update(['virus_scan_status' => 'quarantined']);
    }

    public function down(): void
    {
        Schema::dropIfExists('manuscript_upload_sessions');
    }
};
