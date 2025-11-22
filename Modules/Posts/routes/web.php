<?php

use Illuminate\Support\Facades\Route;
use Modules\Posts\Livewire\PostCreateGallery;
use Modules\Posts\Livewire\PostCreateNews;
use Modules\Posts\Livewire\PostCreateVideo;
use Modules\Posts\Livewire\PostEditRouter;
use Modules\Posts\Livewire\PostIndex;

Route::middleware(['web', 'auth', 'permission:view posts', 'throttle:30,1'])
    ->prefix('admin')
    ->name('posts.')
    ->group(function () {
        Route::get('/posts', PostIndex::class)->name('index');
    });

Route::middleware(['web', 'auth', 'permission:create posts', 'throttle:20,1'])
    ->prefix('admin')
    ->name('posts.')
    ->group(function () {
        Route::get('/posts/create/news', PostCreateNews::class)->name('create.news');
        Route::get('/posts/create/gallery', PostCreateGallery::class)->name('create.gallery');
        Route::get('/posts/create/video', PostCreateVideo::class)->name('create.video');
    });

Route::middleware(['web', 'auth', 'permission:edit posts', 'throttle:20,1'])
    ->prefix('admin')
    ->name('posts.')
    ->group(function () {
        Route::get('/posts/{post}/edit', PostEditRouter::class)->name('edit');
    });
