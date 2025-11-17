<?php

namespace Modules\Posts\Livewire;

use App\Helpers\LogHelper;
use App\Traits\ValidationMessages;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Categories\Models\Category;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

class PostCreateVideo extends Component
{
    use ValidationMessages, WithFileUploads;

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

    public string $embed_code = '';

    public bool $in_newsletter = false;

    public bool $no_ads = false;

    /** @var array<int, \Illuminate\Http\UploadedFile> */
    public array $files = [];

    /** @var array<int> */
    public array $categoryIds = [];

    public string $tagsInput = '';

    public string $successMessage = '';

    public bool $isSaving = false;

    /**
     * Image editor data storage
     * Key: file index, Value: array with crop, effects, meta data
     */
    protected array $imageEditorData = [];

    /**
     * Flag to track if image editor was used (to avoid saving empty spot_data)
     */
    protected bool $imageEditorUsed = false;

    protected PostsService $postsService;

    public function boot()
    {
        $this->postsService = app(PostsService::class);
    }

    protected $listeners = ['contentUpdated'];

    protected function messages()
    {
        return $this->getContextualValidationMessages()['post'] ?? $this->getValidationMessages();
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
            'content' => 'required|string',
            'post_position' => 'required|in:'.implode(',', Post::POSITIONS),
            'status' => 'required|in:'.implode(',', Post::STATUSES),
            'published_date' => 'nullable|date',
            'is_comment' => 'boolean',
            'is_mainpage' => 'boolean',
            'redirect_url' => 'nullable|url',
            'is_photo' => 'boolean',
            'agency_name' => 'nullable|string|max:255',
            'agency_id' => 'nullable|integer',
            'embed_code' => 'required|string',
            'in_newsletter' => 'boolean',
            'no_ads' => 'boolean',
            'categoryIds' => 'required|array|min:1',
            'categoryIds.*' => 'exists:categories,category_id',
            'tagsInput' => 'nullable|string',
            'files.*' => 'nullable|image|max:4096',
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

    public function removeFile($index)
    {
        if (isset($this->files[$index])) {
            unset($this->files[$index]);
            $this->files = array_values($this->files); // Re-index array
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
                    LogHelper::warning('PostCreateVideo updateFilePreview - Failed to decode JSON editorData', [
                        'json_error' => json_last_error_msg(),
                    ]);
                    $editorData = null;
                }
            }

            if ($editorData !== null && is_array($editorData)) {
                LogHelper::info('PostCreateVideo updateFilePreview - editorData received:', [
                    'identifier' => $identifier,
                    'textObjects_count' => isset($editorData['textObjects']) ? count($editorData['textObjects']) : 0,
                    'editorData_keys' => array_keys($editorData),
                ]);
                // Store editorData by identifier (index)
                if (is_numeric($identifier)) {
                    $this->imageEditorData[(int) $identifier] = $editorData;
                } else {
                    $this->imageEditorData[$identifier] = $editorData;
                }
                $this->imageEditorUsed = true; // Mark that image editor was used
            }
        }

        // identifier index (integer) olabilir
        if (is_numeric($identifier)) {
            $index = (int) $identifier;
            if (isset($this->files[$index])) {
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
                        $tempFileName = 'livewire-' . uniqid() . '-' . $this->files[$index]->getClientOriginalName();
                        $tempFilePath = $tempDir . '/' . $tempFileName;
                        file_put_contents($tempFilePath, $imageContent);

                        // Get original file info
                        $originalName = $this->files[$index]->getClientOriginalName();
                        $mimeType = $this->files[$index]->getMimeType() ?: 'image/jpeg';

                        // Create new UploadedFile
                        $newFile = new \Illuminate\Http\UploadedFile(
                            $tempFilePath,
                            $originalName,
                            $mimeType,
                            null,
                            true // test mode
                        );

                        // Replace the file in the array
                        $this->files[$index] = $newFile;

                        $this->dispatch('image-updated', [
                            'index' => $index,
                            'image_url' => $imageUrl,
                        ]);

                        return;
                    } else {
                        $filePath = $imageUrl;
                    }

                    // If we have a local file path
                    if (file_exists($filePath)) {
                        $originalName = $this->files[$index]->getClientOriginalName();
                        $mimeType = $this->files[$index]->getMimeType() ?: mime_content_type($filePath) ?: 'image/jpeg';

                        $newFile = new \Illuminate\Http\UploadedFile(
                            $filePath,
                            $originalName,
                            $mimeType,
                            null,
                            true
                        );

                        $this->files[$index] = $newFile;

                        $this->dispatch('image-updated', [
                            'index' => $index,
                            'image_url' => $imageUrl,
                        ]);
                    }
                } catch (\Exception $e) {
                    LogHelper::error('Dosya önizlemesi güncellenirken hata oluştu', [
                        'error' => $e->getMessage(),
                        'image_url' => $imageUrl,
                        'index' => $index,
                    ]);
                }
            }
        }
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
                $this->slug = $this->postsService->makeUniqueSlug($this->title);
            } else {
                // Slug varsa ama unique değilse, unique yap
                if (Post::where('slug', $this->slug)->exists()) {
                    $this->slug = $this->postsService->makeUniqueSlug($this->title);
                }
            }

            // Validation'ı unique slug ile yap
            $this->validate();

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

            $formData = [
                'title' => $this->title,
                'slug' => $this->slug,
                'summary' => $this->summary,
                'content' => $this->content,
                'post_type' => 'video',
                'post_position' => $this->post_position,
                'status' => $this->status,
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

            $post = $this->postsService->create(
                $formData,
                $this->files,
                $this->categoryIds,
                $tagIds
            );

            // Build and save spot_data with image data only
            // Only save spot_data if image editor was actually used
            $spotData = [];

            // Add image data if we have image files AND image editor was used
            if ($this->imageEditorUsed && !empty($this->files) && $post->primaryFile) {
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
                $primaryFileIndex = 0; // Video'da genelde tek dosya var, index 0
                $editorData = $this->imageEditorData[$primaryFileIndex] ?? null;

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

            $this->dispatch('post-created');

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('created', 'title', 'post'));

            return redirect()->route('posts.index');
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
        // Sadece video kategorilerini getir
        $categories = Category::where('status', 'active')
            ->where('type', 'video')
            ->orderBy('name')
            ->get();

        $postPositions = Post::POSITIONS;
        $postStatuses = Post::STATUSES;

        /** @var view-string $view */
        $view = 'posts::livewire.post-create-video';

        return view($view, compact('categories', 'postPositions', 'postStatuses'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
