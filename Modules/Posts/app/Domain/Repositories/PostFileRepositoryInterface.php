<?php

namespace Modules\Posts\Domain\Repositories;

use Modules\Posts\Models\File;

/**
 * Post File Repository Interface
 *
 * Post file data access işlemlerini soyutlar.
 */
interface PostFileRepositoryInterface
{
    /**
     * Find file by ID
     */
    public function findById(int $fileId): ?File;

    /**
     * Find files by post ID
     */
    public function findByPostId(int $postId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Create a new file
     */
    public function create(array $data): File;

    /**
     * Update a file
     */
    public function update(File $file, array $data): File;

    /**
     * Delete a file
     */
    public function delete(File $file): bool;

    /**
     * Update files by post ID
     *
     * @return int Number of updated records
     */
    public function updateByPostId(int $postId, array $data): int;

    /**
     * Get query builder for files
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
