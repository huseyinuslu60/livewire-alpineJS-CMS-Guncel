// Yorumlar Modülü JavaScript
// Livewire, Alpine.js ve Tailwind CSS uyumlu

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// Yorumlar modülü için Alpine.js bileşenleri - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    Alpine.data('commentsManagement', () => ({
            // Durum
            selectedComments: [],
            showBulkActions: false,
            isLoading: false,

            // Metodlar
            init() {},

            // Toplu işlemler
            toggleSelectAll() {
                if (this.selectedComments.length === this.getTotalComments()) {
                    this.selectedComments = [];
                } else {
                    this.selectedComments = this.getAllCommentIds();
                }
                this.updateBulkActionsVisibility();
            },

            toggleCommentSelection(commentId) {
                const index = this.selectedComments.indexOf(commentId);
                if (index > -1) {
                    this.selectedComments.splice(index, 1);
                } else {
                    this.selectedComments.push(commentId);
                }
                this.updateBulkActionsVisibility();
            },

            updateBulkActionsVisibility() {
                this.showBulkActions = this.selectedComments.length > 0;
            },

            bulkApprove() {
                if (this.selectedComments.length === 0) return;
                
                this.isLoading = true;
                // Implement bulk approve logic
                this.selectedComments = [];
                this.updateBulkActionsVisibility();
                this.isLoading = false;
            },

            bulkReject() {
                if (this.selectedComments.length === 0) return;
                
                this.isLoading = true;
                // Implement bulk reject logic
                this.selectedComments = [];
                this.updateBulkActionsVisibility();
                this.isLoading = false;
            },

            bulkDelete() {
                if (this.selectedComments.length === 0) return;
                
                if (confirm('Seçili yorumları silmek istediğinizden emin misiniz?')) {
                    this.isLoading = true;
                    // Implement bulk delete logic
                    this.selectedComments = [];
                    this.updateBulkActionsVisibility();
                    this.isLoading = false;
                }
            },

            // Yardımcı fonksiyonlar
            getTotalComments() {
                // Bu sunucu tarafından doldurulacak
                return 0;
            },

            getAllCommentIds() {
                // Bu sunucu tarafından doldurulacak
                return [];
            },

            formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            truncateText(text, length = 100) {
                return text.length > length ? text.substring(0, length) + '...' : text;
            }
        }));

        // Flash mesaj bileşeni
        Alpine.data('commentsFlashMessage', () => ({
            show: true,
            
            init() {
                // 5 saniye sonra otomatik gizle
                setTimeout(() => {
                    this.close();
                }, 5000);
            },
            
            close() {
                this.show = false;
            }
        }));

        // Yükleme spinner bileşeni
        Alpine.data('commentsLoadingSpinner', () => ({
            isLoading: false,
            
            start() {
                this.isLoading = true;
            },
            
            stop() {
                this.isLoading = false;
            }
        }));
}, { once: true });

// Module initialization function
function initCommentsModule() {
    // Comments module initialization
    const commentsContainer = document.querySelector('[data-comments-container]');
    if (!commentsContainer && !document.querySelector('[wire\\:id]')) return;
    if (commentsModule && typeof commentsModule.init === 'function') {
        commentsModule.init();
    }
}

// Register module with central lifecycle manager
registerModuleInit('comments', initCommentsModule);

// Livewire entegrasyonu
document.addEventListener('livewire:init', () => {
    Livewire.on('comment-updated', (commentId) => {
        const commentElement = document.querySelector(`[wire\\:key="comment-${commentId}"]`);
        
        if (commentElement) {
            commentElement.style.opacity = '0.5';
            commentElement.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                commentElement.style.opacity = '1';
            }, 300);
        }
    });

    Livewire.on('show-success', (message) => {
        commentsModule.showNotification(message, 'success');
    });

    Livewire.on('show-error', (message) => {
        commentsModule.showNotification(message, 'error');
    });
});

// Şablonlarda kullanım için global fonksiyonlar
const commentsModule = {
    init() {},

    // Yükleme durumunu göster
    showLoading() {
        document.body.classList.add('loading');
    },

    // Yükleme durumunu gizle
    hideLoading() {
        document.body.classList.remove('loading');
    },

    // Bildirim göster
    showNotification(message, type = 'success') {
        // Bildirim elementi oluştur
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-blue-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // 5 saniye sonra otomatik kaldır
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    },

    // Tarihi formatla
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('tr-TR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Metni kısalt
    truncateText(text, length = 100) {
        return text.length > length ? text.substring(0, length) + '...' : text;
    },

    // İşlemi onayla
    confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }
};


