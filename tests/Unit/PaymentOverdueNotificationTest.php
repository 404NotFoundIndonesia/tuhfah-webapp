<?php

namespace Tests\Unit;

use App\Notifications\PaymentOverdueNotification;
use Tests\TestCase;

class PaymentOverdueNotificationTest extends TestCase
{
    private function makeNotification(): PaymentOverdueNotification
    {
        return new PaymentOverdueNotification(
            studentName: 'Siti Aisyah',
            period: '2026-01',
            amount: '500000.00',
            dueDate: '2026-01-31',
        );
    }

    public function test_to_array_has_correct_keys(): void
    {
        $notification = $this->makeNotification();
        $data = $notification->toArray(new \stdClass);

        $this->assertArrayHasKey('student_name', $data);
        $this->assertArrayHasKey('period', $data);
        $this->assertArrayHasKey('amount', $data);
        $this->assertArrayHasKey('due_date', $data);
    }

    public function test_to_array_has_correct_values(): void
    {
        $notification = $this->makeNotification();
        $data = $notification->toArray(new \stdClass);

        $this->assertSame('Siti Aisyah', $data['student_name']);
        $this->assertSame('2026-01', $data['period']);
        $this->assertSame('500000.00', $data['amount']);
        $this->assertSame('2026-01-31', $data['due_date']);
    }

    public function test_via_includes_database(): void
    {
        $notification = $this->makeNotification();
        $notifiable = (object) ['email_notifications' => false];

        $this->assertContains('database', $notification->via($notifiable));
    }

    public function test_via_respects_email_opt_in(): void
    {
        $notification = $this->makeNotification();

        $this->assertContains('mail', $notification->via((object) ['email_notifications' => true]));
        $this->assertNotContains('mail', $notification->via((object) ['email_notifications' => false]));
    }
}
