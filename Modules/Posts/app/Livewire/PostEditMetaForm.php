<?php

namespace Modules\Posts\Livewire;

use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\HandlesExceptionsWithToast;
use App\Traits\ValidationMessages;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\Posts\Enums\PostType;
use Modules\Posts\Models\Post;

class PostEditMetaForm extends Component
{
    use HandlesExceptionsWithToast, InteractsWithToast, ValidationMessages;

    public Post $post;

    public string $title = '';

    public string $slug = '';

    public string $summary = '';

    public string $post_type = 'news';

    public bool $is_photo = false;

    public ?string $agency_name = null;

    public ?int $agency_id = null;

    public ?string $embed_code = null;

    protected $listeners = [
        'postUpdated' => 'refreshFromPost',
        'collectData' => 'sendDataToParent',
        'postTypeUpdated' => 'handlePostTypeChange',
    ];

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->loadFromPost();
    }

    public function loadFromPost()
    {
        $this->title = $this->post->title ?? '';
        $this->slug = $this->post->slug ?? '';
        $this->summary = $this->post->summary ?? '';
        $this->post_type = $this->post->post_type instanceof PostType ? $this->post->post_type->value : $this->post->post_type;
        $this->is_photo = $this->post->is_photo ?? false;
        $this->agency_name = $this->post->agency_name;
        $this->agency_id = $this->post->agency_id;
        $this->embed_code = $this->post->embed_code;
    }

    public function refreshFromPost()
    {
        $this->post->refresh();
        $this->loadFromPost();
    }

    public function updatedTitle($value)
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedPostType($value)
    {
        $this->dispatch('postTypeChanged', $value);
    }

    public function getData(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'post_type' => $this->post_type,
            'is_photo' => $this->is_photo,
            'agency_name' => $this->agency_name,
            'agency_id' => $this->agency_id,
            'embed_code' => $this->embed_code,
        ];
    }

    public function validateMeta(): bool
    {
        $rules = [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,'.$this->post->post_id.',post_id',
            'summary' => 'required|string',
            'post_type' => ['required', \Illuminate\Validation\Rule::enum(PostType::class)],
            'is_photo' => 'boolean',
            'agency_name' => 'nullable|string|max:255',
            'agency_id' => 'nullable|integer',
        ];

        // Video için embed_code zorunlu
        if ($this->post_type === PostType::Video->value) {
            $rules['embed_code'] = 'required|string';
        }

        try {
            $this->validate($rules);

            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validationFailed', ['component' => 'meta', 'errors' => $e->errors()]);

            return false;
        }
    }

    public function sendDataToParent()
    {
        if ($this->validateMeta()) {
            $this->dispatch('metaDataReady', $this->getData());
        }
    }

    public function handlePostTypeChange($postType)
    {
        $this->post_type = $postType;
    }

    public function render()
    {
        return view('posts::livewire.post-edit-meta-form');
    }
}
