// Kategoriler Modülü JavaScript
// ============================

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Kategoriler Tablo Bileşeni
    Alpine.data('categoriesTable', () => ({
        init() {
            // Kategoriler Tablo: Alpine bileşeni başlatıldı
        }
    }));

    // Kategori Form Bileşeni
    Alpine.data('categoryForm', () => ({
        init() {
            // Kategori Form: Alpine bileşeni başlatıldı
        }
    }));
}, { once: true });

// Module initialization function
function initCategoriesModule() {
    // Module-specific initialization (non-Alpine code)
}

// Yardımcı Fonksiyonlar
const CategoriesModule = {
    // Bildirim göster
    showNotification(message, type = 'success') {
        // Bildirim elementi oluştur
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
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

    // Silmeyi onayla
    confirmDelete(message = 'Bu işlemi gerçekleştirmek istediğinizden emin misiniz?') {
        return confirm(message);
    },

    // Tarihi formatla
    formatDate(date) {
        return new Date(date).toLocaleDateString('tr-TR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    },

    // Panoya kopyala
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.showNotification('Panoya kopyalandı!', 'success');
        } catch (err) {
            console.error('Kopyalama hatası:', err);
            this.showNotification('Kopyalama başarısız!', 'error');
        }
    }
};

// Register module with central lifecycle manager
registerModuleInit('categories', initCategoriesModule);

