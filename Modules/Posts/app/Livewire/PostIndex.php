<?php

namespace Modules\Posts\Livewire;

use App\Livewire\Concerns\HasBulkActions;
use App\Livewire\Concerns\HasColumnPreferences;
use App\Livewire\Concerns\HasSearchAndFilters;
use App\Livewire\Concerns\InteractsWithModal;
use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use App\Traits\HandlesExceptionsWithToast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
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
    use InteractsWithModal, InteractsWithToast, HandlesExceptionsWithToast;
    use HasSearchAndFilters, HasBulkActions, HasColumnPreferences;

    protected PostsService $postsService;

    public int $perPage = 10;

    /** @var array<int> */
    public array $selectedPosts = [];

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

    /**
     * Get filter properties for HasSearchAndFilters trait
     */
    protected function getFilterProperties(): array
    {
        return ['search', 'post_type', 'status', 'editorFilter', 'categoryFilter'];
    }

    /**
     * Get selected items property name for HasBulkActions trait
     */
    protected function getSelectedItemsPropertyName(): string
    {
        return 'selectedPosts';
    }

    /**
     * Get visible item IDs for HasBulkActions trait
     */
    protected function getVisibleItemIds(): array
    {
        return $this->visiblePostIds;
    }

    /**
     * Handle updated method - combine both traits
     */
    public function updated($propertyName): void
    {
        // Handle search and filters
        if (in_array($propertyName, $this->getFilterProperties())) {
            $this->onFilterUpdated($propertyName);
        }

        // Handle bulk actions
        $selectedPropertyName = $this->getSelectedItemsPropertyName();
        if ($propertyName === $selectedPropertyName) {
            if (! is_array($this->$propertyName)) {
                $this->$propertyName = [];
            }

            $visibleIds = $this->getVisibleItemIds();
            $diff = array_diff($visibleIds, $this->$propertyName);
            $this->selectAll = empty($diff);
        }
    }

    public function applyBulkAction(): void
    {
        Gate::authorize('edit posts');

        if (empty($this->selectedPosts) || empty($this->bulkAction) || ! is_array($this->selectedPosts)) {
            return;
        }

        try {
            $message = $this->postsService->applyBulkAction($this->bulkAction, $this->selectedPosts);

            $this->clearBulkActionState();

            $this->toastSuccess($message);
        } catch (\Throwable $e) {
            $this->handleException($e, 'Toplu işlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', [
                'selected_ids' => $this->selectedPosts ?? null,
                'bulk_action' => $this->bulkAction ?? null,
            ]);
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
        } catch (\Throwable $e) {
            $this->handleException($e, 'Haber silinirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'post_id' => $id,
            ]);
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
        } catch (\Throwable $e) {
            $this->handleException($e, 'Ana sayfa durumu güncellenirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'post_id' => $id,
            ]);
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

    /**
     * Get default columns for HasColumnPreferences trait
     */
    protected function getDefaultColumns(): array
    {
        return [
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
