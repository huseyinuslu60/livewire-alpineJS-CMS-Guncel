<div class="w-full px-4 sm:px-6 lg:px-8 py-8" x-data="commentsManagement()">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center mr-6">
                    <i class="fas fa-comments text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-[var(--text)]">Yorum Yönetimi</h1>
                    <p class="text-[var(--text-muted)] text-lg mt-2">Sistemdeki yorumları yönetin ve onaylayın</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="bg-[var(--surface)] rounded-xl p-4 shadow-sm border border-[var(--border-subtle)]">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-[var(--text)]">Sistem Aktif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Toplam Yorum -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border-2 border-transparent hover:border-blue-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold mb-2">{{ $stats['total'] }}</h3>
                    <p class="text-blue-100 text-sm font-medium">Toplam Yorum</p>
                </div>
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <i class="fas fa-comments text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Onaylanan -->
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border-2 border-transparent hover:border-green-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold mb-2">{{ $stats['approved'] }}</h3>
                    <p class="text-green-100 text-sm font-medium">Onaylanan</p>
                </div>
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Bekleyen -->
        <div class="bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border-2 border-transparent hover:border-yellow-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold mb-2">{{ $stats['pending'] }}</h3>
                    <p class="text-yellow-100 text-sm font-medium">Bekleyen</p>
                </div>
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Reddedilen -->
        <div class="bg-gradient-to-br from-red-500 to-pink-500 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border-2 border-transparent hover:border-red-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold mb-2">{{ $stats['rejected'] }}</h3>
                    <p class="text-red-100 text-sm font-medium">Reddedilen</p>
                </div>
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-[var(--surface)] rounded-xl shadow-sm border border-[var(--border-subtle)] p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-[var(--text-muted)]"></i>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       class="w-full pl-10 pr-3 py-2 bg-[var(--input-bg)] border border-[var(--border-subtle)] rounded-lg focus:ring-2 focus:ring-[var(--accent)] focus:border-[var(--accent)] text-[var(--text)] placeholder:text-[var(--text-muted)] text-sm" 
                       placeholder="Yorum ara...">
            </div>
            <div>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 bg-[var(--input-bg)] border border-[var(--border-subtle)] rounded-lg focus:ring-2 focus:ring-[var(--accent)] focus:border-[var(--accent)] text-[var(--text)] text-sm">
                    <option value="">Tüm Durumlar</option>
                    <option value="pending">Bekleyen</option>
                    <option value="approved">Onaylanan</option>
                    <option value="rejected">Reddedilen</option>
                </select>
            </div>
            <div>
                <select wire:model.live="perPage" class="w-full px-3 py-2 bg-[var(--input-bg)] border border-[var(--border-subtle)] rounded-lg focus:ring-2 focus:ring-[var(--accent)] focus:border-[var(--accent)] text-[var(--text)] text-sm">
                    <option value="10">10 / Sayfa</option>
                    <option value="25">25 / Sayfa</option>
                    <option value="50">50 / Sayfa</option>
                    <option value="100">100 / Sayfa</option>
                </select>
            </div>
            <div class="flex space-x-3">
                <button wire:click="clearFilters" class="flex-1 px-4 py-2 bg-[var(--surface-alt)] hover:bg-[var(--bg-muted)] text-[var(--text)] rounded-lg text-sm font-medium transition-colors duration-150 text-center">
                    <i class="fas fa-times mr-2"></i>Temizle
                </button>
            </div>
        </div>

        <!-- Comments List -->
        <div class="space-y-4">
            @forelse($comments as $comment)
                <div wire:key="comment-{{ $comment->comment_id }}" class="bg-[var(--surface)] rounded-lg border border-[var(--border-subtle)] overflow-hidden
                    @if($comment->status === 'pending') bg-yellow-50/30 border-yellow-200 @endif
                    @if($comment->status === 'rejected') bg-red-50/30 border-red-200 @endif
                    @if($comment->status === 'approved') bg-green-50/30 border-green-200 @endif">
                    
                    <!-- Comment Header -->
                    <div class="px-6 py-4 border-b border-[var(--border-subtle)]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-medium text-sm">
                                            {{ strtoupper(substr($comment->name, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h4 class="text-sm font-medium text-[var(--text)]">{{ $comment->name }}</h4>
                                        <span class="text-xs text-[var(--text-muted)]">#{{ $comment->comment_id }}</span>
                                    </div>
                                    <div class="flex items-center space-x-4 text-xs text-[var(--text-muted)]">
                                        <span>{{ $comment->ip_address }}</span>
                                        <span>{{ $comment->created_at->format('d.m.Y H:i:s') }}</span>
                                        @if($comment->status === 'pending')
                                            <span class="text-yellow-600 font-medium">- Onay Bekliyor</span>
                                        @elseif($comment->status === 'approved')
                                            <span class="text-green-600 font-medium">- Onaylandı</span>
                                        @else
                                            <span class="text-red-600 font-medium">- Reddedilmiş</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="flex items-center space-x-2">
                                @if($comment->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>Bekleyen
                                    </span>
                                @elseif($comment->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Onaylandı
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>Reddedilmiş
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Comment Content -->
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-[var(--text)] mb-2">
                                    Yorum Metni
                                </label>
                                <textarea 
                                    wire:model.live="editedCommentTexts.{{ $comment->comment_id }}"
                                    rows="2" 
                                    class="w-full px-3 py-2 bg-[var(--input-bg)] border border-[var(--border-subtle)] rounded-lg focus:ring-2 focus:ring-[var(--accent)] focus:border-[var(--accent)] text-[var(--text)] placeholder:text-[var(--text-muted)] text-sm"
                                    placeholder="Yorum metnini düzenleyin...">{{ $editedCommentTexts[$comment->comment_id] ?? $comment->comment_text }}</textarea>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                @can('approve comments')
                                <button 
                                    wire:click="saveAndApprove({{ $comment->comment_id }})"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                    <i class="fas fa-save mr-2"></i>
                                    Kaydet & Onayla
                                </button>
                                @endcan
                                
                                @if($comment->status === 'pending' || $comment->status === 'approved')
                                    @can('reject comments')
                                    <button 
                                        wire:click="reject({{ $comment->comment_id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                        <i class="fas fa-times mr-1"></i>
                                        Reddet
                                    </button>
                                    @endcan
                                @endif
                                
                                @can('delete comments')
                                <button 
                                    wire:click="deleteComment({{ $comment->comment_id }})"
                                    wire:confirm="Bu yorumu silmek istediğinizden emin misiniz?"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                    <i class="fas fa-trash mr-1"></i>
                                    Sil
                                </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-comment-slash text-4xl text-[var(--text-muted)] mb-4"></i>
                        <h3 class="text-lg font-medium text-[var(--text)] mb-2">Henüz yorum bulunmuyor</h3>
                        <p class="text-[var(--text-muted)]">Sistemde henüz hiç yorum bulunmuyor.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($comments->hasPages())
        <div class="bg-[var(--surface)] rounded-lg border border-[var(--border-subtle)] px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="text-sm text-[var(--text)]">
                    Toplam <span class="font-medium">{{ $comments->total() }}</span> yorumdan 
                    <span class="font-medium">{{ $comments->firstItem() ?? 0 }}</span>-<span class="font-medium">{{ $comments->lastItem() ?? 0 }}</span> arası gösteriliyor
                </div>
                <div class="flex items-center space-x-2">
                    {{ $comments->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-4 right-4 z-50" x-data="{ show: true }" x-show="show" x-transition>
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
                    <button @click="show = false" class="ml-4 text-green-400 hover:text-green-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Comments modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Comments/resources/assets/sass/app.scss', 'Modules/Comments/resources/assets/js/app.js'])
</div>
