<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('remember_expires_at')->nullable();
        });
        // Existing plaintext persistent tokens have no trustworthy lifetime.
        DB::table('users')->update(['remember_token' => null]);
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->restrictOnDelete();
            $table->char('password_fingerprint', 64);
            $table->timestamp('absolute_expires_at')->index();
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps(6);
            $table->index(['user_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_sessions');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('remember_expires_at'));
    }
};
