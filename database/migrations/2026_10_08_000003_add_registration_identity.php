<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->unique();
            $table->string('status', 16)->default('pending');
            $table->unsignedInteger('version')->default(1);
        });
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->foreignUlid('user_id')->primary()->constrained()->restrictOnDelete();
            $table->string('affiliation');
            $table->timestamps();
        });
        Schema::create('identity_challenges', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->restrictOnDelete();
            $table->string('purpose');
            $table->char('token_hash', 64)->unique();
            $table->timestampTz('expires_at');
            $table->timestampTz('consumed_at')->nullable();
            $table->timestampsTz();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('journal_id')->nullable()->index();
            $table->foreignUlid('actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('actor_type');
            $table->string('system_actor')->nullable();
            $table->string('effective_role')->nullable();
            $table->string('action');
            $table->string('resource_type');
            $table->string('resource_id')->index();
            $table->json('before_after')->nullable();
            $table->text('reason')->nullable();
            $table->string('request_id')->nullable();
            $table->timestampTz('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('identity_challenges');
        Schema::dropIfExists('user_profiles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'status', 'version']);
        });
    }
};
