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
    trixTimers: new Map(),
    mo: null,
    onUploadFinish: null,
    onLWUpdated: null,

        init() {
      // 1) Mevcut editörleri sadece bu form scope'unda bağla
                this.initTrixEditors(this.$root);

      // 2) Dinamik eklenen editörleri izle (yalnızca form veya galeri scope'u)
      const host = this.$root.querySelector('#gallery-sortable') || this.$root;
      this.mo = new MutationObserver((muts) => {
        let touched = false;
        for (const m of muts) {
          for (const n of m.addedNodes) {
            if (!(n instanceof HTMLElement)) continue;
            if (n.matches?.('trix-editor[id^="description-editor-"]')) {
              this.attachTrix(n); touched = true;
            }
            n.querySelectorAll?.('trix-editor[id^="description-editor-"]').forEach(e => {
              this.attachTrix(e); touched = true;
            });
          }
        }
        if (touched) requestAnimationFrame(() => this.initTrixEditors(this.$root));
      });
      this.mo.observe(host, { childList: true, subtree: true });

      // 3) Scope delegation: global değil, yalnızca bu form kökü
      this.$root.addEventListener('trix-change', (e) => {
        const el = e.target;
        if (!this.isOurEditor(el)) return;
        const id = el.id;
                        const fileId = id.replace('description-editor-', '');
        const html = el.editor ? el.editor.getDocument().toString() : el.innerHTML;

        const key = `debounce:${id}`;
        if (this.trixTimers.has(key)) clearTimeout(this.trixTimers.get(key));
        const t = setTimeout(() => {
                        localStorage.setItem(`trix:${id}`, html);
          this.$wire?.call('updateFileById', fileId, 'description', html);
        }, Alpine.store('posts').trixDebounce || 300);
        this.trixTimers.set(key, t);
      }, true);

      // 4) Livewire olayları: kısa ve tek bekleme
      this.onUploadFinish = () => requestAnimationFrame(() => this.initTrixEditors(this.$root));
      this.onLWUpdated   = () => requestAnimationFrame(() => this.initTrixEditors(this.$root));
      document.addEventListener('livewire:upload-finish', this.onUploadFinish);
      document.addEventListener('livewire:updated', this.onLWUpdated);

      // 5) Cleanup garantisi
      this.$root.addEventListener('alpine:destroy', () => this.cleanup(), { once: true });
    },

    cleanup() {
      if (this.mo) { this.mo.disconnect(); this.mo = null; }
      if (this.onUploadFinish) document.removeEventListener('livewire:upload-finish', this.onUploadFinish);
      if (this.onLWUpdated)   document.removeEventListener('livewire:updated', this.onLWUpdated);
      this.trixTimers.forEach((t) => clearTimeout(t));
      this.trixTimers.clear();
    },

    isOurEditor(el) {
      return el && el.tagName?.toLowerCase() === 'trix-editor' && el.id?.startsWith('description-editor-');
    },

    attachTrix(el) {
      if (!this.isOurEditor(el)) return;
      if (el.dataset._bound === '1') return; // çift bağlanma koruması

      const id = el.id;
      const saved = localStorage.getItem(`trix:${id}`);
      const initial = saved || el.getAttribute('data-description') || '';

                requestAnimationFrame(() => {
        try { el.editor?.loadHTML(initial); }
        catch (_) { el.innerHTML = initial; }
      });

      el.dataset._bound = '1';
    },

    initTrixEditors(scope) {
      scope.querySelectorAll('trix-editor[id^="description-editor-"]')
           .forEach((el) => this.attachTrix(el));
    },

    // mevcut yardımcılar
    addFile(f){ this.files.push(f); },
    removeFile(i){
      this.files.splice(i,1);
      if (this.primaryFileIndex >= this.files.length) {
        this.primaryFileIndex = Math.max(0, this.files.length - 1);
      }
    },
    setPrimary(i){ this.primaryFileIndex = i; },

    // Trumbowyg sync functions
    initTrumbowygSync() {
      const contentTextarea = document.getElementById('content');
      if (!contentTextarea || !window.jQuery) return;

      // Trumbowyg zaten mount edilmiş olabilir, kontrol et
      if (window.jQuery(contentTextarea).data('trumbowyg')) {
        // Trumbowyg change event'ini dinle
        window.jQuery(contentTextarea).on('tbwchange', () => {
          const content = window.jQuery(contentTextarea).trumbowyg('html');
          if (this.$wire) {
            this.$wire.set('content', content, false);
          }
        });
      } else {
        // Trumbowyg henüz mount edilmemiş, bekle
        const checkInterval = setInterval(() => {
          if (window.jQuery(contentTextarea).data('trumbowyg')) {
            clearInterval(checkInterval);
            window.jQuery(contentTextarea).on('tbwchange', () => {
              const content = window.jQuery(contentTextarea).trumbowyg('html');
              if (this.$wire) {
                this.$wire.set('content', content, false);
              }
            });
          }
        }, 100);
        // 5 saniye sonra timeout
        setTimeout(() => clearInterval(checkInterval), 5000);
      }
    },

    syncContentAndSave() {
      // Trumbowyg içeriğini Livewire'a senkronize et
      const contentTextarea = document.getElementById('content');
      if (contentTextarea && window.jQuery && window.jQuery(contentTextarea).data('trumbowyg')) {
        const content = window.jQuery(contentTextarea).trumbowyg('html');
        if (this.$wire) {
          // İçeriği senkronize et
          this.$wire.set('content', content, false);
          // Kısa bir gecikme sonrası submit et (içeriğin senkronize olması için)
          setTimeout(() => {
            if (this.$wire) {
              this.$wire.call('savePost');
            }
          }, 100);
        }
      } else {
        // Trumbowyg yoksa direkt submit et
        if (this.$wire) {
          this.$wire.call('savePost');
        }
      }
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
