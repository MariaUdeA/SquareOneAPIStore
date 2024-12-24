<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use phpDocumentor\Reflection\Types\Nullable;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShoppingCart>
 */
class ShoppingCartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userIds = User::all()->pluck("id")->toArray();
        return [
            'user_id'=> User::factory(),//$this->faker->unique()->randomElement($userIds),
            'status' => fake()->word(),
        ];
    }
}
