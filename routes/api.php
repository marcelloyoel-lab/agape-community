<?php

use App\Http\Controllers\Webhooks\WhatsAppWebhookController;
use App\Http\Middleware\VerifyWahaWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/webhooks/whatsapp', WhatsAppWebhookController::class)
    ->middleware(VerifyWahaWebhook::class)
    ->name('webhooks.whatsapp');