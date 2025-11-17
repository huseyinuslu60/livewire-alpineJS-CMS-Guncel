<div x-data="{ focusNew(){ this.$nextTick(() => { setTimeout(() => { const items = document.querySelectorAll('#gallery-sortable .gallery-item'); if(items && items.length){ const last = items[items.length - 1]; last.scrollIntoView({ behavior: 'smooth', block: 'center' }); last.classList.add('ring-2','ring-orange-400'); setTimeout(() => last.classList.remove('ring-2','ring-orange-400'), 1500); } }, 400); }); } }" x-on:livewire-upload-finish.window="focusNew()">
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
                                            id="gallery-description-{{ $file['file_id'] }}"
                                            class="trumbowyg block w-full text-sm border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                                            data-editor="trumbowyg"
                                            data-file-id="{{ $file['file_id'] }}"
                                            data-field="description"
                                            rows="3"
                                            placeholder="Bu resim için açıklama yazın..."
                                        >{!! $file['description'] ?? '' !!}</textarea>
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

    {{-- Hidden File Input for Gallery --}}
    <input type="file"
           x-ref="galleryFileInput"
           wire:model="newFiles"
           multiple
           accept="image/*"
           class="hidden">
</div>

