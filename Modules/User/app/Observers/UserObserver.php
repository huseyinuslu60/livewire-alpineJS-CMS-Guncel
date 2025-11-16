<?php

namespace Modules\User\Observers;

use App\Models\User;
use App\Traits\HandlesRequestContext;
use Modules\Logs\Models\UserLog;

class UserObserver
{
    use HandlesRequestContext;

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        UserLog::log(
            action: 'create',
            modelType: 'User',
            modelId: $user->id,
            description: "Yeni kullanıcı oluşturuldu: {$user->name}",
            newValues: $user->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        $original = $user->getOriginal();

        if (! empty($changes)) {
            UserLog::log(
                action: 'update',
                modelType: 'User',
                modelId: $user->id,
                description: "Kullanıcı güncellendi: {$user->name}",
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
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        UserLog::log(
            action: 'delete',
            modelType: 'User',
            modelId: $user->id,
            description: "Kullanıcı silindi: {$user->name}",
            oldValues: $user->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        UserLog::log(
            action: 'restore',
            modelType: 'User',
            modelId: $user->id,
            description: "Kullanıcı geri yüklendi: {$user->name}",
            newValues: $user->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }
}
