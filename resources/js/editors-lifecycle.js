// Editor Lifecycle Manager - Trumbowyg
// Editör başlatma için tek kaynak
const __edState = { trumbowyg: [], timers: [], unsubs: [] };

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

  __edState.timers.forEach(t => {
    try {
      clearInterval(t);
    } catch(e) {}
  });
  __edState.timers = [];

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

    // İlk içeriği yükle - textarea içeriği zaten Blade'de {!! !!} ile render ediliyor
    let initialContent = el.value || el.innerHTML || '';

    // Eğer textarea boşsa, data-initial-content attribute'undan al
    if (!initialContent || initialContent.trim() === '') {
      initialContent = el.getAttribute('data-initial-content') || '';
      // HTML entity'leri decode et (eğer varsa)
      if (initialContent && (initialContent.includes('&lt;') || initialContent.includes('&amp;'))) {
        const tempDiv = document.createElement('textarea');
        tempDiv.innerHTML = initialContent;
        initialContent = tempDiv.value;
      }
    }

    // Initial content'i textarea'ya set et (Trumbowyg init'ten önce)
    if (initialContent && initialContent.trim() !== '') {
      el.value = initialContent;
    }

    // wire:model attribute'unu tespit et (wire:model, wire:model.live, wire:model.debounce.* vb.)
    let propertyName = 'content'; // Fallback
    const wireModelAttrs = ['wire:model', 'wire:model.live', 'wire:model.debounce'];
    for (const attrName of wireModelAttrs) {
      if (el.hasAttribute(attrName)) {
        propertyName = el.getAttribute(attrName) || propertyName;
        break;
      }
    }
    // Eğer yukarıdakilerden biri bulunamazsa, tüm attribute'ları tara
    if (propertyName === 'content') {
      const allAttrs = Array.from(el.attributes);
      const wireModelAttr = allAttrs.find(attr => attr.name.startsWith('wire:model'));
      if (wireModelAttr) {
        propertyName = wireModelAttr.value || propertyName;
      }
    }

    // Varsayılan config - window.__TRUMBOWYG_CONFIG ile geçersiz kılınabilir
    // SVG path is set globally via $.trumbowyg.svgPath in app.js
    // No need to set it in config - Trumbowyg uses the global svgPath

    // Sade ve tutarlı toolbar preset - haber içerikleri için
    const defaultButtons = [
      ['viewHTML'],
      ['undo', 'redo'],
      ['formatting'],
      ['strong', 'em', 'del'],
      ['superscript', 'subscript'],
      ['link'],
      ['insertImage'],
      ['justifyLeft', 'justifyCenter', 'justifyRight'],
      ['unorderedList', 'orderedList'],
      ['horizontalRule'],
      ['removeformat'],
      ['fullscreen']
    ];

    // Galeri açıklamaları için minimal toolbar (data-file-id varsa)
    const fileId = el.getAttribute('data-file-id');
    const isGalleryDescription = !!fileId;

    const minimalButtons = [
      ['strong', 'em'],
      ['link'],
      ['unorderedList', 'orderedList'],
      ['removeformat']
    ];

    // Create post'taki gibi basit config - galeri açıklamaları için de aynı
    // Sadece toolbar butonları farklı (minimalButtons vs defaultButtons)
    const config = window.__TRUMBOWYG_CONFIG || {
      lang: 'tr',
      btns: isGalleryDescription ? minimalButtons : defaultButtons,
      autogrow: isGalleryDescription ? true : false,
      fixedHeight: isGalleryDescription ? 120 : 400,
      fullscreen: !isGalleryDescription,
      semantic: false,
      removeformatPasted: true,
      events: {
        'tbwinit': function() {
          // Create post'taki gibi - hiçbir şey yapmıyoruz
          // Trumbowyg zaten textarea'dan içeriği otomatik yükler
        },
        'tbwchange': function() {
          const element = this;
          const $el = jQuery(element);
          let content = $el.trumbowyg('html') || '';

          // Normalize content: null/undefined ise boş string yap
          if (content === null || content === undefined) {
            content = '';
          }
          // String'e çevir (güvenlik için)
          content = String(content || '');

          // ÖNEMLİ: Trumbowyg textarea'nın value'sunu manuel olarak güncelle
          // Trumbowyg otomatik olarak textarea'yı güncellemez, bu yüzden manuel yapmalıyız
          if (element && element.tagName === 'TEXTAREA') {
            element.value = content;
            // Input event'i de tetikle (Livewire için)
            element.dispatchEvent(new Event('input', { bubbles: true }));
          }

          // Livewire component'i bul - wire:ignore içindeki elementler için daha geniş arama
          // ÖNEMLİ: Nested Livewire component'ler için (örneğin post-edit-media nested in post-edit)
          let wireId = element.closest('[wire\\:id]')?.getAttribute('wire:id');

          // Eğer wire:ignore içindeyse, parent'larda ara
          if (!wireId && element.closest('[wire\\:ignore]')) {
            const wireIgnoreParent = element.closest('[wire\\:ignore]');
            wireId = wireIgnoreParent.closest('[wire\\:id]')?.getAttribute('wire:id');
          }

          // Nested component için: Tüm parent'larda wire:id ara
          if (!wireId) {
            let parent = element.parentElement;
            let depth = 0;
            const maxDepth = 20; // Güvenlik için maksimum derinlik
            while (parent && parent !== document.body && depth < maxDepth) {
              const parentWireId = parent.getAttribute('wire:id');
              if (parentWireId) {
                wireId = parentWireId;
                break;
              }
              parent = parent.parentElement;
              depth++;
            }
          }

          // Son çare: document'te tüm Livewire component'lerini ara
          // ÖNEMLİ: data-file-id varsa, bu bir galeri açıklaması demektir
          // Bu durumda post-edit-media component'ini bulmalıyız
          if (!wireId && window.Livewire && element.dataset.fileId) {
            const allWireComponents = document.querySelectorAll('[wire\\:id]');
            if (allWireComponents.length > 0) {
              // Tüm component'leri kontrol et ve post-edit-media'yı bul
              for (const wireEl of allWireComponents) {
                const candidateWireId = wireEl.getAttribute('wire:id');
                if (candidateWireId) {
                  const candidateComponent = window.Livewire.find(candidateWireId);
                  // Eğer component'te updateFileById metodu varsa, bu bizim component'imiz
                  if (candidateComponent && typeof candidateComponent.call === 'function') {
                    // Test et: updateFileById metodu var mı?
                    try {
                      // Sadece kontrol için, gerçek çağrı yapmadan
                      if (candidateComponent.get('existingFiles') !== undefined ||
                          candidateComponent.get('post') !== undefined) {
                        wireId = candidateWireId;
                        break;
                      }
                    } catch (e) {
                      // Devam et
                    }
                  }
                }
              }

              // Eğer hala bulunamadıysa, en yakın parent'taki component'i kullan
              if (!wireId) {
                let parent = element.parentElement;
                while (parent && parent !== document.body) {
                  const parentWireId = parent.getAttribute('wire:id');
                  if (parentWireId) {
                    wireId = parentWireId;
                    break;
                  }
                  parent = parent.parentElement;
                }
              }
            }
          }

          const hasLivewire = wireId && window.Livewire;
          const component = hasLivewire ? window.Livewire.find(wireId) : null;

          // 1) Galeri açıklaması senaryosu: data-file-id + data-field varsa updateFileById kullan
          const fileId = element.dataset.fileId;
          const field = element.dataset.field;

          if (component && fileId && field) {
            try {
              component.call('updateFileById', fileId, field, content);
            } catch (e) {
              if (import.meta.env.DEV) {
                console.error('Trumbowyg gallery sync error', { fileId, field, content, e });
              }
            }
            return; // Galeri senaryosunda erken çık
          } else {
            // DEBUG: Neden galeri senaryosu çalışmadı?
            if (import.meta.env.DEV) {
              console.warn('DEBUG gallery tbwchange: Galeri senaryosu atlandı', {
                hasComponent: !!component,
                hasFileId: !!fileId,
                hasField: !!field,
                fileId,
                field,
                wireId,
              });
            }
          }

          // 2) Normal wire:model senaryosu (content vs.)
          const wireModelAttr = Array.from(element.attributes).find(attr => attr.name.startsWith('wire:model'));
          const hasWireModel = !!wireModelAttr;
          const modelName = wireModelAttr?.value || null;

          // Livewire entegrasyonu - dispatch input event for wire:model bindings
          if (hasWireModel || element.hasAttribute('wire:change') || element.hasAttribute('wire:model.live')) {
            element.dispatchEvent(new Event('input', { bubbles: true }));
          }

          if (component && hasWireModel) {
            const prop = modelName || 'content';
            try {
              const currentValue = component.get(prop) || '';
              // content zaten normalize edildi (yukarıda)
              if (currentValue !== content) {
                component.set(prop, content, false);
              }
            } catch (e) {
              if (import.meta.env.DEV) {
                console.warn('Trumbowyg Livewire sync error', { prop, content, e });
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

      // EK: Trumbowyg'in contenteditable div'ine direkt event listener ekle
      // Çünkü bazen tbwchange event'i tetiklenmeyebilir
      if (isGalleryDescription) {
        // Trumbowyg'in DOM yapısı: .trumbowyg-box > .trumbowyg-editor-box > .trumbowyg-editor
        // textarea'nın parent'ı .trumbowyg-box
        const $trumbowygBox = $el.closest('.trumbowyg-box') || $el.parent('.trumbowyg-box');
        const $editor = $trumbowygBox.find('.trumbowyg-editor');

        if ($editor.length > 0) {
          // Input, keyup, paste, blur event'lerini dinle
          $editor.on('input keyup paste blur', function() {
            // Debounce için setTimeout kullan
            clearTimeout($editor.data('tbwchange-timeout'));
            const timeout = setTimeout(function() {
              // tbwchange event'ini manuel tetikle
              // Trumbowyg'in event sistemini kullan
              if ($el.data('trumbowyg')) {
                // Trumbowyg'den HTML'i al
                let content = $el.trumbowyg('html') || '';

                // tbwchange handler'ını manuel çağır
                // config.events['tbwchange'] fonksiyonunu çağırmak yerine
                // Direkt olarak handler kodunu çalıştır
                const element = el;
                const fileId = element.getAttribute('data-file-id');
                const field = element.getAttribute('data-field');

                if (fileId && field) {
                  // Livewire component'i bul
                  let wireId = element.closest('[wire\\:id]')?.getAttribute('wire:id');

                  if (!wireId && element.closest('[wire\\:ignore]')) {
                    const wireIgnoreParent = element.closest('[wire\\:ignore]');
                    wireId = wireIgnoreParent.closest('[wire\\:id]')?.getAttribute('wire:id');
                  }

                  if (!wireId) {
                    let parent = element.parentElement;
                    let depth = 0;
                    const maxDepth = 20;
                    while (parent && parent !== document.body && depth < maxDepth) {
                      const parentWireId = parent.getAttribute('wire:id');
                      if (parentWireId) {
                        wireId = parentWireId;
                        break;
                      }
                      parent = parent.parentElement;
                      depth++;
                    }
                  }

                  const hasLivewire = wireId && window.Livewire;
                  const component = hasLivewire ? window.Livewire.find(wireId) : null;

                  if (component && fileId && field) {
                    try {
                      component.call('updateFileById', fileId, field, String(content || ''));
                    } catch (e) {
                      if (import.meta.env.DEV) {
                        console.error('Trumbowyg updateFileById error', { fileId, field, e });
                      }
                    }
                  }
                }

                // Ayrıca normal trigger'ı da dene
                $el.trigger('tbwchange');
              }
            }, 300);
            $editor.data('tbwchange-timeout', timeout);
          });
        }
      }
    } catch(e) {
      if (import.meta.env.DEV) {
        console.error('Trumbowyg init error:', e);
      }
      el.removeAttribute('data-editor-mounted');
    }
  });
};

// Trix kaldırıldı - artık sadece Trumbowyg kullanılıyor

/**
 * Tüm editörleri yeniden bağla
 * İdempotent - zaten mount edilmiş editörleri başlatmaz
 * Livewire güncellemelerinde önce temizle, sonra başlat
 */
const edRebind = (shouldTeardown = false) => {
  // Sayfada editör yoksa erken çık
  const hasTrumbowyg = (window.jQuery && (jQuery('textarea[data-editor="trumbowyg"]').length > 0 || jQuery('textarea.trumbowyg').length > 0));

  if (!hasTrumbowyg) {
    return;
  }

  // Livewire güncellemelerinde önce mevcut editörleri temizle
  if (shouldTeardown) {
    // Trumbowyg için teardown
    teardownTrumbowyg();
  }

  initTrumbowyg();
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

  // Livewire component init olduğunda editörleri başlat
  document.addEventListener('livewire:init', () => {
    edSchedule();
  });

  // Livewire component güncellemelerinde editörleri yeniden başlat
  // Debounce ile çoklu güncellemeleri önle
  let __livewireUpdateTimer = null;
  document.addEventListener('livewire:updated', () => {
    // Sayfada editör var mı kontrol et - daha agresif kontrol
    const pageHasEditor = document.querySelector('textarea[data-editor="trumbowyg"], textarea.trumbowyg');

    // Eğer editör yoksa, biraz bekle ve tekrar kontrol et (yeni DOM elementleri için)
    if (!pageHasEditor) {
      // 200ms sonra tekrar kontrol et - yeni dosyalar yüklendiğinde DOM gecikmeli olabilir
      setTimeout(() => {
        const retryCheck = document.querySelector('textarea[data-editor="trumbowyg"], textarea.trumbowyg');
        if (retryCheck) {
          edSchedule();
        }
      }, 200);
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

  // Livewire component'lerin DOM'a eklendiğinde de kontrol et
  // MutationObserver ile yeni textarea'ları izle (sadece wire:ignore içindeki değişiklikler için)
  if (typeof MutationObserver !== 'undefined') {
    let __mutationTimer = null;
    const observer = new MutationObserver((mutations) => {
      let shouldRebind = false;
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1) { // Element node
            // Yeni eklenen node veya child'larında textarea var mı kontrol et
            if (node.matches && node.matches('textarea[data-editor="trumbowyg"], textarea.trumbowyg')) {
              shouldRebind = true;
            } else if (node.querySelector && node.querySelector('textarea[data-editor="trumbowyg"], textarea.trumbowyg')) {
              shouldRebind = true;
            }
          }
        });
      });
      if (shouldRebind) {
        // Debounce: 100ms içinde birden fazla mutation gelirse sadece sonuncusunu çalıştır
        if (__mutationTimer) {
          clearTimeout(__mutationTimer);
        }
        __mutationTimer = setTimeout(() => {
          // Kısa bir gecikme ile başlat (DOM tamamen render edilsin)
          setTimeout(() => {
            edSchedule();
          }, 50);
          __mutationTimer = null;
        }, 100);
      }
    });

    // Observer'ı başlat - sadece wire:ignore içindeki değişiklikleri izle
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });

    // Observer'ı cleanup için sakla
    __edState.observer = observer;
  }
}
