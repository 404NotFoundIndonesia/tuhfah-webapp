<?php

namespace Tests\Feature;

use App\Enum\AttendanceStatus;
use App\Enum\PaymentStatus;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Notifications\NewLearningProgressNotification;
use App\Notifications\PaymentOverdueNotification;
use App\Notifications\StudentAbsentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    // ── T6.1: Notification bell / routes ──────────────────────────────────────

    public function test_authenticated_user_can_view_notifications_index(): void
    {
        $user = User::factory()->teacher()->create();

        $this->actingAs($user)
            ->get(route('notification.index'))
            ->assertOk()
            ->assertViewIs('pages.notification.index');
    }

    public function test_unread_count_returns_json(): void
    {
        $user = User::factory()->teacher()->create();
        $user->notify(new StudentAbsentNotification('Test', '2026-05-14', 'absent', 'Recorder'));

        $this->actingAs($user)
            ->getJson(route('notification.count'))
            ->assertOk()
            ->assertJson(['count' => 1]);
    }

    public function test_mark_read_clears_single_notification(): void
    {
        $user = User::factory()->teacher()->create();
        $user->notify(new StudentAbsentNotification('Test', '2026-05-14', 'absent', 'Recorder'));

        $notification = $user->unreadNotifications()->first();

        $this->assertNull($notification->read_at);

        $this->actingAs($user)
            ->patch(route('notification.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_read_clears_all_notifications(): void
    {
        $user = User::factory()->teacher()->create();
        $user->notify(new StudentAbsentNotification('A', '2026-05-14', 'absent', 'R'));
        $user->notify(new StudentAbsentNotification('B', '2026-05-14', 'sick', 'R'));

        $this->assertSame(2, $user->unreadNotifications()->count());

        $this->actingAs($user)
            ->patch(route('notification.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    // ── T6.2: Attendance notifications ───────────────────────────────────────

    public function test_absent_attendance_sends_notification_to_guardian(): void
    {
        Notification::fake();

        $admin = User::factory()->administrator()->create();
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);

        $this->actingAs($admin)
            ->post(route('attendance.store'), [
                'date' => '2026-05-14',
                'records' => [
                    $student->id => ['status' => AttendanceStatus::ABSENT->value],
                ],
            ]);

        Notification::assertSentTo($guardian, StudentAbsentNotification::class);
    }

    public function test_sick_attendance_sends_notification_to_guardian(): void
    {
        Notification::fake();

        $admin = User::factory()->administrator()->create();
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);

        $this->actingAs($admin)
            ->post(route('attendance.store'), [
                'date' => '2026-05-14',
                'records' => [
                    $student->id => ['status' => AttendanceStatus::SICK->value],
                ],
            ]);

        Notification::assertSentTo($guardian, StudentAbsentNotification::class);
    }

    public function test_present_attendance_does_not_send_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->administrator()->create();
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);

        $this->actingAs($admin)
            ->post(route('attendance.store'), [
                'date' => '2026-05-14',
                'records' => [
                    $student->id => ['status' => AttendanceStatus::PRESENT->value],
                ],
            ]);

        Notification::assertNothingSent();
    }

    public function test_absent_attendance_notification_has_correct_data(): void
    {
        Notification::fake();

        $admin = User::factory()->administrator()->create();
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);

        $this->actingAs($admin)
            ->post(route('attendance.store'), [
                'date' => '2026-05-14',
                'records' => [
                    $student->id => ['status' => AttendanceStatus::ABSENT->value],
                ],
            ]);

        Notification::assertSentTo(
            $guardian,
            StudentAbsentNotification::class,
            function (StudentAbsentNotification $notification) use ($student, $admin) {
                $data = $notification->toArray($student);

                return $data['student_name'] === $student->name
                    && $data['date'] === '2026-05-14'
                    && $data['status'] === AttendanceStatus::ABSENT->value
                    && $data['recorded_by'] === $admin->name;
            }
        );
    }

    // ── T6.3: Payment overdue notifications ───────────────────────────────────

    public function test_mark_overdue_command_sends_notification_to_guardian(): void
    {
        Notification::fake();

        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);
        Payment::factory()->create([
            'student_id' => $student->id,
            'status' => PaymentStatus::UNPAID->value,
            'due_date' => now()->subDay()->toDateString(),
            'notified_at' => null,
        ]);

        $this->artisan('payments:mark-overdue')->assertSuccessful();

        Notification::assertSentTo($guardian, PaymentOverdueNotification::class);
    }

    public function test_mark_overdue_command_sends_notification_to_admins(): void
    {
        Notification::fake();

        $admin = User::factory()->administrator()->create();
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);
        Payment::factory()->create([
            'student_id' => $student->id,
            'status' => PaymentStatus::UNPAID->value,
            'due_date' => now()->subDay()->toDateString(),
            'notified_at' => null,
        ]);

        $this->artisan('payments:mark-overdue')->assertSuccessful();

        Notification::assertSentTo($admin, PaymentOverdueNotification::class);
    }

    public function test_mark_overdue_is_idempotent_does_not_re_notify(): void
    {
        Notification::fake();

        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);
        Payment::factory()->create([
            'student_id' => $student->id,
            'status' => PaymentStatus::UNPAID->value,
            'due_date' => now()->subDay()->toDateString(),
            'notified_at' => now()->subHour(), // already notified
        ]);

        $this->artisan('payments:mark-overdue')->assertSuccessful();

        Notification::assertNothingSent();
    }

    // ── T6.4: Learning Progress notifications ─────────────────────────────────

    public function test_teacher_creating_progress_notifies_guardian(): void
    {
        Notification::fake();

        $teacher = User::factory()->teacher()->create();
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create([
            'student_guardian_id' => $guardian->id,
            'status' => 'active',
        ]);

        $this->actingAs($teacher)
            ->post(route('learning-progress.store'), [
                'student_id' => $student->id,
                'subject' => 'Al-Quran',
                'milestone' => 'Surah Al-Fatihah',
                'date' => '2026-05-14',
            ]);

        Notification::assertSentTo($guardian, NewLearningProgressNotification::class);
    }

    // ── T6.5: Email notification preference ──────────────────────────────────

    public function test_user_with_email_notifications_off_does_not_receive_email(): void
    {
        Mail::fake();
        Notification::fake();

        $guardian = User::factory()->studentGuardian()->create([
            'email_notifications' => false,
        ]);

        $guardian->notify(new StudentAbsentNotification('Test', '2026-05-14', 'absent', 'Recorder'));

        Notification::assertSentTo(
            $guardian,
            StudentAbsentNotification::class,
            fn (StudentAbsentNotification $n, array $channels) => ! in_array('mail', $channels)
        );
    }

    public function test_user_with_email_notifications_on_receives_email(): void
    {
        Mail::fake();
        Notification::fake();

        $guardian = User::factory()->studentGuardian()->create([
            'email_notifications' => true,
        ]);

        $guardian->notify(new StudentAbsentNotification('Test', '2026-05-14', 'absent', 'Recorder'));

        Notification::assertSentTo(
            $guardian,
            StudentAbsentNotification::class,
            fn (StudentAbsentNotification $n, array $channels) => in_array('mail', $channels)
        );
    }

    public function test_profile_update_can_toggle_email_notifications_off(): void
    {
        $user = User::factory()->administrator()->create(['email_notifications' => true]);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'gender' => 'male',
                'phone' => '08123456789',
                // email_notifications checkbox absent = false
            ])
            ->assertRedirect();

        $this->assertFalse($user->fresh()->email_notifications);
    }

    public function test_profile_update_can_toggle_email_notifications_on(): void
    {
        $user = User::factory()->administrator()->create(['email_notifications' => false]);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'gender' => 'male',
                'phone' => '08123456789',
                'email_notifications' => '1',
            ])
            ->assertRedirect();

        $this->assertTrue($user->fresh()->email_notifications);
    }
}
