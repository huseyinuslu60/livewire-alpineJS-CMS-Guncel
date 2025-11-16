<?php

namespace Modules\Posts\Livewire;

use App\Traits\SecureFileUpload;
use App\Traits\ValidationMessages;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Categories\Models\Category;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

class PostCreateGallery extends Component
{
    use SecureFileUpload, ValidationMessages, WithFileUploads;

    protected PostsService $postsService;

    public string $title = '';

    public string $slug = '';

    public string $summary = '';

    public string $content = '';

    public string $post_position = 'normal';

    public string $status = 'published';

    public string $published_date = '';

    public bool $is_comment = true;

    public bool $is_mainpage = false;

    public string $redirect_url = '';

    public bool $is_photo = false;

    public string $agency_name = '';

    public ?int $agency_id = null;

    public bool $in_newsletter = false;

    public bool $no_ads = false;

    /** @var array<int, \Illuminate\Http\UploadedFile> */
    public array $files = [];

    /** @var array<int, \Illuminate\Http\UploadedFile> */
    public array $newFiles = [];

    /** @var array<string, array{file: \Illuminate\Http\UploadedFile, description: string, alt_text?: string}> */
    public array $uploadedFiles = []; // İşlenmiş dosyalar: [file, description] - string key kullanılıyor

    /** @var array<int> */
    public array $categoryIds = [];

    public string $tagsInput = '';

    public ?string $primaryFileId = null;

    public ?string $successMessage = null; // ID bazlı ana görsel seçimi

    public bool $isSaving = false;

    /** @var array<int, \Illuminate\Http\UploadedFile> */
    public array $dropzoneFiles = [];

    /** @var array<int, string> */
    public array $imageDescriptions = [];

    /** @var array<int, string> */
    public array $imageAltTexts = [];

    /** @var array<string, int> */
    public array $imageOrder = []; // ID => order mapping - string key kullanılıyor

    /** @var array<string, array{file: \Illuminate\Http\UploadedFile, description: string, alt_text: string}> */
    public array $imageData = []; // ID => [file, description, alt_text] mapping - string key kullanılıyor

    /** @var array<int, \Modules\Posts\Models\File> */
    public array $existingFiles = []; // Edit'teki gibi existingFiles kullan

    // Primary file index için
    public int $primaryFileIndex = 0;

    protected $listeners = ['contentUpdated'];

    public function boot()
    {
        $this->postsService = app(PostsService::class);
    }

    public function mount()
    {
        Gate::authorize('create posts');
        $this->published_date = Carbon::now()->format('Y-m-d H:i');
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'summary' => 'required|string',
            'content' => 'nullable|string',
            'post_position' => 'required|in:'.implode(',', Post::POSITIONS),
            'status' => 'required|in:'.implode(',', Post::STATUSES),
            'published_date' => 'nullable|date',
            'is_comment' => 'boolean',
            'is_mainpage' => 'boolean',
            'redirect_url' => 'nullable|url',
            'is_photo' => 'boolean',
            'agency_name' => 'nullable|string|max:255',
            'agency_id' => 'nullable|integer',
            'in_newsletter' => 'boolean',
            'no_ads' => 'boolean',
            'categoryIds' => 'required|array|min:1',
            'categoryIds.*' => 'exists:categories,category_id',
            'tagsInput' => 'nullable|string',
            'uploadedFiles' => 'required|array|min:1',
            'uploadedFiles.*.file' => 'nullable|image|max:4096',
            'uploadedFiles.*.description' => 'nullable|string|max:500',
        ];
    }

    protected function messages(): array
    {
        return $this->getValidationMessages();
    }

    protected function attributes(): array
    {
        return $this->getAttributeNames();
    }

    public function updatedTitle($value)
    {
        // Slug'ı her zaman güncelle, sadece boş değilse
        if (! empty(trim($value))) {
            // Türkçe karakterleri düzgün çevirmek için
            $turkishChars = [
                'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
                'Ç' => 'C', 'Ğ' => 'G', 'İ' => 'I', 'Ö' => 'O', 'Ş' => 'S', 'Ü' => 'U',
            ];

            // Türkçe karakterleri çevir ve fazla boşlukları temizle
            $convertedTitle = strtr($value, $turkishChars);
            $convertedTitle = preg_replace('/\s+/', ' ', trim($convertedTitle)); // Çoklu boşlukları tek boşluğa çevir
            $this->slug = Str::slug($convertedTitle);
        }
    }

    public function contentUpdated($content)
    {
        $this->content = $content;
    }

    public function setPrimaryFile($fileId)
    {
        $this->primaryFileId = $fileId;
    }

    public function updatedPrimaryFileId($value)
    {
        $this->primaryFileId = $value;

        // Debug için log ekle
        \Log::info('Primary file ID updated in create:', [
            'newFileId' => $value,
            'primaryFileId' => $this->primaryFileId,
            'totalFiles' => count($this->uploadedFiles),
        ]);
    }

    public function savePost()
    {
        // Duplicate submit'i engelle
        if ($this->isSaving) {
            return;
        }

        Gate::authorize('create posts');

        $this->isSaving = true;

        try {
            // Slug'ı mutlaka unique yap - validation'dan ÖNCE
            // Eğer slug boşsa veya unique değilse, yeni bir unique slug oluştur
            $postsService = new PostsService;
            if (empty($this->slug)) {
                $this->slug = $postsService->makeUniqueSlug($this->title);
            } else {
                // Slug varsa ama unique değilse, unique yap
                if (Post::where('slug', $this->slug)->exists()) {
                    $this->slug = $postsService->makeUniqueSlug($this->title);
                }
            }

            // Validation'ı unique slug ile yap
            try {
                $this->validate();
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Validation hatalarını kullanıcıya göster
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $this->addError($field, $message);
                    }
                }

                // Validation hatası varsa kullanıcıya genel mesaj göster
                if (! empty($e->errors())) {
                    $this->addError('general', 'Lütfen formu kontrol edin ve eksik alanları doldurun.');
                }

                $this->isSaving = false;

                return;
            }

            // Gallery için dosya kontrolü
            if (empty($this->uploadedFiles)) {
                $this->addError('uploadedFiles', 'Galeri yazıları için en az bir görsel yüklenmelidir.');
                $this->isSaving = false;

                return;
            }

            $tagIds = array_filter(array_map('trim', explode(',', $this->tagsInput)));

            // Galeri verilerini PostEdit'teki gibi hazırla
            // uploadedFiles array'i zaten updateOrder ile sıralanmış olmalı
            $galleryData = [];
            if (! empty($this->uploadedFiles)) {
                $fileKeys = array_keys($this->uploadedFiles);
                foreach ($this->uploadedFiles as $fileId => $fileData) {
                    $file = $fileData['file'];
                    // uploadedFiles array'indeki sıralama = order değeri
                    $index = array_search($fileId, $fileKeys);

                    $galleryData[] = [
                        'order' => $index, // Index direkt olarak order değeri
                        'filename' => $file->getClientOriginalName(),
                        'file_path' => '', // Bu PostsService'de doldurulacak
                        'type' => $file->getMimeType(),
                        'is_primary' => $this->primaryFileId === $fileId,
                        'uploaded_at' => now()->toISOString(),
                        'description' => $fileData['description'],
                        'alt_text' => $fileData['alt_text'] ?? '',
                    ];
                }
            }

            $formData = [
                'title' => $this->title,
                'slug' => $this->slug,
                'summary' => $this->summary,
                'post_type' => 'gallery',
                'post_position' => $this->post_position,
                'status' => $this->status,
                'published_date' => $this->published_date,
                'is_comment' => $this->is_comment,
                'is_mainpage' => $this->is_mainpage,
                'redirect_url' => $this->redirect_url,
                'is_photo' => $this->is_photo,
                'agency_name' => $this->agency_name,
                'agency_id' => $this->agency_id,
                'in_newsletter' => $this->in_newsletter,
                'no_ads' => $this->no_ads,
                'content' => ! empty($galleryData) ? json_encode($galleryData, JSON_UNESCAPED_UNICODE) : null, // Gallery için content'e JSON olarak kaydet
            ];

            // Yeni dosyalar için description ve alt_text bilgilerini hazırla (PostEdit'teki gibi)
            $fileDescriptions = [];
            if (! empty($this->uploadedFiles)) {
                foreach ($this->uploadedFiles as $fileId => $data) {
                    $file = $data['file'] ?? null;
                    if ($file) {
                        $fileDescriptions[$file->getClientOriginalName()] = [
                            'description' => $data['description'],
                            'alt_text' => $data['alt_text'] ?? '',
                        ];
                    }
                }
            }

            // Dosyaları sıralı şekilde al
            $orderedFiles = [];
            if (! empty($this->uploadedFiles)) {
                foreach ($this->uploadedFiles as $fileId => $fileData) {
                    $orderedFiles[] = $fileData['file'];
                }
            }

            $postsService = new PostsService;
            $post = $postsService->create(
                $formData,
                $orderedFiles,
                $this->categoryIds,
                $tagIds,
                $fileDescriptions
            );

            // content'i manuel olarak güncelle (galleryData ile)
            if (! empty($galleryData)) {
                // PostsService'den sonra file_path'leri güncelle
                $updatedGalleryData = [];
                /** @var \Illuminate\Database\Eloquent\Collection<int, \Modules\Posts\Models\File> $postFiles */
                $postFiles = $post->files()->orderBy('order')->get();

                foreach ($galleryData as $index => $data) {
                    if (isset($postFiles[$index])) {
                        /** @var \Modules\Posts\Models\File $file */
                        $file = $postFiles[$index];
                        $updatedGalleryData[] = [
                            'order' => $data['order'],
                            'filename' => $data['filename'],
                            'file_path' => $file->file_path,
                            'type' => $data['type'],
                            'is_primary' => $data['is_primary'],
                            'uploaded_at' => $data['uploaded_at'],
                            'description' => $data['description'],
                        ];
                    }
                }

                $post->update(['content' => json_encode($updatedGalleryData, JSON_UNESCAPED_UNICODE)]);
            }

            // Ana resim indeksini ayarla
            if (! empty($orderedFiles) && $this->primaryFileId) {
                // primaryFileId'ye göre index bul
                $primaryIndex = array_search($this->primaryFileId, array_keys($this->uploadedFiles));
                if ($primaryIndex !== false) {
                    $postsService->setPrimaryFile($post, $primaryIndex);
                }
            }

            $this->dispatch('post-created');

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('created', 'title', 'post'));

            return redirect()->route('posts.index');
        } catch (\Exception $e) {
            \Log::error('PostsService hatası: '.$e->getMessage());
            \Log::error('Stack trace: '.$e->getTraceAsString());
            $this->addError('general', 'Galeri oluşturulurken hata oluştu: '.$e->getMessage());
        } finally {
            $this->isSaving = false;
        }
    }

    public function addFile($file)
    {
        // Dropzone'dan gelen dosyayı işle
        $this->dropzoneFiles[] = $file;
    }

    public function handleFileUpload($event)
    {
        // JavaScript'ten dosyaları al
        $this->dispatch('processFiles');
    }

    public function processFiles($files)
    {
        // Dosyaları newFiles'a ekle
        // Livewire otomatik olarak updatedNewFiles() metodunu çağıracaktır
        $this->newFiles = $files;
    }

    public function updatedNewFiles()
    {
        // Yeni dosyalar yüklendiğinde uploadedFiles'a ekle
        if (! empty($this->newFiles)) {
            // Secure file processing
            $result = $this->processSecureUploads($this->newFiles);

            if (! empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $this->addError('newFiles', $error);
                }

                return;
            }

            foreach ($result['files'] as $fileData) {
                $file = $fileData['file'];
                $fileId = 'file_'.time().'_'.rand(1000, 9999);
                $this->uploadedFiles[$fileId] = [
                    'file' => $file,
                    'description' => '',
                ];
            }

            // newFiles'ı temizle
            $this->newFiles = [];

            // Eğer ana görsel seçili değilse ve dosya varsa, ilk dosyayı ana görsel yap
            if (empty($this->primaryFileId) && ! empty($this->uploadedFiles)) {
                $firstFileId = array_keys($this->uploadedFiles)[0];
                $this->primaryFileId = $firstFileId;
            }
        }
    }

    public function removeFile($fileId)
    {
        if (isset($this->uploadedFiles[$fileId])) {
            unset($this->uploadedFiles[$fileId]);

            // Eğer silinen dosya ana görsel ise, yeni ana görsel seç
            if ($this->primaryFileId === $fileId) {
                // Kalan dosyalar varsa ilkini ana görsel yap
                if (! empty($this->uploadedFiles)) {
                    $firstFileId = array_keys($this->uploadedFiles)[0];
                    $this->primaryFileId = $firstFileId;
                } else {
                    $this->primaryFileId = null;
                }
            }
        }
    }

    public function removeDropzoneFile($file)
    {
        // Dropzone'dan dosyayı kaldır
        $this->dropzoneFiles = array_filter($this->dropzoneFiles, function ($f) use ($file) {
            return $f !== $file;
        });
    }

    public function reorderImages($fromIndex, $toIndex)
    {
        if ($fromIndex === $toIndex) {
            return;
        }

        // Sıralı ID'leri al
        $orderedIds = $this->getOrderedImageIds();

        // Taşınacak ID'yi al
        $movedId = $orderedIds[$fromIndex];

        // ID'yi kaldır
        unset($orderedIds[$fromIndex]);
        $orderedIds = array_values($orderedIds);

        // Hedef pozisyona ekle
        array_splice($orderedIds, $toIndex, 0, [$movedId]);

        // Order'ları güncelle
        foreach ($orderedIds as $newIndex => $id) {
            $this->imageOrder[$id] = $newIndex;
        }

        // Ana görsel seçimini güncelle - primaryFileId korunuyor
        if ($this->primaryFileId !== null) {
            // primaryFileId zaten doğru ID'ye sahip, sadece index'i güncelle
            $newIndex = array_search($this->primaryFileId, $orderedIds);
            if ($newIndex !== false) {
                $this->primaryFileIndex = $newIndex;
            }
        }
    }

    private function getOrderedImageIds()
    {
        // Order'a göre sıralı ID'leri döndür
        asort($this->imageOrder);

        $orderedIds = array_keys($this->imageOrder);

        \Log::info('Create: getOrderedImageIds called:', [
            'imageOrder' => $this->imageOrder,
            'orderedIds' => $orderedIds,
            'count' => count($orderedIds),
        ]);

        return $orderedIds;
    }

    public function updateUploadedFileById($fileId, $field, $value)
    {
        // Güvenlik: fileId ve field kontrolü
        if (empty($fileId) || empty($field)) {
            return;
        }

        // Create sayfasında uploadedFiles ile arama yap
        if (isset($this->uploadedFiles[$fileId])) {
            $this->uploadedFiles[$fileId][$field] = $value;

            // Otomatik kaydetme yok - sadece memory'de güncelle
            // Kaydetme butonunda kaydedilecek (performans optimizasyonu)

            return; // Başarılı güncelleme
        }

        // Hiçbir şey bulunamadıysa sessizce devam et (debug için log)
        \Log::debug('File not found for update in create:', [
            'fileId' => $fileId,
            'field' => $field,
            'available_file_ids' => array_keys($this->uploadedFiles),
        ]);
    }

    /**
     * Unified method to update file by ID (for uploaded files in create page)
     *
     * @param  int|string  $fileId
     * @param  string  $field  (description, title, alt)
     * @param  string  $value
     */
    public function updateFileById($fileId, $field, $value)
    {
        // Güvenlik: fileId ve field kontrolü
        if (empty($fileId) || empty($field)) {
            \Log::warning('updateFileById called with empty fileId or field:', [
                'fileId' => $fileId,
                'field' => $field,
            ]);

            return;
        }

        // Field validation
        if (! in_array($field, ['description', 'title', 'alt'])) {
            \Log::error('updateFileById called with invalid field:', [
                'fileId' => $fileId,
                'field' => $field,
            ]);
            abort(403, 'Invalid field');
        }

        // Convert fileId to string for comparison
        $fileIdStr = (string) $fileId;

        \Log::info('updateFileById called:', [
            'fileId' => $fileId,
            'fileIdStr' => $fileIdStr,
            'field' => $field,
            'value_length' => strlen($value),
            'value_preview' => substr($value, 0, 50),
            'uploadedFiles_keys' => array_keys($this->uploadedFiles),
            'uploadedFiles_count' => count($this->uploadedFiles),
        ]);

        // Update in memory (uploadedFiles array)
        // Try exact match first
        if (isset($this->uploadedFiles[$fileIdStr])) {
            $oldValue = $this->uploadedFiles[$fileIdStr][$field] ?? '';
            $this->uploadedFiles[$fileIdStr][$field] = $value;

            \Log::info('updateFileById - File found and updated:', [
                'fileId' => $fileIdStr,
                'field' => $field,
                'old_value_length' => strlen($oldValue),
                'new_value_length' => strlen($value),
                'uploadedFiles_after' => $this->uploadedFiles[$fileIdStr],
            ]);

            return;
        }

        // Try to find by partial match (in case fileId format differs)
        foreach ($this->uploadedFiles as $key => $fileData) {
            if (strpos($key, $fileIdStr) !== false || strpos($fileIdStr, $key) !== false) {
                $oldValue = $fileData[$field] ?? '';
                $this->uploadedFiles[$key][$field] = $value;

                \Log::info('updateFileById - File found by partial match and updated:', [
                    'searched_fileId' => $fileIdStr,
                    'found_key' => $key,
                    'field' => $field,
                    'old_value_length' => strlen($oldValue),
                    'new_value_length' => strlen($value),
                    'uploadedFiles_after' => $this->uploadedFiles[$key],
                ]);

                return;
            }
        }

        // If not found, log debug info
        \Log::warning('File not found in updateFileById (create):', [
            'fileId' => $fileId,
            'fileIdStr' => $fileIdStr,
            'field' => $field,
            'value_length' => strlen($value),
            'available_file_ids' => array_keys($this->uploadedFiles),
            'uploadedFiles_structure' => array_map(function ($data) {
                return [
                    'has_file' => true, // Type'a göre her zaman var
                    'description' => $data['description'],
                    'description_length' => strlen($data['description']),
                ];
            }, $this->uploadedFiles),
        ]);
    }

    public function updateFileOrder($fromIndex, $toIndex)
    {
        if ($fromIndex === $toIndex) {
            return;
        }

        // uploadedFiles array'ini yeniden sırala
        $uploadedFilesArray = $this->uploadedFiles;
        $keys = array_keys($uploadedFilesArray);

        // Taşınacak key'i al
        $movedKey = $keys[$fromIndex];

        // Key'i kaldır
        unset($keys[$fromIndex]);
        $keys = array_values($keys);

        // Hedef pozisyona ekle
        array_splice($keys, $toIndex, 0, [$movedKey]);

        // Yeni sıralı array oluştur
        $reorderedFiles = [];
        foreach ($keys as $newIndex => $key) {
            $reorderedFiles[$key] = $uploadedFilesArray[$key];
        }

        $this->uploadedFiles = $reorderedFiles;

        \Log::info('Files reordered:', [
            'fromIndex' => $fromIndex,
            'toIndex' => $toIndex,
            'newOrder' => array_keys($this->uploadedFiles),
        ]);
    }

    public function saveSortOrder($sortOrder)
    {
        // JavaScript'ten gelen sıralama verilerini işle
        foreach ($sortOrder as $orderData) {
            $fileId = $orderData['id'];
            $newOrder = $orderData['order'];

            if (isset($this->imageOrder[$fileId])) {
                $this->imageOrder[$fileId] = $newOrder;
            }
        }

        // existingFiles'ı da yeniden sırala
        $this->reorderExistingFiles();

        \Log::info('Sort order saved:', [
            'sortOrder' => $sortOrder,
            'imageOrder' => $this->imageOrder,
        ]);
    }

    private function reorderExistingFiles()
    {
        // imageOrder'a göre existingFiles'ı yeniden sırala
        $orderedIds = $this->getOrderedImageIds();
        $reorderedFiles = [];

        foreach ($orderedIds as $index => $fileId) {
            // existingFiles'da bu fileId'yi bul
            foreach ($this->existingFiles as $fileData) {
                if (($fileData['file_id'] ?? '') == $fileId) {
                    $reorderedFiles[$index] = $fileData;
                    break;
                }
            }
        }

        $this->existingFiles = $reorderedFiles;
    }

    public function updateOrder($order)
    {
        try {
            Log::info('=== updateOrder METODU ÇAĞRILDI (PostCreateGallery) ===', [
                'order' => $order,
                'uploadedFiles_before' => array_keys($this->uploadedFiles),
                'timestamp' => now(),
            ]);

            // Validation
            if (! $this->postsService->validateOrder($order, $this->uploadedFiles, false)) {
                Log::warning('Invalid order data received', [
                    'order' => $order,
                    'uploadedFiles_count' => count($this->uploadedFiles),
                ]);

                $this->dispatch('order-update-failed', [
                    'message' => 'Geçersiz sıralama verisi. Lütfen sayfayı yenileyip tekrar deneyin.',
                ]);

                return;
            }

            // Mevcut sıralamayı al
            $currentOrder = array_keys($this->uploadedFiles);

            // Değişiklik var mı kontrol et
            if ($currentOrder === $order) {
                Log::info('Order unchanged, skipping update');

                return;
            }

            // PostsService kullanarak sıralama yap
            $this->uploadedFiles = $this->postsService->reorderFiles(
                $this->uploadedFiles,
                $order,
                false // Associative array
            );

            // Başarı mesajı - Sessizce çalış, alert gösterme
            $this->dispatch('order-updated');

            Log::info('Files reordered via updateOrder (PostCreateGallery):', [
                'newOrder' => array_keys($this->uploadedFiles),
                'orderCount' => count($order),
                'uploadedFiles_after' => array_map(function ($file) {
                    return [
                        'file' => 'file_exists', // Type'a göre her zaman var
                        'description' => $file['description'],
                    ];
                }, $this->uploadedFiles),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update gallery order in create', [
                'order' => $order,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Kullanıcıya hata göster
            $this->dispatch('order-update-failed', [
                'message' => 'Sıralama güncellenirken bir hata oluştu: '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        // Sadece gallery kategorilerini getir
        $categories = Category::where('status', 'active')
            ->where('type', 'gallery')
            ->orderBy('name')
            ->get();

        $postPositions = Post::POSITIONS;
        $postStatuses = Post::STATUSES;

        /** @var view-string $view */
        $view = 'posts::livewire.post-create-gallery';

        return view($view, compact('categories', 'postPositions', 'postStatuses'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
