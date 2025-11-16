<?php

use Illuminate\Support\Facades\Route;
use Modules\Newsletters\Livewire\NewsletterCreate;
use Modules\Newsletters\Livewire\NewsletterEdit;
use Modules\Newsletters\Livewire\NewsletterIndex;
use Modules\Newsletters\Livewire\NewsletterLogIndex;
use Modules\Newsletters\Livewire\NewsletterUserIndex;
use Modules\Newsletters\Livewire\TemplateCreate;
use Modules\Newsletters\Livewire\TemplateEdit;
use Modules\Newsletters\Livewire\TemplateIndex;

// Newsletters modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'permission:view newsletters'])
    ->prefix('admin')
    ->name('newsletters.')
    ->group(function () {
        // Newsletter Routes
        Route::get('/newsletters', NewsletterIndex::class)->name('index');
        Route::get('/newsletters/create', NewsletterCreate::class)->name('create');
        Route::get('/newsletters/{newsletter}/edit', NewsletterEdit::class)->name('edit');

        // Newsletter Users Routes
        Route::prefix('newsletters/users')->name('users.')->group(function () {
            Route::get('/', NewsletterUserIndex::class)->name('index');
        });

        // Newsletter Logs Routes
        Route::prefix('newsletters/logs')->name('logs.')->group(function () {
            Route::get('/', NewsletterLogIndex::class)->name('index');
        });

        // Newsletter Templates Routes
        Route::prefix('newsletters/templates')->name('templates.')->group(function () {
            Route::get('/', TemplateIndex::class)->name('index');
            Route::get('/create', TemplateCreate::class)->name('create');
            Route::get('/{id}/edit', TemplateEdit::class)->name('edit');
        });
    });
