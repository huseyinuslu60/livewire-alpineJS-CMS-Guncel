// Settings Module JS
// Menu Management with Sortable.js drag & drop

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// ============================================================================
// Alpine.js Components
// ============================================================================

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

// ============================================================================
// Sortable Management
// ============================================================================

/**
 * Find Livewire $wire object (global fallback)
 */
function getLivewireWire() {
    if (!window.Livewire) return null;

    const wireElement = document.querySelector('[wire\\:id]');
    if (wireElement) {
        const wireId = wireElement.getAttribute('wire:id');
        if (wireId) {
            try {
                return window.Livewire.find(wireId);
            } catch (e) {
                // Ignore
            }
        }
    }

    if (typeof window.Livewire.first === 'function') {
        try {
            return window.Livewire.first();
        } catch (e) {
            // Ignore
        }
    }

    return null;
}

/**
 * Given an element, find the closest Livewire component ($wire)
 */
function getWireForElement(element) {
    if (!window.Livewire || !element) return null;

    // 1) Önce, en yakın wire:id taşıyan elementi bul
    const root = element.closest('[wire\\:id]');
    if (root) {
        const id = root.getAttribute('wire:id');
        if (id) {
            try {
                return window.Livewire.find(id);
            } catch (e) {
                console.warn('[Settings] Livewire.find failed for id:', id, e);
            }
        }
    }

    // 2) Fallback: eski global yöntem (en kötü ihtimal)
    return getLivewireWire();
}

/**
 * Extract sort order from sortable list
 */
function extractSortOrder(sortableList) {
    const parentId = sortableList.dataset.parent === '0' || !sortableList.dataset.parent
        ? null
        : parseInt(sortableList.dataset.parent, 10);

    // Get all direct children (only direct children, not nested)
    const items = Array.from(sortableList.children);

    const newOrder = items.map((item, index) => {
        // Find sortable-item - could be direct child or inside wrapper
        let sortableItem = null;

        if (item.classList.contains('sortable-item')) {
            // Direkt sortable-item (alt menüler için)
            sortableItem = item;
        } else {
            // Wrapper div içinde sortable-item ara (ana menü için)
            sortableItem = item.querySelector('.sortable-item');

            // Eğer bulunamazsa, item'ın kendisi sortable-item olabilir mi kontrol et
            if (!sortableItem && item.classList.contains('sortable-item')) {
                sortableItem = item;
            }
        }

        // Eğer sortable-item bulunamazsa ama item'ın kendisi data-id'ye sahipse, onu kullan
        if (!sortableItem && item.dataset.id) {
            return {
                id: parseInt(item.dataset.id, 10),
                sort_order: index + 1,
                parent_id: parentId
            };
        }

        // sortable-item bulunduysa onu kullan
        if (sortableItem && sortableItem.dataset.id) {
            return {
                id: parseInt(sortableItem.dataset.id, 10),
                sort_order: index + 1,
                parent_id: parentId
            };
        }

        return null;
    }).filter(item => item !== null && item.id);

    return newOrder;
}

/**
 * Send sort order to Livewire
 */
function updateSortOrderInLivewire(newOrder, sourceList) {
    if (!window.Livewire || !newOrder || newOrder.length === 0) {
        return false;
    }

    const $wire = getWireForElement(sourceList);
    if (!$wire) {
        return false;
    }

    try {
        if (typeof $wire.updateSortOrder === 'function') {
            $wire.updateSortOrder(newOrder);
            return true;
        }
        if (typeof $wire.$call === 'function') {
            $wire.$call('updateSortOrder', newOrder);
            return true;
        }
        return false;
    } catch (e) {
        console.error('[Settings] Error calling updateSortOrder:', e);
        return false;
    }
}

/**
 * Create sortable instance for a list
 */
function createSortable(list, $wire) {
    if (!window.Sortable || !list) {
        return null;
    }

    // Bu liste zaten initialize edildiyse tekrar elleme
    if (list.dataset.sortableInitialized === '1') {
        return null;
    }

    const parentId = list.dataset.parent === '0' || !list.dataset.parent
        ? null
        : parseInt(list.dataset.parent, 10);

    try {
        // Ana menü için wrapper div'i, alt menüler için direkt sortable-item'ı tespit et
        const firstChild = list.firstElementChild;
        const hasWrapper = firstChild && !firstChild.classList.contains('sortable-item') && firstChild.querySelector('.sortable-item');

        // Ana menü için: wrapper div'i seç (sortable-item içeren div)
        // Alt menüler için: direkt sortable-item'ı seç
        let draggableSelector;
        if (hasWrapper) {
            // Ana menü: tüm direkt çocuk div'leri seç (hepsi wrapper)
            draggableSelector = '> div';
        } else {
            // Alt menüler ve nested menüler: direkt sortable-item
            draggableSelector = '> .sortable-item';
        }

        const instance = new window.Sortable(list, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',

            draggable: draggableSelector,

            filter: 'input, select, textarea',
            preventOnFilter: true,

            group: {
                name: `menu-level-${parentId || 'root'}`,
                pull: false,
                put: false
            },

            onStart(evt) {
                try {
                    if (evt.item) {
                        evt.item.classList.add('dragging');
                    }
                } catch (e) {
                    console.warn('[Settings] Error in onStart:', e);
                }
            },

            onEnd(evt) {
                // Sortable kendi iç cleanup'ını bitirsin diye Livewire çağrısını sonraya at
                requestAnimationFrame(() => {
                    try {
                        if (evt.item) {
                            evt.item.classList.remove('dragging');
                        }

                        const toList = evt.to;
                        if (!toList || !document.contains(toList)) {
                            return;
                        }

                        const newOrder = extractSortOrder(toList);
                        if (!newOrder.length) {
                            return;
                        }

                        updateSortOrderInLivewire(newOrder, toList);
                    } catch (error) {
                        console.error('[Settings] Error in onEnd:', error);
                    }
                });
            }
        });

        // "Bir kere initialize edildi" bayrağı
        list.dataset.sortableInitialized = '1';

        return instance;
    } catch (e) {
        console.error('[Settings] Error creating sortable:', e);
        return null;
    }
}

/**
 * Initialize all sortable lists on the page
 */
function initializeSortables() {
    if (!window.Sortable || !window.Livewire) {
        setTimeout(initializeSortables, 100);
        return;
    }

    const lists = document.querySelectorAll('.sortable-list');
    if (!lists.length) return;

    const $wire = getLivewireWire();

    lists.forEach(list => {
        // Eğer zaten initialize edildiyse, önce temizle
        if (list.dataset.sortableInitialized === '1' && list.sortableInstance) {
            try {
                if (document.contains(list)) {
                    list.sortableInstance.destroy();
                }
            } catch (e) {
                // Ignore destroy errors
            }
            list.dataset.sortableInitialized = '0';
            list.sortableInstance = null;
        }

        createSortable(list, $wire);
    });
}

// ============================================================================
// Module Initialization
// ============================================================================

function initSettingsModule() {
    initializeSortables();
}

// Register with lifecycle manager
registerModuleInit('settings', initSettingsModule);

// ============================================================================
// Event Listeners
// ============================================================================

// DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(initSettingsModule, 300);
    });
} else {
    setTimeout(initSettingsModule, 300);
}

// Livewire events
function setupLivewireEvents() {
    if (!window.Livewire) {
        setTimeout(setupLivewireEvents, 100);
        return;
    }

    document.addEventListener('livewire:initialized', () => {
        setTimeout(initSettingsModule, 300);
    }, { once: true });

    document.addEventListener('livewire:navigated', () => {
        setTimeout(initSettingsModule, 300);
    });

    document.addEventListener('livewire:updated', () => {
        setTimeout(initSettingsModule, 200);
    });

    document.addEventListener('livewire:load', () => {
        setTimeout(initSettingsModule, 300);
    });
}

setupLivewireEvents();

// MutationObserver for dynamic content
if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver((mutations) => {
        let hasNewLists = false;
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) {
                    if (node.classList?.contains('sortable-list') ||
                        node.querySelector?.('.sortable-list')) {
                        hasNewLists = true;
                    }
                }
            });
        });
        if (hasNewLists) {
            setTimeout(initSettingsModule, 200);
        }
    });

    if (document.body) {
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    }
}

// ============================================================================
// Global Helpers
// ============================================================================

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
