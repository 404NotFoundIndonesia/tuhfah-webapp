<?php

namespace Database\Factories;

use App\Enum\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HonorariumFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(PaymentStatus::cases());

        return [
            'teacher_id' => User::factory()->teacher(),
            'period' => $this->faker->date('Y-m'),
            'amount' => $this->faker->randomElement([500000, 750000, 1000000, 1500000]),
            'status' => $status->value,
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
}
