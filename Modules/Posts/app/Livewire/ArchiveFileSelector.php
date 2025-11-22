<?php

namespace Modules\Posts\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Files\Services\FileService;

/**
 * Archive File Selector Component
 * Posts modülü için özel arşiv dosya seçici component
 */
class ArchiveFileSelector extends Component
{
    use WithPagination;

    public ?string $search = null;

    public ?string $mimeType = null;

    public int $perPage = 24;

    /** @var array<int> */
    public array $selectedFiles = [];

    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'mimeType' => ['except' => ''],
    ];

    protected FileService $fileService;

    public function boot()
    {
        $this->fileService = app(FileService::class);
    }

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

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedFiles = $this->getFiles()->pluck('file_id')->toArray();
        } else {
            $this->selectedFiles = [];
        }
    }

    public function toggleFileSelection($fileId)
    {
        if (in_array($fileId, $this->selectedFiles)) {
            $this->selectedFiles = array_values(array_diff($this->selectedFiles, [$fileId]));
        } else {
            $this->selectedFiles[] = $fileId;
        }
        $this->selectAll = false;
    }

    public function clearSelection()
    {
        $this->selectedFiles = [];
        $this->selectAll = false;
    }

    public function confirmSelection()
    {
        if (empty($this->selectedFiles)) {
            session()->flash('error', 'Lütfen en az bir dosya seçin.');

            return;
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, \Modules\Posts\Models\File> $selectedFiles */
        $selectedFiles = $this->fileService->getQuery()
            ->whereIn('file_id', $this->selectedFiles)
            ->get();

        $files = $selectedFiles->map(function (\Modules\Posts\Models\File $file) {
            // Type'ı belirle - eğer type yoksa mime_type'dan türet
            $type = $file->type ?? null;
            if (! $type && $file->mime_type) {
                if (str_starts_with($file->mime_type, 'image/')) {
                    $type = 'image';
                } elseif (str_starts_with($file->mime_type, 'video/')) {
                    $type = 'video';
                } else {
                    $type = 'file';
                }
            }

            return [
                'id' => $file->file_id,
                'title' => $file->title ?? '',
                'url' => $file->url ?? '',
                'type' => $type ?? 'file',
                'alt_text' => $file->alt_text ?? '',
                'caption' => $file->caption ?? '',
            ];
        })->toArray();

        // Seçili dosyaları geçici olarak sakla
        $filesToSend = $files;

        // Seçimi temizle (bir sonraki açılışta temiz olsun)
        $this->selectedFiles = [];
        $this->selectAll = false;

        // Livewire event dispatch et
        $this->dispatch('filesSelected', $filesToSend);

        // Modal'ı kapatmak için JavaScript event'i de gönder
        $this->dispatch('closeArchiveModal');
    }

    protected function getFiles()
    {
        $query = $this->fileService->getQuery();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('alt_text', 'like', '%'.$this->search.'%')
                    ->orWhere('caption', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->mimeType && $this->mimeType !== 'all') {
            // MIME type filtresi - extension veya mime_type'a göre filtrele
            if ($this->mimeType === 'image') {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%.jpg')
                        ->orWhere('title', 'like', '%.jpeg')
                        ->orWhere('title', 'like', '%.png')
                        ->orWhere('title', 'like', '%.gif')
                        ->orWhere('title', 'like', '%.webp');
                });
            } elseif ($this->mimeType === 'video') {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%.mp4')
                        ->orWhere('title', 'like', '%.avi')
                        ->orWhere('title', 'like', '%.mov')
                        ->orWhere('title', 'like', '%.webm');
                });
            } elseif ($this->mimeType === 'application') {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%.pdf')
                        ->orWhere('title', 'like', '%.doc')
                        ->orWhere('title', 'like', '%.docx');
                });
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function render()
    {
        $files = $this->getFiles();

        // Select all durumunu güncelle
        if (count($this->selectedFiles) === $files->count() && $files->count() > 0) {
            $this->selectAll = true;
        }

        return view('posts::livewire.archive-file-selector', [
            'files' => $files,
        ]);
    }
}
