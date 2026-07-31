<?php

namespace App\Enums;

enum BotState: string
{
    case IDLE = 'idle';
    case WAITING_SERVICE_DATE = 'waiting_service_date';
    case WAITING_SERVICE_TIME = 'waiting_service_time';
    case SELECTING_MINISTRY = 'selecting_ministry';
    case CONFIRM_POSTER = 'confirm_poster';
}