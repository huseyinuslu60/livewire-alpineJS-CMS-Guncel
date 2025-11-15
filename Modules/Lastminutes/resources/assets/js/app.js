// Son Dakika Modülü JavaScript
// =============================

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';
import { showNotification } from '@/js/ui/notifications';

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

    // Son Dakikalar Tablo Bileşeni - Factory pattern
    function lastminutesTableData() {
        return {
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
                const root = this.$root || document;
                const checkboxes = root.querySelectorAll('input[type="checkbox"][name="lastminute_ids[]"]');
                this.selectedLastminutes = Array.from(checkboxes).map(cb => cb.value);
            },

            deselectAll() {
                this.selectedLastminutes = [];
            }
        };
    }

    Alpine.data('lastminutesTable', lastminutesTableData);

    // Global fonksiyon wrapper - x-data="lastminutesTable" ve x-data="lastminutesTable()" için uyumluluk
    if (typeof window !== 'undefined' && !window.lastminutesTable) {
        window.lastminutesTable = function () {
            return lastminutesTableData();
        };
    }
}, { once: true });

// Module initialization function
function initLastminutesModule() {
    // Module-specific initialization (non-Alpine code)
}

// Register module with central lifecycle manager
registerModuleInit('lastminutes', initLastminutesModule);

// Yardımcı Fonksiyonlar
const LastminutesModule = {
    // Bildirim göster - uses shared notification utility
    showNotification,

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



