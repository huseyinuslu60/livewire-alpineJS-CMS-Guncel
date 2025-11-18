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
        /** @var Role $role */
        $role = Role::create($data);

        return $role;
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

    public function findByIds(array $roleIds): \Illuminate\Database\Eloquent\Collection
    {
        return Role::whereIn('id', $roleIds)->get();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Role::query();
    }
}
