<?php

namespace Tests\Unit;

use App\Enum\AttendanceStatus;
use PHPUnit\Framework\TestCase;

class AttendanceStatusTest extends TestCase
{
    public function test_enum_has_four_cases(): void
    {
        $this->assertCount(4, AttendanceStatus::cases());
    }

    public function test_present_value(): void
    {
        $this->assertSame('present', AttendanceStatus::PRESENT->value);
    }

    public function test_absent_value(): void
    {
        $this->assertSame('absent', AttendanceStatus::ABSENT->value);
    }

    public function test_sick_value(): void
    {
        $this->assertSame('sick', AttendanceStatus::SICK->value);
    }

    public function test_permitted_value(): void
    {
        $this->assertSame('permitted', AttendanceStatus::PERMITTED->value);
    }

    public function test_try_from_valid_value(): void
    {
        $this->assertSame(AttendanceStatus::PRESENT, AttendanceStatus::tryFrom('present'));
        $this->assertSame(AttendanceStatus::ABSENT, AttendanceStatus::tryFrom('absent'));
        $this->assertSame(AttendanceStatus::SICK, AttendanceStatus::tryFrom('sick'));
        $this->assertSame(AttendanceStatus::PERMITTED, AttendanceStatus::tryFrom('permitted'));
    }

    public function test_try_from_invalid_value_returns_null(): void
    {
        $this->assertNull(AttendanceStatus::tryFrom('late'));
        $this->assertNull(AttendanceStatus::tryFrom(''));
    }
}
