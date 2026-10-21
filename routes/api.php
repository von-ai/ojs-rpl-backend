<?php

use App\Http\Controllers\Api\Auth\ApiAuthController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Middleware\IdentityApi;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [ApiAuthController::class, 'login'])->name('api.auth.login');

        Route::middleware([IdentityApi::class, 'throttle:identity'])->group(function () {
            Route::post('register', [RegistrationController::class, 'register']);
            Route::post('email/verify', [RegistrationController::class, 'verify']);
            Route::post('email/resend', [RegistrationController::class, 'resend']);
        });
    });
});
