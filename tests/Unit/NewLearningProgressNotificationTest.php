<?php

namespace Tests\Unit;

use App\Notifications\NewLearningProgressNotification;
use Tests\TestCase;

class NewLearningProgressNotificationTest extends TestCase
{
    private function makeNotification(): NewLearningProgressNotification
    {
        return new NewLearningProgressNotification(
            studentName: 'Fatimah',
            subject: 'Matematika',
            milestone: 'Perkalian dasar',
            teacherName: 'Ustadz Hasan',
            date: '2026-05-14',
        );
    }

    public function test_to_array_has_correct_keys(): void
    {
        $notification = $this->makeNotification();
        $data = $notification->toArray(new \stdClass);

        $this->assertArrayHasKey('student_name', $data);
        $this->assertArrayHasKey('subject', $data);
        $this->assertArrayHasKey('milestone', $data);
        $this->assertArrayHasKey('teacher_name', $data);
        $this->assertArrayHasKey('date', $data);
    }

    public function test_to_array_has_correct_values(): void
    {
        $notification = $this->makeNotification();
        $data = $notification->toArray(new \stdClass);

        $this->assertSame('Fatimah', $data['student_name']);
        $this->assertSame('Matematika', $data['subject']);
        $this->assertSame('Perkalian dasar', $data['milestone']);
        $this->assertSame('Ustadz Hasan', $data['teacher_name']);
        $this->assertSame('2026-05-14', $data['date']);
    }

    public function test_via_includes_database(): void
    {
        $notification = $this->makeNotification();

        $this->assertContains('database', $notification->via((object) ['email_notifications' => false]));
    }

    public function test_via_respects_email_opt_in(): void
    {
        $notification = $this->makeNotification();

        $this->assertContains('mail', $notification->via((object) ['email_notifications' => true]));
        $this->assertNotContains('mail', $notification->via((object) ['email_notifications' => false]));
    }
}
