<?php

namespace Modules\Authors\Domain\Events;

use Modules\Authors\Models\Author;

class AuthorCreated
{
    public Author $author;

    public function __construct(Author $author)
    {
        $this->author = $author;
    }
}
