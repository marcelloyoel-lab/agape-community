<?php

namespace App\Models;

use App\Enums\ScheduleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Schedule extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'status' => ScheduleStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    public function botSession(): HasOne
    {
        return $this->hasOne(BotSession::class);
    }
}