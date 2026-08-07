<?php

namespace App\Jobs;

use App\Enums\ScheduleStatus;
use App\Models\Schedule;
use App\Services\ScheduleService;
use App\Services\WhatsApp\WahaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class PublishPosterToGroupJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    public function __construct(
        public int $scheduleId,
        public string $chatId,
    ) {
    }

    public function backoff(): array
    {
        return [5, 15];
    }

    public function handle(
        ScheduleService $scheduleService,
        WahaService $wahaService,
    ): void {
        $schedule = Schedule::findOrFail($this->scheduleId);

        if ($schedule->status !== ScheduleStatus::GENERATED) {
            throw new RuntimeException(
                "Schedule {$schedule->id} is not ready for publishing."
            );
        }

        if (blank($schedule->poster_path)) {
            throw new RuntimeException(
                "Schedule {$schedule->id} does not have a generated poster."
            );
        }

        if (! Storage::disk('public')->exists($schedule->poster_path)) {
            throw new RuntimeException(
                "Poster file does not exist."
            );
        }

        $imageUrl = $scheduleService->posterUrl(
            $schedule
        );

        $wahaService->sendImageToGroup(
            $imageUrl,
            basename($schedule->poster_path),
            '📋 Jadwal Ibadah Minggu Ini'
        );

        $scheduleService->markAsPublished(
            $schedule
        );

        Log::info('Poster published successfully.', [
            'schedule_id' => $schedule->id,
        ]);

        $wahaService->sendText(
            $this->chatId,
            "✅ Poster has been published successfully to the church group."
        );
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to publish poster after maximum retry attempts.', [
            'schedule_id' => $this->scheduleId,
            'chat_id' => $this->chatId,
            'attempts' => $this->tries,
            'exception' => $exception?->getMessage(),
        ]);

        try {
            app(WahaService::class)->sendText(
                $this->chatId,
                "❌ Failed to publish the poster to the church group after {$this->tries} attempts.\n\n"
                ."The schedule remains in Generated status.\n"
                ."Please try again later."
            );
        } catch (Throwable $sendException) {
            Log::error('Failed to notify administrator about publish failure.', [
                'chat_id' => $this->chatId,
                'exception' => $sendException->getMessage(),
            ]);
        }
    }
}