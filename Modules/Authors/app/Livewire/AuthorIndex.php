<?php

namespace Modules\Authors\Livewire;

use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use App\Traits\HandlesExceptionsWithToast;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Authors\Models\Author;
use Modules\Authors\Services\AuthorService;

/**
 * @property string|null $search
 * @property string|null $statusFilter
 * @property string|null $mainpageFilter
 */
class AuthorIndex extends Component
{
    use HandlesExceptionsWithToast, InteractsWithToast, WithPagination;

    protected AuthorService $authorService;

    public ?string $search = null;

    public ?string $statusFilter = null;

    public ?string $mainpageFilter = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'mainpageFilter' => ['except' => ''],
    ];

    public function boot(AuthorService $authorService)
    {
        $this->authorService = $authorService;
    }

    public function mount()
    {
        Gate::authorize('view authors');
    }

    public function toggleMainpage($authorId)
    {
        Gate::authorize('edit authors');

        try {
            $author = Author::findOrFail($authorId);
            $this->authorService->toggleMainPage($author);

            $visibility = $author->show_on_mainpage ? 'gösterilecek' : 'gizlenecek';
            $this->toastSuccess("Yazar ana sayfada {$visibility}.");
        } catch (\Throwable $e) {
            $this->handleException($e, 'Ana sayfa durumu güncellenirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'author_id' => $authorId,
            ]);
        }
    }

    public function toggleStatus($authorId)
    {
        Gate::authorize('edit authors');

        try {
            $author = Author::findOrFail($authorId);
            $this->authorService->toggleStatus($author);

            $status = $author->status ? 'aktif' : 'pasif';
            $this->toastSuccess("Yazar durumu {$status} olarak güncellendi.");
        } catch (\Throwable $e) {
            $this->handleException($e, 'Yazar durumu güncellenirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'author_id' => $authorId,
            ]);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedMainpageFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = null;
        $this->statusFilter = null;
        $this->mainpageFilter = null;
        $this->resetPage();
    }

    public function deleteAuthor($authorId)
    {
        Gate::authorize('delete authors');

        try {
            $author = Author::findOrFail($authorId);
            $this->authorService->delete($author);

            $this->toastSuccess('Yazar başarıyla silindi.');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Yazar silinirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'author_id' => $authorId,
            ]);
        }
    }

    public function render()
    {
        $filters = [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'mainpageFilter' => $this->mainpageFilter,
        ];

        $query = $this->authorService->getFilteredQuery($filters);
        $authors = $query->paginate(Pagination::clamp(null, 5, 100, 10));

        /** @var view-string $view */
        $view = 'authors::livewire.author-index';

        return view($view, compact('authors'))
            ->extends('layouts.admin')
            ->section('content')
            ->title('Yazar Yönetimi - Admin Panel');
    }
}
