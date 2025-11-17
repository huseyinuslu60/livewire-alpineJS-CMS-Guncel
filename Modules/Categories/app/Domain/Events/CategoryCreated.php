<?php

namespace Modules\Categories\Domain\Events;

use Modules\Categories\Models\Category;

/**
 * Category Created Domain Event
 * 
 * Bir kategori oluşturulduğunda fırlatılır.
 */
class CategoryCreated
{
    public Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
}

