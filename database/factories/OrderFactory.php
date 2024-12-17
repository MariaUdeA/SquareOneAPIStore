<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
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
        $userIds = User::all()->pluck("id")->toArray();
        $orderStates = array('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'returned', 'refunded');
        return [
            'user_id'=> $this->faker->randomElement($userIds),
            'order_date' => now(),
            'total_amount' => fake()->randomNumber(2,true),
            'order_status' => $this->faker->randomElement($orderStates),
            'payment_method' => fake()->word(),
            'shipping_address' => fake()->address(),
        ];
    }
}
