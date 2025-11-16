<?php

namespace Modules\Posts\Livewire;

use App\Contracts\SupportsToastErrors;
use App\Livewire\Concerns\InteractsWithToast;
use App\Services\FileUploadService;
use App\Traits\HandlesExceptionsWithToast;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Posts\Enums\PostType;
use Modules\Posts\Models\File;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

class PostEditMediaManager extends Component implements SupportsToastErrors
{
    use HandlesExceptionsWithToast, InteractsWithToast, ValidationMessages, WithFileUploads;

    public Post $post;

    /** @var array<int, \Illuminate\Http\UploadedFile> */
    public array $newFiles = [];

    /** @var array<string, array{file: \Illuminate\Http\UploadedFile, description: string}> */
    public array $uploadedFiles = [];

    /** @var array<int, array{file_id: string, path: string, original_name: string, description?: string, primary: bool, type: string, order: int, uploaded_at?: string, is_new?: bool}> */
    public array $existingFiles = [];

    public ?string $primaryFileId = null;

    protected PostsService $postsService;

    protected FileUploadService $fileUploadService;

    protected $listeners = [
        'postUpdated' => 'refreshFromPost',
        'updateFileOrder',
        'updateOrder',
        'collectData' => 'sendDataToParent',
        'updateGalleryContent' => 'handleUpdateGalleryContent',
    ];

    public function boot(PostsService $postsService, FileUploadService $fileUploadService)
    {
        $this->postsService = $postsService;
        $this->fileUploadService = $fileUploadService;
    }

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->loadFromPost();
    }

    public function loadFromPost()
    {
        $currentPostType = $this->post->post_type instanceof PostType ? $this->post->post_type->value : $this->post->post_type;

        if ($currentPostType === PostType::Gallery->value) {
            $galleryData = $this->post->gallery_data;

            if (is_array($galleryData) && ! empty($galleryData)) {
                $this->existingFiles = collect($galleryData)->map(function ($fileData, $index) {
                    $fileId = $fileData['file_id'] ?? null;
                    if (empty($fileId)) {
                        $filePath = $fileData['file_path'] ?? null;
                        $fileName = $fileData['filename'] ?? null;
                        if (! empty($filePath)) {
                            $fileId = 'existing_'.md5($filePath);
                        } elseif (! empty($fileName)) {
                            $fileId = 'existing_'.md5($fileName);
                        } else {
                            $fileId = 'existing_'.uniqid('', true);
                        }
                    }

                    return [
                        'file_id' => (string) $fileId,
                        'path' => $fileData['file_path'],
                        'original_name' => $fileData['filename'],
                        'description' => $fileData['description'] ?? '',
                        'primary' => (bool) $fileData['is_primary'],
                        'type' => $fileData['type'] ?? 'image/jpeg',
                        'order' => (int) $fileData['order'],
                        'uploaded_at' => $fileData['uploaded_at'] ?? now()->toISOString(),
                    ];
                })->toArray();

                // Ana dosyayı bul
                $this->primaryFileId = null;
                foreach ($this->existingFiles as $file) {
                    if ($file['primary'] === true) {
                        $this->primaryFileId = (string) $file['file_id'];
                        break;
                    }
                }
            }
        }

        $this->uploadedFiles = [];
    }

    public function refreshFromPost()
    {
        $this->post->refresh();
        $this->loadFromPost();
    }

    public function updatedNewFiles()
    {
        if (! empty($this->newFiles)) {
            $result = $this->fileUploadService->processSecureUploads($this->newFiles);

            if (! empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $this->addError('newFiles', $error);
                }

                return;
            }

            $currentPostType = $this->post->post_type instanceof PostType ? $this->post->post_type->value : $this->post->post_type;

            foreach ($result['files'] as $fileData) {
                if ($currentPostType === PostType::Gallery->value) {
                    $newIndex = count($this->existingFiles);
                    $this->existingFiles[] = [
                        'file_id' => 'new_'.time().'_'.$newIndex,
                        'path' => $fileData['path'],
                        'original_name' => $fileData['original_name'],
                        'description' => '',
                        'primary' => false,
                        'type' => $fileData['mime_type'],
                        'order' => $newIndex,
                        'uploaded_at' => now()->toISOString(),
                        'is_new' => true,
                    ];
                } else {
                    $fileId = 'file_'.time().'_'.rand(1000, 9999);
                    $this->uploadedFiles[$fileId] = [
                        'file' => $fileData['file'],
                        'description' => '',
                    ];
                }
            }

            if ($currentPostType === PostType::Gallery->value) {
                $this->newFiles = [];
            }

            $this->dispatch('filesUpdated');
        }
    }

    public function removeFile($fileId)
    {
        if (isset($this->uploadedFiles[$fileId])) {
            unset($this->uploadedFiles[$fileId]);
            if ((string) $this->primaryFileId === (string) $fileId) {
                $this->primaryFileId = null;
            }
            $this->dispatch('filesUpdated');
        }
    }

    public function removeExistingFile($index)
    {
        if (isset($this->existingFiles[$index])) {
            $file = $this->existingFiles[$index];

            if (isset($file['file_id']) && is_numeric($file['file_id'])) {
                File::where('file_id', $file['file_id'])->delete();
            }

            unset($this->existingFiles[$index]);
            $this->existingFiles = array_values($this->existingFiles);

            if (isset($file['file_id']) && $this->primaryFileId === (string) $file['file_id']) {
                $this->primaryFileId = null;
            }

            $currentPostType = $this->post->post_type instanceof PostType ? $this->post->post_type->value : $this->post->post_type;
            if ($currentPostType === PostType::Gallery->value) {
                $this->updateGalleryContent();
                $this->toastSuccess('Görsel kaldırıldı.');
            }

            $this->dispatch('filesUpdated');
        }
    }

    public function setPrimaryFile($fileId)
    {
        $this->primaryFileId = $fileId !== null ? (string) $fileId : null;

        foreach ($this->existingFiles as $index => $file) {
            $this->existingFiles[$index]['primary'] = false;
        }

        foreach ($this->existingFiles as $index => $file) {
            if (isset($file['file_id']) && (string) $file['file_id'] === (string) $fileId) {
                $this->existingFiles[$index]['primary'] = true;
                break;
            }
        }

        $this->dispatch('primaryFileChanged', $fileId);
    }

    public function updatedPrimaryFileId($value)
    {
        $this->setPrimaryFile($value);
    }

    public function updateExistingFileById($fileId, $field, $value)
    {
        if (empty($fileId) || empty($field)) {
            return;
        }

        foreach ($this->existingFiles as $index => $file) {
            if (isset($file['file_id']) && (string) $file['file_id'] === (string) $fileId) {
                $this->existingFiles[$index][$field] = $value;
                $this->dispatch('fileUpdated', ['fileId' => $fileId, 'field' => $field, 'value' => $value]);

                return;
            }
        }
    }

    public function reorderExistingFiles($newOrder)
    {
        if (empty($newOrder) || ! is_array($newOrder)) {
            return;
        }

        $reorderedFiles = [];
        foreach ($newOrder as $newIndex => $fileId) {
            foreach ($this->existingFiles as $file) {
                if ((string) $file['file_id'] === (string) $fileId) {
                    $file['order'] = $newIndex;
                    $reorderedFiles[] = $file;
                    break;
                }
            }
        }

        $this->existingFiles = $reorderedFiles;

        if (isset($this->existingFiles[0]['file_id'])) {
            $this->primaryFileId = (string) $this->existingFiles[0]['file_id'];
        }

        $this->updateGalleryContent();
        $this->toastSuccess('Sıralama güncellendi.');
    }

    public function updateFileOrder($fromIndex, $toIndex)
    {
        if ($fromIndex === $toIndex) {
            return;
        }

        $orderedIds = $this->getOrderedFileIds();
        $movedId = $orderedIds[$fromIndex];
        unset($orderedIds[$fromIndex]);
        $orderedIds = array_values($orderedIds);
        array_splice($orderedIds, $toIndex, 0, [$movedId]);

        $reorderedFiles = [];
        foreach ($orderedIds as $newIndex => $fileId) {
            foreach ($this->existingFiles as $file) {
                if ((string) $file['file_id'] === (string) $fileId) {
                    $file['order'] = $newIndex;
                    $reorderedFiles[] = $file;
                    break;
                }
            }
        }

        $this->existingFiles = $reorderedFiles;
        $this->saveFileOrderToDatabase();
    }

    public function updateOrder($order)
    {
        try {
            if (! $this->postsService->validateOrder($order, $this->existingFiles, true)) {
                $this->dispatch('order-update-failed', [
                    'message' => 'Geçersiz sıralama verisi. Lütfen sayfayı yenileyip tekrar deneyin.',
                ]);

                return;
            }

            $currentOrder = array_column($this->existingFiles, 'file_id');
            if ($currentOrder === $order) {
                return;
            }

            DB::transaction(function () use ($order) {
                $this->existingFiles = $this->postsService->reorderFiles(
                    $this->existingFiles,
                    $order,
                    true
                );

                foreach ($this->existingFiles as $index => &$file) {
                    $file['order'] = $index;
                }
                unset($file);

                $this->updateGalleryContent();
            });

            $this->dispatch('order-updated');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Sıralama güncellenirken bir hata oluştu. Lütfen tekrar deneyin.');
            $this->post->refresh();
            $this->loadFromPost();
        }
    }

    private function getOrderedFileIds()
    {
        $sortedFiles = $this->existingFiles;
        usort($sortedFiles, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        return array_column($sortedFiles, 'file_id');
    }

    private function saveFileOrderToDatabase()
    {
        $this->updateGalleryContent();
    }

    private function updateGalleryContent(): bool
    {
        try {
            $result = $this->postsService->saveGalleryContent(
                $this->post,
                $this->existingFiles,
                $this->primaryFileId
            );

            if ($result) {
                $this->post->refresh();
                $galleryData = $this->post->gallery_data;

                if (is_array($galleryData)) {
                    foreach ($this->existingFiles as $index => $file) {
                        $fileId = (string) $file['file_id'];
                        foreach ($galleryData as $galleryFile) {
                            if (isset($galleryFile['file_id']) && (string) $galleryFile['file_id'] === $fileId) {
                                $this->existingFiles[$index]['primary'] = (bool) ($galleryFile['is_primary'] ?? false);
                                break;
                            }
                        }
                    }

                    foreach ($this->existingFiles as $file) {
                        if ($file['primary'] === true) {
                            $this->primaryFileId = (string) $file['file_id'];
                            break;
                        }
                    }
                }

                return true;
            }

            return false;
        } catch (\Throwable $e) {
            $this->handleException($e, 'Galeri içeriği güncellenirken bir hata oluştu. Lütfen tekrar deneyin.');
            throw $e;
        }
    }

    public function removePrimaryFile()
    {
        if ($this->post->primaryFile) {
            $this->post->primaryFile->delete();
            $this->post->refresh();
            $this->dispatch('filesUpdated');
        }
    }

    public function getFilesData(): array
    {
        $newFiles = [];
        foreach ($this->uploadedFiles as $data) {
            $newFiles[] = $data['file'];
        }

        $fileDescriptions = [];
        foreach ($this->uploadedFiles as $fileId => $data) {
            $file = $data['file'] ?? null;
            if ($file) {
                $fileDescriptions[$file->getClientOriginalName()] = [
                    'description' => $data['description'] ?? '',
                    'alt_text' => $data['alt_text'] ?? '',
                ];
            }
        }

        // Gallery için existingFiles'dan da descriptions al
        $currentPostType = $this->post->post_type instanceof PostType ? $this->post->post_type->value : $this->post->post_type;
        if ($currentPostType === PostType::Gallery->value && ! empty($this->newFiles)) {
            foreach ($this->newFiles as $newFile) {
                $originalName = $newFile->getClientOriginalName();
                foreach ($this->existingFiles as $file) {
                    if (($file['is_new'] ?? false) || (isset($file['file_id']) && strpos($file['file_id'], 'new_') === 0)) {
                        if ($file['original_name'] === $originalName) {
                            if (! isset($fileDescriptions[$originalName])) {
                                $fileDescriptions[$originalName] = [];
                            }
                            $fileDescriptions[$originalName]['description'] = $file['description'] ?? '';
                            break;
                        }
                    }
                }
            }
        }

        return [
            'newFiles' => $newFiles,
            'fileDescriptions' => $fileDescriptions,
            'existingFiles' => $this->existingFiles,
            'primaryFileId' => $this->primaryFileId,
        ];
    }

    public function sendDataToParent()
    {
        // Media için validation yok, sadece data gönder
        $this->dispatch('mediaDataReady', $this->getFilesData());
    }

    public function handleUpdateGalleryContent($data)
    {
        if (isset($data['existingFiles'])) {
            $this->existingFiles = $data['existingFiles'];
        }
        if (isset($data['primaryFileId'])) {
            $this->primaryFileId = $data['primaryFileId'];
        }
        $this->updateGalleryContent();
    }

    public function render()
    {
        $currentPostType = $this->post->post_type instanceof PostType ? $this->post->post_type->value : $this->post->post_type;
        $isGallery = $currentPostType === PostType::Gallery->value;

        return view('posts::livewire.post-edit-media-manager', compact('isGallery'));
    }
}
