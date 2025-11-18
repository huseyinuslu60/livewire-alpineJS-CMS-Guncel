<?php

namespace Modules\Roles\Domain\Events;

use Spatie\Permission\Models\Role;

class RoleDeleted
{
    public Role $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }
}
