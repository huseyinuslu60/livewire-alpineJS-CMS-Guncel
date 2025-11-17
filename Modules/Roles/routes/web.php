<?php

use Illuminate\Support\Facades\Route;
use Modules\Roles\Livewire\RoleManagement;

// Roles modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'verified', 'permission:view roles', 'module.active:roles'])
    ->prefix('admin')
    ->name('role.')
    ->group(function () {
        Route::get('/role-management', RoleManagement::class)->name('management');
    });
