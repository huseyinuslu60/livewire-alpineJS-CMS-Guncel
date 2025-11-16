<div x-data="postsForm()">
    <!-- Success Message -->
    <x-success-message :message="$successMessage" />

    <!-- Modern Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        @if($this->post->post_type === 'gallery')
                            <i class="fas fa-images text-white text-xl"></i>
                        @elseif($this->post->post_type === 'video')
                            <i class="fas fa-video text-white text-xl"></i>
                        @else
                            <i class="fas fa-edit text-white text-xl"></i>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">
                            @if($this->post->post_type === 'gallery')
                                Galeri Düzenle
                            @elseif($this->post->post_type === 'video')
                                Video Düzenle
                            @else
                                Haber Düzenle
                            @endif
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
                    <form wire:submit="updatePost">
                        <!-- Başlık -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-heading mr-1 text-orange-500"></i>
                                Başlık *
                            </label>
                            <input type="text"
                                   wire:model.live="title"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
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
                                <i class="fas fa-link mr-1 text-orange-500"></i>
                                Slug
                            </label>
                            <input type="text"
                                   wire:model.live="slug"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('slug') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                   id="slug"
                                   placeholder="URL slug'ı (otomatik oluşturulur)">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Özet -->
                        <div class="mb-6">
                            <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file-alt mr-1 text-orange-500"></i>
                                Özet
                            </label>
                            <textarea wire:model.live="summary"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('summary') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                      id="summary"
                                      rows="3"
                                      placeholder="Haber özetini girin..."></textarea>
                            @error('summary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Galeri Görselleri (Sadece Galeri Tipi İçin) -->
                        @if($this->post->post_type === 'gallery')
                            <div class="mb-6" x-data="{ focusNew(){ this.$nextTick(() => { setTimeout(() => { const items = document.querySelectorAll('#gallery-sortable .gallery-item'); if(items && items.length){ const last = items[items.length - 1]; last.scrollIntoView({ behavior: 'smooth', block: 'center' }); last.classList.add('ring-2','ring-orange-400'); setTimeout(() => last.classList.remove('ring-2','ring-orange-400'), 1500); } }, 400); }); } }" x-on:livewire-upload-finish.window="focusNew()">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-images mr-1 text-orange-500"></i>
                                    Galeri Görselleri
                                </label>

                                <!-- Yeni Resim Ekleme - Create Sayfası Tasarımı -->
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition-colors duration-200 relative">
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
                                            <div class="animate-spin inline-block w-10 h-10 border-4 border-orange-500 border-t-transparent rounded-full"></div>
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

                                <!-- Mevcut Galeri Görselleri -->
                                @if(!empty($existingFiles))
                                    <div class="mt-6">
                                        <div class="flex justify-between items-center mb-4">
                                            <h6 class="text-lg font-medium text-gray-900">
                                                <i class="fas fa-eye mr-2 text-orange-500"></i>
                                                Galeri Görselleri ({{ count($existingFiles) }} adet)
                                            </h6>
                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-2 border border-orange-300 rounded-lg text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 transition-colors duration-150"
                                                    @click="$refs.galleryFileInput.click()">
                                                <i class="fas fa-plus mr-2"></i>
                                                Daha Fazla Resim Ekle
                                            </button>
                                        </div>
                                        <div class="space-y-4" id="gallery-sortable" x-data="gallerySortable()">
                                            @foreach($existingFiles as $index => $file)
                                                <div class="gallery-item bg-gray-50 rounded-xl p-4 border border-gray-200 relative" data-index="{{ $index }}" data-file-id="{{ $file['file_id'] }}" id="gallery-item-{{ $file['file_id'] }}" wire:key="gallery-item-{{ $file['file_id'] }}">
                                                    <!-- Sortable Handle -->
                                                    <div class="sortable-handle absolute top-2 left-2 bg-orange-500 text-white px-2 py-1 rounded-full text-xs font-medium z-10 cursor-grab hover:bg-orange-600 transition-colors">
                                                        <i class="fas fa-grip-vertical mr-1"></i>
                                                        {{ $index + 1 }}
                                                    </div>

                                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                                        <!-- Sol taraf: Resim -->
                                                        <div class="lg:col-span-1">
                                                            <div class="relative">
                                                                <div class="relative group">
                                                                    <div class="w-full h-32 bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                                                        <img src="{{ asset('storage/' . $file['path']) }}"
                                                                             class="w-full h-full object-cover"
                                                                             alt="{{ $file['alt_text'] ?? 'Gallery Image' }}">

                                                                    </div>
                                                                    {{-- Top right corner buttons --}}
                                                                    <div class="absolute top-2 right-2 flex gap-2">
                                                                        <button type="button"
                                                                                class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-blue-600 transition-colors duration-200 shadow-md"
                                                                                onclick="if (window.openImageEditor) { window.openImageEditor('{{ $file['file_id'] }}', '{{ asset('storage/' . $file['path']) }}'); }"
                                                                                title="Düzenle">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button type="button"
                                                                                class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-red-600 transition-colors duration-200 shadow-md"
                                                                                wire:click="removeExistingFile({{ $index }})"
                                                                                title="Kaldır">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                @if($file['primary'] === true)
                                                                    <div class="absolute bottom-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium">
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
                                                                           id="primaryFile{{ $index }}"
                                                                           wire:model.live="primaryFileId"
                                                                           value="{{ $file['file_id'] }}"
                                                                           class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300">
                                                                    <span class="ml-2 text-sm text-gray-700">
                                                                        <i class="fas fa-star mr-1"></i>
                                                                        Ana Görsel
                                                                    </span>
                                                                </label>
                                                            </div>


                                                            <div class="mt-2 text-xs text-gray-500">
                                                                <i class="fas fa-file mr-1"></i>
                                                                {{ $file['original_name'] ?? 'Unknown' }}
                                                            </div>
                                                        </div>

                                                        <!-- Sağ taraf: Açıklama (Create sayfasındaki gibi) -->
                                                        <div class="lg:col-span-2">
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                                    <i class="fas fa-edit mr-1"></i>
                                                                    Resim Açıklaması
                                                                </label>
                                                                <div wire:ignore>
                                                                    <trix-editor
                                                                         id="description-editor-{{ $file['file_id'] }}"
                                                                         class="trix-editor block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                                                                         placeholder="Bu resim için detaylı açıklama yazın..."
                                                                         data-description="{{ htmlspecialchars($file['description'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                                         data-file-id="{{ $file['file_id'] }}"></trix-editor>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            {{-- Galeri Altında Daha Fazla Resim Ekle Butonu --}}
                                            <div class="mt-4 p-4 border-2 border-dashed border-orange-300 rounded-lg bg-orange-50">
                                                <div class="text-center">
                                                    <button type="button"
                                                            class="inline-flex items-center px-4 py-2 border border-orange-300 rounded-lg text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 transition-colors duration-150"
                                                            @click="$refs.galleryFileInput.click()"
                                                            wire:loading.attr="disabled"
                                                            wire:target="newFiles">
                                                        <span wire:loading.remove wire:target="newFiles">
                                                            <i class="fas fa-plus mr-2"></i>
                                                            Daha Fazla Resim Ekle
                                                        </span>
                                                        <span wire:loading wire:target="newFiles" class="flex items-center">
                                                            <div class="animate-spin inline-block w-4 h-4 border-2 border-orange-500 border-t-transparent rounded-full mr-2"></div>
                                                            Yükleniyor...
                                                        </span>
                                                    </button>
                                                    <p class="text-xs text-orange-600 mt-2">
                                                        Yeni resimler mevcut galeriye eklenecek
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- Haber/Video için Resim Güncelleme -->
                            @if(in_array($this->post->post_type, ['news', 'video']))
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-image mr-1 text-orange-500"></i>
                                        Resim Güncelle
                                    </label>

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
                                            @if($this->post->primaryFile && empty($newFiles))
                                                <button type="button"
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
                                        <div class="w-64 h-40 bg-gray-100 rounded-lg border border-gray-200 overflow-hidden mx-auto relative">
                                            <!-- Resim Ön İzleme -->
                                            @if(!empty($newFiles))
                                                @php
                                                    $latestFile = collect($newFiles)->last();
                                                @endphp
                                                @if($latestFile)
                                                    <img src="{{ $latestFile->temporaryUrl() }}"
                                                         class="w-full h-full object-cover"
                                                         alt="Yeni seçilen resim">
                                                @endif
                                            @elseif($this->post->primaryFile)
                                                <div class="relative w-full h-full group">
                                                    <img src="{{ asset('storage/' . $this->post->primaryFile->file_path) }}"
                                                         class="w-full h-full object-cover"
                                                         alt="{{ $this->post->primaryFile->alt_text ?? 'Current Image' }}">

                                                    {{-- Top right corner button --}}
                                                    <div class="absolute top-2 right-2">
                                                        <button type="button"
                                                                class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-blue-600 transition-colors duration-200 shadow-md"
                                                                onclick="if (window.openImageEditor) { window.openImageEditor('{{ $this->post->primaryFile->file_id }}', '{{ asset('storage/' . $this->post->primaryFile->file_path) }}'); }"
                                                                title="Düzenle">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <i class="fas fa-image text-2xl"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Yeni Resim Yükleme -->
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition-colors duration-200 relative">
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
                                                <div class="animate-spin inline-block w-10 h-10 border-4 border-orange-500 border-t-transparent rounded-full"></div>
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

                            <!-- Normal İçerik (Galeri Değilse) -->
                        <div class="mb-6">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-1 text-orange-500"></i>
                                İçerik *
                            </label>
                            <div wire:ignore>
                                <textarea wire:model.live="content"
                                          class="trumbowyg block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('content') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                          id="content"
                                          rows="8"
                                          placeholder="Haber içeriğini girin..."
                                          required></textarea>
                            </div>
                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        @if($this->post->post_type === 'video')
                            <!-- Video Embed Kodu -->
                            <div class="mb-6">
                                <label for="embed_code" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-code mr-1 text-orange-500"></i>
                                    Video Embed Kodu *
                                </label>
                                <textarea wire:model.live="embed_code"
                                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('embed_code') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
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
                        @endif

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 shadow-lg hover:shadow-xl transition-all duration-200">
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
                        <i class="fas fa-cog mr-2 text-orange-500"></i>
                        Yayın Ayarları
                    </h3>
                    <div class="space-y-4">
                        <!-- Durum -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-flag mr-1 text-orange-500"></i>
                                Durum *
                        </label>
                            <select wire:model.live="status"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
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
                                <i class="fas fa-layers mr-1 text-orange-500"></i>
                                Pozisyon *
                        </label>
                            <select wire:model.live="post_position"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                                    id="post_position"
                                    required>
                                @foreach($postPositions as $position)
                                    <option value="{{ $position }}">{{ \Modules\Posts\Models\Post::POSITION_LABELS[$position] ?? ucfirst($position) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Yayın Tarihi -->
                        <div>
                            <label for="published_date" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1 text-orange-500"></i>
                                Yayın Tarihi
                        </label>
                            <input type="datetime-local"
                                   wire:model.live="published_date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                                   id="published_date">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kategoriler -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-tags mr-2 text-orange-500"></i>
                        Kategoriler
                    </h3>
                    <div wire:ignore>
                        <select wire:model.live="categoryIds"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
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
                        <i class="fas fa-external-link-alt mr-2 text-orange-500"></i>
                        Yönlendirme Linki
                    </h3>
                    <div>
                        <label for="redirect_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL
                        </label>
                        <input type="url"
                               wire:model.live="redirect_url"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
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
                        <i class="fas fa-tag mr-2 text-orange-500"></i>
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
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                                   placeholder="Etiket ekle...">
                            <button type="button"
                                    @click="addTag()"
                                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
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
                        <i class="fas fa-eye mr-2 text-orange-500"></i>
                        Görünürlük Ayarları
                    </h3>
                    <div class="space-y-4">
                        <!-- Yorumlara izin ver -->
                        <label for="is_comment" class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="is_comment"
                                   id="is_comment"
                                   class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
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
                                   class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
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
                                   class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
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
                                   class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
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
                        <i class="fas fa-chart-bar mr-2 text-orange-500"></i>
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
                        <i class="fas fa-clock mr-2 text-orange-500"></i>
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
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="featuredEndsAt" class="block text-sm font-medium text-gray-700 mb-2">
                                Bitiş Tarihi
                            </label>
                            <input type="datetime-local"
                                   wire:model.live="featuredEndsAt"
                                   id="featuredEndsAt"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
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

    {{-- Hidden File Input for Gallery --}}
    <input type="file"
           x-ref="galleryFileInput"
           wire:model="newFiles"
           multiple
           accept="image/*"
           class="hidden">

    {{-- Posts modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Posts/resources/assets/sass/app.scss', 'Modules/Posts/resources/assets/js/app.js'])

    {{-- Image Editor Modal --}}
    <div x-data="imageEditor()">
        @include('partials.image-editor-modal')
    </div>
</div>
