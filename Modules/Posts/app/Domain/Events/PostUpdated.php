<?php

namespace Modules\Posts\Domain\Events;

use Modules\Posts\Models\Post;

/**
 * Post Updated Domain Event
 * 
 * Bir post güncellendiğinde fırlatılır.
 */
class PostUpdated
{
    public Post $post;
    public array $changedAttributes;

    public function __construct(Post $post, array $changedAttributes = [])
    {
        $this->post = $post;
        $this->changedAttributes = $changedAttributes;
    }
}

