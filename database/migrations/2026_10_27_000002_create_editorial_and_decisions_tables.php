<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Module 03: Editorial Assignments, Desk Reviews, and Decisions.
     */
    public function up(): void
    {
        // 1. Tabel 18: editor_assignments
        Schema::create('editor_assignments', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('submission_id', 26);
            $table->foreignUlid('editor_id')->constrained('users')->restrictOnDelete();
            $table->string('role', 64)->default('section_editor'); // section_editor, managing_editor
            $table->dateTime('assigned_at', 6);
            $table->dateTime('revoked_at', 6)->nullable();
            $table->text('note')->nullable();
            $table->timestamps(6);

            $table->foreign('submission_id')->references('id')->on('submissions')->restrictOnDelete();
            $table->index(['submission_id', 'role']);
        });

        // 2. Tabel 19: desk_reviews
        Schema::create('desk_reviews', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('submission_id', 26);
            $table->foreignUlid('assessed_by')->constrained('users')->restrictOnDelete();
            $table->boolean('scope_fit')->default(true);
            $table->boolean('files_complete')->default(true);
            $table->boolean('guidelines_compliant')->default(true);
            $table->decimal('similarity_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 191)->default('passed'); // passed, rejected, revision_required
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('submission_id')->references('id')->on('submissions')->restrictOnDelete();
            $table->index(['submission_id', 'status']);
        });

        // 3. Tabel 20: editorial_decisions
        Schema::create('editorial_decisions', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('submission_id', 26);
            $table->char('review_round_id', 26)->nullable();
            $table->foreignUlid('decided_by')->constrained('users')->restrictOnDelete();
            $table->string('decision', 191); // accept, revisions_required, resubmit, decline
            $table->text('note_for_author');
            $table->text('internal_note')->nullable();
            $table->string('status', 191)->default('sent'); // draft, sent, corrected
            $table->dateTime('revision_due_at', 6)->nullable();
            $table->dateTime('sent_at', 6)->nullable();
            $table->text('override_reason')->nullable();
            $table->char('corrects_decision_id', 26)->nullable();
            $table->text('minimum_review_override_reason')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('submission_id')->references('id')->on('submissions')->restrictOnDelete();
            $table->foreign('corrects_decision_id')->references('id')->on('editorial_decisions')->restrictOnDelete();
            $table->index(['submission_id', 'decision']);
        });

        // 4. Tabel 21: decision_feedback
        Schema::create('decision_feedback', function (Blueprint $table) {
            $table->char('decision_id', 26);
            $table->char('review_id', 26);
            $table->json('released_feedback');
            $table->dateTime('released_at', 6);
            $table->timestamps(6);

            $table->primary(['decision_id', 'review_id']);
            $table->foreign('decision_id')->references('id')->on('editorial_decisions')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decision_feedback');
        Schema::dropIfExists('editorial_decisions');
        Schema::dropIfExists('desk_reviews');
        Schema::dropIfExists('editor_assignments');
    }
};
