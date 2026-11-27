<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table): void {
            $table->string('cover_image_path')->nullable()->after('author_guideline_updated_at');
            $table->string('cover_image_filename')->nullable()->after('cover_image_path');
            $table->timestamp('cover_image_updated_at')->nullable()->after('cover_image_filename');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table): void {
            $table->dropColumn([
                'cover_image_path',
                'cover_image_filename',
                'cover_image_updated_at',
            ]);
        });
    }
};
