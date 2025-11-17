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
     * 
     * @param int $categoryId
     * @return Category|null
     */
    public function findById(int $categoryId): ?Category;

    /**
     * Find category by slug
     * 
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Create a new category
     * 
     * @param array $data
     * @return Category
     */
    public function create(array $data): Category;

    /**
     * Update an existing category
     * 
     * @param Category $category
     * @param array $data
     * @return Category
     */
    public function update(Category $category, array $data): Category;

    /**
     * Delete a category
     * 
     * @param Category $category
     * @return bool
     */
    public function delete(Category $category): bool;

    /**
     * Check if slug exists
     * 
     * @param string $slug
     * @param int|null $excludeId
     * @return bool
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool;

    /**
     * Check if category has children
     * 
     * @param Category $category
     * @return bool
     */
    public function hasChildren(Category $category): bool;
}

