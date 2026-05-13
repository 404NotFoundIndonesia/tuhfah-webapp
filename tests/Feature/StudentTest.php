<?php

namespace Tests\Feature;

use App\Enum\StudentStatus;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        $guardian = User::factory()->studentGuardian()->create();

        return array_merge([
            'name' => 'Ahmad Fauzan',
            'nickname' => 'Fauzan',
            'birthplace' => 'Banjarmasin',
            'birthdate' => '2015-06-01',
            'gender' => 'male',
            'status' => StudentStatus::CANDIDATE->value,
            'admission_date' => '2023-01-01',
            'departure_date' => null,
            'student_guardian_id' => $guardian->id,
        ], $overrides);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_owner_can_view_student_index(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get(route('student.index'))
            ->assertOk();
    }

    public function test_administrator_can_view_student_index(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('student.index'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_student_index(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('student.index'))
            ->assertForbidden();
    }

    public function test_guardian_cannot_view_student_index(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->get(route('student.index'))
            ->assertForbidden();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_administrator_can_view_create_form(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('student.create'))
            ->assertOk();
    }

    public function test_teacher_cannot_view_create_form(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('student.create'))
            ->assertForbidden();
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_administrator_can_create_student(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('student.store'), $this->validPayload())
            ->assertRedirect(route('student.index'));

        $this->assertDatabaseHas('students', ['name' => 'Ahmad Fauzan']);
    }

    public function test_owner_can_create_student(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->post(route('student.store'), $this->validPayload(['name' => 'Siti Aisyah', 'gender' => 'female']))
            ->assertRedirect(route('student.index'));

        $this->assertDatabaseHas('students', ['name' => 'Siti Aisyah']);
    }

    public function test_teacher_cannot_create_student(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->post(route('student.store'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_guardian_cannot_create_student(): void
    {
        $this->actingAs(User::factory()->studentGuardian()->create())
            ->post(route('student.store'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_store_requires_name(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('student.store'), $this->validPayload(['name' => '']))
            ->assertSessionHasErrors('name');
    }

    public function test_store_requires_birthdate(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('student.store'), $this->validPayload(['birthdate' => '']))
            ->assertSessionHasErrors('birthdate');
    }

    public function test_store_requires_valid_guardian(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('student.store'), $this->validPayload(['student_guardian_id' => '']))
            ->assertSessionHasErrors('student_guardian_id');
    }

    public function test_male_student_gets_id_with_i_prefix(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('student.store'), $this->validPayload([
                'gender' => 'male',
                'student_id_number' => null,
            ]));

        $student = Student::where('name', 'Ahmad Fauzan')->first();
        $this->assertNotNull($student);
        $this->assertStringStartsWith('I', $student->student_id_number);
    }

    public function test_female_student_gets_id_with_a_prefix(): void
    {
        $guardian = User::factory()->studentGuardian()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('student.store'), $this->validPayload([
                'name' => 'Siti Aisyah',
                'gender' => 'female',
                'student_id_number' => null,
                'student_guardian_id' => $guardian->id,
            ]));

        $student = Student::where('name', 'Siti Aisyah')->first();
        $this->assertNotNull($student);
        $this->assertStringStartsWith('A', $student->student_id_number);
    }

    public function test_student_id_is_auto_generated_when_not_provided(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('student.store'), $this->validPayload());

        $student = Student::where('name', 'Ahmad Fauzan')->first();
        $this->assertNotNull($student->student_id_number);
        $this->assertNotEmpty($student->student_id_number);
    }

    public function test_student_ids_are_unique_for_sequential_students(): void
    {
        $admin = User::factory()->administrator()->create();
        $guardian = User::factory()->studentGuardian()->create();

        $this->actingAs($admin)->post(route('student.store'), $this->validPayload([
            'name' => 'Student One',
            'gender' => 'male',
            'student_guardian_id' => $guardian->id,
        ]));

        $this->actingAs($admin)->post(route('student.store'), $this->validPayload([
            'name' => 'Student Two',
            'gender' => 'male',
            'student_guardian_id' => $guardian->id,
        ]));

        $ids = Student::pluck('student_id_number');
        $this->assertCount(2, $ids->unique());
    }

    // ── Edit ─────────────────────────────────────────────────────────────────

    public function test_administrator_can_view_edit_form(): void
    {
        $student = Student::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('student.edit', $student))
            ->assertOk();
    }

    public function test_teacher_cannot_view_edit_form(): void
    {
        $student = Student::factory()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->get(route('student.edit', $student))
            ->assertForbidden();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_administrator_can_update_student(): void
    {
        $student = Student::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->patch(route('student.update', $student), $this->validPayload([
                'name' => 'Updated Name',
                'student_guardian_id' => $student->student_guardian_id,
            ]))
            ->assertRedirect();

        $this->assertSame('Updated Name', $student->fresh()->name);
    }

    public function test_teacher_cannot_update_student(): void
    {
        $student = Student::factory()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->patch(route('student.update', $student), $this->validPayload([
                'student_guardian_id' => $student->student_guardian_id,
            ]))
            ->assertForbidden();
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_administrator_can_delete_student(): void
    {
        $student = Student::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('student.destroy', $student))
            ->assertRedirect();

        $this->assertNull($student->fresh());
    }

    public function test_teacher_cannot_delete_student(): void
    {
        $student = Student::factory()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->delete(route('student.destroy', $student))
            ->assertForbidden();
    }

    public function test_guardian_cannot_delete_student(): void
    {
        $student = Student::factory()->create();

        $this->actingAs(User::factory()->studentGuardian()->create())
            ->delete(route('student.destroy', $student))
            ->assertForbidden();
    }
}
