/**
 * Trumbowyg Editör Başlatma
 *
 * Bu dosya 'editor' chunk'ının bir parçasıdır ve sadece
 * sayfada [data-editor] elementleri olduğunda lazy olarak yüklenir.
 *
 * jQuery, Trumbowyg'i import eder ve editörleri başlatır.
 */

// Önce jQuery'yi import et (global olmalı)
import './../jquery-init';

// Trumbowyg ve bağımlılıklarını import et
import 'trumbowyg';
import 'trumbowyg/dist/langs/tr.min.js';
import 'trumbowyg/dist/ui/trumbowyg.css';
import './../trumbowyg-archive-plugin';

// SVG ikonlarını import et
import svgPath from 'trumbowyg/dist/ui/icons.svg?url';

// Trumbowyg SVG path'ini ayarla
const setTrumbowygSvgPath = () => {
    if (window.jQuery && window.jQuery.trumbowyg) {
        window.jQuery.trumbowyg.svgPath = svgPath;
        return true;
    }
    return false;
};

// SVG path'ini hemen ayarlamayı dene
if (!setTrumbowygSvgPath()) {
    // Trumbowyg'in jQuery'yi extend etmesini bekle
    let attempts = 0;
    const maxAttempts = 20;
    const interval = setInterval(() => {
        attempts++;
        if (setTrumbowygSvgPath() || attempts >= maxAttempts) {
            clearInterval(interval);
        }
    }, 50);
}

// Editör lifecycle fonksiyonlarını import et
import { initTrumbowyg } from '../editors-lifecycle';

/**
 * Trumbowyg editörlerini başlat
 * editor-loader.js tarafından kullanılmak üzere export edilir
 */
export function initTrumbowygEditors() {
    if (typeof initTrumbowyg === 'function') {
        initTrumbowyg();
    }
}

// Yükleme sırasında otomatik başlat (doğrudan import'lar için)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initTrumbowygEditors();
    });
} else {
    initTrumbowygEditors();
}

// Uyumluluk için initTrumbowyg'i de doğrudan export et
export { initTrumbowyg };

