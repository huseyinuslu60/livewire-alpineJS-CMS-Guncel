<?php

use Illuminate\Support\Facades\Route;
use Modules\Comments\Livewire\CommentsIndex;

// Comments modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'verified', 'permission:view comments', 'module.active:comments'])
    ->prefix('admin')
    ->name('comments.')
    ->group(function () {
        Route::get('/comments', CommentsIndex::class)->name('index');
    });
