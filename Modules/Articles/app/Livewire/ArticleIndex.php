<?php

namespace Modules\Articles\Livewire;

use App\Helpers\SystemHelper;
use App\Livewire\Concerns\InteractsWithModal;
use App\Livewire\Concerns\InteractsWithToast;
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
    use InteractsWithModal, InteractsWithToast, WithPagination;

    protected ArticleService $articleService;

    public ?string $search = null;

    public ?string $statusFilter = null;

    public ?string $authorFilter = null;

    public ?string $categoryFilter = null;

    public int $perPage = 25;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'authorFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function boot(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

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

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function updatedSortDirection()
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

    public function confirmDeleteArticle($articleId)
    {
        $this->confirmModal(
            'Makale Sil',
            'Bu makaleyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
            'deleteArticle',
            ['id' => $articleId],
            [
                'confirmLabel' => 'Sil',
                'cancelLabel' => 'İptal',
            ]
        );
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
                $this->toastError('Sadece kendi makalelerinizi silebilirsiniz.');

                return;
            }
        } else {
            $this->toastError('Bu işlem için yetkiniz bulunmuyor.');

            return;
        }

        try {
            $this->articleService->delete($article);

            $this->toastSuccess('Makale başarıyla silindi.');
        } catch (\Exception $e) {
            $this->toastError('Makale silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function toggleStatus($articleId)
    {
        if (! Auth::user()->can('edit articles')) {
            $this->toastError('Bu işlem için yetkiniz bulunmuyor.');

            return;
        }

        try {
            $article = Article::findOrFail($articleId);
            $this->articleService->toggleStatus($article);

            $this->toastSuccess('Makale durumu güncellendi.');
        } catch (\Exception $e) {
            $this->toastError('Makale durumu güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function toggleMainPage($articleId)
    {
        if (! Auth::user()->can('edit articles')) {
            $this->toastError('Bu işlem için yetkiniz bulunmuyor.');

            return;
        }

        try {
            $article = Article::findOrFail($articleId);
            $this->articleService->toggleMainPage($article);

            $this->toastSuccess('Ana sayfa durumu güncellendi.');
        } catch (\Exception $e) {
            $this->toastError('Ana sayfa durumu güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        $canViewAll = Auth::user()->can('view all articles');

        $filters = [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'authorFilter' => $this->authorFilter,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ];

        $query = $this->articleService->getFilteredQuery($filters, $canViewAll);
        $articles = $query->paginate(Pagination::clamp($this->perPage));

        // Mevcut sayfadaki görünen article ID'lerini kaydet - performans için (gelecekte selection özelliği eklendiğinde kullanılabilir)
        // $this->visibleArticleIds = $articles->pluck('article_id')->all();

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
