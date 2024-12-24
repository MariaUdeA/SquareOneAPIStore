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
            'shopping_cart_id'=> $this->faker->randomElement($cartIds), //because it is one to many, no issue repeating
            'product_variant_id'=>ProductVariant::factory(),// $this->faker->unique->randomElement($variantIds), //since it is one to one
            'quantity' => fake()->randomNumber(2,true),
            'unit_price' => fake()->randomFloat(2,0,10000),
        ];
    }
}
