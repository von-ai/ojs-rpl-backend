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
        Schema::create('sections', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->char('journal_id', 26)->index();
            $table->string('name', 255);
            $table->string('slug', 191);
            $table->text('description')->nullable();
            $table->text('policy')->nullable();
            $table->boolean('accepting_submissions')->default(true);
            $table->integer('position')->default(1);
            $table->integer('version')->default(1);
            $table->timestamps(6);

            $table->foreign('journal_id')->references('id')->on('journals')->cascadeOnDelete();
            $table->unique(['journal_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
