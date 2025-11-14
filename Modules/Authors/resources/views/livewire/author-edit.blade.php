<div>
    @vite(['Modules/Authors/resources/assets/sass/app.scss', 'Modules/Authors/resources/assets/js/app.js'])
    
    <!-- Success Message -->
    <x-success-message :message="$successMessage" />
    
    <!-- Modern Header -->
    <div class="mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-600 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-edit text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Yazar Düzenle</h1>
                        <p class="text-gray-600">{{ $author->user->name }} yazarını düzenleyin</p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('authors.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200" x-data="authorForm()">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <i class="fas fa-edit text-blue-500 mr-2"></i>
                <h3 class="text-lg font-semibold text-gray-900">Yazar Bilgileri</h3>
            </div>
        </div>
        <div class="p-6">
            <form wire:submit.prevent="save">
                <!-- User Info -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kullanıcı</label>
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                            @if($author->image)
                                <img src="{{ Storage::url($author->image) }}" alt="{{ $author->user->name }}" class="w-12 h-12 rounded-full object-cover mr-3">
                            @else
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-white font-bold">{{ substr($author->user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div>
                                <h6 class="text-sm font-medium text-gray-900">{{ $author->user->name }}</h6>
                                <p class="text-xs text-gray-500">{{ $author->user->email }}</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Başlık <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="title" class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" id="title" placeholder="Örn: Finans Editörü">
                        <x-validation-error field="title" />
                    </div>
                </div>

                <div class="mb-6">
                    <label for="bio" class="block text-sm font-semibold text-gray-700 mb-2">Biyografi</label>
                    <textarea wire:model.defer="bio" class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" id="bio" rows="4" placeholder="Yazar hakkında kısa bilgi..."></textarea>
                    <x-validation-error field="bio" />
                </div>

                <!-- Profile Image -->
                <div class="mb-6">
                    <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">Profil Fotoğrafı</label>
                    @if($author->image)
                        <div class="mb-4">
                            <div class="flex items-center">
                                <img src="{{ Storage::url($author->image) }}" alt="Mevcut fotoğraf" class="w-16 h-16 rounded-full object-cover mr-3">
                                <div>
                                    <h6 class="text-sm font-medium text-gray-900">Mevcut Fotoğraf</h6>
                                    <p class="text-xs text-gray-500">Şu anki profil fotoğrafı</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors duration-200">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Yeni fotoğraf seçin</span>
                                    <input type="file" wire:model="image" class="sr-only" id="image" accept="image/*">
                                </label>
                                <p class="pl-1">veya sürükleyip bırakın</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF (Max. 2MB)</p>
                        </div>
                    </div>
                    <x-validation-error field="image" />
                    @if($image)
                        <div class="mt-4">
                            <div class="flex items-center">
                                <img src="{{ $image->temporaryUrl() }}" alt="Yeni fotoğraf önizleme" class="w-16 h-16 rounded-full object-cover mr-3">
                                <div>
                                    <h6 class="text-sm font-medium text-gray-900">Yeni Fotoğraf</h6>
                                    <p class="text-xs text-gray-500">Seçilen yeni profil fotoğrafı</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Social Media -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="twitter" class="block text-sm font-semibold text-gray-700 mb-2">Twitter</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fab fa-twitter text-gray-400"></i>
                            </div>
                            <input type="text" wire:model.defer="twitter" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" id="twitter" placeholder="@kullaniciadi">
                        </div>
                        <x-validation-error field="twitter" />
                    </div>
                    <div>
                        <label for="linkedin" class="block text-sm font-semibold text-gray-700 mb-2">LinkedIn</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fab fa-linkedin text-gray-400"></i>
                            </div>
                            <input type="text" wire:model.defer="linkedin" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" id="linkedin" placeholder="linkedin.com/in/kullanici">
                        </div>
                        <x-validation-error field="linkedin" />
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="facebook" class="block text-sm font-semibold text-gray-700 mb-2">Facebook</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fab fa-facebook text-gray-400"></i>
                            </div>
                            <input type="text" wire:model.defer="facebook" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" id="facebook" placeholder="facebook.com/kullanici">
                        </div>
                        <x-validation-error field="facebook" />
                    </div>
                    <div>
                        <label for="instagram" class="block text-sm font-semibold text-gray-700 mb-2">Instagram</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fab fa-instagram text-gray-400"></i>
                            </div>
                            <input type="text" wire:model.defer="instagram" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" id="instagram" placeholder="@kullaniciadi">
                        </div>
                        <x-validation-error field="instagram" />
                    </div>
                </div>

                <div class="mb-6">
                    <label for="website" class="block text-sm font-semibold text-gray-700 mb-2">Website</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-globe text-gray-400"></i>
                        </div>
                        <input type="url" wire:model.defer="website" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" id="website" placeholder="https://example.com">
                    </div>
                    <x-validation-error field="website" />
                </div>

                <!-- Settings -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div>
                        <label for="weight" class="block text-sm font-semibold text-gray-700 mb-2">Sıra</label>
                        <input type="number" wire:model.defer="weight" class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" id="weight" min="0">
                        <x-validation-error field="weight" />
                    </div>
                    <div class="flex items-center">
                        <div class="flex items-center">
                            <input type="checkbox" wire:model.defer="show_on_mainpage" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" id="show_on_mainpage">
                            <label for="show_on_mainpage" class="ml-2 text-sm font-medium text-gray-700">Ana Sayfada Göster</label>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="flex items-center">
                            <input type="checkbox" wire:model.defer="status" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" id="status">
                            <label for="status" class="ml-2 text-sm font-medium text-gray-700">Aktif</label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('authors.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        İptal
                    </a>
                    <button type="submit" class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-lg font-medium hover:from-orange-600 hover:to-red-700 transition-all duration-300 shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i>
                        Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>