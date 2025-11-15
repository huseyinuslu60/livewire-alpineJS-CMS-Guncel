<!-- Dinamik Dashboard - Kullanıcının yetkilerine göre içerik gösterir -->
<div class="min-h-screen from-blue-50 via-white to-indigo-50">
    <!-- Dashboard Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text mb-2">
                    Hoş Geldiniz, {{ $user->name }}
                </h1>
                <p class="text-gray-600 text-lg">
                    @if($user->can('view articles') && !$user->can('view all articles'))
                        Kendi içeriklerinizi buradan takip edebilirsiniz
                    @elseif($user->can('view posts') || $user->can('view articles'))
                        İçerik yönetimi ve editörlük işlemlerinizi buradan takip edebilirsiniz
                    @else
                        Sistem durumunu ve performansı buradan takip edebilirsiniz
                    @endif
                </p>
            </div>
            <div class="flex items-center space-x-3">
                @if($user->can('view users') || $user->can('create articles') || $user->can('create posts'))
                <!-- Hızlı İşlemler Dropdown -->
                <div class="relative" x-data="{ open: false }" x-init="open = false">
                    <button @click="open = !open"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg">
                        <i class="fas fa-bolt mr-2"></i>
                        Hızlı İşlemler
                        <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-[var(--surface)] border border-[var(--border-subtle)] z-50"
                         style="display: none;">
                        <div class="py-1">
                            @if($user->can('create users'))
                            <a href="{{ route('user.create') }}" class="flex items-center px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--table-row-hover)] hover:text-[var(--text)] transition-all duration-200 rounded-md">
                                <i class="fas fa-user-plus mr-3 text-blue-500"></i>
                                Kullanıcı Ekle
                            </a>
                            @endif

                            @if($user->can('create articles'))
                            <a href="{{ route('articles.create') }}" class="flex items-center px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--table-row-hover)] hover:text-[var(--text)] transition-all duration-200 rounded-md">
                                <i class="fas fa-file-alt mr-3 text-green-500"></i>
                                Makale Ekle
                            </a>
                            @endif

                            @if($user->can('create posts'))
                            <a href="{{ route('posts.create.news') }}" class="flex items-center px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--table-row-hover)] hover:text-[var(--text)] transition-all duration-200 rounded-md">
                                <i class="fas fa-newspaper mr-3 text-purple-500"></i>
                                Haber Ekle
                            </a>
                            @endif

                            @if($user->can('create posts'))
                            <a href="{{ route('posts.create.gallery') }}" class="flex items-center px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--table-row-hover)] hover:text-[var(--text)] transition-all duration-200 rounded-md">
                                <i class="fas fa-images mr-3 text-orange-500"></i>
                                Galeri Haberi Ekle
                            </a>
                            @endif

                            @if($user->can('create posts'))
                            <a href="{{ route('posts.create.video') }}" class="flex items-center px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--table-row-hover)] hover:text-[var(--text)] transition-all duration-200 rounded-md">
                                <i class="fas fa-video mr-3 text-red-500"></i>
                                Video Haberi Ekle
                            </a>
                            @endif

                            <div class="border-t border-[var(--border-subtle)] my-1"></div>

                            @if($user->can('view users'))
                            <a href="{{ route('user.index') }}" class="flex items-center px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--table-row-hover)] hover:text-[var(--text)] transition-all duration-200 rounded-md">
                                <i class="fas fa-users mr-3 text-indigo-500"></i>
                                Kullanıcı Listesi
                            </a>
                            @endif

                            @if($user->can('view articles'))
                            <a href="{{ route('articles.index') }}" class="flex items-center px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--table-row-hover)] hover:text-[var(--text)] transition-all duration-200 rounded-md">
                                <i class="fas fa-list mr-3 text-green-500"></i>
                                Makale Listesi
                            </a>
                            @endif

                            @if($user->can('view posts'))
                            <a href="{{ route('posts.index') }}" class="flex items-center px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--table-row-hover)] hover:text-[var(--text)] transition-all duration-200 rounded-md">
                                <i class="fas fa-newspaper mr-3 text-purple-500"></i>
                                Haber Listesi
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <div class="flex items-center space-x-2 bg-white rounded-full px-4 py-2 shadow-sm border border-gray-200">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-gray-700">Sistem Aktif</span>
                </div>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                    @if($user->hasRole('super_admin')) bg-gradient-to-r from-red-500 to-pink-500
                    @elseif($user->hasRole('admin')) bg-gradient-to-r from-red-500 to-pink-500
                    @elseif($user->hasRole('yazar')) bg-gradient-to-r from-purple-500 to-purple-600
                    @elseif($user->hasRole('editor')) bg-gradient-to-r from-green-500 to-teal-500
                    @else bg-gradient-to-r from-red-500 to-pink-500
                    @endif text-white shadow-lg">
                    <i class="fas
                        @if($user->hasRole('super_admin')) fa-crown
                        @elseif($user->hasRole('admin')) fa-shield-alt
                        @elseif($user->hasRole('yazar')) fa-pen-fancy
                        @elseif($user->hasRole('editor')) fa-edit
                        @else fa-shield-alt
                        @endif mr-2"></i>
                    {{ $user->roles->first()->display_name ?? 'Panel' }}
                </span>
            </div>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        @if($user->can('view users') && $user->can('view roles'))
        <!-- Sistem yönetimi yetkisi olanlar için sistem odaklı kartlar -->
        @if(isset($data['totalUsers']))
        <!-- Toplam Kullanıcı -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Toplam Kullanıcı</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $data['totalUsers'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endif

        @if(isset($data['totalPosts']))
        <!-- Toplam Haber -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Toplam Haber</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $data['totalPosts'] }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-newspaper text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endif

        @if(isset($data['totalArticles']))
        <!-- Toplam Makale -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Toplam Makale</p>
                    <p class="text-3xl font-bold text-green-600">{{ $data['totalArticles'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-file-alt text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endif

        @if(isset($data['totalRoles']))
        <!-- Toplam Rol -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Toplam Rol</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $data['totalRoles'] }}</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-full">
                    <i class="fas fa-shield-alt text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endif
        @elseif($user->can('view posts') || ($user->can('view articles') && $user->can('view all articles')))
        <!-- İçerik yönetimi yetkisi olanlar için makale + haber odaklı kartlar -->
        @if(isset($data['totalArticles']))
        <!-- Toplam Makale -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Toplam Makale</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $data['totalArticles'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Yayınlanan Makaleler -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Yayınlanan</p>
                    <p class="text-3xl font-bold text-green-600">{{ $data['publishedArticles'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endif

        @if(isset($data['totalPosts']))
        <!-- Toplam Haber -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Toplam Haber</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $data['totalPosts'] }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-newspaper text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Yayınlanan Haberler -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Yayınlanan Haber</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $data['publishedPosts'] }}</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-full">
                    <i class="fas fa-check-circle text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endif

        @elseif($user->can('view articles') && !$user->can('view all articles'))
        <!-- Sadece kendi içeriklerini görebilenler için makale odaklı kartlar -->
        @if(isset($data['totalArticles']))
        <!-- Toplam Makale -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Toplam Makale</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $data['totalArticles'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Yayınlanan Makaleler -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Yayınlanan</p>
                    <p class="text-3xl font-bold text-green-600">{{ $data['publishedArticles'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Taslak Makaleler -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Taslak</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $data['draftArticles'] }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-edit text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Onay Bekleyen -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Onay Bekleyen</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $data['pendingArticles'] }}</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endif
        @else
        <!-- Diğer roller için varsayılan kartlar -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Hoş Geldiniz</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $user->name }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-user text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- İçerik Listeleri -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if(isset($data['weeklyPopularArticles']))
        <!-- Haftanın En Popüler Makaleleri -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Haftanın En Popüler Makaleleri</h3>
                <i class="fas fa-fire text-red-500"></i>
            </div>
            <div class="space-y-3">
                @forelse($data['weeklyPopularArticles'] as $index => $article)
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 bg-gradient-to-r from-red-400 to-orange-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            {{ $index + 1 }}
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 text-sm">{{ Str::limit($article->title, 50) }}</h4>
                        <p class="text-xs text-gray-500">{{ $article->hit }} okunma</p>
                    </div>
                    <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">
                        Yayında
                    </span>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Bu hafta popüler makale yok</p>
                @endforelse
            </div>
        </div>
        @endif


        @if(isset($data['weeklyPopularPosts']) && ($user->can('view posts') || ($user->can('view articles') && $user->can('view all articles'))))
        <!-- Haftanın En Popüler Haberleri (İçerik yönetimi yetkisi olanlar için) -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Haftanın En Popüler Haberleri</h3>
                <i class="fas fa-fire text-purple-500"></i>
            </div>
            <div class="space-y-3">
                @forelse($data['weeklyPopularPosts'] as $index => $post)
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            {{ $index + 1 }}
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 text-sm">{{ Str::limit($post->title, 50) }}</h4>
                        <p class="text-xs text-gray-500">{{ $post->view_count }} okunma</p>
                    </div>
                    <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">
                        Yayında
                    </span>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Bu hafta popüler haber yok</p>
                @endforelse
            </div>
        </div>
        @endif

    </div>

    <!-- Sistem Yönetimi Özel Bölümler -->
    @if($user->can('view users') && $user->can('view roles'))
    <!-- Sistem İstatistikleri -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sistem Performansı -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Sistem Performansı</h3>
                <i class="fas fa-server text-blue-500"></i>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">PHP Sürümü</span>
                    <span class="text-sm font-medium text-gray-900">{{ $data['systemStats']['php_version'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Laravel Sürümü</span>
                    <span class="text-sm font-medium text-gray-900">{{ $data['systemStats']['laravel_version'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Bellek Kullanımı</span>
                    <span class="text-sm font-medium text-gray-900">{{ $data['systemStats']['memory_usage'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Boş Disk Alanı</span>
                    <span class="text-sm font-medium text-gray-900">{{ $data['systemStats']['disk_free_space'] }}</span>
                </div>
            </div>
        </div>

        <!-- Kullanıcı İstatistikleri -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Kullanıcı İstatistikleri</h3>
                <i class="fas fa-users text-green-500"></i>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Bu Hafta Kayıt</span>
                    <span class="text-sm font-medium text-green-600">{{ $data['thisWeekRegistrations'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Bu Ay Kayıt</span>
                    <span class="text-sm font-medium text-blue-600">{{ $data['thisMonthRegistrations'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Aktif Kullanıcı %</span>
                    <span class="text-sm font-medium text-purple-600">{{ $data['activeUserPercentage'] ?? 0 }}%</span>
                </div>
                @if(isset($data['lastLoginUser']))
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Son Giriş</span>
                    <span class="text-sm font-medium text-gray-900">{{ $data['lastLoginUser']->name }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- En Aktif Kullanıcılar ve Rol Dağılımı -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- En Aktif Kullanıcılar -->
        @if(isset($data['mostActiveUsers']) && $data['mostActiveUsers']->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">En Aktif Kullanıcılar (Son 7 Gün)</h3>
                <i class="fas fa-fire text-orange-500"></i>
            </div>
            <div class="space-y-3">
                @foreach($data['mostActiveUsers'] as $index => $activeUser)
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gradient-to-r from-orange-400 to-red-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            {{ $index + 1 }}
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 text-sm">{{ $activeUser->name }}</h4>
                        <p class="text-xs text-gray-500">{{ $activeUser->updated_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $activeUser->roles->first()->display_name ?? 'Kullanıcı' }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Rol Dağılımı -->
        @if(isset($data['roleDistribution']) && $data['roleDistribution']->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Rol Dağılımı</h3>
                <i class="fas fa-chart-pie text-purple-500"></i>
            </div>
            <div class="space-y-3">
                @foreach($data['roleDistribution'] as $role)
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $role->display_name ?? $role->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $role->users_count }} kullanıcı</p>
                        </div>
                        <div class="text-2xl font-bold text-purple-600">{{ $role->users_count }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- İçerik Yönetimi için Özel Hızlı Erişim -->
    @if($user->can('view posts') || ($user->can('view articles') && $user->can('view all articles')))
    <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Hızlı Haber Ekleme</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Haber Listesi -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-list text-white text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Haber Listesi</h4>
                    <p class="text-sm text-gray-600 mb-4">Tüm haberleri görüntüle ve yönet</p>
                    <a href="{{ route('posts.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        Görüntüle
                    </a>
                </div>
            </div>

            <!-- Normal Haber -->
            <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-2xl p-6 border border-pink-200 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-pink-500 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-newspaper text-white text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Normal Haber</h4>
                    <p class="text-sm text-gray-600 mb-4">Standart metin tabanlı haber oluştur</p>
                    <a href="{{ route('posts.create.news') }}" class="inline-flex items-center px-4 py-2 bg-pink-600 text-white text-sm font-medium rounded-lg hover:bg-pink-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Oluştur
                    </a>
                </div>
            </div>

            <!-- Galeri Haberi -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-6 border border-orange-200 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-images text-white text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Galeri Haberi</h4>
                    <p class="text-sm text-gray-600 mb-4">Fotoğraf galerisi ile haber oluştur</p>
                    <a href="{{ route('posts.create.gallery') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Oluştur
                    </a>
                </div>
            </div>

            <!-- Video Haberi -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 border border-red-200 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-video text-white text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Video Haberi</h4>
                    <p class="text-sm text-gray-600 mb-4">Video içerikli haber oluştur</p>
                    <a href="{{ route('posts.create.video') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Oluştur
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- AI İçerik Önerileri (Altta) -->
    <div class="mt-6 mb-6">
    @if(($user->can('view posts') || $user->can('view articles')) && isset($data['aiSuggestions']) && is_array($data['aiSuggestions']) && count($data['aiSuggestions']) > 0)
    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl shadow-lg p-6 border border-indigo-200 mb-8 min-h-[400px]">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-robot text-indigo-500 mr-2"></i>
                🤖 AI Finans & Borsa Haber Önerileri
            </h3>
            <button onclick="refreshContentSuggestions()" class="text-indigo-500 hover:text-indigo-700 transition-colors duration-200">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto">
            @foreach(array_slice($data['aiSuggestions'], 0, 12) as $index => $suggestion)
            <div class="bg-white rounded-lg p-4 border border-indigo-100 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            {{ $index + 1 }}
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900 text-sm mb-1">{{ $suggestion['title'] }}</h4>
                        <p class="text-xs text-gray-600 mb-2">{{ Str::limit($suggestion['description'], 100) }}</p>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-indigo-600 font-medium">
                                @if($suggestion['type'] == 'news') Haber
                                @elseif($suggestion['type'] == 'gallery') Galeri
                                @elseif($suggestion['type'] == 'video') Video
                                @endif
                            </span>
                            <span class="text-xs text-gray-500">{{ $suggestion['confidence'] }}%</span>
                        </div>
                        <div class="flex justify-end">
                            @if($suggestion['type'] == 'news')
                            <a href="{{ route('posts.create.news') }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition-colors">
                                <i class="fas fa-plus mr-1"></i>
                                Oluştur
                            </a>
                            @elseif($suggestion['type'] == 'gallery')
                            <a href="{{ route('posts.create.gallery') }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-orange-600 bg-orange-100 rounded-lg hover:bg-orange-200 transition-colors">
                                <i class="fas fa-plus mr-1"></i>
                                Oluştur
                            </a>
                            @elseif($suggestion['type'] == 'video')
                            <a href="{{ route('posts.create.video') }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-100 rounded-lg hover:bg-red-200 transition-colors">
                                <i class="fas fa-plus mr-1"></i>
                                Oluştur
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    </div>

</div>

<script>
function refreshContentSuggestions() {
    const button = event.target.closest('button');
    const icon = button.querySelector('i');
    const originalText = button.innerHTML;

    // Loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Yenileniyor...';
    icon.classList.add('fa-spin');

    // AJAX request
    fetch('{{ route("refresh.content.suggestions") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Sayfayı yenile
            location.reload();
        } else {
            throw new Error('Yenileme başarısız');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Hata durumunda butonu eski haline getir
        button.disabled = false;
        button.innerHTML = originalText;
        icon.classList.remove('fa-spin');

        // Hata mesajı göster
        alert('Öneriler yenilenirken bir hata oluştu. Lütfen tekrar deneyin.');
    });
}
</script>
