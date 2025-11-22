<?php

namespace Modules\Posts\Livewire\Concerns;

use Modules\Files\Services\FileService;

/**
 * Trait for handling file selection from archive
 * Provides common functionality for selecting files from Files module and linking them to posts
 */
trait HandlesArchiveFileSelection
{
    /** @var array<int> Arşivden seçilen dosya ID'leri (post oluşturulduktan sonra bağlanacak) */
    public array $selectedArchiveFileIds = [];

    /** @var array<int, array{id: int, url: string, title: string}> Arşivden seçilen dosyaların ön izleme bilgileri */
    public array $selectedArchiveFilesPreview = [];

    /**
     * Handle files selected from archive
     * This method is called when files are selected from the Files module archive
     *
     * @param  array{files: array<int, array{id: int, title?: string, url?: string, type?: string}>}  $data
     */
    public function filesSelectedForPost($data)
    {
        // skipRender() kaldırıldı - ön izlemenin güncellenmesi için render gerekli

        if (! isset($data['files']) || ! is_array($data['files']) || empty($data['files'])) {
            return;
        }

        // Extract file IDs and preview info from selected files
        $fileIds = [];
        $previewData = [];
        foreach ($data['files'] as $selectedFile) {
            if (isset($selectedFile['id'])) {
                $fileIds[] = $selectedFile['id'];
                // Ön izleme için dosya bilgilerini sakla
                $previewData[] = [
                    'id' => $selectedFile['id'],
                    'url' => $selectedFile['url'] ?? '',
                    'title' => $selectedFile['title'] ?? '',
                    'type' => $selectedFile['type'] ?? 'image',
                ];
            }
        }

        if (empty($fileIds)) {
            return;
        }

        // Store file IDs (will be linked to post after creation)
        $this->selectedArchiveFileIds = array_merge($this->selectedArchiveFileIds, $fileIds);

        // Ön izleme için dosya bilgilerini sakla (son seçilen dosyalar)
        // Eğer multiple seçim yapılıyorsa (gallery), mevcut preview'lara ekle
        if (isset($data['multiple']) && $data['multiple'] === true) {
            $this->selectedArchiveFilesPreview = array_merge($this->selectedArchiveFilesPreview, $previewData);
        } else {
            // Tek seçim (news, video) - mevcut preview'ı değiştir
            $this->selectedArchiveFilesPreview = $previewData;
        }

        $fileCount = count($fileIds);
        session()->flash('success', $fileCount.' dosya arşivden seçildi! Post kaydedildiğinde bağlanacak.');

        // Livewire'ın render etmesi için skipRender() kullanma - render edilmesi gerekiyor
        // Property'ler zaten güncellendi, Livewire otomatik olarak render edecek
    }

    /**
     * Link selected archive files to post
     * This method should be called after post is created
     *
     * @param  \Modules\Posts\Models\Post  $post
     * @param  bool  $isGallery  Whether this is a gallery post (allows multiple files)
     */
    protected function linkArchiveFilesToPost($post, bool $isGallery = false): void
    {
        if (empty($this->selectedArchiveFileIds)) {
            return;
        }

        $fileService = app(FileService::class);

        foreach ($this->selectedArchiveFileIds as $fileId) {
            $file = $fileService->findById($fileId);
            if (! $file) {
                continue;
            }

            if ($isGallery) {
                // Gallery için multiple dosya eklenebilir
                // Aynı dosya zaten post'a bağlı mı kontrol et (file_path'e göre)
                $existingFile = $post->files()
                    ->where('file_path', $file->file_path)
                    ->first();

                if ($existingFile) {
                    // Dosya zaten bağlı, yeni kayıt oluşturma
                    continue;
                }

                // Yeni dosya ekle
                $post->files()->create([
                    'file_path' => $file->file_path,
                    'title' => $file->title,
                    'type' => $file->type ?? 'image',
                    'file_size' => $file->file_size,
                    'mime_type' => $file->mime_type,
                    'alt_text' => $file->alt_text,
                    'caption' => $file->caption,
                    'primary' => false, // Gallery için primary seçimi ayrı yapılır
                    'order' => $post->files()->count(), // Son sıraya ekle
                ]);
            } else {
                // News/Video için tek dosya - mevcut primary file varsa güncelle, yoksa yeni oluştur
                $existingPrimaryFile = $post->primaryFile;

                if ($existingPrimaryFile) {
                    // Aynı dosya mı kontrol et (file_path'e göre)
                    if ($existingPrimaryFile->file_path === $file->file_path) {
                        // Aynı dosya, güncelleme yapma
                        continue;
                    }

                    // Farklı dosya, mevcut primary file'ı güncelle
                    $existingPrimaryFile->update([
                        'file_path' => $file->file_path,
                        'title' => $file->title,
                        'file_size' => $file->file_size,
                        'mime_type' => $file->mime_type,
                        'alt_text' => $file->alt_text,
                    ]);
                } else {
                    // Primary file yok, yeni oluştur
                    $post->files()->create([
                        'file_path' => $file->file_path,
                        'title' => $file->title,
                        'type' => $file->type ?? 'image',
                        'file_size' => $file->file_size,
                        'mime_type' => $file->mime_type,
                        'alt_text' => $file->alt_text,
                        'caption' => $file->caption,
                        'primary' => true,
                        'order' => 0,
                    ]);
                }
            }
        }

        // Refresh post to get updated files
        $post->refresh();
    }

    /**
     * Remove a previously selected archive file from preview and pending link list
     */
    public function removeSelectedArchiveFile(?int $fileId = null, ?int $index = null): void
    {
        // Remove by fileId if provided
        if ($fileId !== null) {
            // Remove from IDs
            $this->selectedArchiveFileIds = array_values(array_filter(
                $this->selectedArchiveFileIds,
                fn ($id) => (int) $id !== (int) $fileId
            ));

            // Remove from preview
            $this->selectedArchiveFilesPreview = array_values(array_filter(
                $this->selectedArchiveFilesPreview,
                fn ($item) => (int) $item['id'] !== (int) $fileId
            ));

            return;
        }

        // Fallback: remove by index
        if ($index !== null) {
            if (isset($this->selectedArchiveFilesPreview[$index])) {
                $removed = $this->selectedArchiveFilesPreview[$index];
                unset($this->selectedArchiveFilesPreview[$index]);
                $this->selectedArchiveFilesPreview = array_values($this->selectedArchiveFilesPreview);
                if (isset($removed['id'])) {
                    $this->selectedArchiveFileIds = array_values(array_filter(
                        $this->selectedArchiveFileIds,
                        fn ($id) => (int) $id !== (int) $removed['id']
                    ));
                }
            }
        }
    }
}
