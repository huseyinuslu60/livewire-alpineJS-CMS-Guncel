<div x-data="newslettersTable()" class="newsletter-module">

    <!-- Modern Header with Stats -->
    <div class="mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-envelope text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-1">Bülten Yönetimi</h1>
                        <p class="text-gray-600">Bültenleri yönetin ve takip edin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-center bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-3">
                        <div class="text-2xl font-bold text-blue-600 mb-1">{{ $newsletters->total() }}</div>
                        <div class="text-xs text-gray-600 font-medium">Toplam Bülten</div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('newsletters.users.index') }}"
                           class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                            <i class="fas fa-users mr-2"></i>
                            Kullanıcılar
                        </a>
                        <a href="{{ route('newsletters.logs.index') }}"
                           class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                            <i class="fas fa-chart-line mr-2"></i>
                            Loglar
                        </a>
                        <a href="{{ route('newsletters.create') }}"
                           class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg text-sm font-medium transition-all duration-150 shadow-sm hover:shadow-md">
                            <i class="fas fa-plus mr-2"></i>
                            Yeni Bülten
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="block w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                               placeholder="Newsletter adı, konu...">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                    <select wire:model.live="statusFilter"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Tüm Durumlar</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sıralama</label>
                    <select wire:model.live="sortBy"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="created_at">Oluşturulma Tarihi</option>
                        <option value="name">Ad</option>
                        <option value="status">Durum</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button wire:click="clearFilters"
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                        <i class="fas fa-times mr-2"></i>
                        Filtreleri Temizle
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if($newsletters->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-4">
                        <input type="checkbox"
                               id="select-all"
                               wire:model.live="selectAll"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="select-all" class="text-sm font-medium text-gray-700">
                            Tümünü Seç ({{ $newsletters->count() }})
                        </label>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                        <select wire:model.live="bulkAction"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Toplu İşlem Seçin</option>
                            <option value="delete">Sil</option>
                            <option value="activate">Aktifleştir</option>
                            <option value="deactivate">Pasifleştir</option>
                        </select>
                        <button wire:click="applyBulkAction"
                                :disabled="!bulkAction || selectedNewsletters.length === 0"
                                class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg text-sm font-medium transition-all duration-150 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-check mr-2"></i>
                            Uygula
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Newsletter Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th class="px-2 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    </th>
                    <th class="px-2 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('name')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span class="hidden sm:inline">Newsletter Adı</span>
                            <span class="sm:hidden">Adı</span>
                            @if($sortBy === 'name')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                            @else
                                <i class="fas fa-sort text-gray-400"></i>
                            @endif
                        </button>
                    </th>
                    <th class="px-2 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                        <button wire:click="sortBy('mail_subject')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Konu</span>
                            @if($sortBy === 'mail_subject')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                            @else
                                <i class="fas fa-sort text-gray-400"></i>
                            @endif
                        </button>
                    </th>
                    <th class="px-2 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('status')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Durum</span>
                            @if($sortBy === 'status')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                            @else
                                <i class="fas fa-sort text-gray-400"></i>
                            @endif
                        </button>
                    </th>
                    <th class="px-2 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                        <button wire:click="sortBy('created_at')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Oluşturulma</span>
                            @if($sortBy === 'created_at')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                            @else
                                <i class="fas fa-sort text-gray-400"></i>
                            @endif
                        </button>
                    </th>
                    <th class="px-2 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">
                        İstatistikler
                    </th>
                    <th class="px-2 lg:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        İşlemler
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($newsletters as $newsletter)
                    <tr wire:key="newsletter-{{ $newsletter->id }}" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 cursor-pointer transition-all duration-200"
                        onclick="window.location.href='{{ route('newsletters.edit', $newsletter) }}'">
                        <td class="px-2 lg:px-6 py-4 whitespace-nowrap" onclick="event.stopPropagation()">
                            <input type="checkbox"
                                   wire:model.live="selectedNewsletters"
                                   value="{{ $newsletter->newsletter_id }}"
                                   class="newsletter-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </td>
                        <td class="px-2 lg:px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $newsletter->name }}</div>
                                    <div class="text-sm text-gray-500 hidden sm:block">
                                        {{ $newsletter->creator->name ?? 'Bilinmiyor' }}
                                    </div>
                                    <div class="text-xs text-gray-500 sm:hidden">
                                        {{ Str::limit($newsletter->mail_subject, 30) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 lg:px-6 py-4 hidden md:table-cell">
                            <div class="text-sm text-gray-900">{{ Str::limit($newsletter->mail_subject, 50) }}</div>
                        </td>
                        <td class="px-2 lg:px-6 py-4 whitespace-nowrap">
                            <span class="newsletter-status {{ $newsletter->status }}">
                                {{ $statuses[$newsletter->status] ?? $newsletter->status }}
                            </span>
                        </td>
                        <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                            {{ $newsletter->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden xl:table-cell">
                            <div class="flex space-x-4">
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-green-600">{{ $newsletter->success_count }}</div>
                                    <div class="text-xs text-gray-500">Başarılı</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-blue-600">{{ $newsletter->total_count }}</div>
                                    <div class="text-xs text-gray-500">Toplam</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-right text-sm font-medium table-actions" onclick="event.stopPropagation()">
                            <div class="flex space-x-2">
                                @can('edit newsletters')
                                <button wire:click="toggleStatus({{ $newsletter->newsletter_id }})"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Durum Değiştir">
                                    <i class="fas fa-toggle-{{ $newsletter->status === 'active' ? 'on' : 'off' }}"></i>
                                </button>
                                @endcan
                                @can('edit newsletters')
                                <a href="{{ route('newsletters.edit', $newsletter) }}"
                                   class="text-indigo-600 hover:text-indigo-900"
                                   title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('delete newsletters')
                                <button wire:click="confirmDeleteNewsletter({{ $newsletter->newsletter_id }})"
                                        class="text-red-600 hover:text-red-900"
                                        title="Sil">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-envelope text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium">Henüz newsletter bulunmuyor</p>
                                <p class="text-sm">İlk newsletter'ınızı oluşturmak için yukarıdaki butonu kullanın.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($newsletters->hasPages())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
            <div class="px-6 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                    <div class="text-sm text-gray-700 text-center sm:text-left">
                        Toplam {{ $newsletters->total() }} newsletter'dan {{ $newsletters->firstItem() }}-{{ $newsletters->lastItem() }} arası gösteriliyor
                    </div>
                    <div class="flex justify-center sm:justify-end">
                        {{ $newsletters->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
    @vite('Modules/Newsletters/resources/assets/js/app.js')
@endpush

