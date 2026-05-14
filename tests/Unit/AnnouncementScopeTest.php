<?php

namespace Tests\Unit;

use App\Enum\AnnouncementScope;
use Tests\TestCase;

class AnnouncementScopeTest extends TestCase
{
    public function test_has_public_case(): void
    {
        $this->assertSame('public', AnnouncementScope::PUBLIC->value);
    }

    public function test_has_internal_case(): void
    {
        $this->assertSame('internal', AnnouncementScope::INTERNAL->value);
    }

    public function test_cases_count(): void
    {
        $this->assertCount(2, AnnouncementScope::cases());
    }

    public function test_from_string(): void
    {
        $this->assertSame(AnnouncementScope::PUBLIC, AnnouncementScope::from('public'));
        $this->assertSame(AnnouncementScope::INTERNAL, AnnouncementScope::from('internal'));
    }
}
