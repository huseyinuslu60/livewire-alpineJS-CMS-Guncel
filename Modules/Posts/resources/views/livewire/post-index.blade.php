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
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-newspaper text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Haber Yönetimi</h2>
                        <p class="text-gray-600">Sistem haberlerini yönetin ve organize edin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $posts->total() }}</div>
                        <div class="text-sm text-gray-500">Toplam Haber</div>
                    </div>
                    @can('create posts')
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" 
                                class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-3 rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-200 flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Yeni Haber
                            <i class="fas fa-chevron-down ml-2" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10"
                             style="display: none;">
                            <a href="{{ route('posts.create.news') }}" 
                               class="block px-4 py-3 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-150">
<i class="fas fa-file-text mr-2"></i> Haber
                            </a>
                            <a href="{{ route('posts.create.gallery') }}" 
                               class="block px-4 py-3 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-150">
                                <i class="fas fa-images mr-2"></i> Galeri
                            </a>
                            <a href="{{ route('posts.create.video') }}" 
                               class="block px-4 py-3 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-150">
                                <i class="fas fa-video mr-2"></i> Video
                            </a>
                        </div>
                    </div>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1 text-purple-500"></i>
                        Haber Ara
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               wire:model.live.debounce.300ms="search" 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm" 
                               placeholder="Başlık ile ara...">
                    </div>
                </div>
                
                <!-- Post Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-layer-group mr-1 text-purple-500"></i>
                        Tip Filtresi
                    </label>
                    <select wire:model.live="post_type" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option value="">Tüm Türler</option>
                        @foreach($postTypes as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-1 text-purple-500"></i>
                        Durum Filtresi
                    </label>
                    <select wire:model.live="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option value="">Tüm Durumlar</option>
                        @foreach($postStatuses as $status => $label)
                            <option value="{{ $status }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Editor Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-edit mr-1 text-purple-500"></i>
                        Editör Filtresi
                    </label>
                    <select wire:model.live="editorFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option value="">Tüm Editörler</option>
                        @foreach($editors as $editor)
                            <option value="{{ $editor->id }}">{{ $editor->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-1 text-purple-500"></i>
                        Kategori Filtresi
                    </label>
                    <select wire:model.live="categoryFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option value="">Tüm Kategoriler</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list mr-1 text-purple-500"></i>
                        Sayfa Başına
                    </label>
                    <select wire:model.live="perPage" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option value="10">10 Kayıt</option>
                        <option value="15">15 Kayıt</option>
                        <option value="25">25 Kayıt</option>
                        <option value="50">50 Kayıt</option>
                    </select>
                </div>
                
                <!-- Stats -->
                <div class="flex items-end">
                    <div class="flex space-x-4 w-full">
                        <div class="text-center">
                            <div class="text-lg font-bold text-purple-600">{{ $posts->count() }}</div>
                            <div class="text-xs text-gray-500">Gösterilen</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-green-600">{{ $posts->total() }}</div>
                            <div class="text-xs text-gray-500">Toplam</div>
                        </div>
                    </div>
                </div>

                
            </div>
            
        </div>
    </div>

    <!-- Bulk Actions Card -->
    <div x-data="postsTable" 
         class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6" 
         wire:loading.class="opacity-50" 
         wire:target="selectedPosts">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="flex items-center mr-4">
                        <input type="checkbox" 
                               wire:model.live="selectAll" 
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                               id="selectAll">
                        <label for="selectAll" class="ml-2 text-sm font-medium text-gray-700">
                            Tümünü Seç ({{ count($selectedPosts) }}/{{ $posts->total() }})
                        </label>
                    </div>
                </div>
                @can('edit posts')
                <div class="flex items-center space-x-3">
                    <select wire:model.live="bulkAction" 
                            class="block w-48 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option value="">-- Seçilenleri --</option>
                        @can('delete posts')
                        <option value="delete">Sil</option>
                        @endcan
                        <option value="activate">Aktif Yap</option>
                        <option value="deactivate">Pasif Yap</option>
                        <option value="newsletter_add">Bültene Ekle</option>
                        <option value="newsletter_remove">Bültenden Çıkar</option>
                    </select>
                    <button wire:click="applyBulkAction" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            @if(empty($selectedPosts)) disabled @endif>
                        <i class="fas fa-check mr-2"></i>
                        Uygula
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Posts Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- Table Header with Column Customization -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Haberler</h3>
                <div x-data="{ showColumnModal: false }">
                    <button @click="showColumnModal = true" 
                            class="inline-flex items-center px-3 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 text-sm font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-columns mr-2"></i>
                        Sütunları Özelleştir
                    </button>
                    
                    <!-- Column Customization Modal -->
                    <div x-show="showColumnModal" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 overflow-y-auto modal-container"
                         style="display: none; z-index: 999999 !important; position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;"
                         x-cloak>
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 modal-backdrop transition-opacity" 
                                 style="z-index: 999998 !important; position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;" 
                                 @click="showColumnModal = false"></div>
                            
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full modal-content"
                                 style="z-index: 999999 !important; position: relative !important;">
                                <div class="bg-white px-6 pt-6 pb-4">
                                    <div class="flex items-center mb-6">
                                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100">
                                            <i class="fas fa-columns text-purple-600 text-xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-xl font-semibold text-gray-900">
                                                Sütun Görünürlüğünü Ayarla
                                            </h3>
                                            <p class="text-sm text-gray-500 mt-1">Hangi sütunların görünmesini istediğinizi seçin</p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto">
                                        @foreach([
                                            'checkbox' => ['icon' => 'fas fa-check-square', 'label' => 'Seçim Kutusu'],
                                            'id' => ['icon' => 'fas fa-hashtag', 'label' => 'ID'],
                                            'image' => ['icon' => 'fas fa-image', 'label' => 'Resim'],
                                            'title' => ['icon' => 'fas fa-newspaper', 'label' => 'Başlık'],
                                            'category' => ['icon' => 'fas fa-tag', 'label' => 'Kategori'],
                                            'type' => ['icon' => 'fas fa-layer-group', 'label' => 'Tip'],
                                            'hit' => ['icon' => 'fas fa-eye', 'label' => 'Hit'],
                                            'status' => ['icon' => 'fas fa-flag', 'label' => 'Durum'],
                                            'creator' => ['icon' => 'fas fa-user', 'label' => 'Oluşturan'],
                                            'updater' => ['icon' => 'fas fa-edit', 'label' => 'Güncelleyen'],
                                            'date' => ['icon' => 'fas fa-calendar', 'label' => 'Tarih'],
                                            'actions' => ['icon' => 'fas fa-cog', 'label' => 'İşlemler']
                                        ] as $column => $config)
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                                            <div class="flex items-center">
                                                <i class="{{ $config['icon'] }} mr-3 text-purple-500 text-lg"></i>
                                                <span class="text-sm font-medium text-gray-700">{{ $config['label'] }}</span>
                                            </div>
                                            <label class="toggle-switch">
                                                <input type="checkbox" 
                                                       wire:model.live="visibleColumns.{{ $column }}"
                                                       class="sr-only">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                                    <button @click="showColumnModal = false" 
                                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        İptal
                                    </button>
                                    <button @click="showColumnModal = false" 
                                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        <i class="fas fa-check mr-2"></i>
                                        Tamam
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overflow-hidden">
            <!-- Table Header -->
            <div class="overflow-x-auto" wire:loading.class="opacity-50" wire:target="search,post_type,status,perPage">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-purple-50 to-purple-100">
                        <tr>
                            @if($visibleColumns['checkbox'] ?? true)
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 50px;">
                                <input type="checkbox" 
                                       wire:model.live="selectAll" 
                                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                                       id="selectAllHeader">
                            </th>
                            @endif
                            @if($visibleColumns['id'] ?? true)
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-hashtag mr-2 text-purple-500"></i>
                                    ID
                                </div>
                            </th>
                            @endif
                            @if($visibleColumns['image'] ?? true)
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-image mr-2 text-purple-500"></i>
                                    Resim
                                </div>
                            </th>
                            @endif
                            @if($visibleColumns['title'] ?? true)
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-newspaper mr-2 text-purple-500"></i>
                                    Başlık
                                </div>
                            </th>
                            @endif
                            @if($visibleColumns['category'] ?? true)
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-tag mr-2 text-purple-500"></i>
                                    Kategori
                                </div>
                            </th>
                            @endif
                            @if($visibleColumns['type'] ?? true)
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-layer-group mr-2 text-purple-500"></i>
                                    Tip
                                </div>
                            </th>
                            @endif
                            @if($visibleColumns['hit'] ?? true)
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-eye mr-2 text-purple-500"></i>
                                    Hit
                                </div>
                            </th>
                            @endif
                            @if($visibleColumns['status'] ?? true)
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-flag mr-2 text-purple-500"></i>
                                    Durum
                                </div>
                            </th>
                            @endif
                            @if($visibleColumns['creator'] ?? true)
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-user mr-2 text-purple-500"></i>
                                    Oluşturan
                                </div>
                            </th>
                            @endif
                            @if($visibleColumns['updater'] ?? true)
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-edit mr-2 text-purple-500"></i>
                                    Güncelleyen
                                </div>
                            </th>
                            @endif
                            @if($visibleColumns['date'] ?? true)
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar mr-2 text-purple-500"></i>
                                    Tarih
                                </div>
                            </th>
                            @endif
                            @if($visibleColumns['actions'] ?? true)
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-cog mr-2 text-purple-500"></i>
                                    İşlemler
                                </div>
                            </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($posts as $post)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            @if($visibleColumns['checkbox'] ?? true)
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" 
                                       wire:model.live="selectedPosts" 
                                       value="{{ $post->post_id }}" 
                                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                                       id="post_{{ $post->post_id }}">
                            </td>
                            @endif
                            @if($visibleColumns['id'] ?? true)
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        #{{ $post->post_id }}
                                    </span>
                                </div>
                            </td>
                            @endif
                            @if($visibleColumns['image'] ?? true)
                            <td class="px-6 py-4 text-center">
                                @php
                                    // Galeri türü için özel metod kullan
                                    if ($post->post_type === 'gallery') {
                                        $primaryFile = $post->getPrimaryFileForGallery();
                                    } else {
                                        $primaryFile = $post->primaryFile;
                                    }
                                    
                                    // Get spot_data for preview rendering
                                    $spotData = $post->spot_data ?? null;
                                    $spotDataJson = null;
                                    if ($spotData && isset($spotData['image'])) {
                                        $imageData = $spotData['image'];
                                        // Handle nested structure: if image has 'image' key, unwrap it
                                        if (isset($imageData['image']) && is_array($imageData['image'])) {
                                            $imageData = $imageData['image'];
                                        }
                                        $spotDataJson = json_encode(['image' => $imageData]);
                                    }
                                    
                                    // Generate imageKey for existing primary file
                                    $imageKey = $primaryFile ? 'existing:' . $primaryFile->file_id : null;
                                    $imageUrl = $primaryFile ? asset('storage/' . $primaryFile->file_path) : null;
                                @endphp
                                
                                @if($primaryFile && $primaryFile->is_image)
                                    <div class="flex justify-center image-preview-card"
                                         data-image-key="{{ $imageKey }}">
                                        {{-- Preview canvas for spot_data rendering --}}
                                        <canvas class="image-preview-canvas h-16 w-16 rounded-lg object-cover shadow-sm"
                                                data-image-key="{{ $imageKey }}"
                                                style="display: none;"></canvas>
                                        {{-- Fallback image --}}
                                        <img class="image-preview-img h-16 w-16 rounded-lg object-cover shadow-sm" 
                                             src="{{ $imageUrl }}" 
                                             alt="{{ $primaryFile->alt_text ?? $post->title }}"
                                             data-image-key="{{ $imageKey }}"
                                             data-image-url="{{ $imageUrl }}"
                                             @if($spotDataJson)
                                                 data-spot-data="{{ htmlspecialchars($spotDataJson, ENT_QUOTES, 'UTF-8', false) }}"
                                                 data-has-spot-data="true"
                                             @else
                                                 data-spot-data=""
                                                 data-has-spot-data="false"
                                             @endif
                                             data-file-id="{{ $primaryFile->file_id }}"
                                             onload="if(window.renderPreviewWithSpotData) { window.renderPreviewWithSpotData(this); } else { setTimeout(() => { if(window.renderPreviewWithSpotData) window.renderPreviewWithSpotData(this); }, 100); }">
                                    </div>
                                @else
                                    <div class="flex justify-center">
                                        <div class="h-16 w-16 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-sm">
                                            @if($post->post_type === 'gallery')
                                                <i class="fas fa-images text-white text-lg"></i>
                                            @elseif($post->post_type === 'video')
                                                <i class="fas fa-video text-white text-lg"></i>
                                            @else
                                                <i class="fas fa-newspaper text-white text-lg"></i>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </td>
                            @endif
                            @if($visibleColumns['title'] ?? true)
                            <td class="px-6 py-4">
                                <div class="min-w-0 flex-1">
                                    <h6 class="text-sm font-medium text-gray-900 line-clamp-2">
                                        {{ Str::limit($post->title, 60) }}
                                    </h6>
                                </div>
                            </td>
                            @endif
                            @if($visibleColumns['category'] ?? true)
                            <td class="px-6 py-4">
                                @if($post->categories->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($post->categories->take(2) as $category)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-tag mr-1"></i>
                                                {{ $category->name }}
                                            </span>
                                        @endforeach
                                        @if($post->categories->count() > 2)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                +{{ $post->categories->count() - 2 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">
                                        <i class="fas fa-minus mr-1"></i>
                                        Kategori yok
                                    </span>
                                @endif
                            </td>
                            @endif
                            @if($visibleColumns['type'] ?? true)
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $typeColors = [
                                        'news' => 'bg-blue-100 text-blue-800',
                                        'gallery' => 'bg-purple-100 text-purple-800',
                                        'video' => 'bg-red-100 text-red-800'
                                    ];
                                    $typeLabels = [
                                        'news' => 'Haber',
                                        'gallery' => 'Galeri',
                                        'video' => 'Video'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$post->post_type] ?? 'bg-gray-100 text-gray-800' }}">
                                    @if($post->post_type === 'gallery')
                                        <i class="fas fa-images mr-1"></i>
                                    @elseif($post->post_type === 'video')
                                        <i class="fas fa-video mr-1"></i>
                                    @else
                                        <i class="fas fa-newspaper mr-1"></i>
                                    @endif
                                    {{ $typeLabels[$post->post_type] ?? ucfirst($post->post_type) }}
                                </span>
                            </td>
                            @endif
                            @if($visibleColumns['hit'] ?? true)
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-eye text-gray-400 mr-2"></i>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($post->view_count ?? 0) }}</span>
                                </div>
                            </td>
                            @endif
                            @if($visibleColumns['status'] ?? true)
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-yellow-100 text-yellow-800',
                                        'published' => 'bg-green-100 text-green-800',
                                        'scheduled' => 'bg-blue-100 text-blue-800',
                                        'archived' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Pasif',
                                        'published' => 'Aktif',
                                        'scheduled' => 'Zamanlanmış',
                                        'archived' => 'Arşivlendi'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$post->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    <i class="fas fa-flag mr-1"></i>
                                    {{ $statusLabels[$post->status] ?? ucfirst($post->status) }}
                                </span>
                            </td>
                            @endif
                            @if($visibleColumns['creator'] ?? true)
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                                            <span class="text-white text-xs font-medium">
                                                {{ substr($post->creator->name ?? 'N/A', 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $post->creator->name ?? 'Bilinmiyor' }}</div>
                                        <div class="text-xs text-gray-500">{{ $post->created_at->format('d.m.Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            @endif
                            @if($visibleColumns['updater'] ?? true)
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($post->updater)
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                                                <span class="text-white text-xs font-medium">
                                                    {{ substr($post->updater->name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $post->updater->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $post->updated_at->format('d.m.Y') }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">
                                        <i class="fas fa-minus mr-1"></i>
                                        Güncellenmemiş
                                    </span>
                                @endif
                            </td>
                            @endif
                            @if($visibleColumns['date'] ?? true)
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $post->created_at->format('d.m.Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $post->created_at->format('H:i') }}</div>
                                    </div>
                                </div>
                            </td>
                            @endif
                            @if($visibleColumns['actions'] ?? true)
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @can('edit posts')
                                    <a href="{{ route('posts.edit', $post) }}" 
                                       class="inline-flex items-center justify-center w-8 h-8 border border-purple-300 text-xs font-medium rounded-md text-purple-700 bg-purple-50 hover:bg-purple-100 hover:text-purple-800 transition-colors duration-150"
                                       title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('edit posts')
                                    <button wire:click="toggleMainPage({{ $post->post_id }})" 
                                            class="inline-flex items-center justify-center w-8 h-8 border border-{{ $post->is_mainpage ? 'green' : 'yellow' }}-300 text-xs font-medium rounded-md text-{{ $post->is_mainpage ? 'green' : 'yellow' }}-700 bg-{{ $post->is_mainpage ? 'green' : 'yellow' }}-50 hover:bg-{{ $post->is_mainpage ? 'green' : 'yellow' }}-100 transition-colors duration-150"
                                            title="{{ $post->is_mainpage ? 'Ana sayfadan kaldır' : 'Ana sayfaya ekle' }}">
                                        <i class="fas fa-home"></i>
                                    </button>
                                    @endcan
                                    @can('delete posts')
                                    <button wire:click="deletePost({{ $post->post_id }})" 
                                            onclick="return confirm('Bu haberi silmek istediğinizden emin misiniz?') || event.stopImmediatePropagation()"
                                            class="inline-flex items-center justify-center w-8 h-8 border border-red-500 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 hover:text-red-800 transition-colors duration-150"
                                            title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-newspaper text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Haber bulunamadı</h3>
                                    <p class="text-gray-500 mb-4">Arama kriterlerinize uygun haber bulunamadı.</p>
                                    <a href="{{ route('posts.create.news') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 hover:shadow-lg transition-all duration-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Yeni Haber Oluştur
                                    </a>
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
    <div class="mt-6">
        {{ $posts->links() }}
    </div>

    {{-- Posts modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Posts/resources/assets/sass/app.scss', 'Modules/Posts/resources/assets/js/app.js', 'resources/js/image-preview-renderer/index.js'])
</div>