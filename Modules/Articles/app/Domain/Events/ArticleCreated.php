<?php

namespace Modules\Articles\Domain\Events;

use Modules\Articles\Models\Article;

/**
 * Article Created Domain Event
 */
class ArticleCreated
{
    public Article $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }
}

