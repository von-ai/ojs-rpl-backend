<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Module 05: Galleys, Proofreading, and Production Approvals.
     */
    public function up(): void
    {
        // 1. Tabel 38: galleys
        Schema::create('galleys', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('submission_id', 26);
            $table->char('file_id', 26);
            $table->char('supersedes_galley_id', 26)->nullable();
            $table->string('format', 191)->default('pdf'); // pdf, html, xml
            $table->string('stage', 191)->default('draft'); // draft, proofreading, approved
            $table->string('status', 191)->default('active'); // active, superseded, rejected
            $table->text('checksum_sha256');
            $table->text('approved_checksum')->nullable();
            $table->integer('version')->default(1);
            $table->text('label');
            $table->timestamps(6);

            $table->foreign('submission_id')->references('id')->on('submissions')->restrictOnDelete();
            $table->foreign('file_id')->references('id')->on('files')->restrictOnDelete();
            $table->foreign('supersedes_galley_id')->references('id')->on('galleys')->restrictOnDelete();
        });

        // 2. Tabel 40: proof_assignments
        Schema::create('proof_assignments', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('galley_id', 26);
            $table->foreignUlid('proofreader_id')->constrained('users')->restrictOnDelete();
            $table->char('file_id', 26);
            $table->dateTime('due_at', 6);
            $table->string('status', 191)->default('assigned'); // assigned, in_progress, completed
            $table->dateTime('assigned_at', 6);
            $table->char('checksum_sha256', 64);
            $table->text('message')->nullable();
            $table->timestamps(6);

            $table->foreign('galley_id')->references('id')->on('galleys')->restrictOnDelete();
            $table->foreign('file_id')->references('id')->on('files')->restrictOnDelete();
        });

        // 3. Tabel 41: proofreading_notes
        Schema::create('proofreading_notes', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('proof_assignment_id', 26);
            $table->char('galley_id', 26);
            $table->char('file_id', 26);
            $table->text('checksum_sha256');
            $table->text('category'); // typo, layout, metadata, reference
            $table->integer('page')->nullable();
            $table->text('severity'); // minor, major, critical
            $table->text('description');
            $table->boolean('resolved')->default(false);
            $table->text('resolution')->nullable();
            $table->dateTime('resolved_at', 6)->nullable();
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('proof_assignment_id')->references('id')->on('proof_assignments')->restrictOnDelete();
            $table->foreign('galley_id')->references('id')->on('galleys')->restrictOnDelete();
            $table->foreign('file_id')->references('id')->on('files')->restrictOnDelete();
        });

        // 4. Tabel 42: galley_approvals
        Schema::create('galley_approvals', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('galley_id', 26);
            $table->foreignUlid('approved_by')->constrained('users')->restrictOnDelete();
            $table->string('stage', 191); // author_signoff, editor_final
            $table->text('checksum_sha256');
            $table->dateTime('approved_at', 6);
            $table->dateTime('invalidated_at', 6)->nullable();
            $table->char('file_id', 26);
            $table->text('note')->nullable();
            $table->timestamps(6);

            $table->foreign('galley_id')->references('id')->on('galleys')->restrictOnDelete();
            $table->foreign('file_id')->references('id')->on('files')->restrictOnDelete();
        });

        // Foreign key for production_escalations.galley_id
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('production_escalations', function (Blueprint $table) {
                $table->foreign('galley_id')->references('id')->on('galleys')->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('production_escalations', function (Blueprint $table) {
                $table->dropForeign(['galley_id']);
            });
        }

        Schema::dropIfExists('galley_approvals');
        Schema::dropIfExists('proofreading_notes');
        Schema::dropIfExists('proof_assignments');
        Schema::dropIfExists('galleys');
    }
};
