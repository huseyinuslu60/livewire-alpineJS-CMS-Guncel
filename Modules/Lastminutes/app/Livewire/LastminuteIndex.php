<?php

namespace Modules\Lastminutes\Livewire;

use App\Support\Pagination;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Lastminutes\Services\LastminuteService;

class LastminuteIndex extends Component
{
    use ValidationMessages, WithPagination;

    public string $search = '';

    public string $status = 'all';

    public int $perPage = 10;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    protected LastminuteService $lastminuteService;

    public function boot()
    {
        $this->lastminuteService = app(LastminuteService::class);
    }

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        Gate::authorize('view lastminutes');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
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
        $this->resetPage();
    }

    public function deleteLastminute($id)
    {
        Gate::authorize('delete lastminutes');

        $lastminute = $this->lastminuteService->findById($id);
        $this->lastminuteService->delete($lastminute);

        $this->dispatch('lastminute-deleted');
        session()->flash('success', 'Son dakika başarıyla silindi.');
    }

    public function toggleStatus($id)
    {
        Gate::authorize('edit lastminutes');

        $lastminute = $this->lastminuteService->findById($id);

        if ($lastminute->status === 'active') {
            $lastminute->deactivate();
            $message = 'Son dakika pasif yapıldı.';
        } else {
            $lastminute->activate();
            $message = 'Son dakika aktif yapıldı.';
        }

        $this->dispatch('lastminute-status-changed');
        session()->flash('success', $message);
    }

    public function markAsExpired($id)
    {
        Gate::authorize('edit lastminutes');

        $lastminute = $this->lastminuteService->findById($id);
        $lastminute->markAsExpired();

        $this->dispatch('lastminute-expired');
        session()->flash('success', 'Son dakika süresi dolmuş olarak işaretlendi.');
    }

    public function render()
    {
        /** @var \Illuminate\Database\Eloquent\Builder<\Modules\Lastminutes\Models\Lastminute> $query */
        $query = $this->lastminuteService->getQuery()
            ->search($this->search ?? null)
            ->ofStatus($this->status ?? null);

        // Sorting: Referans modül kalıbına göre
        if ($this->sortBy === 'created_at' && $this->sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        $lastminutes = $query->paginate(Pagination::clamp($this->perPage ?? null));

        $statusOptions = [
            'all' => 'Tümü',
            'active' => 'Aktif',
            'inactive' => 'Pasif',
            'expired' => 'Süresi Dolmuş',
        ];

        /** @var view-string $view */
        $view = 'lastminutes::livewire.lastminute-index';

        return view($view, compact('lastminutes', 'statusOptions'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
