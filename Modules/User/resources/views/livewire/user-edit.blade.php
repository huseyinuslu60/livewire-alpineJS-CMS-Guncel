<div x-data="usersForm()">
    <!-- Success Message -->
    <x-success-message :message="$successMessage" />
    
    <!-- Modern Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-edit text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">Kullanıcı Düzenle</h2>
                        <p class="text-gray-600">{{ $user->name }} kullanıcısını düzenleyin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('user.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div x-data="{ showSuccess: true }" 
             x-show="showSuccess" 
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

    @if (session()->has('warning'))
        <div x-data="{ showWarning: true }" 
             x-show="showWarning" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-lg"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="showWarning = false" class="text-yellow-400 hover:text-yellow-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <i class="fas fa-edit text-blue-500 mr-2"></i>
                <h3 class="text-lg font-semibold text-gray-900">Kullanıcı Bilgileri</h3>
            </div>
        </div>
        <div class="p-6">
            <form wire:submit.prevent="update">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Ad Soyad <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model.defer="name" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                               id="name" 
                               placeholder="Ad Soyad giriniz...">
                        <x-validation-error field="name" />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            E-posta <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               wire:model.defer="email" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                               id="email" 
                               placeholder="E-posta adresi giriniz...">
                        <x-validation-error field="email" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Yeni Şifre
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" 
                                   wire:model.defer="password" 
                                   @input="checkPasswordStrength($event.target.value)"
                                   class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                                   id="password" 
                                   placeholder="Yeni şifre giriniz (boş bırakabilirsiniz)...">
                            <button type="button" 
                                    @click="togglePassword()" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-gray-400"></i>
                            </button>
                        </div>
                        <!-- Password Strength Indicator -->
                        <div x-show="password" class="mt-2">
                            <div class="flex items-center space-x-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300" 
                                         :class="getPasswordStrengthColor()" 
                                         :style="`width: ${(passwordStrength / 5) * 100}%`"></div>
                                </div>
                                <span class="text-xs font-medium" :class="getPasswordStrengthColor().replace('bg-', 'text-')" 
                                      x-text="getPasswordStrengthText()"></span>
                            </div>
                            <!-- Password Requirements -->
                            <div class="mt-2 space-y-1">
                                <div class="flex items-center text-xs" :class="passwordRequirements.length ? 'text-green-600' : 'text-gray-400'">
                                    <i :class="passwordRequirements.length ? 'fas fa-check' : 'fas fa-times'" class="mr-1"></i>
                                    En az 8 karakter
                                </div>
                                <div class="flex items-center text-xs" :class="passwordRequirements.uppercase ? 'text-green-600' : 'text-gray-400'">
                                    <i :class="passwordRequirements.uppercase ? 'fas fa-check' : 'fas fa-times'" class="mr-1"></i>
                                    Büyük harf
                                </div>
                                <div class="flex items-center text-xs" :class="passwordRequirements.lowercase ? 'text-green-600' : 'text-gray-400'">
                                    <i :class="passwordRequirements.lowercase ? 'fas fa-check' : 'fas fa-times'" class="mr-1"></i>
                                    Küçük harf
                                </div>
                                <div class="flex items-center text-xs" :class="passwordRequirements.number ? 'text-green-600' : 'text-gray-400'">
                                    <i :class="passwordRequirements.number ? 'fas fa-check' : 'fas fa-times'" class="mr-1"></i>
                                    Sayı
                                </div>
                                <div class="flex items-center text-xs" :class="passwordRequirements.special ? 'text-green-600' : 'text-gray-400'">
                                    <i :class="passwordRequirements.special ? 'fas fa-check' : 'fas fa-times'" class="mr-1"></i>
                                    Özel karakter
                                </div>
                            </div>
                        </div>
                        <x-validation-error field="password" />
                    </div>
                    <div>
                        <label for="role_ids" class="block text-sm font-medium text-gray-700 mb-2">
                            Roller <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.defer="role_ids" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                                id="role_ids" 
                                multiple>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Ctrl tuşuna basılı tutarak birden fazla rol seçebilirsiniz.</p>
                        <x-validation-error field="role_ids" />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('user.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                        <i class="fas fa-times mr-2"></i>
                        İptal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Kullanıcıyı Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- User modülü asset dosyalarını dahil et --}}
    @vite(['Modules/User/resources/assets/sass/app.scss', 'Modules/User/resources/assets/js/app.js'])
</div>
