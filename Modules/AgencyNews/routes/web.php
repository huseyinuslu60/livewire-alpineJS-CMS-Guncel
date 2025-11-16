<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'permission:view agency_news'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/agencynews', \Modules\AgencyNews\Livewire\AgencyNewsIndex::class)->name('agencynews.index');
    });
