<?php

namespace Modules\Authors\Domain\Repositories;

use Modules\Authors\Models\Author;

interface AuthorRepositoryInterface
{
    public function findById(int $authorId): ?Author;

    public function findBySlug(string $slug): ?Author;

    public function create(array $data): Author;

    public function update(Author $author, array $data): Author;

    public function delete(Author $author): bool;

    public function slugExists(string $slug, ?int $excludeId = null): bool;

    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
