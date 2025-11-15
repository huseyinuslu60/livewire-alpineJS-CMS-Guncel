<?php

namespace Modules\Roles\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Logs\Models\UserLog;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * Sistem rolleri (silinemez/düzenlenemez)
     */
    protected const SYSTEM_ROLES = ['super_admin'];

    /**
     * Modül yönetimi yetkileri (sadece super_admin değiştirebilir)
     */
    protected const MODULE_PERMISSIONS = ['view modules', 'edit modules', 'activate modules'];

    /**
     * Filtreli rol sorgusu (index ekranı için)
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri (search, perPage, sortField, sortDirection)
     */
    public function getFilteredRoles(array $filters = []): LengthAwarePaginator
    {
        $query = Role::withCount(['users', 'permissions']);

        // Arama
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        // Guard name filtresi (varsa)
        if (! empty($filters['guard_name'])) {
            $query->where('guard_name', $filters['guard_name']);
        }

        // Sıralama
        $sortField = $filters['sortField'] ?? 'name';
        $sortDirection = $filters['sortDirection'] ?? 'asc';

        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $filters['perPage'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Tüm roller (selectbox için)
     *
     * @return Collection<int, Role>
     */
    public function getAllRoles(): Collection
    {
        return Role::orderBy('name')->get();
    }

    /**
     * Tüm permission'ları grup bazlı getir
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Permission>>
     */
    public function getGroupedPermissions()
    {
        /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Permission>> */
        return Permission::all()->groupBy(function ($permission) {
            $name = $permission->name;

            // Özel kontroller - önce özel durumları kontrol et
            if (str_contains($name, 'newsletter')) {
                return 'newsletters';
            }

            if (str_contains($name, 'modules')) {
                return 'modules';
            }

            // Modül isimlerini kontrol et - permission name'inde geçen modül adını bul
            $moduleKeywords = [
                'articles' => 'articles',
                'users' => 'users',
                'categories' => 'categories',
                'posts' => 'posts',
                'roles' => 'roles',
                'authors' => 'authors',
                'comments' => 'comments',
                'logs' => 'logs',
                'featured' => 'featured',
                'lastminutes' => 'lastminutes',
                'agency_news' => 'agency_news',
                'files' => 'files',
                'settings' => 'settings',
                'menu' => 'settings',
                'stocks' => 'banks',
                'investor_questions' => 'banks',
            ];

            // Permission name'inde hangi modül adı geçiyor kontrol et
            foreach ($moduleKeywords as $keyword => $module) {
                if (str_contains($name, $keyword)) {
                    return $module;
                }
            }

            return 'other';
        });
    }

    /**
     * Tüm permission'lar (selectbox için)
     *
     * @return Collection<int, Permission>
     */
    public function getAllPermissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }

    /**
     * Yeni rol oluştur
     *
     * @param  array<string, mixed>  $data  Rol verileri (name, display_name, description, permissions)
     * @param  User|null  $currentUser  İşlemi yapan kullanıcı (yetki kontrolü için)
     *
     * @throws \Exception
     */
    public function createRole(array $data, ?User $currentUser = null): Role
    {
        return DB::transaction(function () use ($data, $currentUser) {
            /** @var Role $role */
            $role = Role::create([
                'name' => $data['name'],
                'display_name' => $data['display_name'] ?? $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            // Permission sync (varsa)
            if (! empty($data['permissions'])) {
                $this->syncRolePermissions($role, $data['permissions'], $currentUser);
            }

            Log::info('Rol başarıyla oluşturuldu.', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'created_by' => $currentUser?->id,
            ]);

            return $role;
        });
    }

    /**
     * Rol güncelle
     *
     * @param  Role  $role  Güncellenecek rol modeli
     * @param  array<string, mixed>  $data  Güncelleme verileri (name, display_name, description, permissions)
     * @param  User|null  $currentUser  İşlemi yapan kullanıcı (yetki kontrolü için)
     *
     * @throws \Exception
     */
    public function updateRole(Role $role, array $data, ?User $currentUser = null): Role
    {
        // Sistem rolü kontrolü
        if ($this->isSystemRole($role)) {
            throw new \Exception('Sistem rolleri güncellenemez!');
        }

        return DB::transaction(function () use ($role, $data, $currentUser) {
            $updateData = [
                'name' => $data['name'] ?? $role->name,
                'display_name' => $data['display_name'] ?? $role->display_name ?? $role->name,
            ];

            // Description property'si varsa ekle
            if (isset($data['description']) || property_exists($role, 'description')) {
                $updateData['description'] = $data['description'] ?? ($role->description ?? null);
            }

            $role->update($updateData);

            // Permission sync (varsa)
            if (isset($data['permissions'])) {
                $this->syncRolePermissions($role, $data['permissions'], $currentUser);
            }

            Log::info('Rol başarıyla güncellendi.', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'updated_by' => $currentUser?->id,
            ]);

            return $role->fresh();
        });
    }

    /**
     * Rol sil
     *
     * @param  Role  $role  Silinecek rol modeli
     * @param  User|null  $currentUser  İşlemi yapan kullanıcı (yetki kontrolü için)
     *
     * @throws \Exception
     */
    public function deleteRole(Role $role, ?User $currentUser = null): void
    {
        // Sistem rolü kontrolü
        if ($this->isSystemRole($role)) {
            throw new \Exception('Sistem rolleri silinemez!');
        }

        DB::transaction(function () use ($role, $currentUser) {
            $roleName = $role->name;
            $roleId = $role->id;

            // İlişkileri temizle (Spatie Permission otomatik handle ediyor, ama manuel de yapılabilir)
            $role->users()->detach();
            $role->permissions()->detach();

            $role->delete();

            Log::info('Rol başarıyla silindi.', [
                'role_id' => $roleId,
                'role_name' => $roleName,
                'deleted_by' => $currentUser?->id,
            ]);
        });
    }

    /**
     * Rol için permission sync
     *
     * @param  Role  $role  Permission'ları sync edilecek rol
     * @param  array<string>  $permissionNames  Permission name'leri array'i
     * @param  User|null  $currentUser  İşlemi yapan kullanıcı (yetki kontrolü için)
     *
     * @throws \Exception
     */
    public function syncRolePermissions(Role $role, array $permissionNames, ?User $currentUser = null): void
    {
        // Sistem rolü kontrolü
        if ($this->isSystemRole($role)) {
            throw new \Exception('Sistem rolleri için permission değiştirilemez!');
        }

        DB::transaction(function () use ($role, $permissionNames, $currentUser) {
            // Modül yönetimi yetkilerini kontrol et - sadece super_admin değiştirebilir
            $isSuperAdmin = $currentUser && $currentUser->hasRole('super_admin');

            // Eğer super_admin değilse, modül yetkilerini permissionNames'den çıkar
            if (! $isSuperAdmin) {
                $permissionNames = array_diff($permissionNames, self::MODULE_PERMISSIONS);
            }

            // Permission'ları bul ve sync et
            $permissions = Permission::whereIn('name', $permissionNames)->get();
            $role->syncPermissions($permissions);

            // Log kaydı
            $logMessage = empty($permissionNames)
                ? "Role yetkileri temizlendi: {$role->name}"
                : "Role yetkileri güncellendi: {$role->name} - ".$permissions->pluck('name')->implode(', ');

            UserLog::log(
                'update',
                $logMessage,
                'Spatie\Permission\Models\Role',
                $role->id
            );

            Log::info('Rol permission\'ları başarıyla sync edildi.', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permission_count' => $permissions->count(),
                'updated_by' => $currentUser?->id,
            ]);
        });
    }

    /**
     * Kullanıcıya rol atama / sync
     *
     * @param  User  $user  Rol atanacak kullanıcı
     * @param  array<string|int>  $roleIdsOrNames  Rol ID'leri veya name'leri
     * @param  User|null  $currentUser  İşlemi yapan kullanıcı (yetki kontrolü için)
     *
     * @throws \Exception
     */
    public function syncUserRoles(User $user, array $roleIdsOrNames, ?User $currentUser = null): void
    {
        DB::transaction(function () use ($user, $roleIdsOrNames, $currentUser) {
            // ID'ler mi name'ler mi kontrol et
            $roles = collect($roleIdsOrNames)->map(function ($roleIdOrName) {
                if (is_numeric($roleIdOrName)) {
                    return Role::find($roleIdOrName);
                }

                return Role::where('name', $roleIdOrName)->first();
            })->filter();

            $user->syncRoles($roles);

            Log::info('Kullanıcıya roller başarıyla sync edildi.', [
                'user_id' => $user->id,
                'role_count' => $roles->count(),
                'updated_by' => $currentUser?->id,
            ]);
        });
    }

    /**
     * Sistem rolü kontrolü
     *
     * @param  Role  $role  Kontrol edilecek rol
     */
    public function isSystemRole(Role $role): bool
    {
        return in_array($role->name, self::SYSTEM_ROLES, true);
    }

    /**
     * Rol detaylarını getir (permission'lar ile birlikte)
     *
     * @param  int  $roleId  Rol ID'si
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getRoleWithPermissions(int $roleId): Role
    {
        return Role::with('permissions')->findOrFail($roleId);
    }
}
