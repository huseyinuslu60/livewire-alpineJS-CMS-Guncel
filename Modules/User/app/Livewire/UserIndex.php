<?php

namespace Modules\User\Livewire;

use App\Models\User;
use App\Support\Pagination;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\User\Services\UserService;

/**
 * @property string|null $search
 * @property string|null $roleFilter
 * @property int $perPage
 * @property string $sortBy
 * @property string $sortDirection
 * @property string $successMessage
 */
class UserIndex extends Component
{
    use ValidationMessages, WithPagination;

    public ?string $search = null;

    public ?string $roleFilter = null;

    public int $perPage = 10;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    // Başarı mesajı için
    public string $successMessage = '';

    protected UserService $userService;

    public function boot()
    {
        $this->userService = app(UserService::class);
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        Gate::authorize('view users');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        // Bileşeni zorla yenile
        $this->render();
    }

    // Alternatif metod adı
    public function sort($field)
    {
        $this->sortBy($field);
    }

    public function deleteUser($userId)
    {
        Gate::authorize('delete users');

        try {
            $user = $this->userService->findById($userId);

            // Güvenlik: Super admin değilse, super_admin kullanıcısını silemez
            $currentUser = Auth::user();
            if (! $currentUser->hasRole('super_admin') && $user->hasRole('super_admin')) {
                abort(403, 'Super admin kullanıcısını silemezsiniz.');
            }

            $userName = $user->name;
            $this->userService->delete($user);

            $this->successMessage = $this->createContextualSuccessMessage('deleted', 'name', 'user');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Kullanıcı silinirken hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        $query = User::with('roles');

        if ($this->search !== null) {
            $query->search($this->search);
        }

        if ($this->roleFilter !== null) {
            $query->whereRelation('roles', 'name', $this->roleFilter);
        }

        // Sıralama
        if ($this->sortBy === 'created_at' && $this->sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        $users = $query->paginate(Pagination::clamp($this->perPage));

        // Roller için dropdown
        $roles = app(\Modules\Roles\Services\RoleService::class)->getAll();

        /** @var view-string $view */
        $view = 'user::livewire.user-index';

        return view($view, compact('users', 'roles'))
            ->extends('layouts.admin')->section('content');
    }
}
