<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Log::info('WhatsApp webhook received.', [
            'event' => $request->input('event'),
            'session' => $request->input('session'),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}