<?php

namespace Tests\Feature;

use App\Enum\StudentStatus;
use App\Models\LearningProgress;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningProgressTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_index(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('learning-progress.index'))
            ->assertOk();
    }

    public function test_teacher_can_view_index(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('learning-progress.index'))
            ->assertOk();
    }

    public function test_guardian_cannot_view_index(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('learning-progress.index'))
            ->assertForbidden();
    }

    // ── T3.3: Admin sees all, teacher sees own only ───────────────────────────

    public function test_admin_index_datatable_returns_all_records(): void
    {
        $admin = User::factory()->administrator()->create();
        $teacherA = User::factory()->teacher()->create();
        $teacherB = User::factory()->teacher()->create();

        LearningProgress::factory()->create(['teacher_id' => $teacherA->id]);
        LearningProgress::factory()->create(['teacher_id' => $teacherB->id]);

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('learning-progress.index'));

        $response->assertOk();
        $this->assertSame(2, $response->json('recordsTotal'));
    }

    public function test_teacher_index_datatable_returns_only_own_records(): void
    {
        $teacher = User::factory()->teacher()->create();
        $other = User::factory()->teacher()->create();

        LearningProgress::factory()->create(['teacher_id' => $teacher->id]);
        LearningProgress::factory()->create(['teacher_id' => $other->id]);

        $response = $this->actingAs($teacher)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('learning-progress.index'));

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsTotal'));
    }

    // ── Create form ───────────────────────────────────────────────────────────

    public function test_teacher_can_view_create_form(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('learning-progress.create'))
            ->assertOk();
    }

    public function test_guardian_cannot_view_create_form(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('learning-progress.create'))
            ->assertForbidden();
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_teacher_can_store_progress_record(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($teacher)
            ->post(route('learning-progress.store'), [
                'student_id' => $student->id,
                'date' => '2025-03-10',
                'subject' => 'Al-Quran',
                'milestone' => 'Surah Al-Baqarah ayat 1-5',
                'score' => 90,
                'notes' => 'Good progress',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('learning-progress.index'));

        $this->assertDatabaseHas('learning_progress', [
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'subject' => 'Al-Quran',
            'score' => 90,
        ]);
    }

    public function test_teacher_id_is_auto_assigned_on_store(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($teacher)->post(route('learning-progress.store'), [
            'student_id' => $student->id,
            'date' => '2025-03-10',
            'subject' => 'Hafalan',
            'milestone' => 'Surah Al-Ikhlas',
        ]);

        $this->assertDatabaseHas('learning_progress', [
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_admin_can_store_progress_record(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($admin)
            ->post(route('learning-progress.store'), [
                'student_id' => $student->id,
                'date' => '2025-03-10',
                'subject' => 'Fiqih',
                'milestone' => 'Bab Thaharah',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('learning_progress', ['subject' => 'Fiqih']);
    }

    public function test_store_requires_active_student(): void
    {
        $teacher = User::factory()->teacher()->create();
        $inactiveStudent = Student::factory()->create(['status' => StudentStatus::GRADUATED->value]);

        $this->actingAs($teacher)
            ->post(route('learning-progress.store'), [
                'student_id' => $inactiveStudent->id,
                'date' => '2025-03-10',
                'subject' => 'Al-Quran',
                'milestone' => 'Some milestone',
            ])
            ->assertSessionHasErrors('student_id');
    }

    public function test_store_requires_subject_and_milestone(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($teacher)
            ->post(route('learning-progress.store'), [
                'student_id' => $student->id,
                'date' => '2025-03-10',
            ])
            ->assertSessionHasErrors(['subject', 'milestone']);
    }

    public function test_guardian_cannot_store_progress(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);

        $this->actingAs($guardian)
            ->post(route('learning-progress.store'), [
                'student_id' => $student->id,
                'date' => '2025-03-10',
                'subject' => 'Al-Quran',
                'milestone' => 'Some milestone',
            ])
            ->assertForbidden();
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_teacher_can_view_own_record(): void
    {
        $teacher = User::factory()->teacher()->create();
        $progress = LearningProgress::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->get(route('learning-progress.show', $progress))
            ->assertOk();
    }

    public function test_teacher_cannot_view_other_teachers_record(): void
    {
        $teacher = User::factory()->teacher()->create();
        $other = User::factory()->teacher()->create();
        $progress = LearningProgress::factory()->create(['teacher_id' => $other->id]);

        $this->actingAs($teacher)
            ->get(route('learning-progress.show', $progress))
            ->assertForbidden();
    }

    public function test_admin_can_view_any_record(): void
    {
        $admin = User::factory()->administrator()->create();
        $progress = LearningProgress::factory()->create();

        $this->actingAs($admin)
            ->get(route('learning-progress.show', $progress))
            ->assertOk();
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function test_teacher_can_edit_own_record(): void
    {
        $teacher = User::factory()->teacher()->create();
        $progress = LearningProgress::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->get(route('learning-progress.edit', $progress))
            ->assertOk();
    }

    public function test_teacher_cannot_edit_other_teachers_record(): void
    {
        $teacher = User::factory()->teacher()->create();
        $other = User::factory()->teacher()->create();
        $progress = LearningProgress::factory()->create(['teacher_id' => $other->id]);

        $this->actingAs($teacher)
            ->get(route('learning-progress.edit', $progress))
            ->assertForbidden();
    }

    public function test_teacher_can_update_own_record(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);
        $progress = LearningProgress::factory()->create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($teacher)
            ->put(route('learning-progress.update', $progress), [
                'student_id' => $student->id,
                'date' => '2025-04-01',
                'subject' => 'Updated Subject',
                'milestone' => 'Updated Milestone',
                'score' => 95,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('learning-progress.index'));

        $this->assertDatabaseHas('learning_progress', [
            'id' => $progress->id,
            'subject' => 'Updated Subject',
            'score' => 95,
        ]);
    }

    public function test_teacher_cannot_update_other_teachers_record(): void
    {
        $teacher = User::factory()->teacher()->create();
        $other = User::factory()->teacher()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);
        $progress = LearningProgress::factory()->create([
            'teacher_id' => $other->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($teacher)
            ->put(route('learning-progress.update', $progress), [
                'student_id' => $student->id,
                'date' => '2025-04-01',
                'subject' => 'Hacked',
                'milestone' => 'Hacked milestone',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_update_any_record(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create(['status' => StudentStatus::ACTIVE->value]);
        $progress = LearningProgress::factory()->create(['student_id' => $student->id]);

        $this->actingAs($admin)
            ->put(route('learning-progress.update', $progress), [
                'student_id' => $student->id,
                'date' => '2025-04-01',
                'subject' => 'Admin Updated',
                'milestone' => 'Admin Milestone',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('learning_progress', ['subject' => 'Admin Updated']);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_teacher_can_delete_own_record(): void
    {
        $teacher = User::factory()->teacher()->create();
        $progress = LearningProgress::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->delete(route('learning-progress.destroy', $progress))
            ->assertRedirect();

        $this->assertDatabaseMissing('learning_progress', ['id' => $progress->id]);
    }

    public function test_teacher_cannot_delete_other_teachers_record(): void
    {
        $teacher = User::factory()->teacher()->create();
        $other = User::factory()->teacher()->create();
        $progress = LearningProgress::factory()->create(['teacher_id' => $other->id]);

        $this->actingAs($teacher)
            ->delete(route('learning-progress.destroy', $progress))
            ->assertForbidden();

        $this->assertDatabaseHas('learning_progress', ['id' => $progress->id]);
    }

    public function test_admin_can_delete_any_record(): void
    {
        $admin = User::factory()->administrator()->create();
        $progress = LearningProgress::factory()->create();

        $this->actingAs($admin)
            ->delete(route('learning-progress.destroy', $progress))
            ->assertRedirect();

        $this->assertDatabaseMissing('learning_progress', ['id' => $progress->id]);
    }

    // ── T3.4: Guardian view child progress ────────────────────────────────────

    public function test_guardian_can_view_child_progress(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);
        LearningProgress::factory()->create(['student_id' => $student->id]);

        $this->actingAs($guardian)
            ->get(route('learning-progress.guardian'))
            ->assertOk();
    }

    public function test_admin_cannot_access_guardian_progress_route(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('learning-progress.guardian'))
            ->assertForbidden();
    }

    public function test_teacher_cannot_access_guardian_progress_route(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('learning-progress.guardian'))
            ->assertForbidden();
    }

    // ── T3.5: Chart data endpoint ─────────────────────────────────────────────

    public function test_admin_can_get_chart_data(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create();

        LearningProgress::factory()->create([
            'student_id' => $student->id,
            'date' => '2025-03-10',
            'subject' => 'Al-Quran',
            'score' => 85,
        ]);

        $this->actingAs($admin)
            ->getJson(route('learning-progress.chart-data', ['student_id' => $student->id]))
            ->assertOk()
            ->assertJsonStructure([['date', 'score', 'subject']])
            ->assertJsonFragment(['score' => 85.0]);
    }

    public function test_chart_data_filtered_by_subject(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create();

        LearningProgress::factory()->create([
            'student_id' => $student->id,
            'subject' => 'Al-Quran',
            'score' => 85,
            'date' => '2025-03-01',
        ]);
        LearningProgress::factory()->create([
            'student_id' => $student->id,
            'subject' => 'Hafalan',
            'score' => 90,
            'date' => '2025-03-02',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('learning-progress.chart-data', [
                'student_id' => $student->id,
                'subject' => 'Al-Quran',
            ]));

        $response->assertOk();
        $this->assertCount(1, $response->json());
        $this->assertSame('Al-Quran', $response->json()[0]['subject']);
    }

    public function test_chart_data_excludes_records_without_score(): void
    {
        $admin = User::factory()->administrator()->create();
        $student = Student::factory()->create();

        LearningProgress::factory()->create(['student_id' => $student->id, 'score' => null, 'date' => '2025-03-01']);
        LearningProgress::factory()->create(['student_id' => $student->id, 'score' => 80, 'date' => '2025-03-02']);

        $response = $this->actingAs($admin)
            ->getJson(route('learning-progress.chart-data', ['student_id' => $student->id]));

        $response->assertOk();
        $this->assertCount(1, $response->json());
    }

    public function test_guardian_can_get_chart_data_for_own_child(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $student = Student::factory()->create(['student_guardian_id' => $guardian->id]);

        LearningProgress::factory()->create(['student_id' => $student->id, 'score' => 88, 'date' => '2025-03-01']);

        $this->actingAs($guardian)
            ->getJson(route('learning-progress.chart-data', ['student_id' => $student->id]))
            ->assertOk()
            ->assertJsonFragment(['score' => 88.0]);
    }

    public function test_guardian_cannot_get_chart_data_for_other_child(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $otherStudent = Student::factory()->create();

        $this->actingAs($guardian)
            ->getJson(route('learning-progress.chart-data', ['student_id' => $otherStudent->id]))
            ->assertForbidden();
    }

    public function test_chart_data_requires_student_id(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->getJson(route('learning-progress.chart-data'))
            ->assertUnprocessable();
    }
}
