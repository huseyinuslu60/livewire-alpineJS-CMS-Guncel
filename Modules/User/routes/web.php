<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Livewire\UserCreate;
use Modules\User\Livewire\UserEdit;
use Modules\User\Livewire\UserIndex;

// User modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'verified', 'permission:view users', 'module.active:user'])
    ->prefix('admin')
    ->name('user.')
    ->group(function () {
        Route::get('/users', UserIndex::class)->name('index');
    });

Route::middleware(['web', 'auth', 'verified', 'permission:create users', 'module.active:user'])
    ->prefix('admin')
    ->name('user.')
    ->group(function () {
        Route::get('/users/create', UserCreate::class)->name('create');
    });

Route::middleware(['web', 'auth', 'verified', 'permission:edit users', 'module.active:user'])
    ->prefix('admin')
    ->name('user.')
    ->group(function () {
        Route::get('/users/{user}/edit', UserEdit::class)->name('edit');
    });
