<div x-data="newsletterForm()" class="newsletter-module">
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
        <div x-show="showError" 
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

    <!-- Modern Header -->
    <div class="mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-edit text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-1">Bülten Düzenle</h1>
                        <p class="text-gray-600">Bülten bilgilerini düzenleyin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('newsletters.index') }}" 
                       class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Newsletter Form -->
    <div class="newsletter-form bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <form wire:submit.prevent="update">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Newsletter Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Bülten Adı <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model="name" 
                               id="name"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 @error('name') border-red-300 @enderror"
                               placeholder="Bülten adını girin">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mail Subject -->
                    <div>
                        <label for="mail_subject" class="block text-sm font-semibold text-gray-700 mb-2">
                            E-posta Konusu <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model="mail_subject" 
                               id="mail_subject"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 @error('mail_subject') border-red-300 @enderror"
                               placeholder="E-posta konusunu girin">
                        @error('mail_subject')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                            Durum
                        </label>
                        <select wire:model="status" 
                                id="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200">
                            <option value="draft">Taslak</option>
                            <option value="sending">Gönderiliyor</option>
                            <option value="sent">Gönderildi</option>
                            <option value="failed">Başarısız</option>
                        </select>
                    </div>

                    <!-- Reklam -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   wire:model="reklam" 
                                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Reklam içeriği ekle</span>
                        </label>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Mail Body -->
                    <div>
                        <label for="mail_body" class="block text-sm font-semibold text-gray-700 mb-2">
                            E-posta İçeriği <span class="text-red-500">*</span>
                        </label>
                        <div wire:ignore>
                            <textarea wire:model="mail_body" 
                                      id="mail_body"
                                      rows="12"
                                      class="trumbowyg w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 @error('mail_body') border-red-300 @enderror"
                                      placeholder="E-posta içeriğini girin"></textarea>
                        </div>
                        @error('mail_body')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-8 border-t border-gray-200 mt-8">
                <a href="{{ route('newsletters.index') }}" 
                   class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                    İptal
                </a>
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="px-8 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="update">
                        <i class="fas fa-save mr-2"></i>
                        Güncelle
                    </span>
                    <span wire:loading wire:target="update">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Güncelleniyor...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    // NOT: sadece component registration (tek seferlik) bu blokta kalmalı.
    Alpine.data('newsletterForm', () => ({
        showSuccess: true,
        showError: true,
        
        init() {
            // Auto-hide success message after 5 seconds
            setTimeout(() => {
                this.showSuccess = false;
            }, 5000);
            
            // Auto-hide error message after 10 seconds
            setTimeout(() => {
                this.showError = false;
            }, 10000);
        }
    }));
});

</script>
