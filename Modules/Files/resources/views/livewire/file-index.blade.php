<div x-data="filesTable()" class="bg-[var(--surface)] border border-[var(--border-subtle)] rounded-xl shadow-sm">
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

    <!-- Header -->
    <div class="bg-[var(--surface)] rounded-xl shadow-sm border border-[var(--border-subtle)] mb-6">
        <div class="px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-folder-open text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-[var(--text)]">Medya Kütüphanesi</h1>
                        <p class="text-[var(--text-muted)] mt-1">Dosyalarınızı yönetin ve düzenleyin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @can('create files')
                    <button wire:click="openUploadModal"
                            class="inline-flex items-center px-4 py-2 bg-[var(--accent)] hover:bg-[var(--accent-strong)] text-[var(--text-on-accent)] rounded-lg text-sm font-medium transition-colors duration-150">
                        <i class="fas fa-upload mr-2"></i>
                        Dosya Yükle
                    </button>
                    @endcan
                    <button wire:click="toggleSelectionMode"
                            class="inline-flex items-center px-4 py-2 bg-[var(--accent)] hover:bg-[var(--accent-strong)] text-[var(--text-on-accent)] rounded-lg text-sm font-medium transition-colors duration-150">
                        <i class="fas fa-check-square mr-2"></i>
                        Seçim Modu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-[var(--surface)] rounded-xl shadow-sm border border-[var(--border-subtle)] mb-6">
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-[var(--text)] mb-2">Arama</label>
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="block w-full px-4 py-2 pl-10 bg-[var(--input-bg)] border border-[var(--border-subtle)] rounded-lg focus:ring-2 focus:ring-[var(--accent)] focus:border-[var(--accent)] text-[var(--text)] placeholder:text-[var(--text-muted)] text-sm"
                               placeholder="Dosya adı, açıklama...">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- File Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-[var(--text)] mb-2">Dosya Türü</label>
                    <select wire:model.live="mimeType"
                            class="block w-full px-4 py-2 bg-[var(--input-bg)] border border-[var(--border-subtle)] rounded-lg focus:ring-2 focus:ring-[var(--accent)] focus:border-[var(--accent)] text-[var(--text)] text-sm">
                        <option value="">Tüm Dosyalar</option>
                        <option value="image">Resimler</option>
                        <option value="video">Videolar</option>
                        <option value="audio">Ses Dosyaları</option>
                        <option value="application/pdf">PDF Dosyaları</option>
                        <option value="text">Metin Dosyaları</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-[var(--text)] mb-2">Sayfa Başına</label>
                    <select wire:model.live="perPage"
                            class="block w-full px-4 py-2 bg-[var(--input-bg)] border border-[var(--border-subtle)] rounded-lg focus:ring-2 focus:ring-[var(--accent)] focus:border-[var(--accent)] text-[var(--text)] text-sm">
                        <option value="24">24</option>
                        <option value="48">48</option>
                        <option value="96">96</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button wire:click="clearFilters"
                            class="w-full px-4 py-2 bg-[var(--surface-alt)] hover:bg-[var(--bg-muted)] text-[var(--text)] rounded-lg text-sm font-medium transition-colors duration-150">
                        <i class="fas fa-times mr-2"></i>
                        Filtreleri Temizle
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Selection Mode Bar -->
    @if($selectionMode)
    <div class="bg-blue-50 border-b border-blue-200 p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center text-blue-700">
                <i class="fas fa-check-square mr-2"></i>
                <span class="font-medium">Seçim Modu Aktif</span>
                <span class="ml-2 text-sm">({{ count($selectedFiles) }} dosya seçildi)</span>
            </div>
            <div class="flex items-center space-x-2">
                <button wire:click="selectAllFiles"
                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm">
                    Tümünü Seç
                </button>
                <button wire:click="clearSelection"
                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm">
                    Seçimi Temizle
                </button>
                <button wire:click="toggleSelectionMode"
                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm">
                    Çık
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Bulk Actions -->
    @if(count($selectedFiles) > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center text-yellow-700">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span class="font-medium">{{ count($selectedFiles) }} dosya seçildi</span>
            </div>
            @can('delete files')
            <div class="flex items-center space-x-2">
                <select wire:model="bulkAction"
                        class="px-3 py-1 border border-gray-300 rounded text-sm">
                    <option value="">İşlem Seçin</option>
                    <option value="delete">Sil</option>
                </select>
                <button wire:click="applyBulkAction"
                        class="px-4 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-sm font-medium">
                    <i class="fas fa-check mr-1"></i>
                    Uygula
                </button>
            </div>
            @endcan
        </div>
    </div>
    @endif

    <!-- Files Grid -->
    <div class="bg-[var(--surface)] rounded-xl shadow-sm border border-[var(--border-subtle)]">
        @if($files->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6 p-6">
                @foreach($files as $file)
                <div class="file-card bg-[var(--surface)] border border-[var(--border-subtle)] rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden group">
                    <!-- File Preview -->
                    <div class="relative">
                        @if($file->isImage())
                            <img src="{{ $file->url }}"
                                 alt="{{ $file->alt_text }}"
                                 class="w-full h-32 object-cover group-hover:scale-110 transition-transform duration-300">
                        @else
                            <div class="w-full h-32 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <i class="{{ getFileIcon($file->mime_type) }} text-3xl text-gray-500"></i>
                            </div>
                        @endif

                        <!-- Selection Checkbox -->
                        @if($selectionMode)
                        <div class="absolute top-2 left-2">
                            <input type="checkbox"
                                   wire:model="selectedFiles"
                                   value="{{ $file->file_id }}"
                                   class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500">
                        </div>
                        @endif

                        <!-- File Type Badge -->
                        <div class="absolute top-2 right-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ getFileBadgeColor($file->mime_type) }}">
                                @if($file->isImage())
                                    <i class="fas fa-image mr-1"></i>Resim
                                @elseif(str_contains($file->mime_type, 'video'))
                                    <i class="fas fa-video mr-1"></i>Video
                                @elseif(str_contains($file->mime_type, 'audio'))
                                    <i class="fas fa-music mr-1"></i>Ses
                                @elseif(str_contains($file->mime_type, 'pdf'))
                                    <i class="fas fa-file-pdf mr-1"></i>PDF
                                @else
                                    <i class="fas fa-file mr-1"></i>Dosya
                                @endif
                            </span>
                        </div>

                        <!-- Actions Dropdown -->
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                        class="p-2 bg-white/80 hover:bg-white rounded-full shadow-lg">
                                    <i class="fas fa-ellipsis-v text-gray-600"></i>
                                </button>

                                <div x-show="open"
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 top-8 z-50 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                                    @can('edit files')
                                    <button wire:click="editFile({{ $file->file_id }})"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-edit mr-2"></i> Düzenle
                                    </button>
                                    @endcan
                                    <a href="{{ route('files.download', $file->file_id) }}"
                                       class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-download mr-2"></i> İndir
                                    </a>
                                    <div class="border-t border-gray-200 my-1"></div>
                                    @can('delete files')
                                    <button wire:click="deleteFile({{ $file->file_id }})"
                                            wire:confirm="Bu dosyayı silmek istediğinizden emin misiniz?"
                                            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-trash mr-2"></i> Sil
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Info -->
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 text-sm truncate mb-1">
                            {{ $file->title }}
                        </h3>

                        @if($file->alt_text)
                        <div class="text-xs text-blue-600 mb-1">
                            <i class="fas fa-tag mr-1"></i>
                            <span class="truncate">{{ $file->alt_text }}</span>
                        </div>
                        @endif

                        @if($file->caption)
                        <div class="text-xs text-gray-600 mb-2">
                            <i class="fas fa-comment mr-1"></i>
                            <span class="truncate">{{ $file->caption }}</span>
                        </div>
                        @endif

                        <div class="text-xs text-gray-500 space-y-1">
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-1"></i>
                                {{ $file->created_at->format('d.m.Y H:i') }}
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-weight mr-1"></i>
                                @if($file->file_size && $file->file_size > 0)
                                    {{ formatFileSize($file->file_size) }}
                                @else
                                    <span class="text-gray-400">Boyut bilinmiyor</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mb-6 mt-6 mx-4">
                {{ $files->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-folder-open text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz dosya yok</h3>
                <p class="text-gray-600 mb-6">İlk dosyanızı yükleyerek başlayın</p>
                <button wire:click="openUploadModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors duration-150">
                    <i class="fas fa-upload mr-2"></i>
                    Dosya Yükle
                </button>
            </div>
        @endif
    </div>

    <!-- Edit File Modal -->
    @if($editingFile)
    <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="edit-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-[10000]">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900" id="edit-modal-title">
                            <i class="fas fa-edit mr-2 text-blue-500"></i>
                            Dosya Düzenle
                        </h3>
                        <button wire:click="closeEditModal"
                                class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="updateFile">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Alt Metin
                                </label>
                                <input type="text"
                                       wire:model="editAltText"
                                       class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                       placeholder="Dosya için alt metin...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Açıklama
                                </label>
                                <textarea wire:model="editCaption"
                                          rows="3"
                                          class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                          placeholder="Dosya açıklaması..."></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end space-x-3">
                            <button type="button"
                                    wire:click="closeEditModal"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors duration-150">
                                İptal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors duration-150">
                                <i class="fas fa-save mr-1"></i>
                                Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Upload Modal -->
    @if($showUploadModal)
    <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="upload-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeUploadModal" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full relative z-[10000]">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900" id="upload-modal-title">
                            <i class="fas fa-upload mr-2 text-green-500"></i>
                            Dosya Yükle
                        </h3>
                        <button wire:click="closeUploadModal"
                                class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    @livewire('files.file-upload')
                </div>
            </div>
        </div>
    </div>
    @endif

    @vite(['Modules/Files/resources/assets/js/app.js', 'Modules/Files/resources/assets/sass/app.scss'])
</div>
