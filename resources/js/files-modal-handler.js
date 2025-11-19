/**
 * Files Modal Handler
 * Files modülünü modal olarak açar ve seçilen dosyaları işler
 */

let currentModalContext = null;

export function initFilesModalHandler() {
    // Trumbowyg editöründen arşiv butonuna tıklandığında
    document.addEventListener('trumbowyg:openArchive', function(event) {
        const { editor, textarea } = event.detail;
        currentModalContext = {
            mode: 'editor',
            editor: editor,
            textarea: textarea
        };
        openFilesModal();
    });

    // Genel arşivden seç butonuna tıklandığında
    document.addEventListener('openFilesModal', function(event) {
        const { mode, callback, multiple, type } = event.detail;
        currentModalContext = {
            mode: mode || 'select',
            callback: callback,
            multiple: multiple !== false,
            type: type || 'image'
        };
        openFilesModal();
    });

    // Files seçildiğinde
    window.addEventListener('filesSelected', handleFilesSelected);
}

function openFilesModal() {
    // Modal container'ı bul (zaten HTML'de var)
    const modalContainer = document.getElementById('files-modal-container');
    if (!modalContainer) {
        console.error('Files modal container bulunamadı!');
        return;
    }

    // Modal'ı göster
    modalContainer.style.display = 'block';

    // Body scroll'u engelle
    document.body.style.overflow = 'hidden';

    // Close button ve backdrop event'lerini ayarla
    setupModalCloseHandlers();
}

function setupModalCloseHandlers() {
    const modalContainer = document.getElementById('files-modal-container');
    const closeBtn = document.getElementById('files-modal-close');
    const backdrop = document.getElementById('files-modal-backdrop');

    const closeModal = () => {
        if (modalContainer) {
            modalContainer.style.display = 'none';
        }
        document.body.style.overflow = '';
        currentModalContext = null;
    };

    // Mevcut event listener'ları temizle
    if (closeBtn) {
        const newCloseBtn = closeBtn.cloneNode(true);
        closeBtn.parentNode?.replaceChild(newCloseBtn, closeBtn);
        newCloseBtn.addEventListener('click', closeModal);
    }

    if (backdrop) {
        const newBackdrop = backdrop.cloneNode(true);
        backdrop.parentNode?.replaceChild(newBackdrop, backdrop);
        newBackdrop.addEventListener('click', closeModal);
    }
}

// loadFilesComponent fonksiyonu artık gerekli değil
// Livewire component'i zaten HTML'de render edilmiş durumda

function handleFilesSelected(event) {
    const files = event.detail;
    if (!files || files.length === 0 || !currentModalContext) return;

    // Modal'ı kapat
    const modalContainer = document.getElementById('files-modal-container');
    if (modalContainer) {
        modalContainer.style.display = 'none';
    }
    document.body.style.overflow = '';

    const context = currentModalContext;
    currentModalContext = null;

    // Editor modunda (Trumbowyg)
    if (context.mode === 'editor' && context.textarea) {
        const textarea = context.textarea;
        if (textarea && window.jQuery) {
            const $textarea = window.jQuery(textarea);
            if ($textarea.data('trumbowyg')) {
                files.forEach(file => {
                    if (file.type === 'image' || file.url) {
                        const imgTag = `<img src="${file.url}" alt="${file.alt_text || file.title || ''}" style="max-width: 100%; height: auto;" />`;
                        $textarea.trumbowyg('execCmd', 'insertHTML', imgTag, false);
                    }
                });
            }
        }
    } else {
        // Genel seçim modu - Livewire event dispatch et
        if (window.Livewire) {
            // Tüm Livewire component'lerine event gönder
            window.Livewire.all().forEach(component => {
                if (component && typeof component.call === 'function') {
                    try {
                        component.call('filesSelectedForPost', { files });
                    } catch (e) {
                        // Component bu metodu desteklemiyorsa sessizce geç
                    }
                }
            });
        }

        // Ayrıca custom event de dispatch et (fallback)
        const selectEvent = new CustomEvent('filesSelectedForPost', {
            detail: { files }
        });
        document.dispatchEvent(selectEvent);
    }
}

// Sayfa yüklendiğinde başlat
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initFilesModalHandler();
    });
} else {
    initFilesModalHandler();
}

