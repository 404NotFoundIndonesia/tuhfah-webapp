<?php

namespace Tests\Feature;

use App\Enum\PaymentStatus;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    // ── Webhook ───────────────────────────────────────────────────────────────

    public function test_webhook_with_valid_signature_marks_payment_paid(): void
    {
        config(['services.midtrans.server_key' => 'test-key']);

        $payment = Payment::factory()->unpaid()->create();
        $orderId = 'PAY-'.$payment->id.'-999';
        $statusCode = '200';
        $grossAmount = '200000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.'test-key');

        $this->postJson(route('payment.webhook'), [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ])->assertOk();

        $this->assertSame(PaymentStatus::PAID, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_webhook_with_invalid_signature_returns_403(): void
    {
        config(['services.midtrans.server_key' => 'test-key']);

        $payment = Payment::factory()->unpaid()->create();

        $this->postJson(route('payment.webhook'), [
            'order_id' => 'PAY-'.$payment->id.'-999',
            'status_code' => '200',
            'gross_amount' => '200000.00',
            'signature_key' => 'invalid-signature',
            'transaction_status' => 'settlement',
        ])->assertForbidden();

        $this->assertSame(PaymentStatus::UNPAID, $payment->fresh()->status);
    }

    public function test_webhook_is_idempotent_for_already_paid(): void
    {
        config(['services.midtrans.server_key' => 'test-key']);

        $payment = Payment::factory()->paid()->create();
        $orderId = 'PAY-'.$payment->id.'-999';
        $statusCode = '200';
        $grossAmount = '200000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.'test-key');

        $this->postJson(route('payment.webhook'), [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ])->assertOk();

        // Still paid, not duplicated
        $this->assertSame(PaymentStatus::PAID, $payment->fresh()->status);
    }

    public function test_webhook_does_not_mark_paid_on_pending_status(): void
    {
        config(['services.midtrans.server_key' => 'test-key']);

        $payment = Payment::factory()->unpaid()->create();
        $orderId = 'PAY-'.$payment->id.'-999';
        $statusCode = '201';
        $grossAmount = '200000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.'test-key');

        $this->postJson(route('payment.webhook'), [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'pending',
            'fraud_status' => 'accept',
        ])->assertOk();

        $this->assertSame(PaymentStatus::UNPAID, $payment->fresh()->status);
    }
}
