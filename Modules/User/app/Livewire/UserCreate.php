<?php

namespace Modules\User\Livewire;

use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Roles\Services\RoleService;
use Modules\User\Services\UserService;

class UserCreate extends Component
{
    use ValidationMessages;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

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
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'role_ids' => 'required|array|min:1',
        'role_ids.*' => 'exists:roles,id',
    ];

    protected function messages()
    {
        return $this->getContextualValidationMessages()['user'] ?? $this->getValidationMessages();
    }

    public function mount()
    {
        Gate::authorize('create users');
    }

    public function store()
    {
        Gate::authorize('create users');

        try {
            $this->isLoading = true;
            $this->validate();

            // Güvenlik: Super admin değilse, super_admin rolünü atayamaz
            $currentUser = Auth::user();
            $superAdminRole = $this->roleService->findByName('super_admin');

            if (! $currentUser->hasRole('super_admin')) {
                // Super admin rolünü role_ids'den çıkar
                if ($superAdminRole && in_array($superAdminRole->id, $this->role_ids)) {
                    $this->role_ids = array_values(array_diff($this->role_ids, [$superAdminRole->id]));
                    session()->flash('warning', 'Super admin rolü atayamazsınız.');
                }
            }

            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
            ];

            $this->userService->create($data, $this->role_ids);

            $this->isLoading = false;
            $this->dispatch('user-created');

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('created', 'name', 'user'));

            return redirect()->route('user.index');
        } catch (\InvalidArgumentException $e) {
            $this->isLoading = false;
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Kullanıcı oluşturulurken hata oluştu: '.$e->getMessage());
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
        $view = 'user::livewire.user-create';

        return view($view, compact('roles'))
            ->extends('layouts.admin')->section('content');
    }
}
