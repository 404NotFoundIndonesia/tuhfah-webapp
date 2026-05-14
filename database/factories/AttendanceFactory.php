<?php

namespace Database\Factories;

use App\Enum\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attendable_type' => Student::class,
            'attendable_id' => Student::factory(),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'status' => fake()->randomElement(AttendanceStatus::cases()),
            'notes' => fake()->optional()->sentence(),
            'recorded_by' => User::factory()->administrator(),
        ];
    }

    public function forStudent(Student $student = null): static
    {
        return $this->state(fn () => [
            'attendable_type' => Student::class,
            'attendable_id' => $student ?? Student::factory(),
        ]);
    }

    public function forTeacher(User $teacher = null): static
    {
        return $this->state(fn () => [
            'attendable_type' => User::class,
            'attendable_id' => $teacher ?? User::factory()->teacher(),
        ]);
    }
}
