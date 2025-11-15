// Global Toast Store - Merkezi toast/notification yönetimi
// Tüm modüllerde tek bir toast sistemi kullanılır

document.addEventListener('alpine:init', () => {
    Alpine.store('globalToast', {
        toasts: [],

        defaultDuration: 5000, // 5 saniye
        maxToasts: 5, // Maksimum toast sayısı (queue limit)

        /**
         * Genel show helper
         */
        show(type = 'info', message = '', options = {}) {
            if (!message) return;

            // Queue limit kontrolü - FIFO drop
            if (this.toasts.length >= this.maxToasts) {
                const oldestToast = this.toasts.shift();
                if (oldestToast.timeoutId) {
                    clearTimeout(oldestToast.timeoutId);
                }
            }

            const id = Date.now() + Math.random().toString(16).slice(2);
            const duration = options.duration ?? this.defaultDuration;
            const startTime = Date.now();

            const toast = {
                id,
                type,
                message,
                timeoutId: null,
                paused: false,
                startTime,
                remainingDuration: duration,
            };

            this.toasts.push(toast);

            // Otomatik kapama
            toast.timeoutId = window.setTimeout(() => {
                this.remove(id);
            }, duration);
        },

        pause(id) {
            const toast = this.toasts.find(t => t.id === id);
            if (!toast || toast.paused) return;

            // Kalan süreyi hesapla
            const elapsed = Date.now() - toast.startTime;
            toast.remainingDuration = toast.remainingDuration - elapsed;

            if (toast.timeoutId) {
                clearTimeout(toast.timeoutId);
            }

            toast.paused = true;
        },

        resume(id) {
            const toast = this.toasts.find(t => t.id === id);
            if (!toast || !toast.paused) return;

            // Kalan süre ile devam et
            toast.startTime = Date.now();
            toast.timeoutId = window.setTimeout(() => {
                this.remove(id);
            }, toast.remainingDuration);

            toast.paused = false;
        },

        remove(id) {
            const idx = this.toasts.findIndex(t => t.id === id);
            if (idx === -1) return;

            const toast = this.toasts[idx];
            if (toast.timeoutId) {
                clearTimeout(toast.timeoutId);
            }

            this.toasts.splice(idx, 1);
        },

        // Kısayollar
        success(message, options = {}) {
            this.show('success', message, options);
        },

        error(message, options = {}) {
            this.show('error', message, options);
        },

        warning(message, options = {}) {
            this.show('warning', message, options);
        },

        warn(message, options = {}) {
            this.show('warning', message, options);
        },

        info(message, options = {}) {
            this.show('info', message, options);
        },
    });

    // window.toast shortcut'ları
    if (typeof window !== 'undefined') {
        window.toast = window.toast || {};
        window.toast.success = (message, options = {}) =>
            Alpine.store('globalToast').success(message, options);
        window.toast.error = (message, options = {}) =>
            Alpine.store('globalToast').error(message, options);
        window.toast.warning = (message, options = {}) =>
            Alpine.store('globalToast').warning(message, options);
        window.toast.warn = (message, options = {}) =>
            Alpine.store('globalToast').warn(message, options);
        window.toast.info = (message, options = {}) =>
            Alpine.store('globalToast').info(message, options);
    }
});

