<?php

use App\Http\Controllers\Api\Auth\ApiAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [ApiAuthController::class, 'login'])->name('api.auth.login');
    });
});
