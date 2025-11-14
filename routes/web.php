<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Services\AIContentSuggestionService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Auth routes
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
// Login route with rate limiting: 5 attempts per minute
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modules Management - Sadece super_admin erişebilir
    Route::middleware(['permission:view modules'])->group(function () {
        Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    });
    Route::middleware(['permission:activate modules'])->group(function () {
        Route::post('/modules/{module}/toggle-status', [ModuleController::class, 'toggleStatus'])->name('modules.toggle-status');
    });
    Route::middleware(['permission:edit modules'])->group(function () {
        Route::put('/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    });

    // AI Test Route (geçici) - Sadece admin ve super_admin erişebilir
    Route::middleware(['permission:view articles'])->group(function () {
        Route::get('/test-ai', function () {
            $aiService = new AIContentSuggestionService;
            $suggestions = $aiService->getContentSuggestions(5);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
                'count' => count($suggestions),
            ]);
        })->name('test.ai');

        // AI Önerilerini Yenile
        Route::post('/refresh-ai-suggestions', function () {
            // Cache'i temizle
            \Illuminate\Support\Facades\Cache::forget('ai_content_suggestions_'.date('Y-m-d-H-i'));

            $aiService = new AIContentSuggestionService;
            $suggestions = $aiService->getContentSuggestions(5);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
                'count' => count($suggestions),
                'refreshed_at' => now()->format('d.m.Y H:i'),
            ]);
        })->name('refresh.ai.suggestions');
    });
});

// Logout route
Route::middleware(['auth'])->group(function () {
    Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
});
