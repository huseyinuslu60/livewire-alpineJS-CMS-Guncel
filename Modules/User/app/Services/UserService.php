<?php

namespace Modules\User\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Helpers\LogHelper;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Create a new user
     */
    public function create(array $data, array $roleIds = []): User
    {
        return DB::transaction(function () use ($data, $roleIds) {
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = User::create($data);

            // Assign roles
            if (!empty($roleIds)) {
                $roles = Role::whereIn('id', $roleIds)->get();
                if ($roles->count() > 0) {
                    $user->assignRole($roles);
                }
            }

            LogHelper::info('Kullanıcı oluşturuldu', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

            return $user;
        });
    }

    /**
     * Update an existing user
     */
    public function update(User $user, array $data, ?array $roleIds = null): User
    {
        try {
            return DB::transaction(function () use ($user, $data, $roleIds) {
                // Hash password if provided
                if (isset($data['password']) && !empty($data['password'])) {
                    $data['password'] = Hash::make($data['password']);
                } else {
                    // Remove password from data if empty
                    unset($data['password']);
                }

                $user->update($data);

                // Sync roles if provided
                if ($roleIds !== null) {
                    $roles = Role::whereIn('id', $roleIds)->get();
                    $user->syncRoles($roles);
                }

                LogHelper::info('Kullanıcı güncellendi', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);

                return $user;
            });
        } catch (\Exception $e) {
            LogHelper::error('UserService update error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a user
     */
    public function delete(User $user): void
    {
        try {
            DB::transaction(function () use ($user) {
                // Remove all roles
                $user->roles()->detach();

                $user->delete();

                LogHelper::info('Kullanıcı silindi', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            });
        } catch (\Exception $e) {
            LogHelper::error('UserService delete error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

