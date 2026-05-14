<?php

namespace Tests\Feature;

use App\Enum\PaymentStatus;
use App\Enum\StudentStatus;
use App\Models\Attendance;
use App\Models\Honorarium;
use App\Models\LearningProgress;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_administrator_can_view_attendance_report(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('report.attendance'))
            ->assertOk();
    }

    public function test_owner_can_view_attendance_report(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get(route('report.attendance'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_attendance_report(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('report.attendance'))
            ->assertForbidden();
    }

    public function test_guardian_cannot_view_attendance_report(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('report.attendance'))
            ->assertForbidden();
    }

    // ── Attendance report data ─────────────────────────────────────────────────

    public function test_attendance_report_includes_all_active_students(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $admin = User::factory()->administrator()->create();

        Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value, 'name' => 'Alice']);
        Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value, 'name' => 'Bob']);
        // Graduated — should NOT appear
        Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::GRADUATED->value, 'name' => 'Zara', 'departure_date' => now()]);

        $response = $this->actingAs($admin)
            ->get(route('report.attendance', ['period' => 'monthly', 'date' => now()->toDateString()]))
            ->assertOk();

        $response->assertSee('Alice')->assertSee('Bob')->assertDontSee('Zara');
    }

    // ── Attendance export ──────────────────────────────────────────────────────

    public function test_attendance_export_xlsx_returns_file(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('report.attendance.export', ['period' => 'monthly', 'date' => now()->toDateString(), 'format' => 'xlsx']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_attendance_export_pdf_returns_file(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('report.attendance.export', ['period' => 'monthly', 'date' => now()->toDateString(), 'format' => 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    // ── Financial report ──────────────────────────────────────────────────────

    public function test_administrator_can_view_finance_report(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('report.finance'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_finance_report(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('report.finance'))
            ->assertForbidden();
    }

    public function test_finance_report_totals_match_fixture_data(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value]);
        $admin = User::factory()->administrator()->create();

        $period = '2026-05';

        Payment::factory()->create(['student_id' => $student->id, 'period' => $period, 'amount' => 200000, 'status' => PaymentStatus::PAID->value, 'recorded_by' => $admin->id, 'paid_at' => now()]);
        Payment::factory()->create(['student_id' => $student->id, 'period' => $period, 'amount' => 150000, 'status' => PaymentStatus::UNPAID->value, 'recorded_by' => $admin->id]);
        Payment::factory()->create(['student_id' => $student->id, 'period' => $period, 'amount' => 100000, 'status' => PaymentStatus::OVERDUE->value, 'recorded_by' => $admin->id]);

        $teacher = User::factory()->teacher()->create();
        Honorarium::factory()->create(['teacher_id' => $teacher->id, 'period' => $period, 'amount' => 50000, 'status' => PaymentStatus::PAID->value, 'paid_at' => now(), 'recorded_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->get(route('report.finance', ['period' => $period]))
            ->assertOk();

        $response->assertSee('200');  // collected
        $response->assertSee('150');  // outstanding
        $response->assertSee('50');   // honorarium
    }

    public function test_finance_report_handles_period_with_no_records(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('report.finance', ['period' => '2000-01']))
            ->assertOk();
    }

    // ── Finance export ────────────────────────────────────────────────────────

    public function test_finance_export_xlsx_returns_file(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('report.finance.export', ['period' => '2026-05', 'format' => 'xlsx']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_finance_export_pdf_returns_file(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('report.finance.export', ['period' => '2026-05', 'format' => 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    // ── Progress report ───────────────────────────────────────────────────────

    public function test_administrator_can_view_progress_report(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('report.progress'))
            ->assertOk();
    }

    public function test_teacher_can_view_progress_report(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('report.progress'))
            ->assertOk();
    }

    public function test_guardian_cannot_view_progress_report(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('report.progress'))
            ->assertForbidden();
    }

    public function test_progress_report_scoped_by_date_range(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value]);
        $teacher = User::factory()->teacher()->create();

        LearningProgress::factory()->create(['student_id' => $student->id, 'teacher_id' => $teacher->id, 'date' => '2026-03-01', 'subject' => 'Iqra', 'milestone' => 'In range']);
        LearningProgress::factory()->create(['student_id' => $student->id, 'teacher_id' => $teacher->id, 'date' => '2026-01-01', 'subject' => 'Tajwid', 'milestone' => 'Out of range']);

        $response = $this->actingAs(User::factory()->administrator()->create())
            ->get(route('report.progress', ['student_id' => $student->id, 'from' => '2026-02-01', 'to' => '2026-04-01']))
            ->assertOk();

        $response->assertSee('In range')->assertDontSee('Out of range');
    }

    public function test_teacher_only_sees_own_students_in_progress_report(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $myStudent = Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value, 'name' => 'My Student']);
        $otherStudent = Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value, 'name' => 'Other Student']);

        $teacher = User::factory()->teacher()->create();
        $otherTeacher = User::factory()->teacher()->create();

        LearningProgress::factory()->create(['student_id' => $myStudent->id, 'teacher_id' => $teacher->id, 'date' => now()]);
        LearningProgress::factory()->create(['student_id' => $otherStudent->id, 'teacher_id' => $otherTeacher->id, 'date' => now()]);

        $response = $this->actingAs($teacher)
            ->get(route('report.progress'))
            ->assertOk();

        $response->assertSee('My Student')->assertDontSee('Other Student');
    }

    // ── Progress export ───────────────────────────────────────────────────────

    public function test_progress_export_pdf_returns_file(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id, 'status' => StudentStatus::ACTIVE->value]);

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('report.progress.export', ['student_id' => $student->id, 'from' => '2026-01-01', 'to' => '2026-12-31', 'format' => 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
