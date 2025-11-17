<?php

namespace Modules\Categories\Domain\Repositories;

use Modules\Categories\Models\Category;

/**
 * Eloquent Category Repository Implementation
 */
class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Find category by ID
     */
    public function findById(int $categoryId): ?Category
    {
        return Category::find($categoryId);
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)->first();
    }

    /**
     * Create a new category
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update an existing category
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete a category
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    /**
     * Check if slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Category::where('slug', $slug);
        
        if ($excludeId !== null) {
            $query->where('category_id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if category has children
     */
    public function hasChildren(Category $category): bool
    {
        return $category->children()->count() > 0;
    }
}

