<?php

namespace Modules\Categories\Services;

use App\Helpers\LogHelper;
use App\Services\SlugGenerator;
use App\Services\ValueObjects\Slug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Modules\Categories\Domain\Events\CategoryCreated;
use Modules\Categories\Domain\Events\CategoryDeleted;
use Modules\Categories\Domain\Events\CategoryUpdated;
use Modules\Categories\Domain\Repositories\CategoryRepositoryInterface;
use Modules\Categories\Domain\Services\CategoryValidator;
use Modules\Categories\Domain\ValueObjects\CategoryStatus;
use Modules\Categories\Domain\ValueObjects\CategoryType;
use Modules\Categories\Models\Category;

class CategoryService
{
    protected SlugGenerator $slugGenerator;
    protected CategoryValidator $categoryValidator;
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        ?SlugGenerator $slugGenerator = null,
        ?CategoryValidator $categoryValidator = null,
        ?CategoryRepositoryInterface $categoryRepository = null
    ) {
        $this->slugGenerator = $slugGenerator ?? app(SlugGenerator::class);
        $this->categoryValidator = $categoryValidator ?? app(CategoryValidator::class);
        $this->categoryRepository = $categoryRepository ?? app(CategoryRepositoryInterface::class);
    }

    /**
     * Create a new category
     */
    public function create(array $data): Category
    {
        try {
            // Validate category data
            $this->categoryValidator->validate($data);

            return DB::transaction(function () use ($data) {
                // Generate slug if not provided
                if (empty($data['slug'])) {
                    $slug = $this->slugGenerator->generate($data['name'], Category::class, 'slug', 'category_id');
                    $data['slug'] = $slug->toString();
                }

                $category = $this->categoryRepository->create($data);

                // Fire domain event
                Event::dispatch(new CategoryCreated($category));

                LogHelper::info('Kategori oluşturuldu', [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                ]);

                return $category;
            });
        } catch (\Exception $e) {
            LogHelper::error('CategoryService create error', [
                'name' => $data['name'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing category
     */
    public function update(Category $category, array $data): Category
    {
        try {
            // Validate category data
            $this->categoryValidator->validate($data);

            return DB::transaction(function () use ($category, $data) {
                // Generate slug if name changed and slug is empty
                if (isset($data['name']) && $data['name'] !== $category->name && empty($data['slug'])) {
                    $slug = $this->slugGenerator->generate($data['name'], Category::class, 'slug', 'category_id', $category->category_id);
                    $data['slug'] = $slug->toString();
                }

                $category = $this->categoryRepository->update($category, $data);

                // Fire domain event
                $changedAttributes = array_keys($data);
                Event::dispatch(new CategoryUpdated($category, $changedAttributes));

                LogHelper::info('Kategori güncellendi', [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                ]);

                return $category;
            });
        } catch (\Exception $e) {
            LogHelper::error('CategoryService update error', [
                'category_id' => $category->category_id,
                'error' => $e->getMessage(),
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
                if ($this->categoryRepository->hasChildren($category)) {
                    throw new \Exception('Kategori silinemez çünkü alt kategorileri var.');
                }

                $this->categoryRepository->delete($category);

                // Fire domain event
                Event::dispatch(new CategoryDeleted($category));

                LogHelper::info('Kategori silindi', [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                ]);
            });
        } catch (\Exception $e) {
            LogHelper::error('CategoryService delete error', [
                'category_id' => $category->category_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Make a unique slug from name
     * 
     * @deprecated Use SlugGenerator::generate() instead
     * @see \App\Services\SlugGenerator::generate()
     */
    public function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        // Geriye dönük uyumluluk için wrapper
        $slug = $this->slugGenerator->generate($name, Category::class, 'slug', 'category_id', $ignoreId);
        return $slug->toString();
    }
}

