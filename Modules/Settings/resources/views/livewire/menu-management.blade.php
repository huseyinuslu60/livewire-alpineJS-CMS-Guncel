<div>

    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[var(--text)]">
                    <i class="fas fa-bars mr-2 text-indigo-600"></i>
                    Menü Yönetimi
                </h1>
                <p class="text-[var(--text-muted)] mt-1">
                    Admin panel menü yapısını yönetin ve düzenleyin
                </p>
            </div>
            <button
                type="button"
                wire:click="showCreateModal"
                wire:loading.attr="disabled"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <i class="fas fa-plus mr-2"></i>
                <span wire:loading.remove wire:target="showCreateModal">Yeni Menü Öğesi</span>
                <span wire:loading wire:target="showCreateModal">Yükleniyor...</span>
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="mb-6 bg-emerald-50 text-emerald-900 rounded-xl p-4 border border-emerald-200">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 bg-red-50 text-red-900 rounded-xl p-4 border border-red-200">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Hierarchical Menu Items -->
    <div class="space-y-4 sortable-list" data-parent="0">
        @forelse($menuItems as $item)
            <div class="bg-[var(--card)] rounded-xl shadow-sm border border-[var(--border)]">
                <!-- Parent Menu Item -->
                <div class="sortable-item p-4 border-b border-[var(--border)] menu-level-1"
                     data-id="{{ $item['id'] }}"
                     data-sort-order="{{ $item['sort_order'] }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <!-- Drag Handle -->
                            <div class="drag-handle mr-3 cursor-move text-gray-400 hover:text-gray-600">
                                <i class="fas fa-grip-vertical"></i>
                            </div>

                            <!-- Parent Item Info -->
                            <div class="flex items-center flex-1">
                                    @if($item['icon'])
                                    <i class="{{ $item['icon'] }} mr-3 text-gray-400 text-lg"></i>
                                    @endif
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h3 class="text-lg font-semibold text-[var(--text)]">
                                            {{ $item['title'] }}
                                        </h3>
                                        <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-600 text-white dark:bg-blue-900/30 dark:text-blue-300">
                                            Ana Menü
                                        </span>
                                        </div>
                                        <div class="text-sm text-[var(--text-muted)]">
                                            {{ $item['name'] }}
                                        @if($item['route'])
                                            • {{ $item['route'] }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Parent Actions -->
                            <div class="flex items-center space-x-3">
                                <button
                                    type="button"
                                    wire:click="toggleActive({{ $item['id'] }})"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors duration-200 disabled:opacity-50
                                        @if($item['is_active'])
                                            bg-green-100 text-green-800 hover:bg-green-200
                                        @else
                                            bg-red-100 text-red-800 hover:bg-red-200
                                        @endif">
                                    <span wire:loading.remove wire:target="toggleActive({{ $item['id'] }})">{{ $item['is_active'] ? 'Aktif' : 'Pasif' }}</span>
                                    <span wire:loading wire:target="toggleActive({{ $item['id'] }})">...</span>
                                </button>

                                <div class="flex items-center space-x-2">
                                    <button
                                        type="button"
                                        wire:click="showCreateSubmenuModal({{ $item['id'] }})"
                                        wire:loading.attr="disabled"
                                        class="text-green-600 hover:text-green-900 transition-colors duration-200 p-1 disabled:opacity-50"
                                        title="Alt Menü Ekle"
                                    >
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="showEditModal({{ $item['id'] }})"
                                        wire:loading.attr="disabled"
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200 p-1 disabled:opacity-50"
                                        title="Düzenle"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deleteMenuItem({{ $item['id'] }})"
                                        wire:confirm="Bu menü öğesini silmek istediğinizden emin misiniz?"
                                        wire:loading.attr="disabled"
                                        class="text-red-600 hover:text-red-900 transition-colors duration-200 p-1 disabled:opacity-50"
                                        title="Sil"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Children Menu Items -->
                @if(!empty($item['children']))
                    <div class="p-4 bg-[var(--bg-muted)] menu-level-2">
                        <div class="space-y-2 sortable-list" data-parent="{{ $item['id'] }}">
                            @foreach($item['children'] as $child)
                                <div class="sortable-item bg-[var(--card)] rounded-lg border border-[var(--border)] p-3"
                                     data-id="{{ $child['id'] }}"
                                     data-sort-order="{{ $child['sort_order'] }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center flex-1">
                                            <!-- Child Drag Handle -->
                                            <div class="drag-handle mr-3 cursor-move text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-grip-vertical"></i>
                                            </div>

                                            <!-- Child Item Info -->
                                            <div class="flex items-center flex-1">
                                                @if($child['icon'])
                                                    <i class="{{ $child['icon'] }} mr-3 text-gray-400"></i>
                                                @endif
                                                <div class="flex-1">
                                                    <div class="flex items-center">
                                                        <span class="text-sm font-medium text-[var(--text)]">
                                                            {{ $child['title'] }}
                                                        </span>
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-slate-600 text-white dark:bg-green-900/30 dark:text-green-300">
                                                            Alt Menü
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-[var(--text-muted)]">
                                                        {{ $child['name'] }}
                                                        @if($child['route'])
                                                            • {{ $child['route'] }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Child Actions -->
                                            <div class="flex items-center space-x-3">
                                                <button
                                                    type="button"
                                                    wire:click="toggleActive({{ $child['id'] }})"
                                                    wire:loading.attr="disabled"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium transition-colors duration-200 disabled:opacity-50
                                                        @if($child['is_active'])
                                                            bg-green-100 text-green-800 hover:bg-green-200
                                                        @else
                                                            bg-red-100 text-red-800 hover:bg-red-200
                                                        @endif">
                                                    <span wire:loading.remove wire:target="toggleActive({{ $child['id'] }})">{{ $child['is_active'] ? 'Aktif' : 'Pasif' }}</span>
                                                    <span wire:loading wire:target="toggleActive({{ $child['id'] }})">...</span>
                                                </button>

                                                <div class="flex items-center space-x-1">
                                                    <button
                                                        type="button"
                                                        wire:click="showCreateSubSubmenuModal({{ $child['id'] }})"
                                                        wire:loading.attr="disabled"
                                                        class="text-purple-600 hover:text-purple-900 transition-colors duration-200 p-1 disabled:opacity-50"
                                                        title="Alt-Alt Menü Ekle"
                                                    >
                                                        <i class="fas fa-plus text-sm"></i>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="showEditModal({{ $child['id'] }})"
                                                        wire:loading.attr="disabled"
                                                        class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200 p-1 disabled:opacity-50"
                                                        title="Düzenle"
                                                    >
                                                        <i class="fas fa-edit text-sm"></i>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="deleteMenuItem({{ $child['id'] }})"
                                                        wire:confirm="Bu menü öğesini silmek istediğinizden emin misiniz?"
                                                        wire:loading.attr="disabled"
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-200 p-1 disabled:opacity-50"
                                                        title="Sil"
                                                    >
                                                        <i class="fas fa-trash text-sm"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sub-Children Menu Items (3rd Level) -->
                                    @if(!empty($child['children']))
                                        <div class="mt-3 p-3 bg-[var(--bg-muted)] rounded-lg menu-level-3">
                                            <div class="space-y-2 sortable-list" data-parent="{{ $child['id'] }}">
                                                @foreach($child['children'] as $subChild)
                                                    <div class="sortable-item bg-[var(--card)] rounded-lg border border-[var(--border)] p-2"
                                                         data-id="{{ $subChild['id'] }}"
                                                         data-sort-order="{{ $subChild['sort_order'] }}">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center flex-1">
                                                                <!-- Sub-Child Drag Handle -->
                                                                <div class="drag-handle mr-2 cursor-move text-gray-400 hover:text-gray-600">
                                                                    <i class="fas fa-grip-vertical text-xs"></i>
                                                                </div>

                                                                <!-- Sub-Child Item Info -->
                                                                <div class="flex items-center flex-1">
                                                                    @if($subChild['icon'])
                                                                        <i class="{{ $subChild['icon'] }} mr-2 text-gray-400 text-sm"></i>
                                                                    @endif
                                                                    <div class="flex-1">
                                                                        <div class="flex items-center">
                                                                            <span class="text-xs font-medium text-[var(--text)]">
                                                                                {{ $subChild['title'] }}
                                                                            </span>
                                                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-600 text-white dark:bg-purple-900/30 dark:text-purple-300">
                                                                                Alt-Alt Menü
                                                                            </span>
                                                                        </div>
                                                                        <div class="text-xs text-[var(--text-muted)]">
                                                                            {{ $subChild['name'] }}
                                                                            @if($subChild['route'])
                                                                                • {{ $subChild['route'] }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Sub-Child Actions -->
                                                                <div class="flex items-center space-x-2">
                                                                    <button
                                                                        type="button"
                                                                        wire:click="toggleActive({{ $subChild['id'] }})"
                                                                        wire:loading.attr="disabled"
                                                                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium transition-colors duration-200 disabled:opacity-50
                                                                            @if($subChild['is_active'])
                                                                                bg-green-100 text-green-800 hover:bg-green-200
                                                                            @else
                                                                                bg-red-100 text-red-800 hover:bg-red-200
                                                                            @endif">
                                                                        <span wire:loading.remove wire:target="toggleActive({{ $subChild['id'] }})">{{ $subChild['is_active'] ? 'Aktif' : 'Pasif' }}</span>
                                                                        <span wire:loading wire:target="toggleActive({{ $subChild['id'] }})">...</span>
                                                                    </button>

                                                                    <div class="flex items-center space-x-1">
                                                                        <button
                                                                            type="button"
                                                                            wire:click="showEditModal({{ $subChild['id'] }})"
                                                                            wire:loading.attr="disabled"
                                                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200 p-1 disabled:opacity-50"
                                                                            title="Düzenle"
                                                                        >
                                                                            <i class="fas fa-edit text-xs"></i>
                                                                        </button>
                                                                        <button
                                                                            type="button"
                                                                            wire:click="deleteMenuItem({{ $subChild['id'] }})"
                                                                            wire:confirm="Bu menü öğesini silmek istediğinizden emin misiniz?"
                                                                            wire:loading.attr="disabled"
                                                                            class="text-red-600 hover:text-red-900 transition-colors duration-200 p-1 disabled:opacity-50"
                                                                            title="Sil"
                                                                        >
                                                                            <i class="fas fa-trash text-xs"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
                    @empty
            <div class="bg-[var(--card)] rounded-xl shadow-sm border border-[var(--border)] p-12 text-center">
                <i class="fas fa-bars text-4xl mb-4 block text-[var(--text-muted)]"></i>
                <p class="text-lg font-medium text-[var(--text)]">Henüz menü öğesi yok</p>
                <p class="text-sm text-[var(--text-muted)]">Yeni menü öğesi eklemek için yukarıdaki butonu kullanın.</p>
            </div>
                    @endforelse
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
    <div
        class="fixed inset-0 z-50 overflow-y-auto"
    >
        <!-- Modal Overlay -->
        <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity z-40"
            wire:click="closeModal"
        ></div>

        <div
            class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 relative z-50"
        >
            <div
                class="relative inline-block align-bottom text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full bg-[var(--card)] rounded-lg shadow-xl border border-[var(--border)]"
                @click.stop
            >
                    <form wire:submit.prevent="saveMenuItem">
                        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="w-full">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg leading-6 font-medium text-[var(--text)]">
                                        {{ $isEditing ? 'Menü Öğesini Düzenle' : 'Yeni Menü Öğesi' }}
                                    </h3>
                                        <button
                                            type="button"
                                            wire:click="closeModal"
                                            class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                        >
                                            <i class="fas fa-times text-xl"></i>
                                        </button>
                                    </div>

                                    <!-- Validation Errors Summary -->
                                    @if ($errors->any())
                                        <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                            <div class="flex items-start">
                                                <i class="fas fa-exclamation-circle text-red-500 dark:text-red-400 mt-0.5 mr-2"></i>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-1">
                                                        Lütfen aşağıdaki hataları düzeltin:
                                                    </p>
                                                    <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-1">
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="space-y-4">
                                        <!-- Name -->
                                        <div>
                                            <label class="block text-sm font-medium text-[var(--text)] mb-1">
                                                Ad (Name)
                                            </label>
                                            <input
                                                type="text"
                                                wire:model="name"
                                                class="w-full px-3 py-2 border rounded-md shadow-sm bg-[var(--input-bg)] text-[var(--text)] focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @else border-[var(--border)] @enderror"
                                                placeholder="menu-item-name"
                                            >
                                            @error('name')
                                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Title -->
                                        <div>
                                            <label class="block text-sm font-medium text-[var(--text)] mb-1">
                                                Başlık
                                            </label>
                                            <input
                                                type="text"
                                                wire:model="title"
                                                class="w-full px-3 py-2 border rounded-md shadow-sm bg-[var(--input-bg)] text-[var(--text)] focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @else border-[var(--border)] @enderror"
                                                placeholder="Menü Başlığı"
                                            >
                                            @error('title')
                                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Icon -->
                                        <div>
                                            <label class="block text-sm font-medium text-[var(--text)] mb-1">
                                                İkon
                                            </label>
                                            <input
                                                type="text"
                                                wire:model="icon"
                                                class="w-full px-3 py-2 border border-[var(--border)] rounded-md shadow-sm bg-[var(--input-bg)] text-[var(--text)] focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="fas fa-home"
                                            >
                                        </div>

                                        <!-- Type -->
                                        <div>
                                            <label class="block text-sm font-medium text-[var(--text)] mb-1">
                                                Tip
                                            </label>
                                            <select
                                                wire:model="type"
                                                wire:change="$refresh"
                                                class="w-full px-3 py-2 border border-[var(--border)] rounded-md shadow-sm bg-[var(--input-bg)] text-[var(--text)] focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('type') border-red-500 @enderror"
                                            >
                                                <option value="menu" {{ $type === 'menu' ? 'selected' : '' }}>Ana Menü</option>
                                                <option value="submenu" {{ $type === 'submenu' ? 'selected' : '' }}>Alt Menü</option>
                                                <option value="divider" {{ $type === 'divider' ? 'selected' : '' }}>Ayırıcı</option>
                                            </select>
                                            @error('type')
                                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Parent Menu (only for submenu) -->
                                        @if($type === 'submenu')
                                            <div>
                                                <label class="block text-sm font-medium text-[var(--text)] mb-1">
                                                    Ana Menü
                                                </label>
                                                <select
                                                    wire:model="parentId"
                                                    class="w-full px-3 py-2 border border-[var(--border)] rounded-md shadow-sm bg-[var(--input-bg)] text-[var(--text)] focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                >
                                                    <option value="">Ana Menü Seçin</option>
                                                    @foreach($menuItems as $parentItem)
                                                        <option value="{{ $parentItem['id'] }}" {{ $parentId == $parentItem['id'] ? 'selected' : '' }}>{{ $parentItem['title'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <!-- Route -->
                                        <div>
                                            <label class="block text-sm font-medium text-[var(--text)] mb-1">
                                                Route
                                            </label>
                                            <input
                                                type="text"
                                                wire:model="route"
                                                class="w-full px-3 py-2 border border-[var(--border)] rounded-md shadow-sm bg-[var(--input-bg)] text-[var(--text)] focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="posts.index"
                                            >
                                        </div>

                                        <!-- Permission -->
                                        <div>
                                            <label class="block text-sm font-medium text-[var(--text)] mb-1">
                                                Yetki
                                            </label>
                                            <input
                                                type="text"
                                                wire:model="permission"
                                                class="w-full px-3 py-2 border border-[var(--border)] rounded-md shadow-sm bg-[var(--input-bg)] text-[var(--text)] focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="view posts"
                                            >
                                        </div>

                                        <!-- Active Pattern -->
                                        <div>
                                            <label class="block text-sm font-medium text-[var(--text)] mb-1">
                                                Aktif Pattern
                                            </label>
                                            <input
                                                type="text"
                                                wire:model="activePattern"
                                                class="w-full px-3 py-2 border border-[var(--border)] rounded-md shadow-sm bg-[var(--input-bg)] text-[var(--text)] focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="posts.*"
                                            >
                                        </div>

                                        <!-- Sort Order -->
                                        <div>
                                            <label class="block text-sm font-medium text-[var(--text)] mb-1">
                                                Sıralama
                                            </label>
                                            <input
                                                type="number"
                                                wire:model="sortOrder"
                                                class="w-full px-3 py-2 border rounded-md shadow-sm bg-[var(--input-bg)] text-[var(--text)] focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('sortOrder') border-red-500 @else border-[var(--border)] @enderror"
                                                min="0"
                                            >
                                            @error('sortOrder')
                                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Is Active -->
                                        <div class="flex items-center">
                                            <input
                                                type="checkbox"
                                                wire:model="isActive"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            >
                                            <label class="ml-2 block text-sm text-[var(--text)]">
                                                Aktif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[var(--bg-muted)] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveMenuItem"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <i class="fas fa-save mr-2" wire:loading.remove wire:target="saveMenuItem"></i>
                                <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="saveMenuItem"></i>
                                <span wire:loading.remove wire:target="saveMenuItem">
                                    {{ $isEditing ? 'Güncelle' : 'Oluştur' }}
                                </span>
                                <span wire:loading wire:target="saveMenuItem">Kaydediliyor...</span>
                            </button>
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-[var(--border)] shadow-sm px-4 py-2 bg-[var(--card)] text-base font-medium text-[var(--text)] hover:bg-[var(--table-row-hover)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                İptal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
