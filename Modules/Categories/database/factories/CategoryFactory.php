<?php

namespace Modules\Categories\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Categories\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Categories\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'meta_title' => fake()->optional()->sentence(3),
            'meta_description' => fake()->optional()->paragraph(),
            'meta_keywords' => fake()->optional()->words(5, true),
            'status' => fake()->randomElement(['active', 'inactive', 'draft']),
            'type' => fake()->randomElement(['news', 'gallery', 'video']),
            'show_in_menu' => fake()->boolean(80),
            'weight' => fake()->numberBetween(0, 100),
            'parent_id' => null,
        ];
    }

    /**
     * Indicate that the category is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the category has a parent.
     */
    public function withParent(?Category $parent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->category_id ?? null,
        ]);
    }

    /**
     * Indicate that the category is shown in menu.
     */
    public function shownInMenu(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_menu' => true,
        ]);
    }
}
