// Yazarlar Modülü JavaScript
// ========================

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';
import { showNotification as sharedShowNotification } from '@/js/ui/notifications';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Yazarlar Tablo Bileşeni - Factory pattern
    function authorsTableData() {
        return {
            selectedAuthors: [],
            selectAll: false,

            toggleSelectAll() {
                this.selectAll = !this.selectAll;
                this.selectedAuthors = this.selectAll ? this.getAllAuthorIds() : [];
            },

            toggleAuthor(authorId) {
                const index = this.selectedAuthors.indexOf(authorId);
                if (index > -1) {
                    this.selectedAuthors.splice(index, 1);
                } else {
                    this.selectedAuthors.push(authorId);
                }
                this.updateSelectAllState();
            },

            updateSelectAllState() {
                const allIds = this.getAllAuthorIds();
                this.selectAll = allIds.length > 0 && allIds.every(id => this.selectedAuthors.includes(id));
            },

            getAllAuthorIds() {
                const root = this.$root || document;
                return Array.from(root.querySelectorAll('[data-author-id]'))
                    .map(el => el.dataset.authorId)
                    .filter(Boolean);
            },

            clearSelection() {
                this.selectedAuthors = [];
                this.selectAll = false;
            }
        };
    }

    Alpine.data('authorsTable', authorsTableData);

    // Global fonksiyon wrapper - x-data="authorsTable" ve x-data="authorsTable()" için uyumluluk
    if (typeof window !== 'undefined' && !window.authorsTable) {
        window.authorsTable = function () {
            return authorsTableData();
        };
    }

    // Yazar Form Bileşeni
    Alpine.data('authorForm', () => ({
        isSubmitting: false,
        showImagePreview: false,

        init() {
            this.setupFormValidation();
            this.setupImagePreview();
        },

        setupFormValidation() {
            // Form alanları için gerçek zamanlı doğrulama
            this.$watch('$wire.title', (value) => {
                if (value) {
                    this.validateTitle(value);
                }
            });
        },

        setupImagePreview() {
            this.$watch('$wire.image', (file) => {
                if (file) {
                    this.showImagePreview = true;
                }
            });
        },


        validateTitle(title) {
            if (!title || title.trim().length < 2) {
                this.showFieldError('title', 'Başlık en az 2 karakter olmalıdır');
                return false;
            }
            this.clearFieldError('title');
            return true;
        },

        showFieldError(fieldName, message) {
            const field = document.getElementById(fieldName);
            if (field) {
                field.classList.add('is-invalid');
                this.showErrorMessage(field, message);
            }
        },

        clearFieldError(fieldName) {
            const field = document.getElementById(fieldName);
            if (field) {
                field.classList.remove('is-invalid');
                this.hideErrorMessage(field);
            }
        },

        showErrorMessage(field, message) {
            this.hideErrorMessage(field);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback d-block';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        },

        hideErrorMessage(field) {
            const existingError = field.parentNode.querySelector('.invalid-feedback');
            if (existingError) {
                existingError.remove();
            }
        },

        async submitForm() {
            this.isSubmitting = true;
            try {
                await this.$wire.save();
            } catch (error) {
                console.error('Form submission error:', error);
            } finally {
                this.isSubmitting = false;
            }
        }
    }));
}, { once: true });

// Module initialization function
function initAuthorsModule() {
    // Tooltip initialization
    const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipElements.length === 0) return;
    if (typeof bootstrap !== 'undefined') {
        tooltipElements.forEach(el => {
            if (!el.dataset.tooltipInit) {
                el.dataset.tooltipInit = '1';
                new bootstrap.Tooltip(el);
            }
        });
    }
}

// Register module with central lifecycle manager
registerModuleInit('authors', initAuthorsModule);

// Utility Functions
function showNotification(message, type = 'success') {
    // Use shared Tailwind toast notification
    sharedShowNotification(message, type);
}

function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Export functions for global access
const AuthorsModule = {
    showNotification,
    confirmAction
};
