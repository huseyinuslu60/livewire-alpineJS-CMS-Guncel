<div>
    <!-- Modern Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-plus text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Yeni Kategori Oluştur</h2>
                        <p class="text-gray-600">Sisteme yeni kategori ekleyin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('categories.index') }}"
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
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <i class="fas fa-edit text-blue-500 mr-2"></i>
                <h3 class="text-lg font-semibold text-gray-900">Kategori Bilgileri</h3>
            </div>
        </div>
        <div class="p-6">

            <form wire:submit.prevent="save">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori Adı <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               wire:model.live="name"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               id="name"
                               placeholder="Kategori adını giriniz..."
                               required>
                        <x-validation-error field="name" />
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                            URL Slug
                        </label>
                        <div class="flex">
                            <input type="text"
                                   wire:model="slug"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   id="slug"
                                   placeholder="kategori-adi"
                                   {{ !$isSlugEditable ? 'readonly' : '' }}>
                            <button type="button"
                                    wire:click="toggleSlugEdit"
                                    class="px-3 py-2 border border-l-0 border-gray-300 rounded-r-lg bg-gray-50 hover:bg-gray-100 transition-colors duration-150"
                                    title="{{ $isSlugEditable ? 'Kilitle' : 'Düzenle' }}">
                                <i class="fas fa-{{ $isSlugEditable ? 'lock' : 'edit' }} text-gray-600"></i>
                            </button>
                        </div>
                        <x-validation-error field="slug" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori Tipi <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.defer="type"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                id="type"
                                required>
                            <option value="">Kategori tipini seçiniz</option>
                            <option value="news">Haber</option>
                            <option value="gallery">Galeri</option>
                            <option value="video">Video</option>
                        </select>
                        <x-validation-error field="type" />
                    </div>

                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Üst Kategori
                        </label>
                        <select wire:model.defer="parent_id"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                id="parent_id">
                            <option value="">Ana Kategori</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->category_id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                        <x-validation-error field="parent_id" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Durum <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.defer="status"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                id="status"
                                required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Pasif</option>
                            <option value="draft">Taslak</option>
                        </select>
                        <x-validation-error field="status" />
                    </div>

                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">
                            Sıralama
                        </label>
                        <input type="number"
                               wire:model.defer="weight"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               id="weight"
                               min="0"
                               placeholder="0">
                        <x-validation-error field="weight" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Menü Ayarları
                        </label>
                        <div class="flex items-center mt-2">
                            <input type="checkbox"
                                   wire:model.defer="show_in_menu"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   id="show_in_menu">
                            <label for="show_in_menu" class="ml-2 block text-sm text-gray-900">
                                Menüde Göster
                            </label>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                            Meta Başlık
                        </label>
                        <input type="text"
                               wire:model.defer="meta_title"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               id="meta_title"
                               placeholder="SEO için meta başlık...">
                        <x-validation-error field="meta_title" />
                    </div>

                    <div>
                        <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                            Meta Açıklama
                        </label>
                        <textarea wire:model.defer="meta_description"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                  id="meta_description"
                                  rows="3"
                                  placeholder="SEO için meta açıklama..."></textarea>
                        <x-validation-error field="meta_description" />
                    </div>

                    <div>
                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                            Meta Anahtar Kelimeler
                        </label>
                        <input type="text"
                               wire:model.defer="meta_keywords"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               id="meta_keywords"
                               placeholder="anahtar, kelime, virgül, ile, ayrılmış">
                        <x-validation-error field="meta_keywords" />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 mt-6">
                    <a href="{{ route('categories.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Geri Dön
                    </a>
                    <button type="submit"
                            wire:loading.attr="disabled"
                            @if($isLoading) disabled @endif
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all duration-200">
                        @if(!$isLoading)
                            <i class="fas fa-save mr-2"></i>
                            Kategori Oluştur
                        @else
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Kaydediliyor...
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Categories modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Categories/resources/assets/sass/app.scss', 'Modules/Categories/resources/assets/js/app.js'])
</div>
