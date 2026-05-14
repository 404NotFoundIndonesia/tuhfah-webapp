<?php

namespace Tests\Unit;

use App\Enum\PaymentStatus;
use Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_enum_has_three_cases(): void
    {
        $this->assertCount(3, PaymentStatus::cases());
    }

    public function test_unpaid_value(): void
    {
        $this->assertSame('unpaid', PaymentStatus::UNPAID->value);
    }

    public function test_paid_value(): void
    {
        $this->assertSame('paid', PaymentStatus::PAID->value);
    }

    public function test_overdue_value(): void
    {
        $this->assertSame('overdue', PaymentStatus::OVERDUE->value);
    }

    public function test_try_from_valid(): void
    {
        $this->assertSame(PaymentStatus::PAID, PaymentStatus::tryFrom('paid'));
    }

    public function test_try_from_invalid_returns_null(): void
    {
        $this->assertNull(PaymentStatus::tryFrom('invalid'));
    }
}
