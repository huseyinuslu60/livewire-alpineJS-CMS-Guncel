/**
 * Image Editor - Image Loading Operations
 * Image loading, crop application, and effects application
 */

export function createImageLoaderMethods() {
  return {
    /**
     * Load image and apply spot_data
     */
    loadImage(imageSrc) {
      const img = new Image();
      img.crossOrigin = 'anonymous';

      img.onload = () => {
        this.initializeOriginalDimensions(img);

        // Reset crop data only if no spot_data exists
        if (!this.spotData) {
          this.desktopCrop = [];
          this.mobileCrop = [];
        }

        // Calculate canvas dimensions
        const { maxWidth, maxHeight, canvasWidth, canvasHeight } = this.calculateCanvasDimensions(img);

        // Calculate scale factors for textObjects
        const { scaleX, scaleY, savedCanvasWidth, savedCanvasHeight } = this.calculateScaleFactors(
          canvasWidth,
          canvasHeight,
          maxWidth,
          maxHeight
        );

        // Set canvas dimensions
        this.setCanvasDimensions(canvasWidth, canvasHeight);

        // Apply crop and effects if available, otherwise load full image
        const crop = this.getActiveCrop();
        console.log('Image Editor - loadImage: Checking crop:', {
          crop: crop,
          desktopCrop: this.desktopCrop,
          desktopCropLength: this.desktopCrop?.length,
          mobileCrop: this.mobileCrop,
          mobileCropLength: this.mobileCrop?.length,
          hasSpotData: !!this.spotData,
          isArrayDesktop: Array.isArray(this.desktopCrop),
          isArrayMobile: Array.isArray(this.mobileCrop),
        });
        if (crop && this.spotData) {
          console.log('Image Editor - loadImage: Using applyCropAndEffects');
          this.applyCropAndEffects(img, crop, maxWidth, maxHeight, savedCanvasWidth, savedCanvasHeight);
        } else {
          console.log('Image Editor - loadImage: Using loadFullImage (no crop or no spotData)');
          this.loadFullImage(img, maxWidth, maxHeight, scaleX, scaleY, savedCanvasWidth, savedCanvasHeight);
        }
      };

      img.onerror = () => {
        console.error('Image Editor: Failed to load image', imageSrc);
        if (window.showToast) {
          window.showToast('Resim yüklenemedi', 'error');
        }
      };

      img.src = imageSrc;
    },

    /**
     * Resolve image source URL from spot_data or imageUrl
     * IMPORTANT: Always use original image path from spot_data if available (for edited images)
     * This ensures we load the original image, not the edited preview
     */
    resolveImageSource() {
      // Priority: originalImagePath from spot_data > imageUrl
      let imageSrc = this.originalImagePath || this.imageUrl;

      if (!imageSrc) {
        console.warn('Image Editor - No image source available');
        return null;
      }

      // Convert relative path to full URL if needed
      if (!imageSrc.startsWith('http') && !imageSrc.startsWith('data:')) {
        // Handle storage paths
        if (imageSrc.startsWith('storage/') || imageSrc.startsWith('/storage/')) {
          imageSrc = window.location.origin + (imageSrc.startsWith('/') ? '' : '/') + imageSrc;
        } else if (!imageSrc.startsWith('/')) {
          // Relative path without storage prefix
          imageSrc = window.location.origin + '/storage/' + imageSrc;
        } else {
          // Absolute path
          imageSrc = window.location.origin + imageSrc;
        }
      }

      return imageSrc;
    },

    /**
     * Initialize original image dimensions
     */
    initializeOriginalDimensions(img) {
      if (!this.originalImageWidth || !this.originalImageHeight) {
        this.originalImageWidth = img.width;
        this.originalImageHeight = img.height;
      }

      if (!this.originalImagePath && this.imageUrl) {
        this.originalImagePath = this.imageUrl;
      }
    },

    /**
     * Get active crop data (desktop or mobile)
     */
    getActiveCrop() {
      if (this.desktopCrop && Array.isArray(this.desktopCrop) && this.desktopCrop.length === 4) {
        return this.desktopCrop;
      }
      if (this.mobileCrop && Array.isArray(this.mobileCrop) && this.mobileCrop.length === 4) {
        return this.mobileCrop;
      }
      return null;
    },

    /**
     * Calculate scale factors for textObjects based on saved canvas dimensions
     */
    calculateScaleFactors(canvasWidth, canvasHeight, maxWidth, maxHeight) {
      let scaleX = 1;
      let scaleY = 1;
      let savedCanvasWidth = 0;
      let savedCanvasHeight = 0;

      // Get saved canvas dimensions from spot_data
      if (this.spotData && this.spotData.image && this.spotData.image.canvas) {
        savedCanvasWidth = this.spotData.image.canvas.width || 0;
        savedCanvasHeight = this.spotData.image.canvas.height || 0;
      }

      // Calculate scale factors
      if (savedCanvasWidth && savedCanvasHeight) {
        scaleX = canvasWidth / savedCanvasWidth;
        scaleY = canvasHeight / savedCanvasHeight;
        console.log('Image Editor - Scaling from saved canvas:', {
          savedCanvas: { width: savedCanvasWidth, height: savedCanvasHeight },
          newCanvas: { width: canvasWidth, height: canvasHeight },
          scale: { x: scaleX, y: scaleY },
        });
      } else if (this.savedTextObjects && this.savedTextObjects.length > 0) {
        // Fallback: calculate from original image dimensions
        const savedCanvasAspect = this.originalImageWidth / this.originalImageHeight;
        const canvasAspect = maxWidth / maxHeight;

        let calculatedSavedCanvasWidth, calculatedSavedCanvasHeight;
        if (savedCanvasAspect > canvasAspect) {
          calculatedSavedCanvasWidth = maxWidth;
          calculatedSavedCanvasHeight = maxWidth / savedCanvasAspect;
        } else {
          calculatedSavedCanvasHeight = maxHeight;
          calculatedSavedCanvasWidth = maxHeight * savedCanvasAspect;
        }

        scaleX = canvasWidth / calculatedSavedCanvasWidth;
        scaleY = canvasHeight / calculatedSavedCanvasHeight;

        console.log('Image Editor - Scaling from calculated canvas:', {
          calculatedSavedCanvas: { width: calculatedSavedCanvasWidth, height: calculatedSavedCanvasHeight },
          newCanvas: { width: canvasWidth, height: canvasHeight },
          scale: { x: scaleX, y: scaleY },
        });
      }

      return { scaleX, scaleY, savedCanvasWidth, savedCanvasHeight };
    },

    /**
     * Apply crop and effects from spot_data
     */
    applyCropAndEffects(img, crop, maxWidth, maxHeight, savedCanvasWidth, savedCanvasHeight) {
      const [cropX, cropY, cropWidth, cropHeight] = crop;

      console.log('Image Editor - applyCropAndEffects: Starting', {
        crop: crop,
        originalSize: { width: this.originalImageWidth, height: this.originalImageHeight },
        imageSize: { width: img.width, height: img.height },
        effects: {
          brightness: this.brightness,
          contrast: this.contrast,
          saturation: this.saturation,
          hue: this.hue,
          exposure: this.exposure,
          blur: this.blur,
        },
        savedTextObjectsCount: this.savedTextObjects?.length || 0,
        savedCanvas: { width: savedCanvasWidth, height: savedCanvasHeight },
      });

      // Create temporary canvas for cropping
      const tempCanvas = document.createElement('canvas');
      tempCanvas.width = cropWidth;
      tempCanvas.height = cropHeight;
      const tempCtx = tempCanvas.getContext('2d', { willReadFrequently: true });
      tempCtx.imageSmoothingEnabled = true;
      tempCtx.imageSmoothingQuality = 'high';

      // Calculate scale from original to loaded image
      const imgScaleX = img.width / this.originalImageWidth;
      const imgScaleY = img.height / this.originalImageHeight;

      // Convert crop coordinates from original to loaded image
      const sourceX = cropX * imgScaleX;
      const sourceY = cropY * imgScaleY;
      const sourceW = cropWidth * imgScaleX;
      const sourceH = cropHeight * imgScaleY;

      // Apply effects filter to temp canvas
      tempCtx.save();
      const filterString = this.buildFilterString();
      if (filterString && filterString !== 'none') {
        tempCtx.filter = filterString;
      }

      // Draw cropped portion with effects applied
      tempCtx.drawImage(img, sourceX, sourceY, sourceW, sourceH, 0, 0, cropWidth, cropHeight);
      tempCtx.restore();

      // Create image from cropped canvas
      const croppedImg = new Image();
      croppedImg.onload = () => {
        // Calculate canvas size for cropped image
        const cropAspect = cropWidth / cropHeight;
        const canvasAspect = maxWidth / maxHeight;
        let croppedCanvasWidth, croppedCanvasHeight;

        if (cropAspect > canvasAspect) {
          croppedCanvasWidth = maxWidth;
          croppedCanvasHeight = maxWidth / cropAspect;
        } else {
          croppedCanvasHeight = maxHeight;
          croppedCanvasWidth = maxHeight * cropAspect;
        }

        // Set canvas dimensions
        this.canvasWidth = croppedCanvasWidth;
        this.canvasHeight = croppedCanvasHeight;
        this.canvas.width = this.canvasWidth;
        this.canvas.height = this.canvasHeight;

        // Ensure context is set
        if (!this.ctx) {
          this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
          this.ctx.imageSmoothingEnabled = true;
          this.ctx.imageSmoothingQuality = 'high';
        }

        // Set image and layers
        this.image = croppedImg;
        this.layers = [{
          name: 'Background',
          visible: true,
          opacity: 100,
          locked: false,
          image: croppedImg,
          type: 'image'
        }];
        this.activeLayerIndex = 0;

        // Scale and load textObjects with crop offset adjustment BEFORE drawing
        this.scaleAndLoadTextObjectsWithCrop(
          croppedCanvasWidth,
          croppedCanvasHeight,
          savedCanvasWidth,
          savedCanvasHeight,
          cropX,
          cropY,
          cropWidth,
          cropHeight
        );

        console.log('Image Editor - applyCropAndEffects: TextObjects loaded', {
          textObjectsCount: this.textObjects.length,
          canvasSize: { width: croppedCanvasWidth, height: croppedCanvasHeight },
        });

        // Reset zoom and pan
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;

        // Draw immediately - effects already applied to croppedImg, textObjects will be drawn in draw()
        requestAnimationFrame(() => {
          if (this.image && this.ctx && this.canvas) {
            console.log('Image Editor - applyCropAndEffects: Drawing', {
              textObjectsCount: this.textObjects.length,
              filterString: this.buildFilterString(),
              crop: { x: cropX, y: cropY, width: cropWidth, height: cropHeight },
            });

            this.draw();
            this.saveState();
            this.updateCanvasTransform();

            // Double-check after a short delay
            setTimeout(() => {
              if (this.image && this.ctx && this.canvas) {
                this.draw();
              }
            }, 50);
          }
        });

        console.log('Image Editor - Crop and effects applied successfully');
      };

      croppedImg.src = tempCanvas.toDataURL();
    },

    /**
     * Load full image without crop (but with effects if available)
     */
    loadFullImage(img, maxWidth, maxHeight, scaleX, scaleY, savedCanvasWidth, savedCanvasHeight) {
      console.log('Image Editor - loadFullImage: Starting', {
        imageSize: { width: img.width, height: img.height },
        maxSize: { width: maxWidth, height: maxHeight },
        savedCanvas: { width: savedCanvasWidth, height: savedCanvasHeight },
        hasSpotData: !!this.spotData,
        effects: {
          brightness: this.brightness,
          contrast: this.contrast,
          saturation: this.saturation,
          hue: this.hue,
          exposure: this.exposure,
          blur: this.blur,
        },
        savedTextObjectsCount: this.savedTextObjects?.length || 0,
      });

      // Calculate canvas dimensions based on image aspect ratio
      const imgAspect = img.width / img.height;
      const canvasAspect = maxWidth / maxHeight;

      let canvasWidth, canvasHeight;
      if (imgAspect > canvasAspect) {
        canvasWidth = maxWidth;
        canvasHeight = maxWidth / imgAspect;
      } else {
        canvasHeight = maxHeight;
        canvasWidth = maxHeight * imgAspect;
      }

      // Set canvas dimensions
      this.canvasWidth = canvasWidth;
      this.canvasHeight = canvasHeight;
      this.canvas.width = canvasWidth;
      this.canvas.height = canvasHeight;

      // Ensure context is properly set
      if (!this.ctx) {
        this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
        this.ctx.imageSmoothingEnabled = true;
        this.ctx.imageSmoothingQuality = 'high';
      }

      // Set image
      this.image = img;

      // Scale and load textObjects BEFORE drawing
      this.scaleAndLoadTextObjects(canvasWidth, canvasHeight, savedCanvasWidth, savedCanvasHeight, scaleX, scaleY);

      console.log('Image Editor - loadFullImage: TextObjects loaded', {
        textObjectsCount: this.textObjects.length,
        canvasSize: { width: canvasWidth, height: canvasHeight },
      });

      // Initialize layers
      this.layers = [{
        name: 'Background',
        visible: true,
        opacity: 100,
        locked: false,
        image: img,
        type: 'image'
      }];
      this.activeLayerIndex = 0;

      // Reset zoom and pan before drawing
      this.zoom = 1;
      this.panX = 0;
      this.panY = 0;

      // Draw immediately - effects and textObjects will be applied in draw()
      // Use requestAnimationFrame to ensure canvas is ready
      requestAnimationFrame(() => {
        if (this.image && this.ctx && this.canvas) {
          const filterString = this.buildFilterString();
          console.log('Image Editor - loadFullImage: Drawing', {
            effects: {
              brightness: this.brightness,
              contrast: this.contrast,
              saturation: this.saturation,
              hue: this.hue,
              exposure: this.exposure,
              blur: this.blur,
            },
            textObjectsCount: this.textObjects.length,
            filterString: filterString,
            hasSpotData: !!this.spotData,
            desktopCropLength: this.desktopCrop?.length,
            willApplyEffects: filterString !== 'none',
          });

          this.draw();
          this.saveState();
          this.updateCanvasTransform();

          // Double-check after a short delay to ensure effects are applied
          setTimeout(() => {
            if (this.image && this.ctx && this.canvas) {
              console.log('Image Editor - loadFullImage: Redrawing after delay', {
                filterString: this.buildFilterString(),
              });
              this.draw();
            }
          }, 50);
        }
      });

      // Show crop overlay if crop tool is active
      this.showCropOverlayIfNeeded(img);
    },

    /**
     * Show crop overlay if crop tool is active and crop data exists
     */
    showCropOverlayIfNeeded(img) {
      if (this.activeTool !== 'crop') {
        return;
      }

      const crop = this.getActiveCrop();
      if (!crop) {
        return;
      }

      const [cropX, cropY, cropWidth, cropHeight] = crop;
      const originalWidth = this.originalImageWidth || img.width;
      const originalHeight = this.originalImageHeight || img.height;

      // Calculate scale factors from original to canvas
      const scaleX = this.canvasWidth / originalWidth;
      const scaleY = this.canvasHeight / originalHeight;

      // Convert crop coordinates to canvas coordinates
      this.cropStartX = cropX * scaleX;
      this.cropStartY = cropY * scaleY;
      this.cropEndX = (cropX + cropWidth) * scaleX;
      this.cropEndY = (cropY + cropHeight) * scaleY;

      // Show crop overlay
      this.isCropping = true;

      console.log('Image Editor - Showing crop overlay:', {
        originalCrop: crop,
        canvasCrop: {
          startX: this.cropStartX,
          startY: this.cropStartY,
          endX: this.cropEndX,
          endY: this.cropEndY,
        },
      });
    },
  };
}

