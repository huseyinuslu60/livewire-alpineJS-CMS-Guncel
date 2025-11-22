<?php

namespace Modules\Posts\Livewire;

use App\Helpers\LogHelper;
use App\Services\SlugGenerator;
use App\Traits\SecureFileUpload;
use App\Traits\ValidationMessages;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Categories\Services\CategoryService;
use Modules\Headline\Services\FeaturedService;
use Modules\Posts\Domain\Repositories\PostFileRepositoryInterface;
use Modules\Posts\Domain\ValueObjects\PostPosition;
use Modules\Posts\Domain\ValueObjects\PostStatus;
use Modules\Posts\Domain\ValueObjects\PostType;
use Modules\Posts\Livewire\Concerns\HandlesArchiveFileSelection;
use Modules\Posts\Models\File;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

class PostEditNews extends Component
{
    use HandlesArchiveFileSelection, SecureFileUpload, ValidationMessages, WithFileUploads;

    protected PostsService $postsService;

    protected CategoryService $categoryService;

    protected PostFileRepositoryInterface $postFileRepository;

    protected SlugGenerator $slugGenerator;

    public ?Post $post = null;

    public string $title = '';

    public string $slug = '';

    public string $summary = '';

    public string $content = '';

    public string $post_type = 'news';

    public string $post_position = 'normal';

    public string $status = 'published';

    public string $published_date = '';

    public bool $is_comment = true;

    public bool $is_mainpage = false;

    public ?string $redirect_url = null;

    public bool $is_photo = false;

    public ?string $agency_name = null;

    public ?int $agency_id = null;

    public ?string $embed_code = null;

    public bool $in_newsletter = false;

    public bool $no_ads = false;

    /** @var array<int, \Illuminate\Http\UploadedFile> */
    public array $newFiles = []; // Livewire file upload

    /** @var array<string, array{file: \Illuminate\Http\UploadedFile, description: string}> */
    public array $uploadedFiles = []; // İşlenmiş dosyalar: [file, description] - string key kullanılıyor

    /** @var array<int, array{file_id: string, path: string, original_name: string, description?: string, primary: bool, type: string, order: int, uploaded_at?: string, is_new?: bool}> */
    public array $existingFiles = []; // Mevcut dosyalar (edit için)

    public ?string $primaryFileId = null;

    public ?string $successMessage = null; // Ana görsel ID'si

    // Spot data properties for image editing
    public ?string $originalImagePath = null;

    public ?int $originalImageWidth = null;

    public ?int $originalImageHeight = null;

    public ?string $originalImageHash = null;

    /** @var array<string, mixed> */
    public array $desktopCrop = [];

    /** @var array<string, mixed> */
    public array $mobileCrop = [];

    public string $desktopFocus = 'center';

    public string $mobileFocus = 'center';

    /** @var array<string, mixed> */
    public array $imageEffects = [];

    /** @var array<string, mixed> */
    public array $imageMeta = [];

    /** @var array<int, array<string, mixed>> */
    public array $imageTextObjects = [];

    /** @var array<string, int> */
    public array $canvasDimensions = ['width' => 0, 'height' => 0];

    /**
     * Flag to track if image editor was used (to avoid saving empty spot_data)
     * Must be public for Livewire serialization
     */
    public bool $imageEditorUsed = false;

    /**
     * Flag to track if files were selected from archive (to prevent duplicate file uploads)
     * Must be public for Livewire serialization
     */
    public bool $archiveFilesLinked = false;

    /**
     * Primary image spot_data JSON string
     * Used to sync image editor data with Livewire and save to post_data['image']
     */
    public ?string $primary_image_spot_data = null;

    // Diğer
    /** @var array<int> */
    public array $categoryIds = [];

    public string $tagsInput = '';

    public string $postType = 'news';

    // Otomatik vitrin ekleme için zamanlama
    public string $featuredStartsAt = '';

    public string $featuredEndsAt = '';

    protected $listeners = ['contentUpdated', 'updateFileOrder', 'filesSelectedForPost'];

    public function boot()
    {
        $this->postsService = app(PostsService::class);
        $this->categoryService = app(CategoryService::class);
        $this->postFileRepository = app(PostFileRepositoryInterface::class);
        $this->slugGenerator = app(SlugGenerator::class);
    }

    public function mount(?Post $post = null)
    {
        Gate::authorize('edit posts');

        // Eğer post parametre olarak geçilmişse (doğrudan route), onu kullan
        // Eğer post property olarak ayarlanmışsa (iç içe component), zaten ayarlanmış
        if ($post !== null) {
            $this->post = $post->load(['files', 'categories', 'tags', 'primaryFile', 'author', 'creator', 'updater']);
        }

        // Post mevcutsa component verilerini başlat
        if ($this->post !== null) {
            $this->initializeFromPost();
        }
    }

    /**
     * Post modelinden component verilerini başlat
     */
    protected function initializeFromPost(): void
    {
        if ($this->post === null) {
            return;
        }

        $this->postType = $this->post->post_type;

        $this->title = $this->post->title;
        $this->slug = $this->post->slug;
        $this->summary = $this->post->summary;
        // Content'i post tipine göre ayarla
        if ($this->post->post_type === 'gallery') {
            // Galeri için JSON formatında content
            $this->content = $this->post->content;
        } else {
            // Normal haber için HTML formatında content - JSON ise decode et
            $content = $this->post->content;
            if (! empty($content)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // JSON formatında ise, HTML formatına çevir veya boş bırak
                    $this->content = '';
                } else {
                    // Zaten HTML formatında
                    $this->content = $content;
                }
            } else {
                $this->content = '';
            }
        }
        $this->post_type = $this->post->post_type;
        $this->post_position = $this->post->post_position;
        $this->status = $this->post->status ?: 'draft';
        $this->published_date = $this->post->published_date ?
            (is_string($this->post->published_date) ?
                Carbon::parse($this->post->published_date)->format('Y-m-d H:i') :
                $this->post->published_date->format('Y-m-d H:i')) :
            Carbon::now()->format('Y-m-d H:i');
        $this->is_comment = $this->post->is_comment;
        $this->is_mainpage = $this->post->is_mainpage;
        $this->redirect_url = $this->post->redirect_url ?? null;
        $this->is_photo = $this->post->is_photo;
        $this->agency_name = $this->post->agency_name ?? null;
        $this->agency_id = $this->post->agency_id;
        $this->embed_code = $this->post->embed_code ?? null;
        $this->in_newsletter = $this->post->in_newsletter;
        $this->no_ads = $this->post->no_ads;

        $this->categoryIds = $this->post->categories ? $this->post->categories->pluck('category_id')->toArray() : [];
        // Ensure tagsInput is always a string
        if ($this->post->tags && is_object($this->post->tags) && method_exists($this->post->tags, 'pluck')) {
            $tags = $this->post->tags->pluck('name')->toArray();
            $this->tagsInput = is_array($tags) ? implode(', ', array_filter($tags)) : '';
        } else {
            $this->tagsInput = '';
        }

        // Mevcut dosyaları content'den yükle (gallery için)
        $this->loadExistingFiles();

        // Temiz dosya sistemi başlat
        $this->uploadedFiles = [];

        // Initialize spot_data properties to avoid Livewire serialization issues
        $this->resetImageEditorProperties();

        // Load spot_data if exists, otherwise migrate from legacy data
        $this->loadSpotData();

        // Load primary image spot_data from spot_data['image'] if exists
        $spotData = $this->post->spot_data ?? [];
        if (isset($spotData['image']) && is_array($spotData['image'])) {
            // Convert image data to JSON string for Livewire property
            $this->primary_image_spot_data = json_encode($spotData['image']);
        } else {
            $this->primary_image_spot_data = null;
        }
    }

    /**
     * Reset all image editor properties to default values
     * Used when new image is uploaded or primary file is removed
     */
    protected function resetImageEditorProperties(): void
    {
        $this->desktopCrop = [];
        $this->mobileCrop = [];
        $this->desktopFocus = 'center';
        $this->mobileFocus = 'center';
        $this->imageEffects = [];
        $this->imageTextObjects = [];
        $this->imageMeta = [];
        $this->canvasDimensions = [];
        $this->imageEditorUsed = false;
        $this->originalImagePath = null;
        $this->originalImageWidth = null;
        $this->originalImageHeight = null;
        $this->originalImageHash = null;
    }

    /**
     * Load existing files from database (for gallery posts)
     */
    protected function loadExistingFiles(): void
    {
        $this->existingFiles = [];

        if ($this->post->post_type === 'gallery') {
            // Content'i direkt database'den al (güncel veri için)
            // hydrate() veya mount() zaten refresh() çağırıyor, burada sadece content'i al
            $content = $this->post->content;
            $galleryData = json_decode($content, true) ?: [];

            // Eğer decode başarısız olursa veya content boşsa, post model'inden tekrar al (DB query gereksiz - eager loaded)
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($galleryData) || empty($content)) {
                // Post model zaten eager loaded, tekrar query gereksiz
                $this->post->refresh(); // Sadece content attribute'unu güncellemek için
                $content = $this->post->content;
                $galleryData = json_decode($content, true) ?: [];
            }

            if (is_array($galleryData) && ! empty($galleryData)) {
                // Order'a göre sırala
                $sortedGalleryData = collect($galleryData)->sortBy('order')->values()->toArray();

                $this->existingFiles = collect($sortedGalleryData)->map(function (array $fileData, int $index) {
                    // file_id'yi koru - eğer yoksa file_path'den hash oluştur (kalıcı olması için)
                    $fileId = $fileData['file_id'] ?? null;

                    if (empty($fileId)) {
                        // file_path'den hash oluştur - kalıcı ID
                        $filePath = $fileData['file_path'] ?? '';
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

                    return [
                        'file_id' => (string) $fileId, // Kalıcı file_id kullan (string olarak tutuluyor)
                        'path' => $fileData['file_path'] ?? '',
                        'original_name' => $fileData['filename'] ?? '',
                        'description' => $description, // Boş string de olsa göster
                        'primary' => (bool) ($fileData['is_primary'] ?? false), // is_primary -> primary
                        'type' => $fileData['type'] ?? 'image/jpeg',
                        'order' => (int) ($fileData['order'] ?? $index), // Order'ı koru
                        'uploaded_at' => $fileData['uploaded_at'] ?? now()->toISOString(), // uploaded_at ekle
                    ];
                })->toArray();

                // Ana dosyayı bul - gallery_data'dan direkt index bul
                $this->primaryFileId = null; // Default
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
     * Load spot_data from post or migrate from legacy data
     * Note: Migration is done lazily on save, not during mount to avoid Livewire checksum issues
     */
    protected function loadSpotData(): void
    {
        // Get spot_data - ensure it's an array
        $spotData = $this->post->spot_data;
        if (! is_array($spotData)) {
            $spotData = [];
        }

        // Load spot_data to properties if exists
        if (! empty($spotData) && isset($spotData['image']) && is_array($spotData['image'])) {
            $image = $spotData['image'];

            // Original image data
            $this->originalImagePath = $image['original']['path'] ?? null;
            $this->originalImageWidth = isset($image['original']['width']) ? (int) $image['original']['width'] : null;
            $this->originalImageHeight = isset($image['original']['height']) ? (int) $image['original']['height'] : null;
            $this->originalImageHash = $image['original']['hash'] ?? null;

            // Variants (desktop, mobile) - ensure arrays are always arrays
            if (isset($image['variants']['desktop']['crop']) && is_array($image['variants']['desktop']['crop'])) {
                $this->desktopCrop = $image['variants']['desktop']['crop'];
            }
            $this->desktopFocus = $image['variants']['desktop']['focus'] ?? 'center';

            if (isset($image['variants']['mobile']['crop']) && is_array($image['variants']['mobile']['crop'])) {
                $this->mobileCrop = $image['variants']['mobile']['crop'];
            }
            $this->mobileFocus = $image['variants']['mobile']['focus'] ?? 'center';

            // Effects - ensure array
            if (isset($image['effects']) && is_array($image['effects'])) {
                $this->imageEffects = $image['effects'];
            }

            // Meta - ensure array
            if (isset($image['meta']) && is_array($image['meta'])) {
                $this->imageMeta = $image['meta'];
            }

            // Text objects - ensure array
            if (isset($image['textObjects']) && is_array($image['textObjects'])) {
                $this->imageTextObjects = $image['textObjects'];
            }

            // Canvas dimensions for scaling textObjects
            if (isset($image['canvas']) && is_array($image['canvas'])) {
                $this->canvasDimensions = [
                    'width' => isset($image['canvas']['width']) ? (int) $image['canvas']['width'] : 0,
                    'height' => isset($image['canvas']['height']) ? (int) $image['canvas']['height'] : 0,
                ];
            }

            // If spot_data exists, mark that image editor was used (to preserve existing data)
            $this->imageEditorUsed = true;
        } else {
            // Initialize with primaryFile if available (for display, migration happens on save)
            $primaryFile = $this->post->primaryFile;
            if ($primaryFile) {
                $this->originalImagePath = $primaryFile->file_path;
                $this->imageMeta = [
                    'alt' => $primaryFile->alt_text ?? null,
                    'credit' => null,
                    'source' => null,
                ];
            }
        }
    }

    /**
     * Build spot_data array from properties
     * Also migrates legacy data if needed
     */
    protected function buildSpotData(): void
    {
        // Migrate legacy data if spot_data is empty (lazy migration on save)
        $spotData = $this->post->spot_data;
        if (! is_array($spotData) || empty($spotData) || ! isset($spotData['image'])) {
            $this->post->migrateLegacyImageDataToSpotData();
            $this->post->refresh();
            // Reload spot_data after migration
            $spotData = $this->post->spot_data;
            if (is_array($spotData) && isset($spotData['image'])) {
                $image = $spotData['image'];
                if (empty($this->originalImagePath) && isset($image['original']['path'])) {
                    $this->originalImagePath = $image['original']['path'];
                }
            }
        }

        // If originalImagePath is not set but primaryFile exists, use it
        if (empty($this->originalImagePath)) {
            $primaryFile = $this->post->primaryFile;
            if ($primaryFile) {
                $this->originalImagePath = $primaryFile->file_path;
            }
        }
    }

    /**
     * Build spot_data array for saving
     * Only contains image data, not post information
     */
    protected function buildSpotDataArray(): array
    {
        // Start with empty array - only store image data, not post information
        $spotData = [];

        // Ensure originalImagePath is set - use primaryFile if not set
        if (empty($this->originalImagePath)) {
            $primaryFile = $this->post->primaryFile;
            if ($primaryFile) {
                $this->originalImagePath = $primaryFile->file_path;
            }
        }

        // Only update if we have image data
        if (! empty($this->originalImagePath)) {
            // Get image dimensions and hash if not already set
            if ($this->originalImageWidth === null || $this->originalImageHeight === null || $this->originalImageHash === null) {
                $imagePath = public_path('storage/'.$this->originalImagePath);
                if (file_exists($imagePath)) {
                    // Get image dimensions
                    $imageInfo = @getimagesize($imagePath);
                    if ($imageInfo !== false) {
                        if ($this->originalImageWidth === null) {
                            $this->originalImageWidth = $imageInfo[0];
                        }
                        if ($this->originalImageHeight === null) {
                            $this->originalImageHeight = $imageInfo[1];
                        }
                    }

                    // Calculate file hash if not set
                    if ($this->originalImageHash === null) {
                        $this->originalImageHash = md5_file($imagePath);
                    }
                }
            }

            // Ensure all arrays are properly formatted
            $desktopCrop = $this->desktopCrop ?? [];
            $mobileCrop = $this->mobileCrop ?? [];

            // Effects - use existing or defaults (never empty array)
            $imageEffects = (! empty($this->imageEffects))
                ? $this->imageEffects
                : [
                    'brightness' => 100,
                    'contrast' => 100,
                    'saturation' => 100,
                    'hue' => 0,
                    'exposure' => 0,
                    'blur' => 0,
                ];

            $imageMeta = $this->imageMeta ?? [];
            $textObjects = $this->imageTextObjects ?? [];
            $canvasDimensions = $this->canvasDimensions ?? ['width' => 0, 'height' => 0];

            $spotData['image'] = [
                'original' => [
                    'path' => $this->originalImagePath,
                    'width' => $this->originalImageWidth,
                    'height' => $this->originalImageHeight,
                    'hash' => $this->originalImageHash,
                ],
                'variants' => [
                    'desktop' => [
                        'crop' => $desktopCrop,
                        'focus' => $this->desktopFocus ?? 'center',
                    ],
                    'mobile' => [
                        'crop' => $mobileCrop,
                        'focus' => $this->mobileFocus ?? 'center',
                    ],
                ],
                'effects' => $imageEffects,
                'meta' => $imageMeta,
                'textObjects' => $textObjects, // Always include, even if empty
                'canvas' => [
                    'width' => $canvasDimensions['width'] ?? 0,
                    'height' => $canvasDimensions['height'] ?? 0,
                ],
            ];
        } else {
            LogHelper::warning('PostEdit buildSpotDataArray - originalImagePath is empty', [
                'has_primaryFile' => $this->post->primaryFile !== null,
            ]);
        }

        return $spotData;
    }

    protected function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,'.$this->post->post_id.',post_id',
            'summary' => 'required|string',
            'content' => 'nullable|string',
            'post_type' => 'required|in:'.implode(',', PostType::all()),
            'post_position' => 'required|in:'.implode(',', PostPosition::all()),
            'status' => 'nullable|in:'.implode(',', PostStatus::all()),
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
            // imageData validation kuralları kaldırıldı - dosya yükleme sırasında kontrol ediliyor
        ];

        // Video için embed_code zorunlu
        if ($this->post_type === 'video') {
            $rules['embed_code'] = 'required|string';
        }

        // Dosya kontrolü (tüm türler için)
        $rules['newFiles.*'] = 'nullable|image|max:4096';

        return $rules;
    }

    protected function messages()
    {
        return [
            'title.required' => 'Başlık zorunludur.',
            'title.max' => 'Başlık en fazla 255 karakter olabilir.',
            'summary.required' => 'Özet zorunludur.',
            'content.nullable' => 'İçerik alanı boş bırakılabilir.',
            'categoryIds.required' => 'En az bir kategori seçilmelidir.',
            'categoryIds.min' => 'En az bir kategori seçilmelidir.',
            'categoryIds.*.exists' => 'Seçilen kategori geçersiz.',
            'post_position.required' => 'Pozisyon seçilmelidir.',
            'post_position.in' => 'Geçersiz pozisyon seçildi.',
            'status.in' => 'Geçersiz durum seçildi.',
            'embed_code.required' => 'Video yazıları için embed kodu zorunludur.',
            'newFiles.*.image' => 'Yüklenen dosyalar resim olmalıdır.',
            'newFiles.*.max' => 'Dosya boyutu 4MB\'dan küçük olmalıdır.',
            // imageData validation mesajları kaldırıldı
            'slug.unique' => 'Bu slug zaten kullanılıyor.',
            'redirect_url.url' => 'Geçersiz URL formatı.',
            'published_date.date' => 'Geçersiz tarih formatı.',
        ];
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
            $slugGenerator = app(SlugGenerator::class);
            $slug = $slugGenerator->generate($convertedTitle, Post::class, 'slug', 'post_id', $this->post->post_id ?? null);
            $this->slug = $slug->toString();
        }
    }

    public function updatedPostType($value)
    {
        $this->postType = $value;
        $this->post_type = $value; // post_type'ı da güncelle
        // Video değilse embed_code'u temizle
        if ($value !== 'video') {
            $this->embed_code = null;
        }

        // Kategorileri temizle ve yenile
        $this->categoryIds = [];
        $this->dispatch('postTypeChanged');
    }

    public function updatedPrimaryFileIndex($value)
    {
        // Tüm dosyaları false yap
        foreach ($this->existingFiles as $index => $file) {
            $this->existingFiles[$index]['primary'] = false;
        }

        // Seçilen dosyayı true yap
        if (isset($this->existingFiles[$value])) {
            $this->existingFiles[$value]['primary'] = true;
        }
    }

    // Livewire defer kullanılıyor, ek metod gerekmiyor

    /**
     * Ensure tagsInput is always a string when updated
     * This method is called automatically by Livewire when tagsInput property changes
     */
    public function updatedTagsInput($value)
    {
        // Ensure tagsInput is always a string to prevent Livewire serialization issues
        if (! is_string($value)) {
            $this->tagsInput = is_array($value) ? implode(', ', array_filter($value)) : (string) ($value ?? '');
        } else {
            // Clean up the string: remove extra spaces, ensure proper comma separation
            $this->tagsInput = trim($value);
        }
    }

    public function updatedIsMainpage($value)
    {
        try {
            $this->post->update(['is_mainpage' => $value]);

            $visibility = $value ? 'gösterilecek' : 'gizlenecek';
            session()->flash('success', "Yazı ana sayfada {$visibility}.");
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Ana sayfa durumu güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function reorderExistingFiles($newOrder)
    {
        try {
            $reorderedFiles = [];
            $newPrimaryIndex = 0;

            foreach ($newOrder as $orderItem) {
                $oldIndex = $orderItem['dataIndex'];
                if (isset($this->existingFiles[$oldIndex])) {
                    // Tüm verileri koru (description dahil)
                    $file = $this->existingFiles[$oldIndex];
                    $file['order'] = $orderItem['order']; // Yeni sıralama numarası
                    $reorderedFiles[] = $file;

                    // Ana resmin yeni index'ini bul
                    if ($this->existingFiles[$oldIndex]['primary'] === true) {
                        $newPrimaryIndex = count($reorderedFiles) - 1;
                    }
                }
            }

            $this->existingFiles = $reorderedFiles;

            // Ana resim ID'sini güncelle - file_id'yi kullan (string olarak tutuluyor)
            if (isset($this->existingFiles[$newPrimaryIndex]['file_id'])) {
                $this->primaryFileId = (string) $this->existingFiles[$newPrimaryIndex]['file_id'];
            } else {
                $this->primaryFileId = null;
            }

            // Sıralama sonrası veritabanını güncelle
            $this->updateGalleryContent();

            // Kullanıcıya bilgi ver
            session()->flash('success', 'Sıralama güncellendi ve açıklamalar korundu.');
        } catch (\InvalidArgumentException $e) {
            LogHelper::warning('reorderExistingFiles validation failed', [
                'post_id' => $this->post->post_id ?? null,
                'newOrder' => $newOrder,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            LogHelper::error('reorderExistingFiles failed', [
                'post_id' => $this->post->post_id ?? null,
                'newOrder' => $newOrder,
                'error' => $e->getMessage(),
            ]);

            // Kullanıcıya hata göster
            session()->flash('error', 'Sıralama güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function refreshExistingFiles()
    {
        // Mevcut dosyaları content'den yeniden yükle (gallery için)
        if ($this->post->post_type === 'gallery') {
            $galleryData = $this->post->gallery_data;

            if (is_array($galleryData) && ! empty($galleryData)) {
                $this->existingFiles = collect($galleryData)->map(function (array $fileData, int $index) {
                    // Create sayfasındaki gibi unique ID oluştur
                    $fileId = uniqid('existing_', true);

                    return [
                        'file_id' => (string) $fileId, // Unique ID - sıralama sonrası değişmez (string olarak tutuluyor)
                        'path' => $fileData['file_path'],
                        'original_name' => $fileData['filename'],
                        'description' => $fileData['description'] ?? '',
                        'primary' => (bool) $fileData['is_primary'],
                        'type' => $fileData['type'] ?? 'image/jpeg',
                        'order' => (int) $fileData['order'],
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
        }
    }

    public function contentUpdated($content)
    {
        $this->content = $content;
    }

    public function filesSelectedForPost($data)
    {
        // PostEdit'te post zaten mevcut, direkt bağlayabiliriz
        // Trait'teki filesSelectedForPost metodunu override ediyoruz
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

        // Store file IDs temporarily
        $this->selectedArchiveFileIds = array_merge($this->selectedArchiveFileIds, $fileIds);

        // Ön izleme için dosya bilgilerini sakla
        if (isset($data['multiple']) && $data['multiple'] === true) {
            $this->selectedArchiveFilesPreview = array_merge($this->selectedArchiveFilesPreview, $previewData);
        } else {
            $this->selectedArchiveFilesPreview = $previewData;
        }

        // Directly link files to post (edit mode - post already exists)
        if (! empty($this->selectedArchiveFileIds)) {
            $isGallery = $this->post->post_type === 'gallery';
            $this->linkArchiveFilesToPost($this->post, $isGallery);

            // Set flag to prevent duplicate file uploads in updatePost
            $this->archiveFilesLinked = true;

            // Refresh post to get updated files
            $this->post->refresh();

            // Reload existing files for gallery posts
            if ($isGallery) {
                $this->loadExistingFiles();
            } else {
                // For news/video, update primaryFileId
                if ($this->post->primaryFile) {
                    $this->primaryFileId = (string) $this->post->primaryFile->file_id;
                }
            }

            // Clear selectedArchiveFileIds after linking (already linked)
            $this->selectedArchiveFileIds = [];
        }

        $fileCount = count($fileIds);
        session()->flash('success', $fileCount.' dosya arşivden seçildi ve post\'a bağlandı.');
    }

    public function updateFileOrder($fromIndex, $toIndex)
    {

        if ($fromIndex === $toIndex) {
            return;
        }

        // Create sayfasındaki gibi sıralı ID'leri al
        $orderedIds = $this->getOrderedFileIds();

        // Taşınacak ID'yi al
        $movedId = $orderedIds[$fromIndex];

        // ID'yi kaldır
        unset($orderedIds[$fromIndex]);
        $orderedIds = array_values($orderedIds);

        // Hedef pozisyona ekle
        array_splice($orderedIds, $toIndex, 0, [$movedId]);

        // existingFiles'ı yeni sıraya göre yeniden düzenle
        $reorderedFiles = [];
        foreach ($orderedIds as $newIndex => $fileId) {
            // existingFiles'da bu file_id'ye sahip dosyayı bul
            foreach ($this->existingFiles as $file) {
                if (isset($file['file_id']) && (string) $file['file_id'] === (string) $fileId) {
                    // Tüm verileri koru, sadece order'ı güncelle
                    $file['order'] = $newIndex;
                    $reorderedFiles[] = $file;
                    break;
                }
            }
        }

        $this->existingFiles = $reorderedFiles;

        // Ana görsel seçimini güncelle - primaryFileId korunuyor
        if ($this->primaryFileId !== null) {
            // Tüm primary flag'leri false yap
            foreach ($this->existingFiles as $index => $file) {
                $this->existingFiles[$index]['primary'] = false;
            }

            // Ana görsel dosyasını bul ve işaretle
            foreach ($this->existingFiles as $index => $file) {
                if (isset($file['file_id']) && (string) $file['file_id'] === (string) $this->primaryFileId) {
                    $this->existingFiles[$index]['primary'] = true;
                    break;
                }
            }
        }

        // Sıralama bilgisini veritabanına kaydet
        $this->saveFileOrderToDatabase();

        // Sıralama sonrası veritabanını güncelle
        $this->updateGalleryContent();

        // Kullanıcıya bilgi ver
        session()->flash('success', 'Sıralama güncellendi.');
    }

    // Sıralama bilgisini veritabanına kaydet
    private function saveFileOrderToDatabase()
    {

        if (empty($this->existingFiles)) {
            return;
        }

        // existingFiles'da sadece string ID'ler var (existing_ prefix'li)
        // Bu dosyalar files tablosunda değil, posts.content JSON'ında
        // Sıralama zaten updateGalleryContent() ile posts.content'e kaydediliyor

        // Gerçek files tablosundaki dosyalar varsa onları güncelle
        $realFiles = array_filter($this->existingFiles, function ($file) {
            return is_numeric($file['file_id']);
        });

        if (! empty($realFiles)) {
            foreach ($realFiles as $file) {
                \DB::table('files')
                    ->where('file_id', $file['file_id'])
                    ->update(['order' => $file['order']]);

            }
        }

    }

    private function getOrderedFileIds()
    {
        // existingFiles'ı order'a göre sırala ve file_id'leri döndür
        $sortedFiles = $this->existingFiles;
        usort($sortedFiles, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        return array_column($sortedFiles, 'file_id');
    }

    public function updatedPrimaryFileId($value)
    {
        // Ana görsel seçimi değiştiğinde tüm dosyaları güncelle
        $this->setPrimaryFile($value);
    }

    // TEMİZ DOSYA SİSTEMİ METODLARI - Livewire file upload için

    public function updatedNewFiles()
    {
        // Yeni dosyalar yüklendiğinde işle
        if (! empty($this->newFiles)) {

            // IMPORTANT: For news/video types, clear image editor properties when new image is uploaded
            // This ensures that old spot_data edits don't apply to the new image
            if (in_array($this->post->post_type, ['news', 'video'])) {
                $this->resetImageEditorProperties();
                LogHelper::info('PostEdit updatedNewFiles - Cleared image editor properties for new image', [
                    'post_type' => $this->post->post_type,
                ]);
            }

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

                // Galeri türü için existingFiles'a ekle
                if ($this->post->post_type === 'gallery') {
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
                    // Haber/Video türü için uploadedFiles'a ekle
                    $fileId = 'file_'.time().'_'.rand(1000, 9999);
                    $this->uploadedFiles[$fileId] = [
                        'file' => $file,
                        'description' => '',
                    ];
                }
            }

            // Galeri için newFiles'ı temizle (gallery için existingFiles kullanılıyor)
            // Haber/Video için newFiles'ı temizleme (ön izleme için gerekli)
            if ($this->post->post_type === 'gallery') {
                $this->newFiles = [];
            }
        }
    }

    public function removeFile($fileId)
    {
        try {
            if (isset($this->uploadedFiles[$fileId])) {
                unset($this->uploadedFiles[$fileId]);

                // Eğer silinen dosya ana görsel ise, ana görsel seçimini sıfırla ve spot_data'yı temizle
                if ((string) $this->primaryFileId === (string) $fileId) {
                    $this->primaryFileId = null;
                    // Primary file kaldırıldığında spot_data'yı sıfırla
                    $this->post->spot_data = null;
                    $this->post->save();
                    $this->post->refresh();
                }
            }
        } catch (\InvalidArgumentException $e) {
            LogHelper::warning('removeFile validation failed', [
                'fileId' => $fileId,
                'post_id' => $this->post->post_id ?? null,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            LogHelper::error('removeFile failed', [
                'fileId' => $fileId,
                'post_id' => $this->post->post_id ?? null,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Dosya kaldırılırken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function setPrimaryFile($fileId)
    {
        $this->primaryFileId = $fileId !== null ? (string) $fileId : null;

        // Tüm mevcut dosyaları primary olmaktan çıkar
        foreach ($this->existingFiles as $index => $file) {
            $this->existingFiles[$index]['primary'] = false;
        }

        // Create sayfasındaki gibi basit arama
        foreach ($this->existingFiles as $index => $file) {
            if (isset($file['file_id']) && (string) $file['file_id'] === (string) $fileId) {
                $this->existingFiles[$index]['primary'] = true;
                break;
            }
        }

        // Uploaded files için de aynı işlemi yap
        foreach ($this->uploadedFiles as $fileId => $data) {
            // Bu dosya ana görsel değilse primary flag'ini false yap
            if ($this->primaryFileId !== $fileId) {
                // Uploaded files'da primary flag yok, sadece primaryFileId ile kontrol ediliyor
            }
        }
    }

    public function updateFileDescription($fileId, $description)
    {
        try {
            // Value validation
            if (! is_string($description) && ! is_null($description)) {
                throw new \InvalidArgumentException('Açıklama string veya null olmalıdır');
            }

            // Max length validation
            if (strlen($description) > 10000) {
                throw new \InvalidArgumentException('Açıklama en fazla 10000 karakter olabilir');
            }

            if (isset($this->uploadedFiles[$fileId])) {
                $this->uploadedFiles[$fileId]['description'] = $description;
            } else {
                LogHelper::warning('File not found in uploadedFiles', ['fileId' => $fileId, 'availableFiles' => array_keys($this->uploadedFiles)]);
            }
        } catch (\InvalidArgumentException $e) {
            LogHelper::warning('updateFileDescription validation failed', [
                'fileId' => $fileId,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            LogHelper::error('updateFileDescription failed', [
                'fileId' => $fileId,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Açıklama güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    /**
     * Update file preview after image editing
     *
     * @param  string|int  $identifier  File index or file_id
     * @param  string  $imageUrl  Edited image URL
     * @param  string|null  $tempPath  Temporary file path
     * @param  array|string|null  $editorData  Image editor data (crop, effects, meta) - can be array or JSON string
     */
    public function updateFilePreview($identifier, $imageUrl, $tempPath = null, $editorData = null)
    {

        // IMPORTANT: We do NOT update the file_path anymore
        // The original image file is preserved, only spot_data is updated
        // Extract path from URL only for reference (to set originalImagePath if needed)
        $path = str_replace(asset('storage/'), '', $imageUrl);
        $path = str_replace(asset(''), '', $path);
        if (strpos($path, 'storage/') === 0) {
            $path = substr($path, 8); // Remove 'storage/' prefix
        }

        // Update image editor data if provided and this is primary file
        $isPrimaryFile = false;

        // identifier file_id (string) veya index (integer) olabilir
        if (is_string($identifier)) {
            // file_id ile güncelle
            foreach ($this->existingFiles as $index => $file) {
                if (isset($file['file_id']) && (string) $file['file_id'] === (string) $identifier) {
                    // IMPORTANT: Do NOT update path - original file is preserved
                    // Only mark as not new if it was new
                    if (isset($this->existingFiles[$index]['is_new']) && $this->existingFiles[$index]['is_new']) {
                        $this->existingFiles[$index]['is_new'] = false;
                    }

                    // Update spot_data if this is primary file
                    if ($this->primaryFileId === (string) $identifier) {
                        $this->originalImagePath = $path;
                        $isPrimaryFile = true;
                    }

                    // Livewire'a güncelleme bildir (refresh() gereksiz - sadece preview güncelleniyor)
                    $this->dispatch('image-updated', [
                        'file_id' => $identifier,
                        'image_url' => $imageUrl,
                    ]);

                    // Always process editorData if provided
                    if ($editorData !== null) {
                        // If editorData is a JSON string, decode it
                        if (is_string($editorData)) {
                            $decoded = json_decode($editorData, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $editorData = $decoded;
                            } else {
                                LogHelper::warning('PostEdit updateFilePreview - Failed to decode JSON editorData (file_id)', [
                                    'json_error' => json_last_error_msg(),
                                ]);
                                $editorData = null;
                            }
                        }

                        if ($editorData !== null && is_array($editorData)) {
                            // Ensure originalImagePath is set before updating editor data
                            if (empty($this->originalImagePath)) {
                                $this->originalImagePath = $path;
                            }
                            $this->updateImageEditorData($editorData);
                        }
                    }

                    return;
                }
            }

            // Eğer Posts modülündeki File model'inde varsa
            // IMPORTANT: Do NOT update file_path - original file is preserved
            // Only spot_data will be updated via updateImageEditorData
            $file = $this->post->files->firstWhere('file_id', $identifier);
            if ($file) {
                // Do NOT update file_path - original file is preserved
                // $file->update(['file_path' => $path]); // REMOVED

                // Update spot_data if this is primary file
                if ($this->post->primaryFile && $this->post->primaryFile->file_id == $identifier) {
                    $this->originalImagePath = $path;
                    $isPrimaryFile = true;
                }

                // Livewire'a güncelleme bildir (refresh() gereksiz - sadece preview güncelleniyor)
                $this->dispatch('image-updated', [
                    'file_id' => $identifier,
                    'image_url' => $imageUrl,
                ]);

                // Always process editorData if provided
                if ($editorData !== null) {
                    // If editorData is a JSON string, decode it
                    if (is_string($editorData)) {
                        $decoded = json_decode($editorData, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $editorData = $decoded;
                        } else {
                            LogHelper::warning('PostEdit updateFilePreview - Failed to decode JSON editorData (file model)', [
                                'json_error' => json_last_error_msg(),
                            ]);
                            $editorData = null;
                        }
                    }

                    if ($editorData !== null && is_array($editorData)) {
                        // Ensure originalImagePath is set before updating editor data
                        if (empty($this->originalImagePath)) {
                            $this->originalImagePath = $path;
                        }
                        $this->updateImageEditorData($editorData);
                    }
                }
            }
        } elseif (is_numeric($identifier)) {
            // index ile güncelle veya file_id ile eşleştir
            $index = (int) $identifier;

            // Önce index ile kontrol et
            $fileFound = false;
            if (isset($this->existingFiles[$index])) {
                $fileFound = true;
            } else {
                // Index ile bulunamadıysa, file_id ile eşleştirmeyi dene
                foreach ($this->existingFiles as $idx => $file) {
                    if (isset($file['file_id']) && (string) $file['file_id'] === (string) $identifier) {
                        $index = $idx;
                        $fileFound = true;
                        break;
                    }
                }
            }

            if ($fileFound && isset($this->existingFiles[$index])) {
                // IMPORTANT: Do NOT update path - original file is preserved
                // Only mark as not new if it was new
                if (isset($this->existingFiles[$index]['is_new']) && $this->existingFiles[$index]['is_new']) {
                    $this->existingFiles[$index]['is_new'] = false;
                }

                // Update spot_data if this is primary file
                if ($this->primaryFileId === (string) $this->existingFiles[$index]['file_id']) {
                    $this->originalImagePath = $path;
                    $isPrimaryFile = true;
                }

                // Livewire'a güncelleme bildir (refresh() gereksiz - sadece preview güncelleniyor)
                $this->dispatch('image-updated', [
                    'index' => $index,
                    'image_url' => $imageUrl,
                ]);

                // Always process editorData if provided (for all files, not just primary)
                if ($editorData !== null) {

                    // If editorData is a JSON string, decode it
                    if (is_string($editorData)) {
                        $decoded = json_decode($editorData, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $editorData = $decoded;
                        } else {
                            LogHelper::warning('PostEdit updateFilePreview - Failed to decode JSON editorData (index)', [
                                'json_error' => json_last_error_msg(),
                            ]);
                            $editorData = null;
                        }
                    }

                    if ($editorData !== null && is_array($editorData)) {
                        // Ensure originalImagePath is set before updating editor data
                        if (empty($this->originalImagePath)) {
                            $this->originalImagePath = $path;
                        }
                        // Always update image editor data (will be saved to spot_data if primary file)
                        $this->updateImageEditorData($editorData);
                    }
                } else {
                    LogHelper::warning('PostEdit updateFilePreview - editorData is null (index)', [
                        'index' => $index,
                    ]);
                }
            }
        }
    }

    /**
     * Update image editor data from editor response
     */
    protected function updateImageEditorData(array $editorData): void
    {
        // Extract crop data
        if (isset($editorData['crop']) && is_array($editorData['crop'])) {
            $this->desktopCrop = $editorData['crop']['desktop'] ?? [];
            $this->mobileCrop = $editorData['crop']['mobile'] ?? [];
        } elseif (isset($editorData['desktopCrop'])) {
            $this->desktopCrop = $editorData['desktopCrop'];
            $this->mobileCrop = $editorData['mobileCrop'] ?? [];
        }

        // Extract focus data
        if (isset($editorData['focus']) && is_array($editorData['focus'])) {
            $this->desktopFocus = $editorData['focus']['desktop'] ?? 'center';
            $this->mobileFocus = $editorData['focus']['mobile'] ?? 'center';
        } elseif (isset($editorData['desktopFocus'])) {
            $this->desktopFocus = $editorData['desktopFocus'];
            $this->mobileFocus = $editorData['mobileFocus'] ?? 'center';
        }

        // Extract effects - always extract, even if default values
        if (isset($editorData['effects']) && is_array($editorData['effects'])) {
            // Convert string values to integers/floats for effects
            $this->imageEffects = [
                'brightness' => isset($editorData['effects']['brightness']) ? (int) $editorData['effects']['brightness'] : 100,
                'contrast' => isset($editorData['effects']['contrast']) ? (int) $editorData['effects']['contrast'] : 100,
                'saturation' => isset($editorData['effects']['saturation']) ? (int) $editorData['effects']['saturation'] : 100,
                'hue' => isset($editorData['effects']['hue']) ? (int) $editorData['effects']['hue'] : 0,
                'exposure' => isset($editorData['effects']['exposure']) ? (int) $editorData['effects']['exposure'] : 0,
                'blur' => isset($editorData['effects']['blur']) ? (int) $editorData['effects']['blur'] : 0,
            ];
        } else {
            // If effects not provided, keep existing effects or use defaults
            if (empty($this->imageEffects)) {
                $this->imageEffects = [
                    'brightness' => 100,
                    'contrast' => 100,
                    'saturation' => 100,
                    'hue' => 0,
                    'exposure' => 0,
                    'blur' => 0,
                ];
            }
            LogHelper::warning('PostEdit updateImageEditorData - effects not found or not array', [
                'has_effects' => isset($editorData['effects']),
                'effects_type' => isset($editorData['effects']) ? gettype($editorData['effects']) : 'not set',
                'editorData_keys' => array_keys($editorData),
            ]);
        }

        // Extract meta
        if (isset($editorData['meta']) && is_array($editorData['meta'])) {
            $this->imageMeta = array_merge($this->imageMeta, $editorData['meta']);
        }

        // Extract text objects (store in a property for later use in buildSpotDataArray)
        if (isset($editorData['textObjects']) && is_array($editorData['textObjects'])) {
            $this->imageTextObjects = $editorData['textObjects'];
        } else {
            // If textObjects not provided, keep existing textObjects (don't reset to empty)
            // Only reset if explicitly provided as empty array
            if (isset($editorData['textObjects']) && is_array($editorData['textObjects']) && empty($editorData['textObjects'])) {
                $this->imageTextObjects = [];
            }
            // Otherwise, keep existing textObjects

            LogHelper::warning('PostEdit updateImageEditorData - textObjects not found or not array', [
                'has_textObjects' => isset($editorData['textObjects']),
                'textObjects_type' => isset($editorData['textObjects']) ? gettype($editorData['textObjects']) : 'not set',
                'editorData_keys' => array_keys($editorData),
                'current_textObjects_count' => count($this->imageTextObjects),
            ]);
        }

        // Extract canvas dimensions for scaling textObjects on reload
        if (isset($editorData['canvas']) && is_array($editorData['canvas'])) {
            $this->canvasDimensions = [
                'width' => isset($editorData['canvas']['width']) ? (int) $editorData['canvas']['width'] : 0,
                'height' => isset($editorData['canvas']['height']) ? (int) $editorData['canvas']['height'] : 0,
            ];
        } else {
            LogHelper::warning('PostEdit updateImageEditorData - canvas not found or not array', [
                'has_canvas' => isset($editorData['canvas']),
                'canvas_type' => isset($editorData['canvas']) ? gettype($editorData['canvas']) : 'not set',
            ]);
        }

        // Mark that image editor was used
        $this->imageEditorUsed = true;

    }

    public function updatePost()
    {
        try {
            if (! Auth::user()->can('edit posts')) {
                abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
            }

            $this->validate();

            // Additional value validation
            if (strlen($this->title) > 255) {
                $this->addError('title', 'Başlık en fazla 255 karakter olabilir.');

                return;
            }

            if (strlen($this->summary) > 5000) {
                $this->addError('summary', 'Özet en fazla 5000 karakter olabilir.');

                return;
            }

            // Content validation (gallery için JSON, diğerleri için HTML)
            if ($this->post_type === 'gallery') {
                // Galeri için content validation kaldırıldı
                // PostEditMedia bileşeni updateGalleryContent() ile zaten veritabanına kaydediyor
                // Bu yüzden burada sadece mevcut content'i koruyoruz
            } else {
                // HTML content için max length kontrolü
                if (strlen($this->content) > 100000) {
                    $this->addError('content', 'İçerik çok uzun (maksimum 100.000 karakter).');

                    return;
                }
            }

            // Gallery için dosya kontrolü
            if ($this->post_type === 'gallery' && empty($this->uploadedFiles) && empty($this->existingFiles)) {
                $this->addError('uploadedFiles', 'Galeri yazıları için en az bir görsel yüklenmelidir.');

                return;
            }

            $tagIds = array_filter(array_map('trim', explode(',', $this->tagsInput)));

            // Gallery için content'i güncelle (PostsService->update() sonrası updateGalleryContent() ile yapılacak)
            // Bu yüzden burada sadece mevcut content'i koruyoruz
            if ($this->post_type === 'gallery') {
                // Mevcut content'i koru (PostsService->update() sonrası updateGalleryContent() ile güncellenecek)
                // Bu şekilde yeni dosyalar için açıklamalar korunacak
                $this->content = $this->post->content ?? '';
            }

            $formData = [
                'title' => $this->title,
                'slug' => $this->slug,
                'summary' => $this->summary,
                'content' => $this->content, // Galeri için güncellenmiş JSON, haber için HTML
                'post_type' => $this->post_type,
                'post_position' => $this->post_position,
                'status' => $this->status ?: 'draft',
                'published_date' => $this->published_date,
                'is_comment' => $this->is_comment,
                'is_mainpage' => $this->is_mainpage,
                'redirect_url' => $this->redirect_url,
                'is_photo' => $this->is_photo,
                'agency_name' => $this->agency_name,
                'agency_id' => $this->agency_id,
                'embed_code' => $this->embed_code,
                'in_newsletter' => $this->in_newsletter,
                'no_ads' => $this->no_ads,
            ];

            // Yeni dosyalar için description ve alt_text bilgilerini hazırla
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

            // Galeri için yeni dosyalar için açıklamaları existingFiles'dan al
            if ($this->post_type === 'gallery' && ! empty($this->newFiles)) {
                foreach ($this->newFiles as $newFile) {
                    $originalName = $newFile->getClientOriginalName();

                    // existingFiles'da bu dosya için açıklama var mı kontrol et
                    foreach ($this->existingFiles as $file) {
                        if (($file['is_new'] ?? false) || (isset($file['file_id']) && strpos($file['file_id'], 'new_') === 0)) {
                            if ($file['original_name'] === $originalName) {
                                // Açıklamayı fileDescriptions'a ekle
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

            // Yeni dosyaları hazırla
            $newFiles = [];
            if (! empty($this->uploadedFiles)) {
                foreach ($this->uploadedFiles as $data) {
                    $newFiles[] = $data['file'];
                }
            }

            // News/Video için resim güncelleme
            // IMPORTANT: Arşivden seçilen dosyalar zaten linkArchiveFilesToPost ile bağlandı,
            // bu yüzden burada sadece dropzone'dan yüklenen yeni dosyaları işle
            // Arşivden seçilen dosyalar için uploadedFiles kullanılmamalı
            // Ayrıca, image editor'den sonra uploadedFiles boş olmalı (sadece spot_data güncelleniyor)
            // Eğer uploadedFiles dolu ise, bu dropzone'dan yeni dosya yüklendiği anlamına gelir
            // archiveFilesLinked flag'i arşivden dosya seçildiğini gösterir, bu durumda dosya kopyalama yapma
            // ÖNEMLİ: Görsel düzenleyici kullanıldıysa (imageEditorUsed), uploadedFiles'ı işleme (duplicate önleme)
            if (in_array($this->post_type, ['news', 'video']) && ! empty($this->uploadedFiles) && ! $this->archiveFilesLinked && ! $this->imageEditorUsed) {
                // Mevcut primary file'ı bul ve güncelle
                $existingPrimaryFile = $this->post->primaryFile;

                if ($existingPrimaryFile) {
                    // Mevcut resmi güncelle (sadece bir dosya kullan, çift yükleme önle)
                    $newFile = collect($this->uploadedFiles)->first()['file'];
                    $existingPrimaryFile->update([
                        'file_path' => $newFile->store('posts/'.date('Y/m'), 'public'),
                        'title' => $newFile->getClientOriginalName(),
                        'file_size' => $newFile->getSize(),
                        'mime_type' => $newFile->getMimeType(),
                    ]);

                    // uploadedFiles array'ini temizle (çift yükleme önle)
                    $this->uploadedFiles = [];
                } else {
                    // Yeni resim ekle (sadece bir dosya kullan)
                    $newFile = collect($this->uploadedFiles)->first()['file'];
                    $this->post->files()->create([
                        'file_path' => $newFile->store('posts/'.date('Y/m'), 'public'),
                        'title' => $newFile->getClientOriginalName(),
                        'file_size' => $newFile->getSize(),
                        'mime_type' => $newFile->getMimeType(),
                    ]);

                    // uploadedFiles array'ini temizle (çift yükleme önle)
                    $this->uploadedFiles = [];
                }
            }

            // Build spot_data from properties before saving
            $this->buildSpotData();

            // IMPORTANT: If primary file was removed, clear spot_data
            // Check if primary file exists (refresh to get latest state)
            $this->post->refresh();
            $hasPrimaryFile = $this->post->primaryFile !== null;

            // Build spot_data array BEFORE PostsService->update() to preserve it
            // Check if we have image editor data (even if imageEditorUsed flag is not set)
            // This handles cases where updateFilePreview was called but flag wasn't set
            $hasImageEditorData = $this->imageEditorUsed
                || ! empty($this->imageEffects)
                || ! empty($this->imageTextObjects)
                || ! empty($this->desktopCrop)
                || ! empty($this->mobileCrop)
                || (! empty($this->canvasDimensions) && (($this->canvasDimensions['width'] ?? 0) > 0 || ($this->canvasDimensions['height'] ?? 0) > 0));

            $spotDataArray = null;
            if ($hasImageEditorData && $hasPrimaryFile) {
                // If imageEditorUsed is false but we have data, set it to true
                if (! $this->imageEditorUsed) {
                    $this->imageEditorUsed = true;
                }

                $spotDataArray = $this->buildSpotDataArray();
            } elseif (! $hasPrimaryFile) {
                // Primary file was removed, clear spot_data
                LogHelper::info('PostEdit updatePost - Primary file removed, clearing spot_data', [
                    'has_primary_file' => $hasPrimaryFile,
                    'imageEditorUsed' => $this->imageEditorUsed,
                ]);
                $spotDataArray = null;
            } else {
                LogHelper::warning('PostEdit updatePost - No image editor data to save', [
                    'imageEditorUsed' => $this->imageEditorUsed,
                    'has_effects' => ! empty($this->imageEffects),
                    'has_textObjects' => ! empty($this->imageTextObjects),
                    'has_crop' => ! empty($this->desktopCrop) || ! empty($this->mobileCrop),
                    'has_canvas' => ! empty($this->canvasDimensions),
                ]);
            }

            $postsService = new PostsService;
            $updatedPost = $postsService->update(
                $this->post,
                $formData,
                $newFiles,
                $this->categoryIds,
                $tagIds,
                $fileDescriptions
            );

            // Refresh post model after service update (service returns fresh model)
            $this->post = $updatedPost;

            // Check again if primary file exists after update
            $this->post->refresh();
            $hasPrimaryFileAfterUpdate = $this->post->primaryFile !== null;

            // Save spot_data after post update only if:
            // 1. We have image editor data (imageEditorUsed flag OR actual data) AND
            // 2. We have actual spot_data array AND
            // 3. Primary file still exists
            // Check again if we have image editor data (in case it was set during update)
            $hasImageEditorDataAfterUpdate = $this->imageEditorUsed
                || ! empty($this->imageEffects)
                || ! empty($this->imageTextObjects)
                || ! empty($this->desktopCrop)
                || ! empty($this->mobileCrop)
                || (! empty($this->canvasDimensions) && (($this->canvasDimensions['width'] ?? 0) > 0 || ($this->canvasDimensions['height'] ?? 0) > 0));

            // NEW: Update spot_data['image'] from primary_image_spot_data if provided
            // This allows JS editor to directly update spot_data via Livewire property
            $currentSpotData = $this->post->spot_data ?? [];
            if ($this->primary_image_spot_data) {
                $decodedImageData = json_decode($this->primary_image_spot_data, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedImageData)) {
                    // Handle nested structure: if decodedImageData has 'image' key, unwrap it
                    // This prevents double nesting: spot_data['image']['image']
                    if (isset($decodedImageData['image']) && is_array($decodedImageData['image'])) {
                        // Unwrap: use decodedImageData['image'] instead
                        $decodedImageData = $decodedImageData['image'];
                        LogHelper::info('PostEdit updatePost - Unwrapped nested image structure');
                    }

                    // Update only the 'image' key in spot_data, preserve other keys
                    $currentSpotData['image'] = $decodedImageData;
                    LogHelper::info('PostEdit updatePost - Updated spot_data[image] from primary_image_spot_data', [
                        'has_image' => isset($currentSpotData['image']),
                        'image_keys' => array_keys($decodedImageData),
                    ]);
                }
            }

            if ($hasImageEditorDataAfterUpdate && isset($spotDataArray['image']) && $hasPrimaryFileAfterUpdate) {
                LogHelper::info('PostEdit updatePost - Saving spot_data after PostsService->update()', [
                    'has_spot_data' => isset($spotDataArray['image']),
                    'has_image' => true,
                    'has_textObjects' => ! empty(($spotDataArray['image']['textObjects'] ?? [])),
                    'textObjects_count' => count(($spotDataArray['image']['textObjects'] ?? [])),
                    'has_effects' => ! empty(($spotDataArray['image']['effects'] ?? [])),
                    'effects' => ($spotDataArray['image']['effects'] ?? []),
                    'has_canvas' => isset($spotDataArray['image']['canvas']),
                    'canvas' => $spotDataArray['image']['canvas'] ?? [],
                    'originalImagePath' => $this->originalImagePath,
                    'spot_data_json' => json_encode($spotDataArray),
                ]);

                // Merge primary_image_spot_data with spotDataArray if both exist
                // primary_image_spot_data takes priority (from JS editor)
                if ($this->primary_image_spot_data) {
                    $decodedImageData = json_decode($this->primary_image_spot_data, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedImageData)) {
                        // Handle nested structure: if decodedImageData has 'image' key, unwrap it
                        if (isset($decodedImageData['image']) && is_array($decodedImageData['image'])) {
                            $decodedImageData = $decodedImageData['image'];
                        }
                        $spotDataArray['image'] = $decodedImageData;
                        LogHelper::info('PostEdit updatePost - Merged primary_image_spot_data into spotDataArray');
                    }
                }

                // Update spot_data and save
                $this->post->spot_data = $spotDataArray;
                $this->post->save();
            } elseif ($this->primary_image_spot_data && $hasPrimaryFileAfterUpdate) {
                // If primary_image_spot_data exists but no spotDataArray, update spot_data['image'] only
                $decodedImageData = json_decode($this->primary_image_spot_data, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedImageData)) {
                    // Handle nested structure: if decodedImageData has 'image' key, unwrap it
                    if (isset($decodedImageData['image']) && is_array($decodedImageData['image'])) {
                        $decodedImageData = $decodedImageData['image'];
                    }

                    // Preserve existing spot_data structure, only update 'image' key
                    $this->post->spot_data = $currentSpotData;
                    $this->post->save();
                    LogHelper::info('PostEdit updatePost - Updated spot_data[image] from primary_image_spot_data only', [
                        'post_id' => $this->post->post_id,
                        'has_image' => isset($this->post->spot_data['image']),
                    ]);
                }

                LogHelper::info('PostEdit updatePost - spot_data saved successfully', [
                    'post_id' => $this->post->post_id,
                    'spot_data_saved' => ! empty($this->post->spot_data),
                ]);
            } elseif (! $hasPrimaryFileAfterUpdate) {
                // Primary file was removed, clear spot_data
                $this->post->spot_data = null;
                $this->post->save();
                LogHelper::info('PostEdit updatePost - Primary file removed, spot_data cleared', [
                    'post_id' => $this->post->post_id,
                    'has_primary_file' => $hasPrimaryFileAfterUpdate,
                ]);
            } elseif ($hasImageEditorDataAfterUpdate) {
                LogHelper::warning('PostEdit updatePost - imageEditorData exists but spot_data is empty', [
                    'has_spot_data' => ! empty($spotDataArray),
                    'has_image' => isset($spotDataArray['image']),
                    'originalImagePath' => $this->originalImagePath,
                    'has_textObjects' => ! empty($this->imageTextObjects),
                    'textObjects_count' => count($this->imageTextObjects),
                    'has_primary_file' => $hasPrimaryFileAfterUpdate,
                    'imageEditorUsed' => $this->imageEditorUsed,
                    'hasImageEditorDataAfterUpdate' => $hasImageEditorDataAfterUpdate,
                ]);
            } else {
                LogHelper::info('PostEdit updatePost - No image editor data, not saving spot_data', [
                    'imageEditorUsed' => $this->imageEditorUsed,
                    'has_effects' => ! empty($this->imageEffects),
                    'has_textObjects' => ! empty($this->imageTextObjects),
                    'has_crop' => ! empty($this->desktopCrop) || ! empty($this->mobileCrop),
                    'has_canvas' => ! empty($this->canvasDimensions),
                ]);
            }

            // Galeri için content'i güncelle (açıklamalar dahil)
            if ($this->post_type === 'gallery') {
                // Yeni dosyalar için file_path'leri eager loading ile al (refresh() gereksiz)
                $this->post->load('files');

                // ÖNEMLİ: existingFiles array'i updateFileById ile güncellenmiş olmalı
                // Veritabanından yükleme yapmıyoruz çünkü bu güncel description'ları ezer

                // Yeni dosyalar için file_path'leri güncelle ve açıklamaları koru
                if (! empty($newFiles)) {
                    // Eager loaded files collection'ından yeni dosyaları al (N+1 query önleme)
                    /** @var \Illuminate\Database\Eloquent\Collection<int, \Modules\Posts\Models\File> $postFiles */
                    $postFiles = $this->post->files
                        ->sortByDesc('created_at')
                        ->take(count($newFiles))
                        ->values();

                    foreach ($this->existingFiles as $index => &$file) {
                        // Yeni dosya mı kontrol et
                        if (($file['is_new'] ?? false) || (isset($file['file_id']) && strpos($file['file_id'], 'new_') === 0)) {
                            $originalName = $file['original_name'];
                            $description = $file['description'] ?? ''; // Açıklamayı sakla

                            // Veritabanından file_path'i bul
                            foreach ($postFiles as $postFile) {
                                /** @var \Modules\Posts\Models\File $postFile */
                                if ($postFile->title === $originalName) {
                                    // file_path'i güncelle ama açıklamayı koru
                                    $file['path'] = $postFile->file_path;
                                    $file['file_path'] = $postFile->file_path;
                                    $file['description'] = $description; // Açıklamayı koru
                                    unset($file['is_new']);
                                    break; // Bu break yanlış yerde, foreach'ten çıkıyor
                                }
                            }
                        }
                    }
                    unset($file);
                }

                // existingFiles'dan açıklamaları al ve updateGalleryContent() ile kaydet
                // ÖNEMLİ: existingFiles array'i güncel olmalı (updateFileById ile güncellenmiş olmalı)
                // updateFileById zaten existingFiles array'ini güncelliyor, bu yüzden veritabanından yüklemeye gerek yok

                $this->updateGalleryContent();
            }

            // Otomatik vitrin ekleme (sadece pozisyon bazlı)
            $shouldAddToFeatured = false;
            $zone = null;

            // Sadece pozisyon bazlı vitrin ekleme
            if (in_array($this->post_position, ['manşet', 'sürmanşet', 'öne çıkanlar'])) {
                $shouldAddToFeatured = true;
                $zoneMapping = [
                    'manşet' => 'manset',
                    'sürmanşet' => 'surmanset',
                    'öne çıkanlar' => 'one_cikanlar',
                ];
                $zone = $zoneMapping[$this->post_position];
            }

            if ($shouldAddToFeatured) {
                $featuredService = app(FeaturedService::class);

                // Zamanlama tarihleri
                $startsAt = null;
                $endsAt = null;

                // Zamanlanmış durum için published_date'den başlangıç tarihi al
                if ($this->status === 'scheduled' && $this->published_date) {
                    $startsAt = new \DateTime($this->published_date);
                    // Bitiş tarihi sınırsız (null)
                    $endsAt = null;
                } else {
                    // Manuel zamanlama (vitrin zamanlama bölümünden)
                    $startsAt = $this->featuredStartsAt ? new \DateTime($this->featuredStartsAt) : null;
                    $endsAt = $this->featuredEndsAt ? new \DateTime($this->featuredEndsAt) : null;
                }

                // Zamanlama varsa pinScheduled kullan, yoksa normal pin kullan
                if ($startsAt || $endsAt) {
                    $featuredService->pinScheduled(
                        $zone,
                        'post',
                        $this->post->post_id,
                        $startsAt,
                        $endsAt,
                        0 // priority
                    );
                } else {
                    $featuredService->pin(
                        $zone,
                        'post',
                        $this->post->post_id,
                        null // slot - otomatik slot atanacak
                    );
                }
            }

            // Yeni resim eklenmişse existingFiles'ı yenile
            // Not: refreshExistingFiles() çağrılmadan önce updateGalleryContent() çağrılmalı
            // Çünkü yeni dosyalar için açıklamalar existingFiles'da tutuluyor
            if (! empty($this->uploadedFiles)) {
                // Yeni dosyalar için file_path'leri eager loading ile al (refresh() gereksiz)
                $this->post->load('files');

                // existingFiles'ı yenile (yeni dosyalar için açıklamalar korunmalı)
                // refreshExistingFiles() yeni dosyalar için açıklamaları kaybedebilir
                // Bu yüzden sadece galeri için content'ten yükle
                if ($this->post_type === 'gallery') {
                    // Mevcut existingFiles'ı koru (yeni dosyalar için açıklamalar dahil)
                    // Sadece veritabanından yeni dosyaları ekle
                    $galleryData = $this->post->gallery_data;

                    if (is_array($galleryData) && ! empty($galleryData)) {
                        // Mevcut existingFiles'ı file_path ile eşleştir
                        $existingFilesByPath = [];
                        foreach ($this->existingFiles as $file) {
                            $path = $file['path'];
                            if (! empty($path)) {
                                $existingFilesByPath[$path] = $file;
                            }
                        }

                        // Veritabanından yeni dosyaları ekle
                        $this->existingFiles = collect($galleryData)->map(function (array $fileData, int $index) use ($existingFilesByPath) {
                            $filePath = $fileData['file_path'] ?? '';

                            // Mevcut existingFiles'da varsa açıklamaları koru
                            if (isset($existingFilesByPath[$filePath])) {
                                $existingFile = $existingFilesByPath[$filePath];

                                return [
                                    'file_id' => $existingFile['file_id'], // Mevcut file_id'yi koru
                                    'path' => $filePath,
                                    'original_name' => $fileData['filename'],
                                    'description' => $existingFile['description'] ?? $fileData['description'] ?? '', // Açıklamayı koru
                                    'primary' => (bool) $fileData['is_primary'],
                                    'type' => $fileData['type'] ?? 'image/jpeg',
                                    'order' => (int) $fileData['order'],
                                    'uploaded_at' => $fileData['uploaded_at'] ?? now()->toISOString(),
                                ];
                            }

                            // Yeni dosya için file_id oluştur
                            $fileId = $fileData['file_id'] ?? null;
                            if (empty($fileId)) {
                                $fileId = 'existing_'.md5($filePath);
                            }

                            return [
                                'file_id' => (string) $fileId, // String olarak tutuluyor
                                'path' => $filePath,
                                'original_name' => $fileData['filename'],
                                'description' => $fileData['description'] ?? '', // Veritabanından açıklama
                                'primary' => (bool) $fileData['is_primary'],
                                'type' => $fileData['type'] ?? 'image/jpeg',
                                'order' => (int) $fileData['order'],
                                'uploaded_at' => $fileData['uploaded_at'] ?? now()->toISOString(),
                            ];
                        })->toArray();

                        // Primary file ID'yi güncelle
                        $this->primaryFileId = null;
                        foreach ($this->existingFiles as $file) {
                            if ($file['primary'] === true) {
                                $this->primaryFileId = (string) $file['file_id'];
                                break;
                            }
                        }
                    }
                } else {
                    // Galeri dışı için normal refresh
                    $this->refreshExistingFiles();
                }

                // Yeni dosya seçimlerini sıfırla
                $this->uploadedFiles = [];
                $this->newFiles = []; // newFiles'ı da temizle
                // primaryFileId'yi koru - yukarıda zaten güncellendi
            }

            $this->dispatch('post-updated');

            // Clear archiveFilesLinked flag after successful update
            $this->archiveFilesLinked = false;

            // Success mesajını session flash ile göster ve yönlendir
            $successMessage = $this->createContextualSuccessMessage('updated', 'title', 'post');
            if ($shouldAddToFeatured) {
                $successMessage .= " ve {$this->post_position} alanına otomatik eklendi.";
            }
            session()->flash('success', $successMessage);

            return redirect()->route('posts.index');
        } catch (\InvalidArgumentException $e) {
            // Validation hataları - direkt mesaj göster
            LogHelper::warning('updatePost validation failed', [
                'post_id' => $this->post->post_id ?? null,
                'post_type' => $this->post_type,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Exception $e) {
            LogHelper::error('updatePost failed', [
                'post_id' => $this->post->post_id ?? null,
                'post_type' => $this->post_type ?? null,
                'error' => $e->getMessage(),
            ]);

            // Kullanıcıya hata göster
            session()->flash('error', 'Yazı güncellenirken bir hata oluştu: '.$e->getMessage());

            // Hata durumunda sayfada kal
            return;
        }
    }

    public function removeExistingFile($index)
    {
        if (isset($this->existingFiles[$index])) {
            $file = $this->existingFiles[$index];

            // Dosyayı silmek yerine, sadece post ilişkisini kaldır (arşivde kalsın)
            // Böylece ileride "arşivden seç" özelliği ile tekrar kullanılabilir
            if (isset($file['file_id']) && is_numeric($file['file_id'])) {
                /** @var \Modules\Posts\Models\File|null $fileModel */
                $fileModel = $this->post->files->firstWhere('file_id', (int) $file['file_id']);
                if ($fileModel) {
                    // Dosyayı silmek yerine, sadece post ilişkisini kaldır
                    $fileModel->post_id = null;
                    $fileModel->primary = false; // Primary flag'ini de kaldır
                    $fileModel->save();
                } else {
                    // Eğer eager loaded collection'da yoksa, direkt query yap
                    $fileModel = $this->postFileRepository->findById((int) $file['file_id']);
                    if ($fileModel) {
                        // Dosyayı silmek yerine, sadece post ilişkisini kaldır
                        $this->postFileRepository->update($fileModel, [
                            'post_id' => null,
                            'primary' => false,
                        ]);
                    }
                }
            }

            // Array'den kaldır
            unset($this->existingFiles[$index]);
            $this->existingFiles = array_values($this->existingFiles); // Re-index array

            // Eğer silinen dosya ana dosyaysa, ana dosya seçimini sıfırla ve spot_data'yı temizle
            if (isset($file['file_id']) && $this->primaryFileId === (string) $file['file_id']) {
                $this->primaryFileId = null;
                // Primary file kaldırıldığında spot_data'yı sıfırla
                $this->post->spot_data = null;
                $this->post->save();
                $this->post->refresh();
            }

            // Galeri türü için content'i güncelle
            if ($this->post->post_type === 'gallery') {
                $this->updateGalleryContent();
                session()->flash('success', 'Görsel kaldırıldı.');
            }
        }
    }

    public function updateExistingFile($index, $field, $value)
    {
        if (isset($this->existingFiles[$index])) {
            $this->existingFiles[$index][$field] = $value;

            // Otomatik kaydetme kaldırıldı - sadece güncelle butonunda kaydedilecek
        }
    }

    public function updateExistingFileById($fileId, $field, $value)
    {
        // Güvenlik: fileId ve field kontrolü
        if (empty($fileId) || empty($field)) {
            return;
        }

        // Create sayfasındaki gibi basit arama
        foreach ($this->existingFiles as $index => $file) {
            if (isset($file['file_id']) && (string) $file['file_id'] === (string) $fileId) {
                $this->existingFiles[$index][$field] = $value;

                // Otomatik kaydetme kaldırıldı - sadece memory'de güncelle
                // Kaydetme butonunda kaydedilecek (performans optimizasyonu)
                // Galeri türü için content'i güncelleme kaldırıldı - sadece kaydetme butonunda kaydedilecek

                return; // Başarılı güncelleme
            }
        }

    }

    /**
     * Unified method to update file by ID (supports both existing and uploaded files)
     *
     * @param  int|string  $fileId
     * @param  string  $field  (description, title, alt)
     * @param  string  $value
     */
    public function updateFileById($fileId, $field, $value)
    {
        try {
            // Güvenlik: fileId ve field kontrolü
            if (empty($fileId) || empty($field)) {
                return;
            }

            // Field validation
            if (! in_array($field, ['description', 'title', 'alt'])) {
                abort(403, 'Geçersiz alan');
            }

            // Value validation
            if (! is_string($value) && ! is_null($value)) {
                throw new \InvalidArgumentException('Değer string veya null olmalıdır');
            }

            // Max length validation
            $maxLengths = [
                'description' => 10000, // Trix editor için yeterli
                'title' => 255,
                'alt' => 255,
            ];

            $fieldNames = [
                'description' => 'Açıklama',
                'title' => 'Başlık',
                'alt' => 'Alt metin',
            ];

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

            // Map field names to database columns
            $dbFieldMap = [
                'description' => 'caption',
                'title' => 'title',
                'alt' => 'alt_text',
            ];

            // $dbField = $dbFieldMap[$field] ?? $field;
            $dbField = $dbFieldMap[$field];

            // Convert fileId to string for comparison (handles both string and numeric IDs)
            $fileIdStr = (string) $fileId;

            $updated = false;

            // First, try to update in existingFiles array (existing files)
            foreach ($this->existingFiles as $index => $file) {
                $currentFileId = isset($file['file_id']) ? (string) $file['file_id'] : null;

                if ($currentFileId === $fileIdStr) {
                    // Mevcut değeri log'la
                    $oldValue = $this->existingFiles[$index][$field] ?? '';

                    // existingFiles array'ini güncelle
                    $this->existingFiles[$index][$field] = $value;
                    $updated = true;

                    // If fileId is numeric (existing file in database), update database
                    // Eager loaded files collection'ından bul (N+1 query önleme)
                    if (is_numeric($fileId)) {
                        /** @var \Modules\Posts\Models\File|null $fileModel */
                        $fileModel = $this->post->files->firstWhere('file_id', (int) $fileId);
                        if ($fileModel) {
                            $this->postFileRepository->update($fileModel, [$dbField => $value]);
                        } else {
                            $fileModel = $this->postFileRepository->findById((int) $fileId);
                            if ($fileModel) {
                                $this->postFileRepository->update($fileModel, [$dbField => $value]);
                            }
                        }
                    } else {
                        // String ID ise ve DB güncellemesi yapılamadıysa, file_path veya orijinal adıyla eşleştir
                        $realId = null;
                        if (! empty($file['path'])) {
                            $fm = $this->postFileRepository->getQuery()
                                ->where('post_id', $this->post->post_id)
                                ->where('file_path', $file['path'])
                                ->first();
                            if ($fm) {
                                /** @var \Modules\Posts\Models\File $fm */
                                $realId = $fm->file_id;
                            }
                        }
                        if (! $realId && ! empty($file['original_name'])) {
                            $fmName = $this->postFileRepository->getQuery()
                                ->where('post_id', $this->post->post_id)
                                ->where('title', $file['original_name'])
                                ->orderBy('created_at', 'desc')
                                ->first();
                            if ($fmName) {
                                /** @var \Modules\Posts\Models\File $fmName */
                                $realId = $fmName->file_id;
                            }
                        }
                        if (! $realId && isset($file['order'])) {
                            $fmOrder = $this->postFileRepository->getQuery()
                                ->where('post_id', $this->post->post_id)
                                ->where('order', (int) $file['order'])
                                ->first();
                            if ($fmOrder) {
                                /** @var \Modules\Posts\Models\File $fmOrder */
                                $realId = $fmOrder->file_id;
                            }
                        }
                        if ($realId) {
                            $fileModel = $this->postFileRepository->findById($realId);
                            if ($fileModel) {
                                $this->postFileRepository->update($fileModel, [$dbField => $value]);
                            }
                        }
                    }

                    // Gallery için description güncellendiğinde veritabanına kaydet
                    if ($this->post->post_type === 'gallery' && $field === 'description') {
                        // Gallery post'ları için hemen veritabanına kaydet
                        // updateGalleryContent existingFiles array'ini kullanarak posts.content JSON'ına kaydeder
                        $this->updateGalleryContent();
                    }

                    break;
                }
            }

            // If not found in existingFiles, try uploadedFiles array (new files)
            if (! $updated && isset($this->uploadedFiles[$fileIdStr])) {
                $this->uploadedFiles[$fileIdStr][$field] = $value;
                $updated = true;
            }
        } catch (\InvalidArgumentException $e) {
            LogHelper::warning('updateFileById validation failed', [
                'fileId' => $fileId,
                'field' => $field,
                'post_id' => $this->post->post_id ?? null,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            LogHelper::error('updateFileById failed', [
                'fileId' => $fileId,
                'field' => $field,
                'post_id' => $this->post->post_id ?? null,
                'error' => $e->getMessage(),
            ]);

            // Kullanıcıya hata göster
            session()->flash('error', 'Dosya güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    private function updateGalleryContent(): bool
    {
        try {
            // existingFiles'dan açıklamaları kontrol et (debug)
            $descriptions = array_map(function ($file) {
                return [
                    'file_id' => $file['file_id'],
                    'description' => $file['description'] ?? '',
                    'description_length' => strlen($file['description'] ?? ''),
                    'has_description' => ! empty($file['description']),
                    'original_name' => $file['original_name'],
                ];
            }, $this->existingFiles);

            // PostsService kullanarak veritabanına kaydet
            $result = $this->postsService->saveGalleryContent(
                $this->post,
                $this->existingFiles,
                $this->primaryFileId // String olarak gönder (int'e cast etme)
            );

            if ($result) {
                // gallery_data'yı güncellemek için refresh() kullan (gallery_data JSON column güncellendi)
                $this->post->refresh();

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

                return true;
            }

            return false;
        } catch (\Exception $e) {
            LogHelper::error('Galeri içeriği güncellenirken hata oluştu', [
                'post_id' => $this->post->post_id,
                'error' => $e->getMessage(),
            ]);

            // Kullanıcıya hata göster
            session()->flash('error', 'Galeri içeriği güncellenirken bir hata oluştu: '.$e->getMessage());

            throw $e;
        }
    }

    // Ana resmi kaldır (dosyayı silme, sadece ilişkiyi kaldır - arşivden seç özelliği için)
    public function removePrimaryFile()
    {
        try {
            $primaryFile = $this->post->primaryFile;
            if ($primaryFile) {
                // Dosyayı silmek yerine, sadece post ilişkisini kaldır (arşivde kalsın)
                // Böylece ileride "arşivden seç" özelliği ile tekrar kullanılabilir
                $primaryFile->post_id = null;
                $primaryFile->primary = false; // Primary flag'ini de kaldır
                $primaryFile->save();
            }

            // Primary file ID'yi sıfırla
            $this->primaryFileId = null;

            // Reset image editor properties
            $this->resetImageEditorProperties();

            // Primary file kaldırıldığında spot_data'yı sıfırla
            $this->post->spot_data = null;
            $this->post->save();

            // Refresh post to ensure primaryFile relationship is updated
            $this->post->refresh();

            // Double-check: if primaryFile is still set after refresh, clear spot_data again
            if ($this->post->primaryFile === null && $this->post->spot_data !== null) {
                $this->post->spot_data = null;
                $this->post->save();
                $this->post->refresh();
            }

            LogHelper::info('PostEdit removePrimaryFile - Primary file relationship removed (file archived, not deleted)', [
                'post_id' => $this->post->post_id ?? null,
                'file_id' => ($primaryFile !== null ? $primaryFile->file_id : null),
                'spot_data_after' => $this->post->spot_data,
            ]);
        } catch (\Exception $e) {
            LogHelper::error('removePrimaryFile failed', [
                'post_id' => $this->post->post_id ?? null,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Ana görsel kaldırılırken bir hata oluştu: '.$e->getMessage());
        }
    }

    // Yüklenen dosyayı kaldır
    public function removeUploadedFile($fileId)
    {
        if (isset($this->uploadedFiles[$fileId])) {
            unset($this->uploadedFiles[$fileId]);
        }
    }

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
            // Post'u refresh et ve existingFiles'ı yeniden yükle
            $this->post->refresh();
            $this->loadExistingFiles();
        }
    }

    /**
     * Hydrate metodu - property'ler ayarlandıktan sonra çağrılır (iç içe component'ler için)
     */
    public function hydrate(): void
    {
        // Eğer post property olarak ayarlanmışsa ama başlatılmamışsa, başlat
        if ($this->post !== null && empty($this->title)) {
            // Post'un eager loaded ilişkilere sahip olduğundan emin ol
            if (! $this->post->relationLoaded('files')) {
                $this->post->load(['files', 'categories', 'tags', 'primaryFile', 'author', 'creator', 'updater']);
            }
            $this->initializeFromPost();
        }

        // Gallery post'ları için existingFiles'ı her zaman yeniden yükle
        // Bu sayede description'lar her zaman güncel olur (sayfa yenilendiğinde bile)
        if (isset($this->post) && $this->post->post_type === 'gallery') {
            // Eager loading ile ilişkileri yükle (refresh() yerine - daha performanslı)
            $this->post->load('files');

            // existingFiles'ı yeniden yükle (description'lar dahil - veritabanından)
            $this->loadExistingFiles();
        }
    }

    public function render()
    {
        // Kategori türüne göre filtreleme - cache ile optimize et
        $cacheKey = 'posts:categories:'.$this->post_type;
        $categories = Cache::remember($cacheKey, 300, function () {
            return $this->categoryService->getQuery()
                ->where('status', 'active')
                ->where('type', $this->post_type)
                ->orderBy('name')
                ->get();
        });

        $postTypes = PostType::all();
        $postPositions = PostPosition::all();
        $postStatuses = PostStatus::all();

        /** @var view-string $view */
        $view = 'posts::livewire.post-edit-news';

        return view($view, compact('categories', 'postTypes', 'postPositions', 'postStatuses'));
    }
}
