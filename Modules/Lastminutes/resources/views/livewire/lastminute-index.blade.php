<div x-data="lastminutesTable()">
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
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-clock text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Son Dakika Yönetimi</h2>
                        <p class="text-gray-600">Son dakika haberlerini yönetin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @can('create lastminutes')
                    <a href="{{ route('lastminutes.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 shadow-lg hover:shadow-xl transition-all duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Yeni Son Dakika
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-red-500"></i>
                        Arama
                    </label>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm" 
                           placeholder="Başlık ara...">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-filter mr-1 text-red-500"></i>
                        Durum
                    </label>
                    <select wire:model.live="status" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list mr-1 text-red-500"></i>
                        Sayfa Başına
                    </label>
                    <select wire:model.live="perPage" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <!-- Sort -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort mr-1 text-red-500"></i>
                        Sıralama
                    </label>
                    <select wire:model.live="sortBy" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        <option value="created_at">Oluşturulma</option>
                        <option value="title">Başlık</option>
                        <option value="weight">Ağırlık</option>
                        <option value="end_at">Bitiş Tarihi</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6" 
         wire:loading.class="opacity-50" 
         wire:target="search,status,perPage,sortBy">
        <div class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-red-50 to-red-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('title')" class="flex items-center hover:text-gray-700">
                                    Başlık
                                    <i class="fas fa-sort ml-1"></i>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Yönlendirme
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('end_at')" class="flex items-center hover:text-gray-700">
                                    Bitiş Tarihi
                                    <i class="fas fa-sort ml-1"></i>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('status')" class="flex items-center hover:text-gray-700">
                                    Durum
                                    <i class="fas fa-sort ml-1"></i>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('weight')" class="flex items-center hover:text-gray-700">
                                    Ağırlık
                                    <i class="fas fa-sort ml-1"></i>
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                İşlemler
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($lastminutes as $lastminute)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ Str::limit($lastminute->title, 80) }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $lastminute->created_at->format('d.m.Y H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($lastminute->redirect)
                                        <a href="{{ $lastminute->redirect_url }}" 
                                           target="_blank" 
                                           class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                            <i class="fas fa-external-link-alt mr-1"></i>
                                            {{ Str::limit($lastminute->redirect, 80) }}
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($lastminute->end_at)
                                        <div class="text-sm text-gray-900">
                                            {{ $lastminute->formatted_end_at }}
                                        </div>
                                        @if($lastminute->is_expired)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-clock mr-1"></i>
                                                Süresi Dolmuş
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-400">Sınırsız</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($lastminute->status === 'active') bg-green-100 text-green-800
                                        @elseif($lastminute->status === 'inactive') bg-gray-100 text-gray-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        <i class="fas fa-circle mr-1 text-xs"></i>
                                        {{ $lastminute->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $lastminute->weight }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        @can('edit lastminutes')
                                        <a href="{{ route('lastminutes.edit', $lastminute) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 cursor-pointer">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan

                                        @can('edit lastminutes')
                                        <button wire:click="toggleStatus({{ $lastminute->lastminute_id }})" 
                                                class="text-yellow-600 hover:text-yellow-900 cursor-pointer"
                                                title="{{ $lastminute->status === 'active' ? 'Pasif Yap' : 'Aktif Yap' }}">
                                            <i class="fas fa-{{ $lastminute->status === 'active' ? 'pause' : 'play' }}"></i>
                                        </button>
                                        @endcan

                                        @if($lastminute->end_at && !$lastminute->is_expired)
                                        @can('edit lastminutes')
                                        <button wire:click="markAsExpired({{ $lastminute->lastminute_id }})" 
                                                class="text-orange-600 hover:text-orange-900 cursor-pointer"
                                                title="Süresi Dolmuş Olarak İşaretle">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                        @endcan
                                        @endif

                                        @can('delete lastminutes')
                                        <button wire:click="deleteLastminute({{ $lastminute->lastminute_id }})" 
                                                wire:confirm="Bu son dakikayı silmek istediğinizden emin misiniz?"
                                                class="text-red-600 hover:text-red-900 cursor-pointer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-clock text-4xl text-gray-300 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Son dakika bulunamadı</h3>
                                        <p class="text-gray-500 mb-4">Henüz hiç son dakika eklenmemiş.</p>
                                        @can('create lastminutes')
                                        <a href="{{ route('lastminutes.create') }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                            <i class="fas fa-plus mr-2"></i>
                                            İlk Son Dakikayı Ekle
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($lastminutes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $lastminutes->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    // NOT: sadece component registration (tek seferlik) bu blokta kalmalı.
    Alpine.data('lastminutesTable', () => ({
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
