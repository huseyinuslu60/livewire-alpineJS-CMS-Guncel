// Kullanıcı Modülü JavaScript - Alpine.js + Tailwind CSS
// ===================================================

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';
import { showNotification } from '@/js/ui/notifications';

// Kullanıcı Modülü için Alpine.js Bileşenleri - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Kullanıcı Form Bileşeni
    Alpine.data('usersForm', () => ({
        showPassword: false,
        passwordStrength: 0,
        passwordRequirements: {
            length: false,
            uppercase: false,
            lowercase: false,
            number: false,
            special: false
        },

        togglePassword() {
            this.showPassword = !this.showPassword;
        },

        checkPasswordStrength(password) {
            this.passwordStrength = 0;
            this.passwordRequirements.length = password.length >= 8;
            this.passwordRequirements.uppercase = /[A-Z]/.test(password);
            this.passwordRequirements.lowercase = /[a-z]/.test(password);
            this.passwordRequirements.number = /\d/.test(password);
            this.passwordRequirements.special = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            const requirements = Object.values(this.passwordRequirements);
            this.passwordStrength = requirements.filter(req => req).length;
        },

        getPasswordStrengthColor() {
            if (this.passwordStrength <= 2) return 'bg-red-500';
            if (this.passwordStrength <= 4) return 'bg-yellow-500';
            return 'bg-green-500';
        },

        getPasswordStrengthText() {
            if (this.passwordStrength <= 2) return 'Zayıf';
            if (this.passwordStrength <= 4) return 'Orta';
            return 'Güçlü';
        }
    }));

    // Kullanıcı Tablo Bileşeni - Factory pattern
    function usersTableData() {
        return {
            selectedUsers: [],
            showBulkActions: false,

            toggleUser(userId) {
                if (this.selectedUsers.includes(userId)) {
                    this.selectedUsers = this.selectedUsers.filter(id => id !== userId);
                } else {
                    this.selectedUsers.push(userId);
                }
                this.showBulkActions = this.selectedUsers.length > 0;
            },

            selectAll() {
                const root = this.$root || document;
                const checkboxes = root.querySelectorAll('input[type="checkbox"][name="user_ids[]"]');
                this.selectedUsers = Array.from(checkboxes).map(cb => cb.value);
                this.showBulkActions = this.selectedUsers.length > 0;
            },

            deselectAll() {
                this.selectedUsers = [];
                this.showBulkActions = false;
            },

            bulkDelete() {
                if (confirm(`${this.selectedUsers.length} kullanıcıyı silmek istediğinizden emin misiniz?`)) {
                    // Livewire method call
                    this.$wire.bulkDelete(this.selectedUsers);
                    this.selectedUsers = [];
                    this.showBulkActions = false;
                }
            }
        };
    }

    Alpine.data('usersTable', usersTableData);

    // Global fonksiyon wrapper - x-data="usersTable" ve x-data="usersTable()" için uyumluluk
    if (typeof window !== 'undefined' && !window.usersTable) {
        window.usersTable = function () {
            return usersTableData();
        };
    }

    // Kullanıcı Arama Bileşeni
    Alpine.data('usersSearch', () => ({
        searchQuery: '',
        isSearching: false,
        searchResults: [],

        async search() {
            if (this.searchQuery.length < 2) return;

            this.isSearching = true;
            try {
                // API çağrısını simüle et
                await new Promise(resolve => setTimeout(resolve, 500));
                this.searchResults = [];
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.isSearching = false;
            }
        },

        clearSearch() {
            this.searchQuery = '';
            this.searchResults = [];
        }
    }));
}, { once: true });

// Module initialization function
function initUserModule() {
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
registerModuleInit('user', initUserModule);

// Yardımcı Fonksiyonlar
const UserModule = {
    // Bildirim göster - uses shared notification utility
    showNotification,

    // Onay iletişim kutusu
    confirm(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },

    // Tarihi formatla
    formatDate(date) {
        return new Date(date).toLocaleDateString('tr-TR');
    },

    // Tarih-saat formatla
    formatDateTime(date) {
        return new Date(date).toLocaleString('tr-TR');
    }
};
