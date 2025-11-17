<?php

namespace Modules\Categories\Domain\Events;

use Modules\Categories\Models\Category;

/**
 * Category Deleted Domain Event
 * 
 * Bir kategori silindiğinde fırlatılır.
 */
class CategoryDeleted
{
    public Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
}

