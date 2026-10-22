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
        Schema::create('submissions', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('submission_number', 50)->index();
            $table->char('journal_id', 26)->index();
            $table->char('section_id', 26)->index();
            $table->foreignUlid('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('title');
            $table->text('abstract');
            $table->json('keywords')->nullable();
            $table->text('references')->nullable();
            $table->text('subject_area')->nullable();
            $table->text('comments_for_editor')->nullable();
            $table->string('status', 50)->default('submitted');
            $table->json('guideline_acceptance')->nullable();
            $table->dateTime('submitted_at', 6)->nullable();
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('journal_id')->references('id')->on('journals')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
