<?php

namespace Database\Factories;

use App\Enum\ItemCondition;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'quantity' => fake()->numberBetween(1, 50),
            'condition' => fake()->randomElement(ItemCondition::cases()),
            'acquisition_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function good(): static
    {
        return $this->state(['condition' => ItemCondition::GOOD]);
    }

    public function damaged(): static
    {
        return $this->state(['condition' => ItemCondition::DAMAGED]);
    }

    public function lost(): static
    {
        return $this->state(['quantity' => 0, 'condition' => ItemCondition::LOST]);
    }
}
