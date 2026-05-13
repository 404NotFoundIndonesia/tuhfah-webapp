<?php

namespace Tests\Feature;

use App\Enum\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Teacher',
            'email' => 'newteacher@example.com',
            'phone' => '08123456789',
            'gender' => 'female',
            'address' => null,
            'marital_status' => null,
        ], $overrides);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_owner_can_view_teacher_index(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get(route('teacher.index'))
            ->assertOk();
    }

    public function test_headmaster_can_view_teacher_index(): void
    {
        $this->actingAs(User::factory()->headmaster()->create())
            ->get(route('teacher.index'))
            ->assertOk();
    }

    public function test_administrator_can_view_teacher_index(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('teacher.index'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_teacher_index(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('teacher.index'))
            ->assertForbidden();
    }

    public function test_guardian_cannot_view_teacher_index(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('teacher.index'))
            ->assertForbidden();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_administrator_can_view_create_form(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('teacher.create'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_create_form(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('teacher.create'))
            ->assertForbidden();
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_owner_can_create_teacher(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post(route('teacher.store'), $this->validPayload())
            ->assertRedirect(route('teacher.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'newteacher@example.com',
            'role' => Role::TEACHER->value,
        ]);
    }

    public function test_administrator_can_create_teacher(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('teacher.store'), $this->validPayload(['email' => 'teacher2@example.com']))
            ->assertRedirect(route('teacher.index'));

        $this->assertDatabaseHas('users', ['email' => 'teacher2@example.com', 'role' => Role::TEACHER->value]);
    }

    public function test_teacher_cannot_create_teacher(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->post(route('teacher.store'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_guardian_cannot_create_teacher(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->post(route('teacher.store'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_store_requires_name(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post(route('teacher.store'), $this->validPayload(['name' => '']))
            ->assertSessionHasErrors('name');
    }

    public function test_store_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs(User::factory()->owner()->create())
            ->post(route('teacher.store'), $this->validPayload(['email' => 'taken@example.com']))
            ->assertSessionHasErrors('email');
    }

    public function test_new_teacher_has_default_password(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post(route('teacher.store'), $this->validPayload());

        $user = User::where('email', 'newteacher@example.com')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $user->password));
    }

    // ── Edit ─────────────────────────────────────────────────────────────────

    public function test_administrator_can_view_edit_form(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('teacher.edit', $teacher))
            ->assertOk();
    }

    public function test_teacher_cannot_view_edit_form(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('teacher.edit', $teacher))
            ->assertForbidden();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_administrator_can_update_teacher(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->patch(route('teacher.update', $teacher), $this->validPayload([
                'name' => 'Updated Teacher',
                'email' => $teacher->email,
            ]))
            ->assertRedirect();

        $this->assertSame('Updated Teacher', $teacher->fresh()->name);
    }

    public function test_teacher_cannot_update_teacher(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->patch(route('teacher.update', $teacher), $this->validPayload(['email' => $teacher->email]))
            ->assertForbidden();
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_owner_can_delete_teacher(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs(User::factory()->owner()->create())
            ->delete(route('teacher.destroy', $teacher))
            ->assertRedirect();

        $this->assertNull($teacher->fresh());
    }

    public function test_administrator_can_delete_teacher(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('teacher.destroy', $teacher))
            ->assertRedirect();

        $this->assertNull($teacher->fresh());
    }

    public function test_teacher_cannot_delete_teacher(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->delete(route('teacher.destroy', $teacher))
            ->assertForbidden();
    }

    public function test_guardian_cannot_delete_teacher(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs(User::factory()->studentGuardian()->create())
            ->delete(route('teacher.destroy', $teacher))
            ->assertForbidden();
    }
}
