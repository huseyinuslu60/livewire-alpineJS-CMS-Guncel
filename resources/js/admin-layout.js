// Admin Layout JavaScript
// Admin UI davranışlarını yönetir: CSRF token, fullscreen, vb.

import { registerModuleInit } from './livewire-alpine-lifecycle';

/**
 * Admin layout'u başlat
 * İdempotent - birden fazla kez çağrılabilir
 */
function initAdminLayout() {
  // Tam ekran açma/kapama fonksiyonu (tek seferlik, korumalı)
  if (typeof window.toggleFullScreen === 'undefined') {
    window.toggleFullScreen = () => {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {
          // Fullscreen API desteklenmiyorsa sessizce başarısız ol
        });
      } else {
        document.exitFullscreen?.();
      }
    };
  }
}

// Modülü kaydet - merkezi lifecycle manager tarafından çağrılacak
registerModuleInit('adminLayout', initAdminLayout);
