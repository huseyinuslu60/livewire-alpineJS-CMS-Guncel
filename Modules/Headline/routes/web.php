<?php

use Illuminate\Support\Facades\Route;
use Modules\Headline\Http\Livewire\Manage;

// Headline modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'permission:view featured'])
    ->prefix('admin')
    ->name('headline.')
    ->group(function () {
        Route::get('/featured', Manage::class)->name('index');
    });
