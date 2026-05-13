<?php

namespace Tests\Feature;

use App\Enum\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministratorTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'phone' => '08123456789',
            'gender' => 'male',
            'address' => null,
            'marital_status' => null,
        ], $overrides);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_owner_can_view_administrator_index(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get(route('administrator.index'))
            ->assertOk();
    }

    public function test_headmaster_can_view_administrator_index(): void
    {
        $this->actingAs(User::factory()->headmaster()->create())
            ->get(route('administrator.index'))
            ->assertOk();
    }

    public function test_administrator_cannot_view_administrator_index(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('administrator.index'))
            ->assertForbidden();
    }

    public function test_teacher_cannot_view_administrator_index(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('administrator.index'))
            ->assertForbidden();
    }

    public function test_guardian_cannot_view_administrator_index(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('administrator.index'))
            ->assertForbidden();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_owner_can_view_create_form(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get(route('administrator.create'))
            ->assertOk();
    }

    public function test_administrator_cannot_view_create_form(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('administrator.create'))
            ->assertForbidden();
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_owner_can_create_administrator(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post(route('administrator.store'), $this->validPayload())
            ->assertRedirect(route('administrator.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'role' => Role::ADMINISTRATOR->value,
        ]);
    }

    public function test_headmaster_can_create_administrator(): void
    {
        $this->actingAs(User::factory()->headmaster()->create())
            ->post(route('administrator.store'), $this->validPayload(['email' => 'admin2@example.com']))
            ->assertRedirect(route('administrator.index'));

        $this->assertDatabaseHas('users', ['email' => 'admin2@example.com', 'role' => Role::ADMINISTRATOR->value]);
    }

    public function test_administrator_cannot_create_administrator(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('administrator.store'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_store_requires_name(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post(route('administrator.store'), $this->validPayload(['name' => '']))
            ->assertSessionHasErrors('name');
    }

    public function test_store_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs(User::factory()->owner()->create())
            ->post(route('administrator.store'), $this->validPayload(['email' => 'taken@example.com']))
            ->assertSessionHasErrors('email');
    }

    public function test_new_administrator_has_default_password(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post(route('administrator.store'), $this->validPayload());

        $user = User::where('email', 'newadmin@example.com')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $user->password));
    }

    // ── Edit ─────────────────────────────────────────────────────────────────

    public function test_owner_can_view_edit_form(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs(User::factory()->owner()->create())
            ->get(route('administrator.edit', $admin))
            ->assertOk();
    }

    public function test_administrator_cannot_view_edit_form(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('administrator.edit', $admin))
            ->assertForbidden();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_owner_can_update_administrator(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs(User::factory()->owner()->create())
            ->patch(route('administrator.update', $admin), $this->validPayload([
                'name' => 'Updated Name',
                'email' => $admin->email,
            ]))
            ->assertRedirect();

        $this->assertSame('Updated Name', $admin->fresh()->name);
    }

    public function test_administrator_cannot_update_administrator(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->patch(route('administrator.update', $admin), $this->validPayload(['email' => $admin->email]))
            ->assertForbidden();
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_owner_can_delete_administrator(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs(User::factory()->owner()->create())
            ->delete(route('administrator.destroy', $admin))
            ->assertRedirect();

        $this->assertNull($admin->fresh());
    }

    public function test_headmaster_can_delete_administrator(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs(User::factory()->headmaster()->create())
            ->delete(route('administrator.destroy', $admin))
            ->assertRedirect();

        $this->assertNull($admin->fresh());
    }

    public function test_administrator_cannot_delete_administrator(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('administrator.destroy', $admin))
            ->assertForbidden();
    }

    public function test_teacher_cannot_delete_administrator(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->delete(route('administrator.destroy', $admin))
            ->assertForbidden();
    }
}
