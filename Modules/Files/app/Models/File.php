<?php

namespace Modules\Files\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
 */
class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
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
        'order' => 'integer',
    ];

    // post_id artık nullable, default değer gerekmez

    protected $primaryKey = 'file_id';

    /**
     * Post ile ilişki
     */
    public function post()
    {
        return $this->belongsTo(\Modules\Posts\Models\Post::class, 'post_id');
    }

    /**
     * Resim dosyası mı kontrol et
     */
    public function isImage()
    {
        $extension = strtolower(pathinfo($this->title, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Dosya URL'ini getir
     */
    public function getUrlAttribute()
    {
        return asset('storage/'.$this->file_path);
    }

    /**
     * Dosya boyutunu getir
     */
    public function getFileSizeAttribute()
    {
        $filePath = public_path('storage/'.$this->file_path);
        if (file_exists($filePath)) {
            return filesize($filePath);
        }

        return 0;
    }

    /**
     * Dosya adını getir (title'dan)
     */
    public function getOriginalNameAttribute()
    {
        return $this->title;
    }

    /**
     * Dosya uzantısını getir
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->title, PATHINFO_EXTENSION);
    }

    /**
     * MIME türünü tahmin et
     */
    public function getMimeTypeAttribute()
    {
        $extension = strtolower($this->extension);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Resim dosyalarını getir
     */
    public function scopeImages($query)
    {
        $driver = $query->getModel()->getConnection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where(function ($q) use ($likeOp) {
            $q->where('title', $likeOp, '%.jpg')
                ->orWhere('title', $likeOp, '%.jpeg')
                ->orWhere('title', $likeOp, '%.png')
                ->orWhere('title', $likeOp, '%.gif')
                ->orWhere('title', $likeOp, '%.webp');
        });
    }

    /**
     * Belirli türdeki dosyaları getir
     */
    public function scopeOfType($query, $mimeType)
    {
        if ($mimeType === 'image') {
            return $query->images();
        }

        $extensions = [];
        switch ($mimeType) {
            case 'video':
                $extensions = ['mp4', 'avi', 'mov', 'wmv', 'mkv', 'flv'];
                break;
            case 'audio':
                $extensions = ['mp3', 'wav', 'ogg', 'aac', 'flac'];
                break;
            case 'application/pdf':
                $extensions = ['pdf'];
                break;
            case 'text':
                $extensions = ['txt', 'md', 'rtf', 'csv'];
                break;
        }

        $driver = $query->getModel()->getConnection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where(function ($q) use ($extensions, $likeOp) {
            foreach ($extensions as $ext) {
                $q->orWhere('title', $likeOp, '%.'.$ext);
            }
        });
    }

    /**
     * Arama yap
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $safe = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $driver = $query->getModel()->getConnection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where(function ($q) use ($safe, $likeOp) {
            $q->where('title', $likeOp, "%{$safe}%")
                ->orWhere('alt_text', $likeOp, "%{$safe}%")
                ->orWhere('caption', $likeOp, "%{$safe}%");
        });
    }

    public function scopeSortedLatest($query, $column = 'created_at')
    {
        return $query->latest($column);
    }
}
