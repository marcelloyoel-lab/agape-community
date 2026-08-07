<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\BotAuthenticationService;
use App\Services\WhatsApp\BotSessionService;
use App\Enums\BotState;
use App\Services\WhatsApp\WahaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsApp\WebhookDeduplicationService;
use App\Services\WhatsApp\BotConversationService;
use App\Models\User;
use Throwable;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly BotAuthenticationService $botAuth,
        private readonly WahaService $waha,
        private readonly WebhookDeduplicationService $deduplication,
        private readonly BotSessionService $botSessions,
        private readonly BotConversationService $conversation,
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

        $messageId = $payload['id'] ?? null;

        if (! $messageId) {
            return response()->json(['status' => 'ignored']);
        }

        if ($this->deduplication->alreadyProcessed($messageId)) {
            return response()->json(['status' => 'duplicate']);
        }

        $senderId = $payload['from'] ?? null;
        $message = trim($payload['body'] ?? '');

        if (! $senderId || $message === '') {
            return response()->json(['status' => 'ignored']);
        }

        if ($this->waha->isGroupChat($senderId)) {
                Log::info('Ignoring WhatsApp group message.', [
                    'chat_id' => $senderId,
                    'message_id' => $messageId,
                ]);

                return response()->json([
                    'status' => 'ignored_group',
                ]);
            }

        if ($this->botAuth->isAuthenticated($senderId)) {
            $user = $this->botAuth->user($senderId);

            if (! $user) {
                Log::warning('Authenticated WhatsApp user could not be resolved.');

                return response()->json([
                    'status' => 'unauthorized',
                ], 401);
            }

            $session = $this->botSessions->getOrCreate($senderId);

            if ($this->botSessions->isExpired($session)) {
                $session = $this->botSessions->reset($session);
            } else {
                $session = $this->botSessions->touch($session);
            }

            Log::info('Authenticated WhatsApp message received.', [
                'bot_session_id' => $session->id,
                'user_id' => $user->id,
                'state' => $session->state->value,
            ]);

            try {
                $this->conversation->handle(
                    $session,
                    $user,
                    $senderId,
                    $message
                );

                return response()->json([
                    'status' => 'processed',
                ]);
            } catch (Throwable $exception) {
                Log::error('WhatsApp conversation processing failed.', [
                    'bot_session_id' => $session->id,
                    'user_id' => $user->id,
                    'state' => $session->state->value,
                    'exception' => $exception->getMessage(),
                ]);

                try {
                    $this->botSessions->reset($session);
                } catch (Throwable $resetException) {
                    Log::error('Failed to reset bot session after conversation failure.', [
                        'bot_session_id' => $session->id,
                        'exception' => $resetException->getMessage(),
                    ]);
                }

                try {
                    $this->waha->sendText(
                        $senderId,
                        "Something went wrong and the current process was cancelled.\n"
                        ."Send !poster to start again."
                    );
                } catch (Throwable $sendException) {
                    Log::error('Failed to send WhatsApp conversation failure message.', [
                        'bot_session_id' => $session->id,
                        'exception' => $sendException->getMessage(),
                    ]);
                }

                return response()->json([
                    'status' => 'failed',
                ]);
            }
        }

        $existingSession = $this->botSessions->find($senderId);

        if ($existingSession && $existingSession->state !== BotState::IDLE) {
            $this->botSessions->reset($existingSession);
        }

        if ($this->botAuth->authenticate($senderId, $message)) {
            $session = $this->botSessions->getOrCreate($senderId);

            // A new authentication always starts a fresh conversation.
            $this->botSessions->reset($session);

            $this->waha->sendText(
                $senderId,
                'Authentication successful. You can now use the bot.'
            );

            Log::info('WhatsApp administrator authenticated.', [
                'bot_session_id' => $session->id,
            ]);

            return response()->json([
                'status' => 'authenticated',
            ]);
        }

        if ($this->botAuth->shouldSendAuthPrompt($senderId)) {
            $this->waha->sendText(
                $senderId,
                'Authentication required. Send your credentials as email|password.'
            );
        }

        return response()->json([
            'status' => 'unauthorized',
        ]);
    }
}