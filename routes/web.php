<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Author\AuthorSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Autentikasi Pengguna (Login & Logout)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('throttle:identity')->group(function () {
    Route::get('/register', [RegistrationController::class, 'form'])->name('register');
    Route::post('/register', [RegistrationController::class, 'register'])->name('register.store');
    Route::get('/register/check-email', [RegistrationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify', [RegistrationController::class, 'verificationForm'])->name('verification.form');
    Route::post('/email/verify', [RegistrationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [RegistrationController::class, 'resend'])->name('verification.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Portal Penulis (Author Submissions & Management) - Wajib Autentikasi
Route::middleware('auth')->prefix('author')->name('author.')->group(function () {
    Route::get('/submissions', [AuthorSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/create', [AuthorSubmissionController::class, 'create'])->name('submissions.create');
    Route::get('/submissions/sections', [AuthorSubmissionController::class, 'sectionsApi'])->name('submissions.sections');
    Route::post('/submissions', [AuthorSubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/submissions/export-csv', [AuthorSubmissionController::class, 'exportCsv'])->name('submissions.export');
    Route::get('/submissions/{id}', [AuthorSubmissionController::class, 'show'])->name('submissions.show');
});

Route::redirect('/submissions', '/author/submissions');
Route::get('/dashboard', function () {
    return redirect()->route('author.submissions.index');
})->middleware('auth')->name('dashboard');
