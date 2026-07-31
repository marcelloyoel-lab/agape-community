<?php

namespace App\Enums;

enum BotState: string
{
    case IDLE = 'idle';
    case SELECTING_MINISTRY = 'selecting_ministry';
    case CONFIRM_POSTER = 'confirm_poster';
}