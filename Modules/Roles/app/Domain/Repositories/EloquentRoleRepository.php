<?php

namespace Modules\Roles\Domain\Repositories;

use Spatie\Permission\Models\Role;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function findById(int $roleId): ?Role
    {
        return Role::find($roleId);
    }

    public function findByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);
        return $role->fresh();
    }

    public function delete(Role $role): bool
    {
        return $role->delete();
    }
}

