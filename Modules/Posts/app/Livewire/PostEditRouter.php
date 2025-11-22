<?php

namespace Modules\Posts\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Posts\Models\Post;

class PostEditRouter extends Component
{
    public Post $post;

    /**
     * Image editor property - forwarded to nested components
     * This property exists to prevent Livewire errors when image editor
     * tries to update it on the router component instead of nested component
     */
    public ?string $primary_image_spot_data = null;

    public function mount(Post $post)
    {
        Gate::authorize('edit posts');

        // Eager load same relations as create pages
        $this->post = $post->load(['files', 'categories', 'tags', 'primaryFile', 'author', 'creator', 'updater']);
    }

    public function render()
    {
        /** @var view-string $view */
        $view = 'posts::livewire.post-edit-router';

        return view($view)
            ->extends('layouts.admin')
            ->section('content');
    }
}
