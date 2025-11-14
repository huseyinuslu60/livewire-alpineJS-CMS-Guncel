<?php

namespace Modules\Categories\Observers;

use App\Traits\HandlesRequestContext;
use Modules\Categories\Models\Category;
use Modules\Logs\Models\UserLog;

class CategoryObserver
{
    use HandlesRequestContext;

    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        UserLog::log(
            action: 'create',
            modelType: 'Category',
            modelId: $category->category_id,
            description: "Yeni kategori oluşturuldu: {$category->name}",
            newValues: $category->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        $changes = $category->getChanges();
        $original = $category->getOriginal();

        if (! empty($changes)) {
            UserLog::log(
                action: 'update',
                modelType: 'Category',
                modelId: $category->category_id,
                description: "Kategori güncellendi: {$category->name}",
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
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        UserLog::log(
            action: 'delete',
            modelType: 'Category',
            modelId: $category->category_id,
            description: "Kategori silindi: {$category->name}",
            oldValues: $category->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        UserLog::log(
            action: 'restore',
            modelType: 'Category',
            modelId: $category->category_id,
            description: "Kategori geri yüklendi: {$category->name}",
            newValues: $category->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }
}
