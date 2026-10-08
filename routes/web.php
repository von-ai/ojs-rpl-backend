<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthorDashboardController;
use App\Http\Controllers\PublicJournalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicJournalController::class, 'index'])->name('journal.index');
Route::get('/dashboard', [AuthorDashboardController::class, 'index'])->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
