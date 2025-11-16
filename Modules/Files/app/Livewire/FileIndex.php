<?php

namespace Modules\Files\Livewire;

use App\Livewire\Concerns\HasBulkActions;
use App\Livewire\Concerns\HasSearchAndFilters;
use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use App\Traits\HandlesExceptionsWithToast;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Files\Models\File;
use Modules\Files\Services\FileService;

/**
 * @property string|null $search
 * @property string|null $mimeType
 * @property int $perPage
 * @property array<int> $selectedFiles
 * @property bool $selectAll
 * @property string $bulkAction
 * @property bool $selectionMode
 * @property bool $showUploadModal
 * @property \Modules\Files\Models\File|null $editingFile
 * @property string $editAltText
 * @property string $editCaption
 * @property bool $showErrorMessage
 * @property string $errorMessage
 */
class FileIndex extends Component
{
    use HandlesExceptionsWithToast, InteractsWithToast, ValidationMessages;
    use HasBulkActions, HasSearchAndFilters;

    protected FileService $fileService;

    public int $perPage = 10;

    public ?string $mimeType = null;

    /** @var array<int> */
    public array $selectedFiles = [];

    /** @var array<int> Mevcut sayfadaki görünen file ID'leri - performans için */
    public array $visibleFileIds = [];

    public bool $selectionMode = false; // Medya kütüphanesi seçim modu

    public bool $showUploadModal = false; // Upload modal kontrolü

    // Edit file properties
    public ?\Modules\Files\Models\File $editingFile = null;

    public string $editAltText = '';

    public string $editCaption = '';

    // Flash message properties
    public bool $showErrorMessage = false;

    public string $errorMessage = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'mimeType' => ['except' => ''],
    ];

    protected $listeners = [
        'filesUploaded' => 'refreshFilesList',
        'closeUploadModal' => 'closeUploadModal',
    ];

    public function boot(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function mount()
    {
        Gate::authorize('view files');
    }

    /**
     * Get filter properties for HasSearchAndFilters trait
     */
    protected function getFilterProperties(): array
    {
        return ['search', 'mimeType'];
    }

    /**
     * Get selected items property name for HasBulkActions trait
     */
    protected function getSelectedItemsPropertyName(): string
    {
        return 'selectedFiles';
    }

    /**
     * Get visible item IDs for HasBulkActions trait
     */
    protected function getVisibleItemIds(): array
    {
        return $this->visibleFileIds;
    }

    /**
     * Handle updated method - combine both traits
     */
    public function updated($propertyName): void
    {
        // Handle search and filters
        if (in_array($propertyName, $this->getFilterProperties())) {
            $this->onFilterUpdated($propertyName);
        }

        // Handle bulk actions
        $selectedPropertyName = $this->getSelectedItemsPropertyName();
        if ($propertyName === $selectedPropertyName) {
            if (! is_array($this->$propertyName)) {
                $this->$propertyName = [];
            }

            $visibleIds = $this->getVisibleItemIds();
            $diff = array_diff($visibleIds, $this->$propertyName);
            $this->selectAll = empty($diff);
        }
    }

    public function applyBulkAction(): void
    {
        Gate::authorize('delete files');

        if ($this->bulkAction === 'delete' && ! empty($this->selectedFiles)) {
            $this->deleteSelectedFiles();
        }

        $this->clearBulkActionState();
    }

    public function deleteSelectedFiles()
    {
        try {
            $count = $this->fileService->deleteMultiple($this->selectedFiles);

            $this->toastSuccess($count.' dosya başarıyla silindi.');
            $this->selectedFiles = [];
            $this->selectAll = false;
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->handleException($e, 'Dosyalar silinirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'selected_files' => $this->selectedFiles ?? null,
            ]);
        }
    }

    public function deleteFile($fileId)
    {
        Gate::authorize('delete files');

        try {
            $file = File::find($fileId);
            if (! $file) {
                $this->errorMessage = 'Dosya bulunamadı.';
                $this->showErrorMessage = true;

                return;
            }

            $this->fileService->delete($file);

            $this->toastSuccess($this->createContextualSuccessMessage('deleted', 'name', 'file'));
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->handleException($e, 'Dosya silinirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'file_id' => $fileId,
            ]);
        }
    }

    public function editFile($fileId)
    {
        $file = File::find($fileId);
        if ($file) {
            $this->editingFile = $file;
            $this->editAltText = $file->alt_text ?? '';
            $this->editCaption = $file->caption ?? '';
        }
    }

    public function updateFile()
    {
        Gate::authorize('edit files');

        try {
            if ($this->editingFile) {
                $this->fileService->update($this->editingFile, [
                    'alt_text' => $this->editAltText,
                    'caption' => $this->editCaption,
                ]);

                $this->toastSuccess($this->createContextualSuccessMessage('updated', 'name', 'file'));

                // Form'u reset et ve modal'ı kapat
                $this->resetEditForm();
            }
        } catch (\Throwable $e) {
            $this->handleException($e, 'Dosya güncellenirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'file_id' => $this->editingFile->file_id ?? null,
            ]);
        }
    }

    public function resetEditForm()
    {
        $this->editingFile = null;
        $this->editAltText = '';
        $this->editCaption = '';
    }

    public function closeEditModal()
    {
        $this->resetEditForm();
    }

    /**
     * Dosya yüklendikten sonra listeyi yenile
     */
    public function refreshFilesList()
    {
        $this->resetPage(); // İlk sayfaya dön
        $this->toastSuccess($this->createContextualSuccessMessage('uploaded', 'name', 'file'));
    }

    /**
     * Seçim modunu aktif/pasif yap
     */
    public function toggleSelectionMode()
    {
        $this->selectionMode = ! $this->selectionMode;
        $this->selectedFiles = [];
        $this->selectAll = false;
    }

    /**
     * Upload modal'ını aç
     */
    public function openUploadModal()
    {
        $this->showUploadModal = true;
    }

    /**
     * Upload modal'ını kapat
     */
    public function closeUploadModal()
    {
        $this->showUploadModal = false;
    }

    public function clearFilters()
    {
        $this->search = null;
        $this->mimeType = null;
        $this->resetPage();
    }

    public function selectAllFiles()
    {
        $this->selectedFiles = $this->getFiles()->pluck('file_id')->toArray();
    }

    public function clearSelection()
    {
        $this->selectedFiles = [];
    }

    /**
     * Seçilen dosyaları onayla ve parent window'a gönder
     */
    public function confirmSelection()
    {
        if (empty($this->selectedFiles)) {
            $this->errorMessage = 'Lütfen en az bir dosya seçin.';
            $this->showErrorMessage = true;

            return;
        }

        $selectedFiles = File::whereIn('file_id', $this->selectedFiles)->get();

        // JavaScript ile parent window'a gönder
        $files = $selectedFiles->map(function ($file) {
            return [
                'id' => $file->file_id,
                'title' => $file->title,
                'url' => $file->url,
                'type' => $file->type,
                'alt_text' => $file->alt_text,
                'caption' => $file->caption,
            ];
        })->toArray();

        $this->dispatch('filesSelected', $files);
    }

    public function getFiles()
    {
        $filters = [
            'search' => $this->search,
            'mimeType' => $this->mimeType,
        ];

        return $this->fileService->getFilteredQuery($filters);
    }

    public function render()
    {
        /** @var view-string $view */
        $view = 'files::livewire.file-index';

        $files = $this->getFiles()->paginate(Pagination::clamp($this->perPage));

        // Mevcut sayfadaki görünen file ID'lerini kaydet - performans için
        $this->visibleFileIds = $files->pluck('file_id')->all();

        return view($view, [
            'files' => $files,
            'mimeTypes' => [
                'image' => 'Resimler',
                'video' => 'Videolar',
                'audio' => 'Ses Dosyaları',
                'application/pdf' => 'PDF Dosyaları',
                'text' => 'Metin Dosyaları',
            ],
        ])->extends('layouts.admin')->section('content');
    }
}
