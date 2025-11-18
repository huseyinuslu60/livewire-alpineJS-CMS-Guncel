<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div x-show="showSuccess || true"
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
             x-show="showError || true"
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

    @if($showErrorMessage && $errorMessage)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button wire:click="$set('showErrorMessage', false)" type="button" class="text-red-400 hover:text-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="uploadFiles">
        <!-- Dosya Seçimi -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-900 mb-3">
                <i class="fas fa-upload mr-2 text-blue-500"></i>
                Dosyalar
            </label>
            <div class="upload-area border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-colors duration-200 hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                 x-data="{ dragOver: false }"
                 :class="{ 'border-blue-400 bg-blue-50 dark:bg-blue-900/20': dragOver }"
                 @dragover.prevent="dragOver = true"
                 @dragleave.prevent="dragOver = false"
                 @drop.prevent="
                    dragOver = false;
                    const files = $event.dataTransfer.files;
                    if (files.length > 0) {
                        const dataTransfer = new DataTransfer();
                        Array.from(files).forEach(file => dataTransfer.items.add(file));
                        $refs.fileInput.files = dataTransfer.files;
                        $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                 ">
                <div class="upload-content relative">
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-cloud-upload-alt text-blue-500 text-2xl"></i>
                    </div>
                    <h6 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Dosyaları seçmek için tıklayın</h6>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">veya dosyaları buraya sürükleyin</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">Resim, video, PDF ve diğer dosya türlerini destekler</p>

                    <!-- Sadece buton alanına tıklanabilir input -->
                    <div class="mt-4">
                        <input type="file"
                               class="hidden"
                               id="file-upload-input"
                               x-ref="fileInput"
                               wire:model="files"
                               multiple
                               accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt">
                        <label for="file-upload-input"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium cursor-pointer transition-colors duration-150">
                            <i class="fas fa-folder-open mr-2"></i>
                            Dosya Seç
                        </label>
                    </div>
                </div>
            </div>
            @error('files.*')
                <div class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Seçilen Dosyalar -->
        @if($allFiles)
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h6 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Seçilen Dosyalar
                    </h6>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        {{ count($allFiles) }} dosya
                    </span>
                </div>

                <div class="space-y-4">
                    @foreach($allFiles as $index => $file)
                        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                            <div class="p-4">
                                <!-- Dosya Bilgileri -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-file text-blue-600"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-medium text-gray-900">{{ $file->getClientOriginalName() }}</h6>
                                            <p class="text-sm text-gray-500">
                                                <i class="fas fa-weight-hanging mr-1"></i>
                                                {{ number_format($file->getSize() / 1024, 2) }} KB
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button"
                                            class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                            wire:click="removeFile({{ $index }})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Bu dosya için açıklama alanları -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-image mr-1 text-blue-500"></i>
                                            Alt Text (Görsel Açıklama)
                                        </label>
                                        <input type="text"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               wire:model="allDescriptions.{{ $index }}.alt_text"
                                               placeholder="Bu resim için açıklama...">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-comment mr-1 text-blue-500"></i>
                                            Başlık/Açıklama
                                        </label>
                                        <input type="text"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               wire:model="allDescriptions.{{ $index }}.caption"
                                               placeholder="Bu dosya için başlık...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Butonlar -->
        <div class="flex justify-end space-x-3">
            <button type="button"
                    wire:click="$dispatch('closeUploadModal')"
                    class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                <i class="fas fa-times mr-2"></i>
                İptal
            </button>
            <button type="submit"
                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    @if(empty($allFiles)) disabled @endif>
                <i class="fas fa-upload mr-2"></i>
                Dosyaları Yükle ({{ count($allFiles) }})
            </button>
        </div>
    </form>

    <!-- Loading State -->
    <div wire:loading class="text-center py-8">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
        <h6 class="text-lg font-medium text-gray-900 mb-2">Dosyalar yükleniyor...</h6>
        <p class="text-gray-600">Lütfen bekleyin, işlem tamamlandığında bilgilendirileceksiniz.</p>

        <!-- Progress Bar -->
        <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>

    {{-- Files modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Files/resources/assets/sass/app.scss', 'Modules/Files/resources/assets/js/app.js'])
</div>
