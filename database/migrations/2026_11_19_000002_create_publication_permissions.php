<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('submission_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('source', 20);
            $table->string('license', 191);
            $table->string('agreement_reference')->nullable();
            $table->timestamp('recorded_at');
            $table->index(['submission_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_permissions');
    }
};
