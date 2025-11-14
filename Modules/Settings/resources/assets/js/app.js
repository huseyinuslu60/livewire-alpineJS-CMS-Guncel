// Settings Module JS
// Bootstrap and SCSS are handled by Vite entry in vite.config.js

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// Settings Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    Alpine.data('settings.tabs', () => ({
        activeTab: 'general',

        setActiveTab(tab) {
            this.activeTab = tab;
        }
    }));

    Alpine.data('settings.menuManagement', () => ({
        showCreateModal: false,
        showEditModal: false,
        editingItem: null,

        openCreateModal() {
            this.showCreateModal = true;
        },

        openEditModal(item) {
            this.editingItem = item;
            this.showEditModal = true;
        },

        closeModals() {
            this.showCreateModal = false;
            this.showEditModal = false;
            this.editingItem = null;
        }
    }));
}, { once: true });

// Module initialization function - IDEMPOTENT
let sortableInstances = new Map();

function initSettingsModule() {
    // Idempotent: cleanup existing instances before re-initializing
    sortableInstances.forEach((instance, element) => {
        if (instance && typeof instance.destroy === 'function') {
            try {
                instance.destroy();
            } catch (e) {
                if (import.meta.env.DEV) console.warn('Sortable destroy error:', e);
            }
        }
        element.dataset.sortableInitialized = '';
    });
    sortableInstances.clear();

    // Initialize sortable
    initializeSortable();
}

// Register module with central lifecycle manager
registerModuleInit('settings', initSettingsModule);

// Global functions
const settingsHelpers = {
    formatSettingValue: (value, type) => {
        switch (type) {
            case 'boolean':
                return value ? 'Evet' : 'Hayır';
            case 'image':
                return value ? `<img src="${value}" alt="Image" class="h-8 w-8 rounded">` : 'Resim yok';
            default:
                return value || 'Boş';
        }
    },

    getSettingIcon: (type) => {
        const icons = {
            'text': 'fas fa-font',
            'textarea': 'fas fa-align-left',
            'boolean': 'fas fa-toggle-on',
            'select': 'fas fa-list',
            'image': 'fas fa-image',
        };
        return icons[type] || 'fas fa-cog';
    }
};

// Direct event listeners removed - now handled by centralized lifecycle manager
// The initSettingsModule() function is called automatically by the lifecycle manager

function initializeSortable(container = document) {
    if (!window.Sortable) {
        if (import.meta.env.DEV) console.warn('Sortable.js not available');
        return;
    }

    const sortableLists = container.querySelectorAll('.sortable-list');

    sortableLists.forEach(function(list) {
        // Prevent duplicate initialization
        if (sortableInstances.has(list) || list.dataset.sortableInitialized === '1') {
            return;
        }

        // Mark as initialized
        list.dataset.sortableInitialized = '1';

        const sortableInstance = new window.Sortable(list, {
            handle: '.drag-handle',
            filter: 'button, a, input, select, textarea',
            preventOnFilter: false,
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onStart: function(evt) {
                evt.item.classList.add('dragging');
            },
            onEnd: function(evt) {
                evt.item.classList.remove('dragging');

                // Get the new order - sortable-item class'ına sahip tüm element'leri bul
                const items = Array.from(evt.to.querySelectorAll('.sortable-item'));

                const newOrder = items.map((item, index) => {
                    const id = item.dataset.id;
                    return {
                        id: id,
                        sort_order: index + 1
                    };
                }).filter(item => item.id); // Undefined ID'leri filtrele

                // Send to Livewire
                if (window.Livewire) {
                    const wireId = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
                    if (wireId) {
                        Livewire.find(wireId).call('updateSortOrder', newOrder);
                    }
                }
            }
        });

        // Store instance for cleanup
        sortableInstances.set(list, sortableInstance);
        list.sortable = sortableInstance; // Legacy compatibility
    });
}

