<?php

namespace Modules\User\Livewire;

use App\Contracts\SupportsToastErrors;
use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\HandlesExceptionsWithToast;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\User\Services\UserService;
use Spatie\Permission\Models\Role;

class UserCreate extends Component implements SupportsToastErrors
{
    use HandlesExceptionsWithToast, InteractsWithToast, ValidationMessages;

    protected UserService $userService;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** @var array<int> */
    public array $role_ids = [];

    public ?string $successMessage = null;

    public bool $isLoading = false;

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

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function mount()
    {
        Gate::authorize('create users');
    }

    public function cancel()
    {
        return redirect()->route('user.index');
    }

    public function store()
    {
        Gate::authorize('create users');

        try {
            $this->isLoading = true;
            $this->validate();

            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
            ];

            $currentUser = Auth::user();
            $this->userService->create($data, $this->role_ids, $currentUser);

            $this->isLoading = false;
            $this->toastSuccess($this->createContextualSuccessMessage('created', 'name', 'user'), 6000);

            return redirect()->route('user.index');
        } catch (\Throwable $e) {
            $this->isLoading = false;
            $this->handleException($e, 'Kullanıcı oluşturulurken bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    public function render()
    {
        $currentUser = Auth::user();

        // Super admin değilse, super_admin rolünü listeden çıkar
        if (! $currentUser->hasRole('super_admin')) {
            $roles = Role::where('name', '!=', 'super_admin')->get();
        } else {
            $roles = Role::all();
        }

        /** @var view-string $view */
        $view = 'user::livewire.user-create';

        return view($view, compact('roles'))
            ->extends('layouts.admin')->section('content');
    }
}
