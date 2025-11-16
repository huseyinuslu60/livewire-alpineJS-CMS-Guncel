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
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Kullanıcı Yönetimi</h2>
                        <p class="text-gray-600">Sistem kullanıcılarını yönetin ve organize edin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $users->total() }}</div>
                        <div class="text-sm text-gray-500">Toplam Kullanıcı</div>
                    </div>
                    @can('create users')
                    <a href="{{ route('user.create') }}" 
                       class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-200 flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Yeni Kullanıcı
                    </a>
                    @endcan
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-blue-500"></i>
                        Kullanıcı Ara
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               wire:model.live.debounce.300ms="search" 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                               placeholder="Ad veya email ile ara...">
                    </div>
                </div>
                
                <!-- Role Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-shield-alt mr-1 text-blue-500"></i>
                        Rol Filtresi
                    </label>
                    <select wire:model.live="roleFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Tüm Roller</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list mr-1 text-blue-500"></i>
                        Sayfa Başına
                    </label>
                    <select wire:model.live="perPage" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="10">10 Kayıt</option>
                        <option value="25">25 Kayıt</option>
                        <option value="50">50 Kayıt</option>
                    </select>
                </div>
                
                <!-- Stats -->
                <div class="flex items-end">
                    <div class="flex space-x-4 w-full">
                        <div class="text-center">
                            <div class="text-lg font-bold text-blue-600">{{ $users->count() }}</div>
                            <div class="text-xs text-gray-500">Gösterilen</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-green-600">{{ $users->total() }}</div>
                            <div class="text-xs text-gray-500">Toplam</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-hidden" wire:loading.class="opacity-50" wire:target="search,roleFilter,perPage,sortBy">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button type="button" wire:click="sort('id')" class="flex items-center text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-hashtag mr-2"></i>
                                    ID
                                    @if($sortBy === 'id')
                                        <i class="fas fa-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button type="button" wire:click="sort('name')" class="flex items-center text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-user mr-2"></i>
                                    Kullanıcı
                                    @if($sortBy === 'name')
                                        <i class="fas fa-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button type="button" wire:click="sort('email')" class="flex items-center text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-envelope mr-2"></i>
                                    Email
                                    @if($sortBy === 'email')
                                        <i class="fas fa-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="flex items-center text-gray-500">
                                    <i class="fas fa-shield-alt mr-2"></i>
                                    Rol
                                </span>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button type="button" wire:click="sort('created_at')" class="flex items-center text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-calendar mr-2"></i>
                                    Kayıt Tarihi
                                    @if($sortBy === 'created_at')
                                        <i class="fas fa-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2 text-blue-500"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="flex items-center justify-center text-gray-500">
                                    <i class="fas fa-cog mr-2"></i>
                                    İşlemler
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-bold text-blue-600">{{ $user->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-white font-bold text-sm">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">Kullanıcı</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                    <span class="text-sm text-gray-900">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        @php
                                            $roleColors = [
                                                'admin' => 'bg-red-100 text-red-800',
                                                'editor' => 'bg-yellow-100 text-yellow-800',
                                                'yazar' => 'bg-blue-100 text-blue-800',
                                                'user' => 'bg-gray-100 text-gray-800'
                                            ];
                                            $color = $roleColors[$role->name] ?? 'bg-blue-100 text-blue-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                            <i class="fas fa-shield-alt mr-1"></i>
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $user->created_at->format('d.m.Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->created_at->format('H:i') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    @can('edit users')
                                    @if(auth()->user()->hasRole('super_admin') || !$user->hasRole('super_admin'))
                                    <a href="{{ route('user.edit', $user) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-md text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors duration-150">
                                        <i class="fas fa-edit mr-1"></i>
                                        Düzenle
                                    </a>
                                    @endif
                                    @endcan
                                    @can('delete users')
                                    @if(auth()->user()->hasRole('super_admin') || !$user->hasRole('super_admin'))
                                    <button type="button"
                                            class="inline-flex items-center px-3 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors duration-150"
                                            wire:click="deleteUser({{ $user->id }})"
                                            onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?') || event.stopImmediatePropagation()">
                                        <i class="fas fa-trash mr-1"></i>
                                        Sil
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-users text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Kullanıcı bulunamadı</h3>
                                    <p class="text-gray-500">Arama kriterlerinizi değiştirerek tekrar deneyin</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
        <div class="px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center mb-4 sm:mb-0">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <span class="text-sm text-gray-700">
                        <strong>{{ $users->firstItem() }}</strong> - <strong>{{ $users->lastItem() }}</strong> 
                        arası gösteriliyor, toplam <strong>{{ $users->total() }}</strong> kullanıcı
                    </span>
                </div>
                <div class="flex justify-end">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
    
{{-- User modülü asset dosyalarını dahil et --}}
@vite(['Modules/User/resources/assets/sass/app.scss', 'Modules/User/resources/assets/js/app.js'])
</div>
