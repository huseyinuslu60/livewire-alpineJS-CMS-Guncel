<?php

namespace Modules\Categories\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CategoriesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repository bindings
        $this->app->bind(
            \Modules\Categories\Domain\Repositories\CategoryRepositoryInterface::class,
            \Modules\Categories\Domain\Repositories\EloquentCategoryRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'categories');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        // Blade components
        Blade::componentNamespace('Modules\\Categories\\View\\Components', 'categories');

        // Register model observers
        $this->registerObservers();

        // Livewire components
        Livewire::component('categories.index', \Modules\Categories\Livewire\CategoryIndex::class);
        Livewire::component('categories.create', \Modules\Categories\Livewire\CategoryCreate::class);
        Livewire::component('categories.edit', \Modules\Categories\Livewire\CategoryEdit::class);
        Livewire::component('categories.show', \Modules\Categories\Livewire\CategoryShow::class);
    }

    /**
     * Register model observers
     */
    protected function registerObservers(): void
    {
        \Modules\Categories\Models\Category::observe(\Modules\Categories\Observers\CategoryObserver::class);
    }
}
