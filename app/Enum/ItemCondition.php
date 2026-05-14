<?php

namespace App\Enum;

enum ItemCondition: string
{
    case GOOD = 'good';
    case DAMAGED = 'damaged';
    case LOST = 'lost';
}
