<?php

namespace Modules\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $record_id
 * @property string $email
 * @property string $status
 * @property string|null $reason
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class NewsletterBounce extends Model
{
    protected $table = 'newsletter_bounces';

    protected $primaryKey = 'record_id';

    protected $fillable = [
        'email',
        'status',
    ];

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'bounced' => 'bg-red-100 text-red-800',
            'blocked' => 'bg-orange-100 text-orange-800',
            'spam' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
