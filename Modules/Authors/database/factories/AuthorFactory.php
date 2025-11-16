<?php

namespace Modules\Authors\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Authors\Models\Author;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Authors\Models\Author>
 */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'title' => fake()->optional()->name(),
            'bio' => fake()->optional()->paragraph(3),
            'image' => fake()->optional()->imageUrl(400, 400),
            'twitter' => fake()->optional()->userName(),
            'linkedin' => fake()->optional()->userName(),
            'facebook' => fake()->optional()->userName(),
            'instagram' => fake()->optional()->userName(),
            'website' => fake()->optional()->url(),
            'show_on_mainpage' => fake()->boolean(30),
            'weight' => fake()->numberBetween(0, 100),
            'status' => fake()->boolean(90),
        ];
    }

    /**
     * Indicate that the author is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    /**
     * Indicate that the author is shown on mainpage.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_on_mainpage' => true,
        ]);
    }
}
