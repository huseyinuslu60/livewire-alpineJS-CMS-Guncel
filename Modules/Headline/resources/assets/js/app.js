// Manşet Modülü JavaScript - Alpine.js
// ======================================

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Manşet Yönetimi Bileşeni
    Alpine.data('headlines.manage', () => ({
        activeZone: 'manset',
        zones: {
            'manset': 'Manşet',
            'surmanset': 'Sürmanşet', 
            'one_cikanlar': 'Öne Çıkanlar'
        },
        query: '',
        type: 'all',
        showScheduleModal: false,
        schedZone: '',
        schedSubjectType: '',
        schedSubjectId: 0,
        schedStartsAt: null,
        schedEndsAt: null,
        
        init() {},
        
        setZone(zone) {
            if (this.zones[zone]) {
                this.activeZone = zone;
                this.$wire.call('setZone', zone);
            }
        },
        
        openSchedule(zone, subjectType, subjectId) {
            this.schedZone = zone;
            this.schedSubjectType = subjectType;
            this.schedSubjectId = subjectId;
            this.schedStartsAt = null;
            this.schedEndsAt = null;
            this.showScheduleModal = true;
        },
        
        closeSchedule() {
            this.showScheduleModal = false;
            this.schedZone = '';
            this.schedSubjectType = '';
            this.schedSubjectId = 0;
            this.schedStartsAt = null;
            this.schedEndsAt = null;
        },
        
        applySchedule() {
            if (this.schedStartsAt && this.schedEndsAt) {
                this.$wire.call('applySchedule', {
                    zone: this.schedZone,
                    subjectType: this.schedSubjectType,
                    subjectId: this.schedSubjectId,
                    startsAt: this.schedStartsAt,
                    endsAt: this.schedEndsAt
                });
                this.closeSchedule();
            }
        },
        
        pinItem(zone, subjectType, subjectId, slot) {
            this.$wire.call('pin', zone, subjectType, subjectId, slot);
        },
        
        unpinItem(zone, subjectType, subjectId) {
            this.$wire.call('unpin', zone, subjectType, subjectId);
        },
        
        searchContent() {
            this.$wire.call('searchContent', this.query);
        },
        
        clearSearch() {
            this.query = '';
            this.$wire.call('searchContent', '');
        },
        
        changeType(type) {
            this.type = type;
            this.$wire.call('changeType', type);
        }
    }));
    
    // Manşet Sıralanabilir Bileşeni - Galeri'den uyarlandı
    Alpine.data('headlineSortable', () => ({
        sortableInstance: null,
        
        init() {
            this.$nextTick(() => {
                this.startSortable();
            });
        },
        
        startSortable() {
            // Sortable.js yüklü mü kontrol et
            if (typeof Sortable === 'undefined') {
                setTimeout(() => this.startSortable(), 1000);
                return;
            }
            
            // Kapsayıcıyı bul - this.$el zaten kapsayıcı
            const container = this.$el;
            
            if (container.children.length === 0) {
                setTimeout(() => this.startSortable(), 1000);
                return;
            }
            
            // Mevcut örneği temizle
            if (this.sortableInstance) {
                this.sortableInstance.destroy();
            }
            
            this.sortableInstance = new window.Sortable(container, {
                handle: '.cursor-move',
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                forceFallback: false,
                fallbackTolerance: 0,
                onStart: (evt) => {
                    evt.item.classList.add('sorting-dragging');
                    document.body.classList.add('sorting-active');
                },
                onEnd: (evt) => {
                    evt.item.classList.remove('sorting-dragging');
                    document.body.classList.remove('sorting-active');
                    
                    if (evt.oldIndex !== evt.newIndex) {
                        const items = Array.from(container.children);
                        const ordered = items.map((item) => ({
                            subject_type: item.dataset.type,
                            subject_id: parseInt(item.dataset.id)
                        }));
                        
                        // Livewire metodunu doğrudan çağır
                        if (window.Livewire) {
                            const activeZone = document.querySelector('[data-zone]')?.dataset.zone || 'manset';
                            Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).call('saveOrder', activeZone, ordered);
                        }
                    }
                }
            });
        },
        
        reinitialize() {
            this.$nextTick(() => {
                this.startSortable();
            });
        }
    }));
    
    // Manşet Öneri Bileşeni
    Alpine.data('headlines.suggestion', () => ({
        isSearching: false,
        searchQuery: '',
        
        init() {},
        
        async search() {
            if (this.searchQuery.length < 2) return;
            
            this.isSearching = true;
            this.$wire.call('searchSuggestions', this.searchQuery);
            this.isSearching = false;
        },
        
        clearSearch() {
            this.searchQuery = '';
            this.$wire.call('clearSearch');
        }
    }));
}, { once: true });

// Sortable.js artık window.Sortable üzerinden global olarak kullanılabilir (app.js'de tanımlı)

// Minimal rebind – Sortable için
const initOnce = () => {
    const headlineSortable = document.querySelector('[x-data*="headlineSortable"]');
    if (!headlineSortable) return;
    
    try {
        const alpineData = Alpine.$data(headlineSortable);
        if (alpineData && typeof alpineData.reinitialize === 'function') {
            alpineData.reinitialize();
        }
    } catch (error) {
        if (import.meta.env.DEV) {
            console.debug('livewire:navigated - Headline Sortable restore error:', error);
        }
    }
};

// Module initialization function
function initHeadlineModule() {
    initOnce();
}

// Register module with central lifecycle manager
registerModuleInit('headline', initHeadlineModule);

// Yardımcı fonksiyonlar
function formatDate(date) {
    return new Date(date).toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function formatDateTime(date) {
    return new Date(date).toLocaleString('tr-TR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getZoneColor(zone) {
    switch(zone) {
        case 'manset':
            return 'bg-red-100 text-red-800';
        case 'surmanset':
            return 'bg-blue-100 text-blue-800';
        case 'one_cikanlar':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getZoneIcon(zone) {
    switch(zone) {
        case 'manset':
            return 'fas fa-star';
        case 'surmanset':
            return 'fas fa-newspaper';
        case 'one_cikanlar':
            return 'fas fa-thumbs-up';
        default:
            return 'fas fa-question';
    }
}