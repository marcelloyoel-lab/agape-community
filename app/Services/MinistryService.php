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
}