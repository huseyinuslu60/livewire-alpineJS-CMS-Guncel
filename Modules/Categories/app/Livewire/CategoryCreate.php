<?php

namespace Modules\Categories\Livewire;

use App\Contracts\SupportsToastErrors;
use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\HandlesExceptionsWithToast;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\Categories\Models\Category;
use Modules\Categories\Services\CategoryService;

class CategoryCreate extends Component implements SupportsToastErrors
{
    use HandlesExceptionsWithToast, InteractsWithToast, ValidationMessages;

    protected CategoryService $categoryService;

    public string $name = '';

    public string $slug = '';

    public string $meta_title = '';

    public string $meta_description = '';

    public string $meta_keywords = '';

    public string $status = 'active';

    public string $type = '';

    public bool $show_in_menu = false;

    public int $weight = 0;

    public ?int $parent_id = null;

    public bool $isSlugEditable = false;

    public bool $isLoading = false;

    public ?string $successMessage = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:categories,slug',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string',
        'meta_keywords' => 'nullable|string',
        'status' => 'required|in:active,inactive,draft',
        'type' => 'required|in:news,gallery,video',
        'show_in_menu' => 'boolean',
        'weight' => 'integer|min:0',
        'parent_id' => 'nullable|exists:categories,category_id',
    ];

    protected function messages(): array
    {
        return $this->getContextualValidationMessages()['category'] ?? $this->getValidationMessages();
    }

    protected function attributes(): array
    {
        return $this->getAttributeNames();
    }

    public function boot(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function mount()
    {
        Gate::authorize('create categories');
    }

    public function updatedName()
    {
        if (! $this->isSlugEditable) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function toggleSlugEdit()
    {
        $this->isSlugEditable = ! $this->isSlugEditable;
    }

    public function save()
    {
        $this->isLoading = true;

        try {
            $this->validate();

            $data = [
                'name' => $this->name,
                'slug' => $this->slug,
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
                'status' => $this->status,
                'type' => $this->type,
                'show_in_menu' => $this->show_in_menu,
                'weight' => $this->weight,
                'parent_id' => $this->parent_id,
            ];

            $this->categoryService->create($data);

            // Toast mesajı göster
            $this->isLoading = false;
            $this->toastSuccess($this->createContextualSuccessMessage('created', 'name', 'category'), 6000);

            // Redirect - toast session flash'a da eklenecek
            return redirect()->route('categories.index');
        } catch (\Throwable $e) {
            $this->isLoading = false;
            $this->handleException($e, 'Kategori oluşturulurken bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    public function render()
    {
        $parentCategories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        /** @var view-string $view */
        $view = 'categories::livewire.category-create';

        return view($view, [
            'parentCategories' => $parentCategories,
        ])->extends('layouts.admin')->section('content');
    }
}
