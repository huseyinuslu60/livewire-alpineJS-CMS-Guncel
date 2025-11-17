<?php

namespace Modules\Articles\Services;

use App\Helpers\LogHelper;
use App\Services\SlugGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Articles\Domain\Events\ArticleCreated;
use Modules\Articles\Domain\Events\ArticleDeleted;
use Modules\Articles\Domain\Events\ArticleUpdated;
use Modules\Articles\Domain\Repositories\ArticleRepositoryInterface;
use Modules\Articles\Domain\Services\ArticleValidator;
use Modules\Articles\Domain\ValueObjects\ArticleStatus;
use Modules\Articles\Models\Article;

class ArticleService
{
    protected SlugGenerator $slugGenerator;
    protected ArticleValidator $articleValidator;
    protected ArticleRepositoryInterface $articleRepository;

    public function __construct(
        ?SlugGenerator $slugGenerator = null,
        ?ArticleValidator $articleValidator = null,
        ?ArticleRepositoryInterface $articleRepository = null
    ) {
        $this->slugGenerator = $slugGenerator ?? app(SlugGenerator::class);
        $this->articleValidator = $articleValidator ?? app(ArticleValidator::class);
        $this->articleRepository = $articleRepository ?? app(ArticleRepositoryInterface::class);
    }

    /**
     * Create a new article
     */
    public function create(array $data): Article
    {
        try {
            // Validate article data
            $this->articleValidator->validate($data);

            // Ensure published_at for published articles
            $data = $this->articleValidator->ensurePublishedAt($data);

            return DB::transaction(function () use ($data) {
                // Generate slug if not provided
                if (empty($data['slug']) && !empty($data['title'])) {
                    $slug = $this->slugGenerator->generate($data['title'], Article::class, 'slug', 'article_id');
                    $data['slug'] = $slug->toString();
                }

                $article = $this->articleRepository->create($data);

                // Fire domain event
                Event::dispatch(new ArticleCreated($article));

                LogHelper::info('Makale oluşturuldu', [
                    'article_id' => $article->article_id,
                    'title' => $article->title,
                ]);

                return $article;
            });
        } catch (\Exception $e) {
            LogHelper::error('ArticleService create error', [
                'title' => $data['title'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing article
     */
    public function update(Article $article, array $data): Article
    {
        try {
            // Validate article data
            $this->articleValidator->validate($data);

            // Ensure published_at for published articles
            $data = $this->articleValidator->ensurePublishedAt($data);

            return DB::transaction(function () use ($article, $data) {
                // Generate slug if title changed and slug is empty
                if (isset($data['title']) && $data['title'] !== $article->title && empty($data['slug'])) {
                    $slug = $this->slugGenerator->generate($data['title'], Article::class, 'slug', 'article_id', $article->article_id);
                    $data['slug'] = $slug->toString();
                }

                $article = $this->articleRepository->update($article, $data);

                // Fire domain event
                $changedAttributes = array_keys($data);
                Event::dispatch(new ArticleUpdated($article, $changedAttributes));

                LogHelper::info('Makale güncellendi', [
                    'article_id' => $article->article_id,
                    'title' => $article->title,
                ]);

                return $article;
            });
        } catch (\Exception $e) {
            LogHelper::error('ArticleService güncelleme hatası', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete an article
     */
    public function delete(Article $article): void
    {
        try {
            DB::transaction(function () use ($article) {
                $this->articleRepository->delete($article);

                // Fire domain event
                Event::dispatch(new ArticleDeleted($article));

                LogHelper::info('Makale silindi', [
                    'article_id' => $article->article_id,
                    'title' => $article->title,
                ]);
            });
        } catch (\Exception $e) {
            LogHelper::error('ArticleService silme hatası', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Toggle article status
     */
    public function toggleStatus(Article $article): Article
    {
        try {
            return DB::transaction(function () use ($article) {
                $currentStatus = ArticleStatus::fromString($article->status);

                $newStatus = match ($article->status) {
                    'draft' => ArticleStatus::published(),
                    'published' => ArticleStatus::draft(),
                    'pending' => ArticleStatus::published(),
                    default => ArticleStatus::draft()
                };

                $data = ['status' => $newStatus->toString()];

                // Set published_at if status is published and published_at is empty
                if ($newStatus->isPublished() && empty($article->published_at)) {
                    $data['published_at'] = now();
                }

                $article->update($data);

                LogHelper::info('Makale durumu değiştirildi', [
                    'article_id' => $article->article_id,
                    'status' => $article->status,
                ]);

                return $article;
            });
        } catch (\Exception $e) {
            LogHelper::error('ArticleService durum değiştirme hatası', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Toggle article mainpage visibility
     */
    public function toggleMainPage(Article $article): Article
    {
        try {
            return DB::transaction(function () use ($article) {
                $article->update(['show_on_mainpage' => !$article->show_on_mainpage]);

                LogHelper::info('Makale ana sayfa görünürlüğü değiştirildi', [
                    'article_id' => $article->article_id,
                    'show_on_mainpage' => $article->show_on_mainpage,
                ]);

                return $article;
            });
        } catch (\Exception $e) {
            LogHelper::error('ArticleService ana sayfa görünürlüğü değiştirme hatası', [
                'article_id' => $article->article_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

