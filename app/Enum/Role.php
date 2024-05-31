<?php

namespace App\Enum;

enum Role: string
{
    case OWNER = 'owner';
    case HEADMASTER = 'headmaster';
    case ADMINISTRATOR = 'administrator';
    case TEACHER = 'teacher';
    case STUDENT_GUARDIAN = 'student_guardian';
}
