<?php

namespace Tests\Feature;

use App\Enum\PaymentStatus;
use App\Models\Honorarium;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HonorariumTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_index(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('honorarium.index'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_index(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('honorarium.index'))
            ->assertForbidden();
    }

    public function test_guardian_cannot_view_index(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('honorarium.index'))
            ->assertForbidden();
    }

    public function test_admin_index_datatable_returns_records(): void
    {
        $admin = User::factory()->administrator()->create();
        Honorarium::factory()->count(2)->create(['recorded_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('honorarium.index'));

        $response->assertOk();
        $this->assertSame(2, $response->json('recordsTotal'));
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    public function test_admin_can_view_create_form(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('honorarium.create'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_create_form(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('honorarium.create'))
            ->assertForbidden();
    }

    public function test_admin_can_store_honorarium(): void
    {
        $admin = User::factory()->administrator()->create();
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($admin)
            ->post(route('honorarium.store'), [
                'teacher_id' => $teacher->id,
                'period' => '2025-03',
                'amount' => 1000000,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('honorarium.index'));

        $this->assertDatabaseHas('honorariums', [
            'teacher_id' => $teacher->id,
            'period' => '2025-03',
            'status' => PaymentStatus::UNPAID->value,
            'recorded_by' => $admin->id,
        ]);
    }

    public function test_store_requires_teacher_role(): void
    {
        $admin = User::factory()->administrator()->create();
        $notATeacher = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->post(route('honorarium.store'), [
                'teacher_id' => $notATeacher->id,
                'period' => '2025-03',
                'amount' => 1000000,
            ])
            ->assertSessionHasErrors('teacher_id');
    }

    public function test_store_requires_valid_period_format(): void
    {
        $admin = User::factory()->administrator()->create();
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($admin)
            ->post(route('honorarium.store'), [
                'teacher_id' => $teacher->id,
                'period' => 'March-2025',
                'amount' => 1000000,
            ])
            ->assertSessionHasErrors('period');
    }

    public function test_teacher_cannot_store_honorarium(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->post(route('honorarium.store'), [
                'teacher_id' => $teacher->id,
                'period' => '2025-03',
                'amount' => 1000000,
            ])
            ->assertForbidden();
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_admin_can_view_honorarium(): void
    {
        $admin = User::factory()->administrator()->create();
        $honorarium = Honorarium::factory()->create(['recorded_by' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('honorarium.show', $honorarium))
            ->assertOk();
    }

    // ── Mark Paid ─────────────────────────────────────────────────────────────

    public function test_admin_can_mark_honorarium_paid(): void
    {
        $admin = User::factory()->administrator()->create();
        $honorarium = Honorarium::factory()->unpaid()->create(['recorded_by' => $admin->id]);

        $this->actingAs($admin)
            ->patch(route('honorarium.mark-paid', $honorarium))
            ->assertRedirect(route('honorarium.index'));

        $this->assertDatabaseHas('honorariums', [
            'id' => $honorarium->id,
            'status' => PaymentStatus::PAID->value,
        ]);
        $this->assertNotNull($honorarium->fresh()->paid_at);
    }

    public function test_teacher_cannot_mark_honorarium_paid(): void
    {
        $honorarium = Honorarium::factory()->unpaid()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->patch(route('honorarium.mark-paid', $honorarium))
            ->assertForbidden();
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_admin_can_delete_honorarium(): void
    {
        $admin = User::factory()->administrator()->create();
        $honorarium = Honorarium::factory()->create(['recorded_by' => $admin->id]);

        $this->actingAs($admin)
            ->delete(route('honorarium.destroy', $honorarium))
            ->assertRedirect();

        $this->assertDatabaseMissing('honorariums', ['id' => $honorarium->id]);
    }

    public function test_teacher_cannot_delete_honorarium(): void
    {
        $honorarium = Honorarium::factory()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->delete(route('honorarium.destroy', $honorarium))
            ->assertForbidden();
    }

    // ── Export (T4.7) ─────────────────────────────────────────────────────────

    public function test_admin_can_export_honorariums_xlsx(): void
    {
        $admin = User::factory()->administrator()->create();
        Honorarium::factory()->count(2)->create(['recorded_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->get(route('honorarium.export', ['format' => 'xlsx']));

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml',
            $response->headers->get('Content-Type')
        );
    }

    public function test_admin_can_export_honorariums_pdf(): void
    {
        $admin = User::factory()->administrator()->create();
        Honorarium::factory()->count(2)->create(['recorded_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->get(route('honorarium.export', ['format' => 'pdf']));

        $response->assertOk();
        $this->assertStringContainsString('pdf', $response->headers->get('Content-Type'));
    }

    public function test_teacher_cannot_export_honorariums(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('honorarium.export'))
            ->assertForbidden();
    }
}
