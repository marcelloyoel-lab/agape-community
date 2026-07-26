<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberService
{
    public function getMembers(): Collection
    {
        return Member::select('id', 'name', 'is_active')
        ->latest()
        ->get();
    }

    public function create(array $data): Member
    {
        DB::beginTransaction();

        try {

            $member = Member::create([
                'name' => $data['name'],
                'gender' => $data['gender'],
                'phone_number' => $data['phone_number'] ?? null,
                'is_active' => $data['is_active'],
            ]);

            DB::commit();

            Log::info('Member created successfully.', [
                'member_id' => $member->id,
                'member_name' => $member->name,
            ]);

            return $member;

        } catch (\Throwable $exception) {

            DB::rollBack();

            Log::error('Failed to create member.', [
                'payload' => $data,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            throw $exception;
        }
    }
}