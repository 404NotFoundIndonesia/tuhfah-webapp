<?php

namespace Tests\Unit;

use App\Enum\AttendanceStatus;
use App\Enum\PaymentStatus;
use App\Enum\StudentStatus;
use App\Models\Attendance;
use App\Models\LearningProgress;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService();
    }

    // ── Shape tests ───────────────────────────────────────────────────────────

    public function test_admin_stats_have_required_keys(): void
    {
        $admin = User::factory()->administrator()->create();
        $stats = $this->service->stats($admin);

        $this->assertArrayHasKey('total_active_students', $stats);
        $this->assertArrayHasKey('attendance_rate_today', $stats);
        $this->assertArrayHasKey('total_recorded_today', $stats);
        $this->assertArrayHasKey('total_unpaid_payments', $stats);
        $this->assertArrayHasKey('total_overdue_payments', $stats);
    }

    public function test_teacher_stats_have_required_keys(): void
    {
        $teacher = User::factory()->teacher()->create();
        $stats = $this->service->stats($teacher);

        $this->assertArrayHasKey('total_active_students', $stats);
        $this->assertArrayHasKey('progress_students_this_month', $stats);
    }

    public function test_guardian_stats_have_required_keys(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value]);
        $stats = $this->service->stats($guardian);

        $this->assertArrayHasKey('total_active_students', $stats);
        $this->assertArrayHasKey('child_attendance_rate_this_month', $stats);
        $this->assertArrayHasKey('outstanding_payment_count', $stats);
    }

    // ── Correctness tests ─────────────────────────────────────────────────────

    public function test_total_active_students_counts_only_active(): void
    {
        Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);
        Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);
        Student::factory()->create(['status' => StudentStatus::GRADUATED->value]);

        $stats = $this->service->stats(User::factory()->administrator()->create());

        $this->assertSame(2, $stats['total_active_students']);
    }

    public function test_admin_unpaid_and_overdue_counts(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value]);
        $admin = User::factory()->administrator()->create();

        Payment::factory()->create(['student_id' => $student->id, 'status' => PaymentStatus::UNPAID->value, 'recorded_by' => $admin->id]);
        Payment::factory()->create(['student_id' => $student->id, 'status' => PaymentStatus::UNPAID->value, 'recorded_by' => $admin->id]);
        Payment::factory()->create(['student_id' => $student->id, 'status' => PaymentStatus::OVERDUE->value, 'recorded_by' => $admin->id]);

        $stats = $this->service->stats($admin);

        $this->assertSame(2, $stats['total_unpaid_payments']);
        $this->assertSame(1, $stats['total_overdue_payments']);
    }

    public function test_teacher_progress_students_this_month(): void
    {
        $teacher = User::factory()->teacher()->create();
        $guardian = User::factory()->studentGuardian()->create();
        $s1 = Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value]);
        $s2 = Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value]);

        // Two entries for s1, one for s2 — distinct count should be 2
        LearningProgress::factory()->create(['teacher_id' => $teacher->id, 'student_id' => $s1->id, 'date' => now()]);
        LearningProgress::factory()->create(['teacher_id' => $teacher->id, 'student_id' => $s1->id, 'date' => now()]);
        LearningProgress::factory()->create(['teacher_id' => $teacher->id, 'student_id' => $s2->id, 'date' => now()]);
        // Last month — should not be counted
        LearningProgress::factory()->create(['teacher_id' => $teacher->id, 'student_id' => $s2->id, 'date' => now()->subMonth()]);

        $stats = $this->service->stats($teacher);

        $this->assertSame(2, $stats['progress_students_this_month']);
    }

    public function test_guardian_outstanding_payment_count(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value]);
        $admin = User::factory()->administrator()->create();

        Payment::factory()->create(['student_id' => $student->id, 'status' => PaymentStatus::UNPAID->value, 'recorded_by' => $admin->id]);
        Payment::factory()->create(['student_id' => $student->id, 'status' => PaymentStatus::OVERDUE->value, 'recorded_by' => $admin->id]);
        Payment::factory()->create(['student_id' => $student->id, 'status' => PaymentStatus::PAID->value, 'recorded_by' => $admin->id]);

        $stats = $this->service->stats($guardian);

        $this->assertSame(2, $stats['outstanding_payment_count']);
    }

    public function test_guardian_attendance_rate_this_month(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value]);

        Attendance::factory()->create(['attendable_type' => Student::class, 'attendable_id' => $student->id, 'status' => AttendanceStatus::PRESENT, 'date' => now()->startOfMonth()]);
        Attendance::factory()->create(['attendable_type' => Student::class, 'attendable_id' => $student->id, 'status' => AttendanceStatus::PRESENT, 'date' => now()->startOfMonth()->addDay()]);
        Attendance::factory()->create(['attendable_type' => Student::class, 'attendable_id' => $student->id, 'status' => AttendanceStatus::ABSENT, 'date' => now()->startOfMonth()->addDays(2)]);

        $stats = $this->service->stats($guardian);

        $this->assertEqualsWithDelta(66.7, $stats['child_attendance_rate_this_month'], 0.2);
    }

    public function test_guardian_without_child_returns_nulls(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $stats = $this->service->stats($guardian);

        $this->assertNull($stats['child_attendance_rate_this_month']);
        $this->assertSame(0, $stats['outstanding_payment_count']);
    }
}
