<?php

namespace Tests\Feature;

use App\Enum\AttendanceStatus;
use App\Enum\StudentStatus;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_attendance_index(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('attendance.index'))
            ->assertOk();
    }

    public function test_teacher_can_view_attendance_index(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('attendance.index'))
            ->assertOk();
    }

    public function test_guardian_cannot_view_attendance_index(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('attendance.index'))
            ->assertForbidden();
    }

    // ── Create form ───────────────────────────────────────────────────────────

    public function test_admin_can_view_create_form(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('attendance.create'))
            ->assertOk();
    }

    public function test_teacher_can_view_create_form(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('attendance.create'))
            ->assertOk();
    }

    public function test_guardian_cannot_view_create_form(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('attendance.create'))
            ->assertForbidden();
    }

    // ── Store: happy path ─────────────────────────────────────────────────────

    public function test_admin_can_store_attendance_for_students(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($admin)
            ->post(route('attendance.store'), [
                'date' => '2025-03-10',
                'records' => [
                    $student->id => ['status' => 'present', 'notes' => ''],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'attendable_type' => Student::class,
            'attendable_id' => $student->id,
            'date' => '2025-03-10',
            'status' => 'present',
            'recorded_by' => $admin->id,
        ]);
    }

    public function test_teacher_can_store_attendance_for_students(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($teacher)
            ->post(route('attendance.store'), [
                'date' => '2025-03-10',
                'records' => [
                    $student->id => ['status' => 'absent', 'notes' => 'No reason given'],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'attendable_type' => Student::class,
            'attendable_id' => $student->id,
            'status' => 'absent',
        ]);
    }

    public function test_store_updates_existing_record_for_same_date(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        // First submission
        $this->actingAs($admin)->post(route('attendance.store'), [
            'date' => '2025-03-10',
            'records' => [$student->id => ['status' => 'present']],
        ]);

        // Second submission — should upsert
        $this->actingAs($admin)->post(route('attendance.store'), [
            'date' => '2025-03-10',
            'records' => [$student->id => ['status' => 'absent']],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', ['status' => 'absent']);
    }

    // ── Store: validation ─────────────────────────────────────────────────────

    public function test_store_requires_date(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($admin)
            ->post(route('attendance.store'), [
                'records' => [$student->id => ['status' => 'present']],
            ])
            ->assertSessionHasErrors('date');
    }

    public function test_store_requires_valid_status(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($admin)
            ->post(route('attendance.store'), [
                'date' => '2025-03-10',
                'records' => [$student->id => ['status' => 'invalid_status']],
            ])
            ->assertSessionHasErrors();
    }

    // ── Store: authorization ──────────────────────────────────────────────────

    public function test_guardian_cannot_store_attendance(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($guardian)
            ->post(route('attendance.store'), [
                'date' => '2025-03-10',
                'records' => [$student->id => ['status' => 'present']],
            ])
            ->assertForbidden();
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_admin_can_view_attendance_show(): void
    {
        $admin = User::factory()->administrator()->create();
        $attendance = Attendance::factory()->forStudent()->create(['recorded_by' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('attendance.show', $attendance))
            ->assertOk();
    }

    // ── Self-attendance: view ─────────────────────────────────────────────────

    public function test_teacher_can_view_self_attendance_form(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('attendance.self'))
            ->assertOk();
    }

    public function test_admin_cannot_view_self_attendance_form(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('attendance.self'))
            ->assertForbidden();
    }

    public function test_guardian_cannot_view_self_attendance_form(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('attendance.self'))
            ->assertForbidden();
    }

    // ── Self-attendance: store ────────────────────────────────────────────────

    public function test_teacher_can_submit_self_attendance(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->post(route('attendance.self.store'), [
                'date' => '2025-03-10',
                'status' => 'present',
                'notes' => 'On time',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('attendance.self'));

        $this->assertDatabaseHas('attendances', [
            'attendable_type' => User::class,
            'attendable_id' => $teacher->id,
            'date' => '2025-03-10',
            'status' => 'present',
        ]);
    }

    public function test_teacher_self_attendance_sets_correct_morph_type(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->post(route('attendance.self.store'), [
                'date' => '2025-03-11',
                'status' => 'sick',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendances', [
            'attendable_type' => User::class,
            'attendable_id' => $teacher->id,
            'status' => 'sick',
        ]);
    }

    public function test_teacher_cannot_submit_self_attendance_twice_for_same_date(): void
    {
        $teacher = User::factory()->teacher()->create();

        // First submission
        $this->actingAs($teacher)->post(route('attendance.self.store'), [
            'date' => '2025-03-10',
            'status' => 'present',
        ]);

        // Second submission — should fail
        $this->actingAs($teacher)
            ->post(route('attendance.self.store'), [
                'date' => '2025-03-10',
                'status' => 'absent',
            ])
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_admin_cannot_submit_self_attendance(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('attendance.self.store'), [
                'date' => '2025-03-10',
                'status' => 'present',
            ])
            ->assertForbidden();
    }

    // ── Guardian: child attendance ────────────────────────────────────────────

    public function test_guardian_can_view_child_attendance(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);

        Attendance::factory()->forStudent($student)->create();

        $this->actingAs($guardian)
            ->get(route('attendance.guardian'))
            ->assertOk();
    }

    public function test_admin_cannot_access_guardian_attendance_route(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('attendance.guardian'))
            ->assertForbidden();
    }

    public function test_teacher_cannot_access_guardian_attendance_route(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('attendance.guardian'))
            ->assertForbidden();
    }

    // ── Summary endpoint ──────────────────────────────────────────────────────

    public function test_admin_can_get_monthly_summary(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create();

        Attendance::factory()->forStudent($student)->create([
            'date' => '2025-03-05',
            'status' => AttendanceStatus::PRESENT->value,
            'recorded_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->getJson(route('attendance.summary', [
                'student_id' => $student->id,
                'year' => 2025,
                'month' => 3,
            ]))
            ->assertOk()
            ->assertJsonStructure(['present', 'absent', 'sick', 'permitted', 'total_days'])
            ->assertJsonPath('present', 1)
            ->assertJsonPath('total_days', 1);
    }

    public function test_guardian_can_get_summary_for_own_child(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);
        $recorder = User::factory()->administrator()->create();

        Attendance::factory()->forStudent($student)->create([
            'date' => '2025-03-05',
            'status' => AttendanceStatus::ABSENT->value,
            'recorded_by' => $recorder->id,
        ]);

        $this->actingAs($guardian)
            ->getJson(route('attendance.summary', [
                'student_id' => $student->id,
                'year' => 2025,
                'month' => 3,
            ]))
            ->assertOk()
            ->assertJsonPath('absent', 1);
    }

    public function test_guardian_cannot_get_summary_for_another_child(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $otherStudent = Student::factory()->create(); // linked to different guardian

        $this->actingAs($guardian)
            ->getJson(route('attendance.summary', [
                'student_id' => $otherStudent->id,
                'year' => 2025,
                'month' => 3,
            ]))
            ->assertForbidden();
    }

    public function test_summary_requires_student_id(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->getJson(route('attendance.summary', ['year' => 2025, 'month' => 3]))
            ->assertUnprocessable();
    }
}
