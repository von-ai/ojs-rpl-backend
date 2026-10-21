<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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

    Route::get('/dashboard', function () {
        return response('Dashboard Portal Penulis BRIDA Makassar', 200);
    })->name('dashboard');
});
