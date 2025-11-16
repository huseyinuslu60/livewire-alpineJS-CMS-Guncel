<?php

namespace Modules\Posts\Livewire;

use App\Traits\ValidationMessages;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\AgencyNews\Models\AgencyNews;
use Modules\Categories\Models\Category;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

class PostCreateNews extends Component
{
    use ValidationMessages, WithFileUploads;

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
        if (empty($this->files) || !is_array($this->files)) {
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
                            $tempFileName = 'livewire-edited-' . uniqid() . '-' . $file->getClientOriginalName();
                            $tempFilePath = $tempDir . '/' . $tempFileName;
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

    protected $listeners = ['contentUpdated'];

    /**
     * Hydrate component - called when component is loaded
     */
    public function hydrate()
    {
        // Ensure files array is properly initialized
        if (!is_array($this->files)) {
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
            $agencyNews = AgencyNews::find($agency);
            if ($agencyNews) {
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
            'post_type' => 'required|in:'.implode(',', Post::TYPES),
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
        // Skip render to avoid checksum issues
        $this->skipRender();
        $this->content = $content ?? '';
    }

    /**
     * Handle content updates from Trumbowyg editor
     */
    public function updatedContent($value)
    {
        // Ensure content is always a string
        $this->content = (string) ($value ?? '');
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
     */
    public function updateFilePreview($identifier, $imageUrl, $tempPath = null)
    {
        // Skip render to avoid checksum issues
        $this->skipRender();

        // Store edited file path temporarily (not the UploadedFile object)
        if (is_numeric($identifier)) {
            $index = (int) $identifier;
            // Store the edited path - we'll use this during save
            $this->editedFilePaths[$index] = $imageUrl;
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

        // Log before save for debugging
        \Log::info('PostCreateNews savePost called:', [
            'title' => $this->title,
            'content_length' => strlen($this->content ?? ''),
            'content_preview' => substr($this->content ?? '', 0, 100),
            'summary' => $this->summary,
            'files_count' => is_array($this->files) ? count($this->files) : 0,
            'categoryIds' => $this->categoryIds,
        ]);

        // Set saving flag and skip render to avoid checksum issues
        $this->isSaving = true;
        $this->skipRender();

        try {
            // Slug'ı mutlaka unique yap - validation'dan ÖNCE
            // Eğer slug boşsa veya unique değilse, yeni bir unique slug oluştur
            $postsService = $this->getPostsService();
            if (empty($this->slug)) {
                $this->slug = $postsService->makeUniqueSlug($this->title);
            } else {
                // Slug varsa ama unique değilse, unique yap
                if (Post::where('slug', $this->slug)->exists()) {
                    $this->slug = $postsService->makeUniqueSlug($this->title);
                }
            }

            // Validation'ı unique slug ile yap
            // Note: We validate without modifying $files to avoid serialization issues
            try {
                $this->validate();
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Log validation errors for debugging
                \Log::error('PostCreateNews Validation Errors:', [
                    'errors' => $e->errors(),
                    'content' => $this->content,
                    'content_length' => strlen($this->content ?? ''),
                    'title' => $this->title,
                    'summary' => $this->summary,
                    'files_count' => is_array($this->files) ? count($this->files) : 0,
                    'categoryIds' => $this->categoryIds,
                ]);
                throw $e;
            }

            $tagIds = array_filter(array_map('trim', explode(',', $this->tagsInput)));

            // Process edited files if any
            // WithFileUploads trait handles file serialization, so we can use $this->files directly
            $filesToSave = $this->processEditedFiles();

            // Log files processing for debugging
            \Log::info('PostCreateNews Files Processing:', [
                'original_files_count' => is_array($this->files) ? count($this->files) : 0,
                'processed_files_count' => count($filesToSave),
                'edited_file_paths' => $this->editedFilePaths,
                'files_type' => gettype($this->files),
                'files_is_array' => is_array($this->files),
            ]);

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

            $post = $postsService->create(
                $formData,
                $filesToSave,
                $this->categoryIds,
                $tagIds
            );

            // Build and save spot_data if we have image files
            if (!empty($this->files) && $post->primaryFile) {
                $spotData = [
                    'image' => [
                        'original' => [
                            'path' => $post->primaryFile->file_path,
                            'width' => null,
                            'height' => null,
                            'hash' => null,
                        ],
                        'variants' => [
                            'desktop' => [
                                'crop' => [],
                                'focus' => 'center',
                            ],
                            'mobile' => [
                                'crop' => [],
                                'focus' => 'center',
                            ],
                        ],
                        'effects' => [],
                        'meta' => [
                            'alt' => $post->primaryFile->alt_text ?? null,
                            'credit' => null,
                            'source' => null,
                        ],
                    ],
                ];
                $post->spot_data = $spotData;
                $post->save();
            }

            $this->dispatch('post-created');

            // Clear files array before redirect to avoid serialization issues
            $this->files = [];

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
        // Sadece news kategorilerini getir
        $categories = Category::where('status', 'active')
            ->where('type', 'news')
            ->orderBy('name')
            ->get();

        $postPositions = Post::POSITIONS;
        $postStatuses = Post::STATUSES;

        /** @var view-string $view */
        $view = 'posts::livewire.post-create-news';

        return view($view, compact('categories', 'postPositions', 'postStatuses'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
