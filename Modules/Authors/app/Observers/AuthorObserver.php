<?php

namespace Modules\Authors\Observers;

use App\Traits\HandlesRequestContext;
use Modules\Authors\Models\Author;
use Modules\Logs\Models\UserLog;

class AuthorObserver
{
    use HandlesRequestContext;

    /**
     * Handle the Author "created" event.
     */
    public function created(Author $author): void
    {
        UserLog::log(
            action: 'create',
            modelType: 'Author',
            modelId: $author->author_id,
            description: "Yeni yazar oluşturuldu: {$author->title}",
            newValues: $author->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }

    /**
     * Handle the Author "updated" event.
     */
    public function updated(Author $author): void
    {
        $changes = $author->getChanges();
        $original = $author->getOriginal();

        if (! empty($changes)) {
            UserLog::log(
                action: 'update',
                modelType: 'Author',
                modelId: $author->author_id,
                description: "Yazar güncellendi: {$author->title}",
                oldValues: array_intersect_key($original, $changes),
                newValues: $changes,
                ipAddress: $this->getRequestIp(),
                userAgent: $this->getRequestUserAgent(),
                url: $this->getRequestUrl(),
                method: $this->getRequestMethod()
            );
        }
    }

    /**
     * Handle the Author "deleted" event.
     */
    public function deleted(Author $author): void
    {
        UserLog::log(
            action: 'delete',
            modelType: 'Author',
            modelId: $author->author_id,
            description: "Yazar silindi: {$author->title}",
            oldValues: $author->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }

    /**
     * Handle the Author "restored" event.
     */
    public function restored(Author $author): void
    {
        UserLog::log(
            action: 'restore',
            modelType: 'Author',
            modelId: $author->author_id,
            description: "Yazar geri yüklendi: {$author->title}",
            newValues: $author->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }
}
