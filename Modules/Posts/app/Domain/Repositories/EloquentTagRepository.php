<?php

namespace Modules\Posts\Domain\Repositories;

use App\Services\SlugGenerator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Posts\Models\Tag;

class EloquentTagRepository implements TagRepositoryInterface
{
    public function findById(int $tagId): ?Tag
    {
        return Tag::find($tagId);
    }

    public function findBySlug(string $slug): ?Tag
    {
        return Tag::where('slug', $slug)->first();
    }

    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    public function update(Tag $tag, array $data): Tag
    {
        $tag->update($data);

        return $tag->fresh();
    }

    public function delete(Tag $tag): bool
    {
        return $tag->delete();
    }

    public function getByNames(array $names): array
    {
        $tags = [];

        foreach ($names as $name) {
            if (trim($name)) {
                $tags[] = $this->createFromName(trim($name));
            }
        }

        return $tags;
    }

    public function createFromName(string $name): Tag
    {
        $slugGenerator = app(SlugGenerator::class);
        $slug = $slugGenerator->generate($name, Tag::class, 'slug', 'tag_id');

        return Tag::firstOrCreate(
            ['slug' => $slug->toString()],
            ['name' => $name]
        );
    }

    public function getQuery(): Builder
    {
        return Tag::query();
    }
}
