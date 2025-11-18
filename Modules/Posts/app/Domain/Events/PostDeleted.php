<?php

namespace Modules\Posts\Domain\Events;

use Modules\Posts\Models\Post;

/**
 * Post Deleted Domain Event
 *
 * Bir post silindiğinde fırlatılır.
 */
class PostDeleted
{
    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
