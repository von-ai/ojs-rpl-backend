<?php

use App\Jobs\CheckReviewDeadlinesJob;
use App\Jobs\PruneExpiredChallengesJob;
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

// Pembersihan berkas & token tantangan registrasi kedaluwarsa setiap hari
Schedule::job(new PruneExpiredChallengesJob)->daily();
