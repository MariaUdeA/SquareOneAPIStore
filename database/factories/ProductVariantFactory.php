<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productIds = Product::all()->pluck("id")->toArray();
        return [
            'product_id'=> $this->faker->randomElement($productIds), //because it is one to many, no issue repeating
            'color' => fake()->colorName(),
            'size' => $this->faker->randomElement(['XS','S', 'M', 'L', 'XL']),
            'stock_quantity' => $this->faker->numberBetween(1, 100),
        ];
    }
}
