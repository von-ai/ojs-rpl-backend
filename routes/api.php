<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DefenderWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rute API tanpa CSRF stateful untuk integrasi webhook cloud seperti
| Microsoft Defender for Storage / Azure Event Grid.
|
*/

Route::post('/webhooks/azure-defender', [DefenderWebhookController::class, 'handle'])
    ->name('webhooks.azure-defender');
