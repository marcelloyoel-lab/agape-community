<?php

namespace App\Services\WhatsApp;

use App\Enums\BotState;
use App\Models\BotSession;
use App\Models\Member;
use App\Models\Ministry;
use Illuminate\Support\Facades\Log;
use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Services\ScheduleService;

class BotConversationService
{
    public function __construct(
        private readonly WahaService $waha,
        private readonly BotSessionService $botSessions,
        private readonly ScheduleService $scheduleService,
    ) {
    }

    public function handle(
        BotSession $session,
        User $user,
        string $senderId,
        string $message
    ): void {
        $message = trim($message);
        $command = strtolower($message);

        if ($command === '!cancel') {
            $this->cancelConversation(
                $session,
                $senderId
            );

            return;
        }

        if ($command === '!poster') {
            $this->startPoster(
                $session,
                $senderId
            );

            return;
        }

        if ($session->state === BotState::IDLE) {
            $this->waha->sendText(
                $senderId,
                'Unknown command. Send !poster to create a schedule.'
            );

            return;
        }

        if ($session->state === BotState::WAITING_SERVICE_DATE) {
            $this->handleServiceDate(
                $session,
                $senderId,
                $message
            );

            return;
        }

        if ($session->state === BotState::WAITING_SERVICE_TIME) {
            $this->handleServiceTime(
                $session,
                $senderId,
                $message
            );

            return;
        }

        if ($session->state === BotState::SELECTING_MINISTRY) {
            $this->handleMemberSelection(
                $session,
                $user,
                $senderId,
                $message
            );

            return;
        }
    }

    private function cancelConversation(
        BotSession $session,
        string $senderId
    ): void {
        if ($session->state === BotState::IDLE) {
            $this->waha->sendText(
                $senderId,
                'There is no active conversation to cancel.'
            );

            return;
        }

        $this->botSessions->reset($session);

        Log::info('Bot conversation cancelled.', [
            'bot_session_id' => $session->id,
        ]);

        $this->waha->sendText(
            $senderId,
            'Schedule creation cancelled.'
        );
    }

    private function startPoster(
        BotSession $session,
        string $senderId
    ): void {
        $this->botSessions->update($session, [
            'state' => BotState::WAITING_SERVICE_DATE,
            'schedule_id' => null,
            'current_ministry_id' => null,
            'temp_data' => [],
        ]);

        Log::info('Poster conversation started.', [
            'bot_session_id' => $session->id,
        ]);

        $this->waha->sendText(
            $senderId,
            "Creating a new schedule.\n\n"
            ."Enter the service date in DD/MM/YYYY format.\n"
            ."Example: 02/08/2026"
        );
    }

    private function sendMemberSelection(
        string $senderId,
        Ministry $ministry
    ): void {
        $members = Member::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($members->isEmpty()) {
            $this->waha->sendText(
                $senderId,
                "No active members are available for {$ministry->name}."
            );

            return;
        }

        $options = $members
            ->values()
            ->map(
                fn (Member $member, int $index) =>
                    ($index + 1).'. '.$member->name
            )
            ->implode("\n");

        $instruction = $ministry->allow_multiple_members
            ? 'Reply with one or multiple numbers, e.g. 1,3,4.'
            : 'Reply with one number, e.g. 2.';

        $this->waha->sendText(
            $senderId,
            "Select member for {$ministry->name}:\n\n"
            .$options
            ."\n\n"
            .$instruction
        );
    }

    private function handleMemberSelection(
        BotSession $session,
        User $user,
        string $senderId,
        string $message
    ): void {
        $ministry = $session->currentMinistry;

        if (! $ministry || ! $ministry->is_active) {
            $this->waha->sendText(
                $senderId,
                'The current ministry is no longer available. Send !poster to restart.'
            );

            return;
        }

        $members = Member::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->values();

        if ($members->isEmpty()) {
            $this->waha->sendText(
                $senderId,
                'No active members are available.'
            );

            return;
        }

        $selections = $this->parseSelection($message);

        if ($selections === null) {
            $this->sendInvalidSelection(
                $senderId,
                $ministry
            );

            return;
        }

        if (! $ministry->allow_multiple_members && count($selections) !== 1) {
            $this->waha->sendText(
                $senderId,
                "{$ministry->name} only allows one member."
            );

            return;
        }

        $maxOption = $members->count();

        foreach ($selections as $selection) {
            if ($selection < 1 || $selection > $maxOption) {
                $this->waha->sendText(
                    $senderId,
                    "Invalid selection. Choose a number between 1 and {$maxOption}."
                );

                return;
            }
        }

        $memberIds = collect($selections)
            ->map(
                fn (int $selection) =>
                    $members->get($selection - 1)->id
            )
            ->values()
            ->all();

        $this->storeSelectionAndAdvance(
            $session,
            $user,
            $senderId,
            $ministry,
            $memberIds
        );
    }

    private function parseSelection(string $message): ?array
    {
        $parts = array_map(
            'trim',
            explode(',', $message)
        );

        if ($parts === []) {
            return null;
        }

        $selections = [];

        foreach ($parts as $part) {
            if ($part === '' || ! ctype_digit($part)) {
                return null;
            }

            $selection = (int) $part;

            if ($selection < 1) {
                return null;
            }

            $selections[] = $selection;
        }

        return array_values(array_unique($selections));
    }

    private function sendInvalidSelection(
        string $senderId,
        Ministry $ministry
    ): void {
        $message = $ministry->allow_multiple_members
            ? 'Invalid selection. Reply with numbers like 1 or 1,3,4.'
            : 'Invalid selection. Reply with one number, for example 2.';

        $this->waha->sendText(
            $senderId,
            $message
        );
    }

    private function storeSelectionAndAdvance(
        BotSession $session,
        User $user,
        string $senderId,
        Ministry $ministry,
        array $memberIds
    ): void {
        $nextMinistry = Ministry::query()
            ->where('is_active', true)
            ->where(function ($query) use ($ministry) {
                $query
                    ->where('display_order', '>', $ministry->display_order)
                    ->orWhere(function ($query) use ($ministry) {
                        $query
                            ->where('display_order', $ministry->display_order)
                            ->where('id', '>', $ministry->id);
                    });
            })
            ->orderBy('display_order')
            ->orderBy('id')
            ->first();

        $tempData = $session->temp_data ?? [];

        $tempData['assignments'] ??= [];
        $tempData['assignments'][$ministry->id] = $memberIds;

        $this->botSessions->update($session, [
            'temp_data' => $tempData,
            'current_ministry_id' => $nextMinistry?->id,
        ]);

        Log::info('Bot ministry selection stored.', [
            'bot_session_id' => $session->id,
            'ministry_id' => $ministry->id,
            'member_ids' => $memberIds,
        ]);

        if ($nextMinistry) {
            $this->sendMemberSelection(
                $senderId,
                $nextMinistry
            );

            return;
        }

        $this->completeSchedule(
            $session,
            $user,
            $senderId
        );
    }

    private function handleServiceDate(
        BotSession $session,
        string $senderId,
        string $message
    ): void {
        try {
            $date = Carbon::createFromFormat('!d/m/Y', $message);
        } catch (Throwable) {
            $this->waha->sendText(
                $senderId,
                'Invalid date. Use DD/MM/YYYY, for example 02/08/2026.'
            );

            return;
        }

        if ($date->isBefore(today())) {
            $this->waha->sendText(
                $senderId,
                'Service date cannot be in the past.'
            );

            return;
        }

        $tempData = $session->temp_data ?? [];

        $tempData['service_date'] = $date->format('Y-m-d');

        $this->botSessions->update($session, [
            'state' => BotState::WAITING_SERVICE_TIME,
            'temp_data' => $tempData,
        ]);

        $this->waha->sendText(
            $senderId,
            "Enter the service time in HH:MM format.\nExample: 17:00"
        );
    }

    private function handleServiceTime(
        BotSession $session,
        string $senderId,
        string $message
    ): void {
        try {
            $time = Carbon::createFromFormat('!H:i', $message);
        } catch (Throwable) {
            $this->waha->sendText(
                $senderId,
                'Invalid time. Use HH:MM, for example 17:00.'
            );

            return;
        }

        $firstMinistry = Ministry::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('id')
            ->first();

        if (! $firstMinistry) {
            $this->waha->sendText(
                $senderId,
                'No active ministries are available.'
            );

            return;
        }

        $tempData = $session->temp_data ?? [];

        $tempData['service_time'] = $time->format('H:i');

        $this->botSessions->update($session, [
            'state' => BotState::SELECTING_MINISTRY,
            'current_ministry_id' => $firstMinistry->id,
            'temp_data' => $tempData,
        ]);

        $this->sendMemberSelection(
            $senderId,
            $firstMinistry
        );
    }

    private function completeSchedule(
        BotSession $session,
        User $user,
        string $senderId
    ): void {
        $data = $session->temp_data ?? [];

        if (
            empty($data['service_date']) ||
            empty($data['service_time']) ||
            empty($data['assignments'])
        ) {
            Log::error('Bot schedule completion failed due to incomplete session data.', [
                'bot_session_id' => $session->id,
                'user_id' => $user->id,
            ]);

            $this->waha->sendText(
                $senderId,
                'Schedule data is incomplete. Send !poster to restart.'
            );

            return;
        }

        try {
            $schedule = $this->scheduleService->create(
                $data,
                $user->id,
                $session
            );

            Log::info('Bot schedule completed successfully.', [
                'bot_session_id' => $session->id,
                'schedule_id' => $schedule->id,
                'created_by' => $user->id,
            ]);

            $this->waha->sendText(
                $senderId,
                "Schedule created successfully.\n"
                ."Schedule ID: {$schedule->id}"
            );
        } catch (Throwable $exception) {
            Log::error('Bot failed to complete schedule.', [
                'bot_session_id' => $session->id,
                'user_id' => $user->id,
                'exception' => $exception->getMessage(),
            ]);

            $this->waha->sendText(
                $senderId,
                'Failed to create the schedule. Please try again.'
            );
        }
    }
}