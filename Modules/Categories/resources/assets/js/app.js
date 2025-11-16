// Kategoriler Modülü JavaScript
// ============================

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';
import { showNotification } from '@/js/ui/notifications';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Kategoriler Tablo Bileşeni - Factory pattern
    function categoriesTableData() {
        return {
            init() {}
        };
    }

    Alpine.data('categoriesTable', categoriesTableData);

    // Global fonksiyon wrapper - x-data="categoriesTable" ve x-data="categoriesTable()" için uyumluluk
    if (typeof window !== 'undefined' && !window.categoriesTable) {
        window.categoriesTable = function () {
            return categoriesTableData();
        };
    }

    // Kategori Form Bileşeni
    Alpine.data('categoryForm', () => ({
        init() {}
    }));
}, { once: true });

// Module initialization function
function initCategoriesModule() {
    // Module-specific initialization
}

// Yardımcı Fonksiyonlar
const CategoriesModule = {
    // Bildirim göster - uses shared notification utility
    showNotification,

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
            showNotification('Panoya kopyalandı!', 'success');
        } catch (err) {
            console.error('Kopyalama hatası:', err);
            showNotification('Kopyalama başarısız!', 'error');
        }
    }
};

// Register module with central lifecycle manager
registerModuleInit('categories', initCategoriesModule);

