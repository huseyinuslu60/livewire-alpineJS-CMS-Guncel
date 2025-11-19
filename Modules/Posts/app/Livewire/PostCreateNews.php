<?php

namespace Modules\Posts\Livewire;

use App\Helpers\LogHelper;
use App\Services\SlugGenerator;
use App\Services\ValueObjects\Slug;
use App\Traits\ValidationMessages;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\AgencyNews\Services\AgencyNewsService;
use Modules\Categories\Services\CategoryService;
use Modules\Posts\Domain\ValueObjects\PostPosition;
use Modules\Posts\Domain\ValueObjects\PostStatus;
use Modules\Posts\Domain\ValueObjects\PostType;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

/**
 * @property CategoryService $categoryService
 * @property AgencyNewsService $agencyNewsService
 */
class PostCreateNews extends Component
{
    use ValidationMessages, WithFileUploads;

    protected CategoryService $categoryService;

    protected AgencyNewsService $agencyNewsService;

    public function boot()
    {
        $this->categoryService = app(CategoryService::class);
        $this->agencyNewsService = app(AgencyNewsService::class);
    }

    public string $title = '';

    public string $slug = '';

    public string $summary = '';

    public string $content = '';

    public string $post_type = 'news'; // Fixed type

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
    public array $files = []; // Optional thumbnail

    /**
     * Temporary storage for edited file paths (to avoid serialization issues)
     * Key: file index, Value: edited image path
     */
    protected array $editedFilePaths = [];

    /**
     * Temporary storage for edited file URLs (for display)
     * Key: file index, Value: edited image URL
     */
    public array $editedFileUrls = [];

    /**
     * Image editor data storage
     * Key: file index, Value: array with crop, effects, meta data
     */
    protected array $imageEditorData = [];

    /**
     * Flag to track if image editor was used (to avoid saving empty spot_data)
     * Must be public for Livewire serialization
     */
    public bool $imageEditorUsed = false;

    /** @var array<int> */
    public array $categoryIds = [];

    public string $tagsInput = '';

    public string $successMessage = '';

    public int $primaryFileIndex = 0;

    public bool $isSaving = false;

    /**
     * Get PostsService instance - don't store as property to avoid serialization issues
     */
    protected function getPostsService(): PostsService
    {
        return app(PostsService::class);
    }

    /**
     * Process edited files and return files array ready for saving
     * WithFileUploads trait handles file serialization, so we can use $this->files directly
     */
    protected function processEditedFiles(): array
    {
        // WithFileUploads trait handles file serialization automatically
        // We can use $this->files directly, but filter out non-UploadedFile objects
        if (empty($this->files) || ! is_array($this->files)) {
            return [];
        }

        $processedFiles = [];

        foreach ($this->files as $index => $file) {
            // Only process actual UploadedFile instances
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                // If this file was edited, we need to replace it with the edited version
                if (isset($this->editedFilePaths[$index])) {
                    $editedPath = $this->editedFilePaths[$index];

                    // Convert URL to file path if needed
                    $filePath = $editedPath;
                    if (str_starts_with($editedPath, asset(''))) {
                        $relativePath = str_replace(asset(''), '', $editedPath);
                        $filePath = public_path($relativePath);
                    } elseif (str_starts_with($editedPath, 'http')) {
                        // Download the edited image
                        $imageContent = @file_get_contents($editedPath);
                        if ($imageContent !== false) {
                            $tempDir = sys_get_temp_dir();
                            $tempFileName = 'livewire-edited-'.uniqid().'-'.$file->getClientOriginalName();
                            $tempFilePath = $tempDir.'/'.$tempFileName;
                            file_put_contents($tempFilePath, $imageContent);
                            $filePath = $tempFilePath;
                        } else {
                            // Fallback to original file
                            $processedFiles[] = $file;

                            continue;
                        }
                    }

                    // Create new UploadedFile from edited path
                    if (file_exists($filePath)) {
                        $newFile = new \Illuminate\Http\UploadedFile(
                            $filePath,
                            $file->getClientOriginalName(),
                            $file->getMimeType() ?: mime_content_type($filePath) ?: 'image/jpeg',
                            null,
                            true
                        );
                        $processedFiles[] = $newFile;
                    } else {
                        // Fallback to original file
                        $processedFiles[] = $file;
                    }
                } else {
                    // Use original file
                    $processedFiles[] = $file;
                }
            }
        }

        return $processedFiles;
    }

    protected $listeners = ['contentUpdated', 'filesSelectedForPost'];

    /**
     * Hydrate component - called when component is loaded
     */
    public function hydrate()
    {
        // Ensure files array is properly initialized
        if (! is_array($this->files ?? null)) {
            $this->files = [];
        }
    }

    /**
     * Dehydrate component - called before component state is serialized
     * This helps prevent serialization issues with UploadedFile objects
     */
    public function dehydrate()
    {
        // Files array will be handled by Livewire's WithFileUploads trait
        // We don't need to modify it here
    }

    protected function messages()
    {
        $messages = $this->getContextualValidationMessages()['post'] ?? $this->getValidationMessages();
        // Slug unique validation mesajını ekle
        $messages['slug.unique'] = 'Bu URL adresi zaten kullanılıyor.';
        // CategoryIds validation mesajlarını ekle
        $messages['categoryIds.required'] = 'En az bir kategori seçmelisiniz.';
        $messages['categoryIds.min'] = 'En az bir kategori seçmelisiniz.';
        $messages['categoryIds.*.exists'] = 'Seçilen kategori geçersiz.';

        return $messages;
    }

    public function mount($agency = null)
    {
        Gate::authorize('create posts');
        $this->published_date = Carbon::now()->format('Y-m-d H:i');

        // Eğer agency parametresi yoksa query string'den al
        if (! $agency) {
            $agency = request()->query('agency');
        }

        // Eğer agency parametresi varsa, ajans haberinden verileri yükle
        if ($agency) {
            $agencyNews = $this->agencyNewsService->findById($agency);
            if ($agencyNews !== null) {
                $this->title = $agencyNews->title ?? '';
                $this->summary = $agencyNews->summary ?? '';
                $this->content = $agencyNews->content ?? '';
                $this->agency_id = $agencyNews->agency_id;
                $this->agency_name = $agencyNews->getAgencyName();
                $this->is_photo = $agencyNews->has_image ?? false;

                // Slug'ı başlıktan oluştur
                if (! empty($this->title)) {
                    $this->updatedTitle($this->title);
                }

                // Tags varsa yükle
                if ($agencyNews->tags) {
                    $this->tagsInput = $agencyNews->tags;
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
            'content' => 'required|string',
            'post_type' => 'required|in:'.implode(',', PostType::all()),
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
            'files.*' => 'nullable|image|max:4096',
            'categoryIds' => 'required|array|min:1',
            'categoryIds.*' => 'exists:categories,category_id',
            'tagsInput' => 'nullable|string',
        ];
    }

    /**
     * Ensure tagsInput is always a string when updated
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
            $slug = $slugGenerator->generate($convertedTitle, Post::class, 'slug', 'post_id');
            $this->slug = $slug->toString();
        }
    }

    public function contentUpdated($content)
    {
        // Skip render to avoid checksum issues
        $this->skipRender();
        // Ensure content is always a string
        $this->content = (string) ($content ?? '');
    }

    public function filesSelectedForPost($data)
    {
        $this->skipRender();

        if (! isset($data['files']) || ! is_array($data['files']) || empty($data['files'])) {
            return;
        }

        // İlk dosyayı al (multiple: false olduğu için)
        $selectedFile = $data['files'][0];

        if (! isset($selectedFile['url'])) {
            return;
        }

        try {
            // Dosyayı URL'den indir
            $imageUrl = $selectedFile['url'];

            // Asset URL'ini path'e çevir
            $filePath = null;
            if (str_starts_with($imageUrl, asset(''))) {
                $relativePath = str_replace(asset(''), '', $imageUrl);
                $filePath = public_path($relativePath);
            } elseif (str_starts_with($imageUrl, 'http')) {
                // Full URL ise indir
                $imageContent = @file_get_contents($imageUrl);
                if ($imageContent === false) {
                    throw new \Exception('Dosya indirilemedi');
                }

                $tempDir = sys_get_temp_dir();
                $fileName = $selectedFile['title'] ?? 'archive-'.uniqid().'.jpg';
                $tempFilePath = $tempDir.'/'.'livewire-archive-'.uniqid().'-'.$fileName;
                file_put_contents($tempFilePath, $imageContent);
                $filePath = $tempFilePath;
            } else {
                $filePath = public_path('storage/'.$imageUrl);
            }

            if (! file_exists($filePath)) {
                throw new \Exception('Dosya bulunamadı');
            }

            // MIME type'ı belirle
            $mimeType = mime_content_type($filePath) ?: 'image/jpeg';
            $originalName = $selectedFile['title'] ?? basename($filePath);

            // UploadedFile oluştur
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $filePath,
                $originalName,
                $mimeType,
                null,
                true // test mode
            );

            // Files array'ine ekle (mevcut dosyaları temizle)
            $this->files = [$uploadedFile];

            session()->flash('success', 'Dosya arşivden seçildi!');
        } catch (\Exception $e) {
            \App\Helpers\LogHelper::error('Arşivden dosya seçilirken hata', [
                'error' => $e->getMessage(),
                'file' => $selectedFile,
            ]);
            session()->flash('error', 'Dosya seçilirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function removeFile($index)
    {
        if (isset($this->files[$index])) {
            $this->skipRender();
            unset($this->files[$index]);
            $this->files = array_values($this->files); // Re-index array
        }
    }

    /**
     * Update file preview after image editing
     * Note: We don't replace the UploadedFile object to avoid serialization issues.
     * Instead, we store the edited image path temporarily and use it during save.
     *
     * @param  string|int  $identifier  File index or file_id
     * @param  string  $imageUrl  Edited image URL
     * @param  string|null  $tempPath  Temporary file path
     * @param  array|string|null  $editorData  Image editor data (crop, effects, meta) - can be array or JSON string
     */
    public function updateFilePreview($identifier, $imageUrl, $tempPath = null, $editorData = null)
    {
        // Skip render to avoid checksum issues
        $this->skipRender();

        // Store edited file path temporarily (not the UploadedFile object)
        if (is_numeric($identifier)) {
            $index = (int) $identifier;
            // Store the edited path - we'll use this during save
            $this->editedFilePaths[$index] = $imageUrl;
            // Store the edited URL for display
            $this->editedFileUrls[$index] = $imageUrl;

            // Store image editor data if provided
            if ($editorData !== null) {
                // If editorData is a JSON string, decode it
                if (is_string($editorData)) {
                    $decoded = json_decode($editorData, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $editorData = $decoded;
                    } else {
                        LogHelper::warning('PostCreateNews updateFilePreview - Failed to decode JSON editorData', [
                            'json_error' => json_last_error_msg(),
                            'index' => $index,
                        ]);
                        $editorData = null;
                    }
                }

                if ($editorData !== null && is_array($editorData)) {
                    $this->imageEditorData[$index] = $editorData;
                    $this->imageEditorUsed = true; // Mark that image editor was used

                    LogHelper::info('PostCreateNews updateFilePreview - Stored editor data', [
                        'index' => $index,
                        'has_textObjects' => isset($editorData['textObjects']) && ! empty($editorData['textObjects']),
                        'textObjects_count' => isset($editorData['textObjects']) ? count($editorData['textObjects']) : 0,
                        'has_effects' => isset($editorData['effects']),
                        'has_crop' => isset($editorData['crop']),
                        'has_canvas' => isset($editorData['canvas']),
                    ]);
                }
            } else {
                LogHelper::warning('PostCreateNews updateFilePreview - editorData is null', [
                    'index' => $index,
                ]);
            }
        }

        $this->dispatch('image-updated', [
            'identifier' => $identifier,
            'image_url' => $imageUrl,
        ]);
    }

    public function savePost()
    {
        // Duplicate submit'i engelle
        if ($this->isSaving) {
            return;
        }

        Gate::authorize('create posts');

        // Set saving flag and skip render to avoid checksum issues
        $this->isSaving = true;
        $this->skipRender();

        try {
            // Slug'ı mutlaka unique yap - validation'dan ÖNCE
            // Eğer slug boşsa veya unique değilse, yeni bir unique slug oluştur
            $slugGenerator = app(SlugGenerator::class);
            if (empty($this->slug)) {
                $slug = $slugGenerator->generate($this->title, Post::class, 'slug', 'post_id');
                $this->slug = $slug->toString();
            } else {
                // Slug varsa ama unique değilse, unique yap
                $slug = Slug::fromString($this->slug);
                if (! $slugGenerator->isUnique($slug, Post::class, 'slug', 'post_id')) {
                    $slug = $slugGenerator->generate($this->title, Post::class, 'slug', 'post_id');
                    $this->slug = $slug->toString();
                }
            }

            // Validation'ı unique slug ile yap
            // Note: We validate without modifying $files to avoid serialization issues
            try {
                $this->validate();
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Log validation errors for debugging
                LogHelper::error('PostCreateNews Validation Errors', [
                    'errors' => $e->errors(),
                ]);
                throw $e;
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

            if (strlen($this->content) > 100000) {
                $this->addError('content', 'İçerik çok uzun (maksimum 100.000 karakter).');
                $this->isSaving = false;

                return;
            }

            $tagIds = array_filter(array_map('trim', explode(',', $this->tagsInput)));

            // Process edited files if any
            // WithFileUploads trait handles file serialization, so we can use $this->files directly
            $filesToSave = $this->processEditedFiles();

            $formData = [
                'title' => $this->title,
                'slug' => $this->slug,
                'summary' => $this->summary,
                'content' => $this->content,
                'post_type' => $this->post_type,
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
            ];

            $post = $this->getPostsService()->create(
                $formData,
                $filesToSave,
                $this->categoryIds,
                $tagIds
            );

            // Build and save spot_data with image data only
            // Only save spot_data if image editor was actually used
            $spotData = [];

            // Add image data if we have image files AND image editor was used
            if ($this->imageEditorUsed && ! empty($this->files) && $post->primaryFile) {
                // Get image dimensions and hash
                $imagePath = public_path('storage/'.$post->primaryFile->file_path);
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
                $primaryFileIndex = $this->primaryFileIndex;

                // Debug: Log available editor data
                LogHelper::info('PostCreateNews savePost - Getting editor data', [
                    'primaryFileIndex' => $primaryFileIndex,
                    'imageEditorData_keys' => array_keys($this->imageEditorData),
                    'imageEditorData_count' => count($this->imageEditorData),
                ]);

                // Try to get editor data from primaryFileIndex, or try all indices
                $editorData = $this->imageEditorData[$primaryFileIndex] ?? null;

                // If not found at primaryFileIndex, try to find it in any index
                if ($editorData === null && ! empty($this->imageEditorData)) {
                    // Get first available editor data
                    $editorData = reset($this->imageEditorData);
                    LogHelper::warning('PostCreateNews savePost - editorData not found at primaryFileIndex, using first available', [
                        'primaryFileIndex' => $primaryFileIndex,
                        'used_index' => key($this->imageEditorData),
                    ]);
                }

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
                }

                // Extract text objects (textObjects will be added to spot_data separately)
                $textObjects = [];
                if ($editorData !== null && isset($editorData['textObjects']) && is_array($editorData['textObjects'])) {
                    $textObjects = $editorData['textObjects'];
                } else {
                    LogHelper::warning('PostCreateNews savePost - textObjects not found in editorData');
                }

                // Extract canvas dimensions for scaling textObjects on reload
                $canvasDimensions = ['width' => 0, 'height' => 0];
                if ($editorData !== null && isset($editorData['canvas']) && is_array($editorData['canvas'])) {
                    $canvasDimensions = [
                        'width' => isset($editorData['canvas']['width']) ? (int) $editorData['canvas']['width'] : 0,
                        'height' => isset($editorData['canvas']['height']) ? (int) $editorData['canvas']['height'] : 0,
                    ];
                }

                // Arrays are already initialized above, no need to check again

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
                    'canvas' => $canvasDimensions,
                ];
            }

            // Only save spot_data if image editor was used AND we have actual data
            if ($this->imageEditorUsed && isset($spotData['image'])) {
                LogHelper::info('PostCreateNews savePost - Saving spot_data', [
                    'has_spot_data' => isset($spotData['image']),
                    'has_image' => true,
                    'has_textObjects' => ! empty(($spotData['image']['textObjects'] ?? [])),
                    'textObjects_count' => count(($spotData['image']['textObjects'] ?? [])),
                    'has_effects' => ! empty(($spotData['image']['effects'] ?? [])),
                    'primaryFileIndex' => $this->primaryFileIndex,
                    'imageEditorData_keys' => array_keys($this->imageEditorData),
                ]);
                $post->spot_data = $spotData;
                $post->save();
            } else {
                if ($this->imageEditorUsed) {
                    LogHelper::warning('PostCreateNews savePost - imageEditorUsed is true but spot_data is empty', [
                        'has_spot_data' => ! empty($spotData),
                        'has_image' => isset($spotData['image']),
                        'has_files' => ! empty($this->files),
                        'has_primaryFile' => $post->primaryFile !== null,
                        'imageEditorData_count' => count($this->imageEditorData),
                        'primaryFileIndex' => $this->primaryFileIndex,
                    ]);
                } else {
                    LogHelper::warning('PostCreateNews savePost - imageEditorUsed is false, not saving spot_data', [
                        'has_files' => ! empty($this->files),
                        'has_primaryFile' => $post->primaryFile !== null,
                        'imageEditorData_count' => count($this->imageEditorData),
                    ]);
                }
            }

            $this->dispatch('post-created');

            // Clear files array before redirect to avoid serialization issues
            $this->files = [];

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('created', 'title', 'post'));

            return redirect()->route('posts.index');
        } catch (\InvalidArgumentException $e) {
            // Validation hataları - direkt mesaj göster
            $this->isSaving = false;
            $this->addError('general', $e->getMessage());
        } catch (\Exception $e) {
            // Hata durumunda flag'i temizle
            $this->isSaving = false;
            throw $e;
        } finally {
            $this->isSaving = false;
        }
    }

    public function render()
    {
        // Sadece news kategorilerini getir - cache ile optimize et
        $categories = \Illuminate\Support\Facades\Cache::remember('posts:categories:news', 300, function () {
            return $this->categoryService->getQuery()
                ->where('status', 'active')
                ->where('type', 'news')
                ->orderBy('name')
                ->get();
        });

        $postPositions = PostPosition::all();
        $postStatuses = PostStatus::all();

        /** @var view-string $view */
        $view = 'posts::livewire.post-create-news';

        return view($view, compact('categories', 'postPositions', 'postStatuses'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
