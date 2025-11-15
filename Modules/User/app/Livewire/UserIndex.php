<?php

namespace Modules\User\Livewire;

use App\Livewire\Concerns\InteractsWithModal;
use App\Livewire\Concerns\InteractsWithToast;
use App\Models\User;
use App\Support\Pagination;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\User\Services\UserService;
use Spatie\Permission\Models\Role;

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
    use ValidationMessages, WithPagination, InteractsWithToast, InteractsWithModal;

    protected UserService $userService;

    public ?string $search = null;

    public ?string $roleFilter = null;

    public int $perPage = 10;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    // Başarı mesajı için
    public string $successMessage = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

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

    public function confirmDeleteUser($userId)
    {
        $this->confirmModal(
            'Kullanıcı Sil',
            'Bu kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
            'deleteUser',
            ['id' => $userId],
            [
                'confirmLabel' => 'Sil',
                'cancelLabel' => 'İptal',
            ]
        );
    }

    public function deleteUser($userId)
    {
        Gate::authorize('delete users');

        try {
            $user = User::findOrFail($userId);
            $this->userService->delete($user, Auth::user());

            $this->toastSuccess($this->createContextualSuccessMessage('deleted', 'name', 'user'));
        } catch (\Exception $e) {
            $this->toastError($e->getMessage());
        }
    }

    public function render()
    {
        $filters = [
            'search' => $this->search,
            'roleFilter' => $this->roleFilter,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ];

        $query = $this->userService->getFilteredQuery($filters);
        $users = $query->paginate(Pagination::clamp($this->perPage));

        // Roller için dropdown
        $roles = Role::all();

        /** @var view-string $view */
        $view = 'user::livewire.user-index';

        return view($view, compact('users', 'roles'))
            ->extends('layouts.admin')->section('content');
    }
}
