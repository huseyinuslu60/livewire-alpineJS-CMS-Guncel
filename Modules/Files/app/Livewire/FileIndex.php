<?php

namespace Modules\Files\Livewire;

use App\Support\Pagination;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Files\Models\File;

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
    use ValidationMessages, WithPagination;

    public ?string $search = null;

    public ?string $mimeType = null;

    public int $perPage = 10;

    /** @var array<int> */
    public array $selectedFiles = [];

    public bool $selectAll = false;

    public string $bulkAction = '';

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

    public function mount()
    {
        Gate::authorize('view files');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedMimeType()
    {
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedFiles = $this->getFiles()->pluck('id')->toArray();
        } else {
            $this->selectedFiles = [];
        }
    }

    public function updatedSelectedFiles()
    {
        $this->selectAll = count($this->selectedFiles) === $this->getFiles()->count();
    }

    public function applyBulkAction()
    {
        Gate::authorize('delete files');

        if ($this->bulkAction === 'delete' && ! empty($this->selectedFiles)) {
            $this->deleteSelectedFiles();
        }

        $this->selectedFiles = [];
        $this->selectAll = false;
        $this->bulkAction = '';
    }

    public function deleteSelectedFiles()
    {
        try {
            $files = File::whereIn('file_id', $this->selectedFiles)->get();

            foreach ($files as $file) {
                // Fiziksel dosyayı sil
                if (file_exists(public_path('storage/'.$file->file_path))) {
                    unlink(public_path('storage/'.$file->file_path));
                }

                // Veritabanından sil
                $file->delete();
            }

            session()->flash('success', count($files).' dosya başarıyla silindi.');
            $this->selectedFiles = [];
            $this->selectAll = false;
            $this->resetPage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Dosyalar silinirken hata oluştu: '.$e->getMessage();
            $this->showErrorMessage = true;
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

            // Fiziksel dosyayı sil
            if (file_exists(public_path('storage/'.$file->file_path))) {
                unlink(public_path('storage/'.$file->file_path));
            }

            $file->delete();

            session()->flash('success', $this->createContextualSuccessMessage('deleted', 'name', 'file'));
            $this->resetPage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Dosya silinirken hata oluştu: '.$e->getMessage();
            $this->showErrorMessage = true;
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
                $this->editingFile->update([
                    'alt_text' => $this->editAltText,
                    'caption' => $this->editCaption,
                ]);

                session()->flash('success', $this->createContextualSuccessMessage('updated', 'name', 'file'));

                // Form'u reset et ve modal'ı kapat
                $this->resetEditForm();
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Dosya güncellenirken hata oluştu: '.$e->getMessage();
            $this->showErrorMessage = true;
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
        session()->flash('success', $this->createContextualSuccessMessage('uploaded', 'name', 'file'));
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
        $query = File::query(); // Tüm dosyaları göster

        if ($this->search !== null) {
            $query->search($this->search);
        }

        if ($this->mimeType !== null) {
            if ($this->mimeType === 'image') {
                $query->images();
            } else {
                $query->ofType($this->mimeType);
            }
        }

        return $query->sortedLatest('updated_at')->orderBy('file_id', 'desc');
    }

    public function render()
    {
        /** @var view-string $view */
        $view = 'files::livewire.file-index';

        return view($view, [
            'files' => $this->getFiles()->paginate(Pagination::clamp($this->perPage)),
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
