<?php

use Illuminate\Support\Facades\Route;
use Modules\Files\Livewire\FileIndex;
use Modules\Files\Livewire\FileUpload;
use Modules\Files\Models\File;

// Files modülü - Admin route'lar (standardize edildi)
Route::middleware(['web', 'auth', 'verified', 'permission:view files', 'module.active:files'])
    ->prefix('admin')
    ->name('files.')
    ->group(function () {
        Route::get('/files', FileIndex::class)->name('index');
        Route::get('/files/create', FileUpload::class)->name('create');
        Route::get('/files/upload', FileUpload::class)->name('upload');

        // Download route
        Route::get('/files/{file}/download', function (File $file) {
            $filePath = public_path('storage/'.$file->file_path);

            if (! file_exists($filePath)) {
                abort(404, 'Dosya bulunamadı');
            }

            return response()->download($filePath, $file->title);
        })->name('download');

        // Edit image route - Controller
        Route::post('/files/edit-image', [\Modules\Files\Http\Controllers\ImageEditorController::class, 'editImage'])
            ->name('edit-image');
    });

// Deprecated: Eski route'lar (backward compatibility - kaldırılacak)
Route::middleware(['web', 'auth', 'verified'])
    ->group(function () {
        Route::get('/files', FileIndex::class)->name('files.deprecated.index');
        Route::get('/files/create', FileUpload::class)->name('files.deprecated.create');
        Route::get('/files/upload', FileUpload::class)->name('files.deprecated.upload');
    });
