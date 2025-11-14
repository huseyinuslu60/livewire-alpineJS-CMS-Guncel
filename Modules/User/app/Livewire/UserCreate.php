<?php

namespace Modules\User\Livewire;

use App\Models\User;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

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
            $superAdminRole = Role::where('name', 'super_admin')->first();

            if (! $currentUser->hasRole('super_admin')) {
                // Super admin rolünü role_ids'den çıkar
                if ($superAdminRole && in_array($superAdminRole->id, $this->role_ids)) {
                    $this->role_ids = array_values(array_diff($this->role_ids, [$superAdminRole->id]));
                    session()->flash('warning', 'Super admin rolü atayamazsınız.');
                }
            }

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            // Spatie ile roller ata
            $roles = Role::whereIn('id', $this->role_ids)->get();
            if ($roles->count() > 0) {
                $user->assignRole($roles);
            }

            $this->isLoading = false;
            $this->dispatch('user-created');

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('created', 'name', 'user'));

            return redirect()->route('user.index');
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
