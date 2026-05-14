<?php

namespace App\Services;

use App\Models\Payment;

class PaymentGatewayService
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    public function createTransaction(Payment $payment): string
    {
        $params = $this->buildPayload($payment);

        return \Midtrans\Snap::getSnapToken($params);
    }

    public function buildPayload(Payment $payment): array
    {
        return [
            'transaction_details' => [
                'order_id' => 'PAY-'.$payment->id.'-'.time(),
                'gross_amount' => (int) $payment->amount,
            ],
            'customer_details' => [
                'first_name' => optional($payment->student?->guardian)->name ?? 'Guardian',
                'email' => optional($payment->student?->guardian)->email ?? '',
            ],
            'item_details' => [
                [
                    'id' => 'tuition-'.$payment->period,
                    'price' => (int) $payment->amount,
                    'quantity' => 1,
                    'name' => 'SPP '.$payment->period.' - '.optional($payment->student)->name,
                ],
            ],
        ];
    }

    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signature): bool
    {
        $serverKey = config('services.midtrans.server_key');
        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signature);
    }
}
