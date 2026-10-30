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
        // 1. Perluas tabel journals dengan metadata identitas dan kebijakan editorial
        Schema::table('journals', function (Blueprint $table) {
            $table->string('initials', 50)->nullable()->after('name');
            $table->text('publisher')->nullable()->after('eissn');
            $table->string('editorial_email', 191)->nullable()->after('publisher');
            $table->string('contact_phone', 50)->nullable()->after('editorial_email');
            $table->text('focus_scope')->nullable()->after('contact_phone');
            $table->string('frequency', 191)->nullable()->after('focus_scope');
            $table->string('apc_fee', 191)->nullable()->after('frequency');
            $table->text('open_access_policy')->nullable()->after('apc_fee');
            $table->text('peer_review_policy')->nullable()->after('open_access_policy');
            $table->string('license', 191)->nullable()->after('peer_review_policy');
        });

        // 2. Tabel Edisi & Terbitan Berkala (Issues)
        Schema::create('issues', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('journal_id', 26)->index();
            $table->string('volume', 50);
            $table->string('number', 50);
            $table->integer('year');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 50)->default('draft'); // draft, scheduled, published
            $table->boolean('is_current')->default(false);
            $table->string('doi_prefix', 100)->nullable();
            $table->dateTime('scheduled_at', 6)->nullable();
            $table->dateTime('published_at', 6)->nullable();
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('journal_id')->references('id')->on('journals')->cascadeOnDelete();
            $table->unique(['journal_id', 'volume', 'number', 'year'], 'issues_journal_vol_num_year_unique');
        });

        // 3. Tabel Artikel Terbitan (Issue Articles / Table of Contents)
        Schema::create('issue_articles', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('issue_id', 26)->index();
            $table->char('submission_id', 26)->index();
            $table->integer('position')->default(1);
            $table->string('pages', 50)->nullable();
            $table->string('doi', 191)->nullable();
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('issue_id')->references('id')->on('issues')->cascadeOnDelete();
            $table->foreign('submission_id')->references('id')->on('submissions')->cascadeOnDelete();
            $table->unique(['issue_id', 'submission_id'], 'issue_articles_issue_submission_unique');
        });

        // 4. Tabel Peran Pengguna Jurnal (User Roles)
        Schema::create('user_roles', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('journal_id', 26)->nullable()->index();
            $table->string('role_code', 50); // manager, editor_in_chief, section_editor, copyeditor, layout_editor, proofreader, reviewer, author
            $table->json('expertise_keywords')->nullable();
            $table->timestamps(6);

            $table->foreign('journal_id')->references('id')->on('journals')->cascadeOnDelete();
            $table->unique(['user_id', 'journal_id', 'role_code'], 'user_roles_unique');
        });

        // 5. Tabel Warta Pengumuman Jurnal (Announcements)
        Schema::create('announcements', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('journal_id', 26)->index();
            $table->string('title', 255);
            $table->text('body');
            $table->boolean('is_published')->default(true);
            $table->dateTime('published_at', 6)->nullable();
            $table->timestamps(6);

            $table->foreign('journal_id')->references('id')->on('journals')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('issue_articles');
        Schema::dropIfExists('issues');

        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn([
                'initials',
                'publisher',
                'editorial_email',
                'contact_phone',
                'focus_scope',
                'frequency',
                'apc_fee',
                'open_access_policy',
                'peer_review_policy',
                'license',
            ]);
        });
    }
};
