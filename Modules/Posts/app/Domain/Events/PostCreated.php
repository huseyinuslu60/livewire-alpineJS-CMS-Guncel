<?php

namespace Modules\Posts\Domain\Events;

use Modules\Posts\Models\Post;

/**
 * Post Created Domain Event
 * 
 * Bir post oluşturulduğunda fırlatılır.
 */
class PostCreated
{
    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}

