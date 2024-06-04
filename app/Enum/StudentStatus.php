<?php

namespace App\Enum;

enum StudentStatus: string
{
    case CANDIDATE = 'candidate';
    case ACTIVE = 'active';
    case GRADUATED = 'graduated';
    case EXPELLED = 'expelled';
    case ON_LEAVE = 'on_leave';
    case QUIT = 'quit';

    public static function is(string $value, StudentStatus $status): bool
    {
        return self::tryFrom($value) === $status;
    }

    public static function isIncluded(?string $value, StudentStatus ...$status): bool
    {
        return in_array(self::tryFrom($value), $status);
    }
}
