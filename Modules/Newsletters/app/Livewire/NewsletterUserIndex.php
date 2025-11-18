<?php

namespace Modules\Newsletters\Livewire;

use App\Helpers\SystemHelper;
use App\Support\Pagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Newsletters\Domain\ValueObjects\NewsletterUserStatus;
use Modules\Newsletters\Services\NewsletterUserService;

class NewsletterUserIndex extends Component
{
    use WithPagination;

    protected NewsletterUserService $newsletterUserService;

    public function boot()
    {
        $this->newsletterUserService = app(NewsletterUserService::class);
    }

    public ?string $search = null;

    public ?string $statusFilter = null;

    public ?string $emailStatusFilter = null;

    public ?int $perPage = null;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    /** @var array<int> */
    public array $selectedUsers = [];

    public bool $selectAll = false;

    public string $bulkAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'emailStatusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedEmailStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedUsers = $this->getUsersQuery()->pluck('user_id')->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function updatedSelectedUsers()
    {
        $this->selectAll = count($this->selectedUsers) === $this->getUsersQuery()->count();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->emailStatusFilter = '';
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
    }

    public function confirmDeleteUser($userId)
    {
        $this->dispatch('confirm-delete-user', [
            'title' => 'Kullanıcı Sil',
            'message' => 'Bu kullanıcıyı silmek istediğinizden emin misiniz?',
            'userId' => $userId,
        ]);
    }

    public function deleteUser($userId)
    {
        if (! Auth::user()->can('delete newsletter_users')) {
            abort(403, 'Abone silme yetkiniz bulunmuyor.');
        }

        try {
            $user = $this->newsletterUserService->findById($userId);
            $this->newsletterUserService->delete($user);

            session()->flash('success', 'Kullanıcı başarıyla silindi.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Kullanıcı silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function toggleStatus($userId)
    {
        if (! Auth::user()->can('edit newsletter_users')) {
            abort(403, 'Abone düzenleme yetkiniz bulunmuyor.');
        }

        try {
            $user = $this->newsletterUserService->findById($userId);
            $this->newsletterUserService->toggleStatus($user);

            session()->flash('success', 'Kullanıcı durumu güncellendi.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Kullanıcı durumu güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function getUsersQuery()
    {
        /** @var \Illuminate\Database\Eloquent\Builder<\Modules\Newsletters\Models\NewsletterUser> $query */
        $query = $this->newsletterUserService->getQuery()
            ->search($this->search ?? null)
            ->ofStatus($this->statusFilter ?? null);

        // Email status filter
        if ($this->emailStatusFilter !== null && $this->emailStatusFilter !== '') {
            $query->where('email_status', $this->emailStatusFilter);
        }

        // Sorting: Referans modül kalıbına göre
        if ($this->sortBy === 'created_at' && $this->sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query;
    }

    public function render()
    {
        if (! Auth::user()->can('view newsletter_users')) {
            abort(403, 'Abone görüntüleme yetkiniz bulunmuyor.');
        }

        $users = $this->getUsersQuery()->paginate(Pagination::clamp($this->perPage));

        $statuses = NewsletterUserStatus::labels();

        $emailStatuses = [
            'verified' => 'Doğrulanmış',
            'unverified' => 'Doğrulanmamış',
        ];

        /** @var view-string $view */
        $view = 'newsletters::livewire.newsletter-user-index';

        return view($view, compact('users', 'statuses', 'emailStatuses'))
            ->extends('layouts.admin')->section('content');
    }

    public function getTurkishDate($date)
    {
        return SystemHelper::turkishDate($date);
    }

    public function getStatusBadge($status)
    {
        return SystemHelper::statusBadge($status);
    }

    public function getTruncatedText($text, $limit = 100)
    {
        return SystemHelper::truncateText($text, $limit);
    }
}
