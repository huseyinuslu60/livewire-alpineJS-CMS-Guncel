<div class="container-fluid px-6 py-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-plus text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Yeni Hisse Senedi</h2>
                    <p class="text-gray-600">Hisse senedi bilgilerini girin</p>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save" class="p-6 banks-form">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Hisse Senedi Adı -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hisse Senedi Adı <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="name" 
                           type="text" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ünvan -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Ünvan <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="unvan" 
                           type="text" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('unvan') border-red-500 @enderror">
                    @error('unvan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kuruluş Tarihi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kuruluş Tarihi</label>
                    <input wire:model="kurulus_tarihi" 
                           type="date" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kurulus_tarihi') border-red-500 @enderror">
                    @error('kurulus_tarihi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- İlk İşlem Tarihi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">İlk İşlem Tarihi</label>
                    <input wire:model="ilk_islem_tarihi" 
                           type="date" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('ilk_islem_tarihi') border-red-500 @enderror">
                    @error('ilk_islem_tarihi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Durum -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                    <select wire:model="last_status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('last_status') border-red-500 @enderror">
                        <option value="active">Aktif</option>
                        <option value="inactive">Pasif</option>
                    </select>
                    @error('last_status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Web -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Web Sitesi</label>
                    <input wire:model="web" 
                           type="url" 
                           placeholder="https://example.com"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('web') border-red-500 @enderror">
                    @error('web')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telefon -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                    <input wire:model="telefon" 
                           type="text" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('telefon') border-red-500 @enderror">
                    @error('telefon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Faks -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Faks</label>
                    <input wire:model="faks" 
                           type="text" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('faks') border-red-500 @enderror">
                    @error('faks')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Personel Sayısı -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Personel Sayısı</label>
                    <input wire:model="personel_sayisi" 
                           type="number" 
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('personel_sayisi') border-red-500 @enderror">
                    @error('personel_sayisi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Genel Müdür -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Genel Müdür</label>
                    <input wire:model="genel_mudur" 
                           type="text" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('genel_mudur') border-red-500 @enderror">
                    @error('genel_mudur')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Merkez Adres -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Merkez Adres</label>
                    <textarea wire:model="merkez_adres" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('merkez_adres') border-red-500 @enderror"></textarea>
                    @error('merkez_adres')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Yönetim Kurulu -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Yönetim Kurulu</label>
                    <textarea wire:model="yonetim_kurulu" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('yonetim_kurulu') border-red-500 @enderror"></textarea>
                    @error('yonetim_kurulu')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Faaliyet Alanı -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Faaliyet Alanı</label>
                    <textarea wire:model="faaliyet_alani" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('faaliyet_alani') border-red-500 @enderror"></textarea>
                    @error('faaliyet_alani')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Endeksler -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endeksler</label>
                    <textarea wire:model="endeksler" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('endeksler') border-red-500 @enderror"></textarea>
                    @error('endeksler')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Detaylar -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Detaylar</label>
                    <textarea wire:model="details" 
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('details') border-red-500 @enderror"></textarea>
                    @error('details')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('banks.stocks.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    İptal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Kaydet
                </button>
            </div>
        </form>
    </div>

    {{-- Banks modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Banks/resources/assets/sass/app.scss', 'Modules/Banks/resources/assets/js/app.js'])
</div>
