<?php

namespace Modules\User\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     * @return Builder
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        $query = User::with('roles');

        // Arama filtresi
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Rol filtresi
        if (! empty($filters['roleFilter'])) {
            $query->whereRelation('roles', 'name', $filters['roleFilter']);
        }

        // Sıralama
        $sortBy = $filters['sortBy'] ?? 'created_at';
        $sortDirection = $filters['sortDirection'] ?? 'desc';

        if ($sortBy === 'created_at' && $sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }

    /**
     * Kullanıcı oluştur
     *
     * @param  array<string, mixed>  $data  Kullanıcı verileri
     * @param  array<int>  $roleIds  Rol ID'leri
     * @param  User|null  $currentUser  Mevcut kullanıcı (super_admin kontrolü için)
     * @return User
     */
    public function create(array $data, array $roleIds = [], ?User $currentUser = null): User
    {
        return DB::transaction(function () use ($data, $roleIds, $currentUser) {
            // Password hash
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Super admin kontrolü
            if ($currentUser && ! $currentUser->hasRole('super_admin')) {
                $superAdminRole = Role::where('name', 'super_admin')->first();
                if ($superAdminRole && in_array($superAdminRole->id, $roleIds)) {
                    $roleIds = array_values(array_diff($roleIds, [$superAdminRole->id]));
                }
            }

            // Kullanıcı oluştur
            $user = User::create($data);

            // Roller ata
            if (! empty($roleIds)) {
                $roles = Role::whereIn('id', $roleIds)->get();
                if ($roles->count() > 0) {
                    $user->assignRole($roles);
                }
            }

            Log::info('User created via UserService', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

            return $user;
        });
    }

    /**
     * Kullanıcı güncelle
     *
     * @param  User  $user  Kullanıcı modeli
     * @param  array<string, mixed>  $data  Güncellenecek veriler
     * @param  array<int>  $roleIds  Rol ID'leri
     * @param  User|null  $currentUser  Mevcut kullanıcı (super_admin kontrolü için)
     * @return User
     */
    public function update(User $user, array $data, array $roleIds = [], ?User $currentUser = null): User
    {
        return DB::transaction(function () use ($user, $data, $roleIds, $currentUser) {
            // Password hash (eğer değiştirildiyse)
            if (isset($data['password']) && ! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Super admin kontrolü
            if ($currentUser && ! $currentUser->hasRole('super_admin')) {
                $superAdminRole = Role::where('name', 'super_admin')->first();

                // Super admin rolünü role_ids'den çıkar
                if ($superAdminRole && in_array($superAdminRole->id, $roleIds)) {
                    $roleIds = array_values(array_diff($roleIds, [$superAdminRole->id]));
                }

                // Eğer düzenlenen kullanıcı super_admin ise, rolünü değiştiremez
                if ($user->hasRole('super_admin')) {
                    // Rol değişikliği yapılmaya çalışılıyorsa
                    $currentRoleIds = $user->roles->pluck('id')->toArray();
                    if ($roleIds !== $currentRoleIds) {
                        throw new \Exception('Super admin kullanıcısının rolünü değiştiremezsiniz.');
                    }
                }

                // Eğer kullanıcı kendini düzenliyorsa, super_admin rolünü atayamaz
                if ($currentUser->id === $user->id && $superAdminRole && in_array($superAdminRole->id, $roleIds)) {
                    $roleIds = array_values(array_diff($roleIds, [$superAdminRole->id]));
                }
            }

            // Kullanıcı güncelle
            $user->update($data);

            // Roller güncelle
            if (! empty($roleIds)) {
                $roles = Role::whereIn('id', $roleIds)->get();
                $user->syncRoles($roles);
            }

            Log::info('User updated via UserService', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

            return $user->fresh();
        });
    }

    /**
     * Kullanıcı sil
     *
     * @param  User  $user  Kullanıcı modeli
     * @param  User|null  $currentUser  Mevcut kullanıcı (super_admin kontrolü için)
     * @return void
     * @throws \Exception
     */
    public function delete(User $user, ?User $currentUser = null): void
    {
        DB::transaction(function () use ($user, $currentUser) {
            // Super admin kontrolü
            if ($currentUser && ! $currentUser->hasRole('super_admin') && $user->hasRole('super_admin')) {
                throw new \Exception('Super admin kullanıcısını silemezsiniz.');
            }

            $userId = $user->id;
            $userName = $user->name;

            $user->delete();

            Log::info('User deleted via UserService', [
                'user_id' => $userId,
                'name' => $userName,
            ]);
        });
    }

    /**
     * Kullanıcıya rol ata
     *
     * @param  User  $user  Kullanıcı modeli
     * @param  array<int>  $roleIds  Rol ID'leri
     * @param  User|null  $currentUser  Mevcut kullanıcı (super_admin kontrolü için)
     * @return void
     */
    public function assignRoles(User $user, array $roleIds, ?User $currentUser = null): void
    {
        DB::transaction(function () use ($user, $roleIds, $currentUser) {
            // Super admin kontrolü
            if ($currentUser && ! $currentUser->hasRole('super_admin')) {
                $superAdminRole = Role::where('name', 'super_admin')->first();
                if ($superAdminRole && in_array($superAdminRole->id, $roleIds)) {
                    $roleIds = array_values(array_diff($roleIds, [$superAdminRole->id]));
                }
            }

            if (! empty($roleIds)) {
                $roles = Role::whereIn('id', $roleIds)->get();
                $user->syncRoles($roles);
            }

            Log::info('Roles assigned to user via UserService', [
                'user_id' => $user->id,
                'role_ids' => $roleIds,
            ]);
        });
    }
}

