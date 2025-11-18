<?php

namespace Modules\Posts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $file_id
 * @property int|null $post_id
 * @property string $title
 * @property string $type
 * @property string $file_path
 * @property bool $primary
 * @property string|null $alt_text
 * @property string|null $caption
 * @property int $order
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property string|null $url
 */
class File extends Model
{
    use HasFactory;

    protected $table = 'files';

    protected $primaryKey = 'file_id';

    protected $fillable = [
        'post_id',
        'title',
        'type',
        'file_path',
        'primary',
        'alt_text',
        'caption',
        'order',
    ];

    protected $casts = [
        'primary' => 'boolean',
    ];

    /**
     * Get the post that owns the file.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    /**
     * Get the file path (raw path, no URL conversion).
     */
    public function getPathAttribute(): string
    {
        return $this->file_path;
    }

    /**
     * Get the file extension.
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    /**
     * Check if the file is an image.
     */
    public function getIsImageAttribute(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        return in_array(strtolower($this->extension), $imageExtensions);
    }

    /**
     * Check if the file is a video.
     */
    public function getIsVideoAttribute(): bool
    {
        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];

        return in_array(strtolower($this->extension), $videoExtensions);
    }
}
