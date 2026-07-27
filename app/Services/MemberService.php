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

    public function update(Member $member, array $data): Member
    {
        DB::beginTransaction();

        try {
            $member->update([
                'name' => $data['name'],
                'gender' => $data['gender'],
                'phone_number' => $data['phone_number'] ?? null,
                'is_active' => $data['is_active'],
            ]);

            DB::commit();

            Log::info('Member updated successfully.', [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'edited_by' => auth()->id(),
            ]);

            return $member;
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to update member.', [
                'member_id' => $member->id,
                'edited_by' => auth()->id(),
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            throw $exception;
        }
    }

    public function updateStatus(Member $member, bool $isActive): Member
    {
        DB::beginTransaction();

        try {
            $member->update([
                'is_active' => $isActive,
            ]);

            DB::commit();

            Log::info('Member status updated successfully.', [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'status' => $isActive ? 'active' : 'inactive',
                'edited_by' => auth()->id(),
            ]);

            return $member;

        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to update member status.', [
                'member_id' => $member->id,
                'target_status' => $isActive ? 'active' : 'inactive',
                'edited_by' => auth()->id(),
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            throw $exception;
        }
    }
}