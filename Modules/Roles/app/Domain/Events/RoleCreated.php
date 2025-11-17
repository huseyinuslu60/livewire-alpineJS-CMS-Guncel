<?php

namespace Modules\Roles\Domain\Events;

use Spatie\Permission\Models\Role;

class RoleCreated
{
    public Role $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }
}

