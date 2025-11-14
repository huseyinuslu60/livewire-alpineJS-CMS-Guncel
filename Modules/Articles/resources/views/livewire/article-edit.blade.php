<div x-data="articleForm()">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div x-show="showSuccess" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 shadow-sm">
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
             class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 shadow-sm">
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

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-edit text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Makale Düzenle</h1>
                        <p class="text-gray-600 mt-1">Makale bilgilerini güncelleyin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('articles.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="update" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sol Kolon - Ana Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Başlık -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-heading mr-2 text-orange-500"></i>
                        Makale Bilgileri
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">
                                <i class="fas fa-heading mr-2 text-orange-500"></i>
                                Makale Başlığı
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   wire:model.defer="title" 
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                                   id="title" 
                                   placeholder="Makale başlığını girin..." 
                                   required>
                            @error('title')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="summary" class="block text-sm font-semibold text-gray-900 mb-2">
                                <i class="fas fa-align-left mr-2 text-orange-500"></i>
                                Özet
                            </label>
                            <textarea wire:model.defer="summary" 
                                      rows="3"
                                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm @error('summary') border-red-300 @enderror" 
                                      id="summary" 
                                      placeholder="Makale özetini girin..."></textarea>
                            @error('summary')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="article_text" class="block text-sm font-semibold text-gray-900 mb-2">
                                <i class="fas fa-file-text mr-2 text-orange-500"></i>
                                İçerik
                                <span class="text-red-500">*</span>
                            </label>
                            <div wire:ignore>
                                <textarea wire:model.defer="article_text" 
                                          rows="10"
                                          class="trumbowyg block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm @error('article_text') border-red-300 @enderror" 
                                          id="article_text" 
                                          placeholder="Makale içeriğini girin..." 
                                          required></textarea>
                            </div>
                            @error('article_text')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sağ Kolon - Ayarlar -->
            <div class="space-y-6">
                <!-- Durum Ayarları -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-cog mr-2 text-orange-500"></i>
                        Durum Ayarları
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-3">Durum</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" 
                                           wire:model.defer="status" 
                                           value="draft" 
                                           class="w-4 h-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                    <span class="ml-2 text-sm text-gray-700">Pasif</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           wire:model.defer="status" 
                                           value="published" 
                                           class="w-4 h-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           wire:model.defer="status" 
                                           value="scheduled" 
                                           class="w-4 h-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                    <span class="ml-2 text-sm text-gray-700">Zamanlanmış</span>
                                </label>
                            </div>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       wire:model.defer="show_on_mainpage" 
                                       class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                <span class="ml-2 text-sm text-gray-700">Ana sayfada göster</span>
                            </label>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       wire:model.defer="is_commentable" 
                                       class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                <span class="ml-2 text-sm text-gray-700">Yorumlara izin ver</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Yayın Tarihi -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-calendar mr-2 text-orange-500"></i>
                        Yayın Tarihi
                    </h3>
                    
                    <div>
                        <label for="published_at" class="block text-sm font-semibold text-gray-900 mb-2">
                            Yayın Tarihi ve Saati
                        </label>
                        <input type="datetime-local" 
                               wire:model.defer="published_at" 
                               class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm @error('published_at') border-red-300 @enderror" 
                               id="published_at">
                        @error('published_at')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- İstatistikler -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-bar mr-2 text-orange-500"></i>
                        İstatistikler
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Görüntülenme</span>
                            <span class="text-sm font-medium text-gray-900">{{ $article->hit ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Oluşturulma</span>
                            <span class="text-sm font-medium text-gray-900">{{ $article->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Son Güncelleme</span>
                            <span class="text-sm font-medium text-gray-900">{{ $article->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- İşlem Butonları -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-3">
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="w-full inline-flex items-center justify-center px-4 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-colors duration-150 disabled:opacity-50">
                            <i class="fas fa-save mr-2"></i>
                            <span wire:loading.remove>Makale Güncelle</span>
                            <span wire:loading>Güncelleniyor...</span>
                        </button>
                        
                        <a href="{{ route('articles.index') }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors duration-150">
                            <i class="fas fa-times mr-2"></i>
                            İptal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @vite(['Modules/Articles/resources/assets/js/app.js', 'Modules/Articles/resources/assets/sass/app.scss'])
