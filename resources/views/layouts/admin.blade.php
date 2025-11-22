<!DOCTYPE html>
<html lang="tr" x-data="adminApp()" x-init="init()">

<head>
    <title>@yield('title', 'Admin Panel')</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('assets/png/logo.png') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- jQuery, Trumbowyg, and Trix are now loaded via NPM/Vite in resources/js/app.js -->
    <!-- No CDN scripts needed - everything is bundled via Vite -->

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <!-- Custom CSS -->
    <style>
        [x-cloak] { display: none !important; }

        /* Sidebar'ı hemen göster - Livewire yüklenmesini bekleme */
        .sidebar-immediate {
            display: block !important;
        }

        /* Desktop'ta sidebar her zaman görünür olsun */
        @media (min-width: 1024px) {
            aside {
                display: block !important;
                transform: translateX(0) !important;
            }
        }

        /* Trumbowyg - Normal mod */
        .trumbowyg {
            height: 400px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
        }

        /* Trumbowyg - Galeri açıklamaları için özel yükseklik */
        .trumbowyg-gallery-description {
            height: 180px !important;
            min-height: 180px !important;
            max-height: 180px !important;
        }

        .trumbowyg-gallery-description .trumbowyg-editor {
            min-height: 120px !important;
            max-height: 120px !important;
        }

        /* Trumbowyg Fullscreen Mode */
        .trumbowyg.trumbowyg-fullscreen {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 9999 !important;
            background: white !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .trumbowyg.trumbowyg-fullscreen .trumbowyg-editor {
            height: calc(100vh - 50px) !important;
            max-height: none !important;
        }

        .trumbowyg.trumbowyg-fullscreen .trumbowyg-box {
            height: 100vh !important;
            max-height: none !important;
        }

        /* Fullscreen mode'da diğer elementleri gizle */
        body.trumbowyg-fullscreen {
            overflow: hidden !important;
        }

        body.trumbowyg-fullscreen aside,
        body.trumbowyg-fullscreen .main-header,
        body.trumbowyg-fullscreen .breadcrumb,
        body.trumbowyg-fullscreen .page-header {
            display: none !important;
        }

        body.trumbowyg-fullscreen .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        /* Fullscreen çıkış butonu */
        .trumbowyg.trumbowyg-fullscreen .trumbowyg-button-pane {
            background: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6 !important;
        }

        .trumbowyg.trumbowyg-fullscreen .trumbowyg-button-pane .trumbowyg-button-group:last-child {
            border-left: 1px solid #dee2e6 !important;
        }

        /* Trix Editor - Galeri için */
        .trix-editor {
            height: 100px !important;
            max-height: 100px !important;
            min-height: 100px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            overflow-y: auto !important;
        }

        /* Trix kaldırıldı - artık sadece Trumbowyg kullanılıyor */

    </style>

    <!-- FOUC Prevention Script - Sadece dark mode için -->
    <script nonce="{{ $cspNonce ?? '' }}">
        (function() {
            // localStorage'dan dark mode durumunu kontrol et
            const isDarkMode = localStorage.getItem('darkMode') === 'true';

            // Eğer dark mode aktifse, hemen uygula
            // OS tercihini de kontrol et (kullanıcı tercihi yoksa)
            if (isDarkMode) {
                if (document.documentElement) {
                    document.documentElement.classList.add('dark');
                }
            } else {
                // Kullanıcı tercihi yoksa OS tercihini kontrol et
                const savedDarkMode = localStorage.getItem('darkMode');
                if (savedDarkMode === null && window.matchMedia) {
                    const osDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (osDarkMode && document.documentElement) {
                        document.documentElement.classList.add('dark');
                    }
                }
            }

            // Menü state'lerini temizle - sadece aktif sayfa için koru
            const currentPath = window.location.pathname;
            const menuKeys = Object.keys(localStorage).filter(key => key.includes('submenu_') || key.includes('menu_'));

            menuKeys.forEach(key => {
                // Aktif sayfa kontrolü yap
                if (!currentPath.includes('settings') && key.includes('site-ayarlari')) {
                    localStorage.removeItem(key);
                } else if (!currentPath.includes('posts/create') && key.includes('yeni-icerik')) {
                    localStorage.removeItem(key);
                }
                // Diğer menü state'leri için de benzer kontroller eklenebilir
            });
        })();
    </script>

    @stack('styles')
</head>

<body class="bg-[var(--bg)] text-[var(--text)] font-inter">
    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-gray-600/75 lg:hidden"
         @click="toggleSidebar()">
    </div>

        <!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
            class="fixed z-100 inset-y-0 left-0 w-64 transition duration-300 overflow-y-auto lg:translate-x-0 lg:inset-0 custom-scrollbar sidebar-immediate"
        >
            <!-- Logo -->
            <div class="flex items-center justify-between bg-black/30 h-16 px-4">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Admin Panel" class="w-8 h-8 mr-3">
                        <h1 class="text-white text-lg font-bold uppercase tracking-widest">
                            Admin Panel
                        </h1>
                    </a>
                </div>
                <!-- Mobile Close Button -->
                <button @click="toggleSidebar()"
                        class="lg:hidden text-white hover:text-gray-300 transition-colors duration-200 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

        <!-- Navigation -->
        <nav class="py-6 custom-scrollbar">
            @foreach(\App\Helpers\MenuHelper::getAdminMenu() as $item)
                @if($item['type'] === 'single' || $item['type'] === 'module')
                    <div x-data="{
                        linkHover: false,
                        linkActive: {{ (\App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') || \App\Helpers\MenuHelper::hasActiveSubmenu($item)) ? 'true' : 'false' }},
                        init() {
                            // Menü state'ini localStorage'dan yükle - sadece aktif sayfa için
                            const menuKey = 'submenu_{{ Str::slug($item['title']) }}_active';
                            const isActivePage = {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'true' : 'false' }};
                            const hasActiveSubmenu = {{ \App\Helpers\MenuHelper::hasActiveSubmenu($item) ? 'true' : 'false' }};

                            // Eğer ana menü veya alt menülerinden biri aktifse, menüyü açık tut
                            if (isActivePage || hasActiveSubmenu) {
                                this.linkActive = true;
                                // Aktif sayfada localStorage'a kaydet
                                localStorage.setItem(menuKey, 'true');
                            } else {
                                // Aktif sayfa değilse menüyü kapalı yap ve localStorage'dan temizle
                                this.linkActive = false;
                                localStorage.removeItem(menuKey);
                            }
                        }
                    }">
                        @if(isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']))
                            <a
                                @mouseover = "linkHover = true"
                                @mouseleave = "linkHover = false"
                                @click = "linkActive = !linkActive; const isActivePage = {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'true' : 'false' }}; const hasActiveSubmenu = {{ \App\Helpers\MenuHelper::hasActiveSubmenu($item) ? 'true' : 'false' }}; if (isActivePage || hasActiveSubmenu) { localStorage.setItem('submenu_{{ Str::slug($item['title']) }}_active', linkActive); } else { localStorage.removeItem('submenu_{{ Str::slug($item['title']) }}_active'); }"
                                href="{{ route($item['route']) }}"
                                class="flex items-center justify-between text-gray-400 hover:text-white px-6 py-3 cursor-pointer hover:bg-black/30 transition-all duration-300 rounded-r-xl {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'bg-gradient-to-r from-blue-600/20 to-purple-600/20 text-white border-r-2 border-blue-400' : '' }}"
                            >
                        @else
                            <div
                                @mouseover = "linkHover = true"
                                @mouseleave = "linkHover = false"
                                @click = "linkActive = !linkActive; const isActivePage = {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'true' : 'false' }}; const hasActiveSubmenu = {{ \App\Helpers\MenuHelper::hasActiveSubmenu($item) ? 'true' : 'false' }}; if (isActivePage || hasActiveSubmenu) { localStorage.setItem('submenu_{{ Str::slug($item['title']) }}_active', linkActive); } else { localStorage.removeItem('submenu_{{ Str::slug($item['title']) }}_active'); }"
                                class="flex items-center justify-between text-gray-400 hover:text-white px-6 py-3 cursor-pointer hover:bg-black/30 transition-all duration-300 rounded-r-xl {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'bg-gradient-to-r from-blue-600/20 to-purple-600/20 text-white border-r-2 border-blue-400' : '' }}"
                            >
                        @endif
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-7 h-7 rounded-lg {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'bg-blue-500/20' : 'bg-gray-700/50' }} transition-all duration-300" :class="linkHover ? 'bg-blue-500/20' : ''">
                                <i class="{{ $item['icon'] }} w-3.5 h-3.5 transition duration-300 {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'text-blue-400' : '' }}" :class="linkHover ? 'text-blue-400' : ''"></i>
                            </div>
                            <span
                                class="ml-3 text-sm font-medium transition duration-300 {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'text-white' : '' }}"
                                :class="linkHover ? 'text-white' : ''"
                            >
                                {{ $item['title'] }}
                            </span>
                        </div>
                        @if(isset($item['submenu']) && !empty($item['submenu']))
                            <i class="fas fa-chevron-right w-3 h-3 transition duration-300 {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'text-blue-400' : '' }}" :class="linkActive ? 'rotate-90 text-blue-400' : ''"></i>
                        @endif
                    @if(isset($item['route']))
                        </a>
                    @else
                        </div>
                    @endif

                    <!-- Alt menüleri render et -->
                    @if(isset($item['submenu']) && !empty($item['submenu']))
                        <ul
                            x-show="linkActive"
                            x-cloak
                            x-collapse.duration.300ms
                            class="text-gray-400 ml-4 border-l-2 border-gray-700/50"
                        >
                            @foreach($item['submenu'] as $subItem)
                                @if($subItem['title'] === 'Yeni İçerik')
                                    <!-- Yeni İçerik - Özel Nested Submenu -->
                                    <li class="mb-1">
                                        <div x-data="{
                                            subHover: false,
                                            subActive: {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.*') ? 'true' : 'false' }},
                                            init() {
                                                // Menü state'ini localStorage'dan yükle
                                                const savedState = localStorage.getItem('submenu_yeni_icerik_active');
                                                if (savedState !== null) {
                                                    this.subActive = savedState === 'true';
                                                }
                                            }
                                        }">
                                            <div
                                                @mouseover = "subHover = true"
                                                @mouseleave = "subHover = false"
                                                @click = "subActive = !subActive; localStorage.setItem('submenu_yeni_icerik_active', subActive)"
                                                class="flex items-center justify-between text-gray-400 hover:text-white px-4 py-2 cursor-pointer hover:bg-black/20 transition-all duration-300 rounded-r-lg mx-2 {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.*') ? 'bg-blue-500/10 text-white border-r-2 border-blue-400' : '' }}"
                                            >
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 rounded-full bg-gray-500 mr-3 transition-colors duration-300" :class="subHover || subActive ? 'bg-blue-400' : ''"></div>
                                                    <span class="text-sm font-medium">{{ $subItem['title'] }}</span>
                                                </div>
                                                <i class="fas fa-chevron-right w-3 h-3 transition duration-300" :class="subActive ? 'rotate-90 text-blue-400' : ''"></i>
                                            </div>

                                            <div
                                                x-show="subActive"
                                                x-cloak
                                                x-collapse.duration.300ms
                                                class="ml-6 mt-1 space-y-1 border-l-2 border-gray-600/30"
                                            >
                                                <div class="px-4 py-2 cursor-pointer hover:bg-black/20 transition-all duration-300 hover:text-white rounded-r-lg mx-2 {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.news') ? 'bg-blue-500/10 text-white border-r-2 border-blue-400' : '' }}">
                                                    <a href="{{ route('posts.create.news') }}" class="flex items-center {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.news') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                                        <div class="w-1.5 h-1.5 rounded-full {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.news') ? 'bg-blue-400' : 'bg-gray-400' }} mr-3 transition-colors duration-300 hover:bg-blue-400"></div>
                                                        <span class="text-sm font-medium">Haber Ekle</span>
                                                    </a>
                                                </div>
                                                <div class="px-4 py-2 cursor-pointer hover:bg-black/20 transition-all duration-300 hover:text-white rounded-r-lg mx-2 {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.gallery') ? 'bg-blue-500/10 text-white border-r-2 border-blue-400' : '' }}">
                                                    <a href="{{ route('posts.create.gallery') }}" class="flex items-center {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.gallery') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                                        <div class="w-1.5 h-1.5 rounded-full {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.gallery') ? 'bg-blue-400' : 'bg-gray-400' }} mr-3 transition-colors duration-300 hover:bg-blue-400"></div>
                                                        <span class="text-sm font-medium">Galeri Ekle</span>
                                                    </a>
                                                </div>
                                                <div class="px-4 py-2 cursor-pointer hover:bg-black/20 transition-all duration-300 hover:text-white rounded-r-lg mx-2 {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.video') ? 'bg-blue-500/10 text-white border-r-2 border-blue-400' : '' }}">
                                                    <a href="{{ route('posts.create.video') }}" class="flex items-center {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.video') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                                        <div class="w-1.5 h-1.5 rounded-full {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.video') ? 'bg-blue-400' : 'bg-gray-400' }} mr-3 transition-colors duration-300 hover:bg-blue-400"></div>
                                                        <span class="text-sm font-medium">Video Ekle</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @else
                                    <!-- Normal Submenu Item -->
                                    <li class="mb-1">
                                        @if(isset($subItem['route']))
                                            <a
                                                href="{{ route($subItem['route']) }}"
                                                class="flex items-center text-gray-400 hover:text-white px-4 py-2 cursor-pointer hover:bg-black/20 transition-all duration-300 rounded-r-lg mx-2 {{ \App\Helpers\MenuHelper::isActivePattern($subItem['active'] ?? '') ? 'bg-blue-500/10 text-white border-r-2 border-blue-400' : '' }}"
                                            >
                                                <div class="w-2 h-2 rounded-full bg-gray-500 mr-3"></div>
                                                <span class="text-sm font-medium">{{ $subItem['title'] }}</span>
                                            </a>
                                        @else
                                            <div class="flex items-center text-gray-400 px-4 py-2 mx-2">
                                                <div class="w-2 h-2 rounded-full bg-gray-500 mr-3"></div>
                                                <span class="text-sm font-medium">{{ $subItem['title'] }}</span>
                                            </div>
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                @else
                    <div x-data="{
                        linkHover: false,
                        linkActive: {{ (\App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') || \App\Helpers\MenuHelper::hasActiveSubmenu($item)) ? 'true' : 'false' }},
                        init() {
                            // Menü state'ini kontrol et
                            const menuKey = 'menu_{{ $item['name'] ?? $loop->index }}_active';
                            const isActivePage = {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'true' : 'false' }};
                            const hasActiveSubmenu = {{ \App\Helpers\MenuHelper::hasActiveSubmenu($item) ? 'true' : 'false' }};

                            // Eğer ana menü veya alt menülerinden biri aktifse, menüyü açık tut
                            if (isActivePage || hasActiveSubmenu) {
                                this.linkActive = true;
                                // Aktif sayfada localStorage'a kaydet
                                localStorage.setItem(menuKey, 'true');
                            } else {
                                // Aktif sayfa değilse menüyü kapalı yap ve localStorage'dan temizle
                                this.linkActive = false;
                                localStorage.removeItem(menuKey);
                            }
                        }
                    }">
                        <div
                            @mouseover = "linkHover = true"
                            @mouseleave = "linkHover = false"
                            @click = "linkActive = !linkActive; const isActivePage = {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'true' : 'false' }}; const hasActiveSubmenu = {{ \App\Helpers\MenuHelper::hasActiveSubmenu($item) ? 'true' : 'false' }}; if (isActivePage || hasActiveSubmenu) { localStorage.setItem('menu_{{ $item['name'] ?? $loop->index }}_active', linkActive); } else { localStorage.removeItem('menu_{{ $item['name'] ?? $loop->index }}_active'); }"
                            class="flex items-center justify-between text-gray-400 hover:text-white px-6 py-3 cursor-pointer hover:bg-black/30 transition-all duration-300 rounded-r-xl {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'bg-gradient-to-r from-blue-600/20 to-purple-600/20 text-white border-r-2 border-blue-400' : '' }}"
                        >
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-7 h-7 rounded-lg {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'bg-blue-500/20' : 'bg-gray-700/50' }} transition-all duration-300" :class="linkHover || linkActive ? 'bg-blue-500/20' : ''">
                                    <i class="{{ $item['icon'] }} w-3.5 h-3.5 transition duration-300 {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'text-blue-400' : '' }}" :class="linkHover || linkActive ? 'text-blue-400' : ''"></i>
                                </div>
                                <span class="ml-3 text-sm font-medium mr-2">{{ $item['title'] }}</span>
                            </div>
                            <i class="fas fa-chevron-right w-3 h-3 transition duration-300 {{ \App\Helpers\MenuHelper::isActivePattern($item['active'] ?? '') ? 'text-blue-400' : '' }}" :class="linkActive ? 'rotate-90 text-blue-400' : ''"></i>
                        </div>

                                                @if(isset($item['submenu']) && !empty($item['submenu']))
                            <ul
                                x-show="linkActive"
                                x-cloak
                                x-collapse.duration.300ms
                                class="text-gray-400 ml-4 border-l-2 border-gray-700/50"
                            >
                                @foreach($item['submenu'] as $subItem)
                                    @if($subItem['title'] === 'Yeni İçerik')
                                        <!-- Yeni İçerik - Hardcoded Nested Submenu -->
                                        <div x-data="{
                                            subHover: false,
                                            subActive: {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.*') ? 'true' : 'false' }},
                                            init() {
                                                // Menü state'ini localStorage'dan yükle
                                                const savedState = localStorage.getItem('submenu_yeni_icerik_active');
                                                if (savedState !== null) {
                                                    this.subActive = savedState === 'true';
                                                }
                                            }
                                        }">
                                            <div
                                                @mouseover = "subHover = true"
                                                @mouseleave = "subHover = false"
                                                @click = "subActive = !subActive; localStorage.setItem('submenu_yeni_icerik_active', subActive)"
                                                class="flex items-center justify-between text-gray-400 hover:text-white px-4 py-2 cursor-pointer hover:bg-black/20 transition-all duration-300 rounded-r-lg mx-2 {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.*') ? 'bg-blue-500/10 text-white border-r-2 border-blue-400' : '' }}"
                                            >
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 rounded-full bg-gray-500 mr-3 transition-colors duration-300" :class="subHover || subActive ? 'bg-blue-400' : ''"></div>
                                                    <span class="text-sm font-medium">{{ $subItem['title'] }}</span>
                                                </div>
                                                <i class="fas fa-chevron-right w-3 h-3 transition duration-300" :class="subActive ? 'rotate-90 text-blue-400' : ''"></i>
                                            </div>

                                            <div
                                                x-show="subActive"
                                                x-cloak
                                                x-collapse.duration.300ms
                                                class="ml-6 mt-1 space-y-1 border-l-2 border-gray-600/30"
                                            >
                                                <div class="px-4 py-2 cursor-pointer hover:bg-black/20 transition-all duration-300 hover:text-white rounded-r-lg mx-2 {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.news') ? 'bg-blue-500/10 text-white border-r-2 border-blue-400' : '' }}">
                                                    <a href="{{ route('posts.create.news') }}" class="flex items-center {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.news') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                                        <div class="w-1.5 h-1.5 rounded-full {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.news') ? 'bg-blue-400' : 'bg-gray-400' }} mr-3 transition-colors duration-300 hover:bg-blue-400"></div>
                                                        <span class="text-sm font-medium">Haber Ekle</span>
                                                    </a>
                                                </div>
                                                <div class="px-4 py-2 cursor-pointer hover:bg-black/20 transition-all duration-300 hover:text-white rounded-r-lg mx-2 {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.gallery') ? 'bg-blue-500/10 text-white border-r-2 border-blue-400' : '' }}">
                                                    <a href="{{ route('posts.create.gallery') }}" class="flex items-center {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.gallery') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                                        <div class="w-1.5 h-1.5 rounded-full {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.gallery') ? 'bg-blue-400' : 'bg-gray-400' }} mr-3 transition-colors duration-300 hover:bg-blue-400"></div>
                                                        <span class="text-sm font-medium">Galeri Ekle</span>
                                                    </a>
                                                </div>
                                                <div class="px-4 py-2 cursor-pointer hover:bg-black/20 transition-all duration-300 hover:text-white rounded-r-lg mx-2 {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.video') ? 'bg-blue-500/10 text-white border-r-2 border-blue-400' : '' }}">
                                                    <a href="{{ route('posts.create.video') }}" class="flex items-center {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.video') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                                        <div class="w-1.5 h-1.5 rounded-full {{ \App\Helpers\MenuHelper::isActivePattern('posts.create.video') ? 'bg-blue-400' : 'bg-gray-400' }} mr-3 transition-colors duration-300 hover:bg-blue-400"></div>
                                                        <span class="text-sm font-medium">Video Ekle</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <li class="px-4 py-2 cursor-pointer hover:bg-black/20 transition-all duration-300 hover:text-white rounded-r-lg mx-2 {{ \App\Helpers\MenuHelper::isActivePattern($subItem['active'] ?? '') ? 'bg-blue-500/10 text-white border-r-2 border-blue-400' : '' }}">
                                            @if(isset($subItem['route']))
                                                <a
                                                    href="{{ route($subItem['route']) }}"
                                                    class="flex items-center {{ \App\Helpers\MenuHelper::isActivePattern($subItem['active'] ?? '') ? 'text-white' : '' }}"
                                                >
                                                    <div class="w-2 h-2 rounded-full bg-gray-500 mr-3 transition-colors duration-300 hover:bg-blue-400"></div>
                                                    <span class="text-sm font-medium overflow-ellipsis">{{ $subItem['title'] }}</span>
                                                </a>
                                            @else
                                                <div class="flex items-center {{ \App\Helpers\MenuHelper::isActivePattern($subItem['active'] ?? '') ? 'text-white' : '' }}">
                                                    <div class="w-2 h-2 rounded-full bg-gray-500 mr-3 transition-colors duration-300 hover:bg-blue-400"></div>
                                                    <span class="text-sm font-medium overflow-ellipsis">{{ $subItem['title'] }}</span>
                                                </div>
                                            @endif
                                        </li>
                                                @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            @endforeach
                    </nav>
    </aside>

    <!-- Main Content -->
    <div class="lg:pl-64">
        <!-- Top Navigation -->
        <div class="flex items-center justify-between px-8 h-16 bg-[var(--surface)] border-b border-[var(--border-subtle)]">
            <!-- Mobile menu button -->
            <button @click="toggleSidebar()" class="lg:hidden">
                <i class="fas fa-bars text-gray-600"></i>
            </button>

            <!-- Right side -->
            <div class="flex items-center space-x-4 ml-auto">

                <!-- Dark Mode Toggle -->
                <button @click="toggleDarkMode()"
                        class="p-2 rounded-lg bg-[var(--surface-alt)] hover:bg-[var(--bg-muted)] text-[var(--text)] transition-colors duration-200">
                    <i x-show="!darkMode" class="fas fa-moon"></i>
                    <i x-show="darkMode" class="fas fa-sun"></i>
                </button>


                <!-- User menu -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center space-x-2">
                        <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=7F9CF5&background=EBF4FF" alt="">
                        <span class="text-sm font-medium text-[var(--text)]">{{ Auth::user()->name }}</span>
                        <i class="fas fa-chevron-down text-xs" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open"
                         @click.away="open = false"
                         class="absolute right-0 mt-2 w-48 bg-[var(--surface)] rounded-md shadow-lg border border-[var(--border-subtle)] z-10"
                         x-cloak>
                        <a href="{{ route('user.edit', auth()->id()) }}" class="block px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--table-row-hover)] hover:text-[var(--text)] transition-all duration-200 rounded-md">
                            <i class="fas fa-user mr-2"></i> Profil
                        </a>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-4 py-2 text-sm text-[var(--text)] hover:bg-[var(--table-row-hover)] hover:text-[var(--text)] transition-all duration-200 rounded-md">
                            <i class="fas fa-sign-out-alt mr-2"></i> Çıkış Yap
                        </a>
                        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-[var(--bg)] text-[var(--text)] transition-colors duration-300">
            <div class="p-8">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Files Modal - Hidden, will be shown via JavaScript -->
    <div id="files-modal-container" style="display: none;" wire:ignore>
        <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="files-modal-title" role="dialog" aria-modal="true" id="files-modal-wrapper">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true" id="files-modal-backdrop"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full relative z-[10000]">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900" id="files-modal-title">
                                <i class="fas fa-archive mr-2 text-blue-500"></i>
                                Arşivden Seç
                            </h3>
                            <button id="files-modal-close" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        <div id="files-modal-content">
                            @livewire('files.file-index', ['modal' => true])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- jQuery, Trumbowyg, and Trix are now loaded via NPM/Vite in resources/js/app.js -->
    <!-- No CDN scripts needed - everything is bundled via Vite -->

    <script nonce="{{ $cspNonce ?? '' }}">
        // Livewire file upload sırasında file chooser'ı engelle
        document.addEventListener('livewire:upload-start', function () {
            // File chooser açılmasını engelle
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.style.display = 'none';
            });
        });
    </script>

    @stack('scripts')

    <!-- Archive File Selector Modal - Posts modülü için özel -->
    <div id="archive-modal-container" style="display: none;" wire:ignore>
        <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="archive-modal-title" role="dialog" aria-modal="true" id="archive-modal-wrapper">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true" id="archive-modal-backdrop"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full relative z-[10000] max-h-[90vh] overflow-y-auto">
                    <div id="archive-modal-content">
                        @livewire('posts.archive-file-selector')
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
