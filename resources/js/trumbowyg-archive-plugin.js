/**
 * Trumbowyg Archive Plugin
 * Arşivden resim seçme butonu ekler
 */

(function($) {
    'use strict';

    // Custom button definition
    $.extend(true, $.trumbowyg, {
        langs: {
            tr: {
                archive: 'Arşivden Seç',
            },
            en: {
                archive: 'Select from Archive',
            }
        },
        plugins: {
            archive: {
                init: function(trumbowyg) {
                    const btnDef = {
                        fn: function() {
                            // Open files modal
                            // Range'i kaydet (imleç konumunu sakla)
                            trumbowyg.saveRange();
                            
                            // Range'i Trumbowyg instance'ında sakla (restore için)
                            if (!trumbowyg.savedRange) {
                                trumbowyg.savedRange = null;
                            }
                            // saveRange() zaten range'i saklıyor, restoreRange() ile geri yüklenebilir

                            // Textarea'yı bul ve ID'sini al
                            const textarea = trumbowyg.$ta[0];
                            const textareaId = textarea?.id || null;
                            
                            // Dispatch event to open files modal
                            const event = new CustomEvent('trumbowyg:openArchive', {
                                detail: {
                                    editor: trumbowyg,
                                    textarea: textarea,
                                    textareaId: textareaId
                                }
                            });
                            document.dispatchEvent(event);
                        },
                        title: trumbowyg.lang.archive || 'Select from Archive',
                        // HTML icon kullan (FontAwesome)
                        text: '<i class="fas fa-archive"></i>',
                        hasIcon: false
                    };

                    trumbowyg.addBtnDef('archive', btnDef);
                }
            }
        }
    });
})(jQuery);

