<div class="newsletter-module">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-lg"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-green-400 hover:text-green-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-red-400 hover:text-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <!-- Left Side - Available Posts -->
        <div class="xl:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Son 7 Günün Haberleri</h3>

                <!-- Search Box -->
                <div class="mb-4">
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.300ms="searchQuery"
                               placeholder="Haber ara..."
                               class="w-full px-4 py-2 pl-10 pr-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        @if($searchQuery)
                            <button wire:click="$set('searchQuery', '')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($this->availablePosts ?? [] as $post)
                        <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-move transition-all duration-200 hover:shadow-md hover:border-blue-300"
                             draggable="true"
                             data-post-id="{{ $post->post_id }}"
                             ondragstart="drag(event)"
                             wire:click="addPostToNewsletter({{ $post->post_id }})">

                            <div class="flex items-start space-x-3">
                                @if($post->primaryFile)
                                    <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg overflow-hidden">
                                        @php
                                            $imagePath = $post->primaryFile->file_path;
                                            if (strpos($imagePath, 'http') === 0) {
                                                $imageUrl = $imagePath;
                                            } elseif (strpos($imagePath, 'storage/') === 0) {
                                                $imageUrl = asset($imagePath);
                                            } else {
                                                $imageUrl = asset('storage/' . $imagePath);
                                            }
                                        @endphp
                                        <img src="{{ $imageUrl }}"
                                             alt="{{ $post->title }}"
                                             class="w-full h-full object-cover"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden w-full h-full bg-gray-200 items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 line-clamp-2 mb-1">{{ $post->title }}</h4>
                                    <p class="text-xs text-gray-500 line-clamp-2">{{ $post->summary }}</p>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="text-xs text-gray-400">{{ $post->published_date ? $post->published_date->format('d.m.Y') : '' }}</span>
                                        <button wire:click="addPostToNewsletter({{ $post->post_id }})"
                                                class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full hover:bg-green-200 transition-colors">
                                            <i class="fas fa-plus mr-1"></i>Ekle
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-newspaper text-4xl mb-4"></i>
                            <p>Henüz newsletter için hazır haber bulunmuyor.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($this->availablePosts && method_exists($this->availablePosts, 'hasPages') && $this->availablePosts->hasPages())
                    <div class="mt-4">
                        {{ $this->availablePosts->links('livewire::simple-tailwind', ['pageName' => 'postsPage']) }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Middle - Sortable Area -->
        <div class="xl:col-span-1">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-sm border-2 border-dashed border-blue-200 p-6 min-h-96">
                <div class="text-center mb-4">
                    <i class="fas fa-sort text-3xl text-blue-400 mb-2"></i>
                    <h4 class="text-lg font-semibold text-blue-800 mb-2">Haber Sıralama</h4>
                    <p class="text-sm text-blue-600">Haberleri sürükleyerek sıralayın</p>
                </div>

                <!-- Sortable Posts Area -->
                @if(!empty($selectedPosts))
                    <div class="bg-white rounded-lg border border-blue-200 p-4 min-h-80">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="text-sm font-semibold text-blue-800 flex items-center">
                                <i class="fas fa-grip-vertical text-blue-600 mr-2"></i>
                                Seçili Haberler
                            </h5>
                            <span class="text-xs text-gray-500">{{ count($selectedPosts) }} haber</span>
                        </div>
                        <div id="sortable-posts" class="space-y-2">
                            @foreach($selectedPosts as $index => $postId)
                                @php
                                    $post = null;
                                    if ($this->availablePosts && method_exists($this->availablePosts, 'getCollection')) {
                                        $post = $this->availablePosts->getCollection()->where('post_id', $postId)->first();
                                    } elseif ($this->availablePosts && method_exists($this->availablePosts, 'items')) {
                                        $post = collect($this->availablePosts->items())->where('post_id', $postId)->first();
                                    }
                                    $post = $post ?? \Modules\Posts\Models\Post::with(['primaryFile', 'author'])->find($postId);
                                @endphp
                                @if($post)
                                    <div class="sortable-item flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-sm transition-all cursor-move"
                                         data-post-id="{{ $postId }}"
                                         data-index="{{ $index }}">
                                        <div class="flex items-center space-x-3">
                                            <div class="text-gray-400">
                                                <i class="fas fa-grip-vertical"></i>
                                            </div>
                                            <div class="flex-1">
                                                <span class="text-sm font-medium text-gray-900">{{ $post->title }}</span>
                                                <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ $post->summary }}</p>
                                            </div>
                                        </div>
                                        <button wire:click="removePostFromNewsletter({{ $postId }})"
                                                class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-600 rounded text-xs transition-colors"
                                                onclick="return confirm('Bu haberi bültenden çıkarmak istediğinizden emin misiniz?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                        <i class="fas fa-newspaper text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 mb-2">Henüz haber seçilmedi</p>
                        <p class="text-sm text-gray-400">Sol panelden haberleri buraya sürükleyin</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side - Newsletter Builder -->
        <div class="xl:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Bülte Oluştur</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('newsletters.templates.index') }}"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <i class="fas fa-palette mr-2"></i>Template Yönetimi
                            </a>
                            <a href="{{ route('newsletters.templates.create') }}"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Yeni Template
                            </a>
                        </div>
                    </div>

                    <!-- Template Selection -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-sm font-semibold text-gray-800">📧 Bülten Template Seçin</h4>
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-8 gap-2">
                            @foreach($availableTemplates as $template)
                                <div class="template-card {{ $selectedTemplate == $template->id ? 'selected' : '' }}"
                                     wire:click="selectTemplate({{ $template->id }})"
                                     style="cursor: pointer; border: 1.5px solid {{ $selectedTemplate == $template->id ? '#3b82f6' : '#e5e7eb' }}; border-radius: 6px; padding: 5px; background: {{ $selectedTemplate == $template->id ? '#f0f9ff' : 'white' }}; transition: all 0.2s ease; display: flex; flex-direction: column;">

                                    <!-- Template Preview -->
                                    <div class="template-preview mb-1.5" style="height: 45px; background: {{ $template->styles['background_color'] ?? '#f8fafc' }}; border-radius: 3px; padding: 3px; overflow: hidden; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.08); flex-shrink: 0;">
                                        <!-- Header Preview -->
                                        <div style="background: {{ $template->styles['primary_color'] ?? '#3b82f6' }}; height: 8px; border-radius: 2px; margin-bottom: 3px; display: flex; align-items: center; padding: 0 3px;">
                                            <div style="width: 6px; height: 6px; background: white; border-radius: 1px; margin-right: 3px; flex-shrink: 0;"></div>
                                            <div style="width: 20px; height: 3px; background: rgba(255,255,255,0.7); border-radius: 1px; flex-shrink: 0;"></div>
                                        </div>

                                        <!-- Content Preview -->
                                        <div style="display: flex; gap: 3px; height: 15px; margin-bottom: 3px;">
                                            <div style="width: 12px; height: 12px; background: #e5e7eb; border-radius: 2px; flex-shrink: 0;"></div>
                                            <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; min-width: 0;">
                                                <div style="width: 100%; height: 2px; background: #dc2626; border-radius: 1px; margin-bottom: 2px;"></div>
                                                <div style="width: 85%; height: 1.5px; background: #6b7280; border-radius: 1px; margin-bottom: 1px;"></div>
                                                <div style="width: 70%; height: 1.5px; background: #6b7280; border-radius: 1px;"></div>
                                            </div>
                                        </div>

                                        <!-- Footer Preview -->
                                        <div style="position: absolute; bottom: 2px; left: 3px; right: 3px; height: 4px; background: {{ $template->styles['secondary_color'] ?? '#f3f4f6' }}; border-radius: 2px;"></div>
                                    </div>

                                    <!-- Template Info -->
                                    <div style="flex: 1; display: flex; flex-direction: column; min-width: 0;">
                                        <h5 class="font-semibold text-gray-900 mb-0.5 line-clamp-1" style="font-size: 10px; line-height: 1.2;">{{ $template->name }}</h5>

                                        <!-- Template Colors -->
                                        <div class="flex gap-1 mb-1" style="flex-shrink: 0; margin-top: 2px;">
                                            <div style="width: 6px; height: 6px; background: {{ $template->styles['primary_color'] ?? '#3b82f6' }}; border-radius: 2px; border: 0.5px solid rgba(0,0,0,0.1); flex-shrink: 0;"></div>
                                            <div style="width: 6px; height: 6px; background: {{ $template->styles['secondary_color'] ?? '#6b7280' }}; border-radius: 2px; border: 0.5px solid rgba(0,0,0,0.1); flex-shrink: 0;"></div>
                                            <div style="width: 6px; height: 6px; background: {{ $template->styles['text_color'] ?? '#1f2937' }}; border-radius: 2px; border: 0.5px solid rgba(0,0,0,0.1); flex-shrink: 0;"></div>
                                        </div>

                                        @if($selectedTemplate == $template->id)
                                            <div class="flex items-center text-blue-600 font-medium" style="font-size: 9px; margin-top: auto;">
                                                <i class="fas fa-check-circle mr-0.5" style="font-size: 9px;"></i>
                                                <span>Seçili</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>


                    <!-- Newsletter Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Bülten Adı <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               wire:model="name"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('name') border-red-300 @enderror"
                               placeholder="Bülten adını girin">
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="mail_subject" class="block text-sm font-semibold text-gray-700 mb-2">
                            E-posta Konusu <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="mail_subject"
                               wire:model="mail_subject"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('mail_subject') border-red-300 @enderror"
                               placeholder="E-posta konusunu girin">
                        @error('mail_subject')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                                </div>
                            </div>



                <!-- Action Buttons -->
                <div class="flex items-center justify-between">
                    <div class="flex space-x-3">
                        <button onclick="openPreview()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-eye mr-2"></i> Önizleme
                        </button>
                    </div>

                    <div class="flex space-x-3">
                        <a href="{{ route('newsletters.index') }}"
                           class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i> Geri Dön
                        </a>
                        <button wire:click="store"
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-save mr-2"></i> Bülten Oluştur
                        </button>
                    </div>
                </div>

                <!-- Newsletter Preview Section -->
                <div class="mt-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-md font-semibold text-gray-900">Bülten Önizleme</h3>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500">Template:</span>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                        {{ $availableTemplates->where('id', $selectedTemplate)->first()->name ?? 'Modern Gradient' }}
                                    </span>
                                </div>
                                <button onclick="closePreview()"
                                        class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Email Preview Container -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 max-h-[800px] overflow-y-auto">
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" style="transform: scale(0.85); transform-origin: top left; width: 117.65%;">
                                <!-- Email Header -->
                                <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        </div>
                                        <div class="text-xs text-gray-500">Newsletter Preview</div>
                                    </div>
                                </div>

                                <!-- Email Content -->
                                <div class="p-4">
                                    <div class="newsletter-preview-content text-sm">
                                        {{-- Önizleme için sanitizer kullanmıyoruz, template stillerinin görünmesi için --}}
                                        {!! $mail_body !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <!-- Newsletter Preview Modal -->
    <div id="preview-modal"
         class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-0 px-4 pb-0 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="closePreview()"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-0 sm:align-middle sm:max-w-7xl sm:w-full sm:h-screen">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 h-full flex flex-col">
                    <div class="sm:flex sm:items-start flex-1 flex flex-col">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full flex-1 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Newsletter Önizleme</h3>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500">Template:</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                    {{ $availableTemplates->where('id', $selectedTemplate)->first()->name ?? 'Modern Gradient' }}
                                </span>
                            </div>
                            <button onclick="closePreview()"
                                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200 relative z-10 bg-white rounded-full p-1 hover:bg-gray-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                            </div>
                            <div class="newsletter-preview-content flex-1 overflow-y-auto border border-gray-200 rounded-lg">
                                <div style="transform: scale(0.8); transform-origin: top left; width: 125%;">
                                    {{-- Önizleme için sanitizer kullanmıyoruz, template stillerinin görünmesi için --}}
                                    {!! $mail_body !!}
                                </div>
                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    @vite(['Modules/Newsletters/resources/assets/sass/app.scss', 'Modules/Newsletters/resources/assets/js/app.js'])

    <!-- Sortable.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</div>
