<?php

namespace Modules\Comments\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Comments\Models\Comment;

class CommentUpdated
{
    use Dispatchable;

    public Comment $comment;

    public array $changedAttributes;

    public function __construct(Comment $comment, array $changedAttributes = [])
    {
        $this->comment = $comment;
        $this->changedAttributes = $changedAttributes;
    }
}
