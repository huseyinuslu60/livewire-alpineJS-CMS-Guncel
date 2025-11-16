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

        // Register model observers for HTML sanitization
        \Modules\Posts\Models\Post::observe(\App\Observers\PostObserver::class);
        \Modules\Articles\Models\Article::observe(\App\Observers\ArticleObserver::class);
        \Modules\AgencyNews\Models\AgencyNews::observe(\App\Observers\AgencyNewsObserver::class);

        // Register file observers for XSS protection (title, alt_text, caption)
        \Modules\Files\Models\File::observe(\App\Observers\FileObserver::class);
        \Modules\Posts\Models\File::observe(\App\Observers\PostFileObserver::class);
    }
}
