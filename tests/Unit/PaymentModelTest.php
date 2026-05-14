<?php

namespace Tests\Unit;

use App\Enum\PaymentStatus;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_student(): void
    {
        $payment = Payment::factory()->create();

        $this->assertInstanceOf(Student::class, $payment->student);
    }

    public function test_belongs_to_recorded_by(): void
    {
        $payment = Payment::factory()->create();

        $this->assertInstanceOf(User::class, $payment->recordedBy);
    }

    public function test_student_has_many_payments(): void
    {
        $student = Student::factory()->create();
        Payment::factory()->count(3)->create(['student_id' => $student->id]);

        $this->assertCount(3, $student->payments);
    }

    public function test_status_cast_returns_enum(): void
    {
        $payment = Payment::factory()->paid()->create();

        $this->assertSame(PaymentStatus::PAID, $payment->status);
    }

    public function test_due_date_cast_returns_carbon(): void
    {
        $payment = Payment::factory()->create(['due_date' => '2025-03-01']);

        $this->assertSame('2025-03-01', $payment->due_date->format('Y-m-d'));
    }

    public function test_paid_at_is_null_for_unpaid(): void
    {
        $payment = Payment::factory()->unpaid()->create();

        $this->assertNull($payment->paid_at);
    }

    public function test_paid_at_is_datetime_for_paid(): void
    {
        $payment = Payment::factory()->paid()->create();

        $this->assertNotNull($payment->paid_at);
    }

    public function test_factory_produces_valid_record(): void
    {
        $payment = Payment::factory()->create();

        $this->assertNotNull($payment->student_id);
        $this->assertNotNull($payment->period);
        $this->assertNotNull($payment->amount);
        $this->assertNotNull($payment->due_date);
    }
}
