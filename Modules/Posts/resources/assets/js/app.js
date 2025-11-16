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
    onLWLoad: null,

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
          // data-description attribute'unu da güncelle (böylece onLWUpdated'te gereksiz güncelleme yapmayız)
          el.setAttribute('data-description', html);
          // Basit yaklaşım: direkt updateFileById çağır (form submit'te zaten existingFiles kullanılıyor)
          this.$wire?.call('updateFileById', fileId, 'description', html);
        }, Alpine.store('posts').trixDebounce || 300);
        this.trixTimers.set(key, t);
      }, true);

      // 4) Livewire olayları: DOM güncellemesi için kısa gecikme
      this.onUploadFinish = () => requestAnimationFrame(() => this.initTrixEditors(this.$root));
      this.onLWUpdated   = () => {
        // DOM güncellemesi için kısa gecikme (description'ların Livewire'dan yüklenmesi için)
        setTimeout(() => {
          // Önce data-description attribute'larını Livewire'dan güncelle
          if (this.$wire) {
            let existingFiles = [];
            try {
              if (this.$wire.get && typeof this.$wire.get === 'function') {
                existingFiles = this.$wire.get('existingFiles') || [];
              } else if (this.$wire.existingFiles) {
                existingFiles = this.$wire.existingFiles || [];
              }

              // Her trix editor için data-description attribute'unu güncelle
              // İçeriği güncelleme - sadece attribute'u güncelle, içerik attachTrix'te yüklenecek
              this.$root.querySelectorAll('trix-editor[id^="description-editor-"]').forEach(editor => {
                const fileId = editor.getAttribute('data-file-id') || editor.id.replace('description-editor-', '');
                const file = existingFiles.find(f => String(f.file_id) === String(fileId));
                if (file && file.description !== undefined) {
                  const newDesc = file.description || '';
                  const oldDesc = editor.getAttribute('data-description') || '';

                  // Editor'un mevcut içeriğini al (kullanıcı yazmışsa)
                  const currentContent = editor.innerHTML || '';
                  const currentContentText = editor.textContent || editor.innerText || '';

                  // Eğer kullanıcı yazmışsa (içerik data-description'dan farklıysa), güncelleme yapma
                  // Sadece içerik boşsa veya data-description ile aynıysa güncelle
                  const shouldUpdate = !currentContentText.trim() ||
                                       currentContentText.trim() === oldDesc.trim() ||
                                       currentContent === oldDesc;

                  // data-description attribute'unu her zaman güncelle (attribute güncellemesi zararsız)
                  editor.setAttribute('data-description', newDesc);

                  // Eğer description değişmişse ve güncelleme yapılmalıysa, attachTrix'i çağır
                  if (newDesc !== oldDesc && shouldUpdate) {
                    // _bound flag'ini kaldır ki yeniden yüklensin
                    editor.dataset._bound = '0';
                    setTimeout(() => {
                      this.attachTrix(editor, newDesc);
                    }, 50);
                  }
                }
              });
            } catch (e) {
              console.warn('Could not update data-description from Livewire:', e);
            }
          }

          // Yeni editörler için initTrixEditors çağır (sadece bağlı olmayanlar için)
          this.initTrixEditors(this.$root);
        }, 100);
      };
      document.addEventListener('livewire:upload-finish', this.onUploadFinish);
      document.addEventListener('livewire:updated', this.onLWUpdated);

      // 5) Cleanup garantisi
      this.$root.addEventListener('alpine:destroy', () => this.cleanup(), { once: true });
    },

    cleanup() {
      if (this.mo) { this.mo.disconnect(); this.mo = null; }
      if (this.onUploadFinish) document.removeEventListener('livewire:upload-finish', this.onUploadFinish);
      if (this.onLWUpdated) document.removeEventListener('livewire:updated', this.onLWUpdated);
      this.trixTimers.forEach((t) => clearTimeout(t));
      this.trixTimers.clear();
    },

    isOurEditor(el) {
      return el && el.tagName?.toLowerCase() === 'trix-editor' && el.id?.startsWith('description-editor-');
    },

    attachTrix(el, forceDesc = null) {
      if (!this.isOurEditor(el)) return;

      const id = el.id;
      const saved = localStorage.getItem(`trix:${id}`);
      // Eğer forceDesc parametresi verilmişse onu kullan, yoksa attribute'dan oku
      const dataDesc = forceDesc !== null ? forceDesc : (el.getAttribute('data-description') || '');
      // Öncelik: data-description (veritabanından gelen), sonra localStorage (kullanıcı yazmışsa)
      const initial = dataDesc || saved || '';

      // Eğer forceDesc verilmişse veya zaten bağlıysa, data-description değişmişse her zaman güncelle
      if (el.dataset._bound === '1' || forceDesc !== null) {
        const lastDesc = el.dataset._lastDesc || '';
        // Eğer data-description değişmişse veya forceDesc verilmişse, içeriği güncelle (boş olsa bile)
        if (forceDesc !== null || dataDesc !== lastDesc) {
          // Trix editor'ın hazır olmasını bekle
          const loadContent = () => {
            try {
              if (el.editor && typeof el.editor.loadHTML === 'function') {
                el.editor.loadHTML(dataDesc || '');
              } else if (el.trix && typeof el.trix.loadHTML === 'function') {
                el.trix.loadHTML(dataDesc || '');
              } else {
                el.innerHTML = dataDesc || '';
              }
            }
            catch (_) {
              el.innerHTML = dataDesc || '';
            }
          };

          // Trix editor hazır değilse bekle
          if (!el.editor && !el.trix) {
            setTimeout(() => {
              requestAnimationFrame(loadContent);
            }, 100);
          } else {
            requestAnimationFrame(loadContent);
          }

          el.dataset._lastDesc = dataDesc;
        }
        // forceDesc verilmişse _bound flag'ini set et (yeniden bağlama için)
        if (forceDesc !== null) {
          el.dataset._bound = '1';
        }
        return;
      }

      // İlk bağlama
      const loadInitial = () => {
        try {
          if (el.editor && typeof el.editor.loadHTML === 'function') {
            el.editor.loadHTML(initial);
          } else if (el.trix && typeof el.trix.loadHTML === 'function') {
            el.trix.loadHTML(initial);
          } else {
            el.innerHTML = initial;
          }
        }
        catch (_) {
          el.innerHTML = initial;
        }
      };

      // Trix editor hazır değilse bekle
      if (!el.editor && !el.trix) {
        setTimeout(() => {
          requestAnimationFrame(loadInitial);
        }, 100);
      } else {
        requestAnimationFrame(loadInitial);
      }

      el.dataset._bound = '1';
      el.dataset._lastDesc = dataDesc;
    },

    initTrixEditors(scope) {
      scope.querySelectorAll('trix-editor[id^="description-editor-"]')
           .forEach((el) => {
             // Her zaman attachTrix çağır - içinde zaten gerekli kontroller yapılıyor
             // Eğer zaten bağlıysa ve data-description değişmemişse, attachTrix içinde return edilecek
             this.attachTrix(el);
           });
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
