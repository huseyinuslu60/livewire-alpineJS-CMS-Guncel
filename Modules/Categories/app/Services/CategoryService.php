<?php

namespace Modules\Categories\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Categories\Models\Category;

class CategoryService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     * @return Builder
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        $query = Category::with(['parent', 'children']);

        // Arama filtresi
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Durum filtresi
        if (! empty($filters['statusFilter'])) {
            $query->ofStatus($filters['statusFilter']);
        }

        // Tip filtresi
        if (! empty($filters['typeFilter'])) {
            $query->ofType($filters['typeFilter']);
        }

        // Parent filtresi (opsiyonel)
        if (isset($filters['parent_id'])) {
            if ($filters['parent_id'] === null || $filters['parent_id'] === '') {
                $query->root();
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        // Sıralama
        $sortField = $filters['sortField'] ?? 'weight';
        $sortDirection = $filters['sortDirection'] ?? 'asc';

        if ($sortField === 'created_at' && $sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query;
    }

    /**
     * Yeni kategori oluştur
     *
     * @param  array<string, mixed>  $data  Kategori verileri
     * @return Category
     */
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            // Slug üretimi (eğer boşsa)
            if (empty($data['slug'])) {
                $data['slug'] = $this->makeUniqueSlug($data['name']);
            } else {
                // Slug unique kontrolü
                $data['slug'] = $this->makeUniqueSlug($data['slug']);
            }

            $category = Category::create($data);

            Log::info('Category created via CategoryService', [
                'category_id' => $category->category_id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

            return $category;
        });
    }

    /**
     * Kategori güncelle
     *
     * @param  Category  $category  Kategori modeli
     * @param  array<string, mixed>  $data  Güncellenecek veriler
     * @return Category
     */
    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            // Slug güncelleme (eğer name değiştiyse ve slug boşsa)
            if (isset($data['name']) && $data['name'] !== $category->name && empty($data['slug'])) {
                $data['slug'] = $this->makeUniqueSlug($data['name'], $category->category_id);
            } elseif (isset($data['slug']) && $data['slug'] !== $category->slug) {
                // Slug değiştiyse unique kontrolü yap
                $data['slug'] = $this->makeUniqueSlug($data['slug'], $category->category_id);
            }

            $category->update($data);

            Log::info('Category updated via CategoryService', [
                'category_id' => $category->category_id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

            return $category->fresh();
        });
    }

    /**
     * Kategori sil
     *
     * @param  Category  $category  Kategori modeli
     * @return void
     * @throws \Exception Alt kategoriler varsa hata fırlatır
     */
    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {
            // Alt kategorileri kontrol et
            if ($category->children()->count() > 0) {
                throw new \Exception('Bu kategorinin alt kategorileri bulunuyor. Önce alt kategorileri silin.');
            }

            $categoryId = $category->category_id;
            $categoryName = $category->name;

            $category->delete();

            Log::info('Category deleted via CategoryService', [
                'category_id' => $categoryId,
                'name' => $categoryName,
            ]);
        });
    }

    /**
     * Unique slug üret
     *
     * @param  string  $name  Kategori adı veya slug
     * @param  int|null  $ignoreId  İgnore edilecek kategori ID (update için)
     * @return string
     */
    public function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Category::where('slug', $slug);

            // Update durumunda mevcut kaydı ignore et
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

    /**
     * Toplu işlem uygula
     *
     * @param  string  $action  İşlem tipi (delete, activate, deactivate)
     * @param  array<int>  $ids  Kategori ID'leri
     * @return string Başarı mesajı
     * @throws \InvalidArgumentException
     */
    public function applyBulkAction(string $action, array $ids): string
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException('Kategori ID\'leri boş olamaz.');
        }

        $selectedCount = count($ids);

        return DB::transaction(function () use ($action, $ids, $selectedCount) {
            $categories = Category::whereIn('category_id', $ids);

            switch ($action) {
                case 'delete':
                    // Alt kategori kontrolü
                    foreach ($categories->get() as $category) {
                        if ($category->children()->count() > 0) {
                            throw new \Exception("'{$category->name}' kategorisinin alt kategorileri bulunuyor. Önce alt kategorileri silin.");
                        }
                    }
                    $categories->delete();
                    return $selectedCount.' kategori başarıyla silindi.';

                case 'activate':
                    $categories->update(['status' => 'active']);
                    return $selectedCount.' kategori aktif yapıldı.';

                case 'deactivate':
                    $categories->update(['status' => 'inactive']);
                    return $selectedCount.' kategori pasif yapıldı.';

                default:
                    throw new \InvalidArgumentException("Geçersiz bulk action: {$action}");
            }
        });
    }
}

