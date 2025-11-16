<?php

namespace Modules\Posts\Livewire;

use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\HandlesExceptionsWithToast;
use App\Traits\ValidationMessages;
use Livewire\Component;
use Modules\Posts\Enums\PostPosition;
use Modules\Posts\Enums\PostStatus;
use Modules\Posts\Models\Post;

class PostEditMetaFormSidebar extends Component
{
    use InteractsWithToast, HandlesExceptionsWithToast, ValidationMessages;

    public Post $post;

    public string $status = 'published';

    public string $post_position = 'normal';

    public string $published_date = '';

    public ?string $redirect_url = null;

    public bool $is_comment = true;

    public bool $is_mainpage = false;

    public bool $in_newsletter = false;

    public bool $no_ads = false;

    public string $featuredStartsAt = '';

    public string $featuredEndsAt = '';

    protected $listeners = [
        'postUpdated' => 'refreshFromPost',
        'collectData' => 'sendDataToParent',
    ];

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->loadFromPost();
    }

    public function loadFromPost()
    {
        $this->status = $this->post->status instanceof PostStatus ? $this->post->status->value : $this->post->status;
        $this->post_position = $this->post->post_position instanceof PostPosition ? $this->post->post_position->value : $this->post->post_position;
        $this->published_date = $this->post->published_date ? \Carbon\Carbon::parse($this->post->published_date)->format('Y-m-d\TH:i') : '';
        $this->redirect_url = $this->post->redirect_url;
        $this->is_comment = $this->post->is_comment ?? true;
        $this->is_mainpage = $this->post->is_mainpage ?? false;
        $this->in_newsletter = $this->post->in_newsletter ?? false;
        $this->no_ads = $this->post->no_ads ?? false;
    }

    public function refreshFromPost()
    {
        $this->post->refresh();
        $this->loadFromPost();
    }

    public function updatedIsMainpage($value)
    {
        if ($value) {
            try {
                $this->dispatch('mainpageStatusChanged', $value);
            } catch (\Throwable $e) {
                $this->handleException($e, 'Ana sayfa durumu güncellenirken bir hata oluştu. Lütfen tekrar deneyin.');
            }
        }
    }

    public function getData(): array
    {
        return [
            'status' => $this->status,
            'post_position' => $this->post_position,
            'published_date' => $this->published_date ?: null,
            'redirect_url' => $this->redirect_url,
            'is_comment' => $this->is_comment,
            'is_mainpage' => $this->is_mainpage,
            'in_newsletter' => $this->in_newsletter,
            'no_ads' => $this->no_ads,
            'featured_starts_at' => $this->featuredStartsAt ?: null,
            'featured_ends_at' => $this->featuredEndsAt ?: null,
        ];
    }

    public function sendDataToParent()
    {
        // Sidebar meta data'yı parent'a gönder
        $this->dispatch('metaSidebarDataReady', $this->getData());
    }

    public function render()
    {
        $postPositions = PostPosition::options();
        $postStatuses = PostStatus::options();

        return view('posts::livewire.post-edit-meta-form-sidebar', compact('postPositions', 'postStatuses'));
    }
}

