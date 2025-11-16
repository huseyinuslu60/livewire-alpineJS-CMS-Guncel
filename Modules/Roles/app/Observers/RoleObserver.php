<?php

namespace Modules\Roles\Observers;

use App\Traits\HandlesRequestContext;
use Modules\Logs\Models\UserLog;
use Spatie\Permission\Models\Role;

class RoleObserver
{
    use HandlesRequestContext;

    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        UserLog::log(
            action: 'create',
            description: "Rol oluşturuldu: {$role->name}",
            modelType: 'Spatie\Permission\Models\Role',
            modelId: $role->id,
            newValues: $role->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        $changes = $role->getChanges();
        $original = $role->getOriginal();

        if (! empty($changes)) {
            UserLog::log(
                action: 'update',
                description: "Rol güncellendi: {$role->name}",
                modelType: 'Spatie\Permission\Models\Role',
                modelId: $role->id,
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
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        UserLog::log(
            action: 'delete',
            description: "Rol silindi: {$role->name}",
            modelType: 'Spatie\Permission\Models\Role',
            modelId: $role->id,
            oldValues: $role->toArray(),
            ipAddress: $this->getRequestIp(),
            userAgent: $this->getRequestUserAgent(),
            url: $this->getRequestUrl(),
            method: $this->getRequestMethod()
        );
    }
}
