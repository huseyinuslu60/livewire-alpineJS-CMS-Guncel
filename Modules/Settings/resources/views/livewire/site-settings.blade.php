<div>
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-cog mr-2 text-indigo-600"></i>
            Site Ayarları
        </h1>
        <p class="text-gray-600 mt-1">
            Site genel ayarlarını yönetin ve düzenleyin
        </p>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="mb-6 bg-emerald-50 text-emerald-900 rounded-xl p-4 border border-emerald-200">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 bg-red-50 text-red-900 rounded-xl p-4 border border-red-200">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                @foreach($groups as $groupKey => $groupName)
                    <button
                        wire:click="setActiveTab('{{ $groupKey }}')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === $groupKey ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        {{ $groupName }}
                    </button>
                @endforeach
            </nav>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6">
            @if(isset($settings[$activeTab]))
                <form wire:submit.prevent="saveSettings">
                    <div class="space-y-6">
                        @foreach($settings[$activeTab] as $setting)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Label -->
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700">
                                        {{ $setting['label'] }}
                                        @if($setting['is_required'])
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    @if($setting['description'])
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $setting['description'] }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Input -->
                                <div class="md:col-span-2">
                                    @if($setting['type'] === 'text')
                                        <input
                                            type="text"
                                            wire:model.defer="settings.{{ $activeTab }}.{{ $loop->index }}.value"
                                            class="block w-full px-3 py-2 text-base border-2 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200/50 placeholder-gray-400 transition-all duration-200"
                                            placeholder="{{ $setting['label'] }}"
                                        >
                                    @elseif($setting['type'] === 'textarea')
                                        <textarea
                                            wire:model.defer="settings.{{ $activeTab }}.{{ $loop->index }}.value"
                                            rows="3"
                                            class="block w-full px-3 py-2 text-base border-2 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200/50 placeholder-gray-400 transition-all duration-200"
                                            placeholder="{{ $setting['label'] }}"
                                        ></textarea>
                                    @elseif($setting['type'] === 'boolean')
                                        <div class="flex items-center">
                                            <input
                                                type="checkbox"
                                                wire:model.defer="settings.{{ $activeTab }}.{{ $loop->index }}.value"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            >
                                            <label class="ml-2 text-sm text-gray-700">
                                                Aktif
                                            </label>
                                        </div>
                                    @elseif($setting['type'] === 'select' && $setting['options'])
                                        <select
                                            wire:model.defer="settings.{{ $activeTab }}.{{ $loop->index }}.value"
                                            class="block w-full px-3 py-2 text-base border-2 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200/50 transition-all duration-200"
                                        >
                                            <option value="">Seçiniz</option>
                                            @foreach($setting['options'] as $option)
                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($setting['type'] === 'image')
                                        <div class="space-y-4" x-data="{ previewUrl: null, fileName: null, fileSizeKb: null }">
                                            @if($setting['value'] && !str_contains($setting['value'], 'tmp') && !str_contains($setting['value'], 'php'))
                                                <div class="flex items-center space-x-4">
                                                    <img src="{{ asset('storage/' . $setting['value']) }}" alt="{{ $setting['label'] }}" class="h-20 w-20 object-cover rounded-lg border-2 border-gray-300" loading="eager" decoding="sync">
                                                    <div>
                                                        <p class="text-sm text-gray-600">Mevcut logo</p>
                                                        <p class="text-xs text-gray-500">{{ $setting['value'] }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center space-x-4">
                                                    <div class="h-20 w-20 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm text-gray-600">Logo yüklenmedi</p>
                                                        <p class="text-xs text-gray-500">Yukarıdaki alana tıklayarak logo yükleyin</p>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors duration-200" wire:ignore>
                                                <input
                                                    type="file"
                                                    wire:model.defer="settings.{{ $activeTab }}.{{ $loop->index }}.value"
                                                    accept="image/*"
                                                    class="hidden"
                                                    id="file-{{ $loop->index }}"
                                                    x-on:change="
                                                        const file = $event.target.files[0];
                                                        if (file) {
                                                            const reader = new FileReader();
                                                            reader.onload = (e) => { previewUrl = e.target.result };
                                                            reader.readAsDataURL(file);
                                                            fileName = file.name;
                                                            fileSizeKb = Math.round(file.size/1024);
                                                        }
                                                    "
                                                >
                                                <label for="file-{{ $loop->index }}" class="cursor-pointer block">
                                                    <!-- Preview Image -->
                                                    <template x-if="previewUrl">
                                                        <img :src="previewUrl" class="mx-auto h-20 w-20 object-cover rounded-lg border-2 border-gray-300" alt="Preview">
                                                    </template>
                                                    
                                                    <!-- File Info -->
                                                    <p x-show="fileName" class="mt-2 text-sm text-green-600" x-text="fileName + ' (' + fileSizeKb + ' KB)'"></p>
                                                    
                                                    <!-- Placeholder -->
                                                    <div x-show="!previewUrl">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                        <p class="mt-2 text-sm text-gray-600">
                                                            <span class="font-medium text-blue-600 hover:text-blue-500">Logo yüklemek için tıklayın</span>
                                                        </p>
                                                        <p class="text-xs text-gray-500">PNG, JPG, GIF (Max 2MB)</p>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 mt-6">
                        <button
                            type="button"
                            wire:click="resetSettings"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                        >
                            <i class="fas fa-undo mr-2"></i>
                            Sıfırla
                        </button>
                        
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveSettings"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                        >
                            <i class="fas fa-save mr-2" wire:loading.remove wire:target="saveSettings"></i>
                            <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="saveSettings"></i>
                            <span wire:loading.remove wire:target="saveSettings">Kaydet</span>
                            <span wire:loading wire:target="saveSettings">Kaydediliyor...</span>
                        </button>
                    </div>
                </form>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-cog text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">Bu grup için ayar bulunamadı.</p>
                </div>
            @endif
        </div>
    </div>
</div>
