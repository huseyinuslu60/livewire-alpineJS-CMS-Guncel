<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationEvents;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, string>>
     */
    protected $listen = [
        Login::class => [
            LogAuthenticationEvents::class.'@handleLogin',
        ],
        Logout::class => [
            LogAuthenticationEvents::class.'@handleLogout',
        ],
        Failed::class => [
            LogAuthenticationEvents::class.'@handleFailed',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register sanitization observers
        $this->registerSanitizationObservers();
    }

    /**
     * Register sanitization observers for content sanitization
     */
    protected function registerSanitizationObservers(): void
    {
        // Post content sanitization
        \Modules\Posts\Models\Post::observe(\App\Observers\PostObserver::class);

        // Article content sanitization
        \Modules\Articles\Models\Article::observe(\App\Observers\ArticleObserver::class);

        // AgencyNews content sanitization
        if (class_exists(\Modules\AgencyNews\Models\AgencyNews::class)) {
            \Modules\AgencyNews\Models\AgencyNews::observe(\App\Observers\AgencyNewsObserver::class);
        }

        // File sanitization (Files module)
        if (class_exists(\Modules\Files\Models\File::class)) {
            \Modules\Files\Models\File::observe(\App\Observers\FileObserver::class);
        }

        // Post File sanitization (Posts module File model)
        \Modules\Posts\Models\File::observe(\App\Observers\PostFileObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
