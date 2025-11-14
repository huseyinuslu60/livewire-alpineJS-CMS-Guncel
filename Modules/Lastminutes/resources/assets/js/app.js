// Son Dakika Modülü JavaScript
// =============================

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Son Dakika Form Bileşeni
    Alpine.data('lastminuteForm', () => ({
        showSuccess: true,
        showError: true,
        isSubmitting: false,

        init() {
            // Son Dakika Form: Alpine bileşeni başlatıldı
        },

        async submitForm() {
            this.isSubmitting = true;

            try {
                // Form gönderimi Livewire tarafından işlenecek
                await this.$wire.save();
            } catch (error) {
                console.error('Form submission error:', error);
            } finally {
                this.isSubmitting = false;
            }
        }
    }));

    // Son Dakikalar Tablo Bileşeni
    Alpine.data('lastminutesTable', () => ({
        showSuccess: true,
        showError: true,
        selectedLastminutes: [],

        init() {
            // Son Dakikalar Tablo: Alpine bileşeni başlatıldı
        },

        toggleLastminute(lastminuteId) {
            if (this.selectedLastminutes.includes(lastminuteId)) {
                this.selectedLastminutes = this.selectedLastminutes.filter(id => id !== lastminuteId);
            } else {
                this.selectedLastminutes.push(lastminuteId);
            }
        },

        selectAll() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="lastminute_ids[]"]');
            this.selectedLastminutes = Array.from(checkboxes).map(cb => cb.value);
        },

        deselectAll() {
            this.selectedLastminutes = [];
        }
    }));
}, { once: true });

// Module initialization function
function initLastminutesModule() {
    // Module-specific initialization (non-Alpine code)
}

// Register module with central lifecycle manager
registerModuleInit('lastminutes', initLastminutesModule);

// Yardımcı Fonksiyonlar
const LastminutesModule = {
    // Bildirim göster
    showNotification(message, type = 'success') {
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

    // Tarihi formatla
    formatDate(date) {
        return new Date(date).toLocaleDateString('tr-TR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};



