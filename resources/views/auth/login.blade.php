<!DOCTYPE html>
<html lang="tr" x-data="loginApp()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Giriş Yap - Admin Panel</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>
    
    <!-- FOUC Prevention Script - Sayfa yüklenmeden önce dark mode uygula -->
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
        })();
    </script>
</head>

<body class="min-h-screen bg-[var(--bg)] font-inter transition-colors duration-300">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-grid-slate-100 dark:bg-grid-slate-800/25 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]"></div>
    
    <!-- Main Container -->
    <div class="relative min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header Section -->
            <div class="text-center">
                <!-- Logo -->
                <div class="mx-auto h-20 w-20 bg-[var(--surface)] rounded-2xl shadow-xl flex items-center justify-center ring-1 ring-[var(--border-subtle)] transition-all duration-300 hover:scale-105">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="h-12 w-12">
                </div>
                
                <!-- Title -->
                <h1 class="mt-8 text-3xl font-bold text-[var(--text)]">
                    Hoş Geldiniz
                </h1>
                <p class="mt-2 text-sm text-[var(--text-muted)]">
                    Hesabınızla giriş yapın
                </p>
            </div>

            <!-- Login Card -->
            <div class="bg-[var(--surface)] shadow-2xl rounded-3xl p-8 ring-1 ring-[var(--border-subtle)] transition-all duration-300">
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-[var(--danger-soft)] border border-[var(--danger)]/30 rounded-xl">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-[var(--danger)]"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-[var(--danger)]">Giriş Hatası</h3>
                                <div class="mt-2 text-sm text-[var(--danger)]">
                                    @foreach ($errors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" @submit="isLoading = true" class="space-y-6">
                    @csrf
                    
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-[var(--text)] mb-2">
                            E-posta Adresi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-[var(--text-muted)]"></i>
                            </div>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   class="block w-full pl-10 pr-3 py-3 bg-[var(--input-bg)] border border-[var(--border-subtle)] rounded-xl text-[var(--text)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)] focus:border-[var(--accent)] transition-colors duration-200 @error('email') border-[var(--danger)] @enderror" 
                                   placeholder="ornek@email.com"
                                   autocomplete="email"
                                   required>
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-[var(--danger)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-[var(--text)] mb-2">
                            Şifre
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-[var(--text-muted)]"></i>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   class="block w-full pl-10 pr-10 py-3 bg-[var(--input-bg)] border border-[var(--border-subtle)] rounded-xl text-[var(--text)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)] focus:border-[var(--accent)] transition-colors duration-200 @error('password') border-[var(--danger)] @enderror" 
                                   placeholder="••••••••"
                                   autocomplete="current-password"
                                   required>
                            <button type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-[var(--text-muted)] hover:text-[var(--text)] transition-colors duration-200"
                                    aria-label="Şifreyi göster/gizle">
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-[var(--danger)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="remember" 
                                   name="remember" 
                                   value="1"
                                   class="h-4 w-4 text-[var(--accent)] focus:ring-[var(--accent)] border-[var(--border-subtle)] rounded">
                            <label for="remember" class="ml-2 block text-sm text-[var(--text)]">
                                Beni Hatırla
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-[var(--accent)] hover:text-[var(--accent-strong)] transition-colors duration-200">
                                Şifremi Unuttum?
                            </a>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" 
                                :disabled="isLoading"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-[var(--text-on-accent)] bg-[var(--accent)] hover:bg-[var(--accent-strong)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--accent)] disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]">
                            <span x-show="!isLoading" x-cloak class="flex items-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Giriş Yap
                            </span>
                            <span x-show="isLoading" x-cloak class="flex items-center">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Giriş Yapılıyor...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Dark Mode Toggle -->
            <div class="text-center">
                <button @click="toggleDarkMode" 
                        class="inline-flex items-center px-4 py-2 border border-[var(--border-subtle)] rounded-xl text-sm font-medium text-[var(--text)] bg-[var(--surface)] hover:bg-[var(--surface-alt)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--accent)] transition-all duration-200 transform hover:scale-105">
                    <i x-show="!darkMode" class="fas fa-moon mr-2"></i>
                    <i x-show="darkMode" class="fas fa-sun mr-2"></i>
                    <span x-text="darkMode ? 'Açık Tema' : 'Koyu Tema'"></span>
                </button>
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-sm text-[var(--text-muted)]">
                    © {{ date('Y') }} Admin Panel. Tüm hakları saklıdır.
                </p>
            </div>
        </div>
    </div>

    <!-- Login App Alpine.js function moved to resources/js/app.js -->
</body>
</html>