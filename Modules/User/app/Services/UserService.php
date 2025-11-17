<?php

namespace Modules\User\Services;

use App\Helpers\LogHelper;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\User\Domain\Services\UserValidator;
use Spatie\Permission\Models\Role;

class UserService
{
    protected UserValidator $userValidator;

    public function __construct(?UserValidator $userValidator = null)
    {
        $this->userValidator = $userValidator ?? app(UserValidator::class);
    }

    /**
     * Create a new user
     */
    public function create(array $data, array $roleIds = []): User
    {
        try {
            // Validate user data
            $this->userValidator->validate($data);

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
        } catch (\Exception $e) {
            LogHelper::error('UserService create error', [
                'email' => $data['email'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing user
     */
    public function update(User $user, array $data, ?array $roleIds = null): User
    {
        try {
            // Validate user data (email değişmemişse mevcut email'i kullan)
            $validationData = $data;
            if (!isset($validationData['email'])) {
                $validationData['email'] = $user->email;
            }
            $this->userValidator->validate($validationData);

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

