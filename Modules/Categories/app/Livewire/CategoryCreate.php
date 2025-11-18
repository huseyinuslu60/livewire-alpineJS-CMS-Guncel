<?php

namespace Modules\Categories\Livewire;

use App\Services\SlugGenerator;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Categories\Models\Category;
use Modules\Categories\Services\CategoryService;

class CategoryCreate extends Component
{
    use ValidationMessages;

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

    protected CategoryService $categoryService;

    public function boot()
    {
        $this->categoryService = app(CategoryService::class);
    }

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

    public function mount()
    {
        Gate::authorize('create categories');
    }

    public function updatedName()
    {
        if (! $this->isSlugEditable && ! empty($this->name)) {
            $slugGenerator = app(SlugGenerator::class);
            $slug = $slugGenerator->generate($this->name, Category::class, 'slug', 'category_id');
            $this->slug = $slug->toString();
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
                'slug' => $this->slug ?: app(SlugGenerator::class)->generate($this->name, Category::class, 'slug', 'category_id')->toString(),
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
            $this->dispatch('category-created');

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('created', 'name', 'category'));

            return redirect()->route('categories.index');
        } catch (\InvalidArgumentException $e) {
            $this->isLoading = false;
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Kategori oluşturulurken hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        $parentCategories = $this->categoryService->getQuery()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        /** @var view-string $view */
        $view = 'categories::livewire.category-create';

        return view($view, [
            'parentCategories' => $parentCategories,
        ])->extends('layouts.admin')->section('content');
    }
}
