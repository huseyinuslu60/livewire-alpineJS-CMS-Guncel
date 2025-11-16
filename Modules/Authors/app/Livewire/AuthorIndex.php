<?php

namespace Modules\Authors\Livewire;

use App\Support\Pagination;
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
    use WithPagination;

    public ?string $search = null;

    public ?string $statusFilter = null;

    public ?string $mainpageFilter = null;

    protected AuthorService $authorService;

    public function boot()
    {
        $this->authorService = app(AuthorService::class);
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'mainpageFilter' => ['except' => ''],
    ];

    public function mount()
    {
        Gate::authorize('view authors');
    }

    public function toggleMainpage($authorId)
    {
        Gate::authorize('edit authors');

        try {
            $author = Author::findOrFail($authorId);
            $this->authorService->toggleMainpage($author);

            $visibility = $author->show_on_mainpage ? 'gösterilecek' : 'gizlenecek';
            session()->flash('success', "Yazar ana sayfada {$visibility}.");
        } catch (\Exception $e) {
            session()->flash('error', 'Ana sayfa durumu güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function toggleStatus($authorId)
    {
        Gate::authorize('edit authors');

        try {
            $author = Author::findOrFail($authorId);
            $this->authorService->toggleStatus($author);

            $status = $author->status ? 'aktif' : 'pasif';
            session()->flash('success', "Yazar durumu {$status} olarak güncellendi.");
        } catch (\Exception $e) {
            session()->flash('error', 'Yazar durumu güncellenirken bir hata oluştu: '.$e->getMessage());
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

            session()->flash('success', 'Yazar başarıyla silindi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Yazar silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        $query = Author::with('user');

        if ($this->search !== null) {
            $query->search($this->search);
        }

        if ($this->statusFilter !== null) {
            $query->ofStatus($this->statusFilter);
        }

        if ($this->mainpageFilter !== null) {
            $query->where('show_on_mainpage', $this->mainpageFilter);
        }

        $authors = $query
            ->orderBy('weight', 'asc')
            ->latest('created_at')
            ->paginate(Pagination::clamp(null, 5, 100, 10));

        /** @var view-string $view */
        $view = 'authors::livewire.author-index';

        return view($view, compact('authors'))
            ->extends('layouts.admin')
            ->section('content')
            ->title('Yazar Yönetimi - Admin Panel');
    }
}
