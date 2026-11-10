<?php

namespace Database\Factories;

use App\Models\LaptopHireRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaptopHireRequest>
 */
class LaptopHireRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'equipment_type' => fake()->randomElement(['laptop', 'macbook', 'tablet', 'projector']),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '+254 712 345 678',
            'pickup_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'number_of_items' => fake()->numberBetween(1, 20),
            'desired_specs' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
