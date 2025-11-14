<div>
    @vite(['Modules/Roles/resources/assets/sass/app.scss', 'Modules/Roles/resources/assets/js/app.js'])

    <!-- Success Message -->
    <x-success-message :message="$successMessage" />

    <!-- Modern Header with Stats -->
    <div class="mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-6 lg:mb-0">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-500 via-indigo-500 to-blue-600 rounded-2xl flex items-center justify-center mr-6 shadow-lg">
                        <i class="fas fa-shield-alt text-white text-3xl"></i>
                                </div>
                                <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Rol Yönetimi</h1>
                        <p class="text-gray-600 text-lg">Sistem rollerini ve yetkilerini yönetin</p>
                                </div>
                            </div>
                <div class="flex items-center space-x-6">
                    <div class="text-center bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-4">
                        <div class="text-4xl font-bold text-purple-600 mb-1">{{ $roles->count() }}</div>
                        <div class="text-sm text-gray-600 font-medium">Toplam Rol</div>
                    </div>
                    @can('create roles')
                    <button wire:click="createRole" class="btn-primary-custom hover-lift">
                        <i class="fas fa-plus mr-2"></i>
                        Yeni Rol Oluştur
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 fade-in">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 fade-in">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Roles Grid -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200" x-data="roleManagement()">
        <div class="p-8">
            @if($roles->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($roles as $role)
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 role-card-hover" x-data="roleCard()">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="avatar-custom avatar-lg-custom mr-4">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $role->display_name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $role->description ?? 'Açıklama yok' }}</p>
                                </div>
                                </div>
                                <div class="dropdown-custom" x-data="{ open: false }">
                                    <button @click="open = !open" @click.away="open = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors duration-200">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div x-show="open" x-transition class="dropdown-menu-custom">
                                        @if($role->name !== 'super_admin')
                                            @can('edit roles')
                                            <button wire:click="editRole({{ $role->id }})" class="dropdown-item-custom">
                                                <i class="fas fa-edit mr-3"></i>
                                                Düzenle
                                            </button>
                                            @endcan
                                            @can('edit roles')
                                            <button wire:click="openPermissionModal({{ $role->id }})" class="dropdown-item-custom">
                                                <i class="fas fa-key mr-3"></i>
                                                Yetkiler
                                            </button>
                                            @endcan
                                            @can('delete roles')
                                            <button wire:click="deleteRole({{ $role->id }})"
                                                    wire:confirm="Bu rolü silmek istediğinizden emin misiniz?"
                                                    class="dropdown-item-custom text-red-600 hover:text-red-700 hover:bg-red-50">
                                                <i class="fas fa-trash mr-3"></i>
                                                Sil
                                            </button>
                                            @endcan
                                        @else
                                            <div class="dropdown-item-custom text-gray-400 cursor-not-allowed">
                                                <i class="fas fa-info-circle mr-3"></i>
                                                Süper Admin rolü düzenlenemez
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 mb-6">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Sistem Adı:</span>
                                    <code class="bg-gray-100 px-3 py-1 rounded-lg text-sm font-mono text-gray-900">{{ $role->name }}</code>
                            </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Kullanıcı Sayısı:</span>
                                    <span class="badge-custom badge-primary-custom">
                                        <i class="fas fa-users mr-1"></i>
                                        {{ $role->users_count ?? 0 }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Yetki Sayısı:</span>
                                    <span class="badge-custom badge-success-custom">
                                        <i class="fas fa-key mr-1"></i>
                                        {{ $role->permissions_count ?? 0 }}
                                </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-xs text-gray-400">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $role->created_at->format('d.m.Y') }}
                                </span>
                                <div class="flex space-x-2">
                                    @if($role->name !== 'super_admin')
                                        @can('edit roles')
                                        <button wire:click="editRole({{ $role->id }})" class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200">
                                            <i class="fas fa-edit mr-1"></i>
                                            Düzenle
                                        </button>
                                        @endcan
                                        @can('delete roles')
                                        <button wire:click="deleteRole({{ $role->id }})"
                                                wire:confirm="Bu rolü silmek istediğinizden emin misiniz?"
                                                class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors duration-200">
                                            <i class="fas fa-trash mr-1"></i>
                                            Sil
                                        </button>
                                        @endcan
                                    @else
                                        <span class="text-xs text-gray-400 italic">Süper Admin rolü düzenlenemez</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shield-alt text-gray-400 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Rol bulunamadı</h3>
                    <p class="text-gray-500 mb-8 text-lg">Henüz hiç rol oluşturulmamış. İlk rolünüzü oluşturmaya başlayın!</p>
                    <button wire:click="createRole" class="btn-primary-custom hover-lift">
                        <i class="fas fa-plus mr-2"></i>
                        İlk Rolü Oluştur
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Role Form Modal -->
    @if($showRoleForm)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         x-data="roleManagement()"
         x-show="@entangle('showRoleForm')"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px);">

        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[95vh] overflow-hidden transform transition-all duration-300 relative z-[10000]">
            <!-- Modal Header -->
            <div class="relative bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 p-8 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mr-6">
                            <i class="fas fa-shield-alt text-3xl"></i>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold mb-2">
                                {{ $editingRole ? 'Rol Düzenle' : 'Yeni Rol Oluştur' }}
                            </h2>
                            <p class="text-purple-100 text-lg">
                                {{ $editingRole ? 'Mevcut rol bilgilerini güncelleyin' : 'Sistem için yeni bir rol tanımlayın' }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeRoleForm"
                            class="w-12 h-12 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <i class="fas fa-times text-xl"></i>
                </button>
                </div>

                <!-- Progress Indicator -->
                <div class="mt-6 flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-white/30 rounded-full flex items-center justify-center">
                            <i class="fas fa-info text-sm"></i>
                        </div>
                        <span class="ml-2 text-sm font-medium">Bilgiler</span>
                    </div>
                    <div class="flex-1 h-1 bg-white/20 rounded-full">
                        <div class="h-full bg-white/60 rounded-full" style="width: 33%"></div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-key text-sm"></i>
                        </div>
                        <span class="ml-2 text-sm font-medium">Yetkiler</span>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-8 overflow-y-auto max-h-[calc(95vh-200px)] scrollbar-custom">
                <form wire:submit.prevent="saveRole" class="space-y-8">
                    <!-- Basic Information Section -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-info-circle text-purple-600 text-xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">Temel Bilgiler</h3>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 mb-3">
                                    <i class="fas fa-tag mr-2 text-purple-500"></i>
                                        Rol Adı
                                    </label>
                                <div class="relative">
                                    <input type="text"
                                           wire:model.live="name"
                                           class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-200 text-lg"
                                           placeholder="Örn: admin, editor, author">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                        <i class="fas fa-hashtag text-gray-400"></i>
                                    </div>
                                </div>
                                <x-validation-error field="name" />
                                <p class="text-xs text-gray-500 mt-2">Sistem içinde kullanılacak benzersiz rol adı</p>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 mb-3">
                                    <i class="fas fa-eye mr-2 text-purple-500"></i>
                                        Görünen Ad
                                    </label>
                                <div class="relative">
                                    <input type="text"
                                           wire:model.live="display_name"
                                           class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-200 text-lg"
                                           placeholder="Örn: Yönetici, Editör, Yazar">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                        <i class="fas fa-user-tag text-gray-400"></i>
                                    </div>
                                </div>
                                <x-validation-error field="display_name" />
                                <p class="text-xs text-gray-500 mt-2">Kullanıcıların göreceği rol adı</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-bold text-gray-700 mb-3">
                                <i class="fas fa-file-text mr-2 text-purple-500"></i>
                                Açıklama
                            </label>
                            <textarea wire:model.live="description"
                                      class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-200 text-lg resize-none"
                                      rows="4"
                                      placeholder="Bu rolün ne işe yaradığını açıklayın..."></textarea>
                            <x-validation-error field="description" />
                            <p class="text-xs text-gray-500 mt-2">Rolün amacını ve kapsamını belirtin</p>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-key text-indigo-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">Yetki Yönetimi</h3>
                                    <p class="text-gray-600">Bu role hangi yetkilerin verileceğini seçin</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <button type="button"
                                        @click="selectAllPermissions()"
                                        class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-200 transition-colors duration-200">
                                    <i class="fas fa-check-double mr-2"></i>
                                    Tümünü Seç
                                </button>
                                <button type="button"
                                        @click="clearAllPermissions()"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors duration-200">
                                    <i class="fas fa-times mr-2"></i>
                                    Temizle
                                </button>
                            </div>
                        </div>

                        @foreach($permissions as $groupName => $groupPermissions)
                            <div class="bg-white rounded-2xl p-6 mb-6 shadow-sm border border-gray-100">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4">
                                            @php
                                                $moduleIcons = [
                                                    'users' => 'fas fa-users',
                                                    'articles' => 'fas fa-newspaper',
                                                    'categories' => 'fas fa-tags',
                                                    'posts' => 'fas fa-blog',
                                                    'roles' => 'fas fa-shield-alt',
                                                    'authors' => 'fas fa-user-edit',
                                                    'comments' => 'fas fa-comments',
                                                    'logs' => 'fas fa-clipboard-list',
                                                    'featured' => 'fas fa-star',
                                                    'lastminutes' => 'fas fa-clock',
                                                    'agency_news' => 'fas fa-newspaper',
                                                    'files' => 'fas fa-folder',
                                                    'settings' => 'fas fa-cog',
                                                    'banks' => 'fas fa-money-bill',
                                                    'newsletters' => 'fas fa-envelope',
                                                    'modules' => 'fas fa-puzzle-piece'
                                                ];
                                            @endphp
                                            <i class="{{ $moduleIcons[$groupName] ?? 'fas fa-cog' }} text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xl font-bold text-gray-900">
                                                @php
                                                $moduleLabels = [
                                                    'users' => 'Kullanıcı',
                                                    'articles' => 'Makale',
                                                    'categories' => 'Kategori',
                                                    'posts' => 'Haber',
                                                    'roles' => 'Rol',
                                                    'authors' => 'Yazar',
                                                    'comments' => 'Yorumlar',
                                                    'logs' => 'Log',
                                                    'featured' => 'Vitrin Yönetimi',
                                                    'lastminutes' => 'Son Dakika',
                                                    'agency_news' => 'Ajans Haberleri',
                                                    'files' => 'Dosya Yönetimi',
                                                    'settings' => 'Sistem Ayarları',
                                                    'banks' => 'Hisse Yönetimi',
                                                    'newsletters' => 'Bülten Yönetimi',
                                                    'modules' => 'Modül Yönetimi'
                                                ];
                                                @endphp
                                                {{ $moduleLabels[$groupName] ?? ucfirst($groupName) }} Modülü
                                            </h4>
                                            <p class="text-gray-500 text-sm">
                                                Bu modülle ilgili yetkiler
                                                @if($groupName === 'modules' && !auth()->user()->hasRole('super_admin'))
                                                    <span class="text-red-500 font-semibold">(Sadece Süper Admin değiştirebilir)</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                            {{ $groupPermissions->count() }} Yetki
                                        </span>
                                        <div class="flex items-center space-x-2">
                                            @if($groupName === 'modules' && !auth()->user()->hasRole('super_admin'))
                                                <span class="text-xs text-gray-400 italic">Sadece Süper Admin</span>
                                            @else
                                                <button type="button"
                                                        @click="selectGroupPermissions('{{ $groupName }}')"
                                                        class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-medium hover:bg-green-200 transition-colors duration-200">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Seç
                                                </button>
                                                <button type="button"
                                                        @click="clearGroupPermissions('{{ $groupName }}')"
                                                        class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-medium hover:bg-red-200 transition-colors duration-200">
                                                    <i class="fas fa-times mr-1"></i>
                                                    Temizle
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" data-group="{{ $groupName }}">
                                    @foreach($groupPermissions as $permission)
                                    @php
                                        $modulePermissions = ['view modules', 'edit modules', 'activate modules'];
                                        $isModulePermission = in_array($permission->name, $modulePermissions);
                                        $isSuperAdmin = auth()->user()->hasRole('super_admin');
                                        $isDisabled = $isModulePermission && !$isSuperAdmin;
                                    @endphp
                                    <div class="group relative {{ $isDisabled ? 'opacity-60' : '' }}">
                                        <div class="flex items-start p-4 rounded-xl border-2 border-gray-100 hover:border-purple-300 hover:bg-purple-50/50 transition-all duration-200 {{ $isDisabled ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                            <div class="flex items-center h-5 mr-4">
                                                <input type="checkbox"
                                                       wire:model.live="selectedPermissions"
                                                       value="{{ $permission->name }}"
                                                       @if($isDisabled) disabled @endif
                                                       class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded-lg transition-all duration-200 {{ $isDisabled ? 'cursor-not-allowed opacity-50' : '' }}"
                                                       id="permission_{{ $permission->name }}">
                                            </div>
                                            <label for="permission_{{ $permission->name }}" class="flex-1 cursor-pointer">
                                                @php
                                                    $permissionLabels = [
                                                        'view users' => ['Görüntüle', 'Kullanıcı listesini görüntüleme', 'fas fa-eye'],
                                                        'create users' => ['Oluştur', 'Yeni kullanıcı oluşturma', 'fas fa-user-plus'],
                                                        'edit users' => ['Düzenle', 'Kullanıcı bilgilerini düzenleme', 'fas fa-user-edit'],
                                                        'delete users' => ['Sil', 'Kullanıcı silme', 'fas fa-user-times'],
                                                        'view articles' => ['Görüntüle', 'Makale listesini görüntüleme', 'fas fa-eye'],
                                                        'view all articles' => ['Tümünü Görüntüle', 'Tüm makaleleri görüntüleme', 'fas fa-eye'],
                                                        'create articles' => ['Oluştur', 'Yeni makale oluşturma', 'fas fa-plus'],
                                                        'edit articles' => ['Düzenle', 'Makale düzenleme ve durum değiştirme', 'fas fa-edit'],
                                                        'delete own articles' => ['Kendi Makalelerini Sil', 'Sadece kendi makalelerini silme', 'fas fa-trash'],
                                                        'delete all articles' => ['Tümünü Sil', 'Tüm makaleleri silme', 'fas fa-trash'],
                                                        'delete articles' => ['Sil', 'Makale silme', 'fas fa-trash'],
                                                        'publish articles' => ['Yayınla', 'Makale yayınlama', 'fas fa-paper-plane'],
                                                        'view posts' => ['Görüntüle', 'Haber listesini görüntüleme', 'fas fa-eye'],
                                                        'create posts' => ['Oluştur', 'Yeni haber oluşturma', 'fas fa-plus'],
                                                        'edit posts' => ['Düzenle', 'Haber düzenleme ve toplu işlemler', 'fas fa-edit'],
                                                        'delete posts' => ['Sil', 'Haber silme', 'fas fa-trash'],
                                                        'publish posts' => ['Yayınla', 'Haber yayınlama', 'fas fa-paper-plane'],
                                                        'archive posts' => ['Arşivle', 'Haber arşivleme', 'fas fa-archive'],
                                                        'view categories' => ['Görüntüle', 'Kategori listesini görüntüleme', 'fas fa-eye'],
                                                        'create categories' => ['Oluştur', 'Yeni kategori oluşturma', 'fas fa-plus'],
                                                        'edit categories' => ['Düzenle', 'Kategori düzenleme', 'fas fa-edit'],
                                                        'delete categories' => ['Sil', 'Kategori silme', 'fas fa-trash'],
                                                        'view roles' => ['Görüntüle', 'Rol listesini görüntüleme', 'fas fa-eye'],
                                                        'create roles' => ['Oluştur', 'Yeni rol oluşturma', 'fas fa-plus'],
                                                        'edit roles' => ['Düzenle', 'Rol düzenleme ve yetki atama', 'fas fa-edit'],
                                                        'delete roles' => ['Sil', 'Rol silme', 'fas fa-trash'],
                                                        'view authors' => ['Görüntüle', 'Yazar listesini görüntüleme', 'fas fa-eye'],
                                                        'create authors' => ['Oluştur', 'Yeni yazar oluşturma', 'fas fa-plus'],
                                                        'edit authors' => ['Düzenle', 'Yazar düzenleme', 'fas fa-edit'],
                                                        'delete authors' => ['Sil', 'Yazar silme', 'fas fa-trash'],
                                                        'view comments' => ['Görüntüle', 'Yorum listesini görüntüleme', 'fas fa-eye'],
                                                        'delete comments' => ['Sil', 'Yorum silme', 'fas fa-trash'],
                                                        'approve comments' => ['Onayla', 'Yorum onaylama', 'fas fa-check'],
                                                        'reject comments' => ['Reddet', 'Yorum reddetme', 'fas fa-times'],
                                                        'update comments' => ['Güncelle', 'Yorum güncelleme', 'fas fa-sync'],
                                                        'view logs' => ['Görüntüle', 'Log listesini görüntüleme', 'fas fa-eye'],
                                                        'delete logs' => ['Sil', 'Log silme', 'fas fa-trash'],
                                                        'export logs' => ['Dışa Aktar', 'Log dışa aktarma', 'fas fa-download'],
                                                        'view featured' => ['Görüntüle', 'Vitrin içeriklerini görüntüleme', 'fas fa-eye'],
                                                        'create featured' => ['Oluştur', 'Vitrin içeriklerini oluşturma', 'fas fa-plus'],
                                                        'edit featured' => ['Düzenle', 'Vitrin içeriklerini düzenleme', 'fas fa-edit'],
                                                        'delete featured' => ['Sil', 'Vitrin içeriklerini silme', 'fas fa-trash'],


                                                        // Ajans Haberleri Modülü
                                                        'view agency_news' => ['Görüntüle', 'Ajans haberlerini görüntüleme', 'fas fa-eye'],
                                                        'delete agency_news' => ['Sil', 'Ajans haberlerini silme', 'fas fa-trash'],
                                                        'publish agency_news' => ['Yayınla', 'Ajans haberlerini yayınlama', 'fas fa-paper-plane'],

                                                        // Son Dakika Modülü
                                                        'view lastminutes' => ['Görüntüle', 'Son dakika haberlerini görüntüleme', 'fas fa-eye'],
                                                        'create lastminutes' => ['Oluştur', 'Yeni son dakika haberi oluşturma', 'fas fa-plus'],
                                                        'edit lastminutes' => ['Düzenle', 'Son dakika haberlerini düzenleme', 'fas fa-edit'],
                                                        'delete lastminutes' => ['Sil', 'Son dakika haberlerini silme', 'fas fa-trash'],

                                                        // Dosya Yönetimi Modülü
                                                        'view files' => ['Görüntüle', 'Dosya listesini görüntüleme', 'fas fa-eye'],
                                                        'create files' => ['Oluştur', 'Yeni dosya yükleme', 'fas fa-plus'],
                                                        'edit files' => ['Düzenle', 'Dosya bilgilerini düzenleme', 'fas fa-edit'],
                                                        'delete files' => ['Sil', 'Dosya silme', 'fas fa-trash'],

                                                        // Sistem Ayarları Modülü (basitleştirilmiş)
                                                        'view settings' => ['Görüntüle', 'Tüm ayarları görüntüleme', 'fas fa-eye'],
                                                        'edit settings' => ['Düzenle', 'Tüm ayarları düzenleme', 'fas fa-edit'],
                                                        'manage menu' => ['Menü Yönetimi', 'Menü yapısını düzenleme', 'fas fa-bars'],
                                                        'view menu_management' => ['Görüntüle', 'Menü yönetimini görüntüleme', 'fas fa-eye'],
                                                        'edit menu_management' => ['Düzenle', 'Menü yönetimini düzenleme', 'fas fa-edit'],

                                                        // Hisse Yönetimi Modülü
                                                        'view stocks' => ['Görüntüle', 'Hisse senetlerini görüntüleme', 'fas fa-eye'],
                                                        'create stocks' => ['Oluştur', 'Yeni hisse senedi oluşturma', 'fas fa-plus'],
                                                        'edit stocks' => ['Düzenle', 'Hisse senedi bilgilerini düzenleme', 'fas fa-edit'],
                                                        'delete stocks' => ['Sil', 'Hisse senedi silme', 'fas fa-trash'],
                                                        'view investor_questions' => ['Görüntüle', 'Yatırımcı sorularını görüntüleme', 'fas fa-eye'],
                                                        'edit investor_questions' => ['Düzenle', 'Yatırımcı sorularını cevaplama', 'fas fa-edit'],
                                                        'delete investor_questions' => ['Sil', 'Yatırımcı sorularını silme', 'fas fa-trash'],

                                                        // Bülten Yönetimi Modülü
                                                        'view newsletters' => ['Görüntüle', 'Bülten listesini görüntüleme', 'fas fa-eye'],
                                                        'create newsletters' => ['Oluştur', 'Yeni bülten oluşturma', 'fas fa-plus'],
                                                        'edit newsletters' => ['Düzenle', 'Bülten düzenleme', 'fas fa-edit'],
                                                        'delete newsletters' => ['Sil', 'Bülten silme', 'fas fa-trash'],
                                                        'view newsletter_users' => ['Görüntüle', 'Abone listesini görüntüleme', 'fas fa-eye'],
                                                        'edit newsletter_users' => ['Düzenle', 'Abone düzenleme', 'fas fa-edit'],
                                                        'delete newsletter_users' => ['Sil', 'Abone silme', 'fas fa-trash'],
                                                        'view newsletter_logs' => ['Görüntüle', 'Bülten loglarını görüntüleme', 'fas fa-eye'],
                                                        'delete newsletter_logs' => ['Sil', 'Bülten loglarını silme', 'fas fa-trash'],

                                                        // Modül Yönetimi
                                                        'view modules' => ['Görüntüle', 'Modül listesini görüntüleme', 'fas fa-eye'],
                                                        'edit modules' => ['Düzenle', 'Modül ayarlarını düzenleme', 'fas fa-edit'],
                                                        'activate modules' => ['Aktifleştir', 'Modül aktifleştirme ve devre dışı bırakma', 'fas fa-power-off'],
                                                    ];
                                                    $label = $permissionLabels[$permission->name] ?? [ucfirst(str_replace('_', ' ', $permission->name)), 'Yetki açıklaması', 'fas fa-cog'];
                                                @endphp
                                                <div class="flex items-start">
                                                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-purple-100 transition-colors duration-200">
                                                        <i class="{{ $label[2] }} text-gray-600 text-sm"></i>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="flex items-center mb-1">
                                                            <span class="text-sm font-bold text-gray-900">{{ $label[0] }}</span>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Yetki
                                                </span>
                                                        </div>
                                                        <p class="text-xs text-gray-500">{{ $label[1] }}</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-8 border-t-2 border-gray-100">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span>Değişiklikler kaydedildikten sonra hemen aktif olacaktır</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <button type="button"
                                    wire:click="closeRoleForm"
                                    class="px-8 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-all duration-200 hover:scale-105">
                                <i class="fas fa-times mr-2"></i>
                                İptal
                            </button>
                            <button type="submit"
                                    class="px-8 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl font-medium transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl">
                                <i class="fas fa-save mr-2"></i>
                                {{ $editingRole ? 'Güncelle' : 'Oluştur' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Permission Modal -->
    @if($showPermissionModal)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         x-data="roleManagement()"
         x-show="@entangle('showPermissionModal')"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[95vh] overflow-hidden transform transition-all duration-300 relative z-[10000]">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="avatar-custom avatar-sm-custom mr-4 bg-gradient-to-br from-green-500 to-teal-600">
                        <i class="fas fa-key"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ $editingRole->display_name }} - Yetki Yönetimi
                    </h2>
                </div>
                <button wire:click="closePermissionModal" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <div class="p-8 overflow-y-auto max-h-[calc(90vh-140px)] scrollbar-custom">
                <form wire:submit.prevent="updatePermissions">
                    <div class="mb-8">
                        <label class="block text-sm font-bold text-gray-700 mb-6">
                            <i class="fas fa-shield-alt mr-2 text-green-500"></i>
                                Yetkiler
                            </label>

                            @foreach($permissions as $groupName => $groupPermissions)
                            <div class="bg-gradient-to-r from-green-50 to-teal-50 rounded-xl p-6 mb-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-xl font-bold text-gray-900">
                                            @php
                                                $moduleLabels = [
                                                    'users' => 'Kullanıcı',
                                                    'articles' => 'Makale',
                                                    'categories' => 'Kategori',
                                                    'posts' => 'Haber',
                                                    'vitrin' => 'Vitrin Yönetimi',
                                                    'roles' => 'Rol',
                                                    'authors' => 'Yazar',
                                                    'comments' => 'Yorumlar',
                                                    'logs' => 'Log',
                                                    'lastminutes' => 'Son Dakika',
                                                    'agency_news' => 'Ajans Haberleri',
                                                    'files' => 'Dosya Yönetimi',
                                                    'settings' => 'Sistem Ayarları',
                                                    'banks' => 'Hisse Yönetimi',
                                                    'newsletters' => 'Bülten Yönetimi',
                                                    'modules' => 'Modül Yönetimi'
                                                ];
                                            @endphp
                                            {{ $moduleLabels[$groupName] ?? ucfirst($groupName) }} Modülü
                                    </h4>
                                    <span class="badge-custom badge-success-custom">
                                        {{ $groupPermissions->count() }} Yetki
                                    </span>
                                    </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($groupPermissions as $permission)
                                    @php
                                        $modulePermissions = ['view modules', 'edit modules', 'activate modules'];
                                        $isModulePermission = in_array($permission->name, $modulePermissions);
                                        $isSuperAdmin = auth()->user()->hasRole('super_admin');
                                        $isDisabled = $isModulePermission && !$isSuperAdmin;
                                    @endphp
                                    <div class="permission-item-hover {{ $isDisabled ? 'opacity-60' : '' }}">
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                    <input type="checkbox"
                                                           wire:model.live="selectedPermissions"
                                                           value="{{ $permission->name }}"
                                                           @if($isDisabled) disabled @endif
                                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded {{ $isDisabled ? 'cursor-not-allowed opacity-50' : '' }}"
                                                       id="perm_{{ $permission->name }}">
                                            </div>
                                            <div class="ml-3">
                                                <label for="perm_{{ $permission->name }}" class="text-sm font-medium text-gray-700 cursor-pointer">
                                                        @php
                                                            // İlk tanımda zaten var, burada tekrar tanımlamaya gerek yok
                                                            $label = $permissionLabels[$permission->name] ?? [ucfirst(str_replace('_', ' ', $permission->name)), 'Yetki açıklaması'];
                                                        @endphp
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-bold text-gray-900">{{ $label[0] }}</span>
                                                        <span class="text-xs text-gray-500">{{ $label[1] }}</span>
                                                        </div>
                                                    </label>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    <div class="flex justify-end space-x-4 pt-8 border-t border-gray-200">
                        <button type="button" wire:click="closePermissionModal" class="btn-secondary-custom">
                            <i class="fas fa-times mr-2"></i>
                                İptal
                            </button>
                        <button type="submit" class="btn-primary-custom hover-lift bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700">
                            <i class="fas fa-save mr-2"></i>
                            Yetkileri Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
