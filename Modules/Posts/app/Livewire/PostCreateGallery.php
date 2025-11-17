<?php

namespace Modules\Posts\Livewire;

use App\Helpers\LogHelper;
use App\Services\SlugGenerator;
use App\Services\ValueObjects\Slug;
use App\Traits\SecureFileUpload;
use App\Traits\ValidationMessages;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
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

    /**
     * Image editor data storage
     * Key: fileId (string), Value: array with crop, effects, meta data
     */
    protected array $imageEditorData = [];

    /**
     * Flag to track if image editor was used (to avoid saving empty spot_data)
     */
    protected bool $imageEditorUsed = false;

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
        $this->slugGenerator = app(SlugGenerator::class);
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

    /**
     * Ensure tagsInput is always a string when updated
     */
    public function updatedTagsInput($value)
    {
        // Ensure tagsInput is always a string to prevent Livewire serialization issues
        if (!is_string($value)) {
            $this->tagsInput = is_array($value) ? implode(', ', array_filter($value)) : (string) ($value ?? '');
        } else {
            // Clean up the string: remove extra spaces, ensure proper comma separation
            $this->tagsInput = trim($value);
        }
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
            $slug = $this->slugGenerator->generate($convertedTitle, Post::class, 'slug', 'post_id');
            $this->slug = $slug->toString();
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
            if (empty($this->slug)) {
                $slug = $this->slugGenerator->generate($this->title, Post::class, 'slug', 'post_id');
                $this->slug = $slug->toString();
            } else {
                // Slug varsa ama unique değilse, unique yap
                $slug = Slug::fromString($this->slug);
                if (!$this->slugGenerator->isUnique($slug, Post::class, 'slug', 'post_id')) {
                    $slug = $this->slugGenerator->generate($this->title, Post::class, 'slug', 'post_id');
                    $this->slug = $slug->toString();
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

            // Additional value validation
            if (strlen($this->title) > 255) {
                $this->addError('title', 'Başlık en fazla 255 karakter olabilir.');
                $this->isSaving = false;
                return;
            }

            if (strlen($this->summary) > 5000) {
                $this->addError('summary', 'Özet en fazla 5000 karakter olabilir.');
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

            // Build and save spot_data with image data only
            // Only save spot_data if image editor was actually used
            $spotData = [];

            // Add image data if we have primary file AND image editor was used
            if ($this->imageEditorUsed && $post->primaryFile) {
                // Get image dimensions and hash
                $imagePath = public_path('storage/' . $post->primaryFile->file_path);
                $width = null;
                $height = null;
                $hash = null;

                if (file_exists($imagePath)) {
                    // Get image dimensions
                    $imageInfo = @getimagesize($imagePath);
                    if ($imageInfo !== false) {
                        $width = $imageInfo[0];
                        $height = $imageInfo[1];
                    }

                    // Calculate file hash
                    $hash = md5_file($imagePath);
                }

                // Get image editor data if available (from image editor modal)
                // Find primary file's fileId from uploadedFiles
                $primaryFileId = null;
                foreach ($this->uploadedFiles as $fileId => $fileData) {
                    if (isset($fileData['file']) && $fileData['file']->getClientOriginalName() === $post->primaryFile->original_name) {
                        $primaryFileId = $fileId;
                        break;
                    }
                }
                // If not found by name, try to find by primaryFileId property
                if ($primaryFileId === null && $this->primaryFileId) {
                    $primaryFileId = $this->primaryFileId;
                }
                // Fallback: use first fileId if available
                if ($primaryFileId === null && !empty($this->uploadedFiles)) {
                    $primaryFileId = array_key_first($this->uploadedFiles);
                }

                $editorData = $primaryFileId ? ($this->imageEditorData[$primaryFileId] ?? null) : null;

                // Extract crop, effects, and meta from editor data
                $desktopCrop = [];
                $mobileCrop = [];
                $desktopFocus = 'center';
                $mobileFocus = 'center';
                $imageEffects = [];
                $imageMeta = [
                    'alt' => $post->primaryFile->alt_text ?? null,
                    'credit' => null,
                    'source' => null,
                ];
                $textObjects = [];

                if ($editorData !== null && is_array($editorData)) {
                    // Extract crop data
                    if (isset($editorData['crop']) && is_array($editorData['crop'])) {
                        $desktopCrop = $editorData['crop']['desktop'] ?? [];
                        $mobileCrop = $editorData['crop']['mobile'] ?? [];
                    } elseif (isset($editorData['desktopCrop'])) {
                        $desktopCrop = $editorData['desktopCrop'];
                        $mobileCrop = $editorData['mobileCrop'] ?? [];
                    }

                    // Extract focus data
                    if (isset($editorData['focus']) && is_array($editorData['focus'])) {
                        $desktopFocus = $editorData['focus']['desktop'] ?? 'center';
                        $mobileFocus = $editorData['focus']['mobile'] ?? 'center';
                    } elseif (isset($editorData['desktopFocus'])) {
                        $desktopFocus = $editorData['desktopFocus'];
                        $mobileFocus = $editorData['mobileFocus'] ?? 'center';
                    }

                    // Extract effects
                    if (isset($editorData['effects']) && is_array($editorData['effects'])) {
                        $imageEffects = $editorData['effects'];
                    }

                    // Extract meta
                    if (isset($editorData['meta']) && is_array($editorData['meta'])) {
                        $imageMeta = array_merge($imageMeta, $editorData['meta']);
                    }

                    // Extract text objects
                    if (isset($editorData['textObjects']) && is_array($editorData['textObjects'])) {
                        $textObjects = $editorData['textObjects'];
                    }
                }

                // Ensure arrays are properly formatted
                $desktopCrop = is_array($desktopCrop) ? $desktopCrop : [];
                $mobileCrop = is_array($mobileCrop) ? $mobileCrop : [];
                $imageEffects = is_array($imageEffects) ? $imageEffects : [];
                $imageMeta = is_array($imageMeta) ? $imageMeta : [
                    'alt' => $post->primaryFile->alt_text ?? null,
                    'credit' => null,
                    'source' => null,
                ];
                $textObjects = is_array($textObjects) ? $textObjects : [];

                $spotData['image'] = [
                    'original' => [
                        'path' => $post->primaryFile->file_path,
                        'width' => $width,
                        'height' => $height,
                        'hash' => $hash,
                    ],
                    'variants' => [
                        'desktop' => [
                            'crop' => $desktopCrop,
                            'focus' => $desktopFocus,
                        ],
                        'mobile' => [
                            'crop' => $mobileCrop,
                            'focus' => $mobileFocus,
                        ],
                    ],
                    'effects' => $imageEffects,
                    'meta' => $imageMeta,
                    'textObjects' => $textObjects,
                ];
            }

            // Only save spot_data if image editor was used
            if ($this->imageEditorUsed) {
                $post->spot_data = $spotData;
                $post->save();
            }

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
            LogHelper::error('PostsService hatası', [
                'post_type' => 'gallery',
                'error' => $e->getMessage(),
            ]);
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

    public function updateFilePreview($identifier, $imageUrl, $tempPath = null, $editorData = null)
    {
        $this->skipRender();

        // Store image editor data if provided
        if ($editorData !== null) {
            // If editorData is a JSON string, decode it
            if (is_string($editorData)) {
                $decoded = json_decode($editorData, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $editorData = $decoded;
                } else {
                    LogHelper::warning('PostCreateGallery updateFilePreview - Failed to decode JSON editorData', [
                        'json_error' => json_last_error_msg(),
                    ]);
                    $editorData = null;
                }
            }

            if ($editorData !== null && is_array($editorData)) {
                // Store editorData by identifier (fileId)
                $this->imageEditorData[$identifier] = $editorData;
                $this->imageEditorUsed = true; // Mark that image editor was used
            }
        }

        // identifier fileId (string) olabilir
        if (is_string($identifier) && isset($this->uploadedFiles[$identifier])) {
            try {
                // Convert asset URL to file path if needed
                $filePath = $imageUrl;
                if (str_starts_with($imageUrl, asset(''))) {
                    // Remove asset base URL to get relative path
                    $relativePath = str_replace(asset(''), '', $imageUrl);
                    $filePath = public_path($relativePath);
                } elseif (str_starts_with($imageUrl, 'http')) {
                    // For full URLs, download the content
                    $imageContent = @file_get_contents($imageUrl);
                    if ($imageContent === false) {
                        throw new \Exception('Could not download image from URL');
                    }

                    // Create temporary file in Livewire's temp directory
                    $tempDir = sys_get_temp_dir();
                    $oldFile = $this->uploadedFiles[$identifier]['file'];
                    $tempFileName = 'livewire-' . uniqid() . '-' . $oldFile->getClientOriginalName();
                    $tempFilePath = $tempDir . '/' . $tempFileName;
                    file_put_contents($tempFilePath, $imageContent);

                    // Get original file info
                    $originalName = $oldFile->getClientOriginalName();
                    $mimeType = $oldFile->getMimeType() ?: 'image/jpeg';

                    // Create new UploadedFile
                    $newFile = new \Illuminate\Http\UploadedFile(
                        $tempFilePath,
                        $originalName,
                        $mimeType,
                        null,
                        true // test mode
                    );

                    // Update the file in the array
                    $this->uploadedFiles[$identifier]['file'] = $newFile;

                    $this->dispatch('image-updated', [
                        'file_id' => $identifier,
                        'image_url' => $imageUrl,
                    ]);

                    return;
                } else {
                    $filePath = $imageUrl;
                }

                // If we have a local file path
                if (file_exists($filePath)) {
                    $oldFile = $this->uploadedFiles[$identifier]['file'];
                    $originalName = $oldFile->getClientOriginalName();
                    $mimeType = $oldFile->getMimeType() ?: mime_content_type($filePath) ?: 'image/jpeg';

                    $newFile = new \Illuminate\Http\UploadedFile(
                        $filePath,
                        $originalName,
                        $mimeType,
                        null,
                        true
                    );

                    // Mevcut dosyayı güncelle
                    $this->uploadedFiles[$identifier]['file'] = $newFile;

                    $this->dispatch('image-updated', [
                        'file_id' => $identifier,
                        'image_url' => $imageUrl,
                    ]);
                }
            } catch (\Exception $e) {
                LogHelper::error('Dosya önizlemesi güncellenirken hata oluştu', [
                    'error' => $e->getMessage(),
                    'image_url' => $imageUrl,
                    'file_id' => $identifier,
                ]);
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
            LogHelper::warning('updateFileById called with empty fileId or field', [
                'fileId' => $fileId,
                'field' => $field,
            ]);

            return;
        }

        // Field validation
        if (! in_array($field, ['description', 'title', 'alt'])) {
            LogHelper::error('updateFileById geçersiz alan ile çağrıldı', [
                'fileId' => $fileId,
                'field' => $field,
            ]);
            abort(403, 'Geçersiz alan');
        }

        // Value validation ve normalize: null/undefined ise boş string yap
        if ($value === null || $value === '') {
            $value = '';
        } elseif (!is_string($value)) {
            // String'e çevir (güvenlik için)
            $value = (string) $value;
        }

        // Convert fileId to string for comparison
        $fileIdStr = (string) $fileId;


        // Update in memory (uploadedFiles array)
        // Try exact match first
        if (isset($this->uploadedFiles[$fileIdStr])) {
            $oldValue = $this->uploadedFiles[$fileIdStr][$field] ?? '';
            $this->uploadedFiles[$fileIdStr][$field] = $value;


            return;
        }

        // Try to find by partial match (in case fileId format differs)
        foreach ($this->uploadedFiles as $key => $fileData) {
            if (strpos($key, $fileIdStr) !== false || strpos($fileIdStr, $key) !== false) {
                $oldValue = $fileData[$field] ?? '';
                $this->uploadedFiles[$key][$field] = $value;


                return;
            }
        }

        // If not found, log debug info
        LogHelper::warning('File not found in updateFileById (create)', [
            'fileId' => $fileId,
            'fileIdStr' => $fileIdStr,
            'field' => $field,
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
            // Validation
            if (! $this->postsService->validateOrder($order, $this->uploadedFiles, false)) {
                LogHelper::warning('Geçersiz sıralama verisi alındı', [
                    'order' => $order,
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
        } catch (\Exception $e) {
            LogHelper::error('Galeri sıralaması güncellenirken hata oluştu (create)', [
                'order' => $order,
                'error' => $e->getMessage(),
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
