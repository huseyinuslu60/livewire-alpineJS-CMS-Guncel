<?php

namespace Modules\Categories\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Categories\Models\Category;

class CategoryService
{
    /**
     * Create a new category
     */
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->makeUniqueSlug($data['name']);
            }

            $category = Category::create($data);

            Log::info('Category created', [
                'category_id' => $category->category_id,
                'name' => $category->name,
            ]);

            return $category;
        });
    }

    /**
     * Update an existing category
     */
    public function update(Category $category, array $data): Category
    {
        try {
            return DB::transaction(function () use ($category, $data) {
                // Generate slug if name changed and slug is empty
                if (isset($data['name']) && $data['name'] !== $category->name && empty($data['slug'])) {
                    $data['slug'] = $this->makeUniqueSlug($data['name'], $category->category_id);
                }

                $category->update($data);

                Log::info('Category updated', [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                ]);

                return $category;
            });
        } catch (\Exception $e) {
            Log::error('CategoryService update error:', [
                'category_id' => $category->category_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a category
     */
    public function delete(Category $category): void
    {
        try {
            DB::transaction(function () use ($category) {
                // Check if category has children
                if ($category->children()->count() > 0) {
                    throw new \Exception('Kategori silinemez çünkü alt kategorileri var.');
                }

                $category->delete();

                Log::info('Category deleted', [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('CategoryService delete error:', [
                'category_id' => $category->category_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Make a unique slug from name
     */
    public function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Category::where('slug', $slug);

            if ($ignoreId !== null) {
                $query->where('category_id', '!=', $ignoreId);
            }

            if (! $query->exists()) {
                break;
            }

            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}

