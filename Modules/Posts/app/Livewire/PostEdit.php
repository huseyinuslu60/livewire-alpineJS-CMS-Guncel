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
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Categories\Models\Category;
use Modules\Headline\Services\FeaturedService;
use Modules\Posts\Models\File;
use Modules\Posts\Domain\ValueObjects\PostStatus;
use Modules\Posts\Domain\ValueObjects\PostType;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

class PostEdit extends Component
{
    use SecureFileUpload, ValidationMessages, WithFileUploads;

    protected PostsService $postsService;

    protected SlugGenerator $slugGenerator;

    public Post $post;

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

    /**
     * Flag to track if image editor was used (to avoid saving empty spot_data)
     */
    protected bool $imageEditorUsed = false;

    // Diğer
    /** @var array<int> */
    public array $categoryIds = [];

    public string $tagsInput = '';

    public string $postType = 'news';

    // Otomatik vitrin ekleme için zamanlama
    public string $featuredStartsAt = '';

    public string $featuredEndsAt = '';

    protected $listeners = ['contentUpdated', 'updateFileOrder'];

    public function boot()
    {
        $this->postsService = app(PostsService::class);
        $this->slugGenerator = app(SlugGenerator::class);
    }

    public function mount($post)
    {
        Gate::authorize('edit posts');

        // Eğer $post string ise, Post model'ini bul
        if (is_string($post) || is_numeric($post)) {
            $postId = $post;
        } else {
            $postId = $post->post_id;
        }

        // Model'i eager loading ile yükle (refresh() gereksiz - findOrFail zaten fresh data getiriyor)
        $this->post = Post::with(['files', 'categories', 'tags', 'primaryFile', 'author', 'creator', 'updater'])
            ->findOrFail($postId);
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
        $this->desktopCrop = [];
        $this->mobileCrop = [];
        $this->desktopFocus = 'center';
        $this->mobileFocus = 'center';
        $this->imageEffects = [];
        $this->imageMeta = [];

        // Load spot_data if exists, otherwise migrate from legacy data
        $this->loadSpotData();
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
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($galleryData) || empty($content)) {
                // Post model zaten eager loaded, tekrar query gereksiz
                $this->post->refresh(); // Sadece content attribute'unu güncellemek için
                $content = $this->post->content;
                $galleryData = json_decode($content, true) ?: [];
            }


            if (is_array($galleryData) && ! empty($galleryData)) {
                // Order'a göre sırala
                $sortedGalleryData = collect($galleryData)->sortBy('order')->values()->toArray();

                $this->existingFiles = collect($sortedGalleryData)->map(function ($fileData, $index) {
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
                'uploaded_at' => $primaryFile->created_at ? $primaryFile->created_at->toISOString() : now()->toISOString(),
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
        if (!is_array($spotData)) {
            $spotData = [];
        }

        // Load spot_data to properties if exists
        if (!empty($spotData) && isset($spotData['image']) && is_array($spotData['image'])) {
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
        if (!is_array($spotData) || empty($spotData) || !isset($spotData['image'])) {
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

        // Only update if we have image data
        if (!empty($this->originalImagePath)) {
            // Ensure all arrays are properly formatted
            $desktopCrop = is_array($this->desktopCrop) ? $this->desktopCrop : [];
            $mobileCrop = is_array($this->mobileCrop) ? $this->mobileCrop : [];
            $imageEffects = is_array($this->imageEffects) ? $this->imageEffects : [];
            $imageMeta = is_array($this->imageMeta) ? $this->imageMeta : [];
            $textObjects = is_array($this->imageTextObjects) ? $this->imageTextObjects : [];


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
                'textObjects' => $textObjects,
            ];
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
            'post_position' => 'required|in:'.implode(',', Post::POSITIONS),
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
        if (!is_string($value)) {
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
                $this->existingFiles = collect($galleryData)->map(function ($fileData, $index) {
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

                // Eğer silinen dosya ana görsel ise, ana görsel seçimini sıfırla
                if ((string) $this->primaryFileId === (string) $fileId) {
                    $this->primaryFileId = null;
                }
            }
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
            if (!is_string($description) && !is_null($description)) {
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
     * @param string|int $identifier File index or file_id
     * @param string $imageUrl Edited image URL
     * @param string|null $tempPath Temporary file path
     * @param array|string|null $editorData Image editor data (crop, effects, meta) - can be array or JSON string
     */
    public function updateFilePreview($identifier, $imageUrl, $tempPath = null, $editorData = null)
    {

        // Extract path from URL
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
                    // Path'i güncelle (storage/ ile başlayan path)
                    $this->existingFiles[$index]['path'] = $path;
                    $this->existingFiles[$index]['is_new'] = false;

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
                            $this->updateImageEditorData($editorData);
                        }
                    }

                    return;
                }
            }

            // Eğer Posts modülündeki File model'inde varsa güncelle
            // Eager loaded files collection'ından bul (N+1 query önleme)
            $file = $this->post->files->firstWhere('file_id', $identifier);
            if ($file) {
                $file->update(['file_path' => $path]);

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
                $this->existingFiles[$index]['path'] = $path;
                $this->existingFiles[$index]['is_new'] = false;

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

        // Extract effects
        if (isset($editorData['effects']) && is_array($editorData['effects'])) {
            $this->imageEffects = $editorData['effects'];
        }

        // Extract meta
        if (isset($editorData['meta']) && is_array($editorData['meta'])) {
            $this->imageMeta = array_merge($this->imageMeta, $editorData['meta']);
        }

        // Extract text objects (store in a property for later use in buildSpotDataArray)
        if (isset($editorData['textObjects']) && is_array($editorData['textObjects'])) {
            $this->imageTextObjects = $editorData['textObjects'];
        } else {
            LogHelper::warning('PostEdit updateImageEditorData - textObjects not found');
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
        if (in_array($this->post_type, ['news', 'video']) && ! empty($this->uploadedFiles)) {
            // Mevcut primary file'ı bul ve güncelle
            $existingPrimaryFile = $this->post->primaryFile;

            if ($existingPrimaryFile) {
                // Mevcut resmi güncelle
                $newFile = collect($this->uploadedFiles)->first()['file'];
                $existingPrimaryFile->update([
                    'file_path' => $newFile->store('posts/'.date('Y/m'), 'public'),
                    'title' => $newFile->getClientOriginalName(),
                    'file_size' => $newFile->getSize(),
                    'mime_type' => $newFile->getMimeType(),
                ]);
            } else {
                // Yeni resim ekle
                $newFile = collect($this->uploadedFiles)->first()['file'];
                $this->post->files()->create([
                    'file_path' => $newFile->store('posts/'.date('Y/m'), 'public'),
                    'title' => $newFile->getClientOriginalName(),
                    'file_size' => $newFile->getSize(),
                    'mime_type' => $newFile->getMimeType(),
                ]);
            }
        }

        // Build spot_data from properties before saving
        $this->buildSpotData();

        $postsService = new PostsService;
        $postsService->update(
            $this->post,
            $formData,
            $newFiles,
            $this->categoryIds,
            $tagIds,
            $fileDescriptions
        );

        // Save spot_data after post update only if image editor was used
        if ($this->imageEditorUsed) {
            $this->post->spot_data = $this->buildSpotDataArray();
            $this->post->save();
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
                    $this->existingFiles = collect($galleryData)->map(function ($fileData, $index) use ($existingFilesByPath) {
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

            // Success mesajını session flash ile göster ve yönlendir
            $successMessage = $this->createContextualSuccessMessage('updated', 'title', 'post');
            if ($shouldAddToFeatured) {
                $successMessage .= " ve {$this->post_position} alanına otomatik eklendi.";
            }
            session()->flash('success', $successMessage);

            return redirect()->route('posts.index');
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

            // Sadece gerçek file_id'leri (integer) database'den sil
            // Eager loaded files collection'ından bul (N+1 query önleme)
            if (isset($file['file_id']) && is_numeric($file['file_id'])) {
                $fileModel = $this->post->files->firstWhere('file_id', (int) $file['file_id']);
                if ($fileModel) {
                    $fileModel->delete();
                } else {
                    // Eğer eager loaded collection'da yoksa, direkt query yap
                    File::where('file_id', $file['file_id'])->delete();
                }
            }

            // Array'den kaldır
            unset($this->existingFiles[$index]);
            $this->existingFiles = array_values($this->existingFiles); // Re-index array

            // Eğer silinen dosya ana dosyaysa, ana dosya seçimini sıfırla
            if (isset($file['file_id']) && $this->primaryFileId === (string) $file['file_id']) {
                $this->primaryFileId = null;
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
            if (!is_string($value) && !is_null($value)) {
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

            if (isset($maxLengths[$field]) && strlen($value) > $maxLengths[$field]) {
                $fieldName = $fieldNames[$field] ?? $field;
                throw new \InvalidArgumentException("{$fieldName} en fazla {$maxLengths[$field]} karakter olabilir");
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
                        $fileModel = $this->post->files->firstWhere('file_id', (int) $fileId);
                        if ($fileModel) {
                            $fileModel->update([$dbField => $value]);
                        } else {
                            File::where('file_id', (int) $fileId)->update([$dbField => $value]);
                        }
                    } else {
                        // String ID ise ve DB güncellemesi yapılamadıysa, file_path veya orijinal adıyla eşleştir
                        $realId = null;
                        if (!empty($file['path'])) {
                            $fm = File::where('post_id', $this->post->post_id)
                                ->where('file_path', $file['path'])
                                ->first();
                            if ($fm) { $realId = $fm->file_id; }
                        }
                        if (!$realId && !empty($file['original_name'])) {
                            $fmName = File::where('post_id', $this->post->post_id)
                                ->where('title', $file['original_name'])
                                ->orderBy('created_at', 'desc')
                                ->first();
                            if ($fmName) { $realId = $fmName->file_id; }
                        }
                        if (!$realId && isset($file['order'])) {
                            $fmOrder = File::where('post_id', $this->post->post_id)
                                ->where('order', (int) $file['order'])
                                ->first();
                            if ($fmOrder) { $realId = $fmOrder->file_id; }
                        }
                        if ($realId) {
                            File::where('file_id', (int) $realId)->update([$dbField => $value]);
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
                    'has_description' => !empty($file['description']),
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

    // Ana resmi kaldır
    public function removePrimaryFile()
    {
        try {
            if ($this->post->primaryFile) {
                $this->post->primaryFile->delete();
                $this->post->refresh();
            }
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
            // mount() zaten eager loading ile yüklüyor, refresh() gereksiz
            $this->mount($this->post->post_id);
        }
    }

    /**
     * Hydrate component - called when component is loaded/re-rendered
     * Ensures existingFiles is always up-to-date for gallery posts
     */
    public function hydrate()
    {
        // Gallery post'ları için existingFiles'ı her zaman yeniden yükle
        // Bu sayede description'lar her zaman güncel olur (sayfa yenilendiğinde bile)
        // updateFileById zaten veritabanına kaydediyor, o yüzden her zaman veritabanından yüklemek güvenli
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
        $cacheKey = 'posts:categories:' . $this->post_type;
        $categories = Cache::remember($cacheKey, 300, function () {
            return Category::where('status', 'active')
                ->where('type', $this->post_type)
                ->orderBy('name')
                ->get();
        });

        $postTypes = PostType::all();
        $postPositions = Post::POSITIONS;
        $postStatuses = PostStatus::all();

        /** @var view-string $view */
        $view = 'posts::livewire.post-edit';

        return view($view, compact('categories', 'postTypes', 'postPositions', 'postStatuses'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
