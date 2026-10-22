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
        Schema::create('journals', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->string('slug', 191)->unique();
            $table->text('name');
            $table->string('issn', 50)->nullable();
            $table->string('eissn', 50)->nullable();
            $table->string('status', 50)->default('active');
            $table->char('current_issue_id', 26)->nullable();
            $table->integer('version')->default(1);
            $table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
