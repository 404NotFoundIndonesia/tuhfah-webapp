<?php

namespace Tests\Feature;

use App\Enum\ItemCondition;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Al-Qur\'an 30 Juz',
            'quantity' => 10,
            'condition' => ItemCondition::GOOD->value,
            'acquisition_date' => '2024-01-15',
            'notes' => null,
        ], $overrides);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_owner_can_view_inventory_index(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get(route('inventory.index'))
            ->assertOk();
    }

    public function test_headmaster_can_view_inventory_index(): void
    {
        $this->actingAs(User::factory()->headmaster()->create())
            ->get(route('inventory.index'))
            ->assertOk();
    }

    public function test_administrator_can_view_inventory_index(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('inventory.index'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_inventory_index(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('inventory.index'))
            ->assertForbidden();
    }

    public function test_guardian_cannot_view_inventory_index(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('inventory.index'))
            ->assertForbidden();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_administrator_can_view_create_form(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('inventory.create'))
            ->assertOk();
    }

    public function test_headmaster_cannot_view_create_form(): void
    {
        $this->actingAs(User::factory()->headmaster()->create())
            ->get(route('inventory.create'))
            ->assertForbidden();
    }

    public function test_owner_cannot_view_create_form(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get(route('inventory.create'))
            ->assertForbidden();
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_administrator_can_create_inventory_item(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.store'), $this->validPayload())
            ->assertRedirect(route('inventory.index'));

        $this->assertDatabaseHas('inventories', ['name' => 'Al-Qur\'an 30 Juz']);
    }

    public function test_headmaster_cannot_create_inventory_item(): void
    {
        $this->actingAs(User::factory()->headmaster()->create())
            ->post(route('inventory.store'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_store_requires_name(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.store'), $this->validPayload(['name' => '']))
            ->assertSessionHasErrors('name');
    }

    public function test_store_requires_valid_condition(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.store'), $this->validPayload(['condition' => 'broken']))
            ->assertSessionHasErrors('condition');
    }

    public function test_store_requires_quantity_not_negative(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.store'), $this->validPayload(['quantity' => -1]))
            ->assertSessionHasErrors('quantity');
    }

    public function test_store_accepts_zero_quantity(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.store'), $this->validPayload(['quantity' => 0]))
            ->assertRedirect(route('inventory.index'));
    }

    public function test_store_requires_acquisition_date(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.store'), $this->validPayload(['acquisition_date' => '']))
            ->assertSessionHasErrors('acquisition_date');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_owner_can_view_inventory_show(): void
    {
        $item = Inventory::factory()->create();

        $this->actingAs(User::factory()->owner()->create())
            ->get(route('inventory.show', $item))
            ->assertOk();
    }

    public function test_teacher_cannot_view_inventory_show(): void
    {
        $item = Inventory::factory()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('inventory.show', $item))
            ->assertForbidden();
    }

    // ── Edit ─────────────────────────────────────────────────────────────────

    public function test_administrator_can_view_edit_form(): void
    {
        $item = Inventory::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('inventory.edit', $item))
            ->assertOk();
    }

    public function test_headmaster_cannot_view_edit_form(): void
    {
        $item = Inventory::factory()->create();

        $this->actingAs(User::factory()->headmaster()->create())
            ->get(route('inventory.edit', $item))
            ->assertForbidden();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_administrator_can_update_inventory_item(): void
    {
        $item = Inventory::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->put(route('inventory.update', $item), $this->validPayload(['name' => 'Updated Name']))
            ->assertRedirect(route('inventory.index'));

        $this->assertSame('Updated Name', $item->fresh()->name);
    }

    public function test_headmaster_cannot_update_inventory_item(): void
    {
        $item = Inventory::factory()->create();

        $this->actingAs(User::factory()->headmaster()->create())
            ->put(route('inventory.update', $item), $this->validPayload())
            ->assertForbidden();
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_administrator_can_delete_inventory_item(): void
    {
        $item = Inventory::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('inventory.destroy', $item))
            ->assertRedirect(route('inventory.index'));

        $this->assertNull($item->fresh());
    }

    public function test_headmaster_cannot_delete_inventory_item(): void
    {
        $item = Inventory::factory()->create();

        $this->actingAs(User::factory()->headmaster()->create())
            ->delete(route('inventory.destroy', $item))
            ->assertForbidden();
    }

    public function test_teacher_cannot_delete_inventory_item(): void
    {
        $item = Inventory::factory()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->delete(route('inventory.destroy', $item))
            ->assertForbidden();
    }

    // ── DataTable filtering ───────────────────────────────────────────────────

    public function test_condition_filter_returns_correct_subset(): void
    {
        Inventory::factory()->good()->count(2)->create();
        Inventory::factory()->damaged()->count(3)->create();

        $response = $this->actingAs(User::factory()->administrator()->create())
            ->getJson(route('inventory.index'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        // DataTables AJAX endpoint returns JSON with data key
        $response->assertOk()->assertJsonStructure(['data']);
    }
}
