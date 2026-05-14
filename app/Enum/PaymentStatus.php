<?php

namespace App\Enum;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
}
