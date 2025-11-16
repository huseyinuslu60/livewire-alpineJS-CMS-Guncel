<?php

namespace Modules\Articles\Livewire;

use App\Helpers\SystemHelper;
use App\Models\User;
use App\Support\Pagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Articles\Models\Article;
use Modules\Articles\Services\ArticleService;

/**
 * @property string|null $search
 * @property string|null $statusFilter
 * @property string|null $authorFilter
 * @property string|null $categoryFilter
 * @property int $perPage
 * @property string $sortBy
 * @property string $sortDirection
 */
class ArticleIndex extends Component
{
    use WithPagination;

    public ?string $search = null;

    public ?string $statusFilter = null;

    public ?string $authorFilter = null;

    public ?string $categoryFilter = null;

    public int $perPage = 25;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    protected ArticleService $articleService;

    public function boot()
    {
        $this->articleService = app(ArticleService::class);
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'authorFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        Gate::authorize('view articles');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedAuthorFilter()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = null;
        $this->statusFilter = null;
        $this->authorFilter = null;
        $this->categoryFilter = null;
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

    public function confirmDeleteArticle($articleId)
    {
        $this->dispatch('confirm-delete-article', [
            'title' => 'Makale Sil',
            'message' => 'Bu makaleyi silmek istediğinizden emin misiniz?',
            'articleId' => $articleId,
        ]);
    }

    public function deleteArticle($articleId)
    {
        $article = Article::findOrFail($articleId);

        // Yetki bazlı kontrol: delete own articles veya delete all articles
        if (Auth::user()->can('delete all articles')) {
            Gate::authorize('delete all articles');
        } elseif (Auth::user()->can('delete own articles')) {
            Gate::authorize('delete own articles');
            if ($article->author_id !== Auth::id()) {
                abort(403, 'Sadece kendi makalelerinizi silebilirsiniz.');
            }
        } else {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $this->articleService->delete($article);

            session()->flash('success', 'Makale başarıyla silindi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Makale silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function toggleStatus($articleId)
    {
        if (! Auth::user()->can('edit articles')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $article = Article::findOrFail($articleId);
            $this->articleService->toggleStatus($article);

            session()->flash('success', 'Makale durumu güncellendi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Makale durumu güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function toggleMainPage($articleId)
    {
        if (! Auth::user()->can('edit articles')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $article = Article::findOrFail($articleId);
            $this->articleService->toggleMainPage($article);

            session()->flash('success', 'Ana sayfa durumu güncellendi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Ana sayfa durumu güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        $query = Article::with(['author', 'creator']);

        // Yetki bazlı kontrol: view all articles yetkisi yoksa sadece kendi makalelerini göster
        if (! Auth::user()->can('view all articles')) {
            $query->where('author_id', Auth::id());
        }

        if ($this->search !== null) {
            $query->search($this->search);
        }

        if ($this->statusFilter !== null) {
            $query->ofStatus($this->statusFilter);
        }

        // 0-yutmayan filtre: authorFilter - sadece view all articles yetkisi olanlar kullanabilir
        if ($this->authorFilter !== null && $this->authorFilter !== '' && Auth::user()->can('view all articles')) {
            $query->ofAuthor($this->authorFilter);
        }

        // Sorting: Referans modül kalıbına göre
        if ($this->sortBy === 'created_at' && $this->sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        $articles = $query->paginate(Pagination::clamp($this->perPage));

        // Sadece view all articles yetkisi olanlar yazar listesini görebilir
        $authors = Auth::user()->can('view all articles') ? User::select('id', 'name')->get() : collect();
        $statuses = [
            'draft' => 'Pasif',
            'published' => 'Aktif',
            'pending' => 'Beklemede',
        ];

        /** @var view-string $view */
        $view = 'articles::livewire.article-index';

        return view($view, compact('articles', 'authors', 'statuses'))
            ->extends('layouts.admin')->section('content');
    }

    /**
     * Helper method'ları view'da kullanmak için
     */
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
