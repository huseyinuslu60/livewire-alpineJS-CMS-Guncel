@if($isGallery)
    <!-- Galeri Görselleri -->
    <div class="mb-6" x-data="{ focusNew(){ this.$nextTick(() => { setTimeout(() => { const items = document.querySelectorAll('#gallery-sortable .gallery-item'); if(items && items.length){ const last = items[items.length - 1]; last.scrollIntoView({ behavior: 'smooth', block: 'center' }); last.classList.add('ring-2','ring-orange-400'); setTimeout(() => last.classList.remove('ring-2','ring-orange-400'), 1500); } }, 400); }); } }" x-on:livewire-upload-finish.window="focusNew()">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-images mr-1 text-orange-500"></i>
            Galeri Görselleri
        </label>
        
        <!-- Yeni Resim Ekleme -->
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
                        <div class="gallery-item bg-gray-50 rounded-xl p-4 border border-gray-200 relative" data-index="{{ $index }}" data-file-id="{{ $file['file_id'] }}" id="gallery-item-{{ $file['file_id'] }}">
                            <!-- Sortable Handle -->
                            <div class="sortable-handle absolute top-2 left-2 bg-orange-500 text-white px-2 py-1 rounded-full text-xs font-medium z-10 cursor-grab hover:bg-orange-600 transition-colors">
                                <i class="fas fa-grip-vertical mr-1"></i>
                                {{ $index + 1 }}
                            </div>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <!-- Sol taraf: Resim -->
                                <div class="lg:col-span-1">
                                    <div class="relative">
                                        <div class="w-full h-32 bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                            <img src="{{ asset('storage/' . $file['path']) }}" 
                                                 class="w-full h-full object-cover" 
                                                 alt="{{ $file['alt_text'] ?? 'Gallery Image' }}">
                                        </div>
                                        <div class="absolute top-2 right-2">
                                            <button type="button" 
                                                    class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-red-600 transition-colors duration-200"
                                                    wire:click="removeExistingFile({{ $index }})" 
                                                    title="Kaldır">
                                                <i class="fas fa-times"></i>
                                            </button>
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
                                
                                <!-- Sağ taraf: Açıklama -->
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
                                                 data-description="{{ $file['description'] ?? '' }}"></trix-editor>
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

    {{-- Hidden File Input for Gallery --}}
    <input type="file" 
           x-ref="galleryFileInput" 
           wire:model="newFiles" 
           multiple 
           accept="image/*" 
           class="hidden">
@else
    <!-- Haber/Video için Resim Güncelleme -->
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
                    @elseif($post->primaryFile)
                        Mevcut Resim
                    @else
                        Resim Önizleme
                    @endif
                </span>
                @if($post->primaryFile && empty($newFiles))
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
                @if(!empty($newFiles))
                    @php
                        $latestFile = collect($newFiles)->last();
                    @endphp
                    @if($latestFile)
                        <img src="{{ $latestFile->temporaryUrl() }}" 
                             class="w-full h-full object-cover" 
                             alt="Yeni seçilen resim">
                    @endif
                @elseif($post->primaryFile)
                    <img src="{{ asset('storage/' . $post->primaryFile->file_path) }}" 
                         class="w-full h-full object-cover" 
                         alt="{{ $post->primaryFile->alt_text ?? 'Current Image' }}">
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
                    @elseif($post->primaryFile)
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

