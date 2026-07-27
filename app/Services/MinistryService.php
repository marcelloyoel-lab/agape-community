<?php

namespace App\Services;

use App\Models\Ministry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MinistryService
{
    public function create(array $data): Ministry
    {
        DB::beginTransaction();

        try {
            $displayOrder = (Ministry::max('display_order') ?? 0) + 1;

            $ministry = Ministry::create([
                'name' => $data['name'],
                'display_order' => $displayOrder,
                'allow_multiple_members' => $data['allow_multiple_members'],
                'is_active' => $data['is_active'],
            ]);

            DB::commit();

            Log::info('Ministry created successfully.', [
                'ministry_id' => $ministry->id,
                'created_by' => auth()->id(),
            ]);

            return $ministry;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to create ministry.', [
                'created_by' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function update(Ministry $ministry, array $data): Ministry
    {
        DB::beginTransaction();

        try {
            $ministry->update([
                'name' => $data['name'],
                'allow_multiple_members' => $data['allow_multiple_members'],
                'is_active' => $data['is_active'],
            ]);

            DB::commit();

            Log::info('Ministry updated successfully.', [
                'ministry_id' => $ministry->id,
                'edited_by' => auth()->id(),
            ]);

            return $ministry;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update ministry.', [
                'ministry_id' => $ministry->id,
                'edited_by' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function toggleStatus(Ministry $ministry): Ministry
    {
        DB::beginTransaction();

        try {
            $ministry->update([
                'is_active' => ! $ministry->is_active,
            ]);

            DB::commit();

            Log::info('Ministry status updated successfully.', [
                'ministry_id' => $ministry->id,
                'is_active' => $ministry->is_active,
                'edited_by' => auth()->id(),
            ]);

            return $ministry;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update ministry status.', [
                'ministry_id' => $ministry->id,
                'edited_by' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function delete(Ministry $ministry): void
    {
        DB::beginTransaction();

        try {
            if ($ministry->scheduleAssignments()->exists()) {
                throw new \DomainException(
                    'Ministries used in schedules cannot be deleted. Deactivate it instead.'
                );
            }

            $ministryId = $ministry->id;

            $ministry->delete();

            DB::commit();

            Log::info('Ministry deleted successfully.', [
                'ministry_id' => $ministryId,
                'deleted_by' => auth()->id(),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to delete ministry.', [
                'ministry_id' => $ministry->id,
                'deleted_by' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}