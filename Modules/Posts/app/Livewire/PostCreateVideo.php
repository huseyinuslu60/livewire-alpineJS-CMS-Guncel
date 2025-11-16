<?php

namespace Modules\Posts\Livewire;

use App\Traits\ValidationMessages;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Categories\Models\Category;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

class PostCreateVideo extends Component
{
    use ValidationMessages, WithFileUploads;

    public string $title = '';

    public string $slug = '';

    public string $summary = '';

    public string $content = '';

    public string $post_position = 'normal';

    public string $status = 'published';

    public string $published_date = '';

    public bool $is_comment = true;

    public bool $is_mainpage = false;

    public string $redirect_url = '';

    public bool $is_photo = false;

    public string $agency_name = '';

    public ?int $agency_id = null;

    public string $embed_code = '';

    public bool $in_newsletter = false;

    public bool $no_ads = false;

    /** @var array<int, \Illuminate\Http\UploadedFile> */
    public array $files = [];

    /** @var array<int> */
    public array $categoryIds = [];

    public string $tagsInput = '';

    public string $successMessage = '';

    public bool $isSaving = false;

    protected $listeners = ['contentUpdated'];

    protected function messages()
    {
        return $this->getContextualValidationMessages()['post'] ?? $this->getValidationMessages();
    }

    public function mount()
    {
        Gate::authorize('create posts');
        $this->published_date = Carbon::now()->format('Y-m-d H:i');
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'summary' => 'required|string',
            'content' => 'required|string',
            'post_position' => 'required|in:'.implode(',', Post::POSITIONS),
            'status' => 'required|in:'.implode(',', Post::STATUSES),
            'published_date' => 'nullable|date',
            'is_comment' => 'boolean',
            'is_mainpage' => 'boolean',
            'redirect_url' => 'nullable|url',
            'is_photo' => 'boolean',
            'agency_name' => 'nullable|string|max:255',
            'agency_id' => 'nullable|integer',
            'embed_code' => 'required|string',
            'in_newsletter' => 'boolean',
            'no_ads' => 'boolean',
            'categoryIds' => 'required|array|min:1',
            'categoryIds.*' => 'exists:categories,category_id',
            'tagsInput' => 'nullable|string',
            'files.*' => 'nullable|image|max:4096',
        ];
    }

    public function updatedTitle($value)
    {
        // Slug'ı her zaman güncelle, sadece boş değilse
        if (! empty(trim($value))) {
            // Türkçe karakterleri düzgün çevirmek için
            $turkishChars = [
                'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
                'Ç' => 'C', 'Ğ' => 'G', 'İ' => 'I', 'Ö' => 'O', 'Ş' => 'S', 'Ü' => 'U',
            ];

            // Türkçe karakterleri çevir ve fazla boşlukları temizle
            $convertedTitle = strtr($value, $turkishChars);
            $convertedTitle = preg_replace('/\s+/', ' ', trim($convertedTitle)); // Çoklu boşlukları tek boşluğa çevir
            $this->slug = Str::slug($convertedTitle);
        }
    }

    public function contentUpdated($content)
    {
        $this->content = $content;
    }

    public function removeFile($index)
    {
        if (isset($this->files[$index])) {
            unset($this->files[$index]);
            $this->files = array_values($this->files); // Re-index array
        }
    }

    public function savePost()
    {
        // Duplicate submit'i engelle
        if ($this->isSaving) {
            return;
        }

        Gate::authorize('create posts');

        $this->isSaving = true;

        try {
            // Slug'ı mutlaka unique yap - validation'dan ÖNCE
            // Eğer slug boşsa veya unique değilse, yeni bir unique slug oluştur
            $postsService = new PostsService;
            if (empty($this->slug)) {
                $this->slug = $postsService->makeUniqueSlug($this->title);
            } else {
                // Slug varsa ama unique değilse, unique yap
                if (Post::where('slug', $this->slug)->exists()) {
                    $this->slug = $postsService->makeUniqueSlug($this->title);
                }
            }

            // Validation'ı unique slug ile yap
            $this->validate();

            $tagIds = array_filter(array_map('trim', explode(',', $this->tagsInput)));

            $formData = [
                'title' => $this->title,
                'slug' => $this->slug,
                'summary' => $this->summary,
                'content' => $this->content,
                'post_type' => 'video',
                'post_position' => $this->post_position,
                'status' => $this->status,
                'published_date' => $this->published_date,
                'is_comment' => $this->is_comment,
                'is_mainpage' => $this->is_mainpage,
                'redirect_url' => $this->redirect_url,
                'is_photo' => $this->is_photo,
                'agency_name' => $this->agency_name,
                'agency_id' => $this->agency_id,
                'embed_code' => $this->embed_code,
                'in_newsletter' => $this->in_newsletter,
                'no_ads' => $this->no_ads,
            ];

            $postsService = new PostsService;
            $post = $postsService->create(
                $formData,
                $this->files,
                $this->categoryIds,
                $tagIds
            );

            $this->dispatch('post-created');

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('created', 'title', 'post'));

            return redirect()->route('posts.index');
        } catch (\Exception $e) {
            // Hata durumunda flag'i temizle
            $this->isSaving = false;
            throw $e;
        } finally {
            $this->isSaving = false;
        }
    }

    public function render()
    {
        // Sadece video kategorilerini getir
        $categories = Category::where('status', 'active')
            ->where('type', 'video')
            ->orderBy('name')
            ->get();

        $postPositions = Post::POSITIONS;
        $postStatuses = Post::STATUSES;

        /** @var view-string $view */
        $view = 'posts::livewire.post-create-video';

        return view($view, compact('categories', 'postPositions', 'postStatuses'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
