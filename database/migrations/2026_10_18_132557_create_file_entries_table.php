<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('storage_disk', 50)->default('local');
            $table->text('storage_path');
            $table->text('original_name');
            $table->string('mime_type', 191);
            $table->bigInteger('byte_size');
            $table->char('checksum_sha256', 64)->nullable()->index();
            $table->string('virus_scan_status', 64)->default('clean');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
