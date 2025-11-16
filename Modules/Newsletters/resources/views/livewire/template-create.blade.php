<div>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Modern Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center mb-4 lg:mb-0">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-plus text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-1">Yeni Template Oluştur</h2>
                            <p class="text-gray-600">Newsletter template'i oluşturun ve özelleştirin</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('newsletters.templates.index') }}" 
                           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Geri Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
            <!-- Form -->
            <div class="xl:col-span-3">
                <form wire:submit.prevent="save" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Temel Bilgiler</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template Adı *</label>
                                <input type="text" wire:model.live="name" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Slug *</label>
                                <input type="text" wire:model.live="slug" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('slug') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                            <textarea wire:model.live="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Template açıklaması..."></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- HTML Templates -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">HTML Template'leri</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Header HTML *</label>
                                <div wire:ignore>
                                    <textarea wire:model.live="header_html" rows="6"
                                              class="trumbowyg w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Header HTML kodu..."></textarea>
                                </div>
                                @error('header_html') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Content HTML *</label>
                                <div wire:ignore>
                                    <textarea wire:model.live="content_html" rows="6"
                                              class="trumbowyg w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Content HTML kodu..."></textarea>
                                </div>
                                @error('content_html') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Footer HTML *</label>
                                <div wire:ignore>
                                    <textarea wire:model.live="footer_html" rows="6"
                                              class="trumbowyg w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Footer HTML kodu..."></textarea>
                                </div>
                                @error('footer_html') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Colors -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Renk Ayarları</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ana Renk</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" wire:model.live="primary_color" 
                                           class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                                    <input type="text" wire:model.live="primary_color" 
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                @error('primary_color') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">İkincil Renk</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" wire:model.live="secondary_color" 
                                           class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                                    <input type="text" wire:model.live="secondary_color" 
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                @error('secondary_color') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Metin Rengi</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" wire:model.live="text_color" 
                                           class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                                    <input type="text" wire:model.live="text_color" 
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                @error('text_color') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Arka Plan Rengi</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" wire:model.live="background_color" 
                                           class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                                    <input type="text" wire:model.live="background_color" 
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                @error('background_color') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Ayarlar</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_active" id="is_active" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Aktif Template
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sıralama</label>
                                <input type="number" wire:model="sort_order" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('sort_order') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('newsletters.templates.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            İptal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Template Oluştur
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preview -->
            <div class="xl:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Canlı Önizleme</h3>
                    
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                </div>
                                <div class="text-xs text-gray-500">Template Preview</div>
                            </div>
                        </div>
                        <div class="p-4 max-h-[1200px] overflow-y-auto">
                            <div class="newsletter-preview-content text-sm" style="transform: scale(0.7); transform-origin: top left; width: 143%;">
                                @if($this->preview_header_html)
                                    {{-- sanitize edilmiş değişken --}}
                                    {!! \App\Support\Sanitizer::sanitizeHtml($this->preview_header_html) !!}
                                @endif
                                
                                @if($this->preview_content_html)
                                    {{-- sanitize edilmiş değişken --}}
                                    {!! \App\Support\Sanitizer::sanitizeHtml($this->preview_content_html) !!}
                                @endif
                                
                                @if($this->preview_footer_html)
                                    {{-- sanitize edilmiş değişken --}}
                                    {!! \App\Support\Sanitizer::sanitizeHtml($this->preview_footer_html) !!}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-check mr-2"></i>{{ session('success') }}
        </div>
    @endif
</div>
