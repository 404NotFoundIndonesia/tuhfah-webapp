<?php

namespace Tests\Unit;

use App\Enum\PaymentStatus;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkOverduePaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_unpaid_past_due_date_as_overdue(): void
    {
        Payment::factory()->unpaid()->create(['due_date' => now()->subDays(3)->format('Y-m-d')]);

        $this->artisan('payments:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('payments', ['status' => PaymentStatus::OVERDUE->value]);
    }

    public function test_skips_paid_records(): void
    {
        $paid = Payment::factory()->paid()->create(['due_date' => now()->subDays(3)->format('Y-m-d')]);

        $this->artisan('payments:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('payments', [
            'id' => $paid->id,
            'status' => PaymentStatus::PAID->value,
        ]);
    }

    public function test_skips_future_due_dates(): void
    {
        $future = Payment::factory()->unpaid()->create(['due_date' => now()->addDays(5)->format('Y-m-d')]);

        $this->artisan('payments:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('payments', [
            'id' => $future->id,
            'status' => PaymentStatus::UNPAID->value,
        ]);
    }

    public function test_handles_no_records(): void
    {
        $this->artisan('payments:mark-overdue')->assertSuccessful();
    }

    public function test_marks_multiple_overdue_records(): void
    {
        Payment::factory()->count(3)->unpaid()->create(['due_date' => now()->subDay()->format('Y-m-d')]);
        Payment::factory()->paid()->create(['due_date' => now()->subDay()->format('Y-m-d')]);

        $this->artisan('payments:mark-overdue')->assertSuccessful();

        $this->assertDatabaseCount('payments', 4);
        $this->assertSame(3, Payment::where('status', PaymentStatus::OVERDUE->value)->count());
        $this->assertSame(1, Payment::where('status', PaymentStatus::PAID->value)->count());
    }

    public function test_does_not_touch_already_overdue(): void
    {
        Payment::factory()->overdue()->create();

        $this->artisan('payments:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('payments', ['status' => PaymentStatus::OVERDUE->value]);
    }
}
