<div>
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
                        @foreach($postStatuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
                        @foreach($postPositions as $positionValue => $positionLabel)
                            <option value="{{ $positionValue }}">{{ $positionLabel }}</option>
                        @endforeach
                    </select>
                    @error('post_position')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
                    @error('published_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
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
                @error('redirect_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Bu link varsa, haber tıklandığında bu adrese yönlendirilir
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

