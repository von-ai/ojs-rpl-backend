<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->string('author_guideline_path')->nullable()->after('license');
            $table->string('author_guideline_filename')->nullable()->after('author_guideline_path');
            $table->timestamp('author_guideline_updated_at')->nullable()->after('author_guideline_filename');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn([
                'author_guideline_path',
                'author_guideline_filename',
                'author_guideline_updated_at',
            ]);
        });
    }
};
