/**
 * Files Modal Handler
 * Files modülünü modal olarak açar ve seçilen dosyaları işler
 */

let currentModalContext = null;
let isProcessingFiles = false; // Event'in birden fazla kez işlenmesini önle

export function initFilesModalHandler() {
    // Trumbowyg editöründen arşiv butonuna tıklandığında
    document.addEventListener('trumbowyg:openArchive', function(event) {
        const { editor, textarea, textareaId } = event.detail;

        // Önceki context'i temizle (üstteki "Arşivden Seç" butonundan kalan context'i temizle)
        currentModalContext = null;
        sessionStorage.removeItem('filesModalContext');

        // Editor objesini saklama (circular reference var), sadece textarea referansını sakla
        currentModalContext = {
            mode: 'editor',
            textarea: textarea, // Sadece textarea DOM element'i
            textareaId: textareaId || textarea?.id || null, // Fallback için ID
        };
        console.log('Files Modal Handler - Trumbowyg archive button clicked', {
            hasTextarea: !!textarea,
            textareaId: textareaId || textarea?.id,
            mode: 'editor'
        });
        openFilesModal();
    });

    // Genel arşivden seç butonuna tıklandığında
    document.addEventListener('openFilesModal', function(event) {
        const { mode, callback, multiple, type } = event.detail;

        // Önceki context'i temizle (Trumbowyg'den kalan context'i temizle)
        currentModalContext = null;
        sessionStorage.removeItem('filesModalContext');

        // Yeni context oluştur
        currentModalContext = {
            mode: mode || 'select',
            callback: callback,
            multiple: multiple !== false,
            type: type || 'image'
        };
        console.log('Files Modal Handler - General archive button clicked', {
            mode: currentModalContext.mode,
            multiple: currentModalContext.multiple,
            type: currentModalContext.type
        });
        openFilesModal();
    });

    // Files seçildiğinde - JavaScript CustomEvent
    window.addEventListener('filesSelected', handleFilesSelected);

    // Livewire event'ini dinle (Livewire 3) - Sadece bir kez dinle
    let livewireListenerSetup = false;
    const setupLivewireListener = () => {
        if (livewireListenerSetup) {
            return; // Zaten kurulmuş, tekrar kurma
        }
        if (window.Livewire && typeof window.Livewire.on === 'function') {
            window.Livewire.on('filesSelected', (files) => {
                console.log('Files Modal Handler - Livewire event received:', files);
                const event = new CustomEvent('filesSelected', {
                    detail: files
                });
                window.dispatchEvent(event);
            });
            livewireListenerSetup = true;
        }
    };

    if (window.Livewire) {
        // Livewire zaten yüklüyse direkt dinle
        setupLivewireListener();
    } else {
        // Livewire henüz yüklenmemişse, yüklendiğinde dinle
        document.addEventListener('livewire:init', () => {
            setupLivewireListener();
        });
    }
}

function openFilesModal() {
    // Yeni archive modal'ı kullan (Posts modülü için özel)
    const modalContainer = document.getElementById('archive-modal-container');
    if (!modalContainer) {
        console.error('Archive modal container bulunamadı!');
        return;
    }

    // Context'i sessionStorage'a kaydet (event geldiğinde kullanmak için)
    // Editor objesini kaydetme (circular reference), sadece gerekli bilgileri kaydet
    if (currentModalContext) {
        const contextToSave = {
            mode: currentModalContext.mode,
            textareaId: currentModalContext.textareaId || (currentModalContext.textarea?.id || null),
            callback: currentModalContext.callback || null,
            multiple: currentModalContext.multiple,
            type: currentModalContext.type,
        };
        sessionStorage.setItem('filesModalContext', JSON.stringify(contextToSave));
    }

    // Modal'ı göster
    modalContainer.style.display = 'block';

    // Body scroll'u engelle
    document.body.style.overflow = 'hidden';

    // Close button ve backdrop event'lerini ayarla
    setupModalCloseHandlers();
}

function setupModalCloseHandlers() {
    const modalContainer = document.getElementById('archive-modal-container');
    const backdrop = document.getElementById('archive-modal-backdrop');

    const closeModal = () => {
        if (modalContainer) {
            modalContainer.style.display = 'none';
        }
        document.body.style.overflow = '';
        currentModalContext = null;
        // SessionStorage'dan da temizle
        sessionStorage.removeItem('filesModalContext');
    };

    // Backdrop'a tıklandığında kapat
    if (backdrop) {
        const newBackdrop = backdrop.cloneNode(true);
        backdrop.parentNode?.replaceChild(newBackdrop, backdrop);
        newBackdrop.addEventListener('click', closeModal);
    }

    // ESC tuşu ile kapat
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape' && modalContainer && modalContainer.style.display === 'block') {
            closeModal();
            document.removeEventListener('keydown', escHandler);
        }
    });
}

// loadFilesComponent fonksiyonu artık gerekli değil
// Livewire component'i zaten HTML'de render edilmiş durumda

function handleFilesSelected(event) {
    console.log('Files Modal Handler - handleFilesSelected called:', event);

    // Eğer zaten işleniyorsa, tekrar işleme (sadece aynı event için)
    if (isProcessingFiles) {
        console.log('Files Modal Handler - Already processing, ignoring duplicate event');
        return;
    }

    // İşleme başladığını işaretle
    isProcessingFiles = true;

    let files = event.detail;

    // Eğer files array içinde array varsa, düzleştir
    if (Array.isArray(files) && files.length > 0 && Array.isArray(files[0])) {
        files = files[0];
    }

    // Context'i sessionStorage'dan al (eğer memory'de yoksa)
    if (!currentModalContext) {
        const savedContext = sessionStorage.getItem('filesModalContext');
        if (savedContext) {
            try {
                const parsed = JSON.parse(savedContext);
                // Textarea'yı ID'den bul
                if (parsed.mode === 'editor' && parsed.textareaId) {
                    const textarea = document.getElementById(parsed.textareaId);
                    if (textarea) {
                        currentModalContext = {
                            mode: 'editor',
                            textarea: textarea,
                            textareaId: parsed.textareaId,
                        };
                        console.log('Files Modal Handler - Restored context from sessionStorage:', currentModalContext);
                    }
                } else {
                    currentModalContext = parsed;
                    console.log('Files Modal Handler - Restored context from sessionStorage:', currentModalContext);
                }
            } catch (e) {
                console.error('Files Modal Handler - Failed to parse saved context:', e);
            }
        }
    }

    // Context yoksa ama files varsa, default context oluştur
    if (!currentModalContext && files && files.length > 0) {
        console.log('Files Modal Handler - No context, creating default context');
        currentModalContext = {
            mode: 'select',
            multiple: false,
            type: 'image'
        };
    }

    console.log('Files Modal Handler - files:', files, 'currentModalContext:', currentModalContext);

    if (!files || files.length === 0) {
        console.warn('Files Modal Handler - Missing files:', { files });
        isProcessingFiles = false; // Flag'i temizle
        return;
    }

    // Modal'ı kapat
    const modalContainer = document.getElementById('archive-modal-container');
    if (modalContainer) {
        modalContainer.style.display = 'none';
    }
    document.body.style.overflow = '';

    const context = currentModalContext;
    // Context'i hemen temizleme, işlem bitince temizle

    // Editor modunda (Trumbowyg) - SADECE editor modunda işle
    if (context && context.mode === 'editor') {
        console.log('Files Modal Handler - Processing for Trumbowyg editor');

        // Textarea'yı bul - önce context'ten, sonra ID'den, sonra tüm Trumbowyg textarea'larından
        let textarea = context.textarea;
        if (!textarea && context.textareaId) {
            textarea = document.getElementById(context.textareaId);
        }

        // Eğer hala bulunamadıysa, tüm Trumbowyg textarea'larını kontrol et
        if (!textarea && window.jQuery) {
            const $trumbowygTextareas = window.jQuery('textarea[data-editor="trumbowyg"]');
            if ($trumbowygTextareas.length > 0) {
                textarea = $trumbowygTextareas[0];
                console.log('Files Modal Handler - Found Trumbowyg textarea by selector');
            }
        }

        if (textarea && window.jQuery) {
            const $textarea = window.jQuery(textarea);

            // Trumbowyg instance'ını kontrol et
            const trumbowygInstance = $textarea.data('trumbowyg');
            console.log('Files Modal Handler - Trumbowyg editor found', {
                hasTextarea: !!textarea,
                hasTrumbowygInstance: !!trumbowygInstance,
                textareaId: textarea.id
            });

            // Her dosya için sadece bir kez işle (duplicate önleme)
            const processedUrls = new Set();
            files.forEach(file => {
                console.log('Files Modal Handler - Processing file for Trumbowyg:', file);
                // Resim kontrolü - type veya url'e göre
                const isImage = file.type === 'image' ||
                               (file.type && file.type.startsWith('image/')) ||
                               (file.url && (file.url.match(/\.(jpg|jpeg|png|gif|webp)$/i)));

                if (isImage || file.url) {
                    const imgUrl = file.url || (file.id ? `/storage/files/${file.id}` : '');
                    if (imgUrl) {
                        // Duplicate kontrolü - aynı URL'yi tekrar ekleme
                        if (processedUrls.has(imgUrl)) {
                            console.log('Files Modal Handler - Skipping duplicate image URL:', imgUrl);
                            return; // Bu dosyayı atla
                        }
                        processedUrls.add(imgUrl);

                        console.log('Files Modal Handler - Inserting image with URL:', imgUrl);

                        // Trumbowyg'e resim ekle - imleç konumuna eklemek için range'i restore et
                        try {
                            const imgTag = `<img src="${imgUrl}" alt="${file.alt_text || file.title || ''}" style="max-width: 100%; height: auto;" />`;

                            // Trumbowyg instance'ından range'i restore et (imleç konumuna eklemek için)
                            if (trumbowygInstance) {
                                // Range'i restore et (saveRange() ile kaydedilen konumu geri yükle)
                                // Trumbowyg'de restoreRange() metodu range'i geri yükler
                                try {
                                    if (typeof trumbowygInstance.restoreRange === 'function') {
                                        trumbowygInstance.restoreRange();
                                        console.log('Files Modal Handler - Range restored');
                                    } else {
                                        // Fallback: Trumbowyg'in internal range mekanizmasını kullan
                                        // Trumbowyg'de range genellikle otomatik olarak restore edilir
                                        console.log('Files Modal Handler - restoreRange not available, using default behavior');
                                    }
                                } catch (e) {
                                    console.warn('Files Modal Handler - Error restoring range:', e);
                                }

                                // Instance üzerinden direkt çağır (restoreRange sonrası)
                                if (typeof trumbowygInstance.execCmd === 'function') {
                                    trumbowygInstance.execCmd('insertHTML', imgTag, false);
                                    console.log('Files Modal Handler - Image inserted using instance.execCmd at cursor position');
                                } else {
                                    // Fallback: jQuery plugin üzerinden çağır
                                    $textarea.trumbowyg('execCmd', 'insertHTML', imgTag, false);
                                    console.log('Files Modal Handler - Image inserted using jQuery plugin at cursor position');
                                }
                            } else if (typeof $textarea.trumbowyg === 'function') {
                                // Range'i restore et
                                try {
                                    const trumbowygData = $textarea.data('trumbowyg');
                                    if (trumbowygData && typeof trumbowygData.restoreRange === 'function') {
                                        trumbowygData.restoreRange();
                                        console.log('Files Modal Handler - Range restored (jQuery)');
                                    }
                                } catch (e) {
                                    console.warn('Files Modal Handler - Error restoring range (jQuery):', e);
                                }

                                // jQuery plugin üzerinden çağır
                                $textarea.trumbowyg('execCmd', 'insertHTML', imgTag, false);
                                console.log('Files Modal Handler - Image inserted using jQuery plugin at cursor position');
                            } else {
                                // Son çare: direkt HTML ekle ve Trumbowyg'i güncelle
                                const currentHtml = $textarea.trumbowyg('html') || '';
                                $textarea.trumbowyg('html', currentHtml + imgTag);
                                console.log('Files Modal Handler - Image inserted using html method (fallback)');
                            }

                            // Trumbowyg'i güncelle
                            $textarea.trigger('tbwchange');
                            console.log('Files Modal Handler - Image inserted successfully');
                        } catch (e) {
                            console.error('Files Modal Handler - Error inserting image:', e);
                            // Son çare: direkt HTML ekle
                            try {
                                const currentContent = $textarea.val() || '';
                                const imgTag = `<img src="${imgUrl}" alt="${file.alt_text || file.title || ''}" style="max-width: 100%; height: auto;" />`;
                                $textarea.val(currentContent + imgTag);
                                // Trumbowyg'i güncelle
                                if (typeof $textarea.trumbowyg === 'function') {
                                    $textarea.trumbowyg('html', $textarea.val());
                                }
                                $textarea.trigger('tbwchange');
                                console.log('Files Modal Handler - Image inserted using fallback method');
                            } catch (e2) {
                                console.error('Files Modal Handler - Fallback method also failed:', e2);
                            }
                        }
                    }
                }
            });

            // Trumbowyg işlemi bitti, context'i hemen temizle (duplicate önlemek için)
            currentModalContext = null;
            sessionStorage.removeItem('filesModalContext');

            // Trumbowyg modunda işlem tamamlandı, genel seçim moduna geçme
            // İşlem bittikten sonra flag'i temizle
            setTimeout(() => {
                isProcessingFiles = false;
            }, 100);

            return; // Trumbowyg modunda işlem bitti, genel seçim moduna geçme
        } else {
            console.warn('Files Modal Handler - jQuery or textarea not available', {
                hasTextarea: !!textarea,
                hasJQuery: !!window.jQuery
            });

            // Trumbowyg modunda ama textarea bulunamadı, yine de return yap
            currentModalContext = null;
            sessionStorage.removeItem('filesModalContext');
            setTimeout(() => {
                isProcessingFiles = false;
            }, 100);
            return;
        }
    }

    // Genel seçim modu - SADECE mode === 'select' olduğunda buraya gel
    if (context && context.mode === 'select') {
        // Genel seçim modu - Livewire event dispatch et
        console.log('Files Modal Handler - Dispatching to Livewire components, files:', files);
        console.log('Files Modal Handler - Context:', context);

        // Multiple seçim bilgisini context'ten al
        const multiple = context.multiple || false;

        if (window.Livewire) {
            // Component'i bul - post create sayfalarında
            let targetComponent = null;

            // 1. Tüm component'leri kontrol et ve filesSelectedForPost metodunu destekleyen component'i bul
            const allComponents = window.Livewire.all();
            console.log('Files Modal Handler - Checking', allComponents.length, 'Livewire components');

            for (const component of allComponents) {
                if (component && typeof component.call === 'function') {
                    // Component'in filesSelectedForPost metodunu destekleyip desteklemediğini kontrol et
                    try {
                        // Test: component'in get metodunu kullanarak property kontrolü yap
                        const hasSelectedArchiveFileIds = component.get('selectedArchiveFileIds') !== undefined;
                        const hasSelectedArchiveFilesPreview = component.get('selectedArchiveFilesPreview') !== undefined;

                        if (hasSelectedArchiveFileIds || hasSelectedArchiveFilesPreview) {
                            targetComponent = component;
                            console.log('Files Modal Handler - Found component by property check', {
                                hasSelectedArchiveFileIds,
                                hasSelectedArchiveFilesPreview
                            });
                            break;
                        }
                    } catch (e) {
                        // Devam et - get metodu çalışmıyor olabilir
                        console.warn('Files Modal Handler - Error checking component:', e);
                    }
                }
            }

            // 2. Eğer hala bulunamadıysa, sayfadaki wire:id'leri kontrol et
            if (!targetComponent) {
                const wireElements = document.querySelectorAll('[wire\\:id]');
                console.log('Files Modal Handler - Found', wireElements.length, 'wire:id elements');

                for (const wireEl of wireElements) {
                    const wireId = wireEl.getAttribute('wire:id');
                    if (wireId) {
                        try {
                            const component = window.Livewire.find(wireId);
                            if (component && typeof component.call === 'function') {
                                // Component'in property'lerini kontrol et
                                try {
                                    const hasSelectedArchiveFileIds = component.get('selectedArchiveFileIds') !== undefined;
                                    const hasSelectedArchiveFilesPreview = component.get('selectedArchiveFilesPreview') !== undefined;

                                    if (hasSelectedArchiveFileIds || hasSelectedArchiveFilesPreview) {
                                        targetComponent = component;
                                        console.log('Files Modal Handler - Found component by wire:id:', wireId);
                                        break;
                                    }
                                } catch (e) {
                                    // Devam et
                                }
                            }
                        } catch (e) {
                            // Devam et
                        }
                    }
                }
            }

            // 3. Component bulunduysa metodunu çağır
            if (targetComponent && typeof targetComponent.call === 'function') {
                try {
                    console.log('Files Modal Handler - Calling filesSelectedForPost on component:', targetComponent);
                    targetComponent.call('filesSelectedForPost', {
                        files: files,
                        multiple: multiple
                    });
                    console.log('Files Modal Handler - Successfully called filesSelectedForPost');
                } catch (e) {
                    console.error('Files Modal Handler - Error calling filesSelectedForPost:', e);
                }
            } else {
                // Fallback: Global dispatch dene
                try {
                    console.log('Files Modal Handler - Component not found, trying global dispatch');
                    window.Livewire.dispatch('filesSelectedForPost', {
                        files: files,
                        multiple: multiple
                    });
                    console.log('Files Modal Handler - Global dispatch sent');
                } catch (e) {
                    console.error('Files Modal Handler - Error with global dispatch:', e);
                }
            }
        } else {
            console.warn('Files Modal Handler - Livewire not available');
        }

        // Ayrıca custom event de dispatch et (fallback)
        const selectEvent = new CustomEvent('filesSelectedForPost', {
            detail: { files, multiple: multiple }
        });
        document.dispatchEvent(selectEvent);
        console.log('Files Modal Handler - Dispatched custom event filesSelectedForPost');
    }

    // İşlem bittikten sonra context'i ve flag'i temizle
    setTimeout(() => {
        currentModalContext = null;
        isProcessingFiles = false;
        // SessionStorage'dan da temizle
        sessionStorage.removeItem('filesModalContext');
    }, 100);
}

// Sayfa yüklendiğinde başlat
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initFilesModalHandler();
    });
} else {
    initFilesModalHandler();
}

