<?php

namespace App\Http\Controllers;

use App\Enum\PaymentStatus;
use App\Enum\Role;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentGatewayController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $gateway) {}

    public function checkout(Payment $payment): JsonResponse
    {
        abort_unless(auth()->user()->isRole(Role::STUDENT_GUARDIAN), 403);

        $student = \App\Models\Student::where('student_guardian_id', auth()->id())->firstOrFail();
        abort_unless($payment->student_id === $student->id, 403);
        abort_unless($payment->status !== PaymentStatus::PAID, 422);

        $snapToken = $this->gateway->createTransaction($payment);

        return response()->json(['snap_token' => $snapToken]);
    }

    public function webhook(Request $request): Response
    {
        $orderId = $request->input('order_id', '');
        $statusCode = $request->input('status_code', '');
        $grossAmount = $request->input('gross_amount', '');
        $signature = $request->input('signature_key', '');
        $transactionStatus = $request->input('transaction_status', '');
        $fraudStatus = $request->input('fraud_status', '');

        abort_unless(
            $this->gateway->verifySignature($orderId, $statusCode, $grossAmount, $signature),
            403
        );

        // Extract payment ID from order_id format "PAY-{id}-{timestamp}"
        $parts = explode('-', $orderId);
        $paymentId = $parts[1] ?? null;

        if (! $paymentId) {
            return response('Invalid order_id', 400);
        }

        $payment = Payment::find($paymentId);
        if (! $payment) {
            return response('Payment not found', 404);
        }

        // Idempotent — skip if already paid
        if ($payment->status === PaymentStatus::PAID) {
            return response('OK', 200);
        }

        $isSuccess = ($transactionStatus === 'capture' && $fraudStatus === 'accept')
            || $transactionStatus === 'settlement';

        if ($isSuccess) {
            $payment->update([
                'status' => PaymentStatus::PAID->value,
                'paid_at' => now(),
            ]);
        }

        return response('OK', 200);
    }
}
