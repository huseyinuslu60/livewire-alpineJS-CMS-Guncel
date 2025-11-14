<?php

namespace Modules\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterPost extends Model
{
    protected $table = 'newsletter_posts';

    protected $primaryKey = 'record_id';

    protected $fillable = [
        'newsletter_id',
        'post_id',
        'order',
        'hit',
    ];

    public function newsletter(): BelongsTo
    {
        return $this->belongsTo(Newsletter::class, 'newsletter_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(\Modules\Posts\Models\Post::class, 'post_id');
    }
}
