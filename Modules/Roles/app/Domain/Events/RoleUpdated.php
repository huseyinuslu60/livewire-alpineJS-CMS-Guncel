<?php

namespace Modules\Roles\Domain\Events;

use Spatie\Permission\Models\Role;

class RoleUpdated
{
    public Role $role;
    public array $changedAttributes;

    public function __construct(Role $role, array $changedAttributes = [])
    {
        $this->role = $role;
        $this->changedAttributes = $changedAttributes;
    }
}

