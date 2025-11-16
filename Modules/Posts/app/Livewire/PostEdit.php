<?php

namespace Modules\Posts\Livewire;

use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Sanitizer;
use App\Traits\HandlesExceptionsWithToast;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Modules\Headline\Services\FeaturedService;
use Modules\Posts\Enums\PostPosition;
use Modules\Posts\Enums\PostStatus;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

class PostEdit extends Component
{
    use InteractsWithToast, HandlesExceptionsWithToast, ValidationMessages;

    public Post $post;

    protected PostsService $postsService;

    protected FeaturedService $featuredService;

    protected $listeners = [
        'metaDataReady',
        'metaSidebarDataReady',
        'contentDataReady',
        'mediaDataReady',
        'relationsDataReady',
        'validationFailed',
        'postTypeChanged',
    ];

    public function boot(PostsService $postsService, FeaturedService $featuredService)
    {
        $this->postsService = $postsService;
        $this->featuredService = $featuredService;
    }

    public function mount($post)
    {
        Gate::authorize('edit posts');

        if (is_string($post) || is_numeric($post)) {
            $postId = $post;
        } else {
            $postId = $post->post_id;
        }

        $this->post = Post::findOrFail($postId)->fresh();
    }

    public function updatePost()
    {
        Gate::authorize('edit posts');

        // Child component'lerden data topla
        $this->dispatch('collectData');

        // Validation'ı child component'lerde yapılacak, burada sadece koordine et
        // Eğer validation başarısız olursa, child component'ler 'validationFailed' event'i gönderecek
    }

    public function metaDataReady($data)
    {
        $this->metaData = $data;
        $this->checkAllDataReady();
    }

    public function metaSidebarDataReady($data)
    {
        // Sidebar meta data'yı ana meta data ile birleştir
        if ($this->metaData) {
            $this->metaData = array_merge($this->metaData, $data);
        } else {
            $this->metaData = $data;
        }
        $this->checkAllDataReady();
    }

    public function contentDataReady($data)
    {
        $this->contentData = $data;
        $this->checkAllDataReady();
    }

    public function mediaDataReady($data)
    {
        $this->mediaData = $data;
        $this->checkAllDataReady();
    }

    public function relationsDataReady($data)
    {
        $this->relationsData = $data;
        $this->checkAllDataReady();
    }

    private $metaData = null;
    private $contentData = null;
    private $mediaData = null;
    private $relationsData = null;

    private function checkAllDataReady()
    {
        // Meta data hem main hem sidebar'dan gelmeli
        if ($this->metaData && isset($this->metaData['status']) && $this->contentData && $this->mediaData && $this->relationsData) {
            $this->performUpdate();
        }
    }

    private function performUpdate()
    {
        try {
            // Meta data'dan form data'yı oluştur
            $formData = array_merge($this->metaData, $this->contentData);

            // Tags'i parse et
            $tagIds = array_filter(array_map('trim', explode(',', $this->relationsData['tagsInput'] ?? '')));

            // Media data'dan files ve descriptions'ı al
            $newFiles = $this->mediaData['newFiles'] ?? [];
            $fileDescriptions = $this->mediaData['fileDescriptions'] ?? [];

            // Gallery için content'i güncelle
            if (($this->metaData['post_type'] ?? '') === 'gallery') {
                $formData['content'] = $this->post->content ?? '';
            }

            // PostsService ile update
            $this->postsService->update(
                $this->post,
                $formData,
                $newFiles,
                $this->relationsData['categoryIds'] ?? [],
                $tagIds,
                $fileDescriptions
            );

            // Gallery için content'i güncelle (açıklamalar dahil)
            if (($this->metaData['post_type'] ?? '') === 'gallery') {
                $this->post->refresh();
                if (! empty($newFiles)) {
                    $postFiles = $this->post->files()->orderBy('created_at', 'desc')->take(count($newFiles))->get();
                    $existingFiles = $this->mediaData['existingFiles'] ?? [];

                    foreach ($existingFiles as $index => &$file) {
                        if (($file['is_new'] ?? false) || (isset($file['file_id']) && strpos($file['file_id'], 'new_') === 0)) {
                            $originalName = $file['original_name'];
                            $description = $file['description'] ?? '';

                            foreach ($postFiles as $postFile) {
                                if ($postFile->title === $originalName) {
                                    $file['path'] = $postFile->file_path;
                                    $file['file_path'] = $postFile->file_path;
                                    $file['description'] = $description;
                                    unset($file['is_new']);
                                    break;
                                }
                            }
                        }
                    }
                    unset($file);

                    // MediaManager'a gallery content'i güncellemesi için sinyal gönder
                    $this->dispatch('updateGalleryContent', [
                        'existingFiles' => $existingFiles,
                        'primaryFileId' => $this->mediaData['primaryFileId'] ?? null,
                    ]);
                }
            }

            // Otomatik vitrin ekleme
            $shouldAddToFeatured = false;
            $zone = null;

            if (in_array($this->metaData['post_position'] ?? '', [
                PostPosition::Manset->value,
                PostPosition::Surmanset->value,
                PostPosition::OneCikanlar->value,
            ])) {
                $shouldAddToFeatured = true;
                $zone = PostPosition::toZone($this->metaData['post_position']);
            }

            if ($shouldAddToFeatured && $zone) {
                $startsAt = ! empty($this->metaData['featured_starts_at'])
                    ? \Carbon\Carbon::parse($this->metaData['featured_starts_at'])
                    : null;
                $endsAt = ! empty($this->metaData['featured_ends_at'])
                    ? \Carbon\Carbon::parse($this->metaData['featured_ends_at'])
                    : null;

                $this->featuredService->upsert(
                    $zone,
                    'post',
                    $this->post->post_id,
                    null,
                    null,
                    $startsAt,
                    $endsAt
                );
            }

            // News/Video için resim güncelleme
            if (in_array($this->metaData['post_type'] ?? '', ['news', 'video']) && ! empty($newFiles)) {
                $existingPrimaryFile = $this->post->primaryFile;

                if ($existingPrimaryFile) {
                    $newFile = collect($newFiles)->first();
                    $existingPrimaryFile->update([
                        'file_path' => $newFile->store('posts/'.date('Y/m'), 'public'),
                        'title' => Sanitizer::escape($newFile->getClientOriginalName()), // XSS koruması
                        'file_size' => $newFile->getSize(),
                        'mime_type' => $newFile->getMimeType(),
                    ]);
                } else {
                    $newFile = collect($newFiles)->first();
                    $this->post->files()->create([
                        'file_path' => $newFile->store('posts/'.date('Y/m'), 'public'),
                        'title' => Sanitizer::escape($newFile->getClientOriginalName()), // XSS koruması
                        'file_size' => $newFile->getSize(),
                        'mime_type' => $newFile->getMimeType(),
                    ]);
                }
            }

            $this->post->refresh();

            // Child component'lere post güncellendiğini bildir
            $this->dispatch('postUpdated');

            // Success mesajı
            $successMessage = $this->createContextualSuccessMessage('updated', 'title', 'post');
            if ($shouldAddToFeatured) {
                $successMessage .= " ve {$this->metaData['post_position']} alanına otomatik eklendi.";
            }
            $this->toastSuccess($successMessage, 6000);

            return redirect()->route('posts.index');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Post güncellenirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'post_id' => $this->post->post_id,
            ]);
        }
    }

    public function validationFailed($data)
    {
        // Validation hatalarını göster
        if (isset($data['errors'])) {
            foreach ($data['errors'] as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }
        }
    }

    public function postTypeChanged($postType)
    {
        // Post type değiştiğinde child component'leri bilgilendir
        $this->dispatch('postTypeUpdated', $postType);
    }

    public function render()
    {
        return view('posts::livewire.post-edit')
            ->extends('layouts.admin')
            ->section('content');
    }
}
