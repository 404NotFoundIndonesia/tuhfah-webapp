<?php

namespace Tests\Feature;

use App\Enum\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentGuardianTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Guardian',
            'email' => 'newguardian@example.com',
            'phone' => '08123456789',
            'gender' => 'male',
            'address' => null,
            'marital_status' => null,
        ], $overrides);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_owner_can_view_guardian_index(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get(route('student-guardian.index'))
            ->assertOk();
    }

    public function test_headmaster_can_view_guardian_index(): void
    {
        $this->actingAs(User::factory()->headmaster()->create())
            ->get(route('student-guardian.index'))
            ->assertOk();
    }

    public function test_administrator_can_view_guardian_index(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('student-guardian.index'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_guardian_index(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('student-guardian.index'))
            ->assertForbidden();
    }

    public function test_guardian_cannot_view_guardian_index(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('student-guardian.index'))
            ->assertForbidden();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_administrator_can_view_create_form(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('student-guardian.create'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_create_form(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('student-guardian.create'))
            ->assertForbidden();
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_owner_can_create_guardian(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post(route('student-guardian.store'), $this->validPayload())
            ->assertRedirect(route('student-guardian.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'newguardian@example.com',
            'role' => Role::STUDENT_GUARDIAN->value,
        ]);
    }

    public function test_administrator_can_create_guardian(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('student-guardian.store'), $this->validPayload(['email' => 'guardian2@example.com']))
            ->assertRedirect(route('student-guardian.index'));

        $this->assertDatabaseHas('users', ['email' => 'guardian2@example.com', 'role' => Role::STUDENT_GUARDIAN->value]);
    }

    public function test_teacher_cannot_create_guardian(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->post(route('student-guardian.store'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_guardian_cannot_create_guardian(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->post(route('student-guardian.store'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_store_requires_name(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post(route('student-guardian.store'), $this->validPayload(['name' => '']))
            ->assertSessionHasErrors('name');
    }

    public function test_store_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs(User::factory()->owner()->create())
            ->post(route('student-guardian.store'), $this->validPayload(['email' => 'taken@example.com']))
            ->assertSessionHasErrors('email');
    }

    public function test_new_guardian_has_default_password(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post(route('student-guardian.store'), $this->validPayload());

        $user = User::where('email', 'newguardian@example.com')->first();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    // ── Edit ─────────────────────────────────────────────────────────────────

    public function test_administrator_can_view_edit_form(): void
    {
        $guardian = User::factory()->studentGuardian()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('student-guardian.edit', $guardian))
            ->assertOk();
    }

    public function test_teacher_cannot_view_edit_form(): void
    {
        $guardian = User::factory()->studentGuardian()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('student-guardian.edit', $guardian))
            ->assertForbidden();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_administrator_can_update_guardian(): void
    {
        $guardian = User::factory()->studentGuardian()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->patch(route('student-guardian.update', $guardian), $this->validPayload([
                'name' => 'Updated Guardian',
                'email' => $guardian->email,
            ]))
            ->assertRedirect();

        $this->assertSame('Updated Guardian', $guardian->fresh()->name);
    }

    public function test_teacher_cannot_update_guardian(): void
    {
        $guardian = User::factory()->studentGuardian()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->patch(route('student-guardian.update', $guardian), $this->validPayload(['email' => $guardian->email]))
            ->assertForbidden();
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_owner_can_delete_guardian(): void
    {
        $guardian = User::factory()->studentGuardian()->create();

        $this->actingAs(User::factory()->owner()->create())
            ->delete(route('student-guardian.destroy', $guardian))
            ->assertRedirect();

        $this->assertNull($guardian->fresh());
    }

    public function test_administrator_can_delete_guardian(): void
    {
        $guardian = User::factory()->studentGuardian()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('student-guardian.destroy', $guardian))
            ->assertRedirect();

        $this->assertNull($guardian->fresh());
    }

    public function test_teacher_cannot_delete_guardian(): void
    {
        $guardian = User::factory()->studentGuardian()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->delete(route('student-guardian.destroy', $guardian))
            ->assertForbidden();
    }

    public function test_guardian_cannot_delete_guardian(): void
    {
        $guardian = User::factory()->studentGuardian()->create();

        $this->actingAs(User::factory()->studentGuardian()->create())
            ->delete(route('student-guardian.destroy', $guardian))
            ->assertForbidden();
    }
}
