<?php

namespace Database\Factories;

use App\Models\ShoppingCart;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cartIds = ShoppingCart::all()->pluck("id")->toArray();
        $variantIds = ProductVariant::all()->pluck("id")->toArray();
        return [
            'cart_id'=> $this->faker->randomElement($cartIds), //because it is one to many, no issue repeating
            'variant_id'=> $this->faker->unique()->randomElement($variantIds), //since it is one to one, can't repeat
            'quantity' => fake()->randomNumber(2,true),
            'price' => fake()->randomNumber(4,true),
        ];
    }
}
