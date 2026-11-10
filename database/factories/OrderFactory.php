<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'RT-'.strtoupper(fake()->unique()->bothify('????????????????')),
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '+254712345678',
            'delivery_address' => fake()->streetAddress(),
            'delivery_city' => 'Nairobi',
            'customer_notes' => null,
            'status' => 'awaiting_confirmation',
            'payment_status' => 'unpaid',
            'currency' => 'KES',
            'subtotal' => 1000,
        ];
    }
}
