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
 */
function initAlpineGlobals() {
  // Alpine global store'lar ve bileşenler app.js'de zaten tanımlı
  // Bu fonksiyon gerekirse ek Alpine kurulumu için kullanılabilir
  if (window.Alpine && !window.__alpineGlobalsInitialized) {
    window.__alpineGlobalsInitialized = true;
    // Ek Alpine kurulumu buraya eklenebilir
  }
}

/**
 * Livewire navigasyonunu yönet
 * SPA navigasyonundan sonra UI bileşenlerini yeniden başlatır
 */
function handleLivewireNavigated() {
  // Alpine root component'ini yeniden başlat
  // Livewire navigasyonu sırasında root x-data component'i yeniden değerlendirilmeli
  // Bu, sidebarOpen ve darkMode gibi root-level state'lerin erişilebilir olmasını sağlar

  // Önce modülleri başlat
  initAllModules();

  // Alpine root component'ini hemen yeniden başlat
  // queueMicrotask kullanarak bu işlemi current call stack'in sonunda ama
  // Livewire'ın expression evaluation'ından önce çalıştırıyoruz
  queueMicrotask(() => {
    if (window.Alpine && document.documentElement && window.adminApp) {
      try {
        const rootData = document.documentElement.getAttribute('x-data');
        const rootInit = document.documentElement.getAttribute('x-init');

        if (rootData === 'adminApp()' || rootData?.includes('adminApp')) {
          // Mevcut component data'sını al
          let componentData = null;
          try {
            if (window.Alpine.$data) {
              componentData = window.Alpine.$data(document.documentElement);
            }
          } catch (e) {
            // Component data yoksa devam et
          }

          // Eğer component data varsa ve init() metodunu çağırmadıysak, çağır
          if (componentData && typeof componentData.init === 'function') {
            // Component zaten var, sadece init() metodunu çağır
            // Bu, state'i localStorage'dan yeniden yükler
            componentData.init();
          } else {
            // Component data yoksa veya erişilemiyorsa, attribute'ları yeniden set ederek başlat
            // Önce kaldır
            document.documentElement.removeAttribute('x-data');
            if (rootInit) {
              document.documentElement.removeAttribute('x-init');
            }

            // Hemen tekrar ekle
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
          }
        }
      } catch (e) {
        if (import.meta.env.DEV) {
          console.error('Alpine root component re-init error:', e);
        }
      }
    }
  });
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

  // Livewire navigasyon başladığında - root component'in state'ini koru
  document.addEventListener('livewire:navigating', () => {
    // Bu event'te root component'in state'ini korumak için hiçbir şey yapmıyoruz
    // Çünkü Livewire DOM'u değiştirirken Alpine'in root component'ini korumamız gerekiyor
    // Sadece navigated'da yeniden başlatacağız
  }, { capture: true });

  // Livewire bileşen güncellemeleri (debounced)
  // Not: Bu sadece global davranışlar için
  // Modül-spesifik livewire:updated listener'ları modüllerinde kalmalı
  document.addEventListener('livewire:updated', () => {
    handleLivewireUpdated();
  });
}
