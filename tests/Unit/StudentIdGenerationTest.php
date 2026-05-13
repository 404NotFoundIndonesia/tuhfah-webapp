<?php

namespace Tests\Unit;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentIdGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(string $gender, ?string $studentIdNumber = null): Student
    {
        $guardian = User::factory()->studentGuardian()->create();

        return Student::create([
            'student_id_number' => $studentIdNumber,
            'name' => 'Test Student',
            'birthdate' => '2015-01-01',
            'gender' => $gender,
            'status' => 'candidate',
            'admission_date' => now()->toDateString(),
            'student_guardian_id' => $guardian->id,
        ]);
    }

    public function test_male_student_id_starts_with_i(): void
    {
        $student = $this->makeStudent('male');
        $this->assertStringStartsWith('I', $student->student_id_number);
    }

    public function test_female_student_id_starts_with_a(): void
    {
        $student = $this->makeStudent('female');
        $this->assertStringStartsWith('A', $student->student_id_number);
    }

    public function test_generated_id_has_correct_length(): void
    {
        // Format: prefix(1) + Hijri YYMM(4) + sequence(3) = 8 chars
        $student = $this->makeStudent('male');
        $this->assertSame(8, strlen($student->student_id_number));
    }

    public function test_generated_id_contains_hijri_year_month(): void
    {
        $student = $this->makeStudent('male');
        $expectedYearMonth = now()->toHijri()->isoFormat('YYMM');

        $this->assertStringContainsString($expectedYearMonth, $student->student_id_number);
    }

    public function test_explicit_id_is_not_overridden(): void
    {
        $student = $this->makeStudent('male', 'CUSTOM001');
        $this->assertSame('CUSTOM001', $student->student_id_number);
    }

    public function test_sequential_male_students_get_unique_ids(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $base = [
            'name' => 'Student',
            'birthdate' => '2015-01-01',
            'gender' => 'male',
            'status' => 'candidate',
            'admission_date' => now()->toDateString(),
            'student_guardian_id' => $guardian->id,
        ];

        $s1 = Student::create($base);
        $s2 = Student::create($base);
        $s3 = Student::create($base);

        $this->assertNotSame($s1->student_id_number, $s2->student_id_number);
        $this->assertNotSame($s2->student_id_number, $s3->student_id_number);
        $this->assertNotSame($s1->student_id_number, $s3->student_id_number);
    }

    public function test_sequence_number_is_zero_padded_to_three_digits(): void
    {
        $student = $this->makeStudent('male');
        // Last 3 chars must be digits
        $suffix = substr($student->student_id_number, -3);
        $this->assertMatchesRegularExpression('/^\d{3}$/', $suffix);
    }

    public function test_male_and_female_students_have_different_prefixes(): void
    {
        $guardian = User::factory()->studentGuardian()->create();
        $base = [
            'name' => 'Student',
            'birthdate' => '2015-01-01',
            'status' => 'candidate',
            'admission_date' => now()->toDateString(),
            'student_guardian_id' => $guardian->id,
        ];

        $male = Student::create(array_merge($base, ['gender' => 'male']));
        $female = Student::create(array_merge($base, ['gender' => 'female']));

        $this->assertStringStartsWith('I', $male->student_id_number);
        $this->assertStringStartsWith('A', $female->student_id_number);
    }
}
