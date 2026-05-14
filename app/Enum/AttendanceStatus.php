<?php

namespace App\Enum;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case ABSENT = 'absent';
    case SICK = 'sick';
    case PERMITTED = 'permitted';
}
