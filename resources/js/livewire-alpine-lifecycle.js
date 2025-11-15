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
}
