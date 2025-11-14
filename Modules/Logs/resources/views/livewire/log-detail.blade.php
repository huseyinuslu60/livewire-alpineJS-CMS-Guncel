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

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-clipboard-list text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Log Detayı</h2>
                        <p class="text-gray-600">Log kaydının detaylı bilgileri</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('logs.index') }}" 
                       class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white px-4 py-2 rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-200 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Geri Dön
                    </a>
                    <button wire:click="deleteLog" 
                            onclick="return confirm('Bu log kaydını silmek istediğinizden emin misiniz?')"
                            class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 py-2 rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-200 flex items-center">
                        <i class="fas fa-trash mr-2"></i>
                        Sil
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Log Bilgileri
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-24 text-sm font-medium text-gray-500">ID:</div>
                            <div class="text-sm text-gray-900">#{{ $log->log_id }}</div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-24 text-sm font-medium text-gray-500">İşlem:</div>
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
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-24 text-sm font-medium text-gray-500">Kullanıcı:</div>
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mr-2">
                                    <span class="text-white text-xs font-medium">
                                        {{ substr($log->user->name ?? 'S', 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'Sistem' }}</div>
                                    @if($log->user)
                                        <div class="text-xs text-gray-500">{{ $log->user->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if($log->model_type)
                        <div class="flex items-center">
                            <div class="w-24 text-sm font-medium text-gray-500">Model:</div>
                            <div class="text-sm text-gray-900">{{ class_basename($log->model_type) }}</div>
                        </div>
                        @endif
                        
                        @if($log->model_id)
                        <div class="flex items-center">
                            <div class="w-24 text-sm font-medium text-gray-500">Model ID:</div>
                            <div class="text-sm text-gray-900">{{ $log->model_id }}</div>
                        </div>
                        @endif
                        
                        <div class="flex items-center">
                            <div class="w-24 text-sm font-medium text-gray-500">Tarih:</div>
                            <div class="text-sm text-gray-900">{{ $log->formatted_created_at }}</div>
                        </div>
                        
                        @if($log->ip_address)
                        <div class="flex items-center">
                            <div class="w-24 text-sm font-medium text-gray-500">IP Adresi:</div>
                            <div class="text-sm text-gray-900">{{ $log->ip_address }}</div>
                        </div>
                        @endif
                        
                        @if($log->url)
                        <div class="flex items-start">
                            <div class="w-24 text-sm font-medium text-gray-500">URL:</div>
                            <div class="text-sm text-gray-900 break-all">{{ $log->url }}</div>
                        </div>
                        @endif
                        
                        @if($log->method)
                        <div class="flex items-center">
                            <div class="w-24 text-sm font-medium text-gray-500">Method:</div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white">
                                {{ $log->method }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            @if($log->description)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-align-left mr-2 text-blue-500"></i>
                        Açıklama
                    </h3>
                    <div class="text-sm text-gray-700 bg-gray-50 p-4 rounded-lg">
                        {{ $log->description }}
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Changes -->
            @if($log->hasLogChanges())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-exchange-alt mr-2 text-green-500"></i>
                        Değişiklikler
                    </h3>
                    <div class="space-y-3">
                        @foreach($log->getChangesSummary() as $field => $change)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="text-sm font-medium text-gray-900 mb-2">{{ $field }}</div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <div class="text-gray-500 mb-1">Eski:</div>
                                    <div class="bg-red-50 text-red-800 p-2 rounded">{{ $change['old'] ?? 'Boş' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 mb-1">Yeni:</div>
                                    <div class="bg-green-50 text-green-800 p-2 rounded">{{ $change['new'] ?? 'Boş' }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Metadata -->
            @if($log->metadata)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-database mr-2 text-purple-500"></i>
                        Ek Bilgiler
                    </h3>
                    <div class="text-sm text-gray-700 bg-gray-50 p-4 rounded-lg">
                        <pre class="whitespace-pre-wrap">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- User Agent -->
            @if($log->user_agent)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-desktop mr-2 text-orange-500"></i>
                        Tarayıcı Bilgisi
                    </h3>
                    <div class="text-sm text-gray-700 bg-gray-50 p-4 rounded-lg break-all">
                        {{ $log->user_agent }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Logs modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Logs/resources/assets/sass/app.scss', 'Modules/Logs/resources/assets/js/app.js'])
</div>
