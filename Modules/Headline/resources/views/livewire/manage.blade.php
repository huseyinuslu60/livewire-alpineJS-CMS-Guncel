<div class="w-full px-6 py-6">
    <!-- Flash Messages -->
    @if($successMessage)
        <div x-data="{ showSuccess: true }" 
             x-init="setTimeout(() => { showSuccess = false; $wire.successMessage = ''; }, 5000)"
             x-show="showSuccess" 
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
                    <p class="text-sm font-medium text-green-800">{{ $successMessage }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="showSuccess = false; $wire.successMessage = ''" class="text-green-400 hover:text-green-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modern Header with Stats -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-star text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-1">Vitrin Yönetimi</h1>
                        <p class="text-gray-600">İçerikleri farklı vitrin alanlarında yönetin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $pinnedByZone[$activeZone]->count() }}</div>
                        <div class="text-sm text-gray-500">Sabitlenmiş İçerik</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $suggestions->count() }}</div>
                        <div class="text-sm text-gray-500">Öneri</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-wrap gap-2">
                @foreach($zones as $zone => $label)
                    <button
                        wire:click="setZone('{{ $zone }}')"
                        class="inline-flex items-center px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200
                               {{ $activeZone === $zone 
                                   ? 'bg-purple-100 text-purple-700 shadow-sm' 
                                   : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}"
                    >
                        <i class="fas fa-{{ $zone === 'manset' ? 'newspaper' : ($zone === 'surmanset' ? 'star' : 'heart') }} mr-2"></i>
                        {{ $label }}
                        <span class="ml-2 bg-white text-gray-600 px-2 py-1 rounded-full text-xs font-medium">
                            {{ $pinnedByZone[$zone]->count() }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Column: Pinned Items -->
        <div class="flex-1 lg:w-2/3 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-thumbtack text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">
                            {{ $zones[$activeZone] }} - Sabitlenmiş İçerikler
                        </h2>
                        <span class="text-sm text-gray-500">
                            {{ $pinnedByZone[$activeZone]->count() }} içerik
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pinned List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <ul id="pinned-list-{{ $activeZone }}" class="pinned-items divide-y divide-gray-200" data-zone="{{ $activeZone }}" x-data="headlineSortable()">
                    @forelse($pinnedByZone[$activeZone] as $item)
                        <li 
                            wire:key="pinned-{{ $item->id }}-{{ $item->slot }}"
                            class="p-4 hover:bg-gray-50 transition-colors duration-200
                                   @if($item->starts_at || $item->ends_at)
                                       border-l-4 border-l-orange-400 bg-orange-50/30
                                   @endif"
                            data-type="{{ $item->subject_type }}"
                            data-id="{{ $item->subject_id }}"
                        >
                            <div class="flex items-start space-x-3">
                                <!-- Drag Handle -->
                                <div class="flex-shrink-0 cursor-move text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ $item->subject_type === 'post' 
                                                       ? 'bg-blue-100 text-blue-800' 
                                                       : 'bg-green-100 text-green-800' }}">
                                            {{ $item->subject_type_label }}
                                        </span>
                                        
                                        <!-- Zamanlama Durumu -->
                                        @if($item->starts_at || $item->ends_at)
                                            @if($item->starts_at && $item->starts_at->isFuture())
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Beklemede ({{ $item->starts_at->format('d.m H:i') }})
                                                </span>
                                            @elseif($item->slot)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Aktif (Slot {{ $item->slot }})
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Zamanlanmış
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-500">
                                                {{ $item->slot ? 'Slot ' . $item->slot : 'Sabitlenmiş' }}
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-sm font-medium text-gray-900 mt-1">
                                        {{ $item->subject->title ?? 'Başlık bulunamadı' }}
                                    </h3>
                                    @if($item->starts_at || $item->ends_at)
                                        <div class="mt-2 p-2 bg-orange-50 rounded-lg border border-orange-200">
                                            <div class="flex items-center space-x-2 text-xs">
                                                <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="font-medium text-orange-700">Zamanlama Bilgileri:</span>
                                            </div>
                                            <div class="mt-1 space-y-1">
                                                @if($item->starts_at)
                                                    <div class="flex items-center space-x-2 text-xs text-orange-600">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <span>Başlangıç: {{ $item->starts_at->format('d.m.Y H:i') }}</span>
                                                        @if($item->starts_at->isFuture())
                                                            <span class="px-1.5 py-0.5 bg-green-100 text-green-800 rounded text-xs">Gelecek</span>
                                                        @else
                                                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">Aktif</span>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if($item->ends_at)
                                                    <div class="flex items-center space-x-2 text-xs text-orange-600">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <span>Bitiş: {{ $item->ends_at->format('d.m.Y H:i') }}</span>
                                                        @if($item->ends_at->isPast())
                                                            <span class="px-1.5 py-0.5 bg-red-100 text-red-800 rounded text-xs">Süresi Dolmuş</span>
                                                        @else
                                                            <span class="px-1.5 py-0.5 bg-yellow-100 text-yellow-800 rounded text-xs">Aktif</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-2">
                                    <button
                                        wire:click="openSchedule('{{ $item->zone }}', '{{ $item->subject_type }}', {{ $item->subject_id }})"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm"
                                        title="Zamanla"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    
                                    <button
                                        wire:click="unpin('{{ $item->zone }}', '{{ $item->subject_type }}', {{ $item->subject_id }})"
                                        class="text-red-600 hover:text-red-900 text-sm"
                                        title="Kaldır"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="p-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="mt-2">Bu alanda henüz içerik yok</p>
                        </li>
                    @endforelse
                </ul>
            </div>

        </div>

        <!-- Right Column: Suggestions -->
        <div class="flex-1 lg:w-1/3 lg:min-w-[300px] space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-lightbulb text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Öneriler</h2>
                        <p class="text-sm text-gray-500">Yeni içerikler ekleyin</p>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="space-y-3">
                <div>
                    <input
                        wire:model.live.debounce.300ms="query"
                        type="text"
                        placeholder="İçerik ara..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
                    >
                </div>
                <div>
                    <select
                        wire:model.live="type"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
                    >
                        <option value="all">Tümü</option>
                        <option value="post">Postlar</option>
                        <option value="article">Makaleler</option>
                    </select>
                </div>
            </div>

            <!-- Suggestions List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 max-h-120 overflow-y-auto sticky top-6">
                <div class="p-4">
                    @forelse($suggestions as $suggestion)
                        <div class="flex items-center justify-between p-4 hover:bg-gray-50 rounded-lg transition-all duration-200 border border-transparent hover:border-gray-200 mb-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                               {{ $suggestion->type === 'post' 
                                                   ? 'bg-blue-100 text-blue-800' 
                                                   : 'bg-green-100 text-green-800' }}">
                                        <i class="fas fa-{{ $suggestion->type === 'post' ? 'newspaper' : 'file-alt' }} mr-1"></i>
                                        {{ ucfirst($suggestion->type) }}
                                    </span>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 mb-1 line-clamp-2">
                                    {{ $suggestion->title }}
                                </h3>
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $suggestion->published_date ? $suggestion->published_date->format('d.m.Y H:i') : 'Tarih yok' }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button
                                    wire:click="openSchedule('{{ $activeZone }}', '{{ $suggestion->type }}', {{ $suggestion->id }})"
                                    class="inline-flex items-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md"
                                >
                                    <i class="fas fa-plus mr-1"></i>
                                    Ekle
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-500">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-search text-gray-400 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Öneri Bulunamadı</h3>
                            <p class="text-sm">Arama kriterlerinize uygun içerik bulunamadı</p>
                        </div>
                    @endforelse
                </div>
                
                <!-- Load More Button -->
                @if($hasMoreSuggestions)
                    <div class="p-4 text-center">
                        <button
                            wire:click="loadMoreSuggestions"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200 font-medium"
                        >
                            <i class="fas fa-arrow-down mr-2"></i>
                            Daha Fazla Yükle
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    @if($showScheduleModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showScheduleModal', false)">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-clock mr-2 text-indigo-600"></i>
                    İçerik Ekle
                </h3>
                
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mb-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Zamanlama Seçenekleri
                    </h4>
                    <p class="text-xs text-blue-700">
                        Zaman belirtmezseniz içerik hemen eklenir. Zaman belirtirseniz belirtilen zamanda otomatik olarak en üste çıkar.
                    </p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Başlangıç Tarihi (İsteğe Bağlı)
                        </label>
                        <input
                            wire:model="schedStartsAt"
                            type="datetime-local"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Zaman belirtmezseniz hemen eklenir"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Bitiş Tarihi (İsteğe Bağlı)
                        </label>
                        <input
                            wire:model="schedEndsAt"
                            type="datetime-local"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Zaman belirtmezseniz süresiz kalır"
                        >
                    </div>
                </div>
                
                <div class="flex justify-between mt-6">
                    <button
                        wire:click="clearSchedule"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 font-medium"
                    >
                        <i class="fas fa-trash mr-2"></i>
                        Sıfırla
                    </button>
                    <div class="flex space-x-3">
                        <button
                            wire:click="$set('showScheduleModal', false)"
                            class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium transition-colors duration-200"
                        >
                            İptal
                        </button>
                        <button
                            wire:click="applySchedule"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 font-medium"
                        >
                            <i class="fas fa-save mr-2"></i>
                            Kaydet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
@vite(['Modules/Headline/resources/assets/js/app.js'])
@endpush
