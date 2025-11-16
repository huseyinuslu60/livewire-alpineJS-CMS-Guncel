<?php

namespace Modules\Categories\Livewire;

use App\Livewire\Concerns\InteractsWithModal;
use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use App\Traits\HandlesExceptionsWithToast;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Categories\Models\Category;
use Modules\Categories\Services\CategoryService;

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
    use HandlesExceptionsWithToast, InteractsWithModal, InteractsWithToast, WithPagination;

    protected CategoryService $categoryService;

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

    public function boot(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

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

    public function confirmDeleteCategory($categoryId)
    {
        $this->confirmModal(
            'Kategori Sil',
            'Bu kategoriyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
            'deleteCategory',
            ['id' => $categoryId],
            [
                'confirmLabel' => 'Sil',
                'cancelLabel' => 'İptal',
            ]
        );
    }

    public function deleteCategory($categoryId)
    {
        Gate::authorize('delete categories');

        try {
            $category = Category::findOrFail($categoryId);
            $this->categoryService->delete($category);

            $this->toastSuccess('Kategori başarıyla silindi.');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Kategori silinirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'category_id' => $categoryId,
            ]);
        }
    }

    public function render()
    {
        $filters = [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'typeFilter' => $this->typeFilter,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ];

        $query = $this->categoryService->getFilteredQuery($filters);
        $categories = $query->paginate(Pagination::clamp($this->perPage));

        /** @var view-string $view */
        $view = 'categories::livewire.category-index';

        return view($view, [
            'categories' => $categories,
        ])->extends('layouts.admin')->section('content');
    }
}
