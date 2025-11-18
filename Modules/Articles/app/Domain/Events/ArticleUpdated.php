<?php

namespace Modules\Articles\Domain\Events;

use Modules\Articles\Models\Article;

/**
 * Article Updated Domain Event
 */
class ArticleUpdated
{
    public Article $article;

    public array $changedAttributes;

    public function __construct(Article $article, array $changedAttributes = [])
    {
        $this->article = $article;
        $this->changedAttributes = $changedAttributes;
    }
}
