<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Module 04: Peer Review, Rubrics, Assignments, Reviews, and Extensions.
     */
    public function up(): void
    {
        // 1. Tabel 12: review_rubric_versions
        if (! Schema::hasTable('review_rubric_versions')) {
            Schema::create('review_rubric_versions', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('journal_id', 26);
                $table->integer('version')->default(1);
                $table->json('criteria');
                $table->text('reviewer_guidelines')->nullable();
                $table->timestamps(6);

                $table->foreign('journal_id')->references('id')->on('journals')->restrictOnDelete();
                $table->unique(['journal_id', 'version'], 'uq_rubric_journal_version');
            });
        }

        // 2. Tabel 27: review_rounds
        if (! Schema::hasTable('review_rounds')) {
            Schema::create('review_rounds', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('submission_id', 26);
                $table->integer('round')->default(1);
                $table->char('manuscript_file_id', 26)->nullable();
                $table->char('anonymized_file_id', 26)->nullable();
                $table->char('rubric_version_id', 26)->nullable();
                $table->integer('minimum_completed_reviews')->default(2);
                $table->string('status', 191)->default('active'); // active, completed, cancelled
                $table->integer('version')->default(1);
                $table->string('review_type', 191)->default('double_blind');
                $table->text('note')->nullable();
                $table->char('manuscript_checksum', 64)->nullable();
                $table->char('anonymized_checksum', 64)->nullable();
                $table->json('rubric_snapshot')->nullable();
                $table->text('reviewer_title')->nullable();
                $table->text('reviewer_abstract')->nullable();
                $table->string('se_recommendation', 191)->nullable(); // accept, revisions_required, resubmit, decline
                $table->text('se_recommendation_note')->nullable();
                $table->dateTime('se_recommended_at', 6)->nullable();
                $table->dateTime('closed_at', 6)->nullable();
                $table->timestamps(6);

                $table->foreign('submission_id')->references('id')->on('submissions')->restrictOnDelete();
                if (Schema::hasTable('files')) {
                    $table->foreign('manuscript_file_id')->references('id')->on('files')->restrictOnDelete();
                    $table->foreign('anonymized_file_id')->references('id')->on('files')->restrictOnDelete();
                }
                $table->foreign('rubric_version_id')->references('id')->on('review_rubric_versions')->restrictOnDelete();
                $table->index(['submission_id', 'round']);
            });
        }

        // 3. Tabel 28: review_assignments
        if (! Schema::hasTable('review_assignments')) {
            Schema::create('review_assignments', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('review_round_id', 26);
                $table->foreignUlid('reviewer_id')->constrained('users')->restrictOnDelete();
                $table->string('status', 191)->default('invited'); // invited, accepted, declined, completed, overdue, cancelled, no_response
                $table->dateTime('response_due_at', 6);
                $table->dateTime('review_due_at', 6);
                $table->dateTime('responded_at', 6)->nullable();
                $table->dateTime('completed_at', 6)->nullable();
                $table->integer('deadline_version')->default(1);
                $table->integer('version')->default(1);
                $table->text('invitation_message')->nullable();
                $table->timestamps(6);

                $table->foreign('review_round_id')->references('id')->on('review_rounds')->restrictOnDelete();
                $table->index(['review_round_id', 'reviewer_id']);
                $table->index(['review_round_id', 'status']);
            });
        }

        // 4. Tabel 29: reviews
        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('assignment_id', 26)->unique();
                $table->json('scores')->nullable();
                $table->text('comments_for_author');
                $table->text('confidential_comments_for_editor')->nullable();
                $table->text('recommendation'); // accept, revisions_required, resubmit, decline
                $table->boolean('certify_no_conflict')->default(true);
                $table->string('status', 191)->default('submitted'); // draft, submitted
                $table->dateTime('submitted_at', 6)->nullable();
                $table->integer('version')->default(1);
                $table->timestamps(6);

                $table->foreign('assignment_id')->references('id')->on('review_assignments')->restrictOnDelete();
            });
        }

        // 5. Tabel 30: review_annotations
        if (! Schema::hasTable('review_annotations')) {
            Schema::create('review_annotations', function (Blueprint $table) {
                $table->char('review_id', 26);
                $table->char('file_id', 26);
                $table->timestamps(6);

                $table->primary(['review_id', 'file_id']);
                $table->foreign('review_id')->references('id')->on('reviews')->restrictOnDelete();
                if (Schema::hasTable('files')) {
                    $table->foreign('file_id')->references('id')->on('files')->restrictOnDelete();
                }
            });
        }

        // 6. Tabel 31: review_extensions
        if (! Schema::hasTable('review_extensions')) {
            Schema::create('review_extensions', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('assignment_id', 26);
                $table->dateTime('requested_due_at', 6);
                $table->text('reason');
                $table->string('status', 191)->default('pending'); // pending, approved, rejected
                $table->foreignUlid('decided_by')->nullable()->constrained('users')->restrictOnDelete();
                $table->text('decision_reason')->nullable();
                $table->dateTime('decided_at', 6)->nullable();
                $table->integer('version')->default(1);
                $table->timestamps(6);

                $table->foreign('assignment_id')->references('id')->on('review_assignments')->restrictOnDelete();
                $table->index(['assignment_id', 'status']);
            });
        }

        // 7. Tabel 32: review_reminders
        if (! Schema::hasTable('review_reminders')) {
            Schema::create('review_reminders', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('assignment_id', 26);
                $table->foreignUlid('sent_by')->constrained('users')->restrictOnDelete();
                $table->dateTime('queued_at', 6);
                $table->string('delivery_status', 191)->default('sent');
                $table->text('message')->nullable();
                $table->timestamps(6);

                $table->foreign('assignment_id')->references('id')->on('review_assignments')->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_reminders');
        Schema::dropIfExists('review_extensions');
        Schema::dropIfExists('review_annotations');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('review_assignments');
        Schema::dropIfExists('review_rounds');
        Schema::dropIfExists('review_rubric_versions');
    }
};
