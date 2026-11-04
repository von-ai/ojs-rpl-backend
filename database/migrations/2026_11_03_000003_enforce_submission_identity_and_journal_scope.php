<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_sequences', function (Blueprint $table) {
            $table->unsignedInteger('year')->primary();
            $table->unsignedBigInteger('last_number')->default(0);
        });
        foreach (DB::table('submissions')->select('submission_number')->cursor() as $row) {
            if (preg_match('/^SUB-(\d{4})-(\d+)$/', $row->submission_number, $match)) {
                $year = (int) $match[1];
                $number = max((int) $match[2], (int) DB::table('submission_sequences')->where('year', $year)->value('last_number'));
                DB::table('submission_sequences')->updateOrInsert(['year' => $year], ['last_number' => $number]);
            }
        }
        Schema::table('submissions', fn (Blueprint $table) => $table->unique('submission_number'));
        Schema::table('sections', fn (Blueprint $table) => $table->unique(['id', 'journal_id']));
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreign(['section_id', 'journal_id'], 'submissions_section_journal_fk')->references(['id', 'journal_id'])->on('sections')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign('submissions_section_journal_fk');
            $table->dropUnique(['submission_number']);
        });
        Schema::table('sections', fn (Blueprint $table) => $table->dropUnique(['id', 'journal_id']));
        Schema::dropIfExists('submission_sequences');
    }
};
