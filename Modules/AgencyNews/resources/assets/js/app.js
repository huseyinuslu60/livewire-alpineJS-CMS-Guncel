// Ajans Haberleri Alpine.js Bileşenleri

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    Alpine.data('agencyNewsTable', () => ({
        showSuccess: true,
        showError: true,
        
        init() {
            this.$wire.on('confirm-delete-agency-news', (data) => {
                if (confirm(data.message)) {
                    this.$wire.deleteAgencyNews(data.agencyNewsId);
                }
            });
        }
    }));
    
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
