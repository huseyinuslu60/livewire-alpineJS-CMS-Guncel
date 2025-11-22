<div x-data="postsForm()"
     x-init="// Initialize image editor reference
             $nextTick(() => {
                 const editorEl = document.querySelector('[x-data*=imageEditor]');
                 if (editorEl && editorEl._x_dataStack && editorEl._x_dataStack[0]) {
                     window.postsImageEditor = editorEl._x_dataStack[0];
                 }
             });">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div x-data="{ showSuccess: true }"
             x-show="showSuccess"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-lg"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="showSuccess = false" class="text-green-400 hover:text-green-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ showError: true }"
             x-show="showError"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="showError = false" class="text-red-400 hover:text-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Success Message -->
    <x-success-message :message="$successMessage" />

    <!-- General Error Message -->
    @error('general')
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-red-800">{{ $message }}</p>
                </div>
            </div>
        </div>
    @enderror

    <!-- Modern Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-newspaper text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Yeni Haber Oluştur</h2>
                        <p class="text-gray-600">Haber türünde yeni içerik oluşturun</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('posts.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <form @submit.prevent="syncContentAndSave">
                        <!-- Başlık -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-heading mr-1 text-blue-500"></i>
                                Başlık *
                            </label>
                            <input type="text"
                                   wire:model.live="title"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                   id="title"
                                   placeholder="Haber başlığını girin..."
                                   required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div class="mb-6">
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-link mr-1 text-blue-500"></i>
                                Slug
                            </label>
                            <input type="text"
                                   wire:model="slug"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('slug') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                   id="slug"
                                   placeholder="URL slug'ı (otomatik oluşturulur)">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Özet -->
                        <div class="mb-6">
                            <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file-alt mr-1 text-blue-500"></i>
                                Özet
                            </label>
                            <textarea wire:model.live="summary"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('summary') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                      id="summary"
                                      rows="3"
                                      placeholder="Haber özetini girin..."></textarea>
                            @error('summary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- İçerik -->
                        <div class="mb-6">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-1 text-blue-500"></i>
                                İçerik *
                            </label>
                            <div wire:ignore>
                                <textarea
                                    wire:model.live="content"
                                    class="trumbowyg block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('content') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                    id="content"
                                    data-editor="trumbowyg"
                                    rows="10"
                                    placeholder="Haber içeriğini girin..."
                                    required
                                >{!! $content !!}</textarea>
                            </div>
                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Medya -->
                        <div class="mb-6">
                            <label for="files" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-image mr-1 text-blue-500"></i>
                                Görsel (Opsiyonel)
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors duration-200 relative">
                                <!-- Arşivden Seç Butonu - Dropzone'un sol köşesinde -->
                                <button type="button"
                                        onclick="document.dispatchEvent(new CustomEvent('openFilesModal', { detail: { mode: 'select', multiple: false, type: 'image' } }))"
                                        class="absolute top-3 left-3 inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors duration-150 shadow-md z-10">
                                    <i class="fas fa-archive mr-1"></i>
                                    Arşivden Seç
                                </button>
                                <input type="file"
                                       wire:model.live="files"
                                       multiple
                                       class="hidden"
                                       id="files"
                                       accept="image/*">
                                <label for="files" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-700 mb-2">Dosya Seç veya Sürükle</p>
                                    <p class="text-sm text-gray-500">JPG, PNG, GIF formatları desteklenir</p>
                                </label>
                                <div wire:loading.flex wire:target="files" class="absolute inset-0 rounded-lg bg-white/70 dark:bg-gray-900/60 backdrop-blur-sm">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <div class="animate-spin inline-block w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full"></div>
                                    </div>
                                    <div class="absolute bottom-4 left-0 right-0 text-sm text-gray-700 dark:text-gray-200 text-center font-medium">
                                        Yükleniyor...
                                    </div>
                                </div>
                            </div>

                            @if(!empty($selectedArchiveFilesPreview) && empty($files))
                                <div class="mt-4">
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                        @foreach($selectedArchiveFilesPreview as $index => $archiveFile)
                                            @php
                                                $imageKey = 'existing:' . ($archiveFile['id'] ?? ('archive-'.$index));
                                                $imageUrl = $archiveFile['url'] ?? '';
                                            @endphp
                                            <div class="relative group image-preview-card"
                                                 data-image-key="{{ $imageKey }}">
                                                <canvas class="image-preview-canvas w-full h-24 object-cover rounded-lg border border-gray-200"
                                                        data-image-key="{{ $imageKey }}"
                                                        style="display: none;"></canvas>
                                                <img src="{{ $imageUrl }}"
                                                     class="image-preview-img w-full h-24 object-cover rounded-lg border border-gray-200"
                                                     alt="Preview"
                                                     data-image-key="{{ $imageKey }}"
                                                     data-image-url="{{ $imageUrl }}"
                                                     data-spot-data=""
                                                     data-has-spot-data="false"
                                                     onload="if(window.renderPreviewWithSpotData){ window.renderPreviewWithSpotData(this); }">
                                                <button type="button"
                                                        class="absolute top-1 left-1 bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-blue-600 transition-colors duration-200 z-10 opacity-0 group-hover:opacity-100 image-edit-button"
                                                        data-image-key="{{ $imageKey }}"
                                                        data-image-url="{{ $imageUrl }}"
                                                        onclick="(function(){ const k=this.getAttribute('data-image-key'); const u=this.getAttribute('data-image-url'); if(window.openImageEditor){ window.openImageEditor(k,{url:u}); } }).call(this);"
                                                        title="Resmi Düzenle">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors duration-200 z-10 opacity-0 group-hover:opacity-100"
                                                        wire:click="removeSelectedArchiveFile({{ $archiveFile['id'] ?? 'null' }}, {{ $index }})"
                                                        title="Kaldır">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Seçilen Dosyaların Önizlemesi -->
                            @if($files)
                                <div class="mt-4">
                                    <h6 class="text-sm font-medium text-gray-700 mb-3">
                                        <i class="fas fa-images mr-1"></i>
                                        Yüklenen Dosyalar ({{ count($files) }})
                                    </h6>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                        @foreach($files as $index => $file)
                                            @php
                                                // Generate imageKey for new upload (temp image)
                                                $imageKey = 'temp:' . $index;

                                                // Livewire temporaryUrl() kullan - eğer çalışmazsa base64 fallback
                                                $previewUrl = null;
                                                try {
                                                    $previewUrl = $file->temporaryUrl();
                                                    // URL boş veya null ise fallback kullan
                                                    if (empty($previewUrl) || !filter_var($previewUrl, FILTER_VALIDATE_URL)) {
                                                        throw new \Exception('Invalid URL');
                                                    }
                                                } catch (\Exception $e) {
                                                    // Fallback: Use base64 encoded image for preview (only for small images)
                                                    try {
                                                        $realPath = $file->getRealPath();
                                                        if ($realPath && file_exists($realPath)) {
                                                            $fileSize = filesize($realPath);
                                                            // Sadece küçük resimler için base64 kullan (max 2MB)
                                                            if ($fileSize < 2 * 1024 * 1024) {
                                                                $imageContent = file_get_contents($realPath);
                                                                $base64 = base64_encode($imageContent);
                                                                $mimeType = $file->getMimeType() ?: 'image/jpeg';
                                                                $previewUrl = 'data:' . $mimeType . ';base64,' . $base64;
                                                            } else {
                                                                // Büyük resimler için placeholder
                                                                throw new \Exception('File too large for base64');
                                                            }
                                                        } else {
                                                            throw new \Exception('File path not found');
                                                        }
                                                    } catch (\Exception $e2) {
                                                        // Last resort: placeholder
                                                        $previewUrl = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="#f3f4f6"/><text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#9ca3af" font-size="12">Resim yüklenemedi</text></svg>');
                                                    }
                                                }

                                                // Use edited URL if available
                                                $imageUrl = $editedFileUrls[$index] ?? $previewUrl;
                                            @endphp
                                            <div class="relative group image-preview-card"
                                                 data-image-key="{{ $imageKey }}">
                                                {{-- Preview canvas for spot_data rendering --}}
                                                <canvas class="image-preview-canvas w-full h-24 object-cover rounded-lg border border-gray-200"
                                                        data-image-key="{{ $imageKey }}"
                                                        style="display: none;"></canvas>
                                                {{-- Fallback image --}}
                                                @php
                                                    $spotDataJson = '';
                                                    $hasSpotData = false;
                                                    if (isset($imageEditorData[$index]) && is_array($imageEditorData[$index])) {
                                                        $originalPathForSpot = null;
                                                        if (!empty($editedFileUrls[$index])) {
                                                            $originalPathForSpot = $editedFileUrls[$index];
                                                        } elseif (!empty($imageUrl) && strpos($imageUrl, '/livewire/preview-file/') === false && strpos($imageUrl, 'livewire/preview-file') === false) {
                                                            $originalPathForSpot = $imageUrl;
                                                        }

                                                        $spotData = [
                                                            'image' => [
                                                                'original' => [
                                                                    'path' => $originalPathForSpot,
                                                                    'width' => null,
                                                                    'height' => null,
                                                                    'hash' => null,
                                                                ],
                                                                'variants' => [
                                                                    'desktop' => [
                                                                        'crop' => $imageEditorData[$index]['crop']['desktop'] ?? $imageEditorData[$index]['desktopCrop'] ?? [],
                                                                        'focus' => $imageEditorData[$index]['focus']['desktop'] ?? $imageEditorData[$index]['desktopFocus'] ?? 'center',
                                                                    ],
                                                                    'mobile' => [
                                                                        'crop' => $imageEditorData[$index]['crop']['mobile'] ?? $imageEditorData[$index]['mobileCrop'] ?? [],
                                                                        'focus' => $imageEditorData[$index]['focus']['mobile'] ?? $imageEditorData[$index]['mobileFocus'] ?? 'center',
                                                                    ],
                                                                ],
                                                                'effects' => $imageEditorData[$index]['effects'] ?? [],
                                                                'textObjects' => $imageEditorData[$index]['textObjects'] ?? [],
                                                                'canvas' => $imageEditorData[$index]['canvas'] ?? ['width' => 0, 'height' => 0],
                                                            ],
                                                        ];
                                                        $spotDataJson = json_encode($spotData);
                                                        $hasSpotData = !empty($spotDataJson) && strlen($spotDataJson) > 20;
                                                    }
                                                @endphp
                                                <img src="{{ $imageUrl }}"
                                                     class="image-preview-img w-full h-24 object-cover rounded-lg border border-gray-200"
                                                     alt="Preview {{ $index + 1 }}"
                                                     data-image-key="{{ $imageKey }}"
                                                     data-image-url="{{ $imageUrl }}"
                                                     data-spot-data="{{ $hasSpotData ? htmlspecialchars($spotDataJson, ENT_QUOTES, 'UTF-8') : '' }}"
                                                     data-has-spot-data="{{ $hasSpotData ? 'true' : 'false' }}"
                                                     data-file-index="{{ $index }}"
                                                     onload="if(window.renderPreviewWithSpotData) { window.renderPreviewWithSpotData(this); } else { setTimeout(() => { if(window.renderPreviewWithSpotData) window.renderPreviewWithSpotData(this); }, 100); }">
                                                <!-- Düzenle Butonu -->
                                                <button type="button"
                                                        class="absolute top-1 left-1 bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-blue-600 transition-colors duration-200 z-10 opacity-0 group-hover:opacity-100 image-edit-button"
                                                        data-image-key="{{ $imageKey }}"
                                                        data-image-url="{{ $imageUrl }}"
                                                        onclick="(function(){ const k=this.getAttribute('data-image-key'); const u=this.getAttribute('data-image-url'); if(window.openImageEditor){ window.openImageEditor(k,{url:u}); } }).call(this);"
                                                        title="Resmi Düzenle">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <!-- Kaldır Butonu -->
                                                <button type="button"
                                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors duration-200 z-10"
                                                        onclick="(function(){ var idx={{ $index }}; if (window.imageEditorUnregisterByIndex) { try { window.imageEditorUnregisterByIndex(idx); } catch(e){} } }).call(this);"
                                                        wire:click="removeFile({{ $index }})"
                                                        title="Resmi Kaldır">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <div class="mt-1">
                                                    <p class="text-xs text-gray-500 truncate">{{ $file->getClientOriginalName() }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @error('files.*')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                JPG, PNG, GIF formatları desteklenir. Maksimum 4MB.
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Haber Ekle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Yayın Ayarları -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-cog mr-2 text-blue-500"></i>
                        Yayın Ayarları
                    </h3>
                    <div class="space-y-4">
                        <!-- Pozisyon -->
                        <div>
                            <label for="post_position" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-layers mr-1 text-blue-500"></i>
                                Pozisyon *
                            </label>
                            <select wire:model.live="post_position"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    id="post_position"
                                    required>
                                @foreach($postPositions as $position)
                                    <option value="{{ $position }}">{{ \Modules\Posts\Domain\ValueObjects\PostPosition::labels()[$position] ?? ucfirst($position) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Durum -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-flag mr-1 text-blue-500"></i>
                                Durum *
                            </label>
                            <select wire:model.live="status"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    id="status"
                                    required>
                                @foreach($postStatuses as $status)
                                    <option value="{{ $status }}">
                                        @if($status === 'draft') Pasif
                                        @elseif($status === 'published') Aktif
                                        @elseif($status === 'scheduled') Zamanlanmış
                                        @elseif($status === 'archived') Arşivlendi
                                        @else {{ ucfirst($status) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Yayın Tarihi -->
                        <div>
                            <label for="published_date" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1 text-blue-500"></i>
                                Yayın Tarihi
                            </label>
                            <input type="datetime-local"
                                   wire:model.live="published_date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   id="published_date">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kategoriler -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-tags mr-2 text-blue-500"></i>
                        Kategoriler *
                    </h3>
                    <div wire:ignore>
                        <select wire:model.live="categoryIds"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('categoryIds') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                id="categoryIds"
                                multiple>
                            @foreach($categories as $category)
                                <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('categoryIds')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if(empty($categoryIds))
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            En az bir kategori seçmelisiniz.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Yönlendirme Linki -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-external-link-alt mr-2 text-blue-500"></i>
                        Yönlendirme Linki
                    </h3>
                    <div>
                        <label for="redirect_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL
                        </label>
                        <input type="url"
                               wire:model.live="redirect_url"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               id="redirect_url"
                               placeholder="https://example.com">
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Bu link varsa, haber tıklandığında bu adrese yönlendirilir
                        </p>
                    </div>
                </div>
            </div>

            <!-- Etiketler -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-tag mr-2 text-blue-500"></i>
                        Etiketler
                    </h3>
                    <div x-data="tagsInput($wire.tagsInput || '')" class="space-y-3">
                        <!-- Mevcut Etiketler -->
                        <div class="flex flex-wrap gap-2" x-show="tags.length > 0">
                            <template x-for="(tag, index) in tags" :key="index">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                    <span x-text="tag"></span>
                                    <button type="button"
                                            @click="removeTag(index)"
                                            class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            </template>
                        </div>

                        <!-- Yeni Etiket Ekleme -->
                        <div class="flex space-x-2">
                            <input type="text"
                                   x-model="newTag"
                                   @keydown="keydown($event)"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="Etiket ekle...">
                            <button type="button"
                                    @click="addTag()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>

                        <p class="text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Etiketleri virgülle ayırın veya Enter tuşuna basın.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Görünürlük Ayarları -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-eye mr-2 text-blue-500"></i>
                        Görünürlük Ayarları
                    </h3>
                    <div class="space-y-4">
                        <!-- Yorumlara izin ver -->
                        <label class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="is_comment"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-comment mr-1"></i>
                                Yorumlara izin ver
                            </span>
                        </label>

                        <!-- Ana sayfada göster -->
                        <label class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="is_mainpage"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-home mr-1"></i>
                                Ana sayfada göster
                            </span>
                        </label>

                        <!-- Bülten'de göster -->
                        <label class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="in_newsletter"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-envelope mr-1"></i>
                                Bülten'de göster
                            </span>
                        </label>

                        <!-- Reklam gösterme -->
                        <label class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="no_ads"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-ad mr-1"></i>
                                Reklam gösterme
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Posts modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Posts/resources/assets/sass/app.scss', 'Modules/Posts/resources/assets/js/app.js', 'resources/js/image-preview-renderer/index.js'])

    {{-- Image Editor Modal --}}
    <div x-data="imageEditor()">
        @include('partials.image-editor-modal')
    </div>
</div>
