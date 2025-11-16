<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Morph map for polymorphic relationships
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'post' => \Modules\Posts\Models\Post::class,
            'article' => \Modules\Articles\Models\Article::class,
        ]);

        // Register custom Blade directives for permissions
        \App\Helpers\PermissionHelper::registerBladeDirectives();
    }
}
