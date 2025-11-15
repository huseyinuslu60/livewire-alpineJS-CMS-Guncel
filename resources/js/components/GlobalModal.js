// Global Modal Store - Merkezi modal yönetimi
// Tüm modüllerde tek bir modal sistemi kullanılır

document.addEventListener('alpine:init', () => {
    Alpine.store('globalModal', {
        open: false,
        title: '',
        message: '',
        confirmLabel: 'Onayla',
        cancelLabel: 'Vazgeç',
        onConfirm: null,
        previousFocus: null, // Focus trap için

        show({ title, message, confirmLabel = 'Onayla', cancelLabel = 'Vazgeç', onConfirm }) {
            // Önceki focus'u kaydet
            this.previousFocus = document.activeElement;
            
            this.title = title;
            this.message = message;
            this.confirmLabel = confirmLabel;
            this.cancelLabel = cancelLabel;
            this.onConfirm = onConfirm;
            this.open = true;

            // Modal açıldığında ilk focusable elemente focus ver
            // Alpine reactive update'ini beklemek için setTimeout kullan
            setTimeout(() => {
                const modal = document.querySelector('[role="dialog"][aria-modal="true"]');
                if (modal) {
                    const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                    if (firstFocusable) {
                        firstFocusable.focus();
                    }
                }
            }, 50);
        },

        close() {
            this.open = false;
            
            // Önceki focus'a geri dön
            if (this.previousFocus && typeof this.previousFocus.focus === 'function') {
                setTimeout(() => {
                    this.previousFocus.focus();
                }, 100);
            }
            
            // Cleanup
            this.title = '';
            this.message = '';
            this.confirmLabel = 'Onayla';
            this.cancelLabel = 'Vazgeç';
            this.onConfirm = null;
            this.previousFocus = null;
        },

        confirm() {
            if (typeof this.onConfirm === 'function') {
                this.onConfirm();
            }
            this.close();
        }
    });
});

