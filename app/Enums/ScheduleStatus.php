<?php

namespace App\Enums;

enum ScheduleStatus: string
{
    case DRAFT = 'draft';
    case APPROVED = 'approved';
    case PUBLISHED = 'published';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::APPROVED => 'Approved',
            self::PUBLISHED => 'Published',
        };
    }
}
