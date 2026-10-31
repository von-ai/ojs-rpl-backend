<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add note verification support for Proofreaders.
     */
    public function up(): void
    {
        Schema::table('proofreading_notes', function (Blueprint $table) {
            $table->boolean('verified')->default(false)->after('resolved');
            $table->dateTime('verified_at', 6)->nullable()->after('resolved_at');
            $table->foreignUlid('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proofreading_notes', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verified', 'verified_at', 'verified_by']);
        });
    }
};
