<?php

namespace Modules\Categories\Domain\Events;

use Modules\Categories\Models\Category;

/**
 * Category Updated Domain Event
 *
 * Bir kategori güncellendiğinde fırlatılır.
 */
class CategoryUpdated
{
    public Category $category;

    public array $changedAttributes;

    public function __construct(Category $category, array $changedAttributes = [])
    {
        $this->category = $category;
        $this->changedAttributes = $changedAttributes;
    }
}
