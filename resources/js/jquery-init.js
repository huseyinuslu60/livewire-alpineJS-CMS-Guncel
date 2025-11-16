// jQuery Global Başlatma
// Bu dosya Trumbowyg'den ÖNCE import edilmelidir
// jQuery'nin diğer modüller yüklenmeden önce global olarak erişilebilir olmasını sağlar

import jQuery from 'jquery';

// jQuery'yi global olarak SYNCHRONOUS olarak expose et
// Bu, jQuery'ye bağımlı diğer import'lardan önce gerçekleşmelidir
// window objesi her zaman mevcut olmalı (browser environment)
window.jQuery = window.$ = jQuery;

// jQuery'nin erişilebilir olduğunu doğrula
if (typeof window.jQuery === 'undefined' || typeof window.$ === 'undefined') {
  throw new Error('jQuery global olarak expose edilemedi');
}

// ES modül uyumluluğu için export (gerekirse)
export default jQuery;
export { jQuery, jQuery as $ };

