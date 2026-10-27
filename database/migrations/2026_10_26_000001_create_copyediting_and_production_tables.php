<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Module 05: Production and Copyediting.
     */
    public function up(): void
    {
        // 1. Tabel 33: copyedits
        Schema::create('copyedits', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('submission_id', 26);
            $table->foreignUlid('copy_editor_id')->constrained('users')->restrictOnDelete();
            $table->char('source_file_id', 26);
            $table->string('status', 191)->default('assigned'); // assigned, in_progress, completed
            $table->text('notes')->nullable();
            $table->dateTime('due_at', 6);
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('submission_id')->references('id')->on('submissions')->restrictOnDelete();
            $table->foreign('source_file_id')->references('id')->on('files')->restrictOnDelete();
        });

        // 2. Tabel 34: copyedit_outputs
        Schema::create('copyedit_outputs', function (Blueprint $table) {
            $table->char('copyedit_id', 26);
            $table->char('file_id', 26);
            $table->integer('position')->default(1);
            $table->timestamps(6);

            $table->primary(['copyedit_id', 'file_id']);
            $table->foreign('copyedit_id')->references('id')->on('copyedits')->restrictOnDelete();
            $table->foreign('file_id')->references('id')->on('files')->restrictOnDelete();
        });

        // 3. Tabel 35: copyedit_approvals
        Schema::create('copyedit_approvals', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('copyedit_id', 26);
            $table->foreignUlid('author_id')->constrained('users')->restrictOnDelete();
            $table->string('decision', 191); // approved, revisions_requested
            $table->text('reason')->nullable();
            $table->integer('output_version')->default(1);
            $table->dateTime('approved_at', 6);
            $table->dateTime('invalidated_at', 6)->nullable();
            $table->text('note')->nullable();
            $table->timestamps(6);

            $table->foreign('copyedit_id')->references('id')->on('copyedits')->restrictOnDelete();
        });

        // 4. Tabel 36: copyedit_approved_files
        Schema::create('copyedit_approved_files', function (Blueprint $table) {
            $table->char('approval_id', 26);
            $table->char('file_id', 26);
            $table->text('checksum_sha256');
            $table->timestamps(6);

            $table->primary(['approval_id', 'file_id']);
            $table->foreign('approval_id')->references('id')->on('copyedit_approvals')->restrictOnDelete();
            $table->foreign('file_id')->references('id')->on('files')->restrictOnDelete();
        });

        // 5. Tabel 37: layout_assignments
        Schema::create('layout_assignments', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('submission_id', 26);
            $table->foreignUlid('layout_editor_id')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('assigned_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('assigned_at', 6);
            $table->dateTime('due_at', 6);
            $table->dateTime('revoked_at', 6)->nullable();
            $table->text('reason')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('submission_id')->references('id')->on('submissions')->restrictOnDelete();
        });

        // 6. Tabel 39: production_escalations
        Schema::create('production_escalations', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('submission_id', 26);
            $table->char('copyedit_id', 26)->nullable();
            $table->char('galley_id', 26)->nullable();
            $table->foreignUlid('opened_by')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->string('status', 191)->default('open'); // open, investigating, resolved
            $table->text('resolution')->nullable();
            $table->text('resume_stage')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('submission_id')->references('id')->on('submissions')->restrictOnDelete();
            $table->foreign('copyedit_id')->references('id')->on('copyedits')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_escalations');
        Schema::dropIfExists('layout_assignments');
        Schema::dropIfExists('copyedit_approved_files');
        Schema::dropIfExists('copyedit_approvals');
        Schema::dropIfExists('copyedit_outputs');
        Schema::dropIfExists('copyedits');
    }
};
