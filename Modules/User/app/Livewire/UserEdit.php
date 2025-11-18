<?php

namespace Modules\User\Livewire;

use App\Models\User;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Roles\Services\RoleService;
use Modules\User\Services\UserService;

class UserEdit extends Component
{
    use ValidationMessages;

    public ?\App\Models\User $user = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    /** @var array<int> */
    public array $role_ids = [];

    public ?string $successMessage = null;

    public bool $isLoading = false;

    protected UserService $userService;

    protected RoleService $roleService;

    public function boot()
    {
        $this->userService = app(UserService::class);
        $this->roleService = app(RoleService::class);
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255',
        'password' => 'nullable|string|min:8',
        'role_ids' => 'required|array|min:1',
        'role_ids.*' => 'exists:roles,id',
    ];

    protected function messages()
    {
        return $this->getContextualValidationMessages()['user'] ?? $this->getValidationMessages();
    }

    public function mount($user)
    {
        Gate::authorize('edit users');

        // Eğer $user string ise, User model'ini bul
        if (is_string($user) || is_numeric($user)) {
            $this->user = $this->userService->findById((int) $user);
        } else {
            $this->user = $user;
        }

        // Güvenlik: Super admin değilse, super_admin kullanıcısını düzenleyemez
        $currentUser = Auth::user();
        if (! $currentUser->hasRole('super_admin') && $this->user->hasRole('super_admin')) {
            abort(403, 'Super admin kullanıcısını düzenleyemezsiniz.');
        }

        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->role_ids = $this->user->roles->pluck('id')->toArray();
    }

    public function update()
    {
        Gate::authorize('edit users');

        try {
            $this->isLoading = true;

            // Email unique kontrolü (kendi email'i hariç)
            $this->rules['email'] = 'required|string|email|max:255|unique:users,email,'.$this->user->id;

            $this->validate();

            // Güvenlik: Super admin rolü kontrolü
            $currentUser = Auth::user();
            $superAdminRole = $this->roleService->findByName('super_admin');

            // Eğer mevcut kullanıcı super_admin değilse
            if (! $currentUser->hasRole('super_admin')) {
                // Super admin rolünü role_ids'den çıkar
                if ($superAdminRole && in_array($superAdminRole->id, $this->role_ids)) {
                    $this->role_ids = array_values(array_diff($this->role_ids, [$superAdminRole->id]));
                    session()->flash('warning', 'Super admin rolü atayamazsınız.');
                }

                // Eğer düzenlenen kullanıcı super_admin ise, rolünü değiştiremez
                if ($this->user->hasRole('super_admin')) {
                    abort(403, 'Super admin kullanıcısının rolünü değiştiremezsiniz.');
                }

                // Eğer kullanıcı kendini düzenliyorsa, super_admin rolünü atayamaz
                if ($currentUser->id === $this->user->id && $superAdminRole && in_array($superAdminRole->id, $this->role_ids)) {
                    $this->role_ids = array_values(array_diff($this->role_ids, [$superAdminRole->id]));
                    session()->flash('warning', 'Kendinize super admin rolü atayamazsınız.');
                }
            }

            $data = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            // Şifre varsa ekle
            if (! empty($this->password)) {
                $data['password'] = $this->password;
            }

            $this->userService->update($this->user, $data, $this->role_ids);

            $this->isLoading = false;
            $this->dispatch('user-updated');

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('updated', 'name', 'user'));

            return redirect()->route('user.index');
        } catch (\InvalidArgumentException $e) {
            $this->isLoading = false;
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Kullanıcı güncellenirken hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        $currentUser = Auth::user();

        // Super admin değilse, super_admin rolünü listeden çıkar
        if (! $currentUser->hasRole('super_admin')) {
            $roles = $this->roleService->getQuery()
                ->where('name', '!=', 'super_admin')
                ->get();
        } else {
            $roles = $this->roleService->getAll();
        }

        /** @var view-string $view */
        $view = 'user::livewire.user-edit';

        return view($view, compact('roles'))
            ->extends('layouts.admin')->section('content');
    }
}
