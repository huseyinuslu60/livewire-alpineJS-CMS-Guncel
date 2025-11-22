<div x-data="postsForm()">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div x-data="{ showSuccess: true }" x-show="showSuccess"
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
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-images text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Galeri Düzenle</h2>
                        <p class="text-gray-600">Mevcut galeriyi düzenleyin</p>
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
                          })(event);">
                        <!-- Başlık -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-heading mr-1 text-green-500"></i>
                                Başlık *
                            </label>
                            <input type="text"
                                   wire:model.live="title"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 sm:text-sm @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                   id="title"
                                   placeholder="Galeri başlığını girin..."
                                   required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <input type="hidden"
                               id="primary_image_spot_data"
                               name="primary_image_spot_data"
                               wire:model="primary_image_spot_data">

                        <!-- Slug -->
                        <div class="mb-6">
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-link mr-1 text-green-500"></i>
                                Slug
                            </label>
                            <input type="text"
                                   wire:model="slug"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 sm:text-sm @error('slug') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                   id="slug"
                                   placeholder="URL slug'ı (otomatik oluşturulur)">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Özet -->
                        <div class="mb-6">
                            <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file-alt mr-1 text-green-500"></i>
                                Özet
                            </label>
                            <textarea wire:model.live="summary"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 sm:text-sm @error('summary') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                      id="summary"
                                      rows="3"
                                      placeholder="Galeri özetini girin..."></textarea>
                            @error('summary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Galeri Görselleri -->
                        <div class="mb-6"
                             x-data="galleryUpload()"
                             x-on:livewire-upload-finish.window="focusNew()">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-images mr-1 text-green-500"></i>
                                Galeri Görselleri *
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-green-400 transition-colors duration-200 relative">
                                <!-- Arşivden Seç Butonu - Dropzone'un sol köşesinde -->
                                <button type="button"
                                        onclick="document.dispatchEvent(new CustomEvent('openFilesModal', { detail: { mode: 'select', multiple: true, type: 'image' } }))"
                                        class="absolute top-3 left-3 inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors duration-150 shadow-md z-10">
                                    <i class="fas fa-archive mr-1"></i>
                                    Arşivden Seç
                                </button>
                                <input type="file"
                                       wire:model.live="newFiles"
                                       multiple
                                       class="hidden"
                                       id="files"
                                       accept="image/*">
                                <label for="files" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-700 mb-2">Galeri Görselleri Seç veya Sürükle</p>
                                    <p class="text-sm text-gray-500">JPG, PNG, GIF formatları desteklenir. Maksimum 4MB.</p>
                                </label>
                                <div wire:loading.flex wire:target="newFiles" class="absolute inset-0 rounded-lg bg-white/70 dark:bg-gray-900/60 backdrop-blur-sm">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <div class="animate-spin inline-block w-10 h-10 border-4 border-green-500 border-t-transparent rounded-full"></div>
                                    </div>
                                    <div class="absolute bottom-4 left-0 right-0 text-sm text-gray-700 dark:text-gray-200 text-center font-medium">
                                        Yükleniyor...
                                    </div>
                                </div>
                            </div>

                            @error('files')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('files.*')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                                                    @if(!empty($selectedArchiveFilesPreview) && empty($uploadedFiles))
                                                        <div class="mt-4">
                                                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                                                @foreach($selectedArchiveFilesPreview as $index => $archiveFile)
                                                                    @php
                                                                        $archiveFileId = $archiveFile['id'] ?? ('archive-'.$index);
                                                                        $imageKey = 'existing:' . $archiveFileId;
                                                                        $imageUrl = $archiveFile['url'] ?? '';
                                                                        $isPrimaryArchive = $primaryFileId === (string)$archiveFileId || $primaryFileId === 'archive-'.$index;
                                                                    @endphp
                                                                    <div class="relative group image-preview-card"
                                                                         data-image-key="{{ $imageKey }}"
                                                                         wire:key="archive-file-{{ $archiveFileId }}">
                                                                        <div class="w-full h-32 bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                                                            <canvas class="image-preview-canvas w-full h-full object-cover"
                                                                                    data-image-key="{{ $imageKey }}"
                                                                                    style="display: none;"></canvas>
                                                                            <img src="{{ $imageUrl }}"
                                                                                 class="image-preview-img w-full h-full object-cover"
                                                                                 alt="Preview"
                                                                                 data-image-key="{{ $imageKey }}"
                                                                                 data-image-url="{{ $imageUrl }}"
                                                                                 @if(isset($archiveFile['file_path']))
                                                                                     data-file-path="{{ $archiveFile['file_path'] }}"
                                                                                     data-original-path="{{ $archiveFile['file_path'] }}"
                                                                                 @endif
                                                                                 data-spot-data=""
                                                                                 data-has-spot-data="false"
                                                                                 onload="if(window.renderPreviewWithSpotData){ window.renderPreviewWithSpotData(this); }">
                                                                        </div>
                                                                        @if($isPrimaryArchive)
                                                                            <div class="absolute bottom-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium z-10"
                                                                                 wire:key="primary-archive-badge-{{ $archiveFileId }}">
                                                                                <i class="fas fa-star mr-1"></i>
                                                                                Ana
                                                                            </div>
                                                                        @endif
                                                                    <div class="absolute top-2 right-2 flex gap-2 z-20">
                                                                        {{-- Düzenle butonu sadece ana görsel için göster --}}
                                                                        @if($isPrimaryArchive)
                                                                            <button type="button"
                                                                                    class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-blue-600 transition-colors duration-200 shadow-md image-edit-button"
                                                                                    data-image-key="{{ $imageKey }}"
                                                                                    data-image-url="{{ $imageUrl }}"
                                                                                    onclick="(function(){
                                                                                        const k=this.getAttribute('data-image-key');
                                                                                        const u=this.getAttribute('data-image-url');
                                                                                        let p=u.replace(/^https?:\/\/[^\/]+/,'');
                                                                                        p=p.replace(/^\/storage\//,'');
                                                                                        p=p.replace(/^storage\//,'');
                                                                                        const originalPath=p;
                                                                                        const spot={ image: { original: { path: originalPath } } };
                                                                                        if(window.openImageEditor){ window.openImageEditor(k,{ url:u, initialSpotData: spot }); }
                                                                                    }).call(this);"
                                                                                    title="Düzenle"
                                                                                    wire:key="edit-archive-button-{{ $archiveFileId }}">
                                                                                <i class="fas fa-edit"></i>
                                                                            </button>
                                                                        @endif
                                                                            <button type="button"
                                                                                    class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-red-600 transition-colors duration-200 shadow-md"
                                                                                    wire:click="removeSelectedArchiveFile({{ $archiveFile['id'] ?? 'null' }}, {{ $index }})"
                                                                                    title="Kaldır">
                                                                                <i class="fas fa-times"></i>
                                                                            </button>
                                                                    </div>
                                                                        <!-- Ana Görsel Seçimi -->
                                                                        <div class="mt-2">
                                                                            <label class="flex items-center">
                                                                                <input type="radio"
                                                                                       name="primaryArchiveFile"
                                                                                       id="primaryArchiveFile{{ $archiveFileId }}"
                                                                                       wire:model.live="primaryFileId"
                                                                                       value="{{ $archiveFileId }}"
                                                                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                                                                <span class="ml-2 text-sm text-gray-700">
                                                                                    <i class="fas fa-star mr-1"></i>
                                                                                    Ana Görsel
                                                                                </span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif

                            <!-- Görsel Önizleme -->
                            @php
                                $totalFiles = count($existingFiles ?? []) + count($uploadedFiles ?? []);
                            @endphp
                            @if(!empty($existingFiles) || !empty($uploadedFiles))
                                <div class="mt-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h6 class="text-lg font-medium text-gray-900">
                                            <i class="fas fa-eye mr-2 text-green-500"></i>
                                            Galeri Görselleri ({{ $totalFiles }} adet)
                                        </h6>
                                        <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-green-300 rounded-lg text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-150"
                                                @click="$refs.galleryFileInput.click()">
                                            <i class="fas fa-plus mr-2"></i>
                                            Daha Fazla Resim Ekle
                                        </button>
                                    </div>
                                     <div class="space-y-4" id="gallery-sortable" x-data="gallerySortable()">
                                        {{-- Mevcut Dosyalar (existingFiles) --}}
                                        @if(!empty($existingFiles))
                                            @foreach($existingFiles as $index => $file)
                                                @php
                                                    $fileId = $file['file_id'];
                                                    $imageKey = 'existing:' . $fileId;
                                                    $imageUrl = asset('storage/' . $file['path']);
                                                    $description = $file['description'] ?? '';
                                                    $altText = $file['alt_text'] ?? '';
                                                    $isPrimary = $file['primary'] ?? false;
                                                    // Get spot_data from post if available (only for primary image)
                                                    $spotData = null;
                                                    if ($isPrimary && $this->post->spot_data && isset($this->post->spot_data['image'])) {
                                                        $spotData = $this->post->spot_data['image'];
                                                    }
                                                @endphp
                                                <div wire:key="existing-file-{{ $fileId }}" class="gallery-item bg-gray-50 rounded-xl p-4 border border-gray-200 relative" data-index="{{ $index }}" id="gallery-item-{{ $fileId }}">
                                                    <!-- Sortable Handle -->
                                                    <div class="sortable-handle absolute top-2 left-2 bg-blue-500 text-white px-2 py-1 rounded-full text-xs font-medium z-10 cursor-grab hover:bg-blue-600 transition-colors">
                                                        <i class="fas fa-grip-vertical mr-1"></i>
                                                        {{ $index + 1 }}
                                                    </div>

                                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                                        <!-- Sol taraf: Resim -->
                                                        <div class="lg:col-span-1">
                                                            <div class="relative group">
                                                                <div class="image-preview-card"
                                                                     wire:ignore
                                                                     x-init="window.renderPreviewWithSpotData($el.querySelector('img.image-preview-img'))"
                                                                     data-image-key="{{ $imageKey }}">
                                                                    <div class="w-full h-32 bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                                                        {{-- Preview canvas for spot_data rendering --}}
                                                                        <canvas class="image-preview-canvas w-full h-full object-cover"
                                                                                data-image-key="{{ $imageKey }}"
                                                                                style="display: none;"></canvas>
                                                                        {{-- Fallback image --}}
                                                                        @php
                                                                            $spotArray = null;
                                                                            // Only load spot_data for primary image
                                                                            if ($isPrimary && $spotData && is_array($spotData)) {
                                                                                $spotArray = ['image' => $spotData];
                                                                            }
                                                                        @endphp
                                                                        <img src="{{ $imageUrl }}"
                                                                             class="image-preview-img w-full h-full object-cover"
                                                                             alt="Preview"
                                                                             data-image-key="{{ $imageKey }}"
                                                                             data-image-url="{{ $imageUrl }}"
                                                                             data-file-path="{{ $file['path'] }}"
                                                                             data-original-path="{{ $file['path'] }}"
                                                                             @if(!empty($spotArray))
                                                                                 data-spot-data='@json($spotArray)'
                                                                                 data-has-spot-data="true"
                                                                             @else
                                                                                 data-spot-data=""
                                                                                 data-has-spot-data="false"
                                                                             @endif
                                                                             data-file-id="{{ $fileId }}"
                                                                             onload="if(window.renderPreviewWithSpotData) { window.renderPreviewWithSpotData(this); } else { setTimeout(() => { if(window.renderPreviewWithSpotData) window.renderPreviewWithSpotData(this); }, 100); }"
                                                                             onerror="console.error('Image preview error:', this.src); this.style.backgroundColor='#f3f4f6';"
                                                                             loading="lazy">
                                                                    </div>
                                                                </div>
                                                                {{-- Top right corner buttons - outside wire:ignore so they update when primaryFileId changes --}}
                                                                <div class="absolute top-2 right-2 flex gap-2 z-20">
                                                                    {{-- Düzenle butonu sadece ana görsel için göster --}}
                                                                    @if($fileId === $primaryFileId || (string)$fileId === (string)$primaryFileId)
                                                                        <button type="button"
                                                                                class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-blue-600 transition-colors duration-200 shadow-md image-edit-button"
                                                                                data-image-key="{{ $imageKey }}"
                                                                                data-image-url="{{ $imageUrl }}"
                                                                                data-file-path="{{ $file['path'] }}"
                                                                                onclick="(function(){ const k=this.getAttribute('data-image-key'); const u=this.getAttribute('data-image-url'); const p=this.getAttribute('data-file-path'); if(window.openImageEditor){ window.openImageEditor(k,{url:u, filePath:p}); } }).call(this);"
                                                                                title="Düzenle"
                                                                                wire:key="edit-existing-button-{{ $fileId }}">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                    @endif
                                                                    <button type="button"
                                                                            class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-red-600 transition-colors duration-200 shadow-md"
                                                                            onclick="(function(){ var key='{{ $imageKey }}'; if (window.imageEditorUnregister) { try { window.imageEditorUnregister(key); } catch(e){} } }).call(this);"
                                                                            wire:click="removeExistingFile('{{ $fileId }}')"
                                                                            title="Kaldır">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                                {{-- Primary badge - outside wire:ignore so it updates --}}
                                                                @if($fileId === $primaryFileId || (string)$fileId === (string)$primaryFileId)
                                                                    <div class="absolute bottom-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium z-10"
                                                                         wire:key="primary-existing-badge-{{ $fileId }}">
                                                                        <i class="fas fa-star mr-1"></i>
                                                                        Ana
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <!-- Ana Görsel Seçimi -->
                                                            <div class="mt-3">
                                                                <label class="flex items-center">
                                                                    <input type="radio"
                                                                           name="primaryExistingFile"
                                                                           id="primaryExistingFile{{ $fileId }}"
                                                                           wire:model.live="primaryFileId"
                                                                           value="{{ $fileId }}"
                                                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                                                    <span class="ml-2 text-sm text-gray-700">
                                                                        <i class="fas fa-star mr-1"></i>
                                                                        Ana Görsel
                                                                    </span>
                                                                </label>
                                                            </div>
                                                            <div class="mt-2 text-xs text-gray-500 flex items-center gap-1 min-w-0" title="{{ $file['original_name'] ?? 'Unknown' }}">
                                                                <i class="fas fa-file flex-shrink-0"></i>
                                                                <span class="truncate block min-w-0">
                                                                    {{ $file['original_name'] ?? 'Unknown' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <!-- Sağ taraf: Açıklama -->
                                                        <div class="lg:col-span-2">
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                                    <i class="fas fa-edit mr-1"></i>
                                                                    Resim Açıklaması
                                                                </label>
                                                                <div wire:ignore>
                                                                    <textarea
                                                                        id="gallery-description-existing-{{ $fileId }}"
                                                                        class="trumbowyg block w-full text-sm border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                                                        data-editor="trumbowyg"
                                                                        data-file-id="{{ $fileId }}"
                                                                        data-field="description"
                                                                        rows="3"
                                                                        placeholder="Bu resim için açıklama yazın...">{!! $description !!}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                        {{-- Yeni Yüklenen Dosyalar (uploadedFiles) --}}
                                         @foreach($uploadedFiles as $fileId => $fileData)
                                             @php
                                                 $description = $fileData['description'] ?? '';
                                                 $altText = $fileData['alt_text'] ?? '';
                                                 $uploadedIndex = array_search($fileId, array_keys($uploadedFiles));
                                                 // Index should be after existingFiles count
                                                 $index = count($existingFiles ?? []) + $uploadedIndex;
                                             @endphp
                                                <div wire:key="file-{{ $fileId }}" class="gallery-item bg-gray-50 rounded-xl p-4 border border-gray-200 relative" data-index="{{ $index }}" id="gallery-item-{{ $fileId }}">
                                                    <!-- Sortable Handle -->
                                                    <div class="sortable-handle absolute top-2 left-2 bg-blue-500 text-white px-2 py-1 rounded-full text-xs font-medium z-10 cursor-grab hover:bg-blue-600 transition-colors">
                                                        <i class="fas fa-grip-vertical mr-1"></i>
                                                        {{ $index + 1 }}
                                                    </div>

                                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                                        <!-- Sol taraf: Resim -->
                                                        <div class="lg:col-span-1">
                                                            @php
                                                                $imageKey = 'temp:' . $fileId;
                                                                $imageUrl = $fileData['preview_url'] ?? '';
                                                            @endphp
                                                            <div class="relative group">
                                                                <div class="image-preview-card"
                                                                     wire:ignore
                                                                     x-init="window.renderPreviewWithSpotData($el.querySelector('img.image-preview-img'))"
                                                                     data-image-key="{{ $imageKey }}">
                                                                    <div class="w-full h-32 bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                                                        {{-- Preview canvas for spot_data rendering --}}
                                                                        <canvas class="image-preview-canvas w-full h-full object-cover"
                                                                                data-image-key="{{ $imageKey }}"
                                                                                style="display: none;"></canvas>
                                                                        {{-- Fallback image --}}
                                                                        @php
                                                                            $spotArray = null;
                                                                            if (isset($fileData['spot_data']) && is_array($fileData['spot_data'])) {
                                                                                $spotArray = ['image' => $fileData['spot_data']];
                                                                            } elseif (!empty($imageEditorData) && isset($imageEditorData[$fileId]) && is_array($imageEditorData[$fileId])) {
                                                                                $spotArray = ['image' => $imageEditorData[$fileId]];
                                                                            }
                                                                        @endphp
                                                                        <img src="{{ $imageUrl }}"
                                                                             class="image-preview-img w-full h-full object-cover"
                                                                             alt="Preview"
                                                                             data-image-key="{{ $imageKey }}"
                                                                             data-image-url="{{ $imageUrl }}"
                                                                             @if(isset($fileData['file_path']))
                                                                                 data-file-path="{{ $fileData['file_path'] }}"
                                                                                 data-original-path="{{ $fileData['file_path'] }}"
                                                                             @endif
                                                                             @if(!empty($spotArray))
                                                                                 data-spot-data='@json($spotArray)'
                                                                                 data-has-spot-data="true"
                                                                             @else
                                                                                 data-spot-data=""
                                                                                 data-has-spot-data="false"
                                                                             @endif
                                                                             data-file-id="{{ $fileId }}"
                                                                             onload="if(window.renderPreviewWithSpotData) { window.renderPreviewWithSpotData(this); } else { setTimeout(() => { if(window.renderPreviewWithSpotData) window.renderPreviewWithSpotData(this); }, 100); }"
                                                                             onerror="console.error('Image preview error:', this.src); this.style.backgroundColor='#f3f4f6';"
                                                                             loading="lazy">
                                                                    </div>
                                                                </div>
                                                                {{-- Top right corner buttons - outside wire:ignore so they update when primaryFileId changes --}}
                                                                <div class="absolute top-2 right-2 flex gap-2 z-20">
                                                                    {{-- Düzenle butonu sadece ana görsel için göster --}}
                                                                    @if($fileId === $primaryFileId || (string)$fileId === (string)$primaryFileId)
                                                                        <button type="button"
                                                                                class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-blue-600 transition-colors duration-200 shadow-md image-edit-button"
                                                                                data-image-key="{{ $imageKey }}"
                                                                                data-image-url="{{ $imageUrl }}"
                                                                                onclick="(function(){ const k=this.getAttribute('data-image-key'); const u=this.getAttribute('data-image-url'); if(window.openImageEditor){ window.openImageEditor(k,{url:u}); } }).call(this);"
                                                                                title="Düzenle"
                                                                                wire:key="edit-button-{{ $fileId }}">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                    @endif
                                                                    <button type="button"
                                                                            class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-red-600 transition-colors duration-200 shadow-md"
                                                                            onclick="(function(){ var key='{{ $imageKey }}'; if (window.imageEditorUnregister) { try { window.imageEditorUnregister(key); } catch(e){} } }).call(this);"
                                                                            wire:click="removeFile('{{ $fileId }}')"
                                                                            title="Kaldır">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                                {{-- Primary badge - outside wire:ignore so it updates --}}
                                                                @if($fileId === $primaryFileId || (string)$fileId === (string)$primaryFileId)
                                                                    <div class="absolute bottom-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium z-10"
                                                                         wire:key="primary-badge-{{ $fileId }}">
                                                                        <i class="fas fa-star mr-1"></i>
                                                                        Ana
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <!-- Ana Görsel Seçimi -->
                                                            <div class="mt-3">
                                                                <label class="flex items-center">
                                                                     <input type="radio"
                                                                            name="primaryFile"
                                                                            id="primaryFile{{ $fileId }}"
                                                                            wire:model.live="primaryFileId"
                                                                            value="{{ $fileId }}"
                                                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                                                    <span class="ml-2 text-sm text-gray-700">
                                                                        <i class="fas fa-star mr-1"></i>
                                                                        Ana Görsel
                                                                    </span>
                                                                </label>
                                                            </div>


                                                            <div class="mt-2 text-xs text-gray-500 flex items-center gap-1 min-w-0" title="{{ $fileData['name'] ?? '' }}">
                                                                <i class="fas fa-file flex-shrink-0"></i>
                                                                <span class="truncate block min-w-0">
                                                                    {{ $fileData['name'] ?? '' }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <!-- Sağ taraf: Zengin Metin Editörü -->
                                                        <div class="lg:col-span-2">
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                                    <i class="fas fa-edit mr-1"></i>
                                                                    Resim Açıklaması
                                                                </label>
                                                                <div wire:ignore>
                                                                    <textarea
                                                                        id="gallery-description-{{ $fileId }}"
                                                                        class="trumbowyg block w-full text-sm border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                                                        data-editor="trumbowyg"
                                                                        data-file-id="{{ $fileId }}"
                                                                        data-field="description"
                                                                        rows="3"
                                                                        placeholder="Bu resim için açıklama yazın..."
                                                                    >{!! $description ?? '' !!}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                         @endforeach

                                     </div>

                                    <!-- Galeri Altında Daha Fazla Resim Ekle Butonu -->
                                    <div class="mt-4 p-4 border-2 border-dashed border-green-300 rounded-lg bg-green-50">
                                        <div class="text-center">
                                            <button type="button"
                                                    class="inline-flex items-center px-4 py-2 border border-green-300 rounded-lg text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-150"
                                                    @click="$refs.galleryFileInput.click()"
                                                    wire:loading.attr="disabled"
                                                    wire:target="newFiles">
                                                <span wire:loading.remove wire:target="newFiles">
                                                    <i class="fas fa-plus mr-2"></i>
                                                    Daha Fazla Resim Ekle
                                                </span>
                                                <span wire:loading wire:target="newFiles" class="flex items-center">
                                                    <div class="animate-spin inline-block w-4 h-4 border-2 border-green-500 border-t-transparent rounded-full mr-2"></div>
                                                    Yükleniyor...
                                                </span>
                                            </button>
                                            <p class="text-xs text-green-600 mt-2">
                                                Yeni resimler mevcut galeriye eklenecek
                                            </p>
                                        </div>
                                    </div>

                                     @error('primaryFileId')
                                        <div class="mt-2 text-sm text-red-600">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            @endif

                            <!-- Hidden File Input for Gallery -->
                            <input type="file"
                                   x-ref="galleryFileInput"
                                   wire:model.live="newFiles"
                                   multiple
                                   accept="image/*"
                                   class="hidden">
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 shadow-lg hover:shadow-xl transition-all duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Galeriyi Güncelle
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
                        <i class="fas fa-cog mr-2 text-green-500"></i>
                        Yayın Ayarları
                    </h3>
                    <div class="space-y-4">
                        <!-- Pozisyon -->
                        <div>
                            <label for="post_position" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-layers mr-1 text-green-500"></i>
                                Pozisyon *
                            </label>
                            <select wire:model.live="post_position"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 sm:text-sm"
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
                                <i class="fas fa-flag mr-1 text-green-500"></i>
                                Durum *
                            </label>
                            <select wire:model.live="status"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 sm:text-sm"
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
                                <i class="fas fa-calendar mr-1 text-green-500"></i>
                                Yayın Tarihi
                            </label>
                            <input type="datetime-local"
                                   wire:model.live="published_date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                   id="published_date"
                                   value="{{ $published_date }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kategoriler -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-tags mr-2 text-green-500"></i>
                        Kategoriler *
                    </h3>
                    <div>
                        <select wire:model.live="categoryIds"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 sm:text-sm @error('categoryIds') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                id="categoryIds"
                                multiple
                                required>
                            @foreach($categories as $category)
                                <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('categoryIds')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if(empty($categoryIds) || count($categoryIds) === 0)
                            <p class="mt-1 text-sm text-yellow-600">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                En az bir kategori seçmelisiniz.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Yönlendirme Linki -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-external-link-alt mr-2 text-green-500"></i>
                        Yönlendirme Linki
                    </h3>
                    <div>
                        <label for="redirect_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL
                        </label>
                        <input type="url"
                               wire:model.live="redirect_url"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 sm:text-sm"
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
                        <i class="fas fa-tag mr-2 text-green-500"></i>
                        Etiketler
                    </h3>
                    <div x-data="tagsInput($wire.tagsInput || '')" class="space-y-3">
                        <!-- Mevcut Etiketler -->
                        <div class="flex flex-wrap gap-2" x-show="tags.length > 0">
                            <template x-for="(tag, index) in tags" :key="index">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                                    <span x-text="tag"></span>
                                    <button type="button"
                                            @click="removeTag(index)"
                                            class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-green-200 transition-colors">
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
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                   placeholder="Etiket ekle...">
                            <button type="button"
                                    @click="addTag()"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
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
                        <i class="fas fa-eye mr-2 text-green-500"></i>
                        Görünürlük Ayarları
                    </h3>
                    <div class="space-y-4">
                        <!-- Yorumlara izin ver -->
                        <label class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="is_comment"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-comment mr-1"></i>
                                Yorumlara izin ver
                            </span>
                        </label>

                        <!-- Ana sayfada göster -->
                        <label class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="is_mainpage"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-home mr-1"></i>
                                Ana sayfada göster
                            </span>
                        </label>

                        <!-- Bülten'de göster -->
                        <label class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="in_newsletter"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-envelope mr-1"></i>
                                Bülten'de göster
                            </span>
                        </label>

                        <!-- Reklam gösterme -->
                        <label class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="no_ads"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
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

    {{-- Editors lifecycle is automatically mounted via app.js --}}
</div>
