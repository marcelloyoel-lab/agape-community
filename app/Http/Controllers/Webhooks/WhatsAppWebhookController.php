<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\BotAuthenticationService;
use App\Services\WhatsApp\WahaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly BotAuthenticationService $botAuth,
        private readonly WahaService $waha,
    ) {
    }
    
    public function __invoke(Request $request): JsonResponse
    {
        Log::info('WhatsApp webhook received.', [
            'event' => $request->input('event'),
            'session' => $request->input('session')
        ]);

        $event = $request->input('event');
        $payload = $request->input('payload', []);

        if ($event !== 'message') {
            return response()->json(['status' => 'ignored']);
        }

        if (($payload['fromMe'] ?? false) === true) {
            return response()->json(['status' => 'ignored']);
        }

        $senderId = $payload['from'] ?? null;
        $message = trim($payload['body'] ?? '');

        if (! $senderId || $message === '') {
            return response()->json(['status' => 'ignored']);
        }

        if ($this->botAuth->isAuthenticated($senderId)) {
            Log::info('Authenticated WhatsApp message received.');

            return response()->json(['status' => 'authenticated']);
        }

        if ($this->botAuth->authenticate($senderId, $message)) {
            $this->waha->sendText(
                $senderId,
                'Authentication successful. You can now use the bot.'
            );

            Log::info('WhatsApp administrator authenticated.');

            return response()->json(['status' => 'authenticated']);
        }

        $this->waha->sendText(
            $senderId,
            'Authentication required. Send your credentials as email|password.'
        );

        return response()->json(['status' => 'unauthorized']);
    }
}