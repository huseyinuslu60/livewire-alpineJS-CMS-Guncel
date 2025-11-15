<?php

namespace Modules\Posts\Livewire;

use App\Livewire\Concerns\InteractsWithModal;
use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

/**
 * @property string|null $search
 * @property string|null $post_type
 * @property string|null $status
 * @property string|null $editorFilter
 * @property string|null $categoryFilter
 * @property int $perPage
 * @property array<int> $selectedPosts
 * @property bool $selectAll
 * @property string $bulkAction
 * @property array<string, bool> $visibleColumns
 */
class PostIndex extends Component
{
    use InteractsWithModal, InteractsWithToast, WithPagination;

    protected PostsService $postsService;

    public ?string $search = null;

    public ?string $post_type = null;

    public ?string $status = null;

    public ?string $editorFilter = null;

    public ?string $categoryFilter = null;

    public int $perPage = 10;

    /** @var array<int> */
    public array $selectedPosts = [];

    public bool $selectAll = false;

    public string $bulkAction = '';

    /** @var array<string, bool> */
    public array $visibleColumns = [];

    /** @var array<int> Mevcut sayfadaki görünen post ID'leri - performans için */
    public array $visiblePostIds = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'post_type' => ['except' => ''],
        'status' => ['except' => ''],
        'editorFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
    ];

    public function boot()
    {
        $this->postsService = app(PostsService::class);
    }

    public function updatedSearch()
    {
        $this->resetSelection();
        $this->resetPage();
    }

    public function updatedPostType()
    {
        $this->resetSelection();
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetSelection();
        $this->resetPage();
    }

    public function updatedEditorFilter()
    {
        $this->resetSelection();
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetSelection();
        $this->resetPage();
    }

    /**
     * Selection'ı sıfırla - filtre değişikliklerinde kullanılır
     */
    protected function resetSelection(): void
    {
        $this->selectedPosts = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        // DB query yok - sadece visiblePostIds kullan
        if ($value) {
            $this->selectedPosts = $this->visiblePostIds;
        } else {
            $this->selectedPosts = [];
        }
    }

    public function updatedSelectedPosts()
    {
        if (! is_array($this->selectedPosts)) {
            $this->selectedPosts = [];
        }
        // DB query yok - sadece visiblePostIds kullan
        $diff = array_diff($this->visiblePostIds, $this->selectedPosts);
        $this->selectAll = empty($diff);
    }

    public function applyBulkAction()
    {
        Gate::authorize('edit posts');

        if (empty($this->selectedPosts) || empty($this->bulkAction) || ! is_array($this->selectedPosts)) {
            return;
        }

        try {
            $message = $this->postsService->applyBulkAction($this->bulkAction, $this->selectedPosts);

            $this->selectedPosts = [];
            $this->selectAll = false;
            $this->bulkAction = '';

            $this->toastSuccess($message);
        } catch (\Exception $e) {
            $this->toastError('Toplu işlem sırasında bir hata oluştu: '.$e->getMessage());
        }
    }

    public function confirmDeletePost($id)
    {
        $this->confirmModal(
            'Haber Sil',
            'Bu haberi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
            'deletePost',
            ['id' => $id],
            [
                'confirmLabel' => 'Sil',
                'cancelLabel' => 'İptal',
            ]
        );
    }

    public function deletePost($id)
    {
        Gate::authorize('delete posts');

        try {
            $post = Post::findOrFail($id);
            $this->postsService->delete($post);

            $this->toastSuccess('Haber başarıyla silindi.');
        } catch (\Exception $e) {
            $this->toastError('Haber silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function toggleMainPage($id)
    {
        Gate::authorize('edit posts');

        try {
            $post = Post::findOrFail($id);
            $newValue = $this->postsService->toggleMainPage($post);

            $visibility = $newValue ? 'gösterilecek' : 'gizlenecek';

            $this->toastSuccess("Yazı ana sayfada {$visibility}.");
        } catch (\Exception $e) {
            $this->toastError('Ana sayfa durumu güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function getPosts()
    {
        $filters = [
            'search' => $this->search,
            'post_type' => $this->post_type,
            'status' => $this->status,
            'editorFilter' => $this->editorFilter,
            'categoryFilter' => $this->categoryFilter,
            'sortBy' => 'post_id',
            'sortDirection' => 'desc',
        ];

        $query = $this->postsService->getFilteredQuery($filters);

        return $query->paginate(Pagination::clamp($this->perPage));
    }

    public function mount()
    {
        Gate::authorize('view posts');
        $this->loadUserColumnPreferences();
    }

    public function loadUserColumnPreferences()
    {
        $user = Auth::user();
        $defaultColumns = [
            'checkbox' => true,
            'id' => true,
            'image' => true,
            'title' => true,
            'category' => true,
            'type' => true,
            'hit' => true,
            'status' => true,
            'creator' => true,
            'updater' => true,
            'date' => true,
            'actions' => true,
        ];

        if ($user && $user instanceof \App\Models\User && $user->table_columns) {
            $userColumns = is_array($user->table_columns) ? $user->table_columns : json_decode($user->table_columns, true) ?? [];
            $this->visibleColumns = array_merge($defaultColumns, $userColumns);
        } else {
            $this->visibleColumns = $defaultColumns;
        }
    }

    public function updatedVisibleColumns()
    {
        // Kullanıcının tercihlerini kaydet
        $user = Auth::user();
        if ($user) {
            $user->update(['table_columns' => $this->visibleColumns]);
        }
    }

    public function render()
    {
        // Sadece yazı oluşturan kullanıcıları getir (küçük referans listesi - limit ile)
        $editors = \App\Models\User::whereHas('posts')
            ->select('id', 'name')
            ->orderBy('name')
            ->limit(100) // Referans listesi için limit
            ->get();

        // Kategorileri getir (küçük referans listesi - limit ile)
        $categories = \Modules\Categories\Models\Category::select('category_id', 'name')
            ->orderBy('name')
            ->limit(200) // Referans listesi için limit
            ->get();

        $posts = $this->getPosts();

        // Mevcut sayfadaki görünen post ID'lerini kaydet - performans için
        $this->visiblePostIds = $posts->pluck('post_id')->all();

        /** @var view-string $view */
        $view = 'posts::livewire.post-index';

        return view($view, [
            'posts' => $posts,
            'postTypes' => Post::getTypeLabels(),
            'postStatuses' => Post::getStatusLabels(),
            'editors' => $editors,
            'categories' => $categories,
        ])->extends('layouts.admin')->section('content');
    }
}
