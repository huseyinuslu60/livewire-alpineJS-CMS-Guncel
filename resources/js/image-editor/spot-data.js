/**
 * Image Editor - Spot Data Management
 * Loading and saving spot_data (crop, effects, textObjects, etc.)
 */

export function createSpotDataMethods() {
  return {
    /**
     * Load spot_data (saved edits) into editor
     * IMPORTANT: This function loads all saved edits (crop, effects, textObjects, etc.)
     * to allow the user to continue editing a previously edited image
     */
    loadSpotData(spotData) {
      // Handle nested structure: if spotData has 'image.image', unwrap it
      // This prevents issues with double-nested spot_data structure
      let normalizedSpotData = spotData;
      if (spotData && spotData.image && spotData.image.image && typeof spotData.image.image === 'object') {
        console.log('Image Editor - loadSpotData: Unwrapping nested image structure');
        // Unwrap: use spotData.image.image instead
        normalizedSpotData = { image: spotData.image.image };
      }

      const imageData = normalizedSpotData.image;

      if (!imageData) {
        console.error('Image Editor - loadSpotData: Invalid spotData structure, missing image data');
        return;
      }

      console.log('Image Editor - Loading spot_data:', {
        hasTextObjects: !!(imageData.textObjects && Array.isArray(imageData.textObjects)),
        textObjectsCount: imageData.textObjects?.length || 0,
        hasEffects: !!imageData.effects,
        effects: imageData.effects,
        hasCrop: !!(imageData.variants),
        variants: imageData.variants,
        hasCanvas: !!imageData.canvas,
        canvas: imageData.canvas,
        hasOriginal: !!imageData.original,
        original: imageData.original,
      });

      // Load original image data (path, width, height)
      this.loadOriginalImageData(imageData);

      // Load text objects (will be scaled in initCanvas)
      this.loadTextObjects(imageData);

      // Load crop data (desktop and mobile)
      this.loadCropData(imageData);

      // Load effects (brightness, contrast, saturation, etc.)
      this.loadEffects(imageData);

      // Load meta
      if (imageData.meta && typeof imageData.meta === 'object') {
        this.imageMeta = imageData.meta;
      }

      // Store normalized spot_data for later use (needed for scaling text objects)
      this.spotData = normalizedSpotData;

      console.log('Image Editor - spot_data loaded successfully:', {
        originalImagePath: this.originalImagePath,
        originalImageWidth: this.originalImageWidth,
        originalImageHeight: this.originalImageHeight,
        desktopCrop: this.desktopCrop,
        mobileCrop: this.mobileCrop,
        brightness: this.brightness,
        contrast: this.contrast,
        saturation: this.saturation,
        savedTextObjectsCount: this.savedTextObjects.length,
      });
    },

    /**
     * Load original image dimensions and path
     */
    loadOriginalImageData(imageData) {
      if (imageData.original) {
        this.originalImageWidth = imageData.original.width || 0;
        this.originalImageHeight = imageData.original.height || 0;

        // originalImagePath should be a relative path (e.g., "posts/2025/11/image.jpg")
        // If it's a full URL, extract the relative path
        let originalPath = imageData.original.path || null;
        if (originalPath) {
          // Remove domain and /storage/ prefix to get relative path
          // This ensures we always use the original image, not a preview
          originalPath = originalPath.replace(/^https?:\/\/[^\/]+/, ''); // Remove domain
          originalPath = originalPath.replace(/^\/storage\//, ''); // Remove /storage/ prefix
          originalPath = originalPath.replace(/^storage\//, ''); // Remove storage/ prefix
          this.originalImagePath = originalPath || null;
          
          console.log('Image Editor - Loaded original image data:', {
            originalPathFromSpotData: imageData.original.path,
            extractedRelativePath: this.originalImagePath,
            width: this.originalImageWidth,
            height: this.originalImageHeight,
          });
        } else {
          this.originalImagePath = null;
          console.warn('Image Editor - No original path in spot_data');
        }
      }
    },

    /**
     * Load text objects from spot_data
     * IMPORTANT: Always load text objects if they exist, even if empty array
     * This ensures that when reopening an edited image, text objects are properly restored
     */
    loadTextObjects(imageData) {
      if (imageData.textObjects && Array.isArray(imageData.textObjects)) {
        // Store raw textObjects - will be scaled in initCanvas
        this.savedTextObjects = JSON.parse(JSON.stringify(imageData.textObjects));
        this.textObjects = []; // Will be populated in initCanvas
        console.log('Image Editor - Saved textObjects for scaling:', {
          count: this.savedTextObjects.length,
          textObjects: this.savedTextObjects,
        });
      } else {
        this.textObjects = [];
        this.savedTextObjects = [];
        console.log('Image Editor - No textObjects in spot_data, resetting');
      }
    },

    /**
     * Load crop data from spot_data
     * IMPORTANT: Always load crop data if it exists, even if empty arrays
     * This ensures that when reopening an edited image, crop state is properly restored
     */
    loadCropData(imageData) {
      // Initialize with defaults
      this.desktopCrop = [];
      this.mobileCrop = [];
      this.desktopFocus = 'center';
      this.mobileFocus = 'center';

      if (imageData.variants) {
        if (imageData.variants.desktop) {
          // Load desktop crop (even if empty array)
          this.desktopCrop = Array.isArray(imageData.variants.desktop.crop)
            ? imageData.variants.desktop.crop
            : [];
          this.desktopFocus = imageData.variants.desktop.focus || 'center';
        }
        if (imageData.variants.mobile) {
          // Load mobile crop (even if empty array)
          this.mobileCrop = Array.isArray(imageData.variants.mobile.crop)
            ? imageData.variants.mobile.crop
            : [];
          this.mobileFocus = imageData.variants.mobile.focus || 'center';
        }
      }

      console.log('Image Editor - Loaded crop data:', {
        desktopCrop: this.desktopCrop,
        mobileCrop: this.mobileCrop,
        desktopFocus: this.desktopFocus,
        mobileFocus: this.mobileFocus,
      });
    },

    /**
     * Load effects from spot_data
     * IMPORTANT: Always load effects if they exist, even if they are default values
     * This ensures that when reopening an edited image, all effects are properly restored
     */
    loadEffects(imageData) {
      // Always load effects if they exist in spot_data (even if empty object or default values)
      if (imageData.effects && typeof imageData.effects === 'object') {
        // Load effects from spot_data (use defaults if not specified)
        this.brightness = imageData.effects.brightness !== undefined && imageData.effects.brightness !== null
          ? imageData.effects.brightness
          : 100;
        this.contrast = imageData.effects.contrast !== undefined && imageData.effects.contrast !== null
          ? imageData.effects.contrast
          : 100;
        this.saturation = imageData.effects.saturation !== undefined && imageData.effects.saturation !== null
          ? imageData.effects.saturation
          : 100;
        this.hue = imageData.effects.hue !== undefined && imageData.effects.hue !== null
          ? imageData.effects.hue
          : 0;
        this.exposure = imageData.effects.exposure !== undefined && imageData.effects.exposure !== null
          ? imageData.effects.exposure
          : 0;
        this.blur = imageData.effects.blur !== undefined && imageData.effects.blur !== null
          ? imageData.effects.blur
          : 0;

        console.log('Image Editor - Loaded effects from spot_data:', {
          brightness: this.brightness,
          contrast: this.contrast,
          saturation: this.saturation,
          hue: this.hue,
          exposure: this.exposure,
          blur: this.blur,
          effectsObject: imageData.effects,
        });
      } else {
        // No effects object in spot_data, use defaults
        this.brightness = 100;
        this.contrast = 100;
        this.saturation = 100;
        this.hue = 0;
        this.exposure = 0;
        this.blur = 0;

        console.log('Image Editor - Using default effects (no effects object in spot_data)');
      }
    },

    /**
     * Reset spot_data (for new images)
     */
    resetSpotData(url) {
      this.textObjects = [];
      this.savedTextObjects = [];
      this.desktopCrop = [];
      this.mobileCrop = [];
      this.desktopFocus = 'center';
      this.mobileFocus = 'center';
      this.originalImageWidth = 0;
      this.originalImageHeight = 0;

      // Extract relative path from URL if provided
      // originalImagePath should be a relative path (e.g., "posts/2025/11/image.jpg")
      if (url) {
        let relativePath = url;
        // Remove domain and /storage/ prefix to get relative path
        relativePath = relativePath.replace(/^https?:\/\/[^\/]+/, ''); // Remove domain
        relativePath = relativePath.replace(/^\/storage\//, ''); // Remove /storage/ prefix
        relativePath = relativePath.replace(/^storage\//, ''); // Remove storage/ prefix
        this.originalImagePath = relativePath || null;
      } else {
        this.originalImagePath = null;
      }

      this.spotData = null;
    },

    /**
     * Helper: Serialize textObjects for saving
     */
    serializeTextObjects() {
      return (this.textObjects || []).map(textObj => ({
        text: textObj.text || '',
        x: textObj.x || 0,
        y: textObj.y || 0,
        color: textObj.color || textObj.textColor || '#000000',
        backgroundColor: textObj.backgroundColor || 'transparent',
        fontSize: textObj.fontSize || 32,
        fontFamily: textObj.fontFamily || 'Arial',
        fontWeight: textObj.fontWeight || (textObj.textBold ? 'bold' : 'normal'),
        textBold: textObj.textBold || false,
        textItalic: textObj.textItalic || false,
        textUnderline: textObj.textUnderline || false,
        textStrikethrough: textObj.textStrikethrough || false,
        textAlign: textObj.textAlign || 'left',
        letterSpacing: textObj.letterSpacing || 0,
        lineHeight: textObj.lineHeight || 1.2,
        textShadow: textObj.textShadow || { enabled: false, color: '#000000', blur: 0, offsetX: 0, offsetY: 0 },
        textStroke: textObj.textStroke || { enabled: false, color: '#000000', width: 1 },
        textTransform: textObj.textTransform || 'none',
        padding: textObj.padding || 0,
      }));
    },

    /**
     * Helper: Build editor data object
     * IMPORTANT: This function collects ALL current editor state (crop, effects, textObjects, canvas)
     * and returns it in a format that can be saved to spot_data
     */
    buildEditorData() {
      // Serialize text objects
      const textObjects = this.serializeTextObjects();
      
      // Get crop data - IMPORTANT: Use actual crop arrays, even if empty
      const desktopCrop = Array.isArray(this.desktopCrop) ? this.desktopCrop : [];
      const mobileCrop = Array.isArray(this.mobileCrop) ? this.mobileCrop : [];
      
      // Get canvas dimensions - IMPORTANT: Use actual canvas dimensions, not 0
      const canvasWidth = this.canvasWidth || (this.canvas ? this.canvas.width : 0);
      const canvasHeight = this.canvasHeight || (this.canvas ? this.canvas.height : 0);
      
      const editorData = {
        effects: {
          brightness: this.brightness !== undefined && this.brightness !== null ? this.brightness : 100,
          contrast: this.contrast !== undefined && this.contrast !== null ? this.contrast : 100,
          saturation: this.saturation !== undefined && this.saturation !== null ? this.saturation : 100,
          hue: this.hue !== undefined && this.hue !== null ? this.hue : 0,
          exposure: this.exposure !== undefined && this.exposure !== null ? this.exposure : 0,
          blur: this.blur !== undefined && this.blur !== null ? this.blur : 0,
        },
        crop: {
          desktop: desktopCrop,
          mobile: mobileCrop,
        },
        focus: {
          desktop: this.desktopFocus || 'center',
          mobile: this.mobileFocus || 'center',
        },
        meta: this.imageMeta || {},
        textObjects: textObjects,
        canvas: {
          width: canvasWidth,
          height: canvasHeight,
        },
      };
      
      console.log('Image Editor - buildEditorData:', {
        effects: editorData.effects,
        desktopCrop: editorData.crop.desktop,
        desktopCropLength: editorData.crop.desktop.length,
        mobileCrop: editorData.crop.mobile,
        mobileCropLength: editorData.crop.mobile.length,
        textObjectsCount: editorData.textObjects.length,
        canvas: editorData.canvas,
        canvasWidth_property: this.canvasWidth,
        canvasHeight_property: this.canvasHeight,
        canvas_element_width: this.canvas?.width,
        canvas_element_height: this.canvas?.height,
      });
      
      return editorData;
    },

    /**
     * Helper: Build spot_data structure from editor data
     * IMPORTANT: originalImagePath should always point to the FIRST original image,
     * not the edited version. This ensures that on subsequent edits, we always load
     * the original image and apply all edits from scratch.
     */
    buildSpotData(editorData) {
      // Priority: originalImagePath from spot_data > originalImagePath property > imageUrl
      // originalImagePath should be a relative path (e.g., "posts/2025/11/image.jpg")
      let originalPath = this.originalImagePath || '';

      // If originalImagePath is not set, try to extract it from imageUrl
      if (!originalPath && this.imageUrl) {
        let urlPath = this.imageUrl.replace(/^https?:\/\/[^\/]+/, '');
        urlPath = urlPath.replace(/^\/storage\//, '');
        urlPath = urlPath.replace(/^storage\//, '');
        // Skip Livewire temporary URLs to allow renderer to use img.src
        if (urlPath.includes('livewire/preview-file')) {
          originalPath = '';
        } else {
          originalPath = urlPath;
        }
      }

      // If still no path, use empty string (should not happen in normal flow)
      if (!originalPath) {
        console.warn('Image Editor - buildSpotData: No originalImagePath available, using empty string');
        originalPath = '';
      }

      // IMPORTANT: Ensure all data is properly extracted from editorData
      const spotData = {
        image: {
          original: {
            path: originalPath,
            width: this.originalImageWidth || 0,
            height: this.originalImageHeight || 0,
            hash: null, // Hash will be calculated on server side
          },
          variants: {
            desktop: {
              crop: Array.isArray(editorData.crop?.desktop) ? editorData.crop.desktop : [],
              focus: editorData.focus?.desktop || 'center',
            },
            mobile: {
              crop: Array.isArray(editorData.crop?.mobile) ? editorData.crop.mobile : [],
              focus: editorData.focus?.mobile || 'center',
            },
          },
          effects: editorData.effects && typeof editorData.effects === 'object' ? editorData.effects : {
            brightness: 100,
            contrast: 100,
            saturation: 100,
            hue: 0,
            exposure: 0,
            blur: 0,
          },
          meta: editorData.meta && typeof editorData.meta === 'object' ? editorData.meta : {},
          textObjects: Array.isArray(editorData.textObjects) ? editorData.textObjects : [],
          canvas: editorData.canvas && typeof editorData.canvas === 'object' ? editorData.canvas : { width: 0, height: 0 },
        },
      };
      
      console.log('Image Editor - buildSpotData:', {
        originalPath: spotData.image.original.path,
        originalWidth: spotData.image.original.width,
        originalHeight: spotData.image.original.height,
        desktopCrop: spotData.image.variants.desktop.crop,
        desktopCropLength: spotData.image.variants.desktop.crop.length,
        mobileCrop: spotData.image.variants.mobile.crop,
        mobileCropLength: spotData.image.variants.mobile.crop.length,
        effects: spotData.image.effects,
        textObjectsCount: spotData.image.textObjects.length,
        canvas: spotData.image.canvas,
        editorData_canvas: editorData.canvas,
      });
      
      return spotData;
    },
  };
}


