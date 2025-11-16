<?php

namespace Modules\Categories\Livewire;

use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\Categories\Models\Category;
use Modules\Categories\Services\CategoryService;

class CategoryEdit extends Component
{
    use ValidationMessages;

    public ?\Modules\Categories\Models\Category $category = null;

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
        'slug' => 'nullable|string|max:255',
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

    public function mount($category)
    {
        Gate::authorize('edit categories');

        // Eğer $category string ise, Category model'ini bul
        if (is_string($category)) {
            $this->category = Category::findOrFail($category);
        } else {
            $this->category = $category;
        }

        $this->name = $this->category->name;
        $this->slug = $this->category->slug;
        $this->meta_title = $this->category->meta_title;
        $this->meta_description = $this->category->meta_description;
        $this->meta_keywords = $this->category->meta_keywords;
        $this->status = $this->category->status;
        $this->type = $this->category->type;
        $this->show_in_menu = $this->category->show_in_menu;
        $this->weight = $this->category->weight;
        $this->parent_id = $this->category->parent_id;
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
            // Slug unique kontrolü (kendi kaydı hariç)
            $this->rules['slug'] = 'nullable|string|max:255|unique:categories,slug,'.$this->category->category_id.',category_id';

            $this->validate();

            $data = [
                'name' => $this->name,
                'slug' => $this->slug ?: Str::slug($this->name),
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
                'status' => $this->status,
                'type' => $this->type,
                'show_in_menu' => $this->show_in_menu,
                'weight' => $this->weight,
                'parent_id' => $this->parent_id,
            ];

            $this->categoryService->update($this->category, $data);

            // Toast mesajı göster
            $this->isLoading = false;
            $this->dispatch('category-updated');

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('updated', 'name', 'category'));

            return redirect()->route('categories.index');
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Kategori güncellenirken hata oluştu: '.$e->getMessage());
        }
    }

    public function update()
    {
        $this->save();
    }

    public function render()
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('category_id', '!=', $this->category->category_id)
            ->orderBy('name')
            ->get();

        /** @var view-string $view */
        $view = 'categories::livewire.category-edit';

        return view($view, [
            'parentCategories' => $parentCategories,
        ])->extends('layouts.admin')->section('content');
    }
}
