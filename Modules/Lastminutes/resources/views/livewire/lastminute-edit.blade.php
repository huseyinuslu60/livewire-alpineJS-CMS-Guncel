<div x-data="lastminuteForm()">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div x-show="showSuccess" 
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

    <!-- Modern Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-edit text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Son Dakika Düzenle</h2>
                        <p class="text-gray-600">Son dakika haberini düzenleyin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('lastminutes.index') }}" 
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
                    <form wire:submit.prevent="updateLastminute">
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
                                   placeholder="Son dakika başlığını girin..."
                                   required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Yönlendirme URL -->
                        <div class="mb-6">
                            <label for="redirect" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-external-link-alt mr-1 text-orange-500"></i>
                                Yönlendirme URL
                            </label>
                            <input type="url" 
                                   wire:model.live="redirect" 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('redirect') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                                   id="redirect" 
                                   placeholder="https://example.com">
                            @error('redirect')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Boş bırakılırsa yönlendirme yapılmaz
                            </p>
                        </div>

                        <!-- Bitiş Tarihi -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-clock mr-1 text-orange-500"></i>
                                Bitiş Tarihi
                            </label>
                            <input type="datetime-local" 
                                   wire:model.live="end_at" 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('end_at') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                                   id="end_at"
                                   onclick="this.showPicker()"
                                   onfocus="this.showPicker()">
                            @error('end_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <!-- Hızlı Seçim Butonları -->
                            <div class="mt-3">
                                <p class="text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-bolt mr-1 text-yellow-500"></i>
                                    Hızlı Seçim
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" 
                                            wire:click="setQuickTime(5)"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-150 cursor-pointer">
                                        <i class="fas fa-clock mr-1"></i>
                                        5 Dakika
                                    </button>
                                    <button type="button" 
                                            wire:click="setQuickTime(10)"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors duration-150 cursor-pointer">
                                        <i class="fas fa-clock mr-1"></i>
                                        10 Dakika
                                    </button>
                                    <button type="button" 
                                            wire:click="setQuickTime(15)"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors duration-150 cursor-pointer">
                                        <i class="fas fa-clock mr-1"></i>
                                        15 Dakika
                                    </button>
                                    <button type="button" 
                                            wire:click="setQuickTime(30)"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors duration-150 cursor-pointer">
                                        <i class="fas fa-clock mr-1"></i>
                                        30 Dakika
                                    </button>
                                    <button type="button" 
                                            wire:click="setQuickTime(60)"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors duration-150 cursor-pointer">
                                        <i class="fas fa-clock mr-1"></i>
                                        60 Dakika
                                    </button>
                                </div>
                            </div>
                            
                            <p class="mt-2 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Boş bırakılırsa süresiz olur
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <div class="flex items-center space-x-3">
                                <button type="button" 
                                        wire:click="deleteLastminute"
                                        wire:confirm="Bu son dakikayı silmek istediğinizden emin misiniz?"
                                        class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors duration-150 cursor-pointer">
                                    <i class="fas fa-trash mr-2"></i>
                                    Sil
                                </button>
                            </div>
                            <div class="flex items-center space-x-3">
                                <button type="button" 
                                        onclick="history.back()"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150 cursor-pointer">
                                    <i class="fas fa-times mr-2"></i>
                                    İptal
                                </button>
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer">
                                    <i class="fas fa-save mr-2"></i>
                                    Değişiklikleri Kaydet
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Durum Ayarları -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-cog mr-2 text-orange-500"></i>
                        Durum Ayarları
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
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Ağırlık -->
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-weight-hanging mr-1 text-orange-500"></i>
                                Ağırlık *
                            </label>
                            <input type="number" 
                                   wire:model.live="weight" 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                                   id="weight" 
                                   min="0" 
                                   required>
                            <p class="mt-2 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Düşük sayılar önce gösterilir
                            </p>
                        </div>
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
                            <span class="text-sm text-gray-600">Oluşturulma</span>
                            <span class="text-sm font-medium text-gray-900">{{ $lastminute->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Son Güncelleme</span>
                            <span class="text-sm font-medium text-gray-900">{{ $lastminute->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        @if($lastminute->end_at)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Bitiş Tarihi</span>
                            <span class="text-sm font-medium text-gray-900">{{ $lastminute->formatted_end_at }}</span>
                        </div>
                        @endif
                        @if($lastminute->is_expired)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-red-600">Durum</span>
                            <span class="text-sm font-medium text-red-600">Süresi Dolmuş</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bilgi -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Bilgi
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                            <p class="text-sm text-gray-600">
                                Son dakika haberleri ana sayfada özel bölümde gösterilir.
                            </p>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                            <p class="text-sm text-gray-600">
                                Bitiş tarihi olan haberler otomatik olarak süresi dolmuş olarak işaretlenir.
                            </p>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                            <p class="text-sm text-gray-600">
                                Ağırlık değeri düşük olan haberler önce gösterilir.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    // NOT: sadece component registration (tek seferlik) bu blokta kalmalı.
    Alpine.data('lastminuteForm', () => ({
        showSuccess: true,
        
        init() {
            // Auto-hide success message after 5 seconds
            setTimeout(() => {
                this.showSuccess = false;
            }, 5000);
        }
    }));
});

</script>
