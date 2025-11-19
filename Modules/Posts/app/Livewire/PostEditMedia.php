<?php

namespace Modules\Posts\Livewire;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Posts\Domain\Repositories\PostFileRepositoryInterface;
use Modules\Posts\Models\File;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

/**
 * @property PostsService $postsService
 * @property PostFileRepositoryInterface $postFileRepository
 * @property Post $post
 */
class PostEditMedia extends Component
{
    use WithFileUploads;

    protected PostsService $postsService;

    protected PostFileRepositoryInterface $postFileRepository;

    public Post $post;

    /** @var array<int, \Illuminate\Http\UploadedFile> */
    public array $newFiles = []; // Livewire file upload

    /** @var array<int, array{file_id: string, path: string, original_name: string, description?: string, primary: bool, type: string, order: int, uploaded_at?: string, is_new?: bool}> */
    public array $existingFiles = []; // Mevcut dosyalar (edit için)

    public ?string $primaryFileId = null;

    public function boot()
    {
        $this->postsService = app(PostsService::class);
        $this->postFileRepository = app(PostFileRepositoryInterface::class);
    }

    public function mount($postId)
    {
        // Post model'ini eager loading ile yükle
        $this->post = Post::with(['files', 'primaryFile'])
            ->findOrFail($postId);

        // Mevcut dosyaları yükle
        $this->loadExistingFiles();
    }

    /**
     * Load existing files from database (for gallery posts)
     */
    protected function loadExistingFiles(): void
    {
        $this->existingFiles = [];

        if ($this->post->post_type === 'gallery') {
            // Content'i direkt database'den al (güncel veri için)
            $content = $this->post->content;
            $galleryData = json_decode($content, true) ?: [];

            // Eğer decode başarısız olursa veya content boşsa, post model'inden tekrar al
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($galleryData) || empty($content)) {
                $this->post->refresh();
                $content = $this->post->content;
                $galleryData = json_decode($content, true) ?: [];
            }

            if (is_array($galleryData) && ! empty($galleryData)) {
                // Order'a göre sırala
                $sortedGalleryData = collect($galleryData)->sortBy('order')->values()->toArray();

                $this->existingFiles = collect($sortedGalleryData)->map(function (array $fileData, int $index) {
                    // file_id'yi koru - eğer yoksa file_path'den hash oluştur (kalıcı olması için)
                    $fileId = $fileData['file_id'] ?? null;
                    $filePath = $fileData['file_path'] ?? '';

                    // Eğer file_id yoksa veya string ise, file_path ile files tablosundan gerçek numeric file_id'yi bul
                    $realNumericFileId = null;
                    if (! empty($filePath)) {
                        $fileModel = $this->postFileRepository->getQuery()
                            ->where('post_id', $this->post->post_id)
                            ->where('file_path', $filePath)
                            ->first();
                        if ($fileModel) {
                            /** @var \Modules\Posts\Models\File $fileModel */
                            $realNumericFileId = $fileModel->file_id;
                        }
                    }

                    if (empty($fileId)) {
                        // file_path'den hash oluştur - kalıcı ID
                        $fileName = $fileData['filename'] ?? '';
                        if (! empty($filePath)) {
                            $fileId = 'existing_'.md5($filePath);
                        } elseif (! empty($fileName)) {
                            $fileId = 'existing_'.md5($fileName);
                        } else {
                            // Son çare olarak unique ID
                            $fileId = 'existing_'.uniqid('', true);
                        }
                    }

                    $description = $fileData['description'] ?? '';

                    // HTML entity'leri decode et (çift encode edilmiş olabilir)
                    if (! empty($description)) {
                        // Önce html_entity_decode ile decode et
                        $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        // Eğer hala entity'ler varsa tekrar decode et
                        if (strpos($description, '&lt;') !== false || strpos($description, '&amp;') !== false) {
                            $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        }
                    }

                    return [
                        'file_id' => (string) $fileId,
                        'real_file_id' => $realNumericFileId, // Gerçek numeric file_id (files tablosu için)
                        'path' => $filePath,
                        'original_name' => $fileData['filename'] ?? '',
                        'description' => $description,
                        'primary' => (bool) ($fileData['is_primary'] ?? false),
                        'type' => $fileData['type'] ?? 'image/jpeg',
                        'order' => (int) ($fileData['order'] ?? $index),
                        'uploaded_at' => $fileData['uploaded_at'] ?? now()->toISOString(),
                    ];
                })->toArray();

                // Ana dosyayı bul
                $this->primaryFileId = null;
                foreach ($this->existingFiles as $index => $file) {
                    if ($file['primary'] === true) {
                        $this->primaryFileId = (string) $file['file_id'];
                        break;
                    }
                }
            }
        } elseif ($this->post->primaryFile) {
            // News/Video post'ları için primary file'ı existingFiles'a ekle
            $primaryFile = $this->post->primaryFile;
            $this->existingFiles[0] = [
                'file_id' => (string) $primaryFile->file_id,
                'path' => $primaryFile->file_path,
                'original_name' => $primaryFile->original_name ?? basename($primaryFile->file_path),
                'description' => '',
                'primary' => true,
                'type' => $primaryFile->mime_type ?? 'image/jpeg',
                'order' => 0,
                'uploaded_at' => $primaryFile->created_at ? (is_object($primaryFile->created_at) ? $primaryFile->created_at->toISOString() : (string) $primaryFile->created_at) : now()->toISOString(),
            ];
            $this->primaryFileId = (string) $primaryFile->file_id;
        }
    }

    /**
     * Update file by ID (for Trix editor descriptions)
     *
     * @param  int|string  $fileId
     * @param  string  $field  (description, title, alt)
     * @param  string  $value
     */
    public function updateFileById($fileId, $field, $value)
    {
        try {
            // Map field names to database columns (önce tanımla, sonra log'da kullan)
            $dbFieldMap = [
                'description' => 'caption',
                'title' => 'title',
                'alt' => 'alt_text',
            ];

            // Güvenlik: fileId ve field kontrolü
            if (empty($fileId) || empty($field)) {
                LogHelper::warning('PostEditMedia updateFileById: Empty fileId or field', [
                    'fileId' => $fileId,
                    'field' => $field,
                ]);

                return;
            }

            // Field validation
            if (! in_array($field, ['description', 'title', 'alt'])) {
                abort(403, 'Geçersiz alan');
            }

            // Value validation ve normalize: null/undefined ise boş string yap
            if ($value === null || $value === '') {
                $value = '';
            } elseif (! is_string($value)) {
                // String'e çevir (güvenlik için)
                $value = (string) $value;
            }

            // ÖNEMLİ: Trumbowyg'den gelen HTML zaten decode edilmiş olmalı
            // Ama eğer çift encode edilmişse, decode et
            if (! empty($value) && (strpos($value, '&lt;') !== false || strpos($value, '&amp;') !== false)) {
                // HTML entity'leri decode et (çift encode edilmiş olabilir)
                $decodedValue = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                // Eğer decode sonrası farklıysa, decode edilmiş versiyonu kullan
                if ($decodedValue !== $value) {
                    $value = $decodedValue;
                    // Eğer hala entity'ler varsa tekrar decode et
                    if (strpos($value, '&lt;') !== false || strpos($value, '&amp;') !== false) {
                        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                }
            }

            // Max length validation
            $maxLengths = [
                'description' => 10000,
                'title' => 255,
                'alt' => 255,
            ];

            $fieldNames = [
                'description' => 'Açıklama',
                'title' => 'Başlık',
                'alt' => 'Alt metin',
            ];

            // strlen kontrolü - $value artık her zaman string
            if (! array_key_exists($field, $maxLengths)) {
                throw new \InvalidArgumentException("Geçersiz alan: {$field}");
            }
            $maxLength = $maxLengths[$field];
            if (strlen($value) > $maxLength) {
                if (! array_key_exists($field, $fieldNames)) {
                    throw new \InvalidArgumentException("Geçersiz alan adı: {$field}");
                }
                $fieldName = $fieldNames[$field];
                throw new \InvalidArgumentException("{$fieldName} en fazla {$maxLength} karakter olabilir");
            }

            // dbFieldMap zaten yukarıda tanımlandı
            if (! array_key_exists($field, $dbFieldMap)) {
                throw new \InvalidArgumentException("Geçersiz alan mapping: {$field}");
            }
            $dbField = $dbFieldMap[$field];

            // Convert fileId to string for comparison
            $fileIdStr = (string) $fileId;

            $updated = false;

            // First, try to update in existingFiles array
            foreach ($this->existingFiles as $index => $file) {
                $currentFileId = isset($file['file_id']) ? (string) $file['file_id'] : null;

                if ($currentFileId === $fileIdStr) {
                    // existingFiles array'ini güncelle
                    $this->existingFiles[$index][$field] = $value;
                    $updated = true;

                    // Gerçek numeric file_id'yi bul (files tablosu için)
                    $realNumericFileId = $file['real_file_id'] ?? null;

                    // Önce file_path ile arama yap
                    if (empty($realNumericFileId) && ! empty($file['path'])) {
                        $fileModel = $this->postFileRepository->getQuery()
                            ->where('post_id', $this->post->post_id)
                            ->where('file_path', $file['path'])
                            ->first();
                        if ($fileModel) {
                            /** @var \Modules\Posts\Models\File $fileModel */
                            $realNumericFileId = $fileModel->file_id;
                            $this->existingFiles[$index]['real_file_id'] = $realNumericFileId;
                        }
                    }

                    // Ek güvenilirlik: file_path bulunamazsa, orijinal dosya adına göre arama yap
                    if (empty($realNumericFileId) && ! empty($file['original_name'])) {
                        $fileModelByName = $this->postFileRepository->getQuery()
                            ->where('post_id', $this->post->post_id)
                            ->where('title', $file['original_name'])
                            ->orderBy('created_at', 'desc')
                            ->first();
                        if ($fileModelByName) {
                            /** @var \Modules\Posts\Models\File $fileModelByName */
                            $realNumericFileId = $fileModelByName->file_id;
                            $this->existingFiles[$index]['real_file_id'] = $realNumericFileId;
                        }
                    }

                    // Son fallback: order alanı ile eşleştir (eski kayıtlar için)
                    if (empty($realNumericFileId) && isset($file['order'])) {
                        $fileModelByOrder = $this->postFileRepository->getQuery()
                            ->where('post_id', $this->post->post_id)
                            ->where('order', (int) $file['order'])
                            ->first();
                        if ($fileModelByOrder) {
                            /** @var \Modules\Posts\Models\File $fileModelByOrder */
                            $realNumericFileId = $fileModelByOrder->file_id;
                            $this->existingFiles[$index]['real_file_id'] = $realNumericFileId;
                        }
                    }

                    // If fileId is numeric OR real_file_id exists (existing file in database), update database
                    if (is_numeric($fileId) || ! empty($realNumericFileId)) {
                        $targetFileId = is_numeric($fileId) ? (int) $fileId : (int) $realNumericFileId;

                        /** @var \Modules\Posts\Models\File|null $fileModel */
                        $fileModel = $this->post->files->firstWhere('file_id', $targetFileId);
                        if ($fileModel) {
                            $this->postFileRepository->update($fileModel, [$dbField => $value]);
                        } else {
                            $fileModel = $this->postFileRepository->findById($targetFileId);
                            if ($fileModel) {
                                $this->postFileRepository->update($fileModel, [$dbField => $value]);
                            }
                        }
                    }

                    // Gallery için description güncellendiğinde veritabanına kaydet
                    // Bu, JSON formatında posts.content'e yazılacak
                    if ($this->post->post_type === 'gallery' && $field === 'description') {
                        try {
                            $this->updateGalleryContent();
                        } catch (\InvalidArgumentException $e) {
                            LogHelper::warning('updateGalleryContent validation failed in updateFileById', [
                                'fileId' => $fileId,
                                'field' => $field,
                                'post_id' => $this->post->post_id ?? null,
                                'error' => $e->getMessage(),
                            ]);
                            // Hata olsa bile existingFiles array'i güncellendi, bu yüzden devam et
                        } catch (\Exception $e) {
                            LogHelper::error('updateGalleryContent failed in updateFileById', [
                                'fileId' => $fileId,
                                'field' => $field,
                                'post_id' => $this->post->post_id ?? null,
                                'error' => $e->getMessage(),
                            ]);
                            // Hata olsa bile existingFiles array'i güncellendi, bu yüzden devam et
                        }
                    }

                    break;
                }
            }

            // Eğer dosya bulunamadıysa log'a yaz
            if (! $updated) {
                LogHelper::warning('updateFileById: File not found in existingFiles', [
                    'fileId' => $fileId,
                    'field' => $field,
                ]);
            }
        } catch (\InvalidArgumentException $e) {
            LogHelper::warning('PostEditMedia updateFileById validation failed', [
                'fileId' => $fileId,
                'field' => $field,
                'post_id' => $this->post->post_id ?? null,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            LogHelper::error('PostEditMedia updateFileById failed', [
                'fileId' => $fileId,
                'field' => $field,
                'post_id' => $this->post->post_id ?? null,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Dosya güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    /**
     * Update gallery content in database
     */
    private function updateGalleryContent(): bool
    {
        try {
            // Debug: existingFiles'dan açıklamaları kontrol et
            $descriptionsBefore = array_map(function (array $file) {
                return [
                    'file_id' => $file['file_id'],
                    'description' => $file['description'] ?? '',
                    'description_length' => strlen($file['description'] ?? ''),
                ];
            }, $this->existingFiles);

            // PostsService kullanarak veritabanına kaydet
            // saveGalleryContent zaten existingFiles array'indeki description'ları kullanıyor
            $result = $this->postsService->saveGalleryContent(
                $this->post,
                $this->existingFiles,
                $this->primaryFileId
            );

            if ($result) {
                // saveGalleryContent zaten post'u güncelliyor (content ve files.caption)
                // ÖNEMLİ: Refresh yapmadan önce mevcut existingFiles array'indeki description'ları koru
                // Çünkü refresh sonrası loadExistingFiles() DB'den eski description'ları yükleyebilir
                // (Transaction commit edilmemiş olabilir veya cache sorunu olabilir)

                // Mevcut description'ları koru (güncellenmiş olanlar)
                $preservedDescriptions = [];
                foreach ($this->existingFiles as $file) {
                    $fileId = (string) ($file['file_id'] ?? '');
                    $preservedDescriptions[$fileId] = $file['description'] ?? '';
                }

                // Post'u refresh et (güncel content için)
                $this->post->refresh();

                // existingFiles'ı yeniden yükle (refresh sonrası DB'den güncel veriler)
                // Ama description'ları koruyacağız
                $this->loadExistingFiles();

                // Description'ları geri yükle (güncellenmiş olanlar)
                // Çünkü loadExistingFiles() DB'den eski description'ları yükleyebilir
                foreach ($this->existingFiles as $index => $file) {
                    $fileId = (string) ($file['file_id'] ?? '');
                    if (isset($preservedDescriptions[$fileId]) && $preservedDescriptions[$fileId] !== '') {
                        // Preserved description varsa onu kullan (güncellenmiş olan)
                        $this->existingFiles[$index]['description'] = $preservedDescriptions[$fileId];
                    }
                }

                // Description'ları tekrar DB'ye kaydet
                // Çünkü loadExistingFiles() eski description'ları yükledi
                // Ama saveGalleryContent() zaten kaydetti, bu yüzden tekrar kaydetmemiz gerekiyor
                $result2 = $this->postsService->saveGalleryContent(
                    $this->post,
                    $this->existingFiles,
                    $this->primaryFileId
                );

                if ($result2) {
                    // Son bir refresh yap (güncel content için)
                    $this->post->refresh();
                    // existingFiles'ı tekrar yükle (güncel description'lar için)
                    $this->loadExistingFiles();
                }

                // existingFiles array'indeki primary flag'lerini güncelle
                $galleryData = $this->post->gallery_data;
                if (is_array($galleryData)) {
                    foreach ($this->existingFiles as $index => $file) {
                        $fileId = (string) $file['file_id'];
                        // gallery_data'dan bu file_id'ye sahip dosyayı bul
                        foreach ($galleryData as $galleryFile) {
                            if (isset($galleryFile['file_id']) && (string) $galleryFile['file_id'] === $fileId) {
                                $this->existingFiles[$index]['primary'] = (bool) ($galleryFile['is_primary'] ?? false);
                                break;
                            }
                        }
                    }

                    // primaryFileId'yi güncelle
                    foreach ($this->existingFiles as $file) {
                        if ($file['primary'] === true) {
                            $this->primaryFileId = (string) $file['file_id'];
                            break;
                        }
                    }
                }

                return $result2;
            }

            return false;
        } catch (\InvalidArgumentException $e) {
            LogHelper::warning('Galeri içeriği güncellenirken validation hatası', [
                'post_id' => $this->post->post_id,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            LogHelper::error('Galeri içeriği güncellenirken hata oluştu', [
                'post_id' => $this->post->post_id,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Galeri içeriği güncellenirken bir hata oluştu: '.$e->getMessage());

            throw $e;
        }
    }

    /**
     * Update gallery order (for drag and drop sorting)
     *
     * @param  array  $order  Array of file_id's in new order
     */
    public function updateOrder($order)
    {
        try {
            // Validation
            if (! $this->postsService->validateOrder($order, $this->existingFiles, true)) {
                LogHelper::warning('Geçersiz sıralama verisi alındı', [
                    'order' => $order,
                ]);

                $this->dispatch('order-update-failed', [
                    'message' => 'Geçersiz sıralama verisi. Lütfen sayfayı yenileyip tekrar deneyin.',
                ]);

                return;
            }

            // Mevcut sıralamayı al
            $currentOrder = array_column($this->existingFiles, 'file_id');

            // Değişiklik var mı kontrol et
            if ($currentOrder === $order) {
                return;
            }

            // Transaction içinde sıralama ve kaydetme
            DB::transaction(function () use ($order) {
                // PostsService kullanarak sıralama yap
                $this->existingFiles = $this->postsService->reorderFiles(
                    $this->existingFiles,
                    $order,
                    true // Index-based array
                );

                // Order değerlerini array index'ine göre güncelle
                foreach ($this->existingFiles as $index => &$file) {
                    $file['order'] = $index;
                }
                unset($file); // Reference'ı kaldır

                // Sıralamayı veritabanına kaydet
                $this->updateGalleryContent();
            });

            // Başarı mesajı - Sessizce çalış, alert gösterme
            $this->dispatch('order-updated');
        } catch (\InvalidArgumentException $e) {
            LogHelper::warning('Galeri sıralaması güncellenirken validation hatası', [
                'post_id' => $this->post->post_id,
                'order' => $order,
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('order-update-failed', [
                'message' => $e->getMessage(),
            ]);
            // Hata durumunda orijinal sıralamayı koru
            $this->loadExistingFiles();
        } catch (\Exception $e) {
            LogHelper::error('Galeri sıralaması güncellenirken hata oluştu', [
                'post_id' => $this->post->post_id,
                'order' => $order,
                'error' => $e->getMessage(),
            ]);

            // Kullanıcıya hata göster
            $this->dispatch('order-update-failed', [
                'message' => 'Sıralama güncellenirken bir hata oluştu: '.$e->getMessage(),
            ]);

            // Hata durumunda orijinal sıralamayı koru
            $this->loadExistingFiles();
        }
    }

    public function render()
    {
        /** @var view-string $view */
        $view = 'posts::livewire.post-edit-media';

        return view($view);
    }
}
