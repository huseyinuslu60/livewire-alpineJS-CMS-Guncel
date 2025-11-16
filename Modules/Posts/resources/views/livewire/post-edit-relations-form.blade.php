<div>
    <!-- Kategoriler -->
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-tags mr-2 text-orange-500"></i>
            Kategoriler
        </h3>
        <div wire:ignore>
            <select wire:model.live="categoryIds" 
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                    id="categoryIds" 
                    multiple>
                @foreach($categories as $category)
                    <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        @error('categoryIds')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Etiketler -->
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-tag mr-2 text-orange-500"></i>
            Etiketler
        </h3>
        <div x-data="tagsInput()" class="space-y-3">
            <!-- Mevcut Etiketler -->
            <div class="flex flex-wrap gap-2" x-show="tags.length > 0">
                <template x-for="(tag, index) in tags" :key="index">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800 border border-orange-200">
                        <span x-text="tag"></span>
                        <button type="button" 
                                @click="removeTag(index)"
                                class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-orange-200 transition-colors">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </span>
                </template>
            </div>
            
            <!-- Yeni Etiket Ekleme -->
            <div class="flex space-x-2">
                <input type="text" 
                       x-model="newTag"
                       @keydown="keydown($event)"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm" 
                       placeholder="Etiket ekle...">
                <button type="button" 
                        @click="addTag()"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            
            <p class="text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Etiketleri virgülle ayırın veya Enter tuşuna basın.
            </p>
        </div>
        <input type="hidden" wire:model="tagsInput" id="tagsInput">
    </div>
</div>

