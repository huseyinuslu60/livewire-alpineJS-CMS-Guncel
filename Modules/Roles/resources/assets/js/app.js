// Roller Modülü JavaScript
// ========================

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';
import { showNotification as sharedShowNotification } from '@/js/ui/notifications';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Rol Yönetimi Bileşeni
    Alpine.data('roleManagement', () => ({
        selectedRoles: [],
        selectAll: false,
        searchQuery: '',
        filterStatus: '',
        
        init() {
            this.setupFormValidation();
            this.setupPermissionToggles();
        },
        
        setupFormValidation() {
            // Form alanları için gerçek zamanlı doğrulama
            this.$watch('$wire.name', (value) => {
                if (value) {
                    this.validateRoleName(value);
                }
            });
            
            this.$watch('$wire.display_name', (value) => {
                if (value) {
                    this.validateDisplayName(value);
                }
            });
        },
        
        setupPermissionToggles() {
            // İzin grubu toggle'larını işle
            this.$watch('$wire.selectedPermissions', (value) => {
                this.updatePermissionGroups();
            });
        },
        
        validateRoleName(name) {
            if (!name || name.trim().length < 2) {
                this.showFieldError('name', 'Rol adı en az 2 karakter olmalıdır');
                return false;
            }
            this.clearFieldError('name');
            return true;
        },
        
        validateDisplayName(displayName) {
            if (!displayName || displayName.trim().length < 2) {
                this.showFieldError('display_name', 'Görünen ad en az 2 karakter olmalıdır');
                return false;
            }
            this.clearFieldError('display_name');
            return true;
        },
        
        showFieldError(fieldName, message) {
            const field = document.getElementById(fieldName);
            if (field) {
                field.classList.add('border-red-500');
                this.showErrorMessage(field, message);
            }
        },
        
        clearFieldError(fieldName) {
            const field = document.getElementById(fieldName);
            if (field) {
                field.classList.remove('border-red-500');
                this.hideErrorMessage(field);
            }
        },
        
        showErrorMessage(field, message) {
            this.hideErrorMessage(field);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-red-500 text-sm mt-1';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        },
        
        hideErrorMessage(field) {
            const existingError = field.parentNode.querySelector('.text-red-500');
            if (existingError) {
                existingError.remove();
            }
        },
        
        togglePermissionGroup(groupName) {
            const groupCheckboxes = document.querySelectorAll(`[data-group="${groupName}"]`);
            const allChecked = Array.from(groupCheckboxes).every(cb => cb.checked);
            
            groupCheckboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
                this.updateLivewireModel(checkbox);
            });
        },
        
        updateLivewireModel(checkbox) {
            // Livewire model güncellemesini tetikle
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        },
        
        updatePermissionGroups() {
            // Bireysel izinlere göre grup toggle durumlarını güncelle
            const groups = ['users', 'articles', 'categories', 'posts', 'roles', 'authors'];
            
            groups.forEach(groupName => {
                const groupCheckboxes = document.querySelectorAll(`[data-group="${groupName}"]`);
                const checkedCount = Array.from(groupCheckboxes).filter(cb => cb.checked).length;
                const groupToggle = document.querySelector(`[data-group-toggle="${groupName}"]`);
                
                if (groupToggle) {
                    groupToggle.checked = checkedCount === groupCheckboxes.length;
                    groupToggle.indeterminate = checkedCount > 0 && checkedCount < groupCheckboxes.length;
                }
            });
        },
        
        async submitRoleForm() {
            try {
                await this.$wire.saveRole();
            } catch (error) {
                console.error('Role form submission error:', error);
            }
        },
        
        async submitPermissionForm() {
            try {
                await this.$wire.updatePermissions();
            } catch (error) {
                console.error('Permission form submission error:', error);
            }
        },
        
        selectAllPermissions() {
            // Tüm permission checkbox'larını seç
            const checkboxes = document.querySelectorAll('input[type="checkbox"][wire\\:model\\.live="selectedPermissions"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                // Livewire model'ini güncelle
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            });
        },
        
        clearAllPermissions() {
            // Tüm permission checkbox'larını temizle
            const checkboxes = document.querySelectorAll('input[type="checkbox"][wire\\:model\\.live="selectedPermissions"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                // Livewire model'ini güncelle
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            });
        },
        
        selectGroupPermissions(groupName) {
            // Belirli bir grubun tüm permission'larını seç
            const groupCheckboxes = document.querySelectorAll(`[data-group="${groupName}"] input[type="checkbox"]`);
            groupCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            });
        },
        
        clearGroupPermissions(groupName) {
            // Belirli bir grubun tüm permission'larını temizle
            const groupCheckboxes = document.querySelectorAll(`[data-group="${groupName}"] input[type="checkbox"]`);
            groupCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }
    }));

    // Rol Kartı Bileşeni
    Alpine.data('roleCard', () => ({
        showActions: false,
        
        toggleActions() {
            this.showActions = !this.showActions;
        },
        
        hideActions() {
            this.showActions = false;
        }
    }));

    // İzin Modal Bileşeni
    Alpine.data('rolePermissionModal', () => ({
        isOpen: false,
        
        open() {
            this.isOpen = true;
        },
        
        close() {
            this.isOpen = false;
        }
    }));
}, { once: true });

// Module initialization function
function initRolesModule() {
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
registerModuleInit('roles', initRolesModule);

// Yardımcı Fonksiyonlar
function showNotification(message, type = 'success') {
    // Use shared Tailwind toast notification with animation
    sharedShowNotification(message, type, { animated: true });
}

function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Livewire Olay Dinleyicileri
document.addEventListener('livewire:init', function() {
    Livewire.on('refresh-page', function() {
        document.body.style.opacity = '0.7';
        setTimeout(() => {
            window.location.reload();
        }, 200);
    });
});



// Global erişim için fonksiyonları export et
const RolesModule = {
    showNotification,
    confirmAction
};