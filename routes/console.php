<?php

use App\Jobs\BackupDatabaseToAzureJob;
use App\Jobs\CheckReviewDeadlinesJob;
use App\Jobs\ProcessOutboxJob;
use App\Jobs\PruneExpiredChallengesJob;
use App\Jobs\ScanFile;
use App\Models\FileEntry;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Pola Menerbitkan ke Queue (Publish to Queue Pattern)
|--------------------------------------------------------------------------
| Scheduler container (php artisan schedule:work) bertindak sebagai pemicu
| berbobot ringan (~1-2 ms) yang hanya mendispatch job ke broker antrean.
| Pekerjaan pemrosesan database/I-O berat dieksekusi secara asinkron
| oleh Worker container (php artisan queue:work).
|
*/

// Evaluasi batas waktu peer-review naskah setiap menit
Schedule::job(new CheckReviewDeadlinesJob)->everyMinute();

// Pemrosesan pesan transactional outbox (outbox_messages) setiap menit
Schedule::job(new ProcessOutboxJob)->everyMinute();

// Pembersihan berkas & token tantangan registrasi kedaluwarsa setiap hari
Schedule::job(new PruneExpiredChallengesJob)->daily();

// Pencadangan otomatis basis data ke Azure Blob Storage (SRS NF-08: siklus 24 jam dengan retensi 30 hari)
// Dijadwalkan pukul 02:00 WITA (UTC+8) saat beban sistem terendah
Schedule::job(new BackupDatabaseToAzureJob)->dailyAt('02:00')->timezone('Asia/Makassar');

Artisan::command('files:scan-pending', function () {
    $count = 0;
    FileEntry::whereIn('virus_scan_status', ['pending', 'error'])->chunkById(100, function ($files) use (&$count) {
        foreach ($files as $file) {
            ScanFile::dispatch($file->id);
            $count++;
        }
    });
    $this->info("{$count} berkas masuk antrean pemindaian.");
})->purpose('Antrekan ulang pemindaian berkas yang belum terverifikasi');
