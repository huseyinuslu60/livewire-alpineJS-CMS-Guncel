<?php

namespace Modules\Posts\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Modules\Posts\Models\Post;

/**
 * PostsService - Orchestrator/Facade Service
 *
 * Bu servis, domain-specific servisleri koordine eder.
 * Backward compatibility için eski API'yi korur.
 */
class PostsService
{
    protected PostCreationService $creationService;

    protected PostUpdateService $updateService;

    protected PostMediaService $mediaService;

    protected PostBulkActionService $bulkActionService;

    protected PostQueryService $queryService;

    public function __construct(
        PostCreationService $creationService,
        PostUpdateService $updateService,
        PostMediaService $mediaService,
        PostBulkActionService $bulkActionService,
        PostQueryService $queryService
    ) {
        $this->creationService = $creationService;
        $this->updateService = $updateService;
        $this->mediaService = $mediaService;
        $this->bulkActionService = $bulkActionService;
        $this->queryService = $queryService;
    }

    /**
     * Create a new post.
     */
    public function create(array $data, array $files = [], array $categoryIds = [], array $tagIds = [], array $fileDescriptions = []): Post
    {
        return $this->creationService->create($data, $files, $categoryIds, $tagIds, $fileDescriptions);
    }

    /**
     * Update an existing post.
     */
    public function update(Post $post, array $data, array $files = [], array $categoryIds = [], array $tagIds = [], array $fileDescriptions = []): Post
    {
        return $this->updateService->update($post, $data, $files, $categoryIds, $tagIds, $fileDescriptions);
    }

    /**
     * Delete a post.
     */
    public function delete(Post $post): void
    {
        $this->updateService->delete($post);
    }

    /**
     * Make a unique slug from title.
     */
    public function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        return $this->creationService->makeUniqueSlug($title, $ignoreId);
    }

    /**
     * Store files for a post.
     */
    public function storeFiles(Post $post, array $files, ?string $type = null, ?int $primaryIndex = null, array $fileDescriptions = []): void
    {
        $this->mediaService->storeFiles($post, $files, $type, $primaryIndex, $fileDescriptions);
    }

    /**
     * Set primary file for a post.
     */
    public function setPrimaryFile(Post $post, int $primaryIndex): void
    {
        $this->mediaService->setPrimaryFile($post, $primaryIndex);
    }

    /**
     * Sync post relationships.
     */
    public function syncRelations(Post $post, array $categoryIds, array $tagIds): void
    {
        $this->creationService->syncRelations($post, $categoryIds, $tagIds);
    }

    /**
     * Galeri içeriğini veritabanına kaydet
     */
    public function saveGalleryContent(Post $post, array $files, string|int|null $primaryFileId = null): bool
    {
        return $this->mediaService->saveGalleryContent($post, $files, $primaryFileId);
    }

    /**
     * Galeri verilerini hazırla
     */
    public function prepareGalleryData(array $files, string|int|null $primaryFileId = null): array
    {
        return $this->mediaService->prepareGalleryData($files, $primaryFileId);
    }

    /**
     * Dosyaları yeni sıralamaya göre yeniden düzenle
     */
    public function reorderFiles(array $files, array $order, bool $isIndexed = false): array
    {
        return $this->mediaService->reorderFiles($files, $order, $isIndexed);
    }

    /**
     * Sıralama değişikliğini doğrula
     */
    public function validateOrder(array $order, array $files, bool $isIndexed = false): bool
    {
        return $this->mediaService->validateOrder($order, $files, $isIndexed);
    }

    /**
     * Galeriye yeni dosyalar ekle
     */
    public function addFilesToGallery(Post $post, array $galleryData): void
    {
        $this->mediaService->addFilesToGallery($post, $galleryData);
    }

    /**
     * Filtreli sorgu oluştur
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        return $this->queryService->getFilteredQuery($filters);
    }

    /**
     * Toplu işlem uygula
     */
    public function applyBulkAction(string $action, array $ids): string
    {
        return $this->bulkActionService->applyBulkAction($action, $ids);
    }

    /**
     * Ana sayfa durumunu toggle et
     */
    public function toggleMainPage(Post $post): bool
    {
        return $this->updateService->toggleMainPage($post);
    }
}
