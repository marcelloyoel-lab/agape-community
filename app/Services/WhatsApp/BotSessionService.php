<?php

namespace App\Services\WhatsApp;

use App\Enums\BotState;
use App\Models\BotSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BotSessionService
{
    private const SESSION_TTL_MINUTES = 20;

    public function getOrCreate(string $senderId): BotSession
    {
        $session = BotSession::query()
            ->where('phone_number', $senderId)
            ->first();

        if ($session) {
            return $session;
        }

        DB::beginTransaction();

        try {
            $session = BotSession::create([
                'phone_number' => $senderId,
                'state' => BotState::IDLE,
                'last_activity_at' => now(),
            ]);

            DB::commit();

            Log::info('Bot session created.', [
                'bot_session_id' => $session->id,
            ]);

            return $session;
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to create bot session.', [
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function isExpired(BotSession $session): bool
    {
        if (! $session->last_activity_at) {
            return true;
        }

        return $session->last_activity_at
            ->addMinutes(self::SESSION_TTL_MINUTES)
            ->isPast();
    }

    public function touch(BotSession $session): BotSession
    {
        $session->update([
            'last_activity_at' => now(),
        ]);

        return $session->refresh();
    }

    public function reset(BotSession $session): BotSession
    {
        DB::beginTransaction();

        try {
            $session->update([
                'state' => BotState::IDLE,
                'schedule_id' => null,
                'current_ministry_id' => null,
                'temp_data' => null,
                'last_activity_at' => now(),
            ]);

            DB::commit();

            Log::info('Bot session reset.', [
                'bot_session_id' => $session->id,
            ]);

            return $session->refresh();
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to reset bot session.', [
                'bot_session_id' => $session->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function find(string $senderId): ?BotSession
    {
        return BotSession::query()
            ->where('phone_number', $senderId)
            ->first();
    }
}