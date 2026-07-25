<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;

class MemberService
{
    public function getMembers(): Collection
    {
        return Member::select('id', 'name', 'is_active')
        ->latest()
        ->get();
    }
}