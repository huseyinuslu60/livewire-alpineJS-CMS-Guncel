// Ajans Haberleri Alpine.js Bileşenleri

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // AgencyNews Tablo Bileşeni - Factory pattern
    function agencyNewsTableData() {
        return {
            showSuccess: true,
            showError: true,

            init() {
                // Livewire event listener - silme onayı için
                this.$wire.on('confirm-delete-agency-news', (data) => {
                    // Livewire 3'te $wire.on ile gelen data direkt olarak dispatch edilen array'dir
                    // Bazen data[0] şeklinde de gelebilir, her iki durumu da kontrol et
                    const eventData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                    const message = eventData?.message || 'Bu ajans haberini silmek istediğinizden emin misiniz?';
                    const agencyNewsId = eventData?.agencyNewsId;

                    if (agencyNewsId && confirm(message)) {
                        // Livewire 3'te method çağrısı için $wire.call() kullan
                        this.$wire.call('deleteAgencyNews', agencyNewsId);
                    }
                });
            }
        };
    }

    Alpine.data('agencyNewsTable', agencyNewsTableData);

    // Global fonksiyon wrapper - x-data="agencyNewsTable" ve x-data="agencyNewsTable()" için uyumluluk
    if (typeof window !== 'undefined' && !window.agencyNewsTable) {
        window.agencyNewsTable = function () {
            return agencyNewsTableData();
        };
    }
}, { once: true });

// Module initialization function
function initAgencyNewsModule() {
    // Module-specific initialization (non-Alpine code)
}

// Register module with central lifecycle manager
registerModuleInit('agencynews', initAgencyNewsModule);

// Global fonksiyonlar
const agencyNewsHelpers = {
    formatDate: (date) => {
        return new Date(date).toLocaleDateString('tr-TR');
    },

    formatDateTime: (date) => {
        return new Date(date).toLocaleString('tr-TR');
    },

    truncateText: (text, limit = 100) => {
        if (text.length <= limit) return text;
        return text.substring(0, limit) + '...';
    },

    getStatusBadge: (status) => {
        const badges = {
            'published': 'bg-green-100 text-green-800',
            'draft': 'bg-yellow-100 text-yellow-800',
            'scheduled': 'bg-blue-100 text-blue-800',
            'archived': 'bg-gray-100 text-gray-800'
        };
        return badges[status] || 'bg-gray-100 text-gray-800';
    }
};
