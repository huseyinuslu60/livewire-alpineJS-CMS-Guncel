<?php

namespace Modules\Categories\Livewire;

use App\Support\Pagination;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Categories\Models\Category;

/**
 * @property string|null $search
 * @property string|null $statusFilter
 * @property string|null $typeFilter
 * @property int $perPage
 * @property string $sortField
 * @property string $sortDirection
 */
class CategoryIndex extends Component
{
    use WithPagination;

    public ?string $search = null;

    public ?string $statusFilter = null;

    public ?string $typeFilter = null;

    public int $perPage = 10;

    public string $sortField = 'weight';

    public string $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'weight'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount()
    {
        Gate::authorize('view categories');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        // Force component refresh
        $this->render();
    }

    // Alternative method name
    public function sort($field)
    {
        $this->sortBy($field);
    }

    public function deleteCategory($categoryId)
    {
        Gate::authorize('delete categories');

        $category = Category::findOrFail($categoryId);

        // Alt kategorileri kontrol et
        if ($category->children()->count() > 0) {
            session()->flash('error', 'Bu kategorinin alt kategorileri bulunuyor. Önce alt kategorileri silin.');

            return;
        }

        $category->delete();
        session()->flash('success', 'Kategori başarıyla silindi.');
    }

    public function render()
    {
        $query = Category::with(['parent', 'children']);

        if ($this->search !== null) {
            $query->search($this->search);
        }

        if ($this->statusFilter !== null) {
            $query->ofStatus($this->statusFilter);
        }

        if ($this->typeFilter !== null) {
            $query->ofType($this->typeFilter);
        }

        // Sorting: Referans modül kalıbına göre
        if ($this->sortField === 'created_at' && $this->sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $categories = $query->paginate(Pagination::clamp($this->perPage));

        /** @var view-string $view */
        $view = 'categories::livewire.category-index';

        return view($view, [
            'categories' => $categories,
        ])->extends('layouts.admin')->section('content');
    }
}
