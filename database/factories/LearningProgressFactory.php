<?php

namespace Database\Factories;

use App\Models\LearningProgress;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningProgress>
 */
class LearningProgressFactory extends Factory
{
    public function definition(): array
    {
        $subjects = ['Iqra', 'Al-Quran', 'Tajwid', 'Hafalan', 'Fiqih', 'Aqidah'];

        return [
            'student_id' => Student::factory(),
            'teacher_id' => User::factory()->teacher(),
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
            'subject' => fake()->randomElement($subjects),
            'milestone' => fake()->sentence(4),
            'score' => fake()->optional(0.7)->randomFloat(1, 50, 100),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
