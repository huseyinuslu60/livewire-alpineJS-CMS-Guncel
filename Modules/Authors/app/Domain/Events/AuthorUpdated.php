<?php

namespace Modules\Authors\Domain\Events;

use Modules\Authors\Models\Author;

class AuthorUpdated
{
    public Author $author;
    public array $changedAttributes;

    public function __construct(Author $author, array $changedAttributes = [])
    {
        $this->author = $author;
        $this->changedAttributes = $changedAttributes;
    }
}

