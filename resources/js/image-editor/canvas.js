/**
 * Image Editor - Canvas Operations
 * Canvas initialization, drawing, and transform operations
 */

export function createCanvasMethods() {
  return {
    /**
     * Initialize canvas and load image with spot_data
     * Main entry point for loading edited images
     */
    initCanvas() {
      const canvasEl = document.getElementById('image-editor-canvas');
      if (!canvasEl) {
        console.error('Image Editor: Canvas element not found');
        return;
      }

      // Initialize canvas context
      this.initializeCanvasContext(canvasEl);

      // Resolve image source URL
      const imageSrc = this.resolveImageSource();

      console.log('Image Editor - initCanvas:', {
        originalImagePath: this.originalImagePath,
        imageUrl: this.imageUrl,
        imageSrc: imageSrc,
        hasSpotData: !!this.spotData,
      });

      // Load image
      this.loadImage(imageSrc);
      this.setupCanvasEvents();
    },

    /**
     * Initialize canvas context with optimal settings
     */
    initializeCanvasContext(canvasEl) {
      this.canvas = canvasEl;
      this.ctx = canvasEl.getContext('2d', { willReadFrequently: true });
      this.ctx.imageSmoothingEnabled = true;
      this.ctx.imageSmoothingQuality = 'high';
    },

    /**
     * Calculate canvas dimensions based on image and container size
     */
    calculateCanvasDimensions(img) {
      const canvasWrapper = this.$refs.canvasWrapper;
      let maxWidth = 1000;
      let maxHeight = 700;

      if (canvasWrapper) {
        const wrapperRect = canvasWrapper.getBoundingClientRect();
        maxWidth = Math.floor(wrapperRect.width * 0.9);
        maxHeight = Math.floor(wrapperRect.height * 0.9);
      }

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

      return { maxWidth, maxHeight, canvasWidth, canvasHeight };
    },

    /**
     * Set canvas dimensions
     */
    setCanvasDimensions(width, height) {
      this.canvasWidth = width;
      this.canvasHeight = height;
      this.canvas.width = width;
      this.canvas.height = height;
    },

    /**
     * Update canvas transform (zoom and pan)
     */
    updateCanvasTransform() {
      if (this.canvas) {
        const transform = `translate(${this.panX}px, ${this.panY}px) scale(${this.zoom})`;
        this.canvas.style.transform = transform;
        this.canvas.style.transformOrigin = '0 0';
        // Force a reflow to ensure transform is applied
        this.canvas.offsetHeight;
      }
    },

    /**
     * Draw checkerboard background pattern
     */
    drawCheckerboard() {
      const size = 20;
      this.ctx.fillStyle = '#ffffff';
      this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

      this.ctx.fillStyle = '#e0e0e0';
      for (let x = 0; x < this.canvas.width; x += size) {
        for (let y = 0; y < this.canvas.height; y += size) {
          if ((x / size + y / size) % 2 === 0) {
            this.ctx.fillRect(x, y, size, size);
          }
        }
      }
    },

    /**
     * Draw crop overlay
     */
    drawCropOverlay() {
      const x = Math.min(this.cropStartX, this.cropEndX);
      const y = Math.min(this.cropStartY, this.cropEndY);
      const w = Math.abs(this.cropEndX - this.cropStartX);
      const h = Math.abs(this.cropEndY - this.cropStartY);

      // Only draw if crop area is valid
      if (w < 1 || h < 1) {
        return;
      }

      this.ctx.save();
      // Dark overlay
      this.ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
      this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
      // Clear crop area
      this.ctx.globalCompositeOperation = 'destination-out';
      this.ctx.fillRect(x, y, w, h);
      // Crop border and handles
      this.ctx.globalCompositeOperation = 'source-over';
      this.ctx.strokeStyle = '#0066ff';
      this.ctx.lineWidth = 2;
      this.ctx.strokeRect(x, y, w, h);
      // Corner handles
      const handleSize = 8;
      this.ctx.fillStyle = '#0066ff';
      this.ctx.fillRect(x - handleSize/2, y - handleSize/2, handleSize, handleSize);
      this.ctx.fillRect(x + w - handleSize/2, y - handleSize/2, handleSize, handleSize);
      this.ctx.fillRect(x - handleSize/2, y + h - handleSize/2, handleSize, handleSize);
      this.ctx.fillRect(x + w - handleSize/2, y + h - handleSize/2, handleSize, handleSize);
      this.ctx.restore();
    },

    /**
     * Draw selection rectangle
     */
    drawSelection() {
      if (!this.selection) return;
      
      this.ctx.save();
      this.ctx.strokeStyle = '#0066ff';
      this.ctx.lineWidth = 1;
      this.ctx.setLineDash([5, 5]);
      this.ctx.strokeRect(this.selection.x, this.selection.y, this.selection.width, this.selection.height);
      this.ctx.restore();
    },
  };
}

