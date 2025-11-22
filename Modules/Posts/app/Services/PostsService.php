<?php

namespace Modules\Posts\Services;

use App\Helpers\LogHelper;
use App\Services\SlugGenerator;
use App\Services\ValueObjects\Slug;
use App\Support\Sanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Posts\Domain\Events\PostCreated;
use Modules\Posts\Domain\Events\PostDeleted;
use Modules\Posts\Domain\Events\PostUpdated;
use Modules\Posts\Domain\Repositories\PostFileRepositoryInterface;
use Modules\Posts\Domain\Repositories\PostRepositoryInterface;
use Modules\Posts\Domain\Repositories\TagRepositoryInterface;
use Modules\Posts\Domain\Services\PostValidator;
use Modules\Posts\Models\File;
use Modules\Posts\Models\Post;

class PostsService
{
    protected SlugGenerator $slugGenerator;

    protected PostValidator $postValidator;

    protected PostRepositoryInterface $postRepository;

    protected PostFileRepositoryInterface $postFileRepository;

    protected TagRepositoryInterface $tagRepository;

    public function __construct(
        ?SlugGenerator $slugGenerator = null,
        ?PostValidator $postValidator = null,
        ?PostRepositoryInterface $postRepository = null,
        ?PostFileRepositoryInterface $postFileRepository = null,
        ?TagRepositoryInterface $tagRepository = null
    ) {
        $this->slugGenerator = $slugGenerator ?? app(SlugGenerator::class);
        $this->postValidator = $postValidator ?? app(PostValidator::class);
        $this->postRepository = $postRepository ?? app(PostRepositoryInterface::class);
        $this->postFileRepository = $postFileRepository ?? app(PostFileRepositoryInterface::class);
        $this->tagRepository = $tagRepository ?? app(TagRepositoryInterface::class);
    }

    /**
     * Create a new post.
     */
    public function create(array $data, array $files = [], array $categoryIds = [], array $tagIds = [], array $fileDescriptions = []): Post
    {
        try {
            return DB::transaction(function () use ($data, $files, $categoryIds, $tagIds, $fileDescriptions) {
                // Validate post type specific rules
                $this->postValidator->validatePostType($data);

                // Generate slug if not provided
                if (empty($data['slug'])) {
                    $slug = $this->slugGenerator->generate($data['title'], Post::class, 'slug', 'post_id');
                    $data['slug'] = $slug->toString();
                }

                // Set author field (audit fields are handled by AuditFields trait)
                $data['author_id'] = auth()->id();

                // Create post
                $post = $this->postRepository->create($data);

                // Store files
                if (! empty($files)) {
                    $this->storeFiles($post, $files, $data['post_type'] ?? null, null, $fileDescriptions);
                }

                // Sync relationships
                $this->syncRelations($post, $categoryIds, $tagIds);

                $post->load(['files', 'categories', 'tags', 'author']);

                // Fire domain event
                Event::dispatch(new PostCreated($post));

                return $post;
            });
        } catch (\Exception $e) {
            LogHelper::error('PostsService create error', [
                'title' => $data['title'] ?? null,
                'post_type' => $data['post_type'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing post.
     */
    public function update(Post $post, array $data, array $files = [], array $categoryIds = [], array $tagIds = [], array $fileDescriptions = []): Post
    {
        try {
            return DB::transaction(function () use ($post, $data, $files, $categoryIds, $tagIds, $fileDescriptions) {
                // Validate post type specific rules
                $this->postValidator->validatePostType($data);

                // Generate slug if title changed and slug is empty
                if (isset($data['title']) && $data['title'] !== $post->title && empty($data['slug'])) {
                    $slug = $this->slugGenerator->generate($data['title'], Post::class, 'slug', 'post_id', $post->post_id);
                    $data['slug'] = $slug->toString();
                }

                // Audit fields are handled by AuditFields trait

                // Update post
                $post = $this->postRepository->update($post, $data);

                // Store new files
                if (! empty($files)) {
                    $this->storeFiles($post, $files, $data['post_type'] ?? $post->post_type, null, $fileDescriptions);
                }

                // Sync relationships
                $this->syncRelations($post, $categoryIds, $tagIds);

                $post->load(['files', 'categories', 'tags', 'author']);

                // Fire domain event
                $changedAttributes = array_keys($data);
                Event::dispatch(new PostUpdated($post, $changedAttributes));

                return $post;
            });
        } catch (\Exception $e) {
            LogHelper::error('PostsService update error', [
                'post_id' => $post->post_id,
                'error' => $e->getMessage(),
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
                $this->postRepository->delete($post);

                // Fire domain event
                Event::dispatch(new PostDeleted($post));
            });
        } catch (\Exception $e) {
            LogHelper::error('PostsService delete error', [
                'post_id' => $post->post_id,
                'error' => $e->getMessage(),
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
                $posts = $this->postRepository->findByIds($postIds);
                $deletedCount = 0;

                /** @var Post $post */
                foreach ($posts as $post) {
                    $this->delete($post);
                    $deletedCount++;
                }

                LogHelper::info('Yazılar toplu silindi', [
                    'count' => $deletedCount,
                    'post_ids' => $postIds,
                ]);

                return $deletedCount;
            });
        } catch (\Exception $e) {
            LogHelper::error('PostsService bulkDelete error', [
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
                $updated = $this->postRepository->bulkUpdate($postIds, ['status' => $status]);

                LogHelper::info('Yazılar toplu durum güncellendi', [
                    'count' => $updated,
                    'status' => $status,
                    'post_ids' => $postIds,
                ]);

                return $updated;
            });
        } catch (\Exception $e) {
            LogHelper::error('PostsService bulkUpdateStatus error', [
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
                $updated = $this->postRepository->bulkUpdate($postIds, ['in_newsletter' => $inNewsletter]);

                LogHelper::info('Yazılar toplu newsletter bayrağı güncellendi', [
                    'count' => $updated,
                    'in_newsletter' => $inNewsletter,
                    'post_ids' => $postIds,
                ]);

                return $updated;
            });
        } catch (\Exception $e) {
            LogHelper::error('PostsService bulkUpdateNewsletter error', [
                'post_ids' => $postIds,
                'in_newsletter' => $inNewsletter,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Find a post by ID
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $postId): Post
    {
        $post = $this->postRepository->findById($postId);

        if (! $post) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Post not found');
        }

        return $post;
    }

    /**
     * Get query builder for posts
     *
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Posts\Models\Post>
     */
    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Posts\Models\Post>
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->postRepository->getQuery();
    }

    /**
     * Toggle post mainpage visibility
     */
    public function toggleMainPage(Post $post): Post
    {
        try {
            return DB::transaction(function () use ($post) {
                $post->update(['is_mainpage' => ! $post->is_mainpage]);

                LogHelper::info('Yazı ana sayfa görünürlüğü değiştirildi', [
                    'post_id' => $post->post_id,
                    'is_mainpage' => $post->is_mainpage,
                ]);

                return $post;
            });
        } catch (\Exception $e) {
            LogHelper::error('PostsService toggleMainPage error', [
                'post_id' => $post->post_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Store files for a post.
     */
    public function storeFiles(Post $post, array $files, ?string $type = null, ?int $primaryIndex = null, array $fileDescriptions = []): void
    {
        try {
            $uploadedFiles = [];
            $galleryData = [];

            foreach ($files as $index => $file) {
                if ($file instanceof UploadedFile) {
                    $path = $this->storeFile($file, $post->post_id);

                    // content için gerçek file_path ile kaydet
                    $filename = $file->getClientOriginalName();
                    $description = $fileDescriptions[$filename]['description'] ?? '';
                    $altText = $fileDescriptions[$filename]['alt_text'] ?? '';

                    $safeDescription = Sanitizer::sanitizeHtml($description);
                    $safeAlt = Sanitizer::escape($altText);
                    $uploadedFiles[] = [
                        'post_id' => $post->post_id,
                        'title' => $file->getClientOriginalName(),
                        'type' => $type ?? 'news',
                        'file_path' => $path,
                        'primary' => $primaryIndex === $index,
                        'order' => $index,
                        'caption' => $safeDescription, // Description'ı caption olarak kaydet (sanitize)
                        'alt_text' => $safeAlt, // Alt text'i escape et
                    ];

                    $galleryData[] = [
                        'order' => $index,
                        'filename' => $filename,
                        'file_path' => $path, // Gerçek file_path
                        'type' => $file->getMimeType(),
                        'is_primary' => $primaryIndex === $index,
                        'uploaded_at' => now()->toISOString(),
                        'description' => $safeDescription,
                        'alt_text' => $safeAlt,
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
        } catch (\Exception $e) {
            LogHelper::error('PostsService storeFiles error', [
                'post_id' => $post->post_id,
                'files_count' => count($files),
                'error' => $e->getMessage(),
            ]);
            throw $e;
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
        $this->postFileRepository->updateByPostId($post->post_id, ['primary' => false]);

        // Set the specified file as primary
        $files = $this->postFileRepository->getQuery()
            ->where('post_id', $post->post_id)
            ->skip($primaryIndex)
            ->take(1)
            ->get();

        /** @var \Modules\Posts\Models\File $file */
        foreach ($files as $file) {
            $this->postFileRepository->update($file, ['primary' => true]);
        }
    }

    /**
     * Sync post relationships.
     */
    public function syncRelations(Post $post, array $categoryIds, array $tagIds): void
    {
        try {
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
                $tags = $this->tagRepository->getByNames($tagIds);
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
        } catch (\Exception $e) {
            LogHelper::error('PostsService syncRelations error', [
                'post_id' => $post->post_id,
                'category_count' => count($categoryIds),
                'tag_count' => count($tagIds),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // ============================================
    // GALERI İŞLEMLERİ
    // ============================================

    /**
     * Create a gallery post with all related data
     *
     * @param  array  $context  Context array containing:
     *                          - formData: array of post data
     *                          - orderedFiles: array of UploadedFile objects
     *                          - categoryIds: array of category IDs
     *                          - tagIds: array of tag IDs (strings, will be converted)
     *                          - fileDescriptions: array of file descriptions keyed by filename
     *                          - primaryFileId: string|null primary file identifier
     *                          - uploadedFilesKeys: array of uploaded file keys for index lookup
     * @return Post Created post
     */
    public function createGalleryPost(array $context): Post
    {
        try {
            return DB::transaction(function () use ($context) {
                $formData = $context['formData'];
                $orderedFiles = $context['orderedFiles'] ?? [];
                $categoryIds = $context['categoryIds'] ?? [];
                $tagIds = $context['tagIds'] ?? [];
                $fileDescriptions = $context['fileDescriptions'] ?? [];
                $primaryFileId = $context['primaryFileId'] ?? null;
                $uploadedFilesKeys = $context['uploadedFilesKeys'] ?? [];

                // Convert tag strings to IDs if needed
                if (! empty($tagIds) && is_string($tagIds[0] ?? null)) {
                    $tagIds = array_filter(array_map('trim', $tagIds));
                }

                // Find primary file index before creating post
                $primaryIndex = null;
                if ($primaryFileId !== null && ! empty($uploadedFilesKeys)) {
                    $primaryIndex = array_search($primaryFileId, $uploadedFilesKeys);
                    if ($primaryIndex === false) {
                        $primaryIndex = null;
                    }
                }

                // Create the post (this handles file storage and relationships)
                $post = $this->create(
                    $formData,
                    $orderedFiles,
                    $categoryIds,
                    $tagIds,
                    $fileDescriptions
                );

                // Set primary file after post creation
                if ($primaryIndex !== null) {
                    $this->setPrimaryFile($post, $primaryIndex);
                }

                return $post;
            });
        } catch (\Exception $e) {
            LogHelper::error('PostsService createGalleryPost error', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

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

                // Post model'ini de güncelle (refresh() gereksiz - zaten save() ile güncellendi)
                $post->content = $jsonContent;
                $post->save();

                // Files tablosundaki caption, order, and primary alanlarını da senkronize et
                // Batch update to avoid N+1 queries
                $filePaths = array_filter(array_column($galleryData, 'file_path'));
                if (! empty($filePaths)) {
                    // Fetch all files in one query
                    $files = File::where('post_id', $post->post_id)
                        ->whereIn('file_path', $filePaths)
                        ->get()
                        ->keyBy('file_path');

                    // Update files in-memory
                    $updates = [];
                    foreach ($galleryData as $item) {
                        $filePath = $item['file_path'] ?? '';
                        if (! empty($filePath) && isset($files[$filePath])) {
                            $file = $files[$filePath];
                            $updateData = [];

                            // Update description/caption
                            if (isset($item['description'])) {
                                $updateData['caption'] = Sanitizer::sanitizeHtml($item['description']);
                            }

                            // Update order
                            if (isset($item['order'])) {
                                $updateData['order'] = $item['order'];
                            }

                            // Update primary flag
                            if (isset($item['is_primary'])) {
                                $updateData['primary'] = (bool) $item['is_primary'];
                            }

                            // Update alt_text if provided
                            if (isset($item['alt_text'])) {
                                $updateData['alt_text'] = Sanitizer::escape($item['alt_text']);
                            }

                            if (! empty($updateData)) {
                                $updates[$file->file_id] = $updateData;
                            }
                        }
                    }

                    // Batch update files
                    foreach ($updates as $fileId => $updateData) {
                        $file = $files->firstWhere('file_id', $fileId);
                        if ($file) {
                            $this->postFileRepository->update($file, $updateData);
                        }
                    }
                }

                return $result > 0;
            });
        } catch (\Exception $e) {
            LogHelper::error('Galeri içeriği kaydedilirken hata oluştu', [
                'post_id' => $post->post_id,
                'error' => $e->getMessage(),
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
            return $files;
        }

        $reorderedFiles = [];

        foreach ($order as $fileId) {
            if (isset($files[$fileId])) {
                $reorderedFiles[$fileId] = $files[$fileId];
            }
        }

        if (count($reorderedFiles) !== count($files)) {
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
        try {
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

        } catch (\Exception $e) {
            LogHelper::error('PostsService addFilesToGallery error', [
                'post_id' => $post->post_id,
                'gallery_data_count' => count($galleryData),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
