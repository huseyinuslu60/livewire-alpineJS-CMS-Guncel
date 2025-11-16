<?php

namespace App\Observers;

use App\Support\Sanitizer;
use Modules\Articles\Models\Article;

/**
 * Article Model Observer
 *
 * Article oluşturulurken ve güncellenirken article_text alanını sanitize eder.
 */
class ArticleObserver
{
    /**
     * Handle the Article "creating" event.
     */
    public function creating(Article $article): void
    {
        if (isset($article->article_text)) {
            $article->article_text = Sanitizer::sanitizeHtml($article->article_text);
        }
    }

    /**
     * Handle the Article "updating" event.
     */
    public function updating(Article $article): void
    {
        if ($article->isDirty('article_text')) {
            $article->article_text = Sanitizer::sanitizeHtml($article->article_text);
        }
    }
}
