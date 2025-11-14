<?php

use Illuminate\Support\Facades\Route;
use Modules\Banks\Livewire\InvestorQuestionAnswer;
use Modules\Banks\Livewire\InvestorQuestionIndex;
use Modules\Banks\Livewire\StockCreate;
use Modules\Banks\Livewire\StockEdit;
use Modules\Banks\Livewire\StockIndex;

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

// Banks modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'permission:view stocks', 'module.active:banks'])
    ->prefix('admin')
    ->name('banks.')
    ->group(function () {
        // Stocks Routes
        Route::prefix('banks/stocks')->name('stocks.')->group(function () {
            Route::get('/', StockIndex::class)->name('index');
            Route::get('/create', StockCreate::class)->name('create');
            Route::get('/{id}/edit', StockEdit::class)->name('edit');
        });

        // Investor Questions Routes
        Route::prefix('banks/investor-questions')->name('investor-questions.')->group(function () {
            Route::get('/', InvestorQuestionIndex::class)->name('index');
            Route::get('/{id}/answer', InvestorQuestionAnswer::class)->name('answer');
        });
    });
