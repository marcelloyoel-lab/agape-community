<?php

namespace App\Services;

use App\Enums\BotState;
use App\Enums\ScheduleStatus;
use App\Models\BotSession;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;
use Illuminate\Support\Facades\Storage;

class ScheduleService
{
    /**
     * @throws Throwable
     */
    public function create(
        array $data,
        int $createdBy,
        ?BotSession $botSession = null
    ): Schedule {
        DB::beginTransaction();

        try {
            $schedule = Schedule::create([
                'service_date' => $data['service_date'],
                'service_time' => $data['service_time'],
                'status' => ScheduleStatus::DRAFT,
                'created_by' => $createdBy,
            ]);

            foreach ($data['assignments'] as $ministryId => $memberIds) {
                foreach ($memberIds as $displayOrder => $memberId) {
                    $schedule->assignments()->create([
                        'ministry_id' => $ministryId,
                        'member_id' => $memberId,
                        'display_order' => $displayOrder + 1,
                    ]);
                }
            }

            if ($botSession) {
                $botSession->update([
                    // 'state' => BotState::IDLE,
                    'schedule_id' => $schedule->id,
                    // 'current_ministry_id' => null,
                    // 'temp_data' => null,
                    'last_activity_at' => now(),
                ]);
            }

            DB::commit();

            Log::info('Schedule created successfully.', [
                'schedule_id' => $schedule->id,
                'service_date' => $schedule->service_date,
                'service_time' => $schedule->service_time,
                'created_by' => $createdBy,
                'bot_session_id' => $botSession?->id,
            ]);

            return $schedule;
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to create schedule.', [
                'service_date' => $data['service_date'] ?? null,
                'service_time' => $data['service_time'] ?? null,
                'created_by' => $createdBy,
                'bot_session_id' => $botSession?->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function update(
        Schedule $schedule,
        array $data,
        int $editedBy
    ): Schedule {
        DB::beginTransaction();

        try {
            $schedule->update([
                'service_date' => $data['service_date'],
                'service_time' => $data['service_time'],

                'status' => $schedule->status === ScheduleStatus::REJECTED
                    ? ScheduleStatus::DRAFT
                    : $schedule->status,
            ]);

            $schedule->assignments()->delete();

            foreach ($data['assignments'] as $ministryId => $memberIds) {
                foreach ($memberIds as $displayOrder => $memberId) {
                    $schedule->assignments()->create([
                        'ministry_id' => $ministryId,
                        'member_id' => $memberId,
                        'display_order' => $displayOrder + 1,
                    ]);
                }
            }

            DB::commit();

            Log::info('Schedule updated successfully.', [
                'schedule_id' => $schedule->id,
                'service_date' => $schedule->service_date,
                'service_time' => $schedule->service_time,
                'edited_by' => $editedBy,
            ]);

            return $schedule->refresh();

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update schedule.', [
                'schedule_id' => $schedule->id,
                'edited_by' => $editedBy,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function preparePosterData(Schedule $schedule): array
    {
        $schedule->load([
            'assignments' => fn ($query) => $query->orderBy('display_order'),
            'assignments.member',
            'assignments.ministry',
        ]);

        $assignments = $schedule->assignments->groupBy('ministry.name');

        return [
            'mc' => $assignments->get('MC', collect()),
            'firman' => $assignments->get('Pelayan Firman', collect()),
            'music' => $assignments->get('Music', collect()),
            'multimedia' => $assignments->get('Multimedia', collect()),
        ];
    }

    /**
     * @throws Throwable
     */
    public function generatePoster(Schedule $schedule, int $generatedBy): Schedule
    {
        $posterData = $this->preparePosterData($schedule);

        $filename = sprintf(
            'schedule-%d-%s.jpg',
            $schedule->id,
            Str::uuid()
        );

        $posterDirectory = storage_path('app/public/posters');
        $temporaryDirectory = storage_path('app/poster-temp');

        $posterPath = $posterDirectory.'/'.$filename;
        $temporaryHtmlPath = $temporaryDirectory.'/'.$filename.'.html';

        try {
            File::ensureDirectoryExists($posterDirectory);
            File::ensureDirectoryExists($temporaryDirectory);

            $html = view('poster.template', [
                'schedule' => $schedule,
                'posterData' => $posterData,
            ])->render();

            File::put($temporaryHtmlPath, $html);

            $result = Process::timeout(60)
                ->path(base_path())
                ->run([
                    'node',
                    'scripts/generate-poster.mjs',
                    $temporaryHtmlPath,
                    $posterPath,
                ]);

            if ($result->failed()) {
                throw new RuntimeException(
                    'Playwright failed to generate poster: '.$result->errorOutput()
                );
            }

            if (! File::exists($posterPath)) {
                throw new RuntimeException(
                    'Poster generation completed but the PNG file was not created.'
                );
            }

            DB::beginTransaction();

            try {
                $oldPosterPath = $schedule->poster_path;

                $schedule->update([
                    'poster_path' => 'posters/'.$filename,
                ]);

                DB::commit();

            } catch (Throwable $exception) {
                DB::rollBack();

                throw $exception;
            }

            if (
                $oldPosterPath &&
                $oldPosterPath !== $schedule->poster_path
            ) {
                $oldAbsolutePath = storage_path(
                    'app/public/'.$oldPosterPath
                );

                if (File::exists($oldAbsolutePath)) {
                    File::delete($oldAbsolutePath);
                }
            }

            Log::info('Schedule poster generated successfully.', [
                'schedule_id' => $schedule->id,
                'poster_path' => $schedule->poster_path,
                'generated_by' => $generatedBy,
            ]);

            return $schedule->refresh();

        } catch (Throwable $exception) {
            /*
            * If generation/update fails, don't leave the newly generated
            * poster behind.
            */
            if (File::exists($posterPath)) {
                File::delete($posterPath);
            }

            Log::error('Failed to generate schedule poster.', [
                'schedule_id' => $schedule->id,
                'generated_by' => $generatedBy,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;

        } finally {
            if (File::exists($temporaryHtmlPath)) {
                File::delete($temporaryHtmlPath);
            }
        }
    }

    public function approve(Schedule $schedule): Schedule
    {
        DB::beginTransaction();

        try {
            $schedule->update([
                'status' => ScheduleStatus::APPROVED,
                'approved_at' => now(),
            ]);

            DB::commit();

            Log::info('Schedule approved successfully.', [
                'schedule_id' => $schedule->id,
            ]);

            return $schedule->refresh();
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to approve schedule.', [
                'schedule_id' => $schedule->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function posterUrl(Schedule $schedule): string
    {
        return rtrim(config('services.waha.public_url'), '/')
            . Storage::url($schedule->poster_path);
    }
}