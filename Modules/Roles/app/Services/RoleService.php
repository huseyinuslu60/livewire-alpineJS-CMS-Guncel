<?php

namespace Modules\Roles\Services;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Logs\Models\UserLog;
use Modules\Roles\Domain\Events\RoleCreated;
use Modules\Roles\Domain\Events\RoleDeleted;
use Modules\Roles\Domain\Events\RoleUpdated;
use Modules\Roles\Domain\Repositories\RoleRepositoryInterface;
use Modules\Roles\Domain\Services\RoleValidator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * Module permissions that only super_admin can manage
     */
    protected const MODULE_PERMISSIONS = ['view modules', 'edit modules', 'activate modules'];

    /**
     * Create a new role
     *
     * @param  array  $data
     * @param  array  $permissionNames
     * @return Role
     */
    protected RoleValidator $roleValidator;

    protected RoleRepositoryInterface $roleRepository;

    public function __construct(
        ?RoleValidator $roleValidator = null,
        ?RoleRepositoryInterface $roleRepository = null
    ) {
        $this->roleValidator = $roleValidator ?? app(RoleValidator::class);
        $this->roleRepository = $roleRepository ?? app(RoleRepositoryInterface::class);
    }

    public function create(array $data, array $permissionNames = []): Role
    {
        try {
            // Validate role data
            $this->roleValidator->validate($data);

            return DB::transaction(function () use ($data, $permissionNames) {
                $role = $this->roleRepository->create([
                    'name' => $data['name'],
                    'display_name' => $data['display_name'] ?? $data['name'],
                    'description' => $data['description'] ?? null,
                ]);

                // Yetkileri ata
                if (! empty($permissionNames)) {
                    $filteredPermissions = $this->filterModulePermissions($permissionNames);
                    $permissions = Permission::whereIn('name', $filteredPermissions)->get();
                    $role->syncPermissions($permissions);

                    // Log kaydı - yetki atama
                    UserLog::log(
                        'update',
                        'Role yetkileri atandı: '.$role->name.' - '.$permissions->pluck('name')->implode(', '),
                        'Spatie\Permission\Models\Role',
                        $role->id
                    );
                }

                // Fire domain event
                Event::dispatch(new RoleCreated($role));

                LogHelper::info('Rol oluşturuldu', [
                    'role_id' => $role->id,
                    'name' => $role->name,
                ]);

                return $role;
            });
        } catch (\Exception $e) {
            LogHelper::error('Rol oluşturulurken hata oluştu', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update an existing role
     */
    public function update(Role $role, array $data, ?array $permissionNames = null): Role
    {
        try {
            // Validate role data
            $this->roleValidator->validate($data, $role->id);

            return DB::transaction(function () use ($role, $data, $permissionNames) {
                $role = $this->roleRepository->update($role, [
                    'name' => $data['name'],
                    'display_name' => $data['display_name'] ?? $data['name'],
                    'description' => $data['description'] ?? null,
                ]);

                // Yetkileri güncelle (null değilse)
                if ($permissionNames !== null) {
                    if (! empty($permissionNames)) {
                        $filteredPermissions = $this->filterModulePermissions($permissionNames);
                        $permissions = Permission::whereIn('name', $filteredPermissions)->get();
                        $role->syncPermissions($permissions);

                        // Log kaydı - yetki güncelleme
                        UserLog::log(
                            'update',
                            'Role yetkileri güncellendi: '.$role->name.' - '.$permissions->pluck('name')->implode(', '),
                            'Spatie\Permission\Models\Role',
                            $role->id
                        );
                    } else {
                        $role->syncPermissions([]);

                        // Log kaydı - yetki temizleme
                        UserLog::log(
                            'update',
                            'Role yetkileri temizlendi: '.$role->name,
                            'Spatie\Permission\Models\Role',
                            $role->id
                        );
                    }
                }

                // Fire domain event
                $changedAttributes = array_keys($data);
                Event::dispatch(new RoleUpdated($role, $changedAttributes));

                LogHelper::info('Rol güncellendi', [
                    'role_id' => $role->id,
                    'name' => $role->name,
                ]);

                return $role;
            });
        } catch (\Exception $e) {
            LogHelper::error('Rol güncellenirken hata oluştu', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a role
     */
    public function delete(Role $role): bool
    {
        try {
            return DB::transaction(function () use ($role) {
                $roleId = $role->id;
                $roleName = $role->name;

                $this->roleRepository->delete($role);

                // Fire domain event
                Event::dispatch(new RoleDeleted($role));

                LogHelper::info('Rol silindi', [
                    'role_id' => $roleId,
                    'name' => $roleName,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            LogHelper::error('Rol silinirken hata oluştu', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync permissions to a role
     */
    public function syncPermissions(Role $role, array $permissionNames): Role
    {
        try {
            $filteredPermissions = $this->filterModulePermissions($permissionNames);
            $permissions = Permission::whereIn('name', $filteredPermissions)->get();
            $role->syncPermissions($permissions);

            // Log kaydı
            UserLog::log(
                'update',
                'Role yetkileri güncellendi: '.$role->name.' - '.$permissions->pluck('name')->implode(', '),
                'Spatie\Permission\Models\Role',
                $role->id
            );

            LogHelper::info('Rol yetkileri güncellendi', [
                'role_id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $permissions->count(),
            ]);

            return $role;
        } catch (\Exception $e) {
            LogHelper::error('Rol yetkileri güncellenirken hata oluştu', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if role is super_admin
     *
     * @param  Role|string  $role
     */
    public function isSuperAdmin($role): bool
    {
        $roleName = $role instanceof Role ? $role->name : $role;

        return $roleName === 'super_admin';
    }

    /**
     * Validate that super_admin role cannot be modified
     *
     * @param  Role|string  $role
     *
     * @throws \InvalidArgumentException
     */
    public function validateNotSuperAdmin($role): void
    {
        if ($this->isSuperAdmin($role)) {
            throw new \InvalidArgumentException('Süper Admin rolü değiştirilemez! Bu rol zaten tüm yetkilere sahiptir.');
        }
    }

    /**
     * Filter module permissions based on user role
     * Only super_admin can assign module permissions
     */
    protected function filterModulePermissions(array $permissionNames): array
    {
        $isSuperAdmin = Auth::user()->hasRole('super_admin');

        // Eğer super_admin değilse, modül yetkilerini çıkar
        if (! $isSuperAdmin) {
            return array_diff($permissionNames, self::MODULE_PERMISSIONS);
        }

        return $permissionNames;
    }

    /**
     * Find a role by name
     */
    public function findByName(string $name): ?Role
    {
        return $this->roleRepository->findByName($name);
    }

    /**
     * Get all roles
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->roleRepository->getQuery()->get();
    }

    /**
     * Get query builder for roles
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->roleRepository->getQuery();
    }

    /**
     * Find a role by ID
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $roleId): Role
    {
        $role = $this->roleRepository->findById($roleId);

        if (! $role) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Role not found');
        }

        return $role;
    }
}
