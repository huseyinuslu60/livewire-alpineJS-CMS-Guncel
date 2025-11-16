<?php

namespace Modules\Comments\Livewire;

use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use App\Traits\HandlesExceptionsWithToast;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Comments\Models\Comment;
use Modules\Comments\Services\CommentService;

/**
 * @property string|null $search
 * @property string|null $statusFilter
 * @property int $perPage
 * @property string $sortBy
 * @property string $sortDirection
 * @property array<int, string> $editedCommentTexts
 * @property bool $skipRender
 */
class CommentsIndex extends Component
{
    use InteractsWithToast, HandlesExceptionsWithToast, WithPagination;

    protected CommentService $commentService;

    public ?string $search = null;

    public ?string $statusFilter = null;

    public int $perPage = 10;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    // Yorum düzenleme için
    /** @var array<int, string> */
    public array $editedCommentTexts = [];

    // Render kontrolü için
    public bool $skipRender = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function boot(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function mount()
    {
        Gate::authorize('view comments');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
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
    }

    public function clearFilters()
    {
        $this->search = null;
        $this->statusFilter = null;
        $this->resetPage();
    }

    public function approve($commentId)
    {
        Gate::authorize('approve comments');

        try {
            $comment = Comment::findOrFail($commentId);
            $this->commentService->approve($comment);

            // Sayfa yeniden render'ını engelle
            $this->skipRender = true;

            // Event dispatch et
            $this->dispatch('comment-updated', $commentId);

            // Success mesajını JavaScript ile göster
            $this->dispatch('show-success', 'Yorum onaylandı.');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Yorum onaylanırken bir hata oluştu. Lütfen tekrar deneyin.', [
                'comment_id' => $commentId,
            ]);
        }
    }

    public function reject($commentId)
    {
        Gate::authorize('reject comments');

        try {
            $comment = Comment::findOrFail($commentId);
            $this->commentService->reject($comment);

            // Sayfa yeniden render'ını engelle
            $this->skipRender = true;

            // Event dispatch et
            $this->dispatch('comment-updated', $commentId);

            // Success mesajını JavaScript ile göster
            $this->dispatch('show-success', 'Yorum reddedildi.');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Yorum reddedilirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'comment_id' => $commentId,
            ]);
        }
    }

    public function deleteComment($commentId)
    {
        Gate::authorize('delete comments');

        try {
            $comment = Comment::findOrFail($commentId);
            $this->commentService->delete($comment);
            $this->toastSuccess('Yorum silindi.');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Yorum silinirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'comment_id' => $commentId,
            ]);
        }
    }

    public function saveAndApprove($commentId)
    {
        Gate::authorize('approve comments');

        try {
            $comment = Comment::findOrFail($commentId);
            $newText = $this->editedCommentTexts[$commentId] ?? $comment->comment_text;

            // Validation: Yorum metni boş olamaz
            if (empty(trim($newText))) {
                $this->dispatch('show-error', 'Yorum metni boş olamaz.');

                return;
            }

            $this->commentService->updateAndApprove($comment, $newText);

            unset($this->editedCommentTexts[$commentId]);
            $this->toastSuccess('Yorum düzenlendi ve onaylandı.');

            // Sadece bu yorumu yeniden render et, tüm sayfayı değil
            $this->dispatch('comment-updated', $commentId);
        } catch (\Throwable $e) {
            $this->handleException($e, 'Yorum düzenlenirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'comment_id' => $commentId,
            ]);
        }
    }

    public function update($commentId)
    {
        Gate::authorize('update comments');

        try {
            $comment = Comment::findOrFail($commentId);
            $newText = $this->editedCommentTexts[$commentId] ?? $comment->comment_text;

            // Validation: Yorum metni boş olamaz
            if (empty(trim($newText))) {
                $this->dispatch('show-error', 'Yorum metni boş olamaz.');

                return;
            }

            $this->commentService->update($comment, $newText);

            unset($this->editedCommentTexts[$commentId]);
            $this->toastSuccess('Yorum başarıyla güncellendi.');

            // Sadece bu yorumu yeniden render et, tüm sayfayı değil
            $this->dispatch('comment-updated', $commentId);
        } catch (\Throwable $e) {
            $this->handleException($e, 'Yorum güncellenirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'comment_id' => $commentId,
            ]);
        }
    }

    public function render()
    {
        $filters = [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ];

        $query = $this->commentService->getFilteredQuery($filters);
        $comments = $query->paginate(Pagination::clamp($this->perPage));

        $stats = [
            'total' => Comment::count(),
            'approved' => Comment::approved()->count(),
            'pending' => Comment::pending()->count(),
            'rejected' => Comment::rejected()->count(),
        ];

        // Yeni yorumlar için başlangıç değerlerini ayarla
        /** @var Comment $comment */
        foreach ($comments as $comment) {
            if (! isset($this->editedCommentTexts[$comment->comment_id])) {
                $this->editedCommentTexts[$comment->comment_id] = $comment->comment_text;
            }
        }

        /** @var view-string $view */
        $view = 'comments::livewire.comments-index';

        return view($view, compact('comments', 'stats'))
            ->extends('layouts.admin')
            ->section('content')
            ->title('Yorum Yönetimi');
    }
}
