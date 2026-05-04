<?php

use App\Http\Controllers\Api\WhatsappWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Webhook endpoint for MegaMsg:
|   POST /api/whatsapp/webhook/{instance_id}
|
| Configure this URL in your MegaMsg instance settings dashboard.
| Replace {instance_id} with your actual Instance ID.
|
*/

Route::post('/whatsapp/webhook/{instanceId}', [WhatsappWebhookController::class, 'receive'])
    ->name('api.whatsapp.webhook');
