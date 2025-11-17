<?php

namespace Modules\Files\Domain\Repositories;

use Modules\Files\Models\File;

class EloquentFileRepository implements FileRepositoryInterface
{
    public function findById(int $fileId): ?File
    {
        return File::find($fileId);
    }

    public function create(array $data): File
    {
        return File::create($data);
    }

    public function update(File $file, array $data): File
    {
        $file->update($data);
        return $file->fresh();
    }

    public function delete(File $file): bool
    {
        return $file->delete();
    }
}

