<?php

use App\Http\Controllers\Auth\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('throttle:identity')->group(function () {
    Route::get('/register', [RegistrationController::class, 'form'])->name('register');
    Route::post('/register', [RegistrationController::class, 'register'])->name('register.store');
    Route::get('/register/check-email', [RegistrationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify', [RegistrationController::class, 'verificationForm'])->name('verification.form');
    Route::post('/email/verify', [RegistrationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [RegistrationController::class, 'resend'])->name('verification.resend');
});
