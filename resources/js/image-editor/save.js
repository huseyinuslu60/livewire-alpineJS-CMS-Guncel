/**
 * Görsel Editörü - Kaydetme İşlemleri
 * Düzenlenmiş görselleri kaydetme ve önizlemeleri güncelleme
 */

import { updateImageSpotData, getCurrentImageConfig, getImageConfig } from './state.js';

export function createSaveMethods() {
  return {
    /**
     * Convert data URL to Blob
     */
    dataURLToBlob(dataURL) {
      return new Promise((resolve) => {
        const arr = dataURL.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
          u8arr[n] = bstr.charCodeAt(n);
        }
        resolve(new Blob([u8arr], { type: mime }));
      });
    },

    /**
     * Yardımcı: Ana Livewire component'ini bul
     * Component'i bulmak için birden fazla strateji kullanır
     */
    findParentLivewireComponent() {
      if (!window.Livewire) {
        console.warn('Image Editor - findParentLivewireComponent: Livewire not available');
        return null;
      }

      // Strateji 1: updateFilePreview metoduna göre bulmayı dene (en spesifik)
      const livewireComponents = window.Livewire.all();
      if (livewireComponents && livewireComponents.length > 0) {
        for (const component of livewireComponents) {
          if (component && typeof component.call === 'function') {
            // Check if component has updateFilePreview method by trying to get it
            try {
              // Check if component has the method by checking its properties
              if (component.get && (
                component.get('uploadedFiles') !== undefined ||
                component.get('selectedArchiveFilesPreview') !== undefined ||
                component.get('imageEditorData') !== undefined
              )) {
                // Bu PostCreateGallery veya benzer bir component gibi görünüyor
                return component;
              }
            } catch (e) {
              // Continue searching
            }
          }
        }
      }

      // Strateji 2: Hidden input veya herhangi bir elementten wire:id'ye göre bul
      const primaryImageInput = document.getElementById('primary_image_spot_data');
      let wireId = null;

      if (primaryImageInput) {
        wireId = primaryImageInput.closest('[wire\\:id]')?.getAttribute('wire:id');
      }

      if (!wireId) {
        // Try to find any wire:id on the page
        const wireElement = document.querySelector('[wire\\:id]');
        if (wireElement) {
          wireId = wireElement.getAttribute('wire:id');
        }
      }

      if (wireId && window.Livewire.find) {
        try {
          const component = window.Livewire.find(wireId);
          if (component && typeof component.call === 'function') {
            return component;
          }
        } catch (e) {
          console.warn('Image Editor - findParentLivewireComponent: Failed to find component by wire:id:', e);
        }
      }

      // Strateji 3: Son çare olarak Livewire.first()'i dene
      if (typeof window.Livewire.first === 'function') {
        try {
          const component = window.Livewire.first();
          if (component && typeof component.call === 'function') {
            return component;
          }
        } catch (e) {
          console.warn('Image Editor - findParentLivewireComponent: Livewire.first() failed:', e);
        }
      }

      console.warn('Image Editor - findParentLivewireComponent: Could not find Livewire component');
      return null;
    },

    /**
     * Yardımcı: imageKey'ye göre önizleme görsel elementini bul (YENİ: imageKey tabanlı yaklaşım)
     * imageKey sağlanmazsa eski fileId/index'e geri döner
     */
    findPreviewImageElementByIdentifiers(fileId = null, index = null, imageKey = null) {
      // NEW: Prioritize imageKey-based search
      const targetImageKey = imageKey || this.currentImageKey;
      if (targetImageKey) {
        const img = document.querySelector(`img[data-image-key="${targetImageKey}"]`);
        if (img) {
          return img;
        }
      }

      // Eski fallback: fileId/index'e göre dene (geriye dönük uyumluluk için)
      const targetFileId = fileId !== null && fileId !== undefined ? fileId : this.currentFileId;
      const targetIndex = index !== null && index !== undefined ? index : this.currentIndex;

      // Önce file ID'ye göre bulmayı dene (en spesifik)
      if (targetFileId) {
        const img = document.querySelector(`#preview-img-${targetFileId}`);
        if (img && img.tagName === 'IMG') {
          return img;
        }

        // Also try by data-file-id attribute
        const imgByDataAttr = document.querySelector(`img[data-file-id="${targetFileId}"]`);
        if (imgByDataAttr) {
          return imgByDataAttr;
        }
      }

      // Try by index (ONLY IMG elements with matching index)
      if (targetIndex !== null && targetIndex !== undefined) {
        // Try exact match first
        let img = document.querySelector(`img[data-file-index="${targetIndex}"]`);
        if (img) {
          return img;
        }

        // Try in all images with data-file-index
        const allImages = document.querySelectorAll('img[data-file-index]');
        for (const imgEl of allImages) {
          const imgIndex = imgEl.getAttribute('data-file-index');
          // Strict comparison - must match exactly
          if (String(imgIndex) === String(targetIndex) || Number(imgIndex) === Number(targetIndex)) {
            return imgEl;
          }
        }

        // Try preview-img-* id pattern with matching index
        const allPreviewImages = document.querySelectorAll('img[id^="preview-img-"]');
        for (const imgEl of allPreviewImages) {
          const imgIndex = imgEl.getAttribute('data-file-index');
          // Strict comparison - must match exactly
          if (String(imgIndex) === String(targetIndex) || Number(imgIndex) === Number(targetIndex)) {
            return imgEl;
          }
        }
      }

      // KRİTİK: Rastgele görseller döndüren fallback metodları kullanma
      // Bu, diğer yazılar için yanlış önizleme göstermesine neden olur
      // Tam eşleşmeyi bulamazsak null döndür ve yeniden deneme mantığının halledmesine izin ver
      console.warn('Image Editor - Could not find preview image element:', {
        targetImageKey: targetImageKey,
        targetFileId: targetFileId,
        targetIndex: targetIndex,
        currentImageKey: this.currentImageKey,
        currentFileId: this.currentFileId,
        currentIndex: this.currentIndex,
      });

      return null;
    },

    /**
     * Yardımcı: Önizleme görsel elementini bul
     * ÖNEMLİ: currentFileId veya currentIndex ile eşleşen TAM görsel elementi bulmalı
     * Rastgele bir görsel elementi asla döndürme, bu diğer yazılar için yanlış önizleme göstermesine neden olur
     */
    findPreviewImageElement() {
      return this.findPreviewImageElementByIdentifiers();
    },

    /**
     * Yardımcı: spot_data ile önizleme elementini güncelle
     * ÖNEMLİ: Eski önizlemeyi göstermeyi önlemek için güncellemeden önce canvas'ı temizlemeli
     */
    updatePreviewElement(previewImg, spotDataJson) {
      if (!previewImg || !spotDataJson) {
        console.warn('Image Editor - updatePreviewElement: Missing previewImg or spotDataJson');
        return false;
      }

      // spotData'yı doğrula
      // Not: Minimum uzunluk kontrolü esnek (20 karakter) küçük ama geçerli JSON'lara izin vermek için
      // {"image":{}} gibi çok küçük JSON'lar hala geçerli, sadece anlamlı veri içerme olasılığı düşük
      if (!spotDataJson || spotDataJson.length < 20) {
        console.warn('Image Editor - updatePreviewElement: spotDataJson too short or missing');
        return false;
      }

      try {
        const parsed = JSON.parse(spotDataJson);
        if (!parsed || !parsed.image) {
          console.warn('Image Editor - updatePreviewElement: Invalid spotData structure');
          return false;
        }

        const imageKeyAttr = previewImg.getAttribute('data-image-key');
        const fileId = previewImg.id ? previewImg.id.replace('preview-img-', '') : '';
        const dataFileId = previewImg.getAttribute('data-file-id');
        const dataFileIndex = previewImg.getAttribute('data-file-index');

        // Geçici yüklemeler için imageKey tam eşleşmesini tercih et; eski uyumsuzluk kontrollerini atla
        if (!imageKeyAttr) {
        if (this.currentFileId) {
          if (fileId !== String(this.currentFileId) && dataFileId !== String(this.currentFileId)) {
              if (import.meta?.env?.DEV) console.warn('Image Editor - updatePreviewElement: FileId mismatch', {
                  fileId: fileId,
                  dataFileId: dataFileId,
                  currentFileId: this.currentFileId,
              });
              return false;
          }
        } else if (this.currentIndex !== null && this.currentIndex !== undefined) {
          if (String(dataFileIndex) !== String(this.currentIndex)) {
            if (import.meta?.env?.DEV) console.warn('Image Editor - updatePreviewElement: Index mismatch', {
              dataFileIndex: dataFileIndex,
              currentIndex: this.currentIndex,
            });
            return false;
          }
        }
        }


        // Attribute'ları güncelle - KRİTİK: Bu attribute'lar spot_data için tek gerçek kaynak
        previewImg.setAttribute('data-spot-data', spotDataJson);
        previewImg.setAttribute('data-has-spot-data', 'true');

        // Gerekirse data-file-index'i ayarla (yeni yüklemeler için)
        if (this.currentIndex !== null && this.currentIndex !== undefined) {
          previewImg.setAttribute('data-file-index', String(this.currentIndex));
        }

        // currentFileId varsa data-file-id'yi ayarla (mevcut dosyalar için)
        if (this.currentFileId) {
          previewImg.setAttribute('data-file-id', String(this.currentFileId));
        }

        // Henüz yoksa data-image-url'nin ayarlandığından emin ol
        // Bu, orijinal görsel URL'sine ihtiyaç duyduğumuz yeniden düzenleme senaryolarında yardımcı olur
        if (!previewImg.getAttribute('data-image-url') && this.imageUrl) {
          previewImg.setAttribute('data-image-url', this.imageUrl);
        }

        // Canvas'ın var olduğundan ve temizlendiğinden emin ol
        const parent = previewImg.parentElement;
        if (parent) {
          let canvas = parent.querySelector('canvas[id^="preview-canvas-"]') || parent.querySelector('canvas');
          if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.className = 'w-full h-full object-cover';
            canvas.style.display = 'none';

            let canvasId = 'preview-canvas-';
            if (this.currentFileId) {
              canvasId += this.currentFileId;
            } else if (this.currentIndex !== null && this.currentIndex !== undefined) {
              canvasId += 'index-' + this.currentIndex;
            } else {
              const fileId = previewImg.id.replace('preview-img-', '');
              canvasId += fileId && fileId !== previewImg.id ? fileId : 'temp-' + Date.now();
            }
            canvas.id = canvasId;
            parent.insertBefore(canvas, previewImg);
          } else {
            // Eski önizlemeyi göstermeyi önlemek için mevcut canvas'ı temizle
            const ctx = canvas.getContext('2d');
            if (ctx) {
              ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
          }
        }

        // Render preview - use multiple strategies to ensure it works
        const renderPreview = () => {
          if (window.renderPreviewWithSpotData) {
            try { window.renderPreviewWithSpotData(previewImg); } catch(e) {}
          }
        };
        if (previewImg.complete) {
          requestAnimationFrame(renderPreview);
        } else {
          previewImg.addEventListener('load', () => requestAnimationFrame(renderPreview), { once: true });
        }

        return true;
      } catch (e) {
        console.warn('Image Editor - Failed to parse spotData:', e);
        return false;
      }
    },

    /**
     * Helper: Update preview with retry logic
     * @param {string} spotDataJson - JSON string of spot_data
     * @param {string|null} savedFileId - Saved fileId (for use after editor closes)
     * @param {number|null} savedIndex - Saved index (for use after editor closes)
     * @param {number} maxAttempts - Maximum retry attempts
     */
    updatePreviewWithRetry(spotDataJson, savedFileId = null, savedIndex = null, savedImageKey = null, maxAttempts = 3) {
      const updatePreview = (attempt = 1) => {
        // NEW: Prioritize imageKey-based search
        const targetImageKey = savedImageKey || this.currentImageKey;
        let previewImg = null;

        if (targetImageKey) {
          previewImg = document.querySelector(`img[data-image-key="${targetImageKey}"]`);
        }

        // Legacy fallback: Use saved identifiers if imageKey not found
        if (!previewImg) {
          previewImg = this.findPreviewImageElementByIdentifiers(savedFileId, savedIndex, targetImageKey);
        }
        if (previewImg) {
          if (this.updatePreviewElement(previewImg, spotDataJson)) {
            // Success
            if (window.initPreviewRenderer) {
              window.initPreviewRenderer();
            }
            return;
          }
        }

        // Retry if not found and attempts remaining
        if (attempt < maxAttempts) {
          setTimeout(() => updatePreview(attempt + 1), 200 * attempt);
        } else {
          // Final attempt - trigger initPreviewRenderer anyway
          if (window.initPreviewRenderer) {
            window.initPreviewRenderer();
          }
        }
      };

      // Initial attempt
      updatePreview();

      document.addEventListener('livewire:updated', () => setTimeout(updatePreview, 200), { once: true });
    },

    /**
     * Save edited image data (spot_data only, NOT the edited image file)
     * IMPORTANT: We save only the spot_data (crop, effects, textObjects, etc.)
     * The original image file is preserved and never overwritten.
     * The edited preview is rendered client-side using spot_data.
     */
    async saveEditedImage() {
      if (!this.canvas) return;

      try {
        // Loading state
        if (window.showToast) {
          window.showToast('Düzenlemeler kaydediliyor...', 'info');
        }

        // Find parent Livewire component
        const parentWire = this.findParentLivewireComponent();

        // Save current canvas dimensions for scaling on reload
        this.savedCanvasWidth = this.canvasWidth;
        this.savedCanvasHeight = this.canvasHeight;

        // IMPORTANT: originalImagePath should always point to the ORIGINAL image
        // NOT the edited version. This ensures that on subsequent edits, we always load
        // the original image and apply all edits from scratch.
        // originalImagePath should already be set from spot_data when opening the editor
        if (!this.originalImagePath && this.imageUrl) {
          let newOriginalPath = this.imageUrl;
          newOriginalPath = newOriginalPath.replace(/^https?:\/\/[^\/]+/, '');
          newOriginalPath = newOriginalPath.replace(/^\/storage\//, '');
          newOriginalPath = newOriginalPath.replace(/^storage\//, '');
          // Avoid setting Livewire temporary URLs as original path
          if (newOriginalPath.includes('livewire/preview-file')) {
            this.originalImagePath = null;
          } else {
            this.originalImagePath = newOriginalPath || null;
          }
        }

        // Build editor data using helper
        const editorData = this.buildEditorData();
        const editorDataJson = JSON.stringify(editorData);

        // Build and update spot_data
        const spotData = this.buildSpotData(editorData);
        const spotDataJson = JSON.stringify(spotData);

        // Extract image data for hidden input (only the 'image' part, not the wrapper)
        // spotData format: { image: {...} }
        // For hidden input, we need just the image object: {...}
        // This will be saved to spot_data['image'] in database
        const imageDataJson = spotData.image ? JSON.stringify(spotData.image) : spotDataJson;


        // CRITICAL: Save imageKey BEFORE closing editor (NEW: imageKey-based approach)
        // This is the single source of truth for identifying the image
        const savedImageKey = this.currentImageKey;

        // Also save legacy identifiers for backward compatibility
        const savedFileId = this.currentFileId;
        const savedIndex = this.currentIndex;

        // Update spotData in state (NEW: imageKey-based state management)
        if (savedImageKey) {
          updateImageSpotData(savedImageKey, spotData);
        }


        // IMPORTANT: We do NOT send the edited image file to the server
        // Sadece veritabanını güncellemek için spot_data'yı (editorData) gönderiyoruz
        // Orijinal görsel dosyası değişmeden kalır

        if (parentWire && typeof parentWire.call === 'function') {
          try {
            // Extract identifier from imageKey if savedFileId/savedIndex are not available
            // imageKey format: 'temp:file_1763736804_7504' or 'existing:123'
            let identifier = savedFileId !== null && savedFileId !== undefined
              ? savedFileId
              : (savedIndex !== null && savedIndex !== undefined ? savedIndex : '');

            // If identifier is still empty, try to extract from imageKey
            if ((identifier === '' || identifier === null || identifier === undefined) && savedImageKey) {
              if (savedImageKey.startsWith('temp:')) {
                // Extract fileId from temp:file_xxx format
                const fileIdFromKey = savedImageKey.replace('temp:', '');
                identifier = fileIdFromKey;
              } else if (savedImageKey.startsWith('existing:')) {
                // Extract fileId from existing:xxx format
                const fileIdFromKey = savedImageKey.replace('existing:', '');
                identifier = fileIdFromKey;
              }
            }


            // Get original image URL (not the edited version)
            const originalImageUrl = this.originalImagePath
              ? (this.originalImagePath.startsWith('http')
                  ? this.originalImagePath
                  : `${window.location.origin}/storage/${this.originalImagePath}`)
              : this.imageUrl;

            // Call updateFilePreview only if identifier is valid
            // The imageUrl parameter is the ORIGINAL image, not the edited version
            // The server will NOT update the file_path, only the spot_data
            // ÖNEMLİ: imageDataJson'ı (spot_data görsel objesi) gönder, editorDataJson'ı (ham editör verisi) değil
            if (identifier !== '' && identifier !== null && identifier !== undefined) {

              parentWire.call('updateFilePreview', identifier, originalImageUrl, null, imageDataJson);

            } else {
              console.warn('Image Editor - updateFilePreview NOT called: invalid identifier', {
                identifier,
                savedFileId,
                savedIndex,
              });
            }
          } catch (e) {
            console.error('Image Editor - Could not call updateFilePreview:', e);
          }
        } else {
          console.warn('Image Editor - updateFilePreview NOT called: parentWire not found or invalid', {
            hasParentWire: !!parentWire,
            parentWireType: parentWire ? typeof parentWire.call : 'null',
          });
        }

        // Dispatch event (NEW: include imageKey)
        window.dispatchEvent(new CustomEvent('image-editor:saved', {
          detail: {
            imageKey: savedImageKey,
            fileId: savedFileId,
            index: savedIndex,
            spotData: spotData,
          }
        }));

        // Also dispatch legacy event for backward compatibility
        window.dispatchEvent(new CustomEvent('image-edited', {
          detail: {
            fileId: savedFileId,
            index: savedIndex,
            spotData: spotData,
          }
        }));

        // Update preview element with spotData (NEW: imageKey-based)
        if (savedImageKey) {
          const previewImg = document.querySelector(`img[data-image-key="${savedImageKey}"]`);
          if (previewImg) {
            // Update img element's data-spot-data attribute (single source of truth)
            previewImg.setAttribute('data-spot-data', spotDataJson);
            previewImg.setAttribute('data-has-spot-data', 'true');

            // Ensure renderer runs after Livewire re-render
            setTimeout(() => {
              if (window.renderPreviewWithSpotData) {
                try { window.renderPreviewWithSpotData(previewImg); } catch(e) {}
              }
              if (window.initPreviewRenderer) {
                try { window.initPreviewRenderer(); } catch(e) {}
              }
            }, 50);

            // Update canvas if exists
            const canvas = document.querySelector(`canvas[data-image-key="${savedImageKey}"]`);
            if (canvas) {
              const ctx = canvas.getContext('2d');
              if (ctx) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
              }
            }

            // NEW: If this is primary image, update hidden input for Livewire sync
            // Primary image key format: 'existing:<fileId>' (for archive files) or 'temp:<fileId>' (for uploaded files)
            // For archive files (existing:), always update the hidden input
            // For uploaded files (temp:), check if it's the primary file
            const isArchiveFile = savedImageKey.startsWith('existing:');
            const isUploadedFile = savedImageKey.startsWith('temp:');
            let shouldUpdatePrimaryInput = false;

            if (isArchiveFile) {
              // Archive files: always update (they might become primary later)
              shouldUpdatePrimaryInput = true;
            } else if (isUploadedFile) {
              // Uploaded files: check if this is the primary file
              // Try to find the primary file radio button or check primaryFileId
              const fileIdFromKey = savedImageKey.replace('temp:', '');
              const primaryRadio = document.querySelector(`input[name="primaryFile"][value="${fileIdFromKey}"]`);
              if (primaryRadio && primaryRadio.checked) {
                shouldUpdatePrimaryInput = true;
              }
            }

            if (shouldUpdatePrimaryInput) {
              const primaryImageInput = document.getElementById('primary_image_spot_data');
              if (primaryImageInput) {
                // Update hidden input value with only the image object (not the wrapper)
                // spotData format: { image: {...} }
                // hidden input needs: {...} (just the image object)
                primaryImageInput.value = imageDataJson;

                // Trigger input event for Livewire wire:model to sync
                // Use both native input event and Livewire's wire:model event
                primaryImageInput.dispatchEvent(new Event('input', { bubbles: true }));
                primaryImageInput.dispatchEvent(new Event('change', { bubbles: true }));

                // Force Livewire to sync by calling wire:model update directly
                // Try multiple methods to ensure sync
                const syncToLivewire = () => {
                  try {
                    // Method 1: Find Livewire component by wire:id
                    const wireId = primaryImageInput.closest('[wire\\:id]')?.getAttribute('wire:id') ||
                                  document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
                    if (wireId && window.Livewire && window.Livewire.find) {
                      const component = window.Livewire.find(wireId);
                      if (component && component.set) {
                        component.set('primary_image_spot_data', imageDataJson);
                        return true;
                      }
                    }

                    // Method 2: Use $wire from Alpine.js context
                    if (window.Alpine && primaryImageInput.closest('[x-data]')) {
                      const alpineEl = primaryImageInput.closest('[x-data]');
                      if (alpineEl && alpineEl._x_dataStack && alpineEl._x_dataStack[0] && alpineEl._x_dataStack[0].$wire) {
                        alpineEl._x_dataStack[0].$wire.set('primary_image_spot_data', imageDataJson);
                        return true;
                      }
                    }

                    // Method 3: Use Livewire's wire:model sync mechanism
                    // Trigger wire:model:sync event
                    primaryImageInput.dispatchEvent(new CustomEvent('wire:model:sync', {
                      bubbles: true,
                      detail: { value: imageDataJson }
                    }));

                  } catch (e) {
                    console.warn('Image Editor - Failed to sync to Livewire:', e);
                  }
                  return false;
                };

                // Sync immediately
                syncToLivewire();

                // Also sync after a short delay to ensure it's processed
                setTimeout(() => {
                  syncToLivewire();
                }, 100);

              } else {
                console.warn('Image Editor - primary_image_spot_data hidden input not found');
              }
            }

            // Trigger preview render
            if (window.renderPreviewWithSpotData) {
              try {
                window.renderPreviewWithSpotData(previewImg);
              } catch (e) {
                console.warn('Image Editor - Preview render error:', e);
              }
            }
          }
        }

        // Update preview with retry logic (NEW: using imageKey, with legacy fallback)
        this.updatePreviewWithRetry(spotDataJson, savedFileId, savedIndex, savedImageKey);

        // Close editor after a short delay to ensure preview update starts
        setTimeout(() => {
          this.closeEditor();
        }, 100);

        // Force preview update after editor closes (NEW: using imageKey)
        setTimeout(() => {
          if (savedImageKey) {
            const previewImg = document.querySelector(`img[data-image-key="${savedImageKey}"]`);
            if (previewImg && window.renderPreviewWithSpotData) {
              try {
                window.renderPreviewWithSpotData(previewImg);
              } catch (e) {
                console.warn('Image Editor - Force preview render error:', e);
              }
            }
          }

          // Legacy fallback (using imageKey if available)
          const previewImg = this.findPreviewImageElementByIdentifiers(savedFileId, savedIndex, savedImageKey);
          if (previewImg && window.renderPreviewWithSpotData) {
            try {
              window.renderPreviewWithSpotData(previewImg);
            } catch (e) {
              console.warn('Image Editor - Force preview render error (legacy):', e);
            }
          }

          if (window.initPreviewRenderer) {
            window.initPreviewRenderer();
          }
        }, 500);

        if (window.showToast) {
          window.showToast('Düzenlemeler başarıyla kaydedildi', 'success');
        }
      } catch (error) {
        console.error('Error saving image data:', error);
        if (window.showToast) {
          const errorMessage = error.message || 'Bilinmeyen bir hata oluştu';
          window.showToast('Düzenlemeler kaydedilirken bir hata oluştu: ' + errorMessage, 'error');
        }
      }
    },

    /**
     * Auto-save editor data (textObjects, effects, etc.) without saving the image
     * This is called when text is deleted or modified to keep Livewire in sync
     */
    autoSaveEditorData() {
      if (!this.canvas) return;

      const parentWire = this.findParentLivewireComponent();
      if (!parentWire || typeof parentWire.call !== 'function') {
        return;
      }

      // Build editor data using helper
      const editorData = this.buildEditorData();

      // Build spot_data from editor data
      const spotData = this.buildSpotData(editorData);

      // Extract image data for updateFilePreview (only the 'image' part, not the wrapper)
      // spotData format: { image: {...} }
      // For updateFilePreview, we need just the image object: {...}
      const imageDataJson = spotData.image ? JSON.stringify(spotData.image) : JSON.stringify(spotData);

      try {
        // Use current image URL (don't upload new image, just update editor data)
        const identifier = this.currentFileId !== null && this.currentFileId !== undefined
          ? this.currentFileId
          : (this.currentIndex !== null && this.currentIndex !== undefined ? this.currentIndex : null);

        if (identifier !== null) {
          // ÖNEMLİ: imageDataJson'ı (spot_data görsel objesi) gönder, editorDataJson'ı değil
          parentWire.call('updateFilePreview', identifier, this.imageUrl, null, imageDataJson);
        }

        // Build full spot_data JSON for preview update
        const spotDataJson = JSON.stringify(spotData);

        // Update preview with retry logic
        this.updatePreviewWithRetry(spotDataJson, 5);
      } catch (e) {
        console.warn('Image Editor - Auto-save editor data failed:', e);
      }
    },
  };
}

