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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->foreignUlid('user_id')->primary()->constrained('users')->restrictOnDelete();
            $table->text('affiliation')->nullable();
            $table->string('country_code', 191)->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('journal_id', 26)->nullable();
            $table->foreignUlid('actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('actor_type', 191)->default('user');
            $table->string('system_actor', 191)->nullable();
            $table->text('effective_role')->nullable();
            $table->string('action', 191);
            $table->string('resource_type', 191)->nullable();
            $table->text('resource_id')->nullable();
            $table->json('before_after')->nullable();
            $table->text('reason')->nullable();
            $table->string('user_id', 26)->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_id', 64)->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
        });

        Schema::create('outbox_messages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('type', 191);
            $table->json('payload');
            $table->string('status', 32)->default('pending');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbox_messages');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_profiles');
    }
};
