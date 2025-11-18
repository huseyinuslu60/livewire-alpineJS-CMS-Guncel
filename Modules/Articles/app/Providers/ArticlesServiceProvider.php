<?php

namespace Modules\Articles\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ArticlesServiceProvider extends ServiceProvider
{
    /**
     * Uygulama event'lerini başlat.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Articles', 'database/migrations'));
        $this->registerViews();
        $this->registerLivewireComponents();
        $this->registerObservers();
    }

    /**
     * Service provider'ı kaydet.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Register Repository bindings
        $this->app->bind(
            \Modules\Articles\Domain\Repositories\ArticleRepositoryInterface::class,
            \Modules\Articles\Domain\Repositories\EloquentArticleRepository::class
        );
    }

    /**
     * View'ları kaydet.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/articles');
        $sourcePath = module_path('Articles', 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', 'articles-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), 'articles');
    }

    /**
     * Livewire component'lerini kaydet.
     */
    public function registerLivewireComponents(): void
    {
        Livewire::component('articles.article-index', \Modules\Articles\Livewire\ArticleIndex::class);
        Livewire::component('articles.article-create', \Modules\Articles\Livewire\ArticleCreate::class);
        Livewire::component('articles.article-edit', \Modules\Articles\Livewire\ArticleEdit::class);
    }

    /**
     * Provider tarafından sağlanan servisleri döndür.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/articles')) {
                $paths[] = $path.'/modules/articles';
            }
        }

        return $paths;
    }

    /**
     * Model observer'larını kaydet.
     */
    protected function registerObservers(): void
    {
        \Modules\Articles\Models\Article::observe(\Modules\Articles\Observers\ArticleObserver::class);
    }
}
