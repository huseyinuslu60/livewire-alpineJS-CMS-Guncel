<?php

namespace App\Services;

use App\Services\ValueObjects\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Generic Slug Generator Service
 *
 * Tüm modüllerde kullanılabilir ortak slug generator.
 * Herhangi bir Eloquent model için unique slug oluşturur.
 */
class SlugGenerator
{
    /**
     * String'den unique slug oluştur
     *
     * @param  string  $source  Slug oluşturulacak kaynak metin (title, name, vb.)
     * @param  string  $modelClass  Eloquent model class name (örn: Post::class)
     * @param  string  $slugColumn  Slug kolon adı (varsayılan: 'slug')
     * @param  string  $idColumn  ID kolon adı (varsayılan: model'in primary key'i)
     * @param  int|null  $excludeId  Bu ID'yi hariç tut (update işlemleri için)
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function generate(
        string $source,
        string $modelClass,
        string $slugColumn = 'slug',
        ?string $idColumn = null,
        ?int $excludeId = null
    ): Slug {
        if (empty(trim($source))) {
            throw new \InvalidArgumentException('Slug kaynağı boş olamaz');
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException("{$modelClass} bir Eloquent Model değil");
        }

        // ID kolonunu belirle (model'in primary key'i)
        if ($idColumn === null) {
            $model = new $modelClass;
            $idColumn = $model->getKeyName();
        }

        $slug = Str::slug($source);
        $originalSlug = $slug;
        $counter = 1;

        // Unique slug bulana kadar dene
        while (true) {
            $query = $modelClass::where($slugColumn, $slug);

            // Update işlemleri için belirli bir ID'yi hariç tut
            if ($excludeId !== null) {
                $query->where($idColumn, '!=', $excludeId);
            }

            if (! $query->exists()) {
                break;
            }

            // Slug zaten varsa, sonuna sayı ekle
            $slug = $originalSlug.'-'.$counter;
            $counter++;

            // Sonsuz döngüyü önle (maksimum 1000 deneme)
            if ($counter > 1000) {
                throw new \RuntimeException("Unique slug oluşturulamadı: {$originalSlug}");
            }
        }

        return Slug::fromString($slug);
    }

    /**
     * Mevcut slug'ın unique olup olmadığını kontrol et
     *
     * @param  Slug  $slug  Kontrol edilecek slug
     * @param  string  $modelClass  Eloquent model class name
     * @param  string  $slugColumn  Slug kolon adı
     * @param  string  $idColumn  ID kolon adı
     * @param  int|null  $excludeId  Bu ID'yi hariç tut
     */
    public function isUnique(
        Slug $slug,
        string $modelClass,
        string $slugColumn = 'slug',
        ?string $idColumn = null,
        ?int $excludeId = null
    ): bool {
        if (! is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException("{$modelClass} bir Eloquent Model değil");
        }

        if ($idColumn === null) {
            $model = new $modelClass;
            $idColumn = $model->getKeyName();
        }

        $query = $modelClass::where($slugColumn, $slug->toString());

        if ($excludeId !== null) {
            $query->where($idColumn, '!=', $excludeId);
        }

        return ! $query->exists();
    }
}
