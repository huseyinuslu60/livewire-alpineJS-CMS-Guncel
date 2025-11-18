<?php

namespace Modules\Posts\Livewire;

use App\Support\Pagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Categories\Services\CategoryService;
use Modules\Posts\Domain\ValueObjects\PostStatus;
use Modules\Posts\Domain\ValueObjects\PostType;
use Modules\Posts\Services\PostsService;
use Modules\User\Services\UserService;

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
    use WithPagination;

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

    protected PostsService $postsService;

    protected UserService $userService;

    protected CategoryService $categoryService;

    public function boot()
    {
        $this->postsService = app(PostsService::class);
        $this->userService = app(UserService::class);
        $this->categoryService = app(CategoryService::class);
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'post_type' => ['except' => ''],
        'status' => ['except' => ''],
        'editorFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedPosts = $this->getPosts()->pluck('post_id')->toArray();
        } else {
            $this->selectedPosts = [];
        }
    }

    public function updatedSelectedPosts()
    {
        if (! is_array($this->selectedPosts)) {
            $this->selectedPosts = [];
        }
        $this->selectAll = count($this->selectedPosts) === $this->getPosts()->count();
    }

    public function applyBulkAction()
    {
        Gate::authorize('edit posts');

        if (empty($this->selectedPosts) || empty($this->bulkAction) || ! is_array($this->selectedPosts)) {
            return;
        }

        try {
            $selectedCount = count($this->selectedPosts);

            switch ($this->bulkAction) {
                case 'delete':
                    $this->postsService->bulkDelete($this->selectedPosts);
                    $message = $selectedCount.' haber başarıyla silindi.';
                    break;
                case 'activate':
                    $this->postsService->bulkUpdateStatus($this->selectedPosts, 'published');
                    $message = $selectedCount.' haber aktif yapıldı.';
                    break;
                case 'deactivate':
                    $this->postsService->bulkUpdateStatus($this->selectedPosts, 'draft');
                    $message = $selectedCount.' haber pasif yapıldı.';
                    break;
                case 'newsletter_add':
                    $this->postsService->bulkUpdateNewsletter($this->selectedPosts, true);
                    $message = $selectedCount.' haber bültene eklendi.';
                    break;
                case 'newsletter_remove':
                    $this->postsService->bulkUpdateNewsletter($this->selectedPosts, false);
                    $message = $selectedCount.' haber bültenden çıkarıldı.';
                    break;
                default:
                    return;
            }

            $this->selectedPosts = [];
            $this->selectAll = false;
            $this->bulkAction = '';

            session()->flash('success', $message);
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Toplu işlem sırasında bir hata oluştu: '.$e->getMessage());
        }
    }

    public function deletePost($id)
    {
        Gate::authorize('delete posts');

        try {
            $post = $this->postsService->findById($id);
            $this->postsService->delete($post);

            session()->flash('success', 'Haber başarıyla silindi.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Haber silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function toggleMainPage($id)
    {
        Gate::authorize('edit posts');

        try {
            $post = $this->postsService->findById($id);
            $this->postsService->toggleMainPage($post);

            $visibility = $post->is_mainpage ? 'gösterilecek' : 'gizlenecek';

            session()->flash('success', "Yazı ana sayfada {$visibility}.");
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Ana sayfa durumu güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function getPosts()
    {
        $query = $this->postsService->getQuery()
            ->with(['author', 'primaryFile', 'categories', 'tags', 'creator', 'updater']);

        if ($this->search !== null) {
            $query->search($this->search);
        }

        if ($this->post_type !== null) {
            $query->ofType($this->post_type);
        }

        if ($this->status !== null) {
            $query->ofStatus($this->status);
        }

        if ($this->editorFilter !== null) {
            $query->ofEditor($this->editorFilter);
        }

        if ($this->categoryFilter !== null) {
            $query->inCategory($this->categoryFilter);
        }

        return $query->latest('post_id')->paginate(Pagination::clamp($this->perPage));
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
        $editors = $this->userService->getQuery()
            ->whereHas('posts')
            ->select('id', 'name')
            ->orderBy('name')
            ->limit(100) // Referans listesi için limit
            ->get();

        // Kategorileri getir (küçük referans listesi - limit ile)
        $categories = $this->categoryService->getQuery()
            ->select('category_id', 'name')
            ->orderBy('name')
            ->limit(200) // Referans listesi için limit
            ->get();

        /** @var view-string $view */
        $view = 'posts::livewire.post-index';

        return view($view, [
            'posts' => $this->getPosts(),
            'postTypes' => PostType::labels(),
            'postStatuses' => PostStatus::labels(),
            'editors' => $editors,
            'categories' => $categories,
        ])->extends('layouts.admin')->section('content');
    }
}
