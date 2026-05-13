<?php

namespace Database\Factories;

use App\Enum\Gender;
use App\Enum\MaritalStatus;
use App\Enum\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());

        return [
            'name' => fake()->name($gender->value),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'marital_status' => fake()->randomElement(MaritalStatus::cases()),
            'gender' => $gender,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function owner(): static
    {
        return $this->state(fn () => ['role' => Role::OWNER->value]);
    }

    public function headmaster(): static
    {
        return $this->state(fn () => ['role' => Role::HEADMASTER->value]);
    }

    public function administrator(): static
    {
        return $this->state(fn () => ['role' => Role::ADMINISTRATOR->value]);
    }

    public function teacher(): static
    {
        return $this->state(fn () => ['role' => Role::TEACHER->value]);
    }

    public function studentGuardian(): static
    {
        return $this->state(fn () => ['role' => Role::STUDENT_GUARDIAN->value]);
    }
}
