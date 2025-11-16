<div x-data="postsForm()">
    <!-- Modern Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        @if($this->post->post_type === 'gallery')
                            <i class="fas fa-images text-white text-xl"></i>
                        @elseif($this->post->post_type === 'video')
                            <i class="fas fa-video text-white text-xl"></i>
                        @else
                            <i class="fas fa-edit text-white text-xl"></i>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">
                            @if($this->post->post_type === 'gallery')
                                Galeri Düzenle
                            @elseif($this->post->post_type === 'video')
                                Video Düzenle
                            @else
                                Haber Düzenle
                            @endif
                        </h2>
                        <p class="text-gray-600">Mevcut içeriği düzenleyin</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('posts.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Geri Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <form wire:submit="updatePost">
                        <!-- Meta Form (Title, Slug, Summary, Embed Code) -->
                        @livewire('posts.post-edit-meta-form', ['post' => $this->post], key('meta-form-'.$this->post->post_id))

                        <!-- Media Manager (Gallery/Image Upload) -->
                        @livewire('posts.post-edit-media-manager', ['post' => $this->post], key('media-manager-'.$this->post->post_id))

                        <!-- Content Form (Trumbowyg Editor) -->
                        @livewire('posts.post-edit-content-form', ['post' => $this->post], key('content-form-'.$this->post->post_id))

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button type="submit"
                                    class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 shadow-lg hover:shadow-xl transition-all duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Değişiklikleri Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Meta Form Sidebar (Status, Position, Settings, Featured) -->
            @livewire('posts.post-edit-meta-form-sidebar', ['post' => $this->post], key('meta-form-sidebar-'.$this->post->post_id))

            <!-- Kategoriler ve Etiketler -->
            @livewire('posts.post-edit-relations-form', ['post' => $this->post], key('relations-form-'.$this->post->post_id))

            <!-- İstatistikler -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-chart-bar mr-2 text-orange-500"></i>
                        İstatistikler
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Görüntülenme</span>
                            <span class="text-sm font-medium text-gray-900">{{ number_format($this->post->view_count ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Oluşturulma</span>
                            <span class="text-sm font-medium text-gray-900">{{ $this->post->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Son Güncelleme</span>
                            <span class="text-sm font-medium text-gray-900">{{ $this->post->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Posts modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Posts/resources/assets/sass/app.scss', 'Modules/Posts/resources/assets/js/app.js'])
</div>
