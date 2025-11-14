<?php

namespace Modules\Categories\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Categories\Models\Category;

class CategoryShow extends Component
{
    public ?\Modules\Categories\Models\Category $category = null;

    public function mount($category)
    {
        if (! Auth::user()->can('view categories')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        // Eğer $category string ise, Category model'ini bul
        if (is_string($category)) {
            $this->category = Category::with(['parent', 'children'])->findOrFail($category);
        } else {
            $this->category = $category->load(['parent', 'children']);
        }
    }

    public function render()
    {
        /** @var view-string $view */
        $view = 'categories::livewire.category-show';

        return view($view)
            ->extends('layouts.admin')->section('content');
    }
}
