<?php

namespace Modules\Articles\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Articles\Models\Article;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Articles\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => \Modules\Authors\Models\Author::factory(),
            'title' => fake()->sentence(4),
            'summary' => fake()->paragraph(2),
            'published_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'article_text' => fake()->paragraphs(10, true),
            'hit' => fake()->numberBetween(0, 5000),
            'show_on_mainpage' => fake()->boolean(30),
            'is_commentable' => fake()->boolean(80),
            'created_by' => \App\Models\User::factory(),
            'updated_by' => null,
            'deleted_by' => null,
            'status' => fake()->randomElement(['draft', 'published', 'pending']),
            'site_id' => null,
        ];
    }

    /**
     * Indicate that the article is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the article is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the article is featured on mainpage.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_on_mainpage' => true,
        ]);
    }
}
