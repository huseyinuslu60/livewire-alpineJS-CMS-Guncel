<?php

use Illuminate\Support\Facades\Route;
use Modules\Articles\Livewire\ArticleCreate;
use Modules\Articles\Livewire\ArticleEdit;
use Modules\Articles\Livewire\ArticleIndex;

// Articles modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'permission:view articles', 'module.active:articles'])
    ->prefix('admin')
    ->name('articles.')
    ->group(function () {
        Route::get('/articles', ArticleIndex::class)->name('index');
    });

Route::middleware(['web', 'auth', 'permission:create articles', 'module.active:articles'])
    ->prefix('admin')
    ->name('articles.')
    ->group(function () {
        Route::get('/articles/create', ArticleCreate::class)->name('create');
    });

Route::middleware(['web', 'auth', 'permission:edit articles', 'module.active:articles'])
    ->prefix('admin')
    ->name('articles.')
    ->group(function () {
        Route::get('/articles/{article}', function ($article) {
            return redirect()->route('articles.edit', $article);
        })->name('show');
        Route::get('/articles/{article}/edit', ArticleEdit::class)->name('edit');
    });
