<?php

namespace App\Services;

use App\Enums\ScheduleStatus;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScheduleService
{
    /**
     * @throws Throwable
     */
    public function create(array $data, int $createdBy): Schedule
    {
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

            DB::commit();

            Log::info('Schedule created successfully.', [
                'schedule_id' => $schedule->id,
                'service_date' => $schedule->service_date,
                'service_time' => $schedule->service_time,
                'created_by' => $createdBy,
            ]);

            return $schedule;

        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to create schedule.', [
                'service_date' => $data['service_date'] ?? null,
                'service_time' => $data['service_time'] ?? null,
                'created_by' => $createdBy,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}