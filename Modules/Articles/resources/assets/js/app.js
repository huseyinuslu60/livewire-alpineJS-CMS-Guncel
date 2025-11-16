// Makaleler Modülü - Alpine.js Bileşenleri

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Makaleler Tablo Bileşeni - Factory pattern
    function articlesTableData() {
        return {
            showSuccess: true,
            showError: true,

            init() {},

            // Yardımcı fonksiyonlar
            formatDate(date) {
                return new Date(date).toLocaleDateString('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
            },

            formatDateTime(date) {
                return new Date(date).toLocaleString('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            getStatusColor(status) {
                switch(status) {
                    case 'published':
                        return 'bg-green-100 text-green-800';
                    case 'draft':
                        return 'bg-yellow-100 text-yellow-800';
                    case 'pending':
                        return 'bg-blue-100 text-blue-800';
                    default:
                        return 'bg-gray-100 text-gray-800';
                }
            },

            getStatusIcon(status) {
                switch(status) {
                    case 'published':
                        return 'fas fa-check-circle';
                    case 'draft':
                        return 'fas fa-edit';
                    case 'pending':
                        return 'fas fa-hourglass-half';
                    default:
                        return 'fas fa-question';
                }
            },

            getStatusText(status) {
                switch(status) {
                    case 'published':
                        return 'Yayınlanmış';
                    case 'draft':
                        return 'Taslak';
                    case 'pending':
                        return 'Beklemede';
                    default:
                        return 'Bilinmeyen';
                }
            }
        };
    }

    Alpine.data('articlesTable', articlesTableData);

    // Global fonksiyon wrapper - x-data="articlesTable" ve x-data="articlesTable()" için uyumluluk
    if (typeof window !== 'undefined' && !window.articlesTable) {
        window.articlesTable = function () {
            return articlesTableData();
        };
    }

    // Makale Form Bileşeni
    Alpine.data('articleForm', () => ({
        isSubmitting: false,
        showPreview: false,
        previewContent: '',

        init() {},

        togglePreview() {
            this.showPreview = !this.showPreview;
            if (this.showPreview) {
                this.previewContent = this.$refs.content.value;
            }
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
        },

        validateForm() {
            const title = this.$refs.title?.value;
            const content = this.$refs.content?.value;

            if (!title || title.trim().length < 3) {
                this.showError('Başlık en az 3 karakter olmalıdır');
                return false;
            }

            if (!content || content.trim().length < 10) {
                this.showError('İçerik en az 10 karakter olmalıdır');
                return false;
            }

            return true;
        },

        showError(message) {
            console.error(message);
        }
    }));

    // Makale Arama Bileşeni
    Alpine.data('articles.search', () => ({
        searchQuery: '',
        searchResults: [],
        isSearching: false,

        init() {},

        async performSearch() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            this.isSearching = true;

            try {
                // Search implementation
                const response = await fetch(`/api/articles/search?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                this.searchResults = data.results || [];
            } catch (error) {
                console.error('Search error:', error);
                this.searchResults = [];
            } finally {
                this.isSearching = false;
            }
        },

        clearSearch() {
            this.searchQuery = '';
            this.searchResults = [];
        }
    }));

    // Makale Düzenleyici Bileşeni
    Alpine.data('articles.editor', () => ({
        content: '',
        isFullscreen: false,
        wordCount: 0,
        charCount: 0,

        init() {},

        updateCount() {
            const text = this.content.replace(/<[^>]*>/g, ''); // HTML etiketlerini kaldır
            this.wordCount = text.split(/\s+/).filter(word => word.length > 0).length;
            this.charCount = text.length;
        },

        toggleFullscreen() {
            this.isFullscreen = !this.isFullscreen;
        },

        insertText(text) {
            const textarea = this.$refs.content;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const before = this.content.substring(0, start);
            const after = this.content.substring(end);

            this.content = before + text + after;

            // Set cursor position
            setTimeout(() => {
                textarea.selectionStart = textarea.selectionEnd = start + text.length;
                textarea.focus();
            }, 0);
        }
    }));
}, { once: true });

// Module initialization function
function initArticlesModule() {
    // Module-specific initialization (non-Alpine code)
}

// Register module with central lifecycle manager
registerModuleInit('articles', initArticlesModule);
