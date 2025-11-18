/**
 * Image Editor - Save Operations
 * Saving edited images and updating previews
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
     * Helper: Find parent Livewire component
     */
    findParentLivewireComponent() {
      // Try to find Livewire component using Livewire API
      if (window.Livewire) {
        const livewireComponents = window.Livewire.all();
        if (livewireComponents && livewireComponents.length > 0) {
          for (const component of livewireComponents) {
            if (component.__instance && typeof component.__instance.updateFilePreview === 'function') {
              return component;
            }
          }
        }
      }

      // Fallback: Try to find by traversing DOM
      const editorEl = document.querySelector('[x-data*="imageEditor"]');
      if (editorEl) {
        let current = editorEl.parentElement;
        while (current && current !== document.body) {
          if (current.hasAttribute && current.hasAttribute('wire:id')) {
            const wireId = current.getAttribute('wire:id');
            if (window.Livewire && window.Livewire.find) {
              const component = window.Livewire.find(wireId);
              if (component && component.__instance && typeof component.__instance.updateFilePreview === 'function') {
                return component;
              }
            }
          }
          current = current.parentElement;
        }
      }

      return null;
    },

    /**
     * Helper: Find preview image element by imageKey (NEW: imageKey-based approach)
     * Falls back to legacy fileId/index if imageKey not provided
     */
    findPreviewImageElementByIdentifiers(fileId = null, index = null, imageKey = null) {
      // NEW: Prioritize imageKey-based search
      const targetImageKey = imageKey || this.currentImageKey;
      if (targetImageKey) {
        const img = document.querySelector(`img[data-image-key="${targetImageKey}"]`);
        if (img) {
          console.log('Image Editor - Found preview image by imageKey:', targetImageKey);
          return img;
        }
      }

      // Legacy fallback: Try by fileId/index (for backward compatibility)
      const targetFileId = fileId !== null && fileId !== undefined ? fileId : this.currentFileId;
      const targetIndex = index !== null && index !== undefined ? index : this.currentIndex;

      // First try to find by file ID (most specific)
      if (targetFileId) {
        const img = document.querySelector(`#preview-img-${targetFileId}`);
        if (img && img.tagName === 'IMG') {
          console.log('Image Editor - Found preview image by fileId (legacy):', targetFileId);
          return img;
        }

        // Also try by data-file-id attribute
        const imgByDataAttr = document.querySelector(`img[data-file-id="${targetFileId}"]`);
        if (imgByDataAttr) {
          console.log('Image Editor - Found preview image by data-file-id (legacy):', targetFileId);
          return imgByDataAttr;
        }
      }

      // Try by index (ONLY IMG elements with matching index)
      if (targetIndex !== null && targetIndex !== undefined) {
        // Try exact match first
        let img = document.querySelector(`img[data-file-index="${targetIndex}"]`);
        if (img) {
          console.log('Image Editor - Found preview image by exact index (legacy):', targetIndex);
          return img;
        }

        // Try in all images with data-file-index
        const allImages = document.querySelectorAll('img[data-file-index]');
        for (const imgEl of allImages) {
          const imgIndex = imgEl.getAttribute('data-file-index');
          // Strict comparison - must match exactly
          if (String(imgIndex) === String(targetIndex) || Number(imgIndex) === Number(targetIndex)) {
            console.log('Image Editor - Found preview image by matching index (legacy):', targetIndex);
            return imgEl;
          }
        }

        // Try preview-img-* id pattern with matching index
        const allPreviewImages = document.querySelectorAll('img[id^="preview-img-"]');
        for (const imgEl of allPreviewImages) {
          const imgIndex = imgEl.getAttribute('data-file-index');
          // Strict comparison - must match exactly
          if (String(imgIndex) === String(targetIndex) || Number(imgIndex) === Number(targetIndex)) {
            console.log('Image Editor - Found preview image by id pattern and index (legacy):', targetIndex);
            return imgEl;
          }
        }
      }

      // CRITICAL: Do NOT use fallback methods that return random images
      // This would cause wrong preview to show for other posts
      // If we can't find the exact match, return null and let retry logic handle it
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
     * Helper: Find preview image element
     * IMPORTANT: Must find the EXACT image element matching currentFileId or currentIndex
     * Never return a random image element, as this would show wrong preview for other posts
     */
    findPreviewImageElement() {
      return this.findPreviewImageElementByIdentifiers();
    },

    /**
     * Helper: Update preview element with spot_data
     * IMPORTANT: Must clear canvas before updating to prevent showing old preview
     */
    updatePreviewElement(previewImg, spotDataJson) {
      if (!previewImg || !spotDataJson) {
        console.warn('Image Editor - updatePreviewElement: Missing previewImg or spotDataJson');
        return false;
      }

      // Validate spotData
      // Note: Minimum length check is lenient (20 chars) to allow small but valid JSON
      // Very small JSONs like {"image":{}} are still valid, just unlikely to have meaningful data
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

        // Verify this is the correct image element
        const fileId = previewImg.id.replace('preview-img-', '');
        const dataFileId = previewImg.getAttribute('data-file-id');
        const dataFileIndex = previewImg.getAttribute('data-file-index');

        // Verify match with currentFileId or currentIndex
        if (this.currentFileId) {
          if (fileId !== String(this.currentFileId) && dataFileId !== String(this.currentFileId)) {
            console.warn('Image Editor - updatePreviewElement: FileId mismatch', {
              fileId: fileId,
              dataFileId: dataFileId,
              currentFileId: this.currentFileId,
            });
            return false;
          }
        } else if (this.currentIndex !== null && this.currentIndex !== undefined) {
          if (String(dataFileIndex) !== String(this.currentIndex)) {
            console.warn('Image Editor - updatePreviewElement: Index mismatch', {
              dataFileIndex: dataFileIndex,
              currentIndex: this.currentIndex,
            });
            return false;
          }
        }

        console.log('Image Editor - updatePreviewElement: Updating preview', {
          fileId: fileId,
          dataFileId: dataFileId,
          dataFileIndex: dataFileIndex,
          currentFileId: this.currentFileId,
          currentIndex: this.currentIndex,
        });

        // Update attributes - CRITICAL: These attributes are the single source of truth for spot_data
        previewImg.setAttribute('data-spot-data', spotDataJson);
        previewImg.setAttribute('data-has-spot-data', 'true');

        // Set data-file-index if needed (for new uploads)
        if (this.currentIndex !== null && this.currentIndex !== undefined) {
          previewImg.setAttribute('data-file-index', String(this.currentIndex));
        }

        // Set data-file-id if we have currentFileId (for existing files)
        if (this.currentFileId) {
          previewImg.setAttribute('data-file-id', String(this.currentFileId));
        }

        // Ensure data-image-url is set if not already present
        // This helps with re-edit scenarios where we need the original image URL
        if (!previewImg.getAttribute('data-image-url') && this.imageUrl) {
          previewImg.setAttribute('data-image-url', this.imageUrl);
        }

        // Ensure canvas exists and clear it
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
            // Clear existing canvas to prevent showing old preview
            const ctx = canvas.getContext('2d');
            if (ctx) {
              ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
          }
        }

        // Render preview - use multiple strategies to ensure it works
        const renderPreview = () => {
          if (window.renderPreviewWithSpotData) {
            try {
              window.renderPreviewWithSpotData(previewImg);
            } catch (e) {
              console.warn('Image Editor - renderPreviewWithSpotData error:', e);
            }
          }
        };

        // Try immediate render if image is loaded
        if (previewImg.complete) {
          // Use requestAnimationFrame to ensure DOM is ready
          requestAnimationFrame(() => {
            setTimeout(renderPreview, 50);
          });
        } else {
          // Wait for image load
          previewImg.addEventListener('load', function() {
            requestAnimationFrame(() => {
              setTimeout(renderPreview, 50);
            });
          }, { once: true });

          // Also try after a delay in case load event doesn't fire
          setTimeout(() => {
            if (previewImg.complete) {
              renderPreview();
            }
          }, 200);
        }

        // Force render after a short delay to ensure everything is ready
        setTimeout(renderPreview, 100);
        setTimeout(renderPreview, 300);
        setTimeout(renderPreview, 500);

        console.log('Image Editor - updatePreviewElement: Preview updated successfully');
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
    updatePreviewWithRetry(spotDataJson, savedFileId = null, savedIndex = null, savedImageKey = null, maxAttempts = 10) {
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

      // Listen for Livewire events with longer delays
      const livewireEvents = ['livewire:update', 'livewire:updated', 'livewire:load'];
      livewireEvents.forEach(eventName => {
        document.addEventListener(eventName, () => {
          // Multiple attempts with increasing delays
          setTimeout(() => {
            updatePreview();
            setTimeout(() => {
              updatePreview();
              if (window.initPreviewRenderer) {
                window.initPreviewRenderer();
              }
            }, 200);
          }, 300);

          setTimeout(() => {
            updatePreview();
            if (window.initPreviewRenderer) {
              window.initPreviewRenderer();
            }
          }, 800);

          setTimeout(() => {
            updatePreview();
            if (window.initPreviewRenderer) {
              window.initPreviewRenderer();
            }
          }, 1500);
        }, { once: true });
      });

      // Multiple fallback attempts with increasing delays
      setTimeout(() => {
        updatePreview();
        if (window.initPreviewRenderer) {
          window.initPreviewRenderer();
        }
      }, 500);

      setTimeout(() => {
        updatePreview();
        if (window.initPreviewRenderer) {
          window.initPreviewRenderer();
        }
      }, 1000);

      setTimeout(() => {
        updatePreview();
        if (window.initPreviewRenderer) {
          window.initPreviewRenderer();
        }
      }, 2000);

      setTimeout(() => {
        updatePreview();
        if (window.initPreviewRenderer) {
          window.initPreviewRenderer();
        }
      }, 3000);
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
          // Extract path from imageUrl if originalImagePath is not set
          let newOriginalPath = this.imageUrl;
          // Remove domain and /storage/ prefix to get relative path
          newOriginalPath = newOriginalPath.replace(/^https?:\/\/[^\/]+/, ''); // Remove domain
          newOriginalPath = newOriginalPath.replace(/^\/storage\//, ''); // Remove /storage/ prefix
          newOriginalPath = newOriginalPath.replace(/^storage\//, ''); // Remove storage/ prefix
          this.originalImagePath = newOriginalPath || null;
          console.log('Image Editor - Set originalImagePath from imageUrl:', this.originalImagePath);
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

        console.log('Image Editor - saveEditedImage: Built spotData', {
          spotDataStructure: Object.keys(spotData),
          hasImage: !!spotData.image,
          imageDataJsonLength: imageDataJson.length,
          spotDataJsonLength: spotDataJson.length,
        });

        // CRITICAL: Save imageKey BEFORE closing editor (NEW: imageKey-based approach)
        // This is the single source of truth for identifying the image
        const savedImageKey = this.currentImageKey;

        // Also save legacy identifiers for backward compatibility
        const savedFileId = this.currentFileId;
        const savedIndex = this.currentIndex;

        // Update spotData in state (NEW: imageKey-based state management)
        if (savedImageKey) {
          updateImageSpotData(savedImageKey, spotData);
          console.log('Image Editor - Updated spotData in state for imageKey:', savedImageKey);
        }

        console.log('Image Editor - Built spot_data after save:', {
          originalImagePath: spotData.image?.original?.path,
          hasTextObjects: !!(spotData.image?.textObjects && spotData.image.textObjects.length > 0),
          textObjectsCount: spotData.image?.textObjects?.length || 0,
          hasEffects: !!spotData.image?.effects,
          canvas: spotData.image?.canvas,
          savedFileId: savedFileId,
          savedIndex: savedIndex,
        });

        // IMPORTANT: We do NOT send the edited image file to the server
        // We only send the spot_data (editorData) to update the database
        // The original image file remains unchanged
        if (parentWire && typeof parentWire.call === 'function') {
          try {
            const identifier = savedFileId !== null && savedFileId !== undefined
              ? savedFileId
              : (savedIndex !== null && savedIndex !== undefined ? savedIndex : '');

            // Get original image URL (not the edited version)
            const originalImageUrl = this.originalImagePath
              ? (this.originalImagePath.startsWith('http')
                  ? this.originalImagePath
                  : `${window.location.origin}/storage/${this.originalImagePath}`)
              : this.imageUrl;

            // Call updateFilePreview with original image URL and editorData
            // The imageUrl parameter is the ORIGINAL image, not the edited version
            // The server will NOT update the file_path, only the spot_data
            parentWire.call('updateFilePreview', identifier, originalImageUrl, null, editorDataJson);
          } catch (e) {
            console.error('Image Editor - Could not call updateFilePreview:', e);
          }
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

            // Update canvas if exists
            const canvas = document.querySelector(`canvas[data-image-key="${savedImageKey}"]`);
            if (canvas) {
              const ctx = canvas.getContext('2d');
              if (ctx) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
              }
            }

            // NEW: If this is primary image, update hidden input for Livewire sync
            // Primary image key format: 'existing:<fileId>'
            // Check if this imageKey corresponds to primary image
            if (savedImageKey.startsWith('existing:')) {
              const primaryImageInput = document.getElementById('primary_image_spot_data');
              if (primaryImageInput) {
                // Update hidden input value with only the image object (not the wrapper)
                // spotData format: { image: {...} }
                // hidden input needs: {...} (just the image object)
                primaryImageInput.value = imageDataJson;

                // Trigger input event for Livewire wire:model.lazy to sync
                primaryImageInput.dispatchEvent(new Event('input', { bubbles: true }));

                // Also trigger change event for better compatibility
                primaryImageInput.dispatchEvent(new Event('change', { bubbles: true }));

                console.log('Image Editor - Updated primary_image_spot_data hidden input:', {
                  imageKey: savedImageKey,
                  imageDataLength: imageDataJson.length,
                  hasImage: !!spotData.image,
                });
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
      const editorDataJson = JSON.stringify(editorData);

      try {
        // Use current image URL (don't upload new image, just update editor data)
        const identifier = this.currentFileId !== null && this.currentFileId !== undefined
          ? this.currentFileId
          : (this.currentIndex !== null && this.currentIndex !== undefined ? this.currentIndex : null);

        if (identifier !== null) {
          parentWire.call('updateFilePreview', identifier, this.imageUrl, null, editorDataJson);
        }

        // Build and update spot_data
        const spotData = this.buildSpotData(editorData);
        const spotDataJson = JSON.stringify(spotData);

        // Update preview with retry logic
        this.updatePreviewWithRetry(spotDataJson, 5);
      } catch (e) {
        console.warn('Image Editor - Auto-save editor data failed:', e);
      }
    },
  };
}

