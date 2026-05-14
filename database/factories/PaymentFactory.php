<?php

namespace Database\Factories;

use App\Enum\PaymentStatus;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(PaymentStatus::cases());

        return [
            'student_id' => Student::factory(),
            'period' => $this->faker->date('Y-m'),
            'amount' => $this->faker->randomElement([150000, 200000, 250000, 300000]),
            'status' => $status->value,
            'due_date' => $this->faker->dateTimeBetween('-2 months', '+1 month')->format('Y-m-d'),
            'paid_at' => $status === PaymentStatus::PAID ? now() : null,
            'recorded_by' => User::factory()->administrator(),
        ];
    }

    public function unpaid(): static
    {
        return $this->state(['status' => PaymentStatus::UNPAID->value, 'paid_at' => null]);
    }

    public function paid(): static
    {
        return $this->state(['status' => PaymentStatus::PAID->value, 'paid_at' => now()]);
    }

    public function overdue(): static
    {
        return $this->state([
            'status' => PaymentStatus::OVERDUE->value,
            'due_date' => now()->subDays(5)->format('Y-m-d'),
            'paid_at' => null,
        ]);
    }
}
