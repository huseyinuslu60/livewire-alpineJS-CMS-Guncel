<div x-data="{ showSuccess: true }">
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

    <!-- Modern Header with Stats -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-clipboard-list text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Sistem Logları</h2>
                        <p class="text-gray-600">Kullanıcı aktivitelerini ve sistem işlemlerini takip edin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $logs->total() }}</div>
                        <div class="text-sm text-gray-500">Toplam Log</div>
                    </div>
                    <div class="flex space-x-2">
                        @can('export logs')
                        <button wire:click="exportLogs" 
                           class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-4 py-2 rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-200 flex items-center">
                            <i class="fas fa-download mr-2"></i>
                            Dışa Aktar
                        </button>
                        @endcan
                        @can('delete logs')
                        <button wire:click="clearAllLogs" 
                                onclick="return confirm('Tüm log kayıtlarını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!') || event.stopImmediatePropagation()"
                                class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 py-2 rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-200 flex items-center">
                            <i class="fas fa-trash mr-2"></i>
                            Tümünü Sil
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('error'))
        <div x-data="{ showError: true }" 
             x-show="showError" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 shadow-sm">
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

    <!-- Advanced Filters Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-search mr-1 text-blue-500"></i>
                        Ara
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               wire:model.live.debounce.300ms="search" 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                               placeholder="Açıklama ile ara...">
                    </div>
                </div>
                
                <!-- Action Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-cog mr-1 text-blue-500"></i>
                        İşlem
                    </label>
                    <select wire:model.live="action" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Tüm İşlemler</option>
                        @foreach($actions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- User Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-user mr-1 text-blue-500"></i>
                        Kullanıcı
                    </label>
                    <select wire:model.live="user_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Tüm Kullanıcılar</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Date From -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-calendar mr-1 text-blue-500"></i>
                        Başlangıç
                    </label>
                    <input type="date" 
                           wire:model.live="date_from" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <!-- Date To -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-calendar mr-1 text-blue-500"></i>
                        Bitiş
                    </label>
                    <input type="date" 
                           wire:model.live="date_to" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <!-- Per Page Selector -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-list mr-1 text-blue-500"></i>
                        Sayfa
                    </label>
                    <select wire:model.live="perPage" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="10">10 Kayıt</option>
                        <option value="15">15 Kayıt</option>
                        <option value="25">25 Kayıt</option>
                        <option value="50">50 Kayıt</option>
                        <option value="100">100 Kayıt</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Card -->
    <div x-data="logsTable()" 
         class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6" 
         wire:loading.class="opacity-50" 
         wire:target="selectedLogs">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="flex items-center mr-4">
                        <input type="checkbox" 
                               wire:model.live="selectAll" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                               id="selectAll">
                        <label for="selectAll" class="ml-2 text-sm font-medium text-gray-700">
                            Tümünü Seç ({{ count($selectedLogs) }}/{{ $logs->total() }})
                        </label>
                    </div>
                </div>
                @can('delete logs')
                <div class="flex items-center space-x-3">
                    <select wire:model.live="bulkAction" 
                            class="block w-48 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">-- Seçilenleri --</option>
                        <option value="delete">Sil</option>
                    </select>
                    <button wire:click="applyBulkAction" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            @if(empty($selectedLogs)) disabled @endif>
                        <i class="fas fa-check mr-2"></i>
                        Uygula
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Logs Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-hidden">
            <!-- Table Header -->
            <div class="overflow-x-auto" wire:loading.class="opacity-50" wire:target="search,action,user_id,date_from,date_to,perPage">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 50px;">
                                <input type="checkbox" 
                                       wire:model.live="selectAll" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                       id="selectAllHeader">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-hashtag mr-2 text-blue-500"></i>
                                    ID
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-user mr-2 text-blue-500"></i>
                                    Kullanıcı
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-cog mr-2 text-blue-500"></i>
                                    İşlem
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                    Açıklama
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-globe mr-2 text-blue-500"></i>
                                    IP Adresi
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar mr-2 text-blue-500"></i>
                                    Tarih
                                </div>
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-cog mr-2 text-blue-500"></i>
                                    İşlemler
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" 
                                       wire:model.live="selectedLogs" 
                                       value="{{ $log->log_id }}" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                       id="log_{{ $log->log_id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gray-100 text-gray-800 border border-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                                        #{{ $log->log_id }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                            <span class="text-white text-xs font-medium">
                                                {{ substr($log->user->name ?? 'S', 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'Sistem' }}</div>
                                        @if($log->user)
                                            <div class="text-xs text-gray-500">{{ $log->user->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $actionColors = [
                                        'create' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-white',
                                        'update' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-white',
                                        'delete' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-white',
                                        'login' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-white',
                                        'logout' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white',
                                        'view' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-white',
                                        'http_get' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-white',
                                        'http_post' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-white',
                                        'http_update' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-white',
                                        'http_delete' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-white',
                                    ];
                                    
                                    $actionIcons = [
                                        'create' => 'plus',
                                        'update' => 'edit',
                                        'delete' => 'trash',
                                        'login' => 'sign-in-alt',
                                        'logout' => 'sign-out-alt',
                                        'view' => 'eye',
                                        'http_get' => 'search',
                                        'http_post' => 'plus',
                                        'http_update' => 'edit',
                                        'http_delete' => 'trash',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white' }}">
                                    <i class="fas fa-{{ $actionIcons[$log->action] ?? 'cog' }} mr-1"></i>
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-gray-900 mb-1">
                                        {{ $log->short_description }}
                                    </p>
                                    @if($log->model_type)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white">
                                            <i class="fas fa-database mr-1"></i>
                                            {{ class_basename($log->model_type) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-globe text-gray-400 mr-2"></i>
                                    <span class="text-sm font-medium text-gray-900">{{ $log->ip_address ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $log->created_at->format('d.m.Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('logs.show', $log) }}" 
                                       class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 hover:text-blue-800 transition-colors duration-150">
                                        <i class="fas fa-eye mr-1"></i>
                                        Görüntüle
                                    </a>
                                    @can('delete logs')
                                    <button wire:click="deleteLog({{ $log->log_id }})" 
                                            class="inline-flex items-center px-3 py-1.5 border border-red-500 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 hover:text-red-800 transition-colors duration-150"
                                            onclick="return confirm('Bu log kaydını silmek istediğinizden emin misiniz?') || event.stopImmediatePropagation()">
                                        <i class="fas fa-trash mr-1"></i>
                                        Sil
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Log kaydı bulunamadı</h3>
                                    <p class="text-gray-500 mb-4">Arama kriterlerinize uygun log kaydı bulunamadı.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $logs->links() }}
    </div>

    {{-- Logs modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Logs/resources/assets/sass/app.scss', 'Modules/Logs/resources/assets/js/app.js'])
    
</div>
