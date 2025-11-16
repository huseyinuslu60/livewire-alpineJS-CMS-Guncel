<?php

namespace Modules\Posts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Posts\Models\Post;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Posts\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'author_id' => \App\Models\User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'summary' => fake()->paragraph(2),
            'published_date' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'content' => fake()->paragraphs(5, true),
            'post_type' => fake()->randomElement(['news', 'gallery', 'video']),
            'post_position' => fake()->randomElement(['normal', 'manşet', 'sürmanşet', 'öne çıkanlar']),
            'post_order' => fake()->numberBetween(0, 100),
            'is_comment' => fake()->boolean(80),
            'is_mainpage' => fake()->boolean(30),
            'redirect_url' => fake()->optional()->url(),
            'view_count' => fake()->numberBetween(0, 10000),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'is_photo' => fake()->boolean(20),
            'agency_name' => fake()->optional()->company(),
            'agency_id' => null,
            'created_by' => \App\Models\User::factory(),
            'updated_by' => null,
            'deleted_by' => null,
            'embed_code' => null,
            'in_newsletter' => fake()->boolean(40),
            'no_ads' => fake()->boolean(20),
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'is_mainpage' => fake()->boolean(50),
        ]);
    }

    /**
     * Indicate that the post is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_date' => null,
        ]);
    }

    /**
     * Indicate that the post is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mainpage' => true,
            'post_position' => fake()->randomElement(['manşet', 'sürmanşet', 'öne çıkanlar']),
        ]);
    }
}
