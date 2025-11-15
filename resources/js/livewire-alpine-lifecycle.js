// Livewire + Alpine Lifecycle Manager
// Livewire SPA navigasyonu ve Alpine.js başlatma için merkezi lifecycle yönetimi

/**
 * Modül başlatma kayıt defteri
 * Modüller init fonksiyonlarını buraya kaydedebilir
 * Tüm init fonksiyonları idempotent olmalı (birden fazla kez çağrılabilir)
 */
const moduleInitRegistry = {};

/**
 * Bir modül init fonksiyonunu kaydet
 * Aynı moduleName için tekrar çağrılırsa son tanımı ezer (bilinçli tercih)
 * @param {string} moduleName - Modül adı
 * @param {Function} initFn - Başlatma fonksiyonu (idempotent olmalı)
 */
export function registerModuleInit(moduleName, initFn) {
  if (typeof initFn === 'function') {
    moduleInitRegistry[moduleName] = initFn;
  }
}

/**
 * Tüm kayıtlı modülleri başlat
 * İdempotent - birden fazla kez çağrılması güvenli
 * Her init fonksiyonu kendi içinde idempotent olmalı
 */
function initAllModules() {
  Object.values(moduleInitRegistry).forEach(initFn => {
    if (typeof initFn === 'function') {
      try {
        initFn();
      } catch (error) {
        if (import.meta.env.DEV) {
          console.error('Modül başlatma hatası:', error);
        }
      }
    }
  });
}

/**
 * Alpine.js global store'ları ve bileşenlerini başlat
 * alpine:init event'inde bir kez çağrılır
 * Not: Alpine global component'ler (adminApp, loginApp, moduleManagement) app.js'de tanımlı
 */
function initAlpineGlobals() {
  if (window.Alpine && !window.__alpineGlobalsInitialized) {
    window.__alpineGlobalsInitialized = true;
    // Gelecekte ek Alpine kurulumu buraya eklenebilir
  }
}

/**
 * Alpine root component'ini yeniden başlat
 * Livewire navigasyonu sonrasında root x-data component'inin state'ini korur
 */
function reinitAlpineRoot() {
  if (!window.Alpine || !document.documentElement || !window.adminApp) {
    return;
  }

  try {
    const rootData = document.documentElement.getAttribute('x-data');
    const rootInit = document.documentElement.getAttribute('x-init');

    // Sadece adminApp component'i için re-init yap
    if (!rootData || (!rootData.includes('adminApp') && rootData !== 'adminApp()')) {
      return;
    }

    // Mevcut component data'sını al
    let componentData = null;
    try {
      if (window.Alpine.$data) {
        componentData = window.Alpine.$data(document.documentElement);
      }
    } catch (e) {
      // Component data yoksa devam et
    }

    // Component data varsa sadece init() metodunu çağır
    if (componentData && typeof componentData.init === 'function') {
      componentData.init();
      return;
    }

    // Component data yoksa attribute'ları yeniden set ederek başlat
    document.documentElement.removeAttribute('x-data');
    if (rootInit) {
      document.documentElement.removeAttribute('x-init');
    }

    document.documentElement.setAttribute('x-data', rootData);
    if (rootInit) {
      document.documentElement.setAttribute('x-init', rootInit);
    }

    // Alpine'in initTree metodunu kullanarak root'u yeniden işle
    if (typeof window.Alpine.initTree === 'function') {
      window.Alpine.initTree(document.documentElement);
    } else if (typeof window.Alpine.init === 'function') {
      window.Alpine.init(document.documentElement);
    }
  } catch (e) {
    if (import.meta.env.DEV) {
      console.error('Alpine root component re-init error:', e);
    }
  }
}

/**
 * Livewire navigasyonunu yönet
 * SPA navigasyonundan sonra UI bileşenlerini yeniden başlatır
 */
function handleLivewireNavigated() {
  // Önce modülleri başlat
  initAllModules();

  // Alpine root component'ini yeniden başlat
  // queueMicrotask kullanarak Livewire'ın expression evaluation'ından önce çalıştırıyoruz
  queueMicrotask(reinitAlpineRoot);
}

/**
 * Livewire güncellemelerini yönet (debounced)
 * Livewire bileşen güncellemelerinden sonra UI bileşenlerini yeniden başlatır
 * Gereksiz tekrarları engellemek için debounce kullanılır
 */
let __updateTimer = null;
function handleLivewireUpdated() {
  // Debounce: 100ms içinde birden fazla çağrı gelirse sadece sonuncusunu çalıştır
  if (__updateTimer) {
    clearTimeout(__updateTimer);
  }
  __updateTimer = setTimeout(() => {
    // Livewire güncellemelerine yanıt vermesi gereken tüm modülleri yeniden başlat
    initAllModules();
    __updateTimer = null;
  }, 100);
}

/**
 * Livewire + Alpine lifecycle manager'ı mount et
 * DOMContentLoaded, alpine:init ve livewire event'leri için listener'ları ayarlar
 * İdempotent - birden fazla kez çağrılsa da event listener'ları tekrar eklemez
 */
export function mountLivewireAlpineLifecycle() {
  // Zaten mount edilmişse tekrar mount etme
  if (window.__livewireAlpineLifecycleMounted) {
    return;
  }

  window.__livewireAlpineLifecycleMounted = true;

  // Alpine başlatma (bir kez)
  document.addEventListener('alpine:init', () => {
    initAlpineGlobals();
  }, { once: true });

  // İlk DOM yükleme
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initAllModules();
    }, { once: true });
  } else {
    // DOM zaten yüklendi
    initAllModules();
  }

  // Livewire SPA navigasyonu - navigated event'inde root component'i yeniden başlat
  // capture: true kullanarak event'i erken yakalıyoruz
  document.addEventListener('livewire:navigated', () => {
    handleLivewireNavigated();
  }, { capture: true });

  // Livewire navigasyon başladığında
  // Not: Root component state'i korunur, sadece navigated'da yeniden başlatılır
  document.addEventListener('livewire:navigating', () => {
    // Bu event'te hiçbir şey yapmıyoruz - sadece navigated'da re-init yapılacak
  }, { capture: true });

  // Livewire bileşen güncellemeleri (debounced)
  // Not: Bu sadece global davranışlar için
  // Modül-spesifik livewire:updated listener'ları modüllerinde kalmalı
  document.addEventListener('livewire:updated', () => {
    handleLivewireUpdated();
  });

  // Global Modal Event Listener - Livewire'dan modal açma
  document.addEventListener('livewire:initialized', () => {
    Livewire.on('modal:confirm', (data) => {
      // Livewire 3'te data direkt array olarak gelebilir veya object olabilir
      const eventData = Array.isArray(data) && data.length > 0 ? data[0] : data;

      const { title, message, action, id, confirmLabel = 'Onayla', cancelLabel = 'Vazgeç' } = eventData || {};

      if (!title || !message || !action) {
        console.warn('modal:confirm event eksik parametreler içeriyor:', eventData);
        return;
      }

      // Global modal'ı aç
      if (window.Alpine && window.Alpine.store('globalModal')) {
        window.Alpine.store('globalModal').show({
          title,
          message,
          confirmLabel,
          cancelLabel,
          onConfirm: () => {
            // Livewire action'ı çağır
            if (id !== undefined) {
              Livewire.dispatch(action, { id });
            } else {
              Livewire.dispatch(action);
            }
          }
        });
      }
    });
  });

  // Global Toast Event Listener - Livewire'dan toast gösterme
  // Hem livewire:initialized hem de livewire:navigated'da çalışmalı
  // Duplicate toast'ları engellemek için son toast'ı takip et
  // Global değişken kullanarak hem event hem session flash toast'larını kontrol et
  if (typeof window.__lastToastMessage === 'undefined') {
    window.__lastToastMessage = null;
    window.__lastToastTime = 0;
  }
  const TOAST_DEBOUNCE_MS = 5000; // Aynı mesaj 5 saniye içinde tekrar gelirse ignore et (event + session flash için)

  function setupToastListener() {
    if (!window.Livewire) {
      // Livewire henüz hazır değilse tekrar dene
      setTimeout(setupToastListener, 100);
      return;
    }

    // Listener zaten kurulmuşsa tekrar kurma (duplicate'ı önlemek için)
    if (window.__toastListenerSetup) {
      return;
    }

    window.__toastListenerSetup = true;

    // Toast event handler
    window.__toastListenerHandler = (data) => {
      // Livewire 3'te data direkt array olarak gelebilir veya object olabilir
      const eventData = Array.isArray(data) && data.length > 0 ? data[0] : data;

      const { type = 'success', message, duration } = eventData || {};

      if (!message) {
        if (import.meta.env.DEV) {
          console.warn('toast event eksik message içeriyor:', eventData);
        }
        return;
      }

      // Duplicate toast kontrolü - aynı mesaj kısa süre içinde gelirse ignore et
      // Hem event hem session flash toast'ları için aynı değişkeni kullan
      const now = Date.now();
      if (window.__lastToastMessage === message && (now - window.__lastToastTime) < TOAST_DEBOUNCE_MS) {
        if (import.meta.env.DEV) {
          console.log('Duplicate toast ignored (event):', message);
        }
        return;
      }

      window.__lastToastMessage = message;
      window.__lastToastTime = now;

      // Alpine store'un hazır olmasını bekle
      const showToast = (retryCount = 0) => {
        if (retryCount > 20) {
          // 20 denemeden sonra vazgeç (1 saniye)
          if (import.meta.env.DEV) {
            console.error('Toast gösterilemedi: Alpine store hazır değil');
          }
          return;
        }

        if (window.Alpine && window.Alpine.store('globalToast')) {
          try {
            const options = duration ? { duration } : {};
            window.Alpine.store('globalToast').show(type, message, options);
          } catch (e) {
            if (import.meta.env.DEV) {
              console.error('Toast gösterilirken hata:', e);
            }
            // Hata durumunda tekrar dene
            setTimeout(() => showToast(retryCount + 1), 50);
          }
        } else if (window.toast && typeof window.toast[type] === 'function') {
          // Fallback: window.toast API kullan
          try {
            const options = duration ? { duration } : {};
            window.toast[type](message, options);
          } catch (e) {
            if (import.meta.env.DEV) {
              console.error('Toast gösterilirken hata (fallback):', e);
            }
          }
        } else if (window.toast && typeof window.toast.success === 'function') {
          // Fallback: type yoksa success kullan
          try {
            const options = duration ? { duration } : {};
            window.toast.success(message, options);
          } catch (e) {
            if (import.meta.env.DEV) {
              console.error('Toast gösterilirken hata (fallback 2):', e);
            }
          }
        } else {
          // Alpine henüz hazır değilse bekle
          setTimeout(() => showToast(retryCount + 1), 50);
        }
      };

      showToast();
    };

    // Listener'ı ekle
    try {
      Livewire.on('toast', window.__toastListenerHandler);
    } catch (e) {
      if (import.meta.env.DEV) {
        console.error('Toast listener eklenirken hata:', e);
      }
    }
  }

  // Livewire initialize olduğunda
  document.addEventListener('livewire:initialized', () => {
    setupToastListener();
  });

  // Livewire navigasyon sonrası listener'ı sıfırla ve yeniden kur
  // Çünkü navigated sonrası listener kaybolabilir
  document.addEventListener('livewire:navigated', () => {
    // Listener'ı sıfırla ve yeniden kur
    window.__toastListenerSetup = false;
    if (window.__toastListenerHandler && window.Livewire) {
      try {
        Livewire.off('toast', window.__toastListenerHandler);
      } catch (e) {
        // Ignore
      }
    }
    window.__toastListenerHandler = null;

    // Yeniden kur
    setTimeout(() => {
      setupToastListener();
    }, 100);
  });
}
