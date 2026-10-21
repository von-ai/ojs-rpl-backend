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
            $table->foreignUlid('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->text('affiliation')->nullable();
            $table->string('country_code', 191)->nullable();
            $table->timestamps();
        });

        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('active_membership_id', 26)->nullable();
            $table->string('active_role', 191)->nullable();
            $table->timestamp('absolute_expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('session_id')->constrained('auth_sessions')->cascadeOnDelete();
            $table->string('token_hash', 191)->unique();
            $table->string('parent_token_id', 26)->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('user_id', 26)->nullable();
            $table->string('action', 191);
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_id', 64)->nullable();
            $table->timestamp('created_at')->nullable();
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
        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('auth_sessions');
        Schema::dropIfExists('user_profiles');
    }
};
