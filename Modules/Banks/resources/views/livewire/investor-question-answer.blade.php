<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-reply text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">
                        @if($question->status === 'answered')
                            Cevabı Düzenle
                        @else
                            Soru Cevapla
                        @endif
                    </h2>
                    <p class="text-gray-600">{{ $question->title }}</p>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save" class="p-6">
            <!-- Soru Bilgileri -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Soru Bilgileri</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Başlık</label>
                        <p class="text-sm text-gray-900">{{ $question->title }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">İsim</label>
                        <p class="text-sm text-gray-900">{{ $question->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                        <p class="text-sm text-gray-900">{{ $question->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hisse Senedi</label>
                        <p class="text-sm text-gray-900">{{ $question->stock ?? '-' }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Soru</label>
                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $question->question }}</p>
                    </div>
                </div>
            </div>

            <!-- Cevap Formu -->
            <div class="space-y-6">
                <!-- Cevap Başlığı -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cevap Başlığı</label>
                    <input wire:model="answer_title" 
                           type="text" 
                           placeholder="Cevap için başlık (opsiyonel)"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('answer_title') border-red-500 @enderror">
                    @error('answer_title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cevap -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cevap <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="answer" 
                              rows="8"
                              placeholder="Soruyu detaylı bir şekilde cevaplayın..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('answer') border-red-500 @enderror"></textarea>
                    @error('answer')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                <div class="flex space-x-4">
                    <button type="button" 
                            wire:click="reject"
                            onclick="return confirm('Bu soruyu reddetmek istediğinizden emin misiniz?')"
                            class="px-6 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Reddet
                    </button>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('banks.investor-questions.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        İptal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>
                        @if($question->status === 'answered')
                            Güncelle
                        @else
                            Cevapla
                        @endif
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
