<?php

namespace Modules\Files\Domain\Repositories;

use Modules\Files\Models\File;

interface FileRepositoryInterface
{
    public function findById(int $fileId): ?File;
    public function create(array $data): File;
    public function update(File $file, array $data): File;
    public function delete(File $file): bool;
}

