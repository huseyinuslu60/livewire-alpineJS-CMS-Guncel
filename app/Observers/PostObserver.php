<?php

namespace App\Observers;

use App\Support\Sanitizer;
use Modules\Posts\Models\Post;

/**
 * Post Model Observer
 *
 * Post oluşturulurken ve güncellenirken content alanını sanitize eder.
 */
class PostObserver
{
    /**
     * Handle the Post "creating" event.
     */
    public function creating(Post $post): void
    {
        if (isset($post->content)) {
            $post->content = Sanitizer::sanitizeHtml($post->content);
        }
    }

    /**
     * Handle the Post "updating" event.
     */
    public function updating(Post $post): void
    {
        if ($post->isDirty('content')) {
            $post->content = Sanitizer::sanitizeHtml($post->content);
        }
    }
}
