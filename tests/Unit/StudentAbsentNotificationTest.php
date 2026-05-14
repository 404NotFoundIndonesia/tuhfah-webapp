<?php

namespace Tests\Unit;

use App\Notifications\StudentAbsentNotification;
use Tests\TestCase;

class StudentAbsentNotificationTest extends TestCase
{
    private function makeNotification(): StudentAbsentNotification
    {
        return new StudentAbsentNotification(
            studentName: 'Ahmad Rizky',
            date: '2026-05-14',
            status: 'absent',
            recordedBy: 'Budi Santoso',
        );
    }

    public function test_to_array_has_correct_keys(): void
    {
        $notification = $this->makeNotification();
        $data = $notification->toArray(new \stdClass);

        $this->assertArrayHasKey('student_name', $data);
        $this->assertArrayHasKey('date', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('recorded_by', $data);
    }

    public function test_to_array_has_correct_values(): void
    {
        $notification = $this->makeNotification();
        $data = $notification->toArray(new \stdClass);

        $this->assertSame('Ahmad Rizky', $data['student_name']);
        $this->assertSame('2026-05-14', $data['date']);
        $this->assertSame('absent', $data['status']);
        $this->assertSame('Budi Santoso', $data['recorded_by']);
    }

    public function test_via_includes_database(): void
    {
        $notification = $this->makeNotification();
        $notifiable = (object) ['email_notifications' => false];

        $this->assertContains('database', $notification->via($notifiable));
    }

    public function test_via_includes_mail_when_opted_in(): void
    {
        $notification = $this->makeNotification();
        $notifiable = (object) ['email_notifications' => true];

        $this->assertContains('mail', $notification->via($notifiable));
    }

    public function test_via_excludes_mail_when_opted_out(): void
    {
        $notification = $this->makeNotification();
        $notifiable = (object) ['email_notifications' => false];

        $this->assertNotContains('mail', $notification->via($notifiable));
    }
}
