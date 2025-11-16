<?php

namespace Modules\Files\Livewire;

use App\Traits\SecureFileUpload;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Files\Models\File;
use Modules\Files\Services\FileService;

class FileUpload extends Component
{
    use SecureFileUpload, ValidationMessages, WithFileUploads;

    /** @var array<int, \Illuminate\Http\UploadedFile> */
    public array $files = [];

    /** @var array<int, array{alt_text: string, caption: string}> */
    public array $fileDescriptions = []; // Her dosya için ayrı açıklama

    /** @var array<int, \Illuminate\Http\UploadedFile> */
    public array $allFiles = []; // Tüm dosyalar (eklenen + yeni seçilen)

    /** @var array<int, array{alt_text: string, caption: string}> */
    public array $allDescriptions = []; // Tüm açıklamalar

    // Flash message properties
    public bool $showSuccessMessage = false;

    public bool $showErrorMessage = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    protected FileService $fileService;

    public function boot()
    {
        $this->fileService = app(FileService::class);
    }

    protected $rules = [
        'files.*' => 'required|file|max:10240', // 10MB max
        'fileDescriptions.*.alt_text' => 'nullable|string|max:255',
        'fileDescriptions.*.caption' => 'nullable|string|max:500',
    ];

    protected function messages()
    {
        return $this->getContextualValidationMessages()['file'] ?? $this->getValidationMessages();
    }

    public function mount()
    {
        Gate::authorize('create files');
    }

    public function updatedFiles()
    {
        if (empty($this->files)) {
            return;
        }

        $this->validate([
            'files.*' => 'required|file|max:10240',
        ]);

        // Yeni seçilen dosyaları mevcut dosyalara ekle
        foreach ($this->files as $file) {
            $this->allFiles[] = $file;
            $this->allDescriptions[] = [
                'alt_text' => '',
                'caption' => '',
            ];
        }

        // files array'ini temizle (sadece yeni seçimler için)
        $this->files = [];
    }

    public function uploadFiles()
    {
        Gate::authorize('create files');

        if (empty($this->allFiles)) {
            $this->errorMessage = 'Lütfen en az bir dosya seçin.';
            $this->showErrorMessage = true;

            return;
        }

        $uploadedCount = 0;

        foreach ($this->allFiles as $index => $file) {
            try {
                // Security validation using SecureFileUpload trait
                $validationErrors = $this->validateFile($file);
                if (! empty($validationErrors)) {
                    $this->errorMessage = implode(' ', $validationErrors);
                    $this->showErrorMessage = true;

                    return;
                }

                // Dosya adını oluştur (UUID for security)
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = Str::uuid().'.'.$extension;

                // Secure file storage
                $path = $this->storeFileSecurely($file, 'files');

                // Bu dosya için açıklamaları al
                $description = $this->allDescriptions[$index] ?? ['alt_text' => '', 'caption' => ''];

                // Post ID'yi URL'den al (medya kütüphanesi için)
                $postId = request()->get('post_id'); // Null olabilir

                // Veritabanına kaydet
                $this->fileService->create([
                    'post_id' => $postId, // Null olabilir
                    'title' => $originalName,
                    'alt_text' => $description['alt_text'] ?? '',
                    'caption' => $description['caption'] ?? '',
                    'primary' => false, // Ana dosya seçeneği kaldırıldı
                ], $file, 'files');

                $uploadedCount++;
            } catch (\Exception $e) {
                $this->errorMessage = 'Dosya yüklenirken hata oluştu: '.$e->getMessage();
                $this->showErrorMessage = true;

                return;
            }
        }

        session()->flash('success', $this->createContextualSuccessMessage('uploaded', 'name', 'file'));
        $this->reset(['files', 'fileDescriptions', 'allFiles', 'allDescriptions']);
        // Parent component'e modal'ı kapatmasını ve listeyi yenilemesini söyle
        $this->dispatch('closeUploadModal');
        $this->dispatch('filesUploaded');

    }

    public function removeFile($index)
    {
        unset($this->allFiles[$index]);
        unset($this->allDescriptions[$index]);
        $this->allFiles = array_values($this->allFiles);
        $this->allDescriptions = array_values($this->allDescriptions);
    }

    public function render()
    {
        /** @var view-string $view */
        $view = 'files::livewire.file-upload';

        // Eğer modal içinde render ediliyorsa layout kullanma
        if (request()->has('modal') || str_contains(request()->url(), 'modal')) {
            return view($view);
        }

        return view($view)
            ->extends('layouts.admin')->section('content');
    }
}
