<?php

namespace Modules\Posts\Livewire;

use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\HandlesExceptionsWithToast;
use App\Traits\ValidationMessages;
use Livewire\Component;
use Modules\Posts\Enums\PostType;
use Modules\Posts\Models\Post;

class PostEditContentForm extends Component
{
    use HandlesExceptionsWithToast, InteractsWithToast, ValidationMessages;

    public Post $post;

    public string $content = '';

    protected $listeners = [
        'postUpdated' => 'refreshFromPost',
        'contentUpdated',
        'collectData' => 'sendDataToParent',
    ];

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->loadFromPost();
    }

    public function loadFromPost()
    {
        $postTypeValue = $this->post->post_type instanceof PostType ? $this->post->post_type->value : $this->post->post_type;

        if ($postTypeValue === PostType::Gallery->value) {
            // Galeri için JSON formatında content
            $this->content = $this->post->content ?? '';
        } else {
            // Normal haber için HTML formatında content - JSON ise decode et
            $content = $this->post->content ?? '';
            if (! empty($content)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // JSON formatında ise, HTML formatına çevir veya boş bırak
                    $this->content = '';
                } else {
                    // Zaten HTML formatında
                    $this->content = $content;
                }
            } else {
                $this->content = '';
            }
        }
    }

    public function refreshFromPost()
    {
        $this->post->refresh();
        $this->loadFromPost();
    }

    public function contentUpdated($content)
    {
        $this->content = $content;
        $this->dispatch('contentChanged', $content);
    }

    public function getData(): array
    {
        return [
            'content' => $this->content,
        ];
    }

    public function validateContent(): bool
    {
        $rules = [
            'content' => 'nullable|string',
        ];

        try {
            $this->validate($rules);

            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validationFailed', ['component' => 'content', 'errors' => $e->errors()]);

            return false;
        }
    }

    public function sendDataToParent()
    {
        if ($this->validateContent()) {
            $this->dispatch('contentDataReady', $this->getData());
        }
    }

    public function render()
    {
        $postTypeValue = $this->post->post_type instanceof PostType ? $this->post->post_type->value : $this->post->post_type;
        $isGallery = $postTypeValue === PostType::Gallery->value;

        return view('posts::livewire.post-edit-content-form', compact('isGallery'));
    }
}
