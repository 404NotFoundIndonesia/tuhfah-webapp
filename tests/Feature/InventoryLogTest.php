<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryLogTest extends TestCase
{
    use RefreshDatabase;

    private function logPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'usage',
            'quantity_changed' => 2,
            'reason' => 'Used for class activity',
        ], $overrides);
    }

    // ── Gate ─────────────────────────────────────────────────────────────────

    public function test_administrator_can_log_usage(): void
    {
        $item = Inventory::factory()->create(['quantity' => 10]);

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.log', $item), $this->logPayload())
            ->assertRedirect(route('inventory.show', $item));
    }

    public function test_headmaster_cannot_log_usage(): void
    {
        $item = Inventory::factory()->create(['quantity' => 10]);

        $this->actingAs(User::factory()->headmaster()->create())
            ->post(route('inventory.log', $item), $this->logPayload())
            ->assertForbidden();
    }

    public function test_owner_cannot_log_usage(): void
    {
        $item = Inventory::factory()->create(['quantity' => 10]);

        $this->actingAs(User::factory()->owner()->create())
            ->post(route('inventory.log', $item), $this->logPayload())
            ->assertForbidden();
    }

    // ── Quantity decrement ────────────────────────────────────────────────────

    public function test_logging_usage_decrements_quantity(): void
    {
        $item = Inventory::factory()->create(['quantity' => 10]);

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.log', $item), $this->logPayload(['quantity_changed' => 3]));

        $this->assertSame(7, $item->fresh()->quantity);
    }

    public function test_log_record_is_saved_to_database(): void
    {
        $item = Inventory::factory()->create(['quantity' => 10]);

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.log', $item), $this->logPayload([
                'type' => 'disposal',
                'quantity_changed' => 2,
                'reason' => 'Item damaged',
            ]));

        $this->assertDatabaseHas('inventory_logs', [
            'inventory_id' => $item->id,
            'type' => 'disposal',
            'quantity_changed' => 2,
            'reason' => 'Item damaged',
        ]);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_quantity_changed_cannot_exceed_current_quantity(): void
    {
        $item = Inventory::factory()->create(['quantity' => 5]);

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.log', $item), $this->logPayload(['quantity_changed' => 10]))
            ->assertSessionHasErrors('quantity_changed');
    }

    public function test_quantity_changed_must_be_at_least_one(): void
    {
        $item = Inventory::factory()->create(['quantity' => 5]);

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.log', $item), $this->logPayload(['quantity_changed' => 0]))
            ->assertSessionHasErrors('quantity_changed');
    }

    public function test_type_must_be_usage_or_disposal(): void
    {
        $item = Inventory::factory()->create(['quantity' => 5]);

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.log', $item), $this->logPayload(['type' => 'broken']))
            ->assertSessionHasErrors('type');
    }

    public function test_reason_is_required(): void
    {
        $item = Inventory::factory()->create(['quantity' => 5]);

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.log', $item), $this->logPayload(['reason' => '']))
            ->assertSessionHasErrors('reason');
    }

    public function test_quantity_does_not_change_when_validation_fails(): void
    {
        $item = Inventory::factory()->create(['quantity' => 5]);

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('inventory.log', $item), $this->logPayload(['quantity_changed' => 100]));

        $this->assertSame(5, $item->fresh()->quantity);
    }
}
