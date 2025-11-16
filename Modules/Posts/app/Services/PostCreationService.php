<?php

namespace Modules\Posts\Services;

use App\Support\Sanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Posts\Enums\PostType;
use Modules\Posts\Models\Post;
use Modules\Posts\Models\Tag;

class PostCreationService
{
    protected PostMediaService $mediaService;

    public function __construct(PostMediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Create a new post.
     */
    public function create(array $data, array $files = [], array $categoryIds = [], array $tagIds = [], array $fileDescriptions = []): Post
    {
        return DB::transaction(function () use ($data, $files, $categoryIds, $tagIds, $fileDescriptions) {
            $this->validatePostType($data);

            if (empty($data['slug'])) {
                $data['slug'] = $this->makeUniqueSlug($data['title']);
            }

            $data['author_id'] = auth()->id();

            // Sanitize HTML content before saving
            if (isset($data['content'])) {
                $data['content'] = Sanitizer::sanitizeHtml($data['content']);
            }

            $post = Post::create($data);

            if (! empty($files)) {
                $this->mediaService->storeFiles($post, $files, $data['post_type'] ?? null, null, $fileDescriptions);
            }

            $this->syncRelations($post, $categoryIds, $tagIds);

            return $post->load(['files', 'categories', 'tags', 'author']);
        });
    }

    /**
     * Make a unique slug from title.
     */
    public function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Post::where('slug', $slug);

            if ($ignoreId !== null) {
                $query->where('post_id', '!=', $ignoreId);
            }

            if (! $query->exists()) {
                break;
            }

            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Sync post relationships.
     */
    public function syncRelations(Post $post, array $categoryIds, array $tagIds): void
    {
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

        if (! empty($tagIds)) {
            $tags = Tag::getByNames($tagIds);
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
    }

    /**
     * Validate post type specific rules.
     */
    protected function validatePostType(array $data): void
    {
        $postType = $data['post_type'] ?? null;

        if (! $postType) {
            return;
        }

        $typeValue = $postType instanceof PostType ? $postType->value : $postType;

        switch ($typeValue) {
            case PostType::Gallery->value:
                break;

            case PostType::Video->value:
                if (empty($data['embed_code'])) {
                    throw new \InvalidArgumentException('Video posts must have embed code.');
                }
                break;
        }
    }
}
