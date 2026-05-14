<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\Student;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_payload_has_correct_structure(): void
    {
        $student = Student::factory()->create(['name' => 'Ahmad']);
        $payment = Payment::factory()->unpaid()->create([
            'student_id' => $student->id,
            'period' => '2025-03',
            'amount' => 200000,
        ]);

        // Bind a mock service to avoid real Midtrans config requirements
        $service = new PaymentGatewayService;
        $payload = $service->buildPayload($payment);

        $this->assertArrayHasKey('transaction_details', $payload);
        $this->assertArrayHasKey('item_details', $payload);
        $this->assertArrayHasKey('customer_details', $payload);
        $this->assertSame(200000, $payload['transaction_details']['gross_amount']);
        $this->assertSame(200000, $payload['item_details'][0]['price']);
        $this->assertStringContainsString('PAY-'.$payment->id, $payload['transaction_details']['order_id']);
    }

    public function test_verify_signature_returns_true_for_valid_signature(): void
    {
        config(['services.midtrans.server_key' => 'test-server-key']);
        $service = new PaymentGatewayService;

        $orderId = 'PAY-1-123';
        $statusCode = '200';
        $grossAmount = '200000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.'test-server-key');

        $this->assertTrue($service->verifySignature($orderId, $statusCode, $grossAmount, $signature));
    }

    public function test_verify_signature_returns_false_for_invalid_signature(): void
    {
        config(['services.midtrans.server_key' => 'test-server-key']);
        $service = new PaymentGatewayService;

        $this->assertFalse($service->verifySignature('PAY-1-123', '200', '200000.00', 'wrong-signature'));
    }
}
