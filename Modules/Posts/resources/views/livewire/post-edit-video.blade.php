<div x-data="postsForm()"
     x-init="// Initialize image editor reference
             $nextTick(() => {
                 const editorEl = document.querySelector('[x-data*=imageEditor]');
                 if (editorEl && editorEl._x_dataStack && editorEl._x_dataStack[0]) {
                     window.postsImageEditor = editorEl._x_dataStack[0];
                 }
             });">
    <!-- Success Message -->
    <x-success-message :message="$successMessage" />

    <!-- Modern Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-video text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">
                            Video Düzenle
                        </h2>
                        <p class="text-gray-600">Mevcut içeriği düzenleyin</p>
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

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
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
                    <button @click="show = false" class="text-green-400 hover:text-green-600">
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

    <!-- Form Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <form wire:submit.prevent="updatePost"
                          onsubmit="(function(e) {
                            // Force sync primary_image_spot_data before form submit
                            const primaryImageInput = document.getElementById('primary_image_spot_data');
                            if (primaryImageInput && primaryImageInput.value) {
                              // Find Livewire component and sync
                              const wireId = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
                              if (wireId && window.Livewire && window.Livewire.find) {
                                try {
                                  const component = window.Livewire.find(wireId);
                                  if (component) {
                                    component.set('primary_image_spot_data', primaryImageInput.value);
                                    }
                                  } catch (err) {
                                  }
                                }
                              }
                          })(event);"
                          wire:ignore.self>
                        <!-- Başlık -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-heading mr-1 text-red-500"></i>
                                Başlık *
                            </label>
                            <input type="text"
                                   wire:model.live="title"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
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
                                <i class="fas fa-link mr-1 text-red-500"></i>
                                Slug
                            </label>
                            <input type="text"
                                   wire:model.live="slug"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm @error('slug') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                   id="slug"
                                   placeholder="URL slug'ı (otomatik oluşturulur)">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Özet -->
                        <div class="mb-6">
                            <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file-alt mr-1 text-red-500"></i>
                                Özet
                            </label>
                            <textarea wire:model.live="summary"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm @error('summary') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                      id="summary"
                                      rows="3"
                                      placeholder="Haber özetini girin..."></textarea>
                            @error('summary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Video için Resim Güncelleme -->
                        @if($this->post->post_type === 'video')
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-image mr-1 text-red-500"></i>
                                        Resim Güncelle
                                    </label>

                                    @if(!empty($selectedArchiveFilesPreview) && empty($newFiles))
                                        <div class="mb-4">
                                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                                @foreach($selectedArchiveFilesPreview as $index => $archiveFile)
                                                    @php
                                                        $imageKey = 'existing:' . ($archiveFile['id'] ?? ('archive-'.$index));
                                                        $imageUrl = $archiveFile['url'] ?? '';
                                                    @endphp
                                                    <div class="relative group image-preview-card" data-image-key="{{ $imageKey }}">
                                                        <canvas class="image-preview-canvas w-full h-24 object-cover rounded-lg border border-gray-200"
                                                                data-image-key="{{ $imageKey }}"
                                                                style="display: none;"></canvas>
                                                        <img src="{{ $imageUrl }}" 
                                                             class="image-preview-img w-full h-24 object-cover rounded-lg border border-gray-200" 
                                                             alt="Preview"
                                                             data-image-key="{{ $imageKey }}"
                                                             data-image-url="{{ $imageUrl }}"
                                                             data-file-path="{{ $archiveFile['file_path'] ?? '' }}"
                                                             data-spot-data=""
                                                             data-has-spot-data="false"
                                                             onload="if(window.renderPreviewWithSpotData){ window.renderPreviewWithSpotData(this); }">
                                                        <div class="absolute top-1 right-1">
                                                            <button type="button"
                                                                    class="image-edit-button bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-blue-600 transition-colors duration-200 shadow-md"
                                                                    data-image-key="{{ $imageKey }}"
                                                                    data-image-url="{{ $imageUrl }}"
                                                                    data-file-path="{{ $archiveFile['file_path'] ?? '' }}"
                                                                    onclick="(function(){ const k=this.getAttribute('data-image-key'); const u=this.getAttribute('data-image-url'); const p=this.getAttribute('data-file-path'); if(window.openImageEditor){ window.openImageEditor(k,{url:u, filePath:p}); } }).call(this);"
                                                                    title="Düzenle">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button"
                                                                    class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-red-600 transition-colors duration-200 shadow-md"
                                                                    wire:click="removeSelectedArchiveFile({{ $archiveFile['id'] ?? 'null' }}, {{ $index }})"
                                                                    title="Kaldır">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Resim Ön İzlemesi -->
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">
                                                @if(!empty($newFiles))
                                                    Yeni Seçilen Resim
                                                @elseif($this->post->primaryFile)
                                                    Mevcut Resim
                                                @else
                                                    Resim Önizleme
                                                @endif
                                            </span>
                                            <div class="flex items-center space-x-2">
                                            @if($this->post->primaryFile && empty($newFiles))
                                                <button type="button"
                                                        onclick="(function(){ var key='existing:{{ $this->post->primaryFile->file_id }}'; if (window.imageEditorUnregister) { try { window.imageEditorUnregister(key); } catch(e){} } }).call(this);"
                                                        wire:click="removePrimaryFile"
                                                        class="text-red-600 hover:text-red-800 text-sm">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    Kaldır
                                                </button>
                                            @elseif(!empty($newFiles))
                                                <button type="button"
                                                        wire:click="$set('newFiles', [])"
                                                        wire:loading.attr="disabled"
                                                        wire:target="newFiles"
                                                        class="text-red-600 hover:text-red-800 text-sm disabled:opacity-50">
                                                    <i class="fas fa-times mr-1"></i>
                                                    İptal
                                                </button>
                                            @endif
                                            </div>
                                        </div>
                                        <div class="w-64 h-40 bg-gray-100 rounded-lg border border-gray-200 overflow-hidden mx-auto relative">
                                            <!-- Resim Ön İzleme -->
                                            @if(!empty($newFiles))
                                                @php
                                                    $latestFile = collect($newFiles)->last();
                                                    $newFileIndex = collect($newFiles)->keys()->last();
                                                    $imageKey = 'temp:' . $newFileIndex;
                                                @endphp
                                                @if($latestFile)
                                                    <div class="relative w-full h-full group image-preview-card"
                                                         data-image-key="{{ $imageKey }}">
                                                        <canvas class="image-preview-canvas w-full h-full object-cover"
                                                                data-image-key="{{ $imageKey }}"
                                                                style="display: none;"></canvas>
                                                        <img src="{{ $latestFile->temporaryUrl() }}"
                                                             class="image-preview-img w-full h-full object-cover"
                                                             alt="Yeni seçilen resim"
                                                             data-image-key="{{ $imageKey }}"
                                                             data-image-url="{{ $latestFile->temporaryUrl() }}"
                                                             data-has-spot-data="false">

                                                        {{-- Top right corner button --}}
                                                        <div class="absolute top-2 right-2">
                                                            <button type="button"
                                                                    class="image-edit-button bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-blue-600 transition-colors duration-200 shadow-md"
                                                                    data-image-key="{{ $imageKey }}"
                                                                    data-image-url="{{ $latestFile->temporaryUrl() }}"
                                                                    onclick="(function () {
                                                                        const btn = this;
                                                                        const imageKey = btn.getAttribute('data-image-key');
                                                                        const imageUrl = btn.getAttribute('data-image-url');

                                                                        console.log('Image Edit Button Clicked (New Upload):', { imageKey, imageUrl, hasOpenImageEditor: typeof window.openImageEditor !== 'undefined' });

                                                                        if (!imageKey || !imageUrl) {
                                                                            console.error('Image Edit Button - Missing imageKey or imageUrl');
                                                                            return;
                                                                        }

                                                                        if (typeof window.openImageEditor === 'function') {
                                                                            try {
                                                                                window.openImageEditor(imageKey, {
                                                                                    url: imageUrl,
                                                                                    initialSpotData: null,
                                                                                });
                                                                            } catch(e) {
                                                                                console.error('Image Edit Button - Error calling openImageEditor:', e);
                                                                            }
                                                                        } else {
                                                                            console.error('Image Edit Button - window.openImageEditor is not defined');
                                                                            let attempts = 0;
                                                                            const checkInterval = setInterval(() => {
                                                                                attempts++;
                                                                                if (typeof window.openImageEditor === 'function') {
                                                                                    clearInterval(checkInterval);
                                                                                    window.openImageEditor(imageKey, {
                                                                                        url: imageUrl,
                                                                                        initialSpotData: null,
                                                                                    });
                                                                                } else if (attempts >= 10) {
                                                                                    clearInterval(checkInterval);
                                                                                }
                                                                            }, 100);
                                                                        }
                                                                    }).call(this);"
                                                                    title="Düzenle">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            @elseif($this->post->primaryFile && $this->primaryFileId !== null && $this->primaryFileId !== '' && !empty($this->post->primaryFile->file_path))
                                                @php
                                                    // Generate imageKey for existing primary file
                                                    $imageKey = 'existing:' . $this->post->primaryFile->file_id;

                                                    // NEW: Use primary_image_spot_data from Livewire property (synced with hidden input)
                                                    // This ensures JS editor updates are reflected in Livewire
                                                    $primaryImageSpotDataJson = $primary_image_spot_data ?? null;

                                                    // Fallback: If Livewire property is empty, use post->spot_data['image']
                                                    if (!$primaryImageSpotDataJson) {
                                                        $spotData = $this->post->spot_data ?? null;
                                                        if ($spotData && isset($spotData['image'])) {
                                                            // Wrap in { image: {...} } format for preview renderer
                                                            $imageData = $spotData['image'];
                                                            // Handle nested structure: if image has 'image' key, unwrap it
                                                            if (isset($imageData['image']) && is_array($imageData['image'])) {
                                                                $imageData = $imageData['image'];
                                                            }
                                                            $primaryImageSpotDataJson = json_encode(['image' => $imageData]);
                                                        }
                                                    } else {
                                                        // primary_image_spot_data is the image object (from hidden input)
                                                        // Wrap it in { image: {...} } format for preview renderer
                                                        $decoded = json_decode($primaryImageSpotDataJson, true);
                                                        if ($decoded) {
                                                            // Handle nested structure: if decoded has 'image' key, unwrap it
                                                            if (isset($decoded['image']) && is_array($decoded['image'])) {
                                                                $decoded = $decoded['image'];
                                                            }
                                                            // Wrap in { image: {...} } format for preview renderer
                                                            $primaryImageSpotDataJson = json_encode(['image' => $decoded]);
                                                        }
                                                    }

                                                    // Parse for preview rendering (legacy support)
                                                    $spotData = $this->post->spot_data ?? null;

                                                    // Ensure spot_data is an array (might be JSON string or already decoded)
                                                    if (is_string($spotData)) {
                                                        $decoded = json_decode($spotData, true);
                                                        $spotData = is_array($decoded) ? $decoded : null;
                                                    }

                                                    // If no saved spot_data exists, check if we have live editor data in component properties
                                                    // This is for cases where user is editing but hasn't saved yet
                                                    if (!$spotData || !isset($spotData['image'])) {
                                                        // Check if we have editor data in component properties
                                                        $hasEditorData = false;

                                                        // Check if any editor property is set
                                                        if (!empty($this->originalImagePath)) {
                                                            $hasEditorData = true;
                                                        } elseif (!empty($this->imageTextObjects) && is_array($this->imageTextObjects) && count($this->imageTextObjects) > 0) {
                                                            $hasEditorData = true;
                                                        } elseif (!empty($this->imageEffects) && is_array($this->imageEffects)) {
                                                            $hasEditorData = true;
                                                        } elseif (!empty($this->desktopCrop) && is_array($this->desktopCrop) && count($this->desktopCrop) > 0) {
                                                            $hasEditorData = true;
                                                        } elseif (!empty($this->canvasDimensions) && is_array($this->canvasDimensions) && (($this->canvasDimensions['width'] ?? 0) > 0 || ($this->canvasDimensions['height'] ?? 0) > 0)) {
                                                            $hasEditorData = true;
                                                        } elseif ($this->imageEditorUsed === true) {
                                                            $hasEditorData = true;
                                                        }

                                                        if ($hasEditorData) {
                                                            // Build spot_data from component properties (for unsaved edits)
                                                            $spotData = [
                                                                'image' => [
                                                                    'original' => [
                                                                        'path' => $this->originalImagePath ?? ($this->post->primaryFile->file_path ?? ''),
                                                                        'width' => $this->originalImageWidth ?? 0,
                                                                        'height' => $this->originalImageHeight ?? 0,
                                                                        'hash' => $this->originalImageHash ?? null,
                                                                    ],
                                                                    'variants' => [
                                                                        'desktop' => [
                                                                            'crop' => is_array($this->desktopCrop) && !empty($this->desktopCrop) ? $this->desktopCrop : [],
                                                                            'focus' => $this->desktopFocus ?? 'center',
                                                                        ],
                                                                        'mobile' => [
                                                                            'crop' => is_array($this->mobileCrop) && !empty($this->mobileCrop) ? $this->mobileCrop : [],
                                                                            'focus' => $this->mobileFocus ?? 'center',
                                                                        ],
                                                                    ],
                                                                    'effects' => is_array($this->imageEffects) ? $this->imageEffects : [],
                                                                    'meta' => is_array($this->imageMeta) ? $this->imageMeta : [],
                                                                    'textObjects' => is_array($this->imageTextObjects) ? $this->imageTextObjects : [],
                                                                    'canvas' => [
                                                                        'width' => $this->canvasDimensions['width'] ?? 0,
                                                                        'height' => $this->canvasDimensions['height'] ?? 0,
                                                                    ],
                                                                ],
                                                            ];
                                                        }
                                                    }

                                                    $imageUrl = asset('storage/' . $this->post->primaryFile->file_path);
                                                    if ($spotData && isset($spotData['image']['original']['path'])) {
                                                        $originalPath = $spotData['image']['original']['path'];
                                                        // If path is relative (doesn't start with http), prepend storage
                                                        if (strpos($originalPath, 'http') !== 0) {
                                                            $imageUrl = asset('storage/' . $originalPath);
                                                        } else {
                                                            $imageUrl = $originalPath;
                                                        }
                                                    }

                                                    $cropData = null;
                                                    $effects = null;
                                                    $textObjects = null;
                                                    $originalSize = null;

                                                    if ($spotData && isset($spotData['image'])) {
                                                        $imageData = $spotData['image'];
                                                        // Get desktop crop (or mobile if desktop not available)
                                                        $cropData = $imageData['variants']['desktop']['crop'] ?? $imageData['variants']['mobile']['crop'] ?? null;
                                                        $effects = $imageData['effects'] ?? null;
                                                        $textObjects = $imageData['textObjects'] ?? null;
                                                        $originalSize = $imageData['original'] ?? null;
                                                    }
                                                @endphp
                                                {{-- Hidden input for Livewire sync --}}
                                                <input type="hidden"
                                                       id="primary_image_spot_data"
                                                       name="primary_image_spot_data"
                                                       wire:model="primary_image_spot_data">

                                                <div class="relative w-full h-full group image-preview-card"
                                                     data-image-key="{{ $imageKey }}">
                                                    {{-- Preview canvas for spot_data rendering --}}
                                                    <canvas id="preview-canvas-{{ $this->post->primaryFile->file_id }}"
                                                            class="image-preview-canvas w-full h-full object-cover"
                                                            data-image-key="{{ $imageKey }}"
                                                            style="display: none;"></canvas>
                                                    {{-- Fallback image --}}
                                                    <img id="preview-img-{{ $this->post->primaryFile->file_id }}"
                                                         class="image-preview-img w-full h-full object-cover"
                                                         src="{{ $imageUrl }}"
                                                         alt="{{ $this->post->primaryFile->alt_text ?? 'Current Image' }}"
                                                         data-image-key="{{ $imageKey }}"
                                                         @if($primaryImageSpotDataJson)
                                                             data-spot-data="{{ htmlspecialchars($primaryImageSpotDataJson, ENT_QUOTES, 'UTF-8', false) }}"
                                                             data-has-spot-data="true"
                                                         @else
                                                             data-spot-data=""
                                                             data-has-spot-data="false"
                                                         @endif
                                                         data-image-url="{{ $imageUrl }}"
                                                         data-file-id="{{ $this->post->primaryFile->file_id }}"
                                                         data-file-path="{{ $this->post->primaryFile->file_path ?? '' }}"
                                                         onload="if(window.renderPreviewWithSpotData) { window.renderPreviewWithSpotData(this); } else { setTimeout(() => { if(window.renderPreviewWithSpotData) window.renderPreviewWithSpotData(this); }, 100); }"
                                                         onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">

                                                    {{-- Top right corner button --}}
                                                    <div class="absolute top-2 right-2">
                                                        <button type="button"
                                                                class="image-edit-button bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-blue-600 transition-colors duration-200 shadow-md"
                                                                data-image-key="{{ $imageKey }}"
                                                                data-image-url="{{ $imageUrl }}"
                                                                data-file-path="{{ $this->post->primaryFile->file_path ?? '' }}"
                                                                onclick="(function(){ const k=this.getAttribute('data-image-key'); const u=this.getAttribute('data-image-url'); const p=this.getAttribute('data-file-path'); if(window.openImageEditor){ window.openImageEditor(k,{url:u, filePath:p}); } }).call(this);"
                                                                title="Düzenle">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Placeholder when no image --}}
                                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 bg-gray-50">
                                                    <i class="fas fa-image text-4xl mb-2"></i>
                                                    <p class="text-sm">Resim yükleyin</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Yeni Resim Yükleme -->
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-red-400 transition-colors duration-200 relative">
                                        <!-- Arşivden Seç Butonu - Dropzone'un sol köşesinde -->
                                        <button type="button" 
                                                onclick="document.dispatchEvent(new CustomEvent('openFilesModal', { detail: { mode: 'select', multiple: false, type: 'image' } }))"
                                                class="absolute top-3 left-3 inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors duration-150 shadow-md z-10">
                                            <i class="fas fa-archive mr-1"></i>
                                            Arşivden Seç
                                        </button>
                                        <input type="file"
                                               wire:model.live="newFiles"
                                               class="hidden"
                                               id="imageFile"
                                               accept="image/*">
                                        <label for="imageFile" class="cursor-pointer">
                                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                            <p class="text-lg font-medium text-gray-700 mb-2">
                                                @if(!empty($newFiles))
                                                    Farklı Resim Seç
                                                @elseif($this->post->primaryFile)
                                                    Resmi Değiştir
                                                @else
                                                    Resim Yükle
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-500">JPG, PNG, GIF formatları desteklenir. Maksimum 4MB.</p>
                                        </label>
                                        <div wire:loading.flex wire:target="newFiles" class="absolute inset-0 rounded-lg bg-white/70 dark:bg-gray-900/60 backdrop-blur-sm">
                                            <div class="w-full h-full flex items-center justify-center">
                                                <div class="animate-spin inline-block w-10 h-10 border-4 border-red-500 border-t-transparent rounded-full"></div>
                                            </div>
                                            <div class="absolute bottom-4 left-0 right-0 text-sm text-gray-700 dark:text-gray-200 text-center font-medium">
                                                Yükleniyor...
                                            </div>
                                        </div>
                                    </div>


                                    @error('newFiles')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('newFiles.*')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                        @endif

                        <!-- Normal İçerik -->
                        <div class="mb-6">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-1 text-red-500"></i>
                                İçerik *
                            </label>
                            <div wire:ignore>
                                <textarea
                                    wire:model.live="content"
                                    class="trumbowyg block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 @error('content') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                    id="content"
                                    data-editor="trumbowyg"
                                    rows="8"
                                    placeholder="Haber içeriğini girin..."
                                    required
                                >{!! $content !!}</textarea>
                            </div>
                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Video Embed Kodu -->
                        <div class="mb-6">
                            <label for="embed_code" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-code mr-1 text-red-500"></i>
                                Video Embed Kodu *
                            </label>
                            <textarea wire:model.live="embed_code"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm @error('embed_code') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                      id="embed_code"
                                      rows="4"
                                      placeholder="<iframe src='...' width='630' height='354'></iframe>"
                                      required></textarea>
                            @error('embed_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                YouTube, Vimeo veya diğer video platformlarından embed kodunu yapıştırın.
                                Düzgün görünmesi için width="630" ve height="354" olarak düzenleyin.
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 shadow-lg hover:shadow-xl transition-all duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Değişiklikleri Kaydet
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
                        <i class="fas fa-cog mr-2 text-red-500"></i>
                        Yayın Ayarları
                    </h3>
                    <div class="space-y-4">
                        <!-- Durum -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-flag mr-1 text-red-500"></i>
                                Durum *
                        </label>
                            <select wire:model.live="status"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    id="status"
                                    required>
                                <option value="draft">Pasif</option>
                                <option value="published">Aktif</option>
                                <option value="scheduled">Zamanlanmış</option>
                            </select>
                        </div>

                        <!-- Pozisyon -->
                        <div>
                            <label for="post_position" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-layers mr-1 text-red-500"></i>
                                Pozisyon *
                        </label>
                            <select wire:model.live="post_position"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    id="post_position"
                                    required>
                                @foreach($postPositions as $position)
                                    <option value="{{ $position }}">{{ \Modules\Posts\Domain\ValueObjects\PostPosition::labels()[$position] ?? ucfirst($position) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Yayın Tarihi -->
                        <div>
                            <label for="published_date" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1 text-red-500"></i>
                                Yayın Tarihi
                        </label>
                            <input type="datetime-local"
                                   wire:model.live="published_date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                   id="published_date">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kategoriler -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-tags mr-2 text-red-500"></i>
                        Kategoriler
                    </h3>
                    <div wire:ignore>
                        <select wire:model.live="categoryIds"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                id="categoryIds"
                                multiple>
                        @foreach($categories as $category)
                                <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                        @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Yönlendirme Linki -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-external-link-alt mr-2 text-red-500"></i>
                        Yönlendirme Linki
                    </h3>
                    <div>
                        <label for="redirect_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL
                        </label>
                        <input type="url"
                               wire:model.live="redirect_url"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm"
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
                        <i class="fas fa-tag mr-2 text-red-500"></i>
                        Etiketler
                    </h3>
                    <div x-data="tagsInput($wire.tagsInput || '')" class="space-y-3">
                        <!-- Mevcut Etiketler -->
                        <div class="flex flex-wrap gap-2" x-show="tags.length > 0">
                            <template x-for="(tag, index) in tags" :key="index">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800 border border-orange-200">
                                    <span x-text="tag"></span>
                                    <button type="button"
                                            @click="removeTag(index)"
                                            class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-orange-200 transition-colors">
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
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                   placeholder="Etiket ekle...">
                            <button type="button"
                                    @click="addTag()"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
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
                        <i class="fas fa-eye mr-2 text-red-500"></i>
                        Görünürlük Ayarları
                    </h3>
                    <div class="space-y-4">
                        <!-- Yorumlara izin ver -->
                        <label for="is_comment" class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="is_comment"
                                   id="is_comment"
                                   class="h-4 w-4 text-orange-600 focus:ring-red-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-comment mr-1"></i>
                                Yorumlara izin ver
                            </span>
                        </label>

                        <!-- Ana sayfada göster -->
                        <label for="is_mainpage" class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="is_mainpage"
                                   id="is_mainpage"
                                   class="h-4 w-4 text-orange-600 focus:ring-red-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-home mr-1"></i>
                                Ana sayfada göster
                            </span>
                        </label>

                        <!-- Bülten'de göster -->
                        <label for="in_newsletter" class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="in_newsletter"
                                   id="in_newsletter"
                                   class="h-4 w-4 text-orange-600 focus:ring-red-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-envelope mr-1"></i>
                                Bülten'de göster
                            </span>
                        </label>

                        <!-- Reklam gösterme -->
                        <label for="no_ads" class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="no_ads"
                                   id="no_ads"
                                   class="h-4 w-4 text-orange-600 focus:ring-red-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-ad mr-1"></i>
                                Reklam gösterme
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-chart-bar mr-2 text-red-500"></i>
                        İstatistikler
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Görüntülenme</span>
                            <span class="text-sm font-medium text-gray-900">{{ number_format($this->post->view_count ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Oluşturulma</span>
                            <span class="text-sm font-medium text-gray-900">{{ $this->post->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Son Güncelleme</span>
                            <span class="text-sm font-medium text-gray-900">{{ $this->post->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vitrin Zamanlama -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-clock mr-2 text-red-500"></i>
                        Vitrin Zamanlama
                    </h3>

                    <p class="text-sm text-gray-600 mb-4">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pozisyon "Normal" dışında seçildiğinde otomatik olarak vitrine eklenir.
                    </p>

                    <!-- Zamanlama -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="featuredStartsAt" class="block text-sm font-medium text-gray-700 mb-2">
                                Başlangıç Tarihi
                            </label>
                            <input type="datetime-local"
                                   wire:model.live="featuredStartsAt"
                                   id="featuredStartsAt"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="featuredEndsAt" class="block text-sm font-medium text-gray-700 mb-2">
                                Bitiş Tarihi
                            </label>
                            <input type="datetime-local"
                                   wire:model.live="featuredEndsAt"
                                   id="featuredEndsAt"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Zamanlama boş bırakılırsa hemen vitrine eklenir.
                    </p>
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
