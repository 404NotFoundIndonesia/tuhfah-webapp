<?php

namespace Database\Factories;

use App\Enum\Gender;
use App\Enum\StudentStatus;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());
        $status = fake()->randomElement(StudentStatus::cases());
        $needsDeparture = in_array($status, [
            StudentStatus::GRADUATED,
            StudentStatus::EXPELLED,
            StudentStatus::ON_LEAVE,
            StudentStatus::QUIT,
        ]);

        return [
            'student_id_number' => fake()->unique()->numerify('I######'),
            'name' => fake()->name($gender->value),
            'nickname' => fake()->firstName(),
            'birthplace' => fake()->city(),
            'birthdate' => fake()->dateTimeBetween('-12 years', '-6 years'),
            'gender' => $gender,
            'status' => $status,
            'admission_date' => fake()->dateTimeBetween('-2 years', '-2 weeks'),
            'departure_date' => $needsDeparture ? fake()->dateTimeBetween('-2 weeks') : null,
            'student_guardian_id' => User::factory()->studentGuardian(),
        ];
    }
}
