<?php

namespace Modules\Lastminutes\Livewire;

use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Lastminutes\Models\Lastminute;
use Modules\Lastminutes\Services\LastminuteService;

class LastminuteIndex extends Component
{
    use ValidationMessages, WithPagination, InteractsWithToast;

    protected LastminuteService $lastminuteService;

    public string $search = '';

    public string $status = 'all';

    public int $perPage = 10;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function boot(LastminuteService $lastminuteService)
    {
        $this->lastminuteService = $lastminuteService;
    }

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

        $lastminute = Lastminute::findOrFail($id);
        $this->lastminuteService->delete($lastminute);

        $this->toastSuccess('Son dakika başarıyla silindi.');
    }

    public function toggleStatus($id)
    {
        Gate::authorize('edit lastminutes');

        $lastminute = Lastminute::findOrFail($id);
        $this->lastminuteService->toggleStatus($lastminute);

        $this->toastSuccess('Son dakika durumu güncellendi.');
    }

    public function markAsExpired($id)
    {
        Gate::authorize('edit lastminutes');

        $lastminute = Lastminute::findOrFail($id);
        $this->lastminuteService->markAsExpired($lastminute);

        $this->toastSuccess('Son dakika süresi dolmuş olarak işaretlendi.');
    }

    public function render()
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->status,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ];

        $query = $this->lastminuteService->getFilteredQuery($filters);
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
