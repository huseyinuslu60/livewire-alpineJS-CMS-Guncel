<?php

namespace Modules\Posts\Domain\Repositories;

use Modules\Posts\Models\File;

/**
 * Eloquent Post File Repository Implementation
 */
class EloquentPostFileRepository implements PostFileRepositoryInterface
{
    /**
     * Find file by ID
     */
    public function findById(int $fileId): ?File
    {
        return File::find($fileId);
    }

    /**
     * Find files by post ID
     */
    public function findByPostId(int $postId): \Illuminate\Database\Eloquent\Collection
    {
        return File::where('post_id', $postId)->get();
    }

    /**
     * Create a new file
     */
    public function create(array $data): File
    {
        return File::create($data);
    }

    /**
     * Update a file
     */
    public function update(File $file, array $data): File
    {
        $file->update($data);

        return $file->fresh();
    }

    /**
     * Delete a file
     */
    public function delete(File $file): bool
    {
        return $file->delete();
    }

    /**
     * Update files by post ID
     */
    public function updateByPostId(int $postId, array $data): int
    {
        return File::where('post_id', $postId)->update($data);
    }

    /**
     * Get query builder for files
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return File::query();
    }
}
