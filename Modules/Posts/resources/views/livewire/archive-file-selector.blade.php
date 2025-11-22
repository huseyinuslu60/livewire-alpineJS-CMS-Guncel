<div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-medium text-gray-900">
            <i class="fas fa-archive mr-2 text-blue-500"></i>
            Arşivden Dosya Seç
        </h3>
        <button 
            type="button"
            onclick="document.getElementById('archive-modal-container').style.display='none'; document.body.style.overflow='';"
            class="text-gray-400 hover:text-gray-600 transition-colors"
        >
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Search and Filters -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Arama</label>
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Dosya adı, açıklama..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dosya Türü</label>
            <select 
                wire:model.live="mimeType"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">Tüm Dosyalar</option>
                <option value="image">Resimler</option>
                <option value="video">Videolar</option>
                <option value="application">Dökümanlar</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sayfa Başına</label>
            <select 
                wire:model.live="perPage"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="12">12</option>
                <option value="24">24</option>
                <option value="48">48</option>
                <option value="96">96</option>
            </select>
        </div>

        <div class="flex items-end">
            @if($search || $mimeType)
                <button 
                    wire:click="$set('search', ''); $set('mimeType', '')"
                    class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
                >
                    <i class="fas fa-times mr-2"></i>
                    Filtreleri Temizle
                </button>
            @endif
        </div>
    </div>

    <!-- Selection Controls -->
    <div class="flex items-center justify-between mb-4 p-3 bg-blue-50 rounded-lg">
        <div class="flex items-center">
            <input 
                type="checkbox" 
                wire:model.live="selectAll"
                wire:click="toggleSelectAll"
                id="select-all"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            >
            <label for="select-all" class="ml-2 text-sm font-medium text-gray-700">
                Tümünü Seç ({{ count($selectedFiles) }} dosya seçildi)
            </label>
        </div>
        <div class="flex gap-2">
            <button 
                wire:click="clearSelection"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm"
            >
                <i class="fas fa-eraser mr-1"></i>
                Seçimi Temizle
            </button>
            <button 
                wire:click="confirmSelection"
                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors text-sm font-medium"
            >
                <i class="fas fa-check mr-1"></i>
                Seç ({{ count($selectedFiles) }})
            </button>
        </div>
    </div>

    <!-- Files Grid -->
    @if($files->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($files as $file)
                <div 
                    class="relative border-2 rounded-lg overflow-hidden cursor-pointer transition-all hover:shadow-lg {{ in_array($file->file_id, $selectedFiles) ? 'border-blue-500 ring-2 ring-blue-300' : 'border-gray-200' }}"
                    wire:click="toggleFileSelection({{ $file->file_id }})"
                >
                    <!-- Selection Checkbox -->
                    <div class="absolute top-2 left-2 z-10">
                        <div class="w-6 h-6 bg-white rounded-full flex items-center justify-center shadow-md">
                            @if(in_array($file->file_id, $selectedFiles))
                                <i class="fas fa-check-circle text-blue-500 text-lg"></i>
                            @else
                                <i class="far fa-circle text-gray-400 text-lg"></i>
                            @endif
                        </div>
                    </div>

                    <!-- File Type Badge -->
                    <div class="absolute top-2 right-2 z-10">
                        <span class="px-2 py-1 bg-green-500 text-white text-xs rounded">
                            @if($file->isImage() || str_starts_with($file->mime_type, 'image/'))
                                Resim
                            @elseif(str_starts_with($file->mime_type, 'video/'))
                                Video
                            @else
                                Dosya
                            @endif
                        </span>
                    </div>

                    <!-- File Preview -->
                    <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden relative">
                        @php
                            $isImage = $file->isImage() || str_starts_with($file->mime_type ?? '', 'image/');
                            $imageUrl = $file->url ?? asset('storage/' . $file->file_path);
                        @endphp
                        @if($isImage)
                            <img 
                                src="{{ $imageUrl }}" 
                                alt="{{ $file->title }}"
                                class="w-full h-full object-cover"
                                loading="lazy"
                                onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-image text-gray-400 text-4xl\'></i>';"
                            >
                        @elseif(str_starts_with($file->mime_type ?? '', 'video/'))
                            <i class="fas fa-video text-gray-400 text-4xl"></i>
                        @else
                            <i class="fas fa-file text-gray-400 text-4xl"></i>
                        @endif
                    </div>

                    <!-- File Info -->
                    <div class="p-2 bg-white">
                        <p class="text-xs font-medium text-gray-900 truncate" title="{{ $file->title }}">
                            {{ $file->title }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($file->created_at)
                                {{ $file->created_at->format('d.m.Y H:i') }}
                            @else
                                -
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">
                            @if($file->file_size)
                                {{ number_format($file->file_size / 1024, 2) }} KB
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $files->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <i class="fas fa-folder-open text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-lg">Dosya bulunamadı</p>
            @if($search || $mimeType)
                <button 
                    wire:click="$set('search', ''); $set('mimeType', '')"
                    class="mt-4 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
                >
                    Filtreleri Temizle
                </button>
            @endif
        </div>
    @endif

    <!-- Flash Messages -->
    @if(session()->has('error'))
        <div class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif
</div>

@script
<script>
    // Livewire event'ini JavaScript event'ine dönüştür
    Livewire.on('filesSelected', (files) => {
        const event = new CustomEvent('filesSelected', {
            detail: files
        });
        window.dispatchEvent(event);
    });

    // Modal kapatma event'i
    Livewire.on('closeArchiveModal', () => {
        const modal = document.getElementById('archive-modal-container');
        if (modal) {
            modal.style.display = 'none';
        }
        document.body.style.overflow = '';
    });
</script>
@endscript

