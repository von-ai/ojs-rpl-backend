<?php

declare(strict_types=1);

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
        // 1. Tabel Konfigurasi Platform Global (System Settings)
        Schema::create('system_settings', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 50)->default('string'); // string, boolean, integer, json
            $table->string('group', 50)->default('general');
            $table->timestamps(6);
        });

        // 2. Tabel Riwayat Pengimporan Massal Pengguna via CSV (User Imports)
        Schema::create('user_imports', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('journal_id', 26)->nullable()->index();
            $table->string('file_name', 255);
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->string('status', 50)->default('completed'); // pending, processing, completed, failed
            $table->json('error_log')->nullable();
            $table->foreignUlid('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(6);

            $table->foreign('journal_id')->references('id')->on('journals')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_imports');
        Schema::dropIfExists('system_settings');
    }
};
