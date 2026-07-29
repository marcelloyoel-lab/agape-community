<?php

namespace App\Enums;

enum ScheduleStatus: string
{
    case DRAFT = 'draft';
    case APPROVED = 'approved';
    case PUBLISHED = 'published';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::APPROVED => 'Approved',
            self::PUBLISHED => 'Published',
            self::CANCELLED => 'Cancelled',
            self::REJECTED => 'Rejected',
        };
    }
}
