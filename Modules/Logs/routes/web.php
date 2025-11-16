<?php

use Illuminate\Support\Facades\Route;
use Modules\Logs\Livewire\LogDetail;
use Modules\Logs\Livewire\LogIndex;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Logs modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'permission:view logs', 'module.active:logs'])
    ->prefix('admin')
    ->name('logs.')
    ->group(function () {
        Route::get('/logs', LogIndex::class)->name('index');
        Route::get('/logs/{id}', LogDetail::class)->name('show');
    });
