<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outbox_messages', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempt_count')->default(0);
            $table->timestamp('available_at')->nullable();
            $table->index(['status', 'available_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('outbox_messages', function (Blueprint $table) {
            $table->dropIndex(['status', 'available_at', 'created_at']);
            $table->dropColumn(['attempt_count', 'available_at']);
        });
    }
};
