<?php

namespace Modules\Categories\Domain\Repositories;

use Modules\Categories\Models\Category;

/**
 * Category Repository Interface
 *
 * Category data access işlemlerini soyutlar.
 */
interface CategoryRepositoryInterface
{
    /**
     * Find category by ID
     */
    public function findById(int $categoryId): ?Category;

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Create a new category
     */
    public function create(array $data): Category;

    /**
     * Update an existing category
     */
    public function update(Category $category, array $data): Category;

    /**
     * Delete a category
     */
    public function delete(Category $category): bool;

    /**
     * Check if slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool;

    /**
     * Check if category has children
     */
    public function hasChildren(Category $category): bool;

    /**
     * Get query builder for categories
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
