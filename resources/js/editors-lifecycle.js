// Editor Lifecycle Manager - Trumbowyg & Trix
// Editör başlatma için tek kaynak
const __edState = { trumbowyg: [], trix: [], timers: [], unsubs: [], observers: [] };

/**
 * Trumbowyg editörlerini teardown et
 * Sadece textarea elementlerini hedefler ve destroy eder
 * textarea[data-editor="trumbowyg"] VEYA textarea.trumbowyg selector'larını kullan
 */
const teardownTrumbowyg = () => {
  if (!window.jQuery || !jQuery.fn || typeof jQuery.fn.trumbowyg !== 'function') {
    return;
  }

  try {
    // textarea[data-editor="trumbowyg"] VEYA textarea.trumbowyg selector'larını kullan
    jQuery('textarea[data-editor="trumbowyg"][data-editor-mounted="1"], textarea.trumbowyg[data-editor-mounted="1"]').each(function () {
      const $el = jQuery(this);
      try {
        if ($el.data('trumbowyg')) {
          $el.trumbowyg('destroy');
        }
      } catch (e) {
        if (import.meta.env.DEV) console.warn('Trumbowyg destroy error', e);
      }
      this.removeAttribute('data-editor-mounted');
    });
  } catch (e) {
    if (import.meta.env.DEV) {
      console.warn('Trumbowyg teardown error:', e);
    }
  }
};

/**
 * Temizleme fonksiyonu - editörleri yok eder ve mount edilmiş flag'leri kaldırır
 * @param {boolean} destroyEditors - Editörleri tamamen destroy et (default: true)
 */
const edTeardown = (destroyEditors = true) => {
  // Trumbowyg teardown
  if (destroyEditors) {
    teardownTrumbowyg();
  } else {
    // Sadece flag'leri kaldır
    try {
      if (window.jQuery) {
        jQuery('textarea[data-editor="trumbowyg"][data-editor-mounted="1"], textarea.trumbowyg[data-editor-mounted="1"]').each(function () {
          this.removeAttribute('data-editor-mounted');
        });
      }
    } catch (e) {
      if (import.meta.env.DEV) {
        console.warn('Trumbowyg flag cleanup error:', e);
      }
    }
  }
  __edState.trumbowyg = [];

  try {
    __edState.trix.forEach(el => {
      // Çift toolbar kontrolü
      if (el.querySelector) {
        const toolbars = document.querySelectorAll('.trix-toolbar');
        if (toolbars.length > 1) {
          Array.from(toolbars).slice(1).forEach(toolbar => {
            const editor = toolbar.closest('trix-editor');
            if (editor && editor !== el) {
              toolbar.remove();
            }
          });
        }
      }
      el.removeAttribute('data-editor-mounted');
    });
    __edState.trix = [];
  } catch(e){
    if (import.meta.env.DEV) {
      console.warn('Trix teardown error:', e);
    }
  }

  __edState.timers.forEach(t => {
    try {
      clearInterval(t);
    } catch(e) {}
  });
  __edState.timers = [];

  __edState.observers.forEach(obs => {
    try {
      obs.disconnect();
    } catch(e) {
      if (import.meta.env.DEV) {
        console.warn('Observer disconnect error:', e);
      }
    }
  });
  __edState.observers = [];

  __edState.unsubs.forEach(fn => {
    try { fn(); } catch(e){
      if (import.meta.env.DEV) {
        console.warn('Unsubscribe error:', e);
      }
    }
  });
  __edState.unsubs = [];
};

/**
 * Trumbowyg editörlerini başlat
 * textarea[data-editor="trumbowyg"] VEYA textarea.trumbowyg selector'larını kullan
 * Geriye dönük uyumluluk için class="trumbowyg" da desteklenir
 * Plugin'in ürettiği DOM elementlerini asla hedeflemez
 */
const initTrumbowyg = () => {
  if (!window.jQuery || !jQuery.fn || typeof jQuery.fn.trumbowyg !== 'function') {
    if (import.meta.env.DEV) {
      console.warn('Trumbowyg başlatılamadı - jQuery veya Trumbowyg plugin mevcut değil');
    }
    return;
  }

  // textarea[data-editor="trumbowyg"] VEYA textarea.trumbowyg selector'larını kullan
  // Geriye dönük uyumluluk için class="trumbowyg" da desteklenir
  jQuery('textarea[data-editor="trumbowyg"], textarea.trumbowyg').each(function() {
    const $el = jQuery(this);
    const el = this;

    // Double-init guard: Zaten mount edilmişse atla
    if (el.getAttribute('data-editor-mounted') === '1') {
      return;
    }

    // Eğer zaten Trumbowyg instance'ı varsa, önce destroy et (güvenlik amaçlı)
    if ($el.data('trumbowyg')) {
      try {
        $el.trumbowyg('destroy');
      } catch(e) {
        if (import.meta.env.DEV) {
          console.warn('Trumbowyg destroy error:', e);
        }
      }
    }

    // Mount edildi olarak işaretle
    el.setAttribute('data-editor-mounted', '1');

    // Varsayılan config - window.__TRUMBOWYG_CONFIG ile geçersiz kılınabilir
    // SVG path is set globally via $.trumbowyg.svgPath in app.js
    // No need to set it in config - Trumbowyg uses the global svgPath
    const config = window.__TRUMBOWYG_CONFIG || {
      lang: 'tr',
      btns: [
        ['viewHTML'],
        ['undo', 'redo'],
        ['strong', 'em', 'del'],
        ['link'],
        ['insertImage'],
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
        ['unorderedList', 'orderedList'],
        ['horizontalRule'],
        ['removeformat'],
        ['fullscreen']
      ],
      autogrow: false,
      fixedHeight: 400,
      fullscreen: true,
      events: {
        'tbwinit': function() {
          // Trumbowyg initialized
        },
        'tbwchange': function() {
          const element = this;
          // Livewire entegrasyonu - dispatch input event for wire:model bindings
          if (element.hasAttribute('wire:model') || element.hasAttribute('wire:change') || element.hasAttribute('wire:model.live')) {
            element.dispatchEvent(new Event('input', { bubbles: true }));
          }

          // Always sync directly to Livewire component (wire:ignore içinde wire:model çalışmayabilir)
          const wireId = element.closest('[wire\\:id]')?.getAttribute('wire:id');
          if (wireId && window.Livewire && window.jQuery) {
            const livewireComponent = window.Livewire.find(wireId);
            if (livewireComponent && window.jQuery(element).data('trumbowyg')) {
              const content = window.jQuery(element).trumbowyg('html');
              // Only update if content actually changed (avoid infinite loops)
              const currentValue = livewireComponent.get('content');
              if (content !== currentValue) {
                // Use call() method to trigger contentUpdated listener (more reliable than set())
                try {
                  livewireComponent.call('contentUpdated', content);
                } catch (e) {
                  // Fallback to set() if call() fails
                  livewireComponent.set('content', content, false);
                }
              }
            }
          }
        },
        'tbwfullscreenenter': function() {
          document.body.classList.add('trumbowyg-fullscreen');
          // Sidebar'ı gizle
          const sidebar = document.querySelector('aside');
          if (sidebar) {
            sidebar.style.display = 'none';
          }
        },
        'tbwfullscreenexit': function() {
          document.body.classList.remove('trumbowyg-fullscreen');
          // Sidebar'ı göster
          const sidebar = document.querySelector('aside');
          if (sidebar) {
            sidebar.style.display = 'block';
          }
        }
      }
    };

    try {
      $el.trumbowyg(config);
      __edState.trumbowyg.push($el);
    } catch(e) {
      if (import.meta.env.DEV) {
        console.error('Trumbowyg init error:', e);
      }
      el.removeAttribute('data-editor-mounted');
    }
  });
};

/**
 * Trix editörlerini başlat
 * [data-editor="trix"] veya trix-editor tag elementlerini hedefler
 * Posts modülü editörlerini hariç tutar (description-editor-*)
 */
const initTrix = () => {
  // Hem data-editor attribute'u hem de trix-editor tag'ini destekle
  document.querySelectorAll('[data-editor="trix"], trix-editor').forEach(el => {
    // Posts modülü editörlerini atla (kendi lifecycle'ları var)
    if (el.id && el.id.startsWith('description-editor-')) {
      return;
    }

    // Double-init guard: Zaten mount edilmişse atla
    if (el.dataset.editorMounted === '1' || el.dataset.trixMounted === '1') {
      return;
    }

    // Çift toolbar kontrolü - eğer bu editör için zaten toolbar varsa atla
    const existingToolbar = el.querySelector('.trix-toolbar');
    if (existingToolbar) {
      // Eğer bu editör için birden fazla toolbar varsa, fazlalıkları kaldır
      const allToolbars = el.querySelectorAll('.trix-toolbar');
      if (allToolbars.length > 1) {
        Array.from(allToolbars).slice(1).forEach(toolbar => toolbar.remove());
      }
      // Zaten toolbar varsa ve mount edilmemişse, sadece flag'i set et
      if (!el.dataset.editorMounted) {
        el.dataset.editorMounted = '1';
        return;
      }
    }

    // Mount edildi olarak işaretle
    el.dataset.editorMounted = '1';

    // Varsa başlangıç içeriğini yükle
    // Hem data-initial-content hem de data-description attribute'larını destekle
    const initialContent = el.getAttribute('data-initial-content') || el.getAttribute('data-description');
    if (initialContent && initialContent.trim() !== '') {
      const loadContent = () => {
        if (el.trix) {
          try {
            el.trix.loadHTML(initialContent);
          } catch(e) {
            if (import.meta.env.DEV) {
              console.error('Trix loadHTML hatası:', e);
            }
          }
        } else {
          // Kısa bir gecikme sonrası tekrar dene
          setTimeout(loadContent, 100);
        }
      };
      loadContent();
    }

    // Livewire entegrasyonu - change event'lerini dispatch et
    const onChange = (e) => {
      if (el.hasAttribute('wire:change')) {
        el.dispatchEvent(new Event('change', { bubbles: true }));
      }
    };
    el.addEventListener('trix-change', onChange);
    __edState.unsubs.push(() => el.removeEventListener('trix-change', onChange));

    // Trix'i özelleştir - attach fonksiyonunu kaldır
    const onInitialize = () => {
      // Varsa içeriği yükle (Trix daha sonra başlatılabilir)
      const contentToLoad = el.getAttribute('data-initial-content') || el.getAttribute('data-description');
      if (contentToLoad && contentToLoad.trim() !== '' && el.trix) {
        try {
          el.trix.loadHTML(contentToLoad);
        } catch(e) {
          if (import.meta.env.DEV) {
            console.error('Trix loadHTML error in initialize:', e);
          }
        }
      }

      // Toolbar'dan dosya araçlarını kaldır
      const toolbar = el.querySelector('.trix-toolbar');
      if (toolbar) {
        const fileToolsGroup = toolbar.querySelector('.trix-button-group--file-tools');
        if (fileToolsGroup) {
          fileToolsGroup.remove();
        }

        const attachButtons = toolbar.querySelectorAll(
          '.trix-button--icon-attach, ' +
          '[data-trix-action="attachFiles"], ' +
          '[title="Attach Files"]'
        );
        attachButtons.forEach(btn => btn.remove());
      }
    };
    el.addEventListener('trix-initialize', onInitialize);
    __edState.unsubs.push(() => el.removeEventListener('trix-initialize', onInitialize));

    // Attachment event'lerini engelle
    const preventAttach = (e) => {
      e.preventDefault();
      e.stopPropagation();
      return false;
    };
    el.addEventListener('trix-attachment-add', preventAttach);
    el.addEventListener('trix-attachment-remove', preventAttach);
    __edState.unsubs.push(() => {
      el.removeEventListener('trix-attachment-add', preventAttach);
      el.removeEventListener('trix-attachment-remove', preventAttach);
    });

    // MutationObserver ile attach butonlarını izle ve kaldır
    const toolbar = el.querySelector('.trix-toolbar');
    if (toolbar) {
      const observer = new MutationObserver(() => {
        const fileToolsGroup = toolbar.querySelector('.trix-button-group--file-tools');
        if (fileToolsGroup) {
          fileToolsGroup.remove();
        }

        const attachButtons = toolbar.querySelectorAll(
          '.trix-button--icon-attach, ' +
          '[data-trix-action="attachFiles"], ' +
          '[title="Attach Files"]'
        );
        attachButtons.forEach(btn => btn.remove());
      });

      observer.observe(toolbar, {
        childList: true,
        subtree: true
      });

      __edState.observers.push(observer);

      // 15 saniye sonra observer'ı durdur (güvenlik için)
      setTimeout(() => {
        observer.disconnect();
        const index = __edState.observers.indexOf(observer);
        if (index > -1) {
          __edState.observers.splice(index, 1);
        }
      }, 15000);
    }

    __edState.trix.push(el);
  });
};

/**
 * Tüm editörleri yeniden bağla
 * İdempotent - zaten mount edilmiş editörleri başlatmaz
 * Livewire güncellemelerinde önce temizle, sonra başlat
 */
const edRebind = (shouldTeardown = false) => {
  // Sayfada editör yoksa erken çık
  const hasTrix = document.querySelector('[data-editor="trix"], trix-editor');
  const hasTrumbowyg = (window.jQuery && (jQuery('textarea[data-editor="trumbowyg"]').length > 0 || jQuery('textarea.trumbowyg').length > 0));

  if (!hasTrix && !hasTrumbowyg) {
    return;
  }

  // Livewire güncellemelerinde önce mevcut editörleri temizle
  if (shouldTeardown) {
    // Trumbowyg için teardown
    teardownTrumbowyg();

    // Trix için mount flag'lerini kaldır
    try {
      document.querySelectorAll('[data-editor="trix"][data-editor-mounted="1"], trix-editor[data-editor-mounted="1"]').forEach(el => {
        el.removeAttribute('data-editor-mounted');
      });
    } catch(e) {
      if (import.meta.env.DEV) {
        console.warn('Trix flag cleanup warning:', e);
      }
    }
  }

  initTrumbowyg();
  initTrix();
};

/**
 * requestAnimationFrame kullanarak editör yeniden bağlamayı zamanla
 * Birden fazla hızlı çağrıyı önler
 */
let __edTimer = null;
const edSchedule = () => {
  if (__edTimer) {
    cancelAnimationFrame(__edTimer);
  }
  __edTimer = requestAnimationFrame(() => edRebind());
};

/**
 * Editör lifecycle manager'ı mount et
 * DOMContentLoaded ve Livewire event listener'larını ayarlar
 * İdempotent - birden fazla kez çağrılsa da event listener'ları tekrar eklemez
 */
export function mountEditorsLifecycle() {
  if (window.__editorsLifecycleMounted) {
    return;
  }

  window.__editorsLifecycleMounted = true;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      edSchedule();
    }, { once: true });
  } else {
    edSchedule();
  }

  document.addEventListener('livewire:navigated', () => {
    edSchedule();
  });

  document.addEventListener('livewire:navigating', () => {
    edTeardown();
  });

  // Livewire component güncellemelerinde editörleri yeniden başlat
  // Debounce ile çoklu güncellemeleri önle
  let __livewireUpdateTimer = null;
  document.addEventListener('livewire:updated', () => {
    // Sayfada editör var mı kontrol et
    const pageHasEditor = document.querySelector('textarea[data-editor="trumbowyg"], textarea.trumbowyg, [data-editor="trix"], trix-editor');
    if (!pageHasEditor) {
      return;
    }

    // Debounce: 150ms içinde birden fazla güncelleme gelirse sadece sonuncusunu çalıştır
    if (__livewireUpdateTimer) {
      clearTimeout(__livewireUpdateTimer);
    }
    __livewireUpdateTimer = setTimeout(() => {
      edRebind(true); // shouldTeardown = true
      __livewireUpdateTimer = null;
    }, 150);
  });
}
