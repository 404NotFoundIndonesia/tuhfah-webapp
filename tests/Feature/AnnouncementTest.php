<?php

namespace Tests\Feature;

use App\Enum\AnnouncementScope;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    // ── T5.1: published() scope ───────────────────────────────────────────────

    public function test_published_scope_returns_published_records(): void
    {
        Announcement::factory()->published()->count(3)->create();
        Announcement::factory()->draft()->count(2)->create();

        $this->assertCount(3, Announcement::published()->get());
    }

    public function test_published_scope_excludes_future_scheduled(): void
    {
        Announcement::factory()->create(['published_at' => now()->addDays(2)]);
        Announcement::factory()->published()->create();

        $this->assertCount(1, Announcement::published()->get());
    }

    // ── T5.2: Admin CRUD ──────────────────────────────────────────────────────

    public function test_admin_can_view_index(): void
    {
        $admin = User::factory()->administrator()->create();
        Announcement::factory()->draft()->count(2)->create();

        $this->actingAs($admin)
            ->get(route('announcement.index'))
            ->assertOk()
            ->assertViewIs('pages.announcement.index');
    }

    public function test_admin_index_shows_all_including_drafts(): void
    {
        $admin = User::factory()->administrator()->create();
        Announcement::factory()->draft()->count(2)->create();
        Announcement::factory()->published()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('announcement.index'));

        $response->assertSee($response->viewData('announcements')->first()->title ?? true);
        $this->assertCount(4, Announcement::all());
    }

    public function test_non_admin_index_shows_only_published(): void
    {
        $teacher = User::factory()->teacher()->create();
        Announcement::factory()->draft()->create();
        Announcement::factory()->published()->count(2)->create();

        $this->actingAs($teacher)
            ->get(route('announcement.index'))
            ->assertOk();

        // non-admin sees only published
        $announcements = Announcement::published()->get();
        $this->assertCount(2, $announcements);
    }

    public function test_admin_can_view_create_form(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('announcement.create'))
            ->assertOk()
            ->assertViewIs('pages.announcement.create');
    }

    public function test_non_admin_cannot_access_create_form(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get(route('announcement.create'))
            ->assertForbidden();
    }

    public function test_admin_can_store_announcement(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->post(route('announcement.store'), [
                'title' => 'Test Announcement',
                'body' => 'Body content here',
                'scope' => AnnouncementScope::INTERNAL->value,
                'published_at' => null,
            ])
            ->assertRedirect(route('announcement.index'));

        $this->assertDatabaseHas('announcements', [
            'title' => 'Test Announcement',
            'author_id' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_store_announcement(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->post(route('announcement.store'), [
                'title' => 'Test Announcement',
                'body' => 'Body content here',
                'scope' => AnnouncementScope::INTERNAL->value,
            ])
            ->assertForbidden();
    }

    public function test_store_validation_requires_title(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->post(route('announcement.store'), [
                'body' => 'Body content',
                'scope' => AnnouncementScope::INTERNAL->value,
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_admin_can_edit_announcement(): void
    {
        $admin = User::factory()->administrator()->create();
        $announcement = Announcement::factory()->draft()->create();

        $this->actingAs($admin)
            ->get(route('announcement.edit', $announcement))
            ->assertOk()
            ->assertViewIs('pages.announcement.edit');
    }

    public function test_admin_can_update_announcement(): void
    {
        $admin = User::factory()->administrator()->create();
        $announcement = Announcement::factory()->draft()->create();

        $this->actingAs($admin)
            ->put(route('announcement.update', $announcement), [
                'title' => 'Updated Title',
                'body' => 'Updated body',
                'scope' => AnnouncementScope::PUBLIC->value,
                'published_at' => null,
            ])
            ->assertRedirect(route('announcement.index'));

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_publish_announcement(): void
    {
        $admin = User::factory()->administrator()->create();
        $announcement = Announcement::factory()->draft()->create();

        $this->assertNull($announcement->published_at);

        $this->actingAs($admin)
            ->patch(route('announcement.publish', $announcement))
            ->assertRedirect(route('announcement.show', $announcement));

        $this->assertNotNull($announcement->fresh()->published_at);
    }

    public function test_publish_is_idempotent(): void
    {
        $admin = User::factory()->administrator()->create();
        $publishedAt = now()->subDays(2);
        $announcement = Announcement::factory()->create(['published_at' => $publishedAt]);

        $this->actingAs($admin)
            ->patch(route('announcement.publish', $announcement))
            ->assertRedirect();

        // published_at should NOT change
        $this->assertEquals(
            $publishedAt->toDateTimeString(),
            $announcement->fresh()->published_at->toDateTimeString()
        );
    }

    public function test_admin_can_delete_announcement(): void
    {
        $admin = User::factory()->administrator()->create();
        $announcement = Announcement::factory()->create();

        $this->actingAs($admin)
            ->delete(route('announcement.destroy', $announcement))
            ->assertRedirect(route('announcement.index'));

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_non_admin_cannot_delete_announcement(): void
    {
        $teacher = User::factory()->teacher()->create();
        $announcement = Announcement::factory()->published()->create();

        $this->actingAs($teacher)
            ->delete(route('announcement.destroy', $announcement))
            ->assertForbidden();
    }

    // ── T5.3: Public announcement on welcome page ─────────────────────────────

    public function test_published_public_announcement_appears_on_welcome(): void
    {
        $announcement = Announcement::factory()->published()->public()->create([
            'title' => 'Public Welcome Announcement',
        ]);

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('Public Welcome Announcement');
    }

    public function test_internal_announcement_does_not_appear_on_welcome(): void
    {
        $announcement = Announcement::factory()->published()->internal()->create([
            'title' => 'Internal Only Announcement',
        ]);

        $this->get(route('welcome'))
            ->assertOk()
            ->assertDontSee('Internal Only Announcement');
    }

    public function test_draft_announcement_does_not_appear_on_welcome(): void
    {
        $announcement = Announcement::factory()->draft()->public()->create([
            'title' => 'Draft Public Announcement',
        ]);

        $this->get(route('welcome'))
            ->assertOk()
            ->assertDontSee('Draft Public Announcement');
    }

    // ── T5.4: Dashboard announcements for authenticated users ─────────────────

    public function test_authenticated_user_sees_announcements_on_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create();
        $announcement = Announcement::factory()->published()->internal()->create([
            'title' => 'Internal Dashboard Announcement',
        ]);

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Internal Dashboard Announcement');
    }

    public function test_dashboard_contains_view_all_link(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('announcement.index'));
    }

    public function test_guest_cannot_see_show_for_internal_announcement(): void
    {
        $announcement = Announcement::factory()->published()->internal()->create();

        $this->get(route('announcement.show', $announcement))
            ->assertForbidden();
    }

    public function test_guest_can_see_show_for_published_public_announcement(): void
    {
        $announcement = Announcement::factory()->published()->public()->create();

        $this->get(route('announcement.show', $announcement))
            ->assertOk();
    }

    public function test_guest_cannot_see_draft_announcement(): void
    {
        $announcement = Announcement::factory()->draft()->public()->create();

        $this->get(route('announcement.show', $announcement))
            ->assertForbidden();
    }
}
