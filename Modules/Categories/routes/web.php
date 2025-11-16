<?php

use Illuminate\Support\Facades\Route;
use Modules\Categories\Livewire\CategoryCreate;
use Modules\Categories\Livewire\CategoryEdit;
use Modules\Categories\Livewire\CategoryIndex;
use Modules\Categories\Livewire\CategoryShow;

// Categories modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'permission:view categories', 'module.active:categories'])
    ->prefix('admin')
    ->name('categories.')
    ->group(function () {
        Route::get('/categories', CategoryIndex::class)->name('index');
    });

Route::middleware(['web', 'auth', 'permission:create categories', 'module.active:categories'])
    ->prefix('admin')
    ->name('categories.')
    ->group(function () {
        Route::get('/categories/create', CategoryCreate::class)->name('create');
    });

Route::middleware(['web', 'auth', 'permission:edit categories', 'module.active:categories'])
    ->prefix('admin')
    ->name('categories.')
    ->group(function () {
        Route::get('/categories/{category}/edit', CategoryEdit::class)->name('edit');
        Route::get('/categories/{category}', CategoryShow::class)->name('show');
    });
