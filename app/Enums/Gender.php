<?php

namespace App\Enums;

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
        };
    }

    public static function options(): array
    {
        return array_column(
            self::cases(),
            'value',
            'name'
        );
    }
}