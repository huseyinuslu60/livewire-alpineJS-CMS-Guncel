<?php

namespace Modules\Authors\Domain\Repositories;

use Modules\Authors\Models\Author;

class EloquentAuthorRepository implements AuthorRepositoryInterface
{
    public function findById(int $authorId): ?Author
    {
        return Author::find($authorId);
    }

    public function findBySlug(string $slug): ?Author
    {
        return Author::where('slug', $slug)->first();
    }

    public function create(array $data): Author
    {
        return Author::create($data);
    }

    public function update(Author $author, array $data): Author
    {
        $author->update($data);
        return $author->fresh();
    }

    public function delete(Author $author): bool
    {
        return $author->delete();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Author::where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('author_id', '!=', $excludeId);
        }
        return $query->exists();
    }
}

