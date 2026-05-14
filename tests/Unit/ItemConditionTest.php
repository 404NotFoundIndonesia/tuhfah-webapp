<?php

namespace Tests\Unit;

use App\Enum\ItemCondition;
use PHPUnit\Framework\TestCase;

class ItemConditionTest extends TestCase
{
    public function test_has_good_case(): void
    {
        $this->assertSame('good', ItemCondition::GOOD->value);
    }

    public function test_has_damaged_case(): void
    {
        $this->assertSame('damaged', ItemCondition::DAMAGED->value);
    }

    public function test_has_lost_case(): void
    {
        $this->assertSame('lost', ItemCondition::LOST->value);
    }

    public function test_has_exactly_three_cases(): void
    {
        $this->assertCount(3, ItemCondition::cases());
    }
}
