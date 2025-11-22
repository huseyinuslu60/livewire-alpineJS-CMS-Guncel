/**
 * Editör Lazy Loader
 *
 * Sayfada [data-editor] attribute'u olan elementler varsa
 * editör chunk'ını (jQuery + Trumbowyg) koşullu olarak yükler.
 *
 * Bu, editör olmayan sayfalarda ilk bundle boyutunu azaltır.
 */

/**
 * Mevcut sayfada editör gerekip gerekmediğini kontrol et
 */
function needsEditor() {
    // data-editor attribute'u olan herhangi bir element var mı kontrol et
    return document.querySelector('[data-editor]') !== null;
}

/**
 * Editör chunk'ını yükle ve editörleri başlat
 */
async function loadEditor() {
    try {
        // Editör başlatma kodunu dinamik olarak import et
        // Bu, 'editor' chunk'ını (jQuery + Trumbowyg) yükleyecek
        const editorModule = await import('./editors/trumbowyg-init.js');

        // Chunk yüklendikten sonra editörleri başlat
        // Uyumluluk için her iki export adını da dene
        const initFn = editorModule.initTrumbowygEditors || editorModule.initTrumbowyg;
        if (typeof initFn === 'function') {
            initFn();
        }
    } catch (error) {
        // Sadece development modunda hata göster
        if (import.meta.env.DEV) {
            console.error('Editör chunk yüklenemedi:', error);
        }
    }
}

/**
 * Editör loader'ı başlat
 * DOMContentLoaded'da veya DOM zaten hazırsa hemen çalışır
 */
function initEditorLoader() {
    if (needsEditor()) {
        loadEditor();
    }
}

// DOM hazır olduğunda çalıştır
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEditorLoader);
} else {
    // DOM zaten hazır
    initEditorLoader();
}

// Livewire navigasyon event'lerini de dinle (SPA benzeri navigasyon için)
if (window.Livewire) {
    document.addEventListener('livewire:navigated', () => {
        // DOM'un güncellendiğinden emin olmak için kısa bir gecikme
        setTimeout(() => {
            if (needsEditor() && !window.jQuery) {
                loadEditor();
            }
        }, 100);
    });
}

export { needsEditor, loadEditor };

