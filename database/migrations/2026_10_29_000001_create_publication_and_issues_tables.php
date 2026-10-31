<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Module 06: Publication, Issues, Articles, and COUNTER Metrics.
     */
    public function up(): void
    {
        // 1. Tabel 43: issues
        if (! Schema::hasTable('issues')) {
            Schema::create('issues', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('journal_id', 26);
                $table->text('volume');
                $table->text('number');
                $table->integer('year');
                $table->text('title')->nullable();
                $table->text('description')->nullable();
                $table->string('status', 191)->default('draft'); // draft, scheduled, published
                $table->boolean('is_current')->default(false);
                $table->string('doi_prefix', 100)->nullable();
                $table->dateTime('scheduled_at', 6)->nullable();
                $table->dateTime('published_at', 6)->nullable();
                $table->date('publication_date')->nullable();
                $table->json('failure_diagnostics')->nullable();
                $table->integer('version')->default(1);
                $table->text('publication_failure_code')->nullable();
                $table->dateTime('publication_failed_at', 6)->nullable();
                $table->timestamps(6);

                $table->foreign('journal_id')->references('id')->on('journals')->restrictOnDelete();
            });
        } else {
            Schema::table('issues', function (Blueprint $table) {
                if (! Schema::hasColumn('issues', 'publication_date')) {
                    $table->date('publication_date')->nullable();
                }
                if (! Schema::hasColumn('issues', 'failure_diagnostics')) {
                    $table->json('failure_diagnostics')->nullable();
                }
                if (! Schema::hasColumn('issues', 'publication_failure_code')) {
                    $table->text('publication_failure_code')->nullable();
                }
                if (! Schema::hasColumn('issues', 'publication_failed_at')) {
                    $table->dateTime('publication_failed_at', 6)->nullable();
                }
            });
        }

        // 2. Tabel 45: articles
        if (! Schema::hasTable('articles')) {
            Schema::create('articles', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('submission_id', 26)->unique();
                $table->char('journal_id', 26);
                $table->char('section_id', 26);
                $table->string('slug', 191);
                $table->text('title');
                $table->text('abstract');
                $table->json('keywords');
                $table->text('references')->nullable();
                $table->string('license')->default('CC BY 4.0');
                $table->string('status', 191)->default('published'); // in_press, published, retracted
                $table->dateTime('published_at', 6)->nullable();
                $table->integer('version')->default(1);
                $table->timestamps(6);

                $table->foreign('submission_id')->references('id')->on('submissions')->restrictOnDelete();
                $table->foreign('journal_id')->references('id')->on('journals')->restrictOnDelete();
                $table->foreign('section_id')->references('id')->on('sections')->restrictOnDelete();
                $table->unique(['journal_id', 'slug']);
            });
        }

        // 3. Tabel 44: issue_articles
        if (! Schema::hasTable('issue_articles')) {
            Schema::create('issue_articles', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('issue_id', 26)->index();
                $table->char('article_id', 26)->nullable()->index();
                $table->char('submission_id', 26)->nullable()->index();
                $table->integer('position')->default(1);
                $table->text('pages')->nullable();
                $table->string('doi', 191)->nullable();
                $table->integer('version')->default(1);
                $table->timestamps(6);

                $table->foreign('issue_id')->references('id')->on('issues')->cascadeOnDelete();
                $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
                $table->foreign('submission_id')->references('id')->on('submissions')->cascadeOnDelete();
            });
        } else {
            Schema::table('issue_articles', function (Blueprint $table) {
                if (! Schema::hasColumn('issue_articles', 'article_id')) {
                    $table->char('article_id', 26)->nullable()->after('submission_id');
                }
            });
        }

        // 4. Tabel 46: article_contributors
        if (! Schema::hasTable('article_contributors')) {
            Schema::create('article_contributors', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('article_id', 26);
                $table->text('given_name');
                $table->text('family_name')->nullable();
                $table->text('affiliation');
                $table->text('orcid')->nullable();
                $table->integer('position')->default(1);
                $table->timestamps(6);

                $table->foreign('article_id')->references('id')->on('articles')->restrictOnDelete();
            });
        }

        // 5. Tabel 47: article_galleys
        if (! Schema::hasTable('article_galleys')) {
            Schema::create('article_galleys', function (Blueprint $table) {
                $table->char('article_id', 26);
                $table->char('galley_id', 26);
                $table->integer('position')->default(1);
                $table->timestamps(6);

                $table->primary(['article_id', 'galley_id']);
                $table->foreign('article_id')->references('id')->on('articles')->restrictOnDelete();
                $table->foreign('galley_id')->references('id')->on('galleys')->restrictOnDelete();
            });
        }

        // 6. Tabel 48: article_metrics
        if (! Schema::hasTable('article_metrics')) {
            Schema::create('article_metrics', function (Blueprint $table) {
                $table->char('id', 26)->primary();
                $table->char('article_id', 26);
                $table->char('galley_id', 26)->nullable();
                $table->string('event_type', 191); // abstract_view, galley_download
                $table->char('visit_hash', 64);
                $table->dateTime('window_start', 6);
                $table->integer('count')->default(1);
                $table->timestamps(6);

                $table->foreign('article_id')->references('id')->on('articles')->restrictOnDelete();
                if (Schema::hasTable('galleys')) {
                    $table->foreign('galley_id')->references('id')->on('galleys')->restrictOnDelete();
                }
                $table->index(['article_id', 'event_type', 'window_start']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_metrics');
        Schema::dropIfExists('article_galleys');
        Schema::dropIfExists('article_contributors');

        if (Schema::hasTable('issue_articles') && Schema::hasColumn('issue_articles', 'article_id')) {
            Schema::table('issue_articles', function (Blueprint $table) {
                $table->dropColumn('article_id');
            });
        }

        Schema::dropIfExists('articles');
    }
};
