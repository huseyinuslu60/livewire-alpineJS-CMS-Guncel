@if(!$isGallery)
    <div class="mb-6">
        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-align-left mr-1 text-orange-500"></i>
            İçerik *
        </label>
        <div wire:ignore>
            <textarea wire:model.live="content" 
                      class="trumbowyg block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('content') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                      id="content" 
                      rows="8" 
                      placeholder="Haber içeriğini girin..."
                      required></textarea>
        </div>
        @error('content')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
@endif

