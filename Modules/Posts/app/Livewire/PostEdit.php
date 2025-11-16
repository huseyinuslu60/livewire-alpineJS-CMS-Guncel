<?php

namespace Modules\Posts\Livewire;

use App\Traits\SecureFileUpload;
use App\Traits\ValidationMessages;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Categories\Models\Category;
use Modules\Headline\Services\FeaturedService;
use Modules\Posts\Models\File;
use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostsService;

class PostEdit extends Component
{
    use SecureFileUpload, ValidationMessages, WithFileUploads;

    protected PostsService $postsService;

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

        // Model'i yenile ki güncel verileri alsın
        $this->post = Post::findOrFail($postId)->fresh();
        $this->postType = $this->post->post_type;

        // Debug: Post content kontrolü
        \Log::info('Mount method - Post content after fresh:', [
            'post_id' => $postId,
            'content' => $this->post->content,
        ]);

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
        $this->tagsInput = $this->post->tags && is_object($this->post->tags) ? $this->post->tags->pluck('name')->implode(', ') : '';

        // Mevcut dosyaları content'den yükle (gallery için)
        $this->existingFiles = [];
        if ($this->post->post_type === 'gallery') {
            // Content'i direkt database'den al (fresh ile güncel veri)
            $content = \DB::table('posts')->where('post_id', $postId)->value('content');
            $galleryData = json_decode($content, true) ?: [];

            // Debug: Gallery data kontrolü (sadece development'ta)
            if (config('app.debug')) {
                \Log::info('Loading gallery data:', [
                    'post_id' => $postId,
                    'gallery_data' => $galleryData,
                    'content' => $this->post->content,
                    'db_content' => $content,
                ]);
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

                    return [
                        'file_id' => (string) $fileId, // Kalıcı file_id kullan (string olarak tutuluyor)
                        'path' => $fileData['file_path'],
                        'original_name' => $fileData['filename'],
                        'description' => $fileData['description'] ?? '', // Boş string de olsa göster
                        'primary' => (bool) $fileData['is_primary'], // is_primary -> primary
                        'type' => $fileData['type'] ?? 'image/jpeg',
                        'order' => (int) $fileData['order'], // Order'ı koru
                        'uploaded_at' => $fileData['uploaded_at'] ?? now()->toISOString(), // uploaded_at ekle
                    ];
                })->toArray();

                // Debug: Description verilerini kontrol et
                \Log::info('Loaded existingFiles with descriptions:', array_map(function ($file) {
                    return [
                        'file_id' => $file['file_id'],
                        'description' => $file['description'],
                        'description_length' => strlen($file['description']),
                    ];
                }, $this->existingFiles));

                // Ana dosyayı bul - gallery_data'dan direkt index bul
                $this->primaryFileId = null; // Default
                foreach ($this->existingFiles as $index => $file) {
                    if ($file['primary'] === true) {
                        $this->primaryFileId = (string) $file['file_id'];
                        break;
                    }
                }

                // Debug için
                \Log::info('Primary file ID set to:', ['id' => $this->primaryFileId, 'galleryData' => $galleryData]);
                \Log::info('Existing files loaded:', ['count' => count($this->existingFiles)]);
            }
        }

        // Temiz dosya sistemi başlat
        $this->uploadedFiles = [];
    }

    protected function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,'.$this->post->post_id.',post_id',
            'summary' => 'required|string',
            'content' => 'nullable|string',
            'post_type' => 'required|in:'.implode(',', Post::TYPES),
            'post_position' => 'required|in:'.implode(',', Post::POSITIONS),
            'status' => 'nullable|in:'.implode(',', Post::STATUSES),
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
            $this->slug = Str::slug($convertedTitle);
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
        \Log::info('=== reorderExistingFiles CALLED ===', [
            'newOrder' => $newOrder,
            'existingFiles_before' => array_map(function ($f) {
                return [
                    'file_id' => $f['file_id'],
                    'description' => $f['description'] ?? 'N/A',
                    'primary' => $f['primary'],
                ];
            }, $this->existingFiles),
        ]);

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

        \Log::info('=== reorderExistingFiles COMPLETED ===', [
            'existingFiles_after' => array_map(function ($f) {
                return [
                    'file_id' => $f['file_id'],
                    'description' => $f['description'] ?? 'N/A',
                    'primary' => $f['primary'],
                    'order' => $f['order'],
                ];
            }, $this->existingFiles),
            'primaryFileId' => $this->primaryFileId,
        ]);

        // Kullanıcıya bilgi ver
        session()->flash('success', 'Sıralama güncellendi ve açıklamalar korundu.');
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

                // Debug için
                \Log::info('Refreshed existingFiles count:', ['count' => count($this->existingFiles)]);
            }
        }
    }

    public function contentUpdated($content)
    {
        $this->content = $content;
    }

    public function updateFileOrder($fromIndex, $toIndex)
    {
        \Log::info('=== updateFileOrder CALLED ===', [
            'fromIndex' => $fromIndex,
            'toIndex' => $toIndex,
            'existingFiles_count' => count($this->existingFiles),
            'uploadedFiles_count' => count($this->uploadedFiles),
        ]);

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

        // Debug için log ekle
        \Log::info('File reorder completed (create style):', [
            'fromIndex' => $fromIndex,
            'toIndex' => $toIndex,
            'movedId' => $movedId,
            'orderedIds' => $orderedIds,
            'existingFiles_after' => array_map(function ($f) {
                return [
                    'file_id' => $f['file_id'],
                    'path' => $f['path'],
                    'primary' => $f['primary'],
                    'order' => $f['order'],
                ];
            }, $this->existingFiles),
        ]);

        // Sıralama sonrası veritabanını güncelle
        $this->updateGalleryContent();

        // Kullanıcıya bilgi ver
        session()->flash('success', 'Sıralama güncellendi.');
    }

    // Sıralama bilgisini veritabanına kaydet
    private function saveFileOrderToDatabase()
    {
        \Log::info('=== saveFileOrderToDatabase CALLED ===', [
            'existingFiles_count' => count($this->existingFiles),
            'existingFiles' => array_map(function ($f) {
                return [
                    'file_id' => $f['file_id'],
                    'order' => $f['order'],
                    'is_numeric' => is_numeric($f['file_id']),
                ];
            }, $this->existingFiles),
        ]);

        if (empty($this->existingFiles)) {
            \Log::info('No existingFiles to update');

            return;
        }

        // existingFiles'da sadece string ID'ler var (existing_ prefix'li)
        // Bu dosyalar files tablosunda değil, posts.content JSON'ında
        // Sıralama zaten updateGalleryContent() ile posts.content'e kaydediliyor
        \Log::info('File order will be saved via updateGalleryContent() to posts.content JSON');

        // Gerçek files tablosundaki dosyalar varsa onları güncelle
        $realFiles = array_filter($this->existingFiles, function ($file) {
            return is_numeric($file['file_id']);
        });

        if (! empty($realFiles)) {
            foreach ($realFiles as $file) {
                \DB::table('files')
                    ->where('file_id', $file['file_id'])
                    ->update(['order' => $file['order']]);

                \Log::info('Updated real file order in database:', [
                    'file_id' => $file['file_id'],
                    'order' => $file['order'],
                ]);
            }
        }

        \Log::info('File order processing completed:', [
            'total_files' => count($this->existingFiles),
            'real_files' => count($realFiles),
            'string_files' => count($this->existingFiles) - count($realFiles),
        ]);
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
            \Log::info('New files uploaded:', ['count' => count($this->newFiles)]);

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
                    \Log::info('File added to existingFiles (gallery):', ['filename' => $fileData['original_name']]);
                } else {
                    // Haber/Video türü için uploadedFiles'a ekle
                    $fileId = 'file_'.time().'_'.rand(1000, 9999);
                    $this->uploadedFiles[$fileId] = [
                        'file' => $file,
                        'description' => '',
                    ];
                    \Log::info('File added to uploadedFiles (news/video):', ['fileId' => $fileId, 'filename' => $fileData['original_name']]);
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
        if (isset($this->uploadedFiles[$fileId])) {
            unset($this->uploadedFiles[$fileId]);

            // Eğer silinen dosya ana görsel ise, ana görsel seçimini sıfırla
            if ((string) $this->primaryFileId === (string) $fileId) {
                $this->primaryFileId = null;
            }
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
        if (isset($this->uploadedFiles[$fileId])) {
            $this->uploadedFiles[$fileId]['description'] = $description;
            \Log::info('Updated file description:', ['fileId' => $fileId, 'description' => $description]);
        } else {
            \Log::warning('File not found in uploadedFiles:', ['fileId' => $fileId, 'availableFiles' => array_keys($this->uploadedFiles)]);
        }
    }

    public function updateFilePreview($identifier, $imageUrl, $tempPath = null)
    {
        // identifier file_id (string) veya index (integer) olabilir
        if (is_string($identifier)) {
            // file_id ile güncelle
            foreach ($this->existingFiles as $index => $file) {
                if (isset($file['file_id']) && (string) $file['file_id'] === (string) $identifier) {
                    // Path'i güncelle (storage/ ile başlayan path)
                    $path = str_replace(asset('storage/'), '', $imageUrl);
                    $this->existingFiles[$index]['path'] = $path;
                    $this->existingFiles[$index]['is_new'] = false;
                    
                    // Post'u refresh et ki değişiklikler görünsün
                    $this->post->refresh();
                    
                    // Livewire'a güncelleme bildir
                    $this->dispatch('image-updated', [
                        'file_id' => $identifier,
                        'image_url' => $imageUrl,
                    ]);
                    
                    return;
                }
            }
            
            // Eğer Posts modülündeki File model'inde varsa güncelle
            $file = \Modules\Posts\Models\File::find($identifier);
            if ($file) {
                $path = str_replace(asset('storage/'), '', $imageUrl);
                $file->update(['file_path' => $path]);
                $this->post->refresh();
                
                $this->dispatch('image-updated', [
                    'file_id' => $identifier,
                    'image_url' => $imageUrl,
                ]);
            }
        } elseif (is_numeric($identifier)) {
            // index ile güncelle
            $index = (int) $identifier;
            if (isset($this->existingFiles[$index])) {
                $path = str_replace(asset('storage/'), '', $imageUrl);
                $this->existingFiles[$index]['path'] = $path;
                $this->existingFiles[$index]['is_new'] = false;
                
                $this->post->refresh();
                
                $this->dispatch('image-updated', [
                    'index' => $index,
                    'image_url' => $imageUrl,
                ]);
            }
        }
    }

    public function updatePost()
    {
        if (! Auth::user()->can('edit posts')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        $this->validate();

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
            \Log::info('Preparing files for PostsService:', ['uploadedFiles_count' => count($this->uploadedFiles)]);
            foreach ($this->uploadedFiles as $data) {
                $newFiles[] = $data['file'];
                \Log::info('File added to newFiles array:', ['filename' => $data['file']->getClientOriginalName()]);
            }
            \Log::info('Final newFiles count:', ['count' => count($newFiles)]);
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

        $postsService = new PostsService;
        $postsService->update(
            $this->post,
            $formData,
            $newFiles,
            $this->categoryIds,
            $tagIds,
            $fileDescriptions
        );

        // Galeri için content'i güncelle (açıklamalar dahil)
        if ($this->post_type === 'gallery') {
            // Post'u refresh et ki yeni dosyalar için file_path'ler güncellensin
            $this->post->refresh();

            // Yeni dosyalar için file_path'leri güncelle ve açıklamaları koru
            if (! empty($newFiles)) {
                /** @var \Illuminate\Database\Eloquent\Collection<int, \Modules\Posts\Models\File> $postFiles */
                $postFiles = $this->post->files()->orderBy('created_at', 'desc')->take(count($newFiles))->get();

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
            // Post'u yenile ki content güncellensin
            $this->post->refresh();

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
    }

    public function removeExistingFile($index)
    {
        if (isset($this->existingFiles[$index])) {
            $file = $this->existingFiles[$index];

            // Sadece gerçek file_id'leri (integer) database'den sil
            if (isset($file['file_id']) && is_numeric($file['file_id'])) {
                File::where('file_id', $file['file_id'])->delete();
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

            // Debug için log ekle
            \Log::info('Updating existing file by index:', [
                'index' => $index,
                'field' => $field,
                'value' => $value,
                'post_type' => $this->post->post_type,
            ]);

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

        // Hiçbir şey bulunamadıysa sessizce devam et (debug için log)
        \Log::debug('File not found for update:', [
            'fileId' => $fileId,
            'field' => $field,
            'available_file_ids' => array_column($this->existingFiles, 'file_id'),
        ]);
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
        // Güvenlik: fileId ve field kontrolü
        if (empty($fileId) || empty($field)) {
            return;
        }

        // Field validation
        if (! in_array($field, ['description', 'title', 'alt'])) {
            abort(403, 'Invalid field');
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

        // First, try to update in existingFiles array (existing files)
        foreach ($this->existingFiles as $index => $file) {
            $currentFileId = isset($file['file_id']) ? (string) $file['file_id'] : null;

            if ($currentFileId === $fileIdStr) {
                $this->existingFiles[$index][$field] = $value;

                // If fileId is numeric (existing file in database), update database
                if (is_numeric($fileId)) {
                    File::where('file_id', (int) $fileId)->update([$dbField => $value]);
                }

                return;
            }
        }

        // If not found in existingFiles, try uploadedFiles array (new files)
        if (isset($this->uploadedFiles[$fileIdStr])) {
            $this->uploadedFiles[$fileIdStr][$field] = $value;

            return;
        }

        // If not found in either array, log debug info
        \Log::debug('File not found in updateFileById:', [
            'fileId' => $fileId,
            'field' => $field,
            'existing_file_ids' => array_column($this->existingFiles, 'file_id'),
            'uploaded_file_ids' => array_keys($this->uploadedFiles),
        ]);
    }

    private function updateGalleryContent(): bool
    {
        try {
            // existingFiles'dan açıklamaları kontrol et (debug)
            $descriptions = array_map(function ($file) {
                return [
                    'file_id' => $file['file_id'],
                    'description' => $file['description'] ?? '',
                    'original_name' => $file['original_name'],
                ];
            }, $this->existingFiles);

            Log::info('Before saveGalleryContent - existingFiles descriptions:', [
                'descriptions' => $descriptions,
            ]);

            // PostsService kullanarak veritabanına kaydet
            $result = $this->postsService->saveGalleryContent(
                $this->post,
                $this->existingFiles,
                $this->primaryFileId // String olarak gönder (int'e cast etme)
            );

            if ($result) {
                // Post model'ini yenile
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

            Log::warning('Gallery content update returned false', [
                'post_id' => $this->post->post_id,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to update gallery content', [
                'post_id' => $this->post->post_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Kullanıcıya hata göster
            session()->flash('error', 'Galeri içeriği güncellenirken bir hata oluştu: '.$e->getMessage());

            throw $e;
        }
    }

    // Ana resmi kaldır
    public function removePrimaryFile()
    {
        if ($this->post->primaryFile) {
            $this->post->primaryFile->delete();
            $this->post->refresh();
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
            Log::info('=== updateOrder METODU ÇAĞRILDI (PostEdit) ===', [
                'order' => $order,
                'timestamp' => now(),
                'existingFiles_count' => count($this->existingFiles),
            ]);

            // Validation
            if (! $this->postsService->validateOrder($order, $this->existingFiles, true)) {
                Log::warning('Invalid order data received', [
                    'order' => $order,
                    'existingFiles_count' => count($this->existingFiles),
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
                Log::info('Order unchanged, skipping update');

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

                Log::info('Files reordered via updateOrder (PostEdit):', [
                    'newOrder' => array_column($this->existingFiles, 'file_id'),
                    'orderCount' => count($order),
                    'final_count' => count($this->existingFiles),
                    'orders' => array_column($this->existingFiles, 'order'),
                ]);

                // Sıralamayı veritabanına kaydet
                $this->updateGalleryContent();
            });

            // Başarı mesajı - Sessizce çalış, alert gösterme
            $this->dispatch('order-updated');

            Log::info('Gallery order updated successfully', [
                'post_id' => $this->post->post_id,
                'new_order' => array_column($this->existingFiles, 'file_id'),
                'existingFiles' => array_map(function ($file) {
                    return [
                        'file_id' => $file['file_id'],
                        'order' => $file['order'],
                    ];
                }, $this->existingFiles),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update gallery order', [
                'post_id' => $this->post->post_id,
                'order' => $order,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Kullanıcıya hata göster
            $this->dispatch('order-update-failed', [
                'message' => 'Sıralama güncellenirken bir hata oluştu: '.$e->getMessage(),
            ]);

            // Hata durumunda orijinal sıralamayı koru
            $this->post->refresh();
            $this->mount($this->post->post_id);
        }
    }

    public function render()
    {
        // Kategori türüne göre filtreleme
        $categories = Category::where('status', 'active')
            ->where('type', $this->post_type)
            ->orderBy('name')
            ->get();

        $postTypes = Post::TYPES;
        $postPositions = Post::POSITIONS;
        $postStatuses = Post::STATUSES;

        /** @var view-string $view */
        $view = 'posts::livewire.post-edit';

        return view($view, compact('categories', 'postTypes', 'postPositions', 'postStatuses'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
