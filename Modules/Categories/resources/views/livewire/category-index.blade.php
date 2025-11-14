<div x-data="categoriesTable()">
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
        <div x-data="{ showError: true }" 
             x-show="showError" 
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
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-tags text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Kategori Yönetimi</h2>
                        <p class="text-gray-600">Sistem kategorilerini yönetin ve organize edin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @can('create categories')
                    <a href="{{ route('categories.create') }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg hover:shadow-xl transition-all duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Yeni Kategori
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Arama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-indigo-500"></i>
                        Arama
                    </label>
                    <input type="text" 
                           wire:model.live="search" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                           placeholder="Kategori ara...">
                </div>

                <!-- Durum -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-filter mr-1 text-indigo-500"></i>
                        Durum
                    </label>
                    <select wire:model.live="status" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Tümü</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Pasif</option>
                    </select>
                </div>

                <!-- Tür -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-1 text-indigo-500"></i>
                        Tür
                    </label>
                    <select wire:model.live="typeFilter" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Tüm Türler</option>
                        <option value="news">📰 Haber</option>
                        <option value="gallery">🖼️ Galeri</option>
                        <option value="video">🎥 Video</option>
                    </select>
                </div>

                <!-- Sırala -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort mr-1 text-indigo-500"></i>
                        Sırala
                    </label>
                    <select wire:model.live="sortBy" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="name">İsim</option>
                        <option value="weight">Ağırlık</option>
                        <option value="created_at">Oluşturulma Tarihi</option>
                        <option value="updated_at">Güncellenme Tarihi</option>
                    </select>
                </div>

                <!-- Sayfa Başına -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list mr-1 text-indigo-500"></i>
                        Sayfa Başına
                    </label>
                    <select wire:model.live="perPage" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="10">10 / sayfa</option>
                        <option value="20">20 / sayfa</option>
                        <option value="50">50 / sayfa</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-hashtag mr-1"></i>
                            ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-tag mr-1"></i>
                            Kategori
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-link mr-1"></i>
                            Slug
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-tag mr-1"></i>
                            Tür
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-weight-hanging mr-1"></i>
                            Ağırlık
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-eye mr-1"></i>
                            Menüde Göster
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-flag mr-1"></i>
                            Durum
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-calendar mr-1"></i>
                            Tarih
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-cog mr-1"></i>
                            İşlemler
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <!-- ID -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ $category->category_id }}
                            </td>

                            <!-- Kategori Adı -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                            <i class="fas fa-folder text-indigo-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                        @if($category->meta_title)
                                            <div class="text-sm text-gray-500">{{ Str::limit($category->meta_title, 30) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Slug -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $category->slug }}
                                </span>
                            </td>

                            <!-- Tür -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($category->type)
                                    @if($category->type === 'news')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-newspaper mr-1"></i>
                                            Haber
                                        </span>
                                    @elseif($category->type === 'gallery')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-images mr-1"></i>
                                            Galeri
                                        </span>
                                    @elseif($category->type === 'video')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-video mr-1"></i>
                                            Video
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-tag mr-1"></i>
                                            {{ ucfirst($category->type) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-question-circle mr-1"></i>
                                        Belirtilmemiş
                                    </span>
                                @endif
                            </td>

                            <!-- Ağırlık -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-weight-hanging mr-1"></i>
                                    {{ $category->weight ?? 0 }}
                                </span>
                            </td>

                            <!-- Menüde Göster -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($category->show_in_menu)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-eye mr-1"></i>
                                        Evet
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-eye-slash mr-1"></i>
                                        Hayır
                                    </span>
                                @endif
                            </td>

                            <!-- Durum -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($category->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Pasif
                                    </span>
                                @endif
                            </td>

                            <!-- Tarih -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>
                                    <div class="text-xs">
                                        <i class="fas fa-plus mr-1"></i>
                                        {{ $category->created_at ? $category->created_at->format('d.m.Y H:i') : '-' }}
                                    </div>
                                    @if($category->updated_at && $category->updated_at != $category->created_at)
                                        <div class="text-xs text-gray-400">
                                            <i class="fas fa-edit mr-1"></i>
                                            {{ $category->updated_at->format('d.m.Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- İşlemler -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @can('edit categories')
                                    <a href="{{ route('categories.edit', $category->category_id) }}" 
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors duration-150">
                                        <i class="fas fa-edit mr-1"></i>
                                        Düzenle
                                    </a>
                                    @endcan
                                    @can('delete categories')
                                    <button onclick="if(confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')) { $wire.deleteCategory({{ $category->category_id }}) }" 
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-red-700 bg-red-100 hover:bg-red-200 transition-colors duration-150">
                                        <i class="fas fa-trash mr-1"></i>
                                        Sil
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-tags text-gray-400 text-xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Kategori Bulunamadı</h3>
                                    <p class="text-gray-600 mb-6">Henüz hiç kategori oluşturulmamış.</p>
                                    <a href="{{ route('categories.create') }}" 
                                       class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg hover:shadow-xl transition-all duration-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        İlk Kategoriyi Oluştur
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($categories->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center text-sm text-gray-700 mb-4 sm:mb-0">
                    <i class="fas fa-info-circle mr-2 text-indigo-500"></i>
                    <span>
                        <strong>{{ $categories->firstItem() }}</strong> - <strong>{{ $categories->lastItem() }}</strong> 
                        arası gösteriliyor, toplam <strong>{{ $categories->total() }}</strong> kategori
                    </span>
                </div>
                <div class="flex justify-center sm:justify-end">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Categories modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Categories/resources/assets/sass/app.scss', 'Modules/Categories/resources/assets/js/app.js'])
</div>
</div>