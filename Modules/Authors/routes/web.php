<?php

use Illuminate\Support\Facades\Route;
use Modules\Authors\Livewire\AuthorCreate;
use Modules\Authors\Livewire\AuthorEdit;
use Modules\Authors\Livewire\AuthorIndex;

// Authors modülü - Permission kontrolleri ile
Route::middleware(['web', 'auth', 'permission:view authors'])
    ->prefix('admin')
    ->name('authors.')
    ->group(function () {
        Route::get('/authors', AuthorIndex::class)->name('index');
    });

Route::middleware(['web', 'auth', 'permission:create authors'])
    ->prefix('admin')
    ->name('authors.')
    ->group(function () {
        Route::get('/authors/create', AuthorCreate::class)->name('create');
    });

Route::middleware(['web', 'auth', 'permission:edit authors'])
    ->prefix('admin')
    ->name('authors.')
    ->group(function () {
        Route::get('/authors/{author}/edit', AuthorEdit::class)->name('edit');
    });
