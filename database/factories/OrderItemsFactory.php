<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductVariant;
use App\Models\Order;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderIds = Order::all()->pluck("id")->toArray();
        $variantIds = ProductVariant::all()->pluck("id")->toArray();
        return [
            'order_id'=> $this->faker->randomElement($orderIds), //because it is one to many, no issue repeating
            'variant_id'=> $this->faker->unique()->randomElement($variantIds), //because it is one to many, no issue repeating
            'quantity' => fake()->randomNumber(2,true),
            'price' => fake()->randomNumber(4,true),
        ];
    }
}
