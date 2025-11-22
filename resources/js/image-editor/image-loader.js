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
        if (crop && this.spotData) {
          this.applyCropAndEffects(img, crop, maxWidth, maxHeight, savedCanvasWidth, savedCanvasHeight);
        } else {
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

      // IMPORTANT: If imageSrc is a Livewire preview file URL, try to get file_path from uploadedFiles
      // Livewire preview file URLs expire and cause 500 errors
      if (imageSrc.includes('livewire/preview-file')) {
        console.warn('Image Editor - resolveImageSource: Detected Livewire preview file URL, trying to find file_path', {
          imageSrc: imageSrc.substring(0, 100),
        });

        // Try to find file_path from uploadedFiles using currentFileId or currentIndex
        const fileId = this.currentFileId;
        const index = this.currentIndex;
        const imageKey = this.currentImageKey;

        // Try to find file_path from img element's data attributes
        let filePath = null;
        if (imageKey) {
          const img = document.querySelector(`img[data-image-key="${imageKey}"]`);
          if (img) {
            filePath = img.getAttribute('data-file-path') || img.getAttribute('data-original-path');
            if (filePath) {
              imageSrc = filePath;
            }
          }
        }

        // If still not found, try to extract from imageKey (temp:file_xxx or existing:xxx)
        if (!filePath && imageKey) {
          if (imageKey.startsWith('temp:')) {
            // For temp files, try to find in uploadedFiles
            const fileIdFromKey = imageKey.replace('temp:', '');
            // Try to find img element with matching data-file-id
            const img = document.querySelector(`img[data-file-id="${fileIdFromKey}"]`);
            if (img) {
              filePath = img.getAttribute('data-file-path') || img.getAttribute('data-original-path');
              if (filePath) {
                imageSrc = filePath;
              }
            }
          }
        }

        // If still Livewire URL, log warning but continue (might work if not expired)
        if (imageSrc.includes('livewire/preview-file')) {
          console.warn('Image Editor - resolveImageSource: Could not find file_path, using Livewire URL (may fail if expired)', {
            imageSrc: imageSrc.substring(0, 100),
          });
        }
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

      }

      return { scaleX, scaleY, savedCanvasWidth, savedCanvasHeight };
    },

    /**
     * Apply crop and effects from spot_data
     */
    applyCropAndEffects(img, crop, maxWidth, maxHeight, savedCanvasWidth, savedCanvasHeight) {
      const [cropX, cropY, cropWidth, cropHeight] = crop;


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


        // Reset zoom and pan
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;

        // Draw immediately - effects already applied to croppedImg, textObjects will be drawn in draw()
        requestAnimationFrame(() => {
          if (this.image && this.ctx && this.canvas) {

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

      };

      croppedImg.src = tempCanvas.toDataURL();
    },

    /**
     * Load full image without crop (but with effects if available)
     */
    loadFullImage(img, maxWidth, maxHeight, scaleX, scaleY, savedCanvasWidth, savedCanvasHeight) {
      if (import.meta?.env?.DEV) console.log('Image Editor - loadFullImage', {
        saturation: this.saturation,
        hue: this.hue,
        exposure: this.exposure,
        blur: this.blur,
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
      // IMPORTANT: Wait for image to be fully loaded before drawing
      const drawWhenReady = () => {
        if (!this.image || !this.ctx || !this.canvas) {
          console.warn('Image Editor - loadFullImage: Not ready yet, retrying...', {
            hasImage: !!this.image,
            hasCtx: !!this.ctx,
            hasCanvas: !!this.canvas,
          });
          // Retry after a short delay
          setTimeout(drawWhenReady, 50);
          return;
        }

        const filterString = this.buildFilterString();

        this.draw();
        this.saveState();
        this.updateCanvasTransform();

        // Double-check after a short delay to ensure effects are applied
        setTimeout(() => {
          if (this.image && this.ctx && this.canvas) {
            this.draw();
          }
        }, 50);
      };

      requestAnimationFrame(drawWhenReady);

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

    },
  };
}

