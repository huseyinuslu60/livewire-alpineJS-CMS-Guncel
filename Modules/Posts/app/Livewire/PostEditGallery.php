<?php

namespace Modules\Posts\Livewire;

use App\Helpers\LogHelper;
use App\Services\SlugGenerator;
use App\Services\ValueObjects\Slug;
use App\Traits\SecureFileUpload;
use App\Traits\ValidationMessages;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Categories\Services\CategoryService;
use Modules\Posts\Domain\ValueObjects\PostPosition;
use Modules\Posts\Domain\ValueObjects\PostStatus;
use Modules\Posts\Livewire\Concerns\HandlesArchiveFileSelection;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

/**
 * @property PostsService $postsService
 * @property CategoryService $categoryService
 * @property SlugGenerator $slugGenerator
 */
class PostEditGallery extends Component
{
    use HandlesArchiveFileSelection, SecureFileUpload, ValidationMessages, WithFileUploads;

    protected PostsService $postsService;

    protected CategoryService $categoryService;

    protected SlugGenerator $slugGenerator;

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
     * Görsel düzenleyici veri depolama
     * Anahtar: fileId (string), Değer: kırpma, efektler, meta verileri içeren dizi
     */
    public array $imageEditorData = [];

    /**
     * Görsel düzenleyicinin kullanılıp kullanılmadığını takip eden bayrak (boş spot_data kaydetmeyi önlemek için)
     */
    public bool $imageEditorUsed = false;

    /**
     * Ana görsel spot_data JSON string veya dizi
     * Livewire JSON string'leri otomatik olarak diziye çevirebilir
     *
     * @var string|array|null
     */
    public $primary_image_spot_data = null;

    /**
     * fileId ile eşleştirilmiş gerçek UploadedFile nesneleri
     * Livewire payload'ını stabilize etmek için uploadedFiles meta'sından ayrı tutulur
     *
     * @var array<string, \Illuminate\Http\UploadedFile>
     */
    protected array $uploadedFileObjs = [];

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

    public ?Post $post = null;

    protected $listeners = ['contentUpdated', 'filesSelectedForPost'];

    public function boot()
    {
        $this->postsService = app(PostsService::class);
        $this->categoryService = app(CategoryService::class);
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

        $this->title = $this->post->title;
        $this->slug = $this->post->slug;
        $this->summary = $this->post->summary;
        // Gallery için JSON formatında content
        $this->content = $this->post->content;
        $this->post_position = $this->post->post_position;
        $this->status = $this->post->status ?: 'draft';
        $this->published_date = $this->post->published_date ?
            (is_string($this->post->published_date) ?
                Carbon::parse($this->post->published_date)->format('Y-m-d H:i') :
                $this->post->published_date->format('Y-m-d H:i')) :
            Carbon::now()->format('Y-m-d H:i');
        $this->is_comment = $this->post->is_comment;
        $this->is_mainpage = $this->post->is_mainpage;
        $this->redirect_url = $this->post->redirect_url ?? '';
        $this->is_photo = $this->post->is_photo;
        $this->agency_name = $this->post->agency_name ?? '';
        $this->agency_id = $this->post->agency_id;
        $this->in_newsletter = $this->post->in_newsletter;
        $this->no_ads = $this->post->no_ads;

        $this->categoryIds = $this->post->categories ? $this->post->categories->pluck('category_id')->toArray() : [];
        // tagsInput'ın her zaman string olduğundan emin ol
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

        // Eğer varsa spot_data'yı yükle
        $spotData = $this->post->spot_data ?? [];
        if (isset($spotData['image']) && is_array($spotData['image'])) {
            // Görsel verilerini Livewire property için JSON string'e çevir
            $this->primary_image_spot_data = json_encode($spotData['image']);
        } else {
            $this->primary_image_spot_data = null;
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
    }

    /**
     * Veritabanından mevcut dosyaları yükle (galeri yazıları için)
     */
    protected function loadExistingFiles(): void
    {
        $this->existingFiles = [];

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
                    'file_id' => (string) $fileId,
                    'path' => $fileData['file_path'] ?? '',
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
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'summary' => 'required|string',
            'content' => 'nullable|string',
            'post_position' => 'required|in:'.implode(',', PostPosition::all()),
            'status' => 'required|in:'.implode(',', PostStatus::all()),
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
            'uploadedFiles' => 'nullable|array',
            'uploadedFiles.*.file' => 'nullable|image|max:4096',
            'uploadedFiles.*.description' => 'nullable|string|max:500',
        ];
    }

    /**
     * tagsInput güncellendiğinde her zaman string olduğundan emin ol
     */
    public function updatedTagsInput($value)
    {
        // Livewire serileştirme sorunlarını önlemek için tagsInput'ın her zaman string olduğundan emin ol
        if (! is_string($value)) {
            $this->tagsInput = is_array($value) ? implode(', ', array_filter($value)) : (string) ($value ?? '');
        } else {
            // String'i temizle: fazla boşlukları kaldır, uygun virgül ayrımını sağla
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

    // Arşivden seçilen dosyaları gerçek galeri öğelerine dönüştür
    public function filesSelectedForPost($data)
    {
        if (! isset($data['files']) || ! is_array($data['files']) || empty($data['files'])) {
            return;
        }

        try {
            $uploadedFiles = [];
            $archivePreviewUrls = [];

            foreach ($data['files'] as $selectedFile) {
                if (! isset($selectedFile['url'])) {
                    continue;
                }

                $imageUrl = $selectedFile['url'];
                $filePath = null;

                if (str_starts_with($imageUrl, asset(''))) {
                    $relativePath = str_replace(asset(''), '', $imageUrl);
                    $filePath = public_path($relativePath);
                } elseif (str_starts_with($imageUrl, 'http')) {
                    $imageContent = @file_get_contents($imageUrl);
                    if ($imageContent === false) {
                        continue;
                    }
                    $tempDir = sys_get_temp_dir();
                    $fileName = $selectedFile['title'] ?? 'archive-'.uniqid().'.jpg';
                    $tempFilePath = $tempDir.'/'.'livewire-archive-'.uniqid().'-'.$fileName;
                    file_put_contents($tempFilePath, $imageContent);
                    $filePath = $tempFilePath;
                } else {
                    $filePath = public_path('storage/'.$imageUrl);
                }

                if (! $filePath || ! file_exists($filePath)) {
                    continue;
                }

                $mimeType = mime_content_type($filePath) ?: 'image/jpeg';
                $originalName = $selectedFile['title'] ?? basename($filePath);

                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $filePath,
                    $originalName,
                    $mimeType,
                    null,
                    true
                );

                $uploadedFiles[] = $uploadedFile;
                // Orijinal seçimden önizleme URL'i oluştur
                $previewForGallery = $imageUrl;
                if (! (str_starts_with($previewForGallery, 'http') || str_starts_with($previewForGallery, asset('')))) {
                    $previewForGallery = asset('storage/'.ltrim($previewForGallery, '/'));
                }
                $archivePreviewUrls[] = $previewForGallery;
            }

            // Dosyaları doğrudan işleyip uploadedFiles'a ekle (Livewire payload uyumu için)
            if (! empty($uploadedFiles)) {
                $result = $this->processSecureUploads($uploadedFiles);
                if (! empty($result['errors'])) {
                    foreach ($result['errors'] as $error) {
                        $this->addError('newFiles', $error);
                    }
                }
                foreach ($result['files'] as $i => $fileData) {
                    $file = $fileData['file'];
                    $fileId = 'file_'.time().'_'.rand(1000, 9999);
                    // Dosya nesnesini ayrı olarak sakla (Livewire tarafından serileştirilmez)
                    $this->uploadedFileObjs[$fileId] = $file;
                    // Önizleme URL'i oluştur
                    $previewUrl = $archivePreviewUrls[$i] ?? '';
                    if (empty($previewUrl)) {
                        try {
                            $previewUrl = method_exists($file, 'temporaryUrl') ? ($file->temporaryUrl() ?: '') : '';
                        } catch (\Exception $e) {
                            $previewUrl = '';
                        }
                    }
                    if (empty($previewUrl)) {
                        $previewUrl = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="#f3f4f6"/></svg>');
                    }
                    $pathNoDomain = preg_replace('#^https?://[^/]+#', '', $previewUrl);
                    $pathNoStorage = preg_replace('#^/storage/#', '', $pathNoDomain);
                    $derivedPath = ltrim($pathNoStorage, '/');
                    $this->uploadedFiles[$fileId] = [
                        'preview_url' => $previewUrl,
                        'file_path' => $derivedPath,
                        'name' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),
                        'description' => '',
                        'alt_text' => '',
                    ];
                }
                // Ana görsel yoksa ilkini ata
                if (empty($this->primaryFileId)) {
                    if (! empty($this->uploadedFiles)) {
                        $firstFileId = array_keys($this->uploadedFiles)[0];
                        $this->primaryFileId = $firstFileId;
                    } elseif (! empty($this->existingFiles)) {
                        // Eğer uploadedFiles boşsa, existingFiles'dan ilkini seç
                        $firstExistingFile = reset($this->existingFiles);
                        $this->primaryFileId = $firstExistingFile['file_id'] ?? null;
                    }
                }
            }

            // Arşiv ön izlemesini temizle (tek grid gösterimi için)
            $this->selectedArchiveFilesPreview = [];
            $this->selectedArchiveFileIds = [];

            session()->flash('success', count($uploadedFiles).' dosya arşivden galeriye eklendi');
        } catch (\Exception $e) {
            \App\Helpers\LogHelper::error('Arşivden galeriye dosya eklerken hata', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Dosya eklenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    // Eski metod kaldırıldı (filesSelectedForPostOld) – tek akış filesSelectedForPost üzerinden

    public function setPrimaryFile($fileId)
    {
        $this->primaryFileId = $fileId;
    }

    public function updatedPrimaryFileId($value)
    {
        // Tutarlı karşılaştırma için primaryFileId'nin her zaman string olduğundan emin ol
        $this->primaryFileId = $value !== null ? (string) $value : null;

        // Rozetleri güncellemek için Livewire'ı yeniden render etmeye zorla
        $this->dispatch('primary-file-changed', primaryFileId: $this->primaryFileId);
    }

    public function updatePost()
    {
        // Duplicate submit'i engelle
        if ($this->isSaving) {
            return;
        }

        Gate::authorize('edit posts');

        // İşlemeden önce gizli input'tan primary_image_spot_data'yı zorla senkronize et
        // Bu, JS düzenleyicisinden en son değerin yakalanmasını sağlar
        // Hem request input hem de property'yi dene (property wire:model ile güncellenmiş olabilir)
        $primaryImageInputFromRequest = request()->input('primary_image_spot_data');
        if ($primaryImageInputFromRequest) {
            // Eğer request'te varsa, onu kullan (form gönderiminden en güncel olan)
            $this->primary_image_spot_data = $primaryImageInputFromRequest;
        }

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
                if (! $this->slugGenerator->isUnique($slug, Post::class, 'slug', 'post_id')) {
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
            if (empty($this->uploadedFiles) && empty($this->existingFiles)) {
                $this->addError('uploadedFiles', 'Galeri yazıları için en az bir görsel yüklenmelidir.');
                $this->isSaving = false;

                return;
            }

            // Ek değer doğrulaması
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
            // ÖNEMLİ: Sadece yeni dosyaları işle (existing files zaten existingFiles'da ve updateGalleryContent ile işlenecek)
            $galleryData = [];
            if (! empty($this->uploadedFiles)) {
                $fileKeys = array_keys($this->uploadedFiles);

                // Existing files'ın file_id'lerini topla (duplicate kontrolü için)
                $existingFileIds = [];
                foreach ($this->existingFiles as $existingFile) {
                    if (isset($existingFile['file_id'])) {
                        $existingFileIds[] = (string) $existingFile['file_id'];
                    }
                }

                foreach ($this->uploadedFiles as $fileId => $fileData) {
                    // Eğer bu dosya existingFiles'da varsa, atla (duplicate önleme)
                    if (in_array((string) $fileId, $existingFileIds, true)) {
                        LogHelper::info('PostEditGallery updatePost - Skipping existing file in uploadedFiles', [
                            'fileId' => $fileId,
                        ]);

                        continue;
                    }

                    $index = array_search($fileId, $fileKeys);
                    $previewUrl = $fileData['preview_url'] ?? '';
                    $derivedPath = '';
                    if (! empty($previewUrl)) {
                        $pathNoDomain = preg_replace('#^https?://[^/]+#', '', $previewUrl);
                        $pathNoStorage = preg_replace('#^/storage/#', '', $pathNoDomain);
                        $derivedPath = ltrim($pathNoStorage, '/');
                    }
                    $galleryData[] = [
                        'order' => $index,
                        'filename' => $fileData['name'] ?? '',
                        'file_path' => $derivedPath,
                        'type' => $fileData['mime'] ?? 'image/jpeg',
                        'is_primary' => $this->primaryFileId === $fileId,
                        'uploaded_at' => now()->toISOString(),
                        'description' => $fileData['description'] ?? '',
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
                    $name = $data['name'] ?? null;
                    if ($name) {
                        $fileDescriptions[$name] = [
                            'description' => $data['description'] ?? '',
                            'alt_text' => $data['alt_text'] ?? '',
                        ];
                    }
                }
            }

            // Dosyaları sıralı şekilde al
            $orderedFiles = [];
            if (! empty($this->uploadedFiles)) {
                foreach ($this->uploadedFiles as $fileId => $_) {
                    if (isset($this->uploadedFileObjs[$fileId])) {
                        $orderedFiles[] = $this->uploadedFileObjs[$fileId];
                    }
                }
            }

            $postsService = new PostsService;

            // Yeni dosyaları hazırla
            $newFiles = [];
            if (! empty($this->uploadedFiles)) {
                foreach ($this->uploadedFiles as $fileId => $_) {
                    if (isset($this->uploadedFileObjs[$fileId])) {
                        $newFiles[] = $this->uploadedFileObjs[$fileId];
                    }
                }
            }

            $updatedPost = $postsService->update(
                $this->post,
                $formData,
                $newFiles,
                $this->categoryIds,
                $tagIds,
                $fileDescriptions
            );

            // Servis güncellemesinden sonra post modelini yenile
            $this->post = $updatedPost;
            $this->post->refresh();

            // Arşivden seçilen dosyaları post'a bağla
            $this->linkArchiveFilesToPost($this->post, true);

            // Sadece görsel verileriyle spot_data'yı oluştur ve kaydet
            // Sadece görsel düzenleyici gerçekten kullanıldıysa spot_data'yı kaydet (PostEdit gibi)
            $spotData = [];
            $primaryImageSpotData = null; // Kapsam için if bloğunun dışında başlat
            $editorData = null; // Kapsam için if bloğunun dışında başlat

            // Ana dosyamız varsa VEYA primary_image_spot_data'mız varsa görsel verilerini ekle
            // ÖNEMLİ: Post yeni oluşturulmuşsa primaryFile null olabilir, ama yine de spot_data'yı kaydetmemiz gerekir

            // primaryFile null olsa bile spot_data'yı işle (post kaydedildikten sonra oluşturulacak)
            // Doğru dosyayı bulmak için property'den veya uploadedFiles'den primaryFileId'yi kullanabiliriz
            $shouldProcessSpotData = $this->post->primaryFile !== null || ! empty($this->primary_image_spot_data) || ! empty($this->imageEditorData);

            if ($shouldProcessSpotData) {
                // Görsel boyutlarını ve hash'i al
                $width = null;
                $height = null;
                $hash = null;
                $filePath = null;

                if ($this->post->primaryFile) {
                    $imagePath = public_path('storage/'.$this->post->primaryFile->file_path);
                    $filePath = $this->post->primaryFile->file_path;

                    if (file_exists($imagePath)) {
                        // Görsel boyutlarını al
                        $imageInfo = @getimagesize($imagePath);
                        if ($imageInfo !== false) {
                            $width = $imageInfo[0];
                            $height = $imageInfo[1];
                        }

                        // Dosya hash'ini hesapla
                        $hash = md5_file($imagePath);
                    }
                } elseif ($this->primaryFileId && isset($this->uploadedFiles[$this->primaryFileId])) {
                    // Eğer primaryFile null ise ama primaryFileId'miz varsa, uploadedFiles'den file_path'i al
                    $filePath = $this->uploadedFiles[$this->primaryFileId]['file_path'] ?? null;
                    if ($filePath) {
                        $imagePath = public_path('storage/'.$filePath);
                        if (file_exists($imagePath)) {
                            $imageInfo = @getimagesize($imagePath);
                            if ($imageInfo !== false) {
                                $width = $imageInfo[0];
                                $height = $imageInfo[1];
                            }
                            $hash = md5_file($imagePath);
                        }
                    }
                }

                // Eğer mevcutsa görsel düzenleyici verilerini al (görsel düzenleyici modal'ından)
                // uploadedFiles'den ana dosyanın fileId'sini bul
                // file_path ile sağlam eşleştirmeyi tercih et
                $primaryFileId = null;

                // Önce primaryFileId property'sini kullanmayı dene (en güvenilir)
                if ($this->primaryFileId) {
                    $primaryFileId = $this->primaryFileId;
                }

                // Eğer primaryFile varsa, file_path ile eşleştirmeyi dene
                if ($primaryFileId === null && $this->post->primaryFile) {
                    foreach ($this->uploadedFiles as $fileId => $fileData) {
                        $filePath = array_key_exists('file_path', $fileData) ? $fileData['file_path'] : null;
                        if (! empty($filePath) && ($filePath === ($this->post->primaryFile->file_path ?? ''))) {
                            $primaryFileId = $fileId;
                            break;
                        }
                    }
                    // İsim ile yedek eşleştirme
                    if ($primaryFileId === null) {
                        foreach ($this->uploadedFiles as $fileId => $fileData) {
                            if (($fileData['name'] ?? '') === ($this->post->primaryFile->original_name ?? '')) {
                                $primaryFileId = $fileId;
                                break;
                            }
                        }
                    }
                }

                // Yedek: Eğer mevcutsa ilk fileId'yi kullan
                if ($primaryFileId === null && ! empty($this->uploadedFiles)) {
                    $primaryFileId = array_key_first($this->uploadedFiles);
                }

                // uploadedFiles meta'sında kalıcı spot_data'yı tercih et; yedek olarak imageEditorData
                // Not: $editorData zaten yukarıda kapsam için başlatıldı
                if ($primaryFileId) {
                    // Önce uploadedFiles'i kontrol et (en güvenilir, yeniden render'lardan kurtulur)
                    if (isset($this->uploadedFiles[$primaryFileId]['spot_data']) && is_array($this->uploadedFiles[$primaryFileId]['spot_data'])) {
                        $editorData = $this->uploadedFiles[$primaryFileId]['spot_data'];
                    } elseif (isset($this->imageEditorData[$primaryFileId]) && is_array($this->imageEditorData[$primaryFileId])) {
                        $editorData = $this->imageEditorData[$primaryFileId];
                    }
                }

                // HER ZAMAN gizli input'tan primary_image_spot_data'yı kontrol et (arşiv görselleri için)
                // Bu, doğrudan JS düzenleyicisinden senkronize edildiği için editorData'dan önceliklidir
                // Not: $primaryImageSpotData zaten yukarıda kapsam için başlatıldı
                // ÖNEMLİ: Property'nin var olduğunu ve boş olmadığını kontrol et (Livewire onu boş string veya boş dizi olarak ayarlayabilir)
                $hasPrimaryImageSpotData = $this->primary_image_spot_data !== null
                    && $this->primary_image_spot_data !== ''
                    && (is_string($this->primary_image_spot_data) ? strlen($this->primary_image_spot_data) > 0 : (is_array($this->primary_image_spot_data) ? count($this->primary_image_spot_data) > 0 : false));

                if ($hasPrimaryImageSpotData) {
                    // Hem string (JSON) hem de array'i handle et (Livewire JSON'u otomatik parse edebilir)
                    if (is_string($this->primary_image_spot_data)) {
                        $decodedImageData = json_decode($this->primary_image_spot_data, true);
                        $jsonError = json_last_error();

                        if ($jsonError === JSON_ERROR_NONE && is_array($decodedImageData)) {
                            $primaryImageSpotData = $decodedImageData;
                        } else {
                            LogHelper::warning('PostEditGallery updatePost - primary_image_spot_data JSON decode hatası', [
                                'json_error' => json_last_error_msg(),
                            ]);
                        }
                    } elseif (is_array($this->primary_image_spot_data)) {
                        $primaryImageSpotData = $this->primary_image_spot_data;
                    }

                    // İç içe yapıyı handle et: primaryImageSpotData'da 'image' key'i varsa unwrap et
                    // Bu, çift iç içe geçmeyi önler: spot_data['image']['image']
                    if ($primaryImageSpotData !== null && isset($primaryImageSpotData['image']) && is_array($primaryImageSpotData['image'])) {
                        $primaryImageSpotData = $primaryImageSpotData['image'];
                    }

                    // Varsa livewire temp preview path'lerini normalize et
                    if ($primaryImageSpotData !== null && isset($primaryImageSpotData['original']['path']) && is_string($primaryImageSpotData['original']['path'])) {
                        $origPath = $primaryImageSpotData['original']['path'];
                        if (str_contains($origPath, 'livewire/preview-file')) {
                            $primaryImageSpotData['original']['path'] = $this->post->primaryFile->file_path ?? $origPath;
                        }
                    }
                }

                // Extract crop, effects, and meta from editor data
                // Priority: primary_image_spot_data > editorData (primary_image_spot_data is synced directly from JS)
                $desktopCrop = [];
                $mobileCrop = [];
                $desktopFocus = 'center';
                $mobileFocus = 'center';
                $imageEffects = [];
                $imageMeta = [
                    'alt' => $this->post->primaryFile->alt_text ?? null,
                    'credit' => null,
                    'source' => null,
                ];
                $textObjects = [];

                // Prefer primary_image_spot_data if available (it's the most up-to-date from JS editor)
                if ($primaryImageSpotData !== null) {
                    // Extract from primary_image_spot_data if editorData is null
                    // Extract crop data
                    if (isset($primaryImageSpotData['variants']['desktop']['crop']) && is_array($primaryImageSpotData['variants']['desktop']['crop'])) {
                        $desktopCrop = $primaryImageSpotData['variants']['desktop']['crop'];
                    }
                    if (isset($primaryImageSpotData['variants']['mobile']['crop']) && is_array($primaryImageSpotData['variants']['mobile']['crop'])) {
                        $mobileCrop = $primaryImageSpotData['variants']['mobile']['crop'];
                    }

                    // Extract focus data
                    if (isset($primaryImageSpotData['variants']['desktop']['focus'])) {
                        $desktopFocus = $primaryImageSpotData['variants']['desktop']['focus'];
                    }
                    if (isset($primaryImageSpotData['variants']['mobile']['focus'])) {
                        $mobileFocus = $primaryImageSpotData['variants']['mobile']['focus'];
                    }

                    // Extract effects
                    if (isset($primaryImageSpotData['effects']) && is_array($primaryImageSpotData['effects'])) {
                        $imageEffects = $primaryImageSpotData['effects'];
                    }

                    // Extract meta
                    if (isset($primaryImageSpotData['meta']) && is_array($primaryImageSpotData['meta'])) {
                        $imageMeta = array_merge($imageMeta, $primaryImageSpotData['meta']);
                    }

                    // Extract text objects
                    if (isset($primaryImageSpotData['textObjects']) && is_array($primaryImageSpotData['textObjects'])) {
                        $textObjects = $primaryImageSpotData['textObjects'];
                    }
                } elseif ($editorData !== null) {
                    // Fallback to editorData if primary_image_spot_data is not available
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

                // Extract canvas dimensions for scaling textObjects on reload
                $canvasDimensions = ['width' => 0, 'height' => 0];
                if ($editorData !== null && isset($editorData['canvas']) && is_array($editorData['canvas'])) {
                    $canvasDimensions = [
                        'width' => isset($editorData['canvas']['width']) ? (int) $editorData['canvas']['width'] : 0,
                        'height' => isset($editorData['canvas']['height']) ? (int) $editorData['canvas']['height'] : 0,
                    ];
                } elseif ($primaryImageSpotData !== null && isset($primaryImageSpotData['canvas']) && is_array($primaryImageSpotData['canvas'])) {
                    $canvasDimensions = [
                        'width' => isset($primaryImageSpotData['canvas']['width']) ? (int) $primaryImageSpotData['canvas']['width'] : 0,
                        'height' => isset($primaryImageSpotData['canvas']['height']) ? (int) $primaryImageSpotData['canvas']['height'] : 0,
                    ];
                }

                // Build spot_data if we have editor data OR primary_image_spot_data
                // primary_image_spot_data takes priority as it's synced directly from JS editor
                if ($primaryImageSpotData !== null || $editorData !== null) {
                    // Use original path from primaryImageSpotData if available, otherwise use file_path
                    $originalPath = $filePath ?? ($this->post->primaryFile->file_path ?? null);
                    if ($primaryImageSpotData !== null && isset($primaryImageSpotData['original']['path'])) {
                        $originalPath = $primaryImageSpotData['original']['path'];
                    }

                    // Use dimensions and hash from primaryImageSpotData if available, otherwise calculate
                    $originalWidth = $width;
                    $originalHeight = $height;
                    $originalHash = $hash;
                    if ($primaryImageSpotData !== null && isset($primaryImageSpotData['original'])) {
                        $originalWidth = $primaryImageSpotData['original']['width'] ?? $width;
                        $originalHeight = $primaryImageSpotData['original']['height'] ?? $height;
                        $originalHash = $primaryImageSpotData['original']['hash'] ?? $hash;
                    }

                    $spotData['image'] = [
                        'original' => [
                            'path' => $originalPath,
                            'width' => $originalWidth,
                            'height' => $originalHeight,
                            'hash' => $originalHash,
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
                        'canvas' => $canvasDimensions,
                    ];
                }
            }

            // Only save spot_data if we have actual editor data
            if (! empty($spotData['image'])) {
                \App\Helpers\LogHelper::info('PostEditGallery updatePost - Saving spot_data', [
                    'has_image' => true,
                    'has_original' => true,
                    'has_variants' => true,
                    'has_effects' => true,
                    'has_textObjects' => true,
                    'textObjects_count' => count($spotData['image']['textObjects']),
                ]);
                $this->post->spot_data = $spotData;
                $this->post->save();
            } else {
                \App\Helpers\LogHelper::warning('PostEditGallery updatePost - spot_data is empty, not saving', [
                    'has_primaryImageSpotData' => $primaryImageSpotData !== null,
                    'has_editorData' => $editorData !== null,
                    'spotData_keys' => array_keys($spotData),
                ]);
            }

            // content'i her durumda güncelle: mevcut post files üzerinden

            $updatedGalleryData = [];
            /** @var \Illuminate\Database\Eloquent\Collection<int, \Modules\Posts\Models\File> $postFiles */
            $postFiles = $this->post->files()->orderBy('order')->get();

            foreach ($postFiles as $index => $file) {
                $name = $file->original_name ?? basename($file->file_path);
                $description = '';
                if (! empty($fileDescriptions) && isset($fileDescriptions[$name])) {
                    $description = $fileDescriptions[$name]['description'];
                }

                $updatedGalleryData[] = [
                    'order' => $index,
                    'filename' => $name,
                    'file_path' => $file->file_path,
                    'type' => $file->mime_type ?? 'image/jpeg',
                    'is_primary' => (bool) ($file->primary ?? false),
                    'uploaded_at' => now()->toISOString(),
                    'description' => $description,
                ];
            }

            if (! empty($updatedGalleryData)) {
                $this->post->update(['content' => json_encode($updatedGalleryData, JSON_UNESCAPED_UNICODE)]);
            }

            // Update gallery content (existingFiles'dan)
            $this->updateGalleryContent();

            $this->dispatch('post-updated');

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('updated', 'title', 'post'));

            return redirect()->route('posts.index');
        } catch (\InvalidArgumentException $e) {
            // Validation hataları - direkt mesaj göster
            LogHelper::warning('PostEditGallery validation failed', [
                'post_type' => 'gallery',
                'post_id' => $this->post->post_id ?? null,
                'error' => $e->getMessage(),
            ]);
            $this->addError('general', $e->getMessage());
        } catch (\Exception $e) {
            LogHelper::error('PostsService hatası', [
                'post_type' => 'gallery',
                'post_id' => $this->post->post_id ?? null,
                'error' => $e->getMessage(),
            ]);
            $this->addError('general', 'Galeri güncellenirken hata oluştu: '.$e->getMessage());
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
        // Yeni dosyalar yüklendiğinde uploadedFiles'a ekle (edit sayfasında)
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
                // store object separately
                $this->uploadedFileObjs[$fileId] = $file;
                // build preview url
                $previewUrl = '';
                try {
                    $previewUrl = method_exists($file, 'temporaryUrl') ? ($file->temporaryUrl() ?: '') : '';
                } catch (\Exception $e) {
                    $previewUrl = '';
                }
                if (empty($previewUrl)) {
                    $previewUrl = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="#f3f4f6"/></svg>');
                }
                $this->uploadedFiles[$fileId] = [
                    'preview_url' => $previewUrl,
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'description' => '',
                    'alt_text' => '',
                    'file_path' => $fileData['path'] ?? '',
                ];
            }

            // newFiles'ı temizle
            $this->newFiles = [];

            // Eğer ana görsel seçili değilse ve dosya varsa, ilk dosyayı ana görsel yap
            if (empty($this->primaryFileId)) {
                if (! empty($this->uploadedFiles)) {
                    $firstFileId = array_keys($this->uploadedFiles)[0];
                    $this->primaryFileId = $firstFileId;
                } elseif (! empty($this->existingFiles)) {
                    // Eğer uploadedFiles boşsa, existingFiles'dan ilkini seç
                    $firstExistingFile = reset($this->existingFiles);
                    $this->primaryFileId = $firstExistingFile['file_id'] ?? null;
                }
            }
        }
    }

    public function removeFile($fileId)
    {
        if (isset($this->uploadedFiles[$fileId])) {
            unset($this->uploadedFiles[$fileId]);
            if (isset($this->uploadedFileObjs[$fileId])) {
                unset($this->uploadedFileObjs[$fileId]);
            }

            // Eğer silinen dosya ana görsel ise, yeni ana görsel seç
            if ($this->primaryFileId === $fileId) {
                // Kalan dosyalar varsa ilkini ana görsel yap
                if (! empty($this->uploadedFiles)) {
                    $firstFileId = array_keys($this->uploadedFiles)[0];
                    $this->primaryFileId = $firstFileId;
                } elseif (! empty($this->existingFiles)) {
                    // Eğer uploadedFiles boşsa, existingFiles'dan ilkini seç
                    $firstExistingFile = reset($this->existingFiles);
                    $this->primaryFileId = $firstExistingFile['file_id'] ?? null;
                } else {
                    $this->primaryFileId = null;
                }
            }
        }
    }

    public function removeExistingFile($fileId)
    {
        try {
            // existingFiles array'inden kaldır
            foreach ($this->existingFiles as $index => $file) {
                if (isset($file['file_id']) && (string) $file['file_id'] === (string) $fileId) {
                    unset($this->existingFiles[$index]);
                    // Re-index array
                    $this->existingFiles = array_values($this->existingFiles);
                    break;
                }
            }

            // Eğer silinen dosya ana görsel ise, yeni ana görsel seç
            if ($this->primaryFileId === (string) $fileId) {
                // Kalan dosyalar varsa ilkini ana görsel yap
                if (! empty($this->existingFiles)) {
                    $firstFile = reset($this->existingFiles);
                    $this->primaryFileId = $firstFile['file_id'] ?? null;
                } elseif (! empty($this->uploadedFiles)) {
                    // Eğer existingFiles boşsa, uploadedFiles'dan ilkini seç
                    $firstFileId = array_keys($this->uploadedFiles)[0];
                    $this->primaryFileId = $firstFileId;
                } else {
                    $this->primaryFileId = null;
                }
            }

            // Veritabanından da kaldır (files tablosundan)
            // Find file by file_path from existingFiles before removal
            $filePathToDelete = null;
            foreach ($this->existingFiles as $file) {
                if (isset($file['file_id']) && (string) $file['file_id'] === (string) $fileId) {
                    $filePathToDelete = $file['path'] ?? null;
                    break;
                }
            }

            if ($filePathToDelete) {
                $fileModel = $this->post->files()->where('file_path', $filePathToDelete)->first();
                if ($fileModel) {
                    $fileModel->delete();
                }
            }

            // Galeri content'ini güncelle
            $this->updateGalleryContent();

            session()->flash('success', 'Görsel kaldırıldı.');
        } catch (\Exception $e) {
            LogHelper::error('removeExistingFile failed', [
                'fileId' => $fileId,
                'post_id' => $this->post->post_id ?? null,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Görsel kaldırılırken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function updateFilePreview($identifier, $imageUrl, $tempPath = null, $spotDataImage = null)
    {
        $this->skipRender();

        // Store spot_data image object if provided
        // IMPORTANT: This now receives spot_data['image'] object, NOT raw editorData
        // Format: { original: {...}, variants: {...}, effects: {...}, textObjects: [...], canvas: {...} }
        if ($spotDataImage !== null) {
            // If spotDataImage is a JSON string, decode it
            if (is_string($spotDataImage)) {
                $decoded = json_decode($spotDataImage, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $spotDataImage = $decoded;
                } else {
                    LogHelper::warning('PostCreateGallery updateFilePreview - Failed to decode JSON spotDataImage', [
                        'json_error' => json_last_error_msg(),
                        'identifier' => $identifier,
                    ]);
                    $spotDataImage = null;
                }
            }

            if ($spotDataImage !== null && is_array($spotDataImage)) {
                // Store spot_data image object by identifier (fileId)
                // This is the final format that will be saved to spot_data['image'] in database
                $this->imageEditorData[$identifier] = $spotDataImage;
                $this->imageEditorUsed = true; // Mark that image editor was used

                // Identifier'ın existing file olup olmadığını kontrol et
                $isExistingFile = false;
                foreach ($this->existingFiles as $existingFile) {
                    if (isset($existingFile['file_id']) && (string) $existingFile['file_id'] === (string) $identifier) {
                        $isExistingFile = true;
                        break;
                    }
                }

                // Sadece yeni dosyalar için uploadedFiles'a ekle (existing files için ekleme)
                // Existing files için sadece spot_data'yı imageEditorData'da sakla
                if (! $isExistingFile && ! isset($this->uploadedFiles[$identifier])) {
                    // Yeni dosya için uploadedFiles entry oluştur
                    $this->uploadedFiles[$identifier] = [];
                    LogHelper::info('PostEditGallery updateFilePreview - Created uploadedFiles entry for new file', [
                        'identifier' => $identifier,
                    ]);
                }

                // Eğer uploadedFiles entry varsa (yeni dosya için), spot_data'yı oraya da kaydet
                if (isset($this->uploadedFiles[$identifier]) && is_array($this->uploadedFiles[$identifier])) {
                    $this->uploadedFiles[$identifier]['spot_data'] = $spotDataImage;
                }

                LogHelper::info('PostCreateGallery updateFilePreview - Stored spot_data image object', [
                    'identifier' => $identifier,
                    'has_uploadedFiles_entry' => isset($this->uploadedFiles[$identifier]),
                    'has_original' => isset($spotDataImage['original']),
                    'has_variants' => isset($spotDataImage['variants']),
                    'has_effects' => isset($spotDataImage['effects']),
                    'has_textObjects' => isset($spotDataImage['textObjects']),
                    'textObjects_count' => isset($spotDataImage['textObjects']) ? count($spotDataImage['textObjects']) : 0,
                ]);
            } else {
                LogHelper::warning('PostCreateGallery updateFilePreview - spotDataImage is null or not array', [
                    'identifier' => $identifier,
                    'spotDataImage_type' => gettype($spotDataImage),
                ]);
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
                    // Persist file_path for robust mapping back after save
                    try {
                        $this->uploadedFiles[$identifier]['file_path'] = ltrim(preg_replace('#^/storage/#', '', $relativePath), '/');
                    } catch (\Exception $e) {
                    }
                } elseif (str_starts_with($imageUrl, 'http')) {
                    // For full URLs, download the content
                    $imageContent = @file_get_contents($imageUrl);
                    if ($imageContent === false) {
                        throw new \Exception('Could not download image from URL');
                    }

                    // Create temporary file in Livewire's temp directory
                    $tempDir = sys_get_temp_dir();
                    // Check if 'file' key exists (for archive files, it might not exist)
                    if (! isset($this->uploadedFiles[$identifier]['file'])) {
                        LogHelper::warning('PostCreateGallery updateFilePreview - file key not found in uploadedFiles', [
                            'identifier' => $identifier,
                            'uploadedFiles_keys' => array_keys($this->uploadedFiles[$identifier] ?? []),
                        ]);

                        return; // Skip file update for archive files
                    }
                    $oldFile = $this->uploadedFiles[$identifier]['file'];
                    $tempFileName = 'livewire-'.uniqid().'-'.$oldFile->getClientOriginalName();
                    $tempFilePath = $tempDir.'/'.$tempFileName;
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
                    // Persist file_path meta if we can derive from imageUrl
                    try {
                        $pathNoDomain = preg_replace('#^https?://[^/]+#', '', $imageUrl);
                        $pathNoStorage = preg_replace('#^/storage/#', '', $pathNoDomain);
                        $this->uploadedFiles[$identifier]['file_path'] = ltrim($pathNoStorage, '/');
                    } catch (\Exception $e) {
                    }

                    $this->dispatch('image-updated', [
                        'file_id' => $identifier,
                        'image_url' => $imageUrl,
                    ]);

                    return;
                } else {
                    $filePath = $imageUrl;
                    // Persist file_path meta for robust mapping
                    try {
                        $pathNoDomain = preg_replace('#^https?://[^/]+#', '', $imageUrl);
                        $pathNoStorage = preg_replace('#^/storage/#', '', $pathNoDomain);
                        $this->uploadedFiles[$identifier]['file_path'] = ltrim($pathNoStorage, '/');
                    } catch (\Exception $e) {
                    }
                }

                // If we have a local file path
                if (file_exists($filePath)) {
                    // Check if 'file' key exists (for archive files, it might not exist)
                    if (! isset($this->uploadedFiles[$identifier]['file'])) {
                        LogHelper::warning('PostCreateGallery updateFilePreview - file key not found in uploadedFiles (local path)', [
                            'identifier' => $identifier,
                            'uploadedFiles_keys' => array_keys($this->uploadedFiles[$identifier] ?? []),
                        ]);

                        return; // Skip file update for archive files
                    }
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
        } elseif (! is_string($value)) {
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

        // Try existingFiles array (for edit mode)
        foreach ($this->existingFiles as $index => $file) {
            if (isset($file['file_id']) && (string) $file['file_id'] === $fileIdStr) {
                $oldValue = $file[$field] ?? '';
                $this->existingFiles[$index][$field] = $value;

                return;
            }
        }

        // If not found, log debug info
        LogHelper::warning('File not found in updateFileById', [
            'fileId' => $fileId,
            'fileIdStr' => $fileIdStr,
            'field' => $field,
            'has_uploadedFiles' => ! empty($this->uploadedFiles),
            'has_existingFiles' => ! empty($this->existingFiles),
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
            // Edit sayfasında existingFiles kullanılıyor, create sayfasında uploadedFiles
            // Önce existingFiles ile dene (edit için)
            $useExistingFiles = ! empty($this->existingFiles);

            if ($useExistingFiles) {
                // Validation - existingFiles için
                if (! $this->postsService->validateOrder($order, $this->existingFiles, true)) {
                    LogHelper::warning('Geçersiz sıralama verisi alındı (existingFiles)', [
                        'order' => $order,
                    ]);

                    $this->dispatch('order-update-failed', [
                        'message' => 'Geçersiz sıralama verisi. Lütfen sayfayı yenileyip tekrar deneyin.',
                    ]);

                    return;
                }

                // Mevcut sıralamayı al (string'e normalize et - JavaScript'ten gelen değerler string olabilir)
                $currentOrder = array_map('strval', array_column($this->existingFiles, 'file_id'));
                $order = array_map('strval', $order);

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
            } else {
                // Create sayfası için uploadedFiles kullan
                // Validation
                if (! $this->postsService->validateOrder($order, $this->uploadedFiles, false)) {
                    LogHelper::warning('Geçersiz sıralama verisi alındı (uploadedFiles)', [
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
            }

            // Başarı mesajı - Sessizce çalış, alert gösterme
            $this->dispatch('order-updated');
        } catch (\Exception $e) {
            LogHelper::error('Galeri sıralaması güncellenirken hata oluştu', [
                'post_id' => $this->post->post_id ?? null,
                'order' => $order,
                'error' => $e->getMessage(),
            ]);

            // Kullanıcıya hata göster
            $this->dispatch('order-update-failed', [
                'message' => 'Sıralama güncellenirken bir hata oluştu: '.$e->getMessage(),
            ]);
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

            return false;
        }
    }

    public function render()
    {
        // Sadece gallery kategorilerini getir
        $categories = $this->categoryService->getQuery()
            ->where('status', 'active')
            ->where('type', 'gallery')
            ->orderBy('name')
            ->get();

        $postPositions = PostPosition::all();
        $postStatuses = PostStatus::all();

        /** @var view-string $view */
        $view = 'posts::livewire.post-edit-gallery';

        return view($view, compact('categories', 'postPositions', 'postStatuses'));
    }
}
