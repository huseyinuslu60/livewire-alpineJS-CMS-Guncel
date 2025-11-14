<?php

namespace Modules\Comments\Livewire;

use App\Support\Pagination;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Comments\Models\Comment;

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
    use WithPagination;

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
            $comment->update(['status' => 'approved']);

            // Sayfa yeniden render'ını engelle
            $this->skipRender = true;

            // Event dispatch et
            $this->dispatch('comment-updated', $commentId);

            // Success mesajını JavaScript ile göster
            $this->dispatch('show-success', 'Yorum onaylandı.');
        } catch (\Exception $e) {
            $this->dispatch('show-error', 'Yorum onaylanırken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function reject($commentId)
    {
        Gate::authorize('reject comments');

        try {
            $comment = Comment::findOrFail($commentId);
            $comment->update(['status' => 'rejected']);

            // Sayfa yeniden render'ını engelle
            $this->skipRender = true;

            // Event dispatch et
            $this->dispatch('comment-updated', $commentId);

            // Success mesajını JavaScript ile göster
            $this->dispatch('show-success', 'Yorum reddedildi.');
        } catch (\Exception $e) {
            $this->dispatch('show-error', 'Yorum reddedilirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function deleteComment($commentId)
    {
        Gate::authorize('delete comments');

        try {
            $comment = Comment::findOrFail($commentId);
            $comment->delete();
            session()->flash('success', 'Yorum silindi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Yorum silinirken bir hata oluştu: '.$e->getMessage());
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

            $comment->update([
                'comment_text' => $newText,
                'status' => 'approved',
            ]);

            unset($this->editedCommentTexts[$commentId]);
            session()->flash('success', 'Yorum düzenlendi ve onaylandı.');

            // Sadece bu yorumu yeniden render et, tüm sayfayı değil
            $this->dispatch('comment-updated', $commentId);
        } catch (\Exception $e) {
            session()->flash('error', 'Yorum düzenlenirken bir hata oluştu: '.$e->getMessage());
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

            $comment->update([
                'comment_text' => $newText,
            ]);

            unset($this->editedCommentTexts[$commentId]);
            session()->flash('success', 'Yorum başarıyla güncellendi.');

            // Sadece bu yorumu yeniden render et, tüm sayfayı değil
            $this->dispatch('comment-updated', $commentId);
        } catch (\Exception $e) {
            session()->flash('error', 'Yorum güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        $query = Comment::query();

        if ($this->search !== null) {
            $query->search($this->search);
        }

        if ($this->statusFilter !== null) {
            $query->ofStatus($this->statusFilter);
        }

        // Sorting: Referans modül kalıbına göre
        if ($this->sortBy === 'created_at' && $this->sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        $comments = $query->paginate(Pagination::clamp($this->perPage));

        $stats = [
            'total' => Comment::count(),
            'approved' => Comment::approved()->count(),
            'pending' => Comment::pending()->count(),
            'rejected' => Comment::rejected()->count(),
        ];

        // Yeni yorumlar için başlangıç değerlerini ayarla
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
