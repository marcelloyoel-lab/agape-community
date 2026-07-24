<?php

namespace App\Enums;

enum ScheduleStatus: string
{
    case DRAFT = 'draft';

    case APPROVED = 'approved';

    case PUBLISHED = 'published';
}
