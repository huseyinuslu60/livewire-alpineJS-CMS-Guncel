// Loglar Modülü JavaScript
// Alpine.js + Livewire + Tailwind CSS uyumlu

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';
import { showNotification as sharedShowNotification } from '@/js/ui/notifications';

// Loglar modülü için Alpine.js bileşenleri - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Loglar Tablo Bileşeni - Factory pattern
    function logsTableData() {
        return {
            init() {}
        };
    }

    Alpine.data('logsTable', logsTableData);

    // Global fonksiyon wrapper - x-data="logsTable" ve x-data="logsTable()" için uyumluluk
    if (typeof window !== 'undefined' && !window.logsTable) {
        window.logsTable = function () {
            return logsTableData();
        };
    }

    // Loglar yönetim bileşeni
    Alpine.data('logs.management', () => ({
            // Durum
            isLoading: false,
        selectedLogs: [],
            showBulkActions: false,

            // Metodlar
        init() {},

            // Toplu işlemler
        toggleSelectAll() {
                if (this.selectedLogs.length === this.getTotalLogs()) {
                    this.selectedLogs = [];
                } else {
                    this.selectedLogs = this.getAllLogIds();
                }
                this.updateBulkActionsVisibility();
            },

            toggleLogSelection(logId) {
                const index = this.selectedLogs.indexOf(logId);
                if (index > -1) {
                    this.selectedLogs.splice(index, 1);
                } else {
                    this.selectedLogs.push(logId);
                }
                this.updateBulkActionsVisibility();
            },

            updateBulkActionsVisibility() {
                this.showBulkActions = this.selectedLogs.length > 0;
            },

            // Yardımcı fonksiyonlar
            getTotalLogs() {
                // Bu sunucu tarafından doldurulacak
                return 0;
            },

            getAllLogIds() {
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

        // Flash message component
        Alpine.data('logs.flashMessage', () => ({
            show: true,

        init() {
                setTimeout(() => {
                    this.close();
                }, 5000);
            },

            close() {
                this.show = false;
        }
    }));

        // Loading spinner component
        Alpine.data('logs.loadingSpinner', () => ({
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
function initLogsModule() {
    // Logs module initialization
    const logsContainer = document.querySelector('[data-logs-container]');
    if (!logsContainer && !document.querySelector('[wire\\:id]')) return;
    if (logsModule && typeof logsModule.init === 'function') {
        logsModule.init();
    }
}

// Register module with central lifecycle manager
registerModuleInit('logs', initLogsModule);

// Livewire entegrasyonu - Livewire 3 standartları
document.addEventListener('livewire:init', () => {
    window.addEventListener('show-error', (e) => {
        const message = e.detail?.message ?? e.detail ?? 'Bilinmeyen hata';
        logsModule.showNotification('Hata: ' + message, 'error');
    });

    window.addEventListener('show-success', (e) => {
        const message = e.detail?.message ?? e.detail ?? 'İşlem başarılı';
        logsModule.showNotification('Başarılı: ' + message, 'success');
    });

    // CSV indirme
    window.addEventListener('download-csv', (e) => {
        try {
            const { data, filename = 'logs.csv' } = e.detail || {};
            // Excel uyumluluğu için BOM ekle
            const BOM = '\uFEFF';
            const blob = new Blob([BOM + (data ?? '')], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
            logsModule.showNotification('Log kayıtları dışa aktarıldı: ' + filename, 'success');
        } catch (error) {
            logsModule.showNotification('Dosya indirilirken hata: ' + error.message, 'error');
        }
    });
});

// Şablonlarda kullanım için global fonksiyonlar
const logsModule = {
    init() {},

    // Yükleme durumunu göster
    showLoading() {
        document.body.classList.add('loading');
    },

    // Yükleme durumunu gizle
    hideLoading() {
        document.body.classList.remove('loading');
    },

    // Bildirim göster - shared helper kullanıyor
    showNotification(message, type = 'success') {
        sharedShowNotification(message, type);
    },

    // Tarihi formatla
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('tr-TR', {
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
