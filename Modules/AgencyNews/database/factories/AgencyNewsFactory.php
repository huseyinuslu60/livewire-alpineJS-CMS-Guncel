<?php

namespace Modules\AgencyNews\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\AgencyNews\Models\AgencyNews;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\AgencyNews\Models\AgencyNews>
 */
class AgencyNewsFactory extends Factory
{
    protected $model = AgencyNews::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'summary' => $this->faker->paragraph(2),
            'tags' => $this->faker->optional()->words(3, true),
            'original_id' => $this->faker->optional()->numberBetween(1000, 9999),
            'agency_id' => $this->faker->numberBetween(1, 10),
            'category' => $this->faker->optional()->randomElement(['politics', 'economy', 'sports', 'technology']),
            'has_image' => $this->faker->boolean(60),
            'file_path' => $this->faker->optional()->imageUrl(),
            'sites' => $this->faker->optional()->randomElements([1, 2, 3], $this->faker->numberBetween(1, 3)),
            'content' => $this->faker->optional()->paragraphs(3, true),
        ];
    }
}
