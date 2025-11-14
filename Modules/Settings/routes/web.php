<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Livewire\MenuManagement;
use Modules\Settings\Livewire\SiteSettings;

Route::middleware(['web', 'auth', 'permission:view settings'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/settings', SiteSettings::class)->name('settings.index');
    });

Route::middleware(['web', 'auth', 'permission:manage menu'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/menu', MenuManagement::class)->name('menu.index');
    });
