<?php

namespace Tests\Feature;

use App\Enum\PaymentStatus;
use App\Enum\StudentStatus;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_index(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('payment.index'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_index(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('payment.index'))
            ->assertForbidden();
    }

    public function test_guardian_cannot_view_index(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('payment.index'))
            ->assertForbidden();
    }

    public function test_admin_index_datatable_returns_all_records(): void
    {
        $admin = User::factory()->administrator()->create();
        Payment::factory()->count(3)->create(['recorded_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('payment.index'));

        $response->assertOk();
        $this->assertSame(3, $response->json('recordsTotal'));
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    public function test_admin_can_view_create_form(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('payment.create'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_create_form(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('payment.create'))
            ->assertForbidden();
    }

    public function test_admin_can_store_payment(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($admin)
            ->post(route('payment.store'), [
                'student_id' => $student->id,
                'period' => '2025-03',
                'amount' => 200000,
                'due_date' => '2025-03-10',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('payment.index'));

        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'period' => '2025-03',
            'amount' => 200000,
            'status' => PaymentStatus::UNPAID->value,
            'recorded_by' => $admin->id,
        ]);
    }

    public function test_store_requires_active_student(): void
    {
        $admin = User::factory()->administrator()->create();
        $graduated = Student::factory()->create(['status' => StudentStatus::GRADUATED->value]);

        $this->actingAs($admin)
            ->post(route('payment.store'), [
                'student_id' => $graduated->id,
                'period' => '2025-03',
                'amount' => 200000,
                'due_date' => '2025-03-10',
            ])
            ->assertSessionHasErrors('student_id');
    }

    public function test_store_requires_valid_period_format(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($admin)
            ->post(route('payment.store'), [
                'student_id' => $student->id,
                'period' => 'March 2025',
                'amount' => 200000,
                'due_date' => '2025-03-10',
            ])
            ->assertSessionHasErrors('period');
    }

    public function test_store_requires_positive_amount(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($admin)
            ->post(route('payment.store'), [
                'student_id' => $student->id,
                'period' => '2025-03',
                'amount' => 0,
                'due_date' => '2025-03-10',
            ])
            ->assertSessionHasErrors('amount');
    }

    public function test_teacher_cannot_store_payment(): void
    {
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs(User::factory()->teacher()->create())
            ->post(route('payment.store'), [
                'student_id' => $student->id,
                'period' => '2025-03',
                'amount' => 200000,
                'due_date' => '2025-03-10',
            ])
            ->assertForbidden();
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_admin_can_view_payment(): void
    {
        $admin = User::factory()->administrator()->create();
        $payment = Payment::factory()->create(['recorded_by' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('payment.show', $payment))
            ->assertOk();
    }

    public function test_teacher_cannot_view_payment(): void
    {
        $payment = Payment::factory()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('payment.show', $payment))
            ->assertForbidden();
    }

    // ── Mark Paid ─────────────────────────────────────────────────────────────

    public function test_admin_can_mark_payment_paid(): void
    {
        $admin = User::factory()->administrator()->create();
        $payment = Payment::factory()->unpaid()->create();

        $this->actingAs($admin)
            ->patch(route('payment.mark-paid', $payment))
            ->assertRedirect(route('payment.index'));

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::PAID->value,
            'recorded_by' => $admin->id,
        ]);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_mark_paid_updates_paid_at_and_recorded_by(): void
    {
        $admin = User::factory()->administrator()->create();
        $payment = Payment::factory()->unpaid()->create();

        $this->actingAs($admin)->patch(route('payment.mark-paid', $payment));

        $fresh = $payment->fresh();
        $this->assertSame(PaymentStatus::PAID, $fresh->status);
        $this->assertNotNull($fresh->paid_at);
        $this->assertSame($admin->id, $fresh->recorded_by);
    }

    public function test_teacher_cannot_mark_payment_paid(): void
    {
        $payment = Payment::factory()->unpaid()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->patch(route('payment.mark-paid', $payment))
            ->assertForbidden();
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_admin_can_delete_payment(): void
    {
        $admin = User::factory()->administrator()->create();
        $payment = Payment::factory()->create(['recorded_by' => $admin->id]);

        $this->actingAs($admin)
            ->delete(route('payment.destroy', $payment))
            ->assertRedirect();

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function test_teacher_cannot_delete_payment(): void
    {
        $payment = Payment::factory()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->delete(route('payment.destroy', $payment))
            ->assertForbidden();
    }

    // ── Guardian View (T4.4) ──────────────────────────────────────────────────

    public function test_guardian_can_view_child_payments(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);
        Payment::factory()->count(2)->create(['student_id' => $student->id]);

        $this->actingAs($guardian)
            ->get(route('payment.guardian'))
            ->assertOk();
    }

    public function test_admin_cannot_access_guardian_payment_route(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('payment.guardian'))
            ->assertForbidden();
    }

    public function test_teacher_cannot_access_guardian_payment_route(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('payment.guardian'))
            ->assertForbidden();
    }

    // ── Export (T4.7) ─────────────────────────────────────────────────────────

    public function test_admin_can_export_payments_xlsx(): void
    {
        $admin = User::factory()->administrator()->create();
        Payment::factory()->count(2)->create(['recorded_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->get(route('payment.export', ['format' => 'xlsx']));

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml',
            $response->headers->get('Content-Type')
        );
    }

    public function test_admin_can_export_payments_pdf(): void
    {
        $admin = User::factory()->administrator()->create();
        Payment::factory()->count(2)->create(['recorded_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->get(route('payment.export', ['format' => 'pdf']));

        $response->assertOk();
        $this->assertStringContainsString('pdf', $response->headers->get('Content-Type'));
    }

    public function test_teacher_cannot_export_payments(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('payment.export'))
            ->assertForbidden();
    }
}
