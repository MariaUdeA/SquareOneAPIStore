<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2,0,10000),
            'other_attributes' => ["material" => $this->faker->randomElement(['Silk','Wool', 'Linen', 'Cashemere', 'Cotton']),
                                    "brand" => $this->faker->randomElement(['Gucci','Prada', 'Louis Vuitton', 'Denim', 'Versace'])],


        ];
    }
}
