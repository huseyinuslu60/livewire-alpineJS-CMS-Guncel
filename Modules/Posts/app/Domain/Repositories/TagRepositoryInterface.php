<?php

namespace Modules\Posts\Domain\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Posts\Models\Tag;

interface TagRepositoryInterface
{
    public function findById(int $tagId): ?Tag;

    public function findBySlug(string $slug): ?Tag;

    public function create(array $data): Tag;

    public function update(Tag $tag, array $data): Tag;

    public function delete(Tag $tag): bool;

    public function getByNames(array $names): array;

    public function createFromName(string $name): Tag;

    public function getQuery(): Builder;
}
