/**
 * Image Editor - Main Entry Point
 * Combines all modules into a single Alpine.js component
 */

import { createInitialState, registerImage, setCurrentImage, updateImageSpotData, getImageConfig, parseImageKey } from './state.js';
import { createCanvasMethods } from './canvas.js';
import { createImageLoaderMethods } from './image-loader.js';
import { createSpotDataMethods } from './spot-data.js';
import { createTextMethods } from './text.js';
import { createEffectsMethods } from './effects.js';
import { createHistoryMethods } from './history.js';
import { createSaveMethods } from './save.js';
import { createToolsMethods } from './tools.js';
import { createEventsMethods } from './events.js';

/**
 * Register Image Editor Alpine.js component
 */
export function registerImageEditor() {
  // Alpine.js is loaded globally in app.js, we'll access it from window
  // This function is called after Alpine is initialized (in alpine:init event)
  const Alpine = window.Alpine;

  if (!Alpine) {
    console.error('Alpine.js is not loaded. Make sure app.js is loaded first.');
    return;
  }

  // Global helper function to open image editor
  // NEW API: window.openImageEditor(imageKey, config)
  // imageKey format: "existing:<fileId>" or "temp:<index>"
  // config: { url, type, fileId, index, initialSpotData }
  if (typeof window !== 'undefined') {
    // Only initialize if not already initialized
    // But allow re-initialization if window.openImageEditor is not defined
    if (window.__IMAGE_EDITOR_INITED__ && typeof window.openImageEditor === 'function') {
      console.warn('Image Editor - Already initialized, skipping');
      return;
    }
    window.__IMAGE_EDITOR_INITED__ = true;

    window.openImageEditor = function(imageKey, config = {}) {
      // Validate imageKey
      if (!imageKey) {
        console.error('Image Editor - openImageEditor: imageKey is required');
        return;
      }

      // Parse imageKey to extract type, fileId, index
      const parsed = parseImageKey(imageKey);

      // Build config from imageKey if not provided
      const finalConfig = {
        url: config.url || '',
        type: config.type || parsed.type,
        fileId: config.fileId !== undefined ? config.fileId : parsed.fileId,
        index: config.index !== undefined ? config.index : parsed.index,
        initialSpotData: config.initialSpotData || null,
      };

      // If image not registered, register it
      const existingConfig = getImageConfig(imageKey);
      if (!existingConfig) {
        registerImage({
          imageKey,
          type: finalConfig.type,
          fileId: finalConfig.fileId,
          index: finalConfig.index,
          url: finalConfig.url,
          spotData: finalConfig.initialSpotData,
        });
      } else {
        // Update URL and spotData if provided
        if (finalConfig.url) {
          existingConfig.url = finalConfig.url;
        }
        if (finalConfig.initialSpotData !== null) {
          updateImageSpotData(imageKey, finalConfig.initialSpotData);
        }
      }

      // Set current image
      setCurrentImage(imageKey);

      // Get final config (from state or provided)
      const imageConfig = getImageConfig(imageKey);
      if (!imageConfig) {
        console.error('Image Editor - openImageEditor: Failed to get image config for:', imageKey);
        return;
      }

      // Try to find the editor instance
      const editorEl = document.querySelector('[x-data*="imageEditor"]');
      if (editorEl && editorEl._x_dataStack && editorEl._x_dataStack[0]) {
        editorEl._x_dataStack[0].openEditorWithImageKey(imageKey, imageConfig);
        return;
      }

      // Fallback: use global reference
      if (window.postsImageEditor && typeof window.postsImageEditor.openEditorWithImageKey === 'function') {
        window.postsImageEditor.openEditorWithImageKey(imageKey, imageConfig);
        return;
      }

      // Wait for Alpine to be ready if not already
      if (window.Alpine && !window.Alpine.store) {
        const checkAlpine = setInterval(() => {
          const editorEl = document.querySelector('[x-data*="imageEditor"]');
          if (editorEl && editorEl._x_dataStack && editorEl._x_dataStack[0]) {
            clearInterval(checkAlpine);
            editorEl._x_dataStack[0].openEditorWithImageKey(imageKey, imageConfig);
          } else if (window.postsImageEditor && typeof window.postsImageEditor.openEditorWithImageKey === 'function') {
            clearInterval(checkAlpine);
            window.postsImageEditor.openEditorWithImageKey(imageKey, imageConfig);
          }
        }, 50);

        // Timeout after 2 seconds
        setTimeout(() => {
          clearInterval(checkAlpine);
          console.warn('Image Editor - Alpine not ready after 2 seconds, dispatching event');
          window.dispatchEvent(new CustomEvent('open-image-editor', {
            detail: { imageKey, config: imageConfig }
          }));
        }, 2000);
        return;
      }

      // Last resort: dispatch event
      console.warn('Image Editor - Editor instance not found, dispatching event');
      window.dispatchEvent(new CustomEvent('open-image-editor', {
        detail: { imageKey, config: imageConfig }
      }));
    };
  }

  // Create Alpine.js component by combining all modules
  window.Alpine.data('imageEditor', () => {
    // Initialize state
    const state = createInitialState();

    // Combine all method modules
    const methods = {
      ...createCanvasMethods(),
      ...createImageLoaderMethods(),
      ...createSpotDataMethods(),
      ...createTextMethods(),
      ...createEffectsMethods(),
      ...createHistoryMethods(),
      ...createSaveMethods(),
      ...createToolsMethods(),
      ...createEventsMethods(),

      /**
       * Initialize component
       */
      init() {
        this.setupKeyboardShortcuts();

        // Set global reference immediately
        if (typeof window !== 'undefined') {
          window.postsImageEditor = this;
        }
      },

      /**
       * Open image editor with imageKey (NEW API)
       * @param {string} imageKey - Image key (existing:<fileId> or temp:<index>)
       * @param {object} imageConfig - Image configuration from state
       */
      openEditorWithImageKey(imageKey, imageConfig) {
        if (!imageKey || !imageConfig) {
          console.error('Image Editor - openEditorWithImageKey: imageKey and imageConfig are required');
          return;
        }

        // Set current image key
        this.currentImageKey = imageKey;
        setCurrentImage(imageKey);

        // Extract data from config
        const url = imageConfig.url || '';
        let spotData = imageConfig.spotData || null;
        const fileId = imageConfig.fileId;
        const index = imageConfig.index;

        // IMPORTANT: If spotData is null, try to get it from img element's data-spot-data attribute
        // This handles cases where initialSpotData wasn't passed or failed to parse
        if (!spotData) {
          const img = document.querySelector(`img[data-image-key="${imageKey}"]`);
          if (img) {
            const spotJson = img.getAttribute('data-spot-data');
            if (spotJson && spotJson.length > 20) {
              try {
                // Handle HTML-escaped JSON
                let parsedJson = spotJson;
                if (spotJson.includes('&quot;') || spotJson.includes('&amp;') || spotJson.includes('&lt;') || spotJson.includes('&gt;')) {
                  const tempDiv = document.createElement('div');
                  tempDiv.innerHTML = spotJson;
                  parsedJson = tempDiv.textContent || tempDiv.innerText || spotJson;
                }
                spotData = JSON.parse(parsedJson);
              } catch (e) {
                console.warn('Image Editor - openEditorWithImageKey: Failed to parse spotData from img element:', e);
              }
            }
          }
        }

        // Handle nested structure: if spotData has 'image.image', unwrap it
        // This prevents issues with double-nested spot_data structure
        if (spotData && spotData.image && spotData.image.image && typeof spotData.image.image === 'object') {
          // Unwrap: use spotData.image.image instead
          spotData = { image: spotData.image.image };
        }

        // Set legacy identifiers for backward compatibility
        this.currentFileId = fileId || null;
        this.currentIndex = index !== undefined ? index : null;

        // Call legacy openEditor with extracted data
        this.openEditor(fileId || index, url, spotData);
      },

      /**
       * Open image editor with image and optional spot_data (LEGACY API - kept for compatibility)
       * @param {string|number} identifier - File ID or index
       * @param {string} url - Image URL
       * @param {object|null} spotData - Spot data containing saved edits
       */
      openEditor(identifier, url, spotData = null) {
        // IMPORTANT: If spotData is provided, use original image path from spotData
        // The url parameter might be the edited preview, but we always want to load the original
        let imageUrlToUse = url;

        // Handle nested structure: if spotData has 'image.image', unwrap it
        let normalizedSpotDataForUrl = spotData;
        if (spotData && spotData.image && spotData.image.image && typeof spotData.image.image === 'object') {
          normalizedSpotDataForUrl = { image: spotData.image.image };
        }

        if (normalizedSpotDataForUrl && normalizedSpotDataForUrl.image && normalizedSpotDataForUrl.image.original && normalizedSpotDataForUrl.image.original.path) {
          // Use original image path from spot_data
          const originalPath = normalizedSpotDataForUrl.image.original.path;

          // Convert relative path to full URL if needed
          if (!originalPath.startsWith('http') && !originalPath.startsWith('data:')) {
            // Handle storage paths
            if (originalPath.startsWith('storage/') || originalPath.startsWith('/storage/')) {
              imageUrlToUse = window.location.origin + (originalPath.startsWith('/') ? '' : '/') + originalPath;
            } else if (!originalPath.startsWith('/')) {
              // Relative path without storage prefix
              imageUrlToUse = window.location.origin + '/storage/' + originalPath;
            } else {
              // Absolute path
              imageUrlToUse = window.location.origin + originalPath;
            }
          } else {
            imageUrlToUse = originalPath;
          }

          // Using original image from spot_data
        } else if (!url) {
          console.error('ImageEditor: URL is required');
          return;
        }

        // Opening editor

        // Initialize editor state
        this.isOpen = true;
        this.setIdentifier(identifier, spotData);

        // IMPORTANT: Set imageUrl BEFORE loading spot_data
        // This ensures resolveImageSource can use originalImagePath if available
        this.imageUrl = imageUrlToUse;
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;
        this.history = [];
        this.historyIndex = -1;

        // Load spot_data if available
        // Handle nested structure: if spotData has 'image.image', unwrap it
        // This prevents issues with double-nested spot_data structure
        let normalizedSpotData = spotData;
        if (spotData && spotData.image && spotData.image.image && typeof spotData.image.image === 'object') {
          console.log('Image Editor - openEditor: Unwrapping nested image structure');
          // Unwrap: use spotData.image.image instead
          normalizedSpotData = { image: spotData.image.image };
        }

        // This will set originalImagePath from spot_data, which takes priority in resolveImageSource
        if (normalizedSpotData && normalizedSpotData.image) {
          this.loadSpotData(normalizedSpotData);
          // After loading spot_data, ensure originalImagePath is set correctly
          // If loadSpotData didn't set it, use the imageUrlToUse
          if (!this.originalImagePath && imageUrlToUse) {
            // Extract relative path from full URL
            let relativePath = imageUrlToUse;
            relativePath = relativePath.replace(/^https?:\/\/[^\/]+/, '');
            relativePath = relativePath.replace(/^\/storage\//, '');
            relativePath = relativePath.replace(/^storage\//, '');
            this.originalImagePath = relativePath || null;
          }
        } else {
          this.resetSpotData(imageUrlToUse);
        }

        // Reset UI state
        this.layers = [];
        this.activeTool = 'select';
        this.selectedTemplate = null;

        // IMPORTANT: Do NOT reset filters if spot_data exists
        // Filters are already loaded by loadSpotData -> loadEffects
        // Only reset filters if this is a completely new image (no spot_data)
        if (!normalizedSpotData || !normalizedSpotData.image) {
          this.resetFilters();
        }

        // Set global reference
        if (typeof window !== 'undefined') {
          window.postsImageEditor = this;
        }

        // Initialize canvas after DOM is ready
        this.$nextTick(() => {
          this.initCanvas();
        });
      },

      /**
       * Set identifier (fileId or index) based on context
       */
      setIdentifier(identifier, spotData) {
        if (identifier === null || identifier === undefined || identifier === '') {
          this.currentIndex = null;
          this.currentFileId = null;
          return;
        }

        const numIdentifier = typeof identifier === 'string' ? parseInt(identifier, 10) : identifier;

        // If we have spot_data, treat as fileId (edit page)
        if (spotData && spotData.image) {
          this.currentFileId = String(identifier);
          this.currentIndex = null;
        } else if (!isNaN(numIdentifier) && numIdentifier.toString() === String(identifier) && String(identifier).length <= 3) {
          // Small number = index (create page)
          this.currentIndex = numIdentifier;
          this.currentFileId = null;
        } else {
          // String = fileId
          this.currentFileId = String(identifier);
          this.currentIndex = null;
        }

        // Identifier set
      },

      /**
       * Reset editor state for new session
       */
      resetEditorState(url) {
        this.imageUrl = url;
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;
        this.history = [];
        this.historyIndex = -1;
      },

      /**
       * Close editor
       */
      closeEditor() {
        this.isOpen = false;
        this.canvas = null;
        this.ctx = null;
        this.image = null;
        this.currentIndex = null;
        this.currentFileId = null;
        this.currentImageKey = null; // Clear current image key
        this.imageUrl = null;
        this.editingText = false;
        // Reset crop data and original dimensions
        this.desktopCrop = [];
        this.mobileCrop = [];
        this.originalImageWidth = 0;
        this.originalImageHeight = 0;
        this.originalImagePath = null;
      },

      /**
       * Draw main canvas (combines all drawing operations)
       */
      draw() {
        if (!this.ctx || !this.image) {
          console.warn('Image Editor - draw: Canvas or image not ready', {
            hasCtx: !!this.ctx,
            hasImage: !!this.image,
            canvasSize: { width: this.canvas?.width, height: this.canvas?.height },
          });
          return;
        }

        // Update canvas transform (zoom and pan)
        this.updateCanvasTransform();

        // Clear canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // Draw background pattern (transparency indicator)
        this.drawCheckerboard();

        // Always apply effects via filter for live preview
        // Effects should be applied in real-time regardless of crop state
        this.ctx.save();
        const filterString = this.buildFilterString();
        if (filterString && filterString !== 'none') {
          this.ctx.filter = filterString;
        }

        // Draw layers
        this.layers.forEach((layer, index) => {
          if (!layer.visible) return;

          this.ctx.save();
          this.ctx.globalAlpha = layer.opacity / 100;

          if (layer.type === 'image' && layer.image) {
            this.ctx.drawImage(layer.image, 0, 0, this.canvasWidth, this.canvasHeight);
          }

          this.ctx.restore();
        });

        // Restore filter context (we always save it, so always restore it)
        this.ctx.restore();

        // Draw text objects
        if (this.textObjects && this.textObjects.length > 0) {
          this.textObjects.forEach((text, index) => {
            this.drawText(text, index === this.activeTextIndex);
          });
        }

        // Draw crop overlay
        if (this.isCropping) {
          this.drawCropOverlay();
        }

        // Draw selection
        if (this.selection) {
          this.drawSelection();
        }
      },

      /**
       * Text property update methods (for UI bindings)
       */
      changeTextColor(color) {
        if (this.activeTextIndex !== null) {
          this.textObjects[this.activeTextIndex].color = color;
          this.draw();
        } else {
          this.textColor = color;
        }
      },

      changeTextBackgroundColor(color) {
        if (this.activeTextIndex !== null) {
          this.textObjects[this.activeTextIndex].backgroundColor = color;
          // If color is transparent, set padding to 0, otherwise use default padding
          if (color === 'transparent' || !color) {
            this.textObjects[this.activeTextIndex].padding = 0;
          } else if (this.textObjects[this.activeTextIndex].padding === 0) {
            this.textObjects[this.activeTextIndex].padding = 15; // Default padding
          }
          this.draw();
          this.saveState();
        } else {
          this.textBackgroundColor = color;
        }
      },

      changeFontSize(size) {
        if (this.activeTextIndex !== null) {
          this.textObjects[this.activeTextIndex].fontSize = parseInt(size);
          this.draw();
        } else {
          this.fontSize = parseInt(size);
        }
      },

      changeFontFamily(family) {
        if (this.activeTextIndex !== null) {
          this.textObjects[this.activeTextIndex].fontFamily = family;
          this.draw();
        } else {
          this.fontFamily = family;
        }
      },

      updateTextProperty(property, value) {
        // Ensure numeric properties are stored as numbers
        const numericProperties = ['fontSize', 'letterSpacing', 'lineHeight', 'brushSize', 'brushOpacity', 'brushHardness'];
        if (numericProperties.includes(property)) {
          value = parseFloat(value) || (property === 'lineHeight' ? 1.2 : 0);
        }

        if (this.activeTextIndex !== null) {
          this.textObjects[this.activeTextIndex][property] = value;
          this.draw();
        } else {
          this[property] = value;
        }
      },

      updateTextShadow(property, value) {
        if (this.activeTextIndex !== null) {
          if (!this.textObjects[this.activeTextIndex].textShadow) {
            this.textObjects[this.activeTextIndex].textShadow = JSON.parse(JSON.stringify(this.textShadow));
          }
          this.textObjects[this.activeTextIndex].textShadow[property] = value;
          this.draw();
        } else {
          this.textShadow[property] = value;
        }
      },

      updateTextStroke(property, value) {
        if (this.activeTextIndex !== null) {
          if (!this.textObjects[this.activeTextIndex].textStroke) {
            this.textObjects[this.activeTextIndex].textStroke = JSON.parse(JSON.stringify(this.textStroke));
          }
          this.textObjects[this.activeTextIndex].textStroke[property] = value;
          this.draw();
        } else {
          this.textStroke[property] = value;
        }
      },

      /**
       * Utility: Convert hex to RGB
       */
      hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
          r: parseInt(result[1], 16),
          g: parseInt(result[2], 16),
          b: parseInt(result[3], 16)
        } : { r: 0, g: 0, b: 0 };
      },
    };

    // Return combined state and methods
    return {
      ...state,
      ...methods,
    };
  });
}

