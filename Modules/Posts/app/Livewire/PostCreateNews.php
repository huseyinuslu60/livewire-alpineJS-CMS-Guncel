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

    /** @var array<int> */
    public array $categoryIds = [];

    public string $tagsInput = '';

    public string $successMessage = '';

    public int $primaryFileIndex = 0;

    public bool $isSaving = false;

    protected $listeners = ['contentUpdated'];

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

    public function updateFilePreview($identifier, $imageUrl, $tempPath = null)
    {
        // identifier index (integer) olabilir
        if (is_numeric($identifier)) {
            $index = (int) $identifier;
            if (isset($this->files[$index])) {
                // Backend'den dönen image'ı yeni bir temporary file olarak oluştur
                // Image URL'den blob'u al ve yeni temporary file oluştur
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
                    \Log::error('Error updating file preview: '.$e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
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
            $this->validate();

            $tagIds = array_filter(array_map('trim', explode(',', $this->tagsInput)));

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

            $postsService = new PostsService;
            $post = $postsService->create(
                $formData,
                $this->files,
                $this->categoryIds,
                $tagIds
            );

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
