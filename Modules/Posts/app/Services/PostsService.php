<?php

namespace Modules\Posts\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Posts\Models\File;
use Modules\Posts\Models\Post;
use Modules\Posts\Models\Tag;

class PostsService
{
    /**
     * Create a new post.
     */
    public function create(array $data, array $files = [], array $categoryIds = [], array $tagIds = [], array $fileDescriptions = []): Post
    {
        return DB::transaction(function () use ($data, $files, $categoryIds, $tagIds, $fileDescriptions) {
            // Validate post type specific rules
            $this->validatePostType($data);

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->makeUniqueSlug($data['title']);
            }

            // Set author field (audit fields are handled by AuditFields trait)
            $data['author_id'] = auth()->id();

            // Create post
            $post = Post::create($data);

            // Store files
            if (! empty($files)) {
                $this->storeFiles($post, $files, $data['post_type'] ?? null, null, $fileDescriptions);
            }

            // Sync relationships
            $this->syncRelations($post, $categoryIds, $tagIds);

            return $post->load(['files', 'categories', 'tags', 'author']);
        });
    }

    /**
     * Update an existing post.
     */
    public function update(Post $post, array $data, array $files = [], array $categoryIds = [], array $tagIds = [], array $fileDescriptions = []): Post
    {
        try {
            return DB::transaction(function () use ($post, $data, $files, $categoryIds, $tagIds, $fileDescriptions) {
                // Validate post type specific rules
                $this->validatePostType($data);

                // Generate slug if title changed and slug is empty
                if (isset($data['title']) && $data['title'] !== $post->title && empty($data['slug'])) {
                    $data['slug'] = $this->makeUniqueSlug($data['title'], $post->post_id);
                }

                // Audit fields are handled by AuditFields trait

                // Update post
                $post->update($data);

                // Store new files
                if (! empty($files)) {
                    $this->storeFiles($post, $files, $data['post_type'] ?? $post->post_type, null, $fileDescriptions);
                }

                // Sync relationships
                $this->syncRelations($post, $categoryIds, $tagIds);

                return $post->load(['files', 'categories', 'tags', 'author']);
            });
        } catch (\Exception $e) {
            Log::error('PostsService update error:', [
                'post_id' => $post->post_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a post.
     */
    public function delete(Post $post): void
    {
        try {
            DB::transaction(function () use ($post) {
                // deleted_by is handled by AuditFields trait
                // Soft delete
                $post->delete();
            });
        } catch (\Exception $e) {
            Log::error('PostsService delete error:', [
                'post_id' => $post->post_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Bulk delete posts
     */
    public function bulkDelete(array $postIds): int
    {
        try {
            return DB::transaction(function () use ($postIds) {
                $posts = Post::whereIn('post_id', $postIds)->get();
                $deletedCount = 0;

                foreach ($posts as $post) {
                    $this->delete($post);
                    $deletedCount++;
                }

                Log::info('Posts bulk deleted', [
                    'count' => $deletedCount,
                    'post_ids' => $postIds,
                ]);

                return $deletedCount;
            });
        } catch (\Exception $e) {
            Log::error('PostsService bulkDelete error:', [
                'post_ids' => $postIds,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Bulk update post status
     */
    public function bulkUpdateStatus(array $postIds, string $status): int
    {
        try {
            return DB::transaction(function () use ($postIds, $status) {
                $updated = Post::whereIn('post_id', $postIds)
                    ->update(['status' => $status]);

                Log::info('Posts bulk status updated', [
                    'count' => $updated,
                    'status' => $status,
                    'post_ids' => $postIds,
                ]);

                return $updated;
            });
        } catch (\Exception $e) {
            Log::error('PostsService bulkUpdateStatus error:', [
                'post_ids' => $postIds,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Bulk update newsletter flag
     */
    public function bulkUpdateNewsletter(array $postIds, bool $inNewsletter): int
    {
        try {
            return DB::transaction(function () use ($postIds, $inNewsletter) {
                $updated = Post::whereIn('post_id', $postIds)
                    ->update(['in_newsletter' => $inNewsletter]);

                Log::info('Posts bulk newsletter flag updated', [
                    'count' => $updated,
                    'in_newsletter' => $inNewsletter,
                    'post_ids' => $postIds,
                ]);

                return $updated;
            });
        } catch (\Exception $e) {
            Log::error('PostsService bulkUpdateNewsletter error:', [
                'post_ids' => $postIds,
                'in_newsletter' => $inNewsletter,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Toggle post mainpage visibility
     */
    public function toggleMainPage(Post $post): Post
    {
        try {
            return DB::transaction(function () use ($post) {
                $post->update(['is_mainpage' => !$post->is_mainpage]);

                Log::info('Post mainpage toggled', [
                    'post_id' => $post->post_id,
                    'is_mainpage' => $post->is_mainpage,
                ]);

                return $post;
            });
        } catch (\Exception $e) {
            Log::error('PostsService toggleMainPage error:', [
                'post_id' => $post->post_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Make a unique slug from title.
     */
    public function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Post::where('slug', $slug);

            // 0-yutmayan filtre: ignoreId
            if ($ignoreId !== null) {
                $query->where('post_id', '!=', $ignoreId);
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
     * Store files for a post.
     */
    public function storeFiles(Post $post, array $files, ?string $type = null, ?int $primaryIndex = null, array $fileDescriptions = []): void
    {
        $uploadedFiles = [];
        $galleryData = [];

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $path = $this->storeFile($file, $post->post_id);

                // content için gerçek file_path ile kaydet
                $filename = $file->getClientOriginalName();
                $description = $fileDescriptions[$filename]['description'] ?? '';
                $altText = $fileDescriptions[$filename]['alt_text'] ?? '';

                Log::info('PostsService::storeFiles - Processing file:', [
                    'index' => $index,
                    'filename' => $filename,
                    'description' => $description,
                    'description_length' => strlen($description),
                    'alt_text' => $altText,
                    'fileDescriptions_keys' => array_keys($fileDescriptions),
                ]);

                $uploadedFiles[] = [
                    'post_id' => $post->post_id,
                    'title' => $file->getClientOriginalName(),
                    'type' => $type ?? 'news',
                    'file_path' => $path,
                    'primary' => $primaryIndex === $index,
                    'order' => $index,
                    'caption' => $description, // Description'ı caption olarak kaydet
                    'alt_text' => $altText, // Alt text'i kaydet
                ];

                $galleryData[] = [
                    'order' => $index,
                    'filename' => $filename,
                    'file_path' => $path, // Gerçek file_path
                    'type' => $file->getMimeType(),
                    'is_primary' => $primaryIndex === $index,
                    'uploaded_at' => now()->toISOString(),
                    'description' => $description,
                    'alt_text' => $altText,
                ];
            }
        }

        if (! empty($uploadedFiles)) {
            // Edit sayfasında yeni resim eklerken mevcut ana resmi koru
            // Sadece yeni eklenen resimler için primary flag'leri false yap
            foreach ($uploadedFiles as &$file) {
                $file['primary'] = false; // Yeni eklenen resimler ana resim olmasın
            }

            // Add timestamps to each file record
            $now = now();
            foreach ($uploadedFiles as &$file) {
                $file['created_at'] = $now;
                $file['updated_at'] = $now;
            }

            // Create file records
            File::insert($uploadedFiles);

            // Gallery için content güncelleme
            if (! empty($galleryData) && $type === 'gallery') {
                // file_path'leri doldur
                $fileIndex = 0;
                foreach ($galleryData as &$data) {
                    if (isset($uploadedFiles[$fileIndex])) {
                        $data['file_path'] = $uploadedFiles[$fileIndex]['file_path'];
                    }
                    $fileIndex++;
                }

                $this->addFilesToGallery($post, $galleryData);
            }
        }
    }

    /**
     * Store a single file.
     */
    protected function storeFile(UploadedFile $file, int $postId): string
    {
        $year = date('Y');
        $month = date('m');
        $path = "posts/{$year}/{$month}";

        return $file->store($path, 'public');
    }

    /**
     * Set primary file for a post.
     */
    public function setPrimaryFile(Post $post, int $primaryIndex): void
    {
        // Reset all primary flags for this post
        File::where('post_id', $post->post_id)->update(['primary' => false]);

        // Set the specified file as primary
        File::where('post_id', $post->post_id)
            ->skip($primaryIndex)
            ->take(1)
            ->update(['primary' => true]);
    }

    /**
     * Sync post relationships.
     */
    public function syncRelations(Post $post, array $categoryIds, array $tagIds): void
    {
        // Sync categories with timestamps
        if (! empty($categoryIds)) {
            $categoryData = [];
            $now = now();
            foreach ($categoryIds as $categoryId) {
                $categoryData[$categoryId] = [
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $post->categories()->sync($categoryData);
        }

        // Sync tags with timestamps
        if (! empty($tagIds)) {
            $tags = Tag::getByNames($tagIds);
            $tagData = [];
            $now = now();
            foreach (collect($tags)->pluck('tag_id') as $tagId) {
                $tagData[$tagId] = [
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $post->tags()->sync($tagData);
        }
    }

    /**
     * Validate post type specific rules.
     */
    protected function validatePostType(array $data): void
    {
        $postType = $data['post_type'] ?? null;

        if (! $postType) {
            return;
        }

        switch ($postType) {
            case 'gallery':
                // Gallery posts should have at least one file
                // This will be validated in the Livewire component
                break;

            case 'video':
                // Video posts should have embed_code
                if (empty($data['embed_code'])) {
                    throw new \InvalidArgumentException('Video posts must have embed code.');
                }
                break;
        }
    }

    // ============================================
    // GALERI İŞLEMLERİ
    // ============================================

    /**
     * Galeri içeriğini veritabanına kaydet
     *
     * @param  Post  $post  Post modeli
     * @param  array  $files  Dosya array'i
     * @param  string|int|null  $primaryFileId  Ana dosya ID'si
     * @return bool Başarılı mı?
     */
    public function saveGalleryContent(Post $post, array $files, string|int|null $primaryFileId = null): bool
    {
        try {
            return DB::transaction(function () use ($post, $files, $primaryFileId) {
                // Gallery data oluştur
                $galleryData = $this->prepareGalleryData($files, $primaryFileId);

                // JSON formatına çevir
                $jsonContent = json_encode($galleryData, JSON_UNESCAPED_UNICODE);

                // Veritabanına kaydet
                $result = DB::table('posts')
                    ->where('post_id', $post->post_id)
                    ->update(['content' => $jsonContent]);

                // Post model'ini de güncelle
                $post->content = $jsonContent;
                $post->save();

                // Model'i yenile
                $post->refresh();

                Log::info('Gallery content saved successfully', [
                    'post_id' => $post->post_id,
                    'files_count' => count($files),
                    'result' => $result,
                    'gallery_data' => $galleryData,
                    'json_content' => $jsonContent,
                ]);

                return $result > 0;
            });
        } catch (\Exception $e) {
            Log::error('Failed to save gallery content', [
                'post_id' => $post->post_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Galeri verilerini hazırla
     *
     * @param  array  $files  Dosya array'i
     * @param  string|int|null  $primaryFileId  Ana dosya ID'si
     * @return array Hazırlanmış galeri verisi
     */
    public function prepareGalleryData(array $files, string|int|null $primaryFileId = null): array
    {
        $galleryData = [];

        foreach ($files as $index => $file) {
            // file_id'yi koru - eğer yoksa file_path'den hash oluştur (kalıcı olması için)
            $fileId = is_array($file) ? ($file['file_id'] ?? null) : null;

            // file_id yoksa file_path'den hash oluştur (kalıcı olması için)
            if (empty($fileId)) {
                $filePath = $file['path'] ?? $file['file_path'] ?? '';
                $fileName = $file['original_name'] ?? $file['filename'] ?? '';

                if (! empty($filePath)) {
                    $fileId = 'existing_'.md5($filePath);
                } elseif (! empty($fileName)) {
                    $fileId = 'existing_'.md5($fileName);
                } else {
                    // Son çare olarak unique ID
                    $fileId = 'existing_'.uniqid('', true);
                }
            }

            // Ana resim kontrolü
            $isPrimary = false;
            if ($primaryFileId !== null) {
                // Her iki tarafı da string'e çevirerek karşılaştır (güvenli)
                $isPrimary = ((string) $fileId === (string) $primaryFileId);
            }

            // Order değerini kullan - eğer yoksa index kullan
            $orderValue = $file['order'] ?? $index;

            $galleryData[] = [
                'order' => $orderValue, // Sıralama değeri (güncellenmiş order veya index)
                'file_id' => $fileId, // Kalıcı file_id kullan
                'filename' => $file['original_name'] ?? $file['filename'] ?? '',
                'file_path' => $file['path'] ?? $file['file_path'] ?? '',
                'type' => $file['type'] ?? 'image/jpeg',
                'is_primary' => $isPrimary,
                'uploaded_at' => $file['uploaded_at'] ?? now()->toISOString(),
                'description' => $file['description'] ?? '',
            ];
        }

        Log::info('Gallery data prepared', [
            'files_count' => count($files),
            'gallery_data_count' => count($galleryData),
            'file_ids' => array_column($galleryData, 'file_id'),
            'orders' => array_column($galleryData, 'order'),
        ]);

        return $galleryData;
    }

    /**
     * Dosyaları yeni sıralamaya göre yeniden düzenle
     *
     * @param  array  $files  Mevcut dosya array'i
     * @param  array  $order  Yeni sıralama (file_id array'i)
     * @param  bool  $isIndexed  Index-based array mi?
     * @return array Yeniden sıralanmış dosya array'i
     */
    public function reorderFiles(array $files, array $order, bool $isIndexed = false): array
    {
        if ($isIndexed) {
            return $this->reorderIndexedFiles($files, $order);
        }

        return $this->reorderAssociativeFiles($files, $order);
    }

    /**
     * Index-based dosya array'ini yeniden sırala
     *
     * @param  array  $files  Index-based dosya array'i
     * @param  array  $order  Yeni sıralama (file_id array'i)
     * @return array Yeniden sıralanmış dosya array'i
     */
    protected function reorderIndexedFiles(array $files, array $order): array
    {
        if (empty($order)) {
            Log::warning('Invalid order data in reorderIndexedFiles', [
                'order' => $order,
                'files_count' => count($files),
            ]);

            return $files;
        }

        $reorderedFiles = [];

        foreach ($order as $fileId) {
            foreach ($files as $fileData) {
                if (($fileData['file_id'] ?? '') == $fileId) {
                    $reorderedFiles[] = $fileData;
                    break;
                }
            }
        }

        if (count($reorderedFiles) !== count($files)) {
            Log::warning('File count mismatch after reordering indexed files', [
                'original_count' => count($files),
                'reordered_count' => count($reorderedFiles),
            ]);

            return $files;
        }

        return $reorderedFiles;
    }

    /**
     * Associative dosya array'ini yeniden sırala
     *
     * @param  array  $files  Associative dosya array'i
     * @param  array  $order  Yeni sıralama (file_id array'i)
     * @return array Yeniden sıralanmış dosya array'i
     */
    protected function reorderAssociativeFiles(array $files, array $order): array
    {
        if (empty($order)) {
            Log::warning('Invalid order data in reorderAssociativeFiles', [
                'order' => $order,
                'files_count' => count($files),
            ]);

            return $files;
        }

        $reorderedFiles = [];

        foreach ($order as $fileId) {
            if (isset($files[$fileId])) {
                $reorderedFiles[$fileId] = $files[$fileId];
            }
        }

        if (count($reorderedFiles) !== count($files)) {
            Log::warning('File count mismatch after reordering associative files', [
                'original_count' => count($files),
                'reordered_count' => count($reorderedFiles),
            ]);

            return $files;
        }

        return $reorderedFiles;
    }

    /**
     * Sıralama değişikliğini doğrula
     *
     * @param  array  $order  Yeni sıralama
     * @param  array  $files  Mevcut dosyalar
     * @param  bool  $isIndexed  Index-based array mi?
     * @return bool Geçerli mi?
     */
    public function validateOrder(array $order, array $files, bool $isIndexed = false): bool
    {
        if (empty($order)) {
            return false;
        }

        if (count($order) !== count($files)) {
            return false;
        }

        if ($isIndexed) {
            $fileIds = array_map(function ($file) {
                return $file['file_id'] ?? null;
            }, $files);

            foreach ($order as $fileId) {
                if (! in_array($fileId, $fileIds)) {
                    return false;
                }
            }
        } else {
            $fileKeys = array_keys($files);

            foreach ($order as $fileId) {
                if (! in_array($fileId, $fileKeys)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Galeriye yeni dosyalar ekle (storeFiles'den sonra çağrılır)
     *
     * @param  Post  $post  Post modeli
     * @param  array  $galleryData  Yeni eklenen dosyaların gallery data formatı
     */
    public function addFilesToGallery(Post $post, array $galleryData): void
    {
        if (empty($galleryData)) {
            return;
        }

        // Yeni eklenen resimler ana resim olmasın
        foreach ($galleryData as &$data) {
            $data['is_primary'] = false;
        }

        // Mevcut content'i al
        $existingContentData = $post->content ? (is_string($post->content) ? json_decode($post->content, true) : $post->content) : [];

        // Güvenlik kontrolü - null veya geçersiz değerleri boş array yap
        if (! is_array($existingContentData)) {
            $existingContentData = [];
        }

        if (! empty($existingContentData)) {
            // Mevcut verileri koru, yeni resimleri ekle
            // Ama önce mevcut content'de boş file_path'li (yeni eklenen) dosyaları temizle
            $existingContentData = array_filter($existingContentData, function ($item) {
                return ! empty($item['file_path']); // Sadece gerçek file_path'i olan dosyaları koru
            });

            $updatedContentData = array_merge($existingContentData, $galleryData);
            $post->update(['content' => json_encode($updatedContentData, JSON_UNESCAPED_UNICODE)]);
        } else {
            // İlk resim ekleniyor
            $post->update(['content' => json_encode($galleryData, JSON_UNESCAPED_UNICODE)]);
        }

        // Debug için (sadece development'ta)
        if (config('app.debug')) {
            Log::info('PostsService added files to gallery:', [
                'post_id' => $post->post_id,
                'existing_count' => count($existingContentData),
                'new_count' => count($galleryData),
                'total_count' => count($updatedContentData ?? $galleryData),
            ]);
        }
    }
}
