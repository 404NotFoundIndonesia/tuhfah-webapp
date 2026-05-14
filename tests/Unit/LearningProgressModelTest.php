<?php

namespace Tests\Unit;

use App\Models\LearningProgress;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningProgressModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_student(): void
    {
        $progress = LearningProgress::factory()->create();

        $this->assertInstanceOf(Student::class, $progress->student);
    }

    public function test_belongs_to_teacher(): void
    {
        $progress = LearningProgress::factory()->create();

        $this->assertInstanceOf(User::class, $progress->teacher);
    }

    public function test_student_has_many_learning_progress(): void
    {
        $student = Student::factory()->create();
        LearningProgress::factory()->count(3)->create(['student_id' => $student->id]);

        $this->assertCount(3, $student->learningProgress);
    }

    public function test_date_cast_returns_carbon(): void
    {
        $progress = LearningProgress::factory()->create(['date' => '2025-03-10']);

        $this->assertSame('2025-03-10', $progress->date->format('Y-m-d'));
    }

    public function test_score_cast_returns_float_or_null(): void
    {
        $withScore = LearningProgress::factory()->create(['score' => 85.5]);
        $withoutScore = LearningProgress::factory()->create(['score' => null]);

        $this->assertSame(85.5, $withScore->score);
        $this->assertNull($withoutScore->score);
    }

    public function test_factory_produces_valid_record(): void
    {
        $progress = LearningProgress::factory()->create();

        $this->assertNotNull($progress->student_id);
        $this->assertNotNull($progress->teacher_id);
        $this->assertNotEmpty($progress->subject);
        $this->assertNotEmpty($progress->milestone);
    }
}
