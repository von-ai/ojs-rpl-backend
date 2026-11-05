<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('files')) {
            DB::table('files')->where('storage_disk', 'local')->update(['storage_disk' => 'azure']);
        }
    }

    public function down(): void
    {
        // Operasi satu arah: arsitektur sepenuhnya bermigrasi ke Azure Blob Storage
    }
};
