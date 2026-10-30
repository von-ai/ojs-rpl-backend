<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Module 03 (Tabel 22: revisions).
     */
    public function up(): void
    {
        Schema::create('revisions', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('submission_id', 26);
            $table->char('decision_id', 26)->nullable();
            $table->integer('round')->default(1);
            $table->string('status', 191)->default('submitted'); // draft, submitted, overdue, accepted
            $table->char('manuscript_file_id', 26);
            $table->char('response_letter_file_id', 26)->nullable();
            $table->dateTime('due_at', 6)->nullable();
            $table->json('grace_policy_snapshot')->nullable();
            $table->dateTime('submitted_at', 6)->nullable();
            $table->text('note')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('submission_id')->references('id')->on('submissions')->restrictOnDelete();
            if (Schema::hasTable('editorial_decisions')) {
                $table->foreign('decision_id')->references('id')->on('editorial_decisions')->restrictOnDelete();
            }
            $table->foreign('manuscript_file_id')->references('id')->on('files')->restrictOnDelete();
            $table->foreign('response_letter_file_id')->references('id')->on('files')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
