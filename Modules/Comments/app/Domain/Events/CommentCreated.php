<?php

namespace Modules\Comments\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Comments\Models\Comment;

class CommentCreated
{
    use Dispatchable;

    public Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
}

