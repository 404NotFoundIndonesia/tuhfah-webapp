<?php

namespace Tests\Unit;

use App\Enum\AnnouncementScope;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_scope_returns_only_published(): void
    {
        Announcement::factory()->published()->create();
        Announcement::factory()->draft()->create();

        $results = Announcement::published()->get();

        $this->assertCount(1, $results);
        $this->assertNotNull($results->first()->published_at);
    }

    public function test_published_scope_excludes_future_published_at(): void
    {
        Announcement::factory()->create(['published_at' => now()->addDay()]);
        Announcement::factory()->published()->create();

        $results = Announcement::published()->get();

        $this->assertCount(1, $results);
    }

    public function test_published_scope_excludes_null_published_at(): void
    {
        Announcement::factory()->draft()->count(3)->create();

        $this->assertCount(0, Announcement::published()->get());
    }

    public function test_author_relation(): void
    {
        $author = User::factory()->create();
        $announcement = Announcement::factory()->create(['author_id' => $author->id]);

        $this->assertInstanceOf(User::class, $announcement->author);
        $this->assertEquals($author->id, $announcement->author->id);
    }

    public function test_scope_cast_to_enum(): void
    {
        $announcement = Announcement::factory()->public()->create();

        $this->assertInstanceOf(AnnouncementScope::class, $announcement->scope);
        $this->assertSame(AnnouncementScope::PUBLIC, $announcement->scope);
    }

    public function test_internal_scope_cast(): void
    {
        $announcement = Announcement::factory()->internal()->create();

        $this->assertSame(AnnouncementScope::INTERNAL, $announcement->scope);
    }
}
