// Yazılar Modülü JavaScript - Alpine.js
// ======================================

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// Sortable.js artık window.Sortable üzerinden global olarak kullanılabilir

// Alpine.js Store and Data Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
  Alpine.store('posts', { trixDebounce: 300 });

  // postsTable - Factory pattern ile her instance kendi state'ine sahip
  function postsTableData() {
    return {
      selected: new Set(),
      selectAll: false,
      bulkAction: '',

      init() {},

      toggleSelectAll() {
        this.selectAll = !this.selectAll;
        this.selected = this.selectAll ? new Set(this.getAllIds()) : new Set();
        this.$wire.set('selectAll', this.selectAll);
      },

      getAllIds() {
        const root = this.$root || document;
        return Array.from(root.querySelectorAll('[data-post-id]')).map(el => el.dataset.postId);
      },

      applyBulk() {
        if (this.bulkAction && this.selected.size > 0) {
          this.$wire.call('applyBulkAction', this.bulkAction, Array.from(this.selected));
        }
      }
    };
  }

  Alpine.data('postsTable', postsTableData);

  // Global fonksiyon wrapper - x-data="postsTable" ve x-data="postsTable()" için uyumluluk
  if (typeof window !== 'undefined' && !window.postsTable) {
    window.postsTable = function() {
      return postsTableData();
    };
  }

  Alpine.data('tagsInput', (initial = '') => ({
        tags: [],
        newTag: '',

        init() {
          // Get initial value from parameter or Livewire wire
          let tagsValue = initial;
          if (!tagsValue && this.$wire) {
            tagsValue = this.$wire.get ? this.$wire.get('tagsInput') : (this.$wire.tagsInput || '');
          }
          // Ensure it's a string
          tagsValue = typeof tagsValue === 'string' ? tagsValue : String(tagsValue || '');
          // Parse tags
          this.tags = tagsValue
            .split(',')
            .map(t => t.trim())
            .filter(Boolean);
        },

        addTag() {
          const parts = this.newTag.split(',').map(t => t.trim()).filter(Boolean);
          parts.forEach(t => {
            if (!this.tags.includes(t)) {
              this.tags.push(t);
            }
          });
          this.newTag = '';
          this.sync();
        },

        removeTag(i) {
          this.tags.splice(i, 1);
          this.sync();
        },

        keydown(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            this.addTag();
          }
        },

        sync() {
          if (this.$wire && Array.isArray(this.tags)) {
            // Filter out empty tags and join with comma
            const tagsString = this.tags
              .filter(tag => tag && typeof tag === 'string' && tag.trim() !== '')
              .join(',');
            // Use $wire.set() with skipDebounce=true to prevent multiple rapid updates
            // This is safer than call() and prevents corrupt payload issues
            if (this.$wire.set && typeof this.$wire.set === 'function') {
              this.$wire.set('tagsInput', tagsString, true);
            } else if (this.$wire.tagsInput !== undefined) {
              // Fallback: direct property assignment
              this.$wire.tagsInput = tagsString;
            }
          }
        }
  }));

    Alpine.data('postsForm', () => ({
        files: [],
        primaryFileIndex: 0,

    // mevcut yardımcılar
    addFile(f){ this.files.push(f); },
    removeFile(i){
      this.files.splice(i,1);
      if (this.primaryFileIndex >= this.files.length) {
        this.primaryFileIndex = Math.max(0, this.files.length - 1);
      }
    },
    setPrimary(i){ this.primaryFileIndex = i; },

    // Trumbowyg sync functions - Form submit'ten önce son bir sync yap
    syncContentAndSave() {
      // Form submit'ten önce Trumbowyg içeriğini son kez sync et
      const contentTextarea = document.querySelector('textarea[data-editor="trumbowyg"], textarea.trumbowyg');
      if (contentTextarea && window.jQuery && window.jQuery(contentTextarea).data('trumbowyg')) {
        try {
          const content = window.jQuery(contentTextarea).trumbowyg('html');
          if (this.$wire && this.$wire.set) {
            // wire:model attribute'unu bul
            let propertyName = 'content';
            const wireModelAttrs = ['wire:model', 'wire:model.live', 'wire:model.debounce'];
            for (const attrName of wireModelAttrs) {
              if (contentTextarea.hasAttribute(attrName)) {
                propertyName = contentTextarea.getAttribute(attrName) || propertyName;
                break;
              }
            }
            // Eğer bulunamazsa, tüm attribute'ları tara
            if (propertyName === 'content') {
              const allAttrs = Array.from(contentTextarea.attributes);
              const wireModelAttr = allAttrs.find(attr => attr.name.startsWith('wire:model'));
              if (wireModelAttr) {
                propertyName = wireModelAttr.value || propertyName;
              }
            }
            // Livewire'a set et
            this.$wire.set(propertyName, content, false);
          }
        } catch (e) {
          if (import.meta.env?.DEV) {
            console.warn('Trumbowyg sync error before submit:', e);
          }
        }
      }

      // Kısa bir gecikme sonrası submit et (Livewire'ın property'yi işlemesi için)
      setTimeout(() => {
        if (this.$wire) {
          this.$wire.call('savePost');
        }
      }, 100);
    },

    syncContentAndUpdate() {
      // PostEdit için - updatePost metodunu çağır
      // Form submit'ten önce Trumbowyg içeriğini son kez sync et
      const contentTextarea = document.querySelector('textarea[data-editor="trumbowyg"], textarea.trumbowyg');
      if (contentTextarea && window.jQuery && window.jQuery(contentTextarea).data('trumbowyg')) {
        try {
          const content = window.jQuery(contentTextarea).trumbowyg('html');
          if (this.$wire && this.$wire.set) {
            // wire:model attribute'unu bul
            let propertyName = 'content';
            const wireModelAttrs = ['wire:model', 'wire:model.live', 'wire:model.debounce'];
            for (const attrName of wireModelAttrs) {
              if (contentTextarea.hasAttribute(attrName)) {
                propertyName = contentTextarea.getAttribute(attrName) || propertyName;
                break;
              }
            }
            // Eğer bulunamazsa, tüm attribute'ları tara
            if (propertyName === 'content') {
              const allAttrs = Array.from(contentTextarea.attributes);
              const wireModelAttr = allAttrs.find(attr => attr.name.startsWith('wire:model'));
              if (wireModelAttr) {
                propertyName = wireModelAttr.value || propertyName;
              }
            }
            // Livewire'a set et
            this.$wire.set(propertyName, content, false);
          }
        } catch (e) {
          if (import.meta.env?.DEV) {
            console.warn('Trumbowyg sync error before submit:', e);
          }
        }
      }

      // Kısa bir gecikme sonrası submit et (Livewire'ın property'yi işlemesi için)
      setTimeout(() => {
        if (this.$wire) {
          this.$wire.call('updatePost');
        }
      }, 100);
    }
  }));

    Alpine.data('galleryUpload', () => ({
        focusNew() {
      this.$nextTick(() => {
            setTimeout(() => {
          const items = document.querySelectorAll('#gallery-sortable .gallery-item');
          if (items && items.length) {
            const last = items[items.length - 1];
            last.scrollIntoView({ behavior: 'smooth', block: 'center' });
            last.classList.add('ring-2', 'ring-orange-400');
            setTimeout(() => last.classList.remove('ring-2', 'ring-orange-400'), 1500);
          }
        }, 400);
      });
        }
    }));

    Alpine.data('gallerySortable', () => ({
    sortable: null,
        isDragging: false,
    raf: null,
    onLWUpdated: null,

        init() {
      const el = this.$root;
      if (!el || !window.Sortable) return;
            this.initializeSortable();

      this.onLWUpdated = () => {
        cancelAnimationFrame(this.raf);
        this.raf = requestAnimationFrame(() => this.rebind());
      };
      document.addEventListener('livewire:updated', this.onLWUpdated);

      this.$root.addEventListener('alpine:destroy', () => this.cleanup(), { once: true });
    },

    cleanup() {
      if (this.sortable) { this.sortable.destroy(); this.sortable = null; }
      if (this.onLWUpdated) document.removeEventListener('livewire:updated', this.onLWUpdated);
        },

        initializeSortable() {
      const el = this.$root;
      if (!el || !window.Sortable) return;
      if (this.sortable) { this.sortable.destroy(); this.sortable = null; }

      this.sortable = window.Sortable.create(el, {
                animation: 150,
                handle: '.sortable-handle',
        onStart: () => { this.isDragging = true; },
        onEnd:   () => { this.isDragging = false; this.flushOrder(); }
            });
        },

    rebind() {
      // Sadece yakın postsForm scope'u içinde Trix re-init
      const form = this.$root.closest('form,[x-data*="postsForm"]') || document.querySelector('[x-data*="postsForm"]');
      if (form) Alpine.$data(form)?.initTrixEditors?.(form);
      this.initializeSortable();
    },

    flushOrder() {
      const items = Array.from(this.$root.querySelectorAll('.gallery-item'));
      if (!items.length) return;
      const order = items.map(i => i.id?.replace('gallery-item-','') || i.dataset.fileId).filter(Boolean);
      if (order.length) this.$wire?.call('updateOrder', order);
        }
    }));
}, { once: true });

// Module initialization function
function initPostsModule() {
  // Module-specific initialization (non-Alpine code)
  postsInitOnce();
}

const postsInitOnce = () => {
  document.querySelectorAll('[x-data*="gallerySortable"]').forEach(el => {
    try { Alpine.$data(el)?.rebind?.(); } catch(_) {}
  });
};

// Register module with central lifecycle manager
registerModuleInit('posts', initPostsModule);

// Trumbowyg integration is now handled by centralized editors-lifecycle.js
// The editors-lifecycle.js automatically syncs Trumbowyg changes to Livewire
// via the 'tbwchange' event which dispatches 'input' events for wire:model bindings.
// No duplicate event bindings needed here.
