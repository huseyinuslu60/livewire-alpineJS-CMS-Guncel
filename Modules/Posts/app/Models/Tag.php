<?php

namespace Modules\Posts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $primaryKey = 'tag_id';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Get the posts that belong to the tag.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'posts_tags',
            'tag_id',
            'post_id',
            'tag_id',
            'post_id'
        );
    }

    /**
     * Create a tag from name.
     */
    public static function createFromName(string $name): self
    {
        $slug = Str::slug($name);

        return static::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );
    }

    /**
     * Get tags by names.
     */
    public static function getByNames(array $names): array
    {
        $tags = [];

        foreach ($names as $name) {
            if (trim($name)) {
                $tags[] = static::createFromName(trim($name));
            }
        }

        return $tags;
    }
}
