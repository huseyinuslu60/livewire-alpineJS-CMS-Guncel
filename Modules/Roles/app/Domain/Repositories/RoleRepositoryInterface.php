<?php

namespace Modules\Roles\Domain\Repositories;

use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    public function findById(int $roleId): ?Role;

    public function findByName(string $name): ?Role;

    public function create(array $data): Role;

    public function update(Role $role, array $data): Role;

    public function delete(Role $role): bool;

    public function findByIds(array $roleIds): \Illuminate\Database\Eloquent\Collection;

    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
