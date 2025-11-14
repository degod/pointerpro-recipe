<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::count() > 0
            ? User::inRandomOrder()->first()
            : User::factory()->create();

        return [
            'user_id' => $user->id,
            'name' => fake()->sentence(2),
            'cuisine_type' => fake()->randomElement(['Italian', 'Mexican', 'Chinese', 'Indian', 'American']),
            'ingredients' => implode("\n", fake()->paragraphs(3)),
            'steps' => implode("\n\n", fake()->paragraphs(4)),
            'picture' => null,
        ];
    }
}
