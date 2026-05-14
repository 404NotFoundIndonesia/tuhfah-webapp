<?php

namespace Database\Factories;

use App\Enum\AnnouncementScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'body' => $this->faker->paragraphs(3, true),
            'scope' => $this->faker->randomElement(AnnouncementScope::cases())->value,
            'published_at' => $this->faker->boolean(70) ? now()->subDays(rand(1, 30)) : null,
            'author_id' => User::factory()->administrator(),
        ];
    }

    public function published(): static
    {
        return $this->state(['published_at' => now()->subHour()]);
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }

    public function public(): static
    {
        return $this->state(['scope' => AnnouncementScope::PUBLIC->value]);
    }

    public function internal(): static
    {
        return $this->state(['scope' => AnnouncementScope::INTERNAL->value]);
    }
}
