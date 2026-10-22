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
        Schema::create('submission_contributors', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('submission_id', 26)->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('given_name', 150)->nullable();
            $table->string('family_name', 150);
            $table->string('email', 254);
            $table->text('affiliation');
            $table->string('orcid', 50)->nullable();
            $table->boolean('is_corresponding')->default(false);
            $table->integer('position')->default(1);
            $table->timestamps(6);

            $table->foreign('submission_id')->references('id')->on('submissions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_contributors');
    }
};
