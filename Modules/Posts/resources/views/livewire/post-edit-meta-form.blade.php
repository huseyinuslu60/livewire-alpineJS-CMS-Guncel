<div>
    <!-- Başlık -->
    <div class="mb-6">
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-heading mr-1 text-orange-500"></i>
            Başlık *
        </label>
        <input type="text" 
               wire:model.live="title" 
               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
               id="title" 
               placeholder="Haber başlığını girin..."
               required>
        @error('title')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Slug -->
    <div class="mb-6">
        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-link mr-1 text-orange-500"></i>
            Slug
        </label>
        <input type="text" 
               wire:model.live="slug" 
               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('slug') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
               id="slug" 
               placeholder="URL slug'ı (otomatik oluşturulur)">
        @error('slug')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Özet -->
    <div class="mb-6">
        <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-file-alt mr-1 text-orange-500"></i>
            Özet
        </label>
        <textarea wire:model.live="summary" 
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('summary') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                  id="summary" 
                  rows="3" 
                  placeholder="Haber özetini girin..."></textarea>
        @error('summary')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if($post->post_type === 'video')
        <!-- Video Embed Kodu -->
        <div class="mb-6">
            <label for="embed_code" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-code mr-1 text-orange-500"></i>
                Video Embed Kodu *
            </label>
            <textarea wire:model.live="embed_code" 
                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('embed_code') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                      id="embed_code" 
                      rows="4" 
                      placeholder="<iframe src='...' width='630' height='354'></iframe>"
                      required></textarea>
            @error('embed_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                YouTube, Vimeo veya diğer video platformlarından embed kodunu yapıştırın.
            </p>
        </div>
    @endif
</div>

