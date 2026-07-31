<?php

namespace App\Models;

use App\Enums\BotState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotSession extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'state' => BotState::class,
            'temp_data' => 'array',
            'last_activity_at' => 'datetime',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function currentMinistry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class, 'current_ministry_id');
    }
}