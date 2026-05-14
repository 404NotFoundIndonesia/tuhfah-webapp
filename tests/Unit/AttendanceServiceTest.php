<?php

namespace Tests\Unit;

use App\Enum\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AttendanceService;
    }

    public function test_returns_zero_counts_when_no_records(): void
    {
        $student = Student::factory()->create();

        $summary = $this->service->monthlySummary($student, 2025, 1);

        $this->assertSame(0, $summary['present']);
        $this->assertSame(0, $summary['absent']);
        $this->assertSame(0, $summary['sick']);
        $this->assertSame(0, $summary['permitted']);
        $this->assertSame(0, $summary['total_days']);
    }

    public function test_counts_each_status_correctly(): void
    {
        $student = Student::factory()->create();
        $recorder = User::factory()->administrator()->create();

        $base = ['attendable_type' => Student::class, 'attendable_id' => $student->id, 'recorded_by' => $recorder->id];

        Attendance::create(array_merge($base, ['date' => '2025-03-01', 'status' => AttendanceStatus::PRESENT->value]));
        Attendance::create(array_merge($base, ['date' => '2025-03-02', 'status' => AttendanceStatus::PRESENT->value]));
        Attendance::create(array_merge($base, ['date' => '2025-03-03', 'status' => AttendanceStatus::ABSENT->value]));
        Attendance::create(array_merge($base, ['date' => '2025-03-04', 'status' => AttendanceStatus::SICK->value]));
        Attendance::create(array_merge($base, ['date' => '2025-03-05', 'status' => AttendanceStatus::PERMITTED->value]));

        $summary = $this->service->monthlySummary($student, 2025, 3);

        $this->assertSame(2, $summary['present']);
        $this->assertSame(1, $summary['absent']);
        $this->assertSame(1, $summary['sick']);
        $this->assertSame(1, $summary['permitted']);
        $this->assertSame(5, $summary['total_days']);
    }

    public function test_ignores_records_outside_the_requested_month(): void
    {
        $student = Student::factory()->create();
        $recorder = User::factory()->administrator()->create();

        $base = ['attendable_type' => Student::class, 'attendable_id' => $student->id, 'recorded_by' => $recorder->id];

        // March record — should be counted
        Attendance::create(array_merge($base, ['date' => '2025-03-10', 'status' => AttendanceStatus::PRESENT->value]));
        // April record — should be excluded
        Attendance::create(array_merge($base, ['date' => '2025-04-01', 'status' => AttendanceStatus::ABSENT->value]));

        $summary = $this->service->monthlySummary($student, 2025, 3);

        $this->assertSame(1, $summary['total_days']);
        $this->assertSame(1, $summary['present']);
        $this->assertSame(0, $summary['absent']);
    }

    public function test_ignores_records_for_other_students(): void
    {
        $studentA = Student::factory()->create();
        $studentB = Student::factory()->create();
        $recorder = User::factory()->administrator()->create();

        Attendance::create([
            'attendable_type' => Student::class,
            'attendable_id' => $studentB->id,
            'date' => '2025-03-01',
            'status' => AttendanceStatus::ABSENT->value,
            'recorded_by' => $recorder->id,
        ]);

        $summary = $this->service->monthlySummary($studentA, 2025, 3);

        $this->assertSame(0, $summary['total_days']);
    }

    public function test_ignores_teacher_self_attendance_records(): void
    {
        $student = Student::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $recorder = User::factory()->administrator()->create();

        // Teacher self-attendance — should NOT be counted in student summary
        Attendance::create([
            'attendable_type' => User::class,
            'attendable_id' => $teacher->id,
            'date' => '2025-03-01',
            'status' => AttendanceStatus::PRESENT->value,
            'recorded_by' => $teacher->id,
        ]);

        $summary = $this->service->monthlySummary($student, 2025, 3);

        $this->assertSame(0, $summary['total_days']);
    }

    public function test_summary_has_required_keys(): void
    {
        $student = Student::factory()->create();

        $summary = $this->service->monthlySummary($student, 2025, 1);

        $this->assertArrayHasKey('present', $summary);
        $this->assertArrayHasKey('absent', $summary);
        $this->assertArrayHasKey('sick', $summary);
        $this->assertArrayHasKey('permitted', $summary);
        $this->assertArrayHasKey('total_days', $summary);
    }
}
