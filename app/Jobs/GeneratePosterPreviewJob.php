<?php

namespace App\Jobs;

use App\Models\Schedule;
use App\Models\User;
use App\Services\ScheduleService;
use App\Services\WhatsApp\WahaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeneratePosterPreviewJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $scheduleId,
        public string $chatId,
        public int $userId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        ScheduleService $scheduleService,
        WahaService $wahaService,
    ): void {
        try {
            $schedule = Schedule::findOrFail($this->scheduleId);
            Log::info('Poster URL', [
                'url' => $scheduleService->posterUrl($schedule),
            ]); 
            
            $user = User::findOrFail($this->userId);

            $schedule = $scheduleService->generatePoster(
                $schedule,
                $user->id
            );

            $imageUrl = $scheduleService->posterUrl(
                $schedule
            );

            $wahaService->sendImage(
                $this->chatId,
                $imageUrl,
                basename($schedule->poster_path),
                '📋 Poster Preview'
            );

            $wahaService->sendText(
                $this->chatId,
                "Please review the poster.\n\n"
                ."Reply with:\n"
                ."1. Send\n"
                ."2. Edit\n"
                ."3. Cancel"
            );

            Log::info('Poster preview generated successfully.', [
                'schedule_id' => $schedule->id,
                'user_id' => $user->id,
                'chat_id' => $this->chatId,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to generate poster preview.', [
                'schedule_id' => $this->scheduleId,
                'user_id' => $this->userId,
                'chat_id' => $this->chatId,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
