<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ministry extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'allow_multiple_members' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scheduleAssignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    public function botSessions(): HasMany
    {
        return $this->hasMany(BotSession::class, 'current_ministry_id');
    }
}