<?php

namespace Modules\Articles\Domain\Events;

use Modules\Articles\Models\Article;

/**
 * Article Deleted Domain Event
 */
class ArticleDeleted
{
    public Article $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }
}

