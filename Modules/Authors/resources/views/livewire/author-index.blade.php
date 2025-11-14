<div>
    @vite(['Modules/Authors/resources/assets/sass/app.scss', 'Modules/Authors/resources/assets/js/app.js'])
    
    <!-- Success Message -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Modern Header with Stats -->
    <div class="mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Yazar Yönetimi</h1>
                        <p class="text-gray-600">Sistem yazarlarını yönetin ve organize edin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $authors->total() }}</div>
                        <div class="text-sm text-gray-500">Toplam Yazar</div>
                    </div>
                    @can('create authors')
                    <a href="{{ route('authors.create') }}" class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-3 rounded-lg font-medium hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-sm hover:shadow-md">
                        <i class="fas fa-plus mr-2"></i>
                        Yeni Yazar
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6" x-data="authorsTable()">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <i class="fas fa-filter text-blue-500 mr-2"></i>
                <h3 class="text-lg font-semibold text-gray-900">Filtreler ve Arama</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Yazar Ara</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="İsim veya email ile ara...">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Durum Filtresi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-flag text-gray-400"></i>
                        </div>
                        <select wire:model.live="statusFilter" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Tüm Durumlar</option>
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ana Sayfa Filtresi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-eye text-gray-400"></i>
                        </div>
                        <select wire:model.live="mainpageFilter" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Tüm Görünürlük</option>
                            <option value="1">Gösterilen</option>
                            <option value="0">Gizlenen</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">İşlemler</label>
                    <button wire:click="clearFilters" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i>
                        Temizle
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Authors List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-list text-blue-500 mr-2"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Yazar Listesi</h3>
                </div>
                <div class="text-gray-500 text-sm">
                    {{ $authors->count() }} yazar gösteriliyor
                </div>
            </div>
        </div>
        
        @if($authors->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-user text-blue-500 mr-1"></i>Yazar
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-award text-blue-500 mr-1"></i>Başlık
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-flag text-blue-500 mr-1"></i>Durum
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-eye text-blue-500 mr-1"></i>Ana Sayfa
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-sort text-blue-500 mr-1"></i>Sıra
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-cog text-blue-500 mr-1"></i>İşlemler
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($authors as $author)
                            <tr wire:key="author-{{ $author->id }}" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($author->image)
                                            <img src="{{ Storage::url($author->image) }}" alt="{{ $author->user->name }}" class="w-10 h-10 rounded-full object-cover mr-3">
                                        @else
                                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-white font-bold text-sm">{{ substr($author->user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $author->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $author->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-award text-blue-500 mr-2"></i>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $author->title ?: 'Yazar' }}</div>
                                            <div class="text-sm text-gray-500">{{ $author->bio ? \Illuminate\Support\Str::limit($author->bio, 50) : 'Biyografi yok' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $author->status ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $author->status ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $author->show_on_mainpage ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $author->show_on_mainpage ? 'Gösteriliyor' : 'Gizli' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $author->weight }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        @can('edit authors')
                                        <a href="{{ route('authors.edit', $author) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200">
                                            <i class="fas fa-edit mr-1"></i>Düzenle
                                        </a>
                                        @endcan
                                        @can('delete authors')
                                        <button wire:click="deleteAuthor({{ $author->id }})" 
                                                wire:confirm="Bu yazarı silmek istediğinizden emin misiniz?"
                                                class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200">
                                            <i class="fas fa-trash mr-1"></i>Sil
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        {{ $authors->firstItem() }} - {{ $authors->lastItem() }} arası gösteriliyor, toplam {{ $authors->total() }} yazar
                    </div>
                    <div>
                        {{ $authors->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz yazar bulunmuyor</h3>
                <p class="text-gray-500 mb-6">İlk yazarı eklemek için yukarıdaki butonu kullanın.</p>
                <a href="{{ route('authors.create') }}" class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-3 rounded-lg font-medium hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-sm hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    İlk Yazarı Ekle
                </a>
            </div>
        @endif
    </div>
</div>