<?php

namespace Modules\Posts\Livewire;

use App\Contracts\SupportsToastErrors;
use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\HandlesExceptionsWithToast;
use App\Traits\ValidationMessages;
use Livewire\Component;
use Modules\Categories\Models\Category;
use Modules\Posts\Enums\PostType;
use Modules\Posts\Models\Post;

class PostEditRelationsForm extends Component implements SupportsToastErrors
{
    use HandlesExceptionsWithToast, InteractsWithToast, ValidationMessages;

    public Post $post;

    /** @var array<int> */
    public array $categoryIds = [];

    public string $tagsInput = '';

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
        $this->categoryIds = $this->post->categories->pluck('category_id')->toArray();

        // Tags'i string'e çevir
        /** @var \Illuminate\Database\Eloquent\Collection $tagsCollection */
        $tagsCollection = $this->post->tags;
        $tags = $tagsCollection->pluck('name')->toArray();
        $this->tagsInput = implode(', ', $tags);
    }

    public function refreshFromPost()
    {
        $this->post->refresh();
        $this->loadFromPost();
    }

    public function getData(): array
    {
        return [
            'categoryIds' => $this->categoryIds,
            'tagsInput' => $this->tagsInput,
        ];
    }

    public function validateRelations(): bool
    {
        $rules = [
            'categoryIds' => 'required|array|min:1',
            'categoryIds.*' => 'exists:categories,category_id',
            'tagsInput' => 'nullable|string',
        ];

        try {
            $this->validate($rules);

            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validationFailed', ['component' => 'relations', 'errors' => $e->errors()]);

            return false;
        }
    }

    public function sendDataToParent()
    {
        if ($this->validateRelations()) {
            $this->dispatch('relationsDataReady', $this->getData());
        }
    }

    public function handlePostTypeChange($postType)
    {
        // Post type değiştiğinde kategori listesini yenile
        $this->post->refresh();
    }

    public function render()
    {
        $postTypeValue = $this->post->post_type instanceof PostType ? $this->post->post_type->value : $this->post->post_type;

        $categories = Category::where('status', 'active')
            ->where('type', $postTypeValue)
            ->orderBy('name')
            ->get();

        return view('posts::livewire.post-edit-relations-form', compact('categories'));
    }
}
