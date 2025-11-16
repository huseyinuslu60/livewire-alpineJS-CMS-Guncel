<?php

use Illuminate\Support\Facades\Route;
use Modules\Lastminutes\Livewire\LastminuteCreate;
use Modules\Lastminutes\Livewire\LastminuteEdit;
use Modules\Lastminutes\Livewire\LastminuteIndex;

// Lastminutes modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'verified', 'permission:view lastminutes'])
    ->prefix('admin')
    ->name('lastminutes.')
    ->group(function () {
        Route::get('/lastminutes', LastminuteIndex::class)->name('index');
    });

Route::middleware(['web', 'auth', 'verified', 'permission:create lastminutes'])
    ->prefix('admin')
    ->name('lastminutes.')
    ->group(function () {
        Route::get('/lastminutes/create', LastminuteCreate::class)->name('create');
    });

Route::middleware(['web', 'auth', 'verified', 'permission:edit lastminutes'])
    ->prefix('admin')
    ->name('lastminutes.')
    ->group(function () {
        Route::get('/lastminutes/{lastminute}/edit', LastminuteEdit::class)->name('edit');
    });
