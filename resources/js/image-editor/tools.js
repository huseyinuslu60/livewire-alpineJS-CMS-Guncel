/**
 * Image Editor - Tool Operations
 * Crop, eraser, select, text, pan tool operations
 */

export function createToolsMethods() {
  return {
    /**
     * Set active tool
     */
    setTool(tool) {
      this.activeTool = tool;
      this.activeTextIndex = null;

      // If crop tool is selected and we have existing crop data, show it
      if (tool === 'crop' && this.canvas && this.image && this.canvasWidth > 0 && this.canvasHeight > 0) {
        const crop = this.desktopCrop && this.desktopCrop.length === 4 ? this.desktopCrop :
                     (this.mobileCrop && this.mobileCrop.length === 4 ? this.mobileCrop : null);

        if (crop) {
          // Crop is [x, y, width, height] relative to original image
          const [cropX, cropY, cropWidth, cropHeight] = crop;

          // Get original image dimensions
          const originalWidth = this.originalImageWidth || this.image.width;
          const originalHeight = this.originalImageHeight || this.image.height;

          if (originalWidth > 0 && originalHeight > 0) {
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

            console.log('Image Editor - setTool crop: Loaded existing crop:', {
              originalCrop: crop,
              canvasCrop: {
                startX: this.cropStartX,
                startY: this.cropStartY,
                endX: this.cropEndX,
                endY: this.cropEndY,
              },
              scale: { x: scaleX, y: scaleY },
              canvasSize: { width: this.canvasWidth, height: this.canvasHeight },
              originalSize: { width: originalWidth, height: originalHeight },
              imageSize: { width: this.image.width, height: this.image.height },
            });

            this.draw();
          } else {
            console.warn('Image Editor - setTool crop: Invalid original dimensions', {
              originalWidth,
              originalHeight,
            });
            this.isCropping = false;
            this.draw();
          }
        } else {
          // No existing crop, reset crop state
          this.isCropping = false;
          this.cropStartX = 0;
          this.cropStartY = 0;
          this.cropEndX = 0;
          this.cropEndY = 0;
          this.draw();
        }
      } else if (tool !== 'crop') {
        // If switching away from crop tool, hide crop overlay
        this.isCropping = false;
        this.draw();
      } else if (tool === 'crop' && (!this.canvas || !this.image)) {
        // Canvas or image not ready yet, crop will be shown in initCanvas
        console.log('Image Editor - setTool crop: Canvas or image not ready, will show crop in initCanvas');
      }

      if (this.canvas) {
        this.updateCursor(0, 0);
      }
    },

    /**
     * Apply crop to image
     */
    applyCrop() {
      if (!this.image) return;

      const x = Math.min(this.cropStartX, this.cropEndX);
      const y = Math.min(this.cropStartY, this.cropEndY);
      const w = Math.abs(this.cropEndX - this.cropStartX);
      const h = Math.abs(this.cropEndY - this.cropStartY);

      if (w < 10 || h < 10) return;

      // Calculate crop coordinates relative to original image dimensions
      const originalWidth = this.originalImageWidth || this.image.width;
      const originalHeight = this.originalImageHeight || this.image.height;

      // Calculate scale factors: how much the canvas is scaled compared to original image
      const scaleX = originalWidth / this.canvasWidth;
      const scaleY = originalHeight / this.canvasHeight;

      // Calculate crop coordinates in canvas space (accounting for zoom and pan)
      const canvasX = (x - this.panX) / this.zoom;
      const canvasY = (y - this.panY) / this.zoom;
      const canvasW = w / this.zoom;
      const canvasH = h / this.zoom;

      // Convert canvas coordinates to original image coordinates
      const cropX = Math.round(canvasX * scaleX);
      const cropY = Math.round(canvasY * scaleY);
      const cropWidth = Math.round(canvasW * scaleX);
      const cropHeight = Math.round(canvasH * scaleY);

      // Ensure crop coordinates are within image bounds
      const finalCropX = Math.max(0, Math.min(cropX, originalWidth));
      const finalCropY = Math.max(0, Math.min(cropY, originalHeight));
      const finalCropWidth = Math.max(1, Math.min(cropWidth, originalWidth - finalCropX));
      const finalCropHeight = Math.max(1, Math.min(cropHeight, originalHeight - finalCropY));

      // Save crop data for both desktop and mobile (same crop for now, can be different later)
      this.desktopCrop = [finalCropX, finalCropY, finalCropWidth, finalCropHeight];
      this.mobileCrop = [finalCropX, finalCropY, finalCropWidth, finalCropHeight];

      console.log('Image Editor - Crop applied:', {
        desktopCrop: this.desktopCrop,
        mobileCrop: this.mobileCrop,
        originalImageSize: { width: originalWidth, height: originalHeight },
        canvasSize: { width: this.canvasWidth, height: this.canvasHeight },
        cropCoordinates: { x: finalCropX, y: finalCropY, width: finalCropWidth, height: finalCropHeight },
        scaleFactors: { scaleX, scaleY },
      });

      // Apply crop to canvas preview - show only the cropped area
      const croppedCanvas = document.createElement('canvas');
      croppedCanvas.width = finalCropWidth;
      croppedCanvas.height = finalCropHeight;
      const croppedCtx = croppedCanvas.getContext('2d', { willReadFrequently: true });

      // Draw the cropped portion of the image
      const imgScaleX = this.image.width / originalWidth;
      const imgScaleY = this.image.height / originalHeight;
      const sourceX = finalCropX * imgScaleX;
      const sourceY = finalCropY * imgScaleY;
      const sourceW = finalCropWidth * imgScaleX;
      const sourceH = finalCropHeight * imgScaleY;

      croppedCtx.drawImage(this.image, sourceX, sourceY, sourceW, sourceH, 0, 0, finalCropWidth, finalCropHeight);

      // Create new image from cropped canvas
      const croppedImg = new Image();
      croppedImg.onload = () => {
        // Store old canvas dimensions before updating
        const oldCanvasWidth = this.canvasWidth;
        const oldCanvasHeight = this.canvasHeight;

        // Crop area in old canvas coordinates (before crop was applied)
        const oldCropStartX = x;
        const oldCropStartY = y;

        // Update image to cropped version for preview
        this.image = croppedImg;
        this.layers[0].image = croppedImg;

        // Update canvas size to match cropped image (but scale to fit display)
        const maxWidth = 1000;
        const maxHeight = 700;
        const cropAspect = finalCropWidth / finalCropHeight;
        const canvasAspect = maxWidth / maxHeight;

        let newCanvasWidth, newCanvasHeight;
        if (cropAspect > canvasAspect) {
          newCanvasWidth = maxWidth;
          newCanvasHeight = maxWidth / cropAspect;
        } else {
          newCanvasHeight = maxHeight;
          newCanvasWidth = maxHeight * cropAspect;
        }

        // Calculate scale factor for textObjects
        const cropWidthInOldCanvas = w;
        const cropHeightInOldCanvas = h;
        const textScaleX = newCanvasWidth / cropWidthInOldCanvas;
        const textScaleY = newCanvasHeight / cropHeightInOldCanvas;

        // Update textObjects: adjust positions relative to crop offset
        // Also filter out textObjects that are completely outside crop area
        this.textObjects = this.textObjects.filter(textObj => {
          // Check if text is within crop area (in old canvas coordinates)
          const textRight = textObj.x + (textObj.fontSize || 32) * 2; // Approximate text width
          const textBottom = textObj.y + (textObj.fontSize || 32); // Text height
          const cropRight = oldCropStartX + cropWidthInOldCanvas;
          const cropBottom = oldCropStartY + cropHeightInOldCanvas;

          // Keep text if it's at least partially within crop area
          const isWithinCrop = textObj.x < cropRight && textRight > oldCropStartX &&
                               textObj.y < cropBottom && textBottom > oldCropStartY;

          if (isWithinCrop) {
            // Adjust position relative to crop offset (move to new canvas origin)
            textObj.x = (textObj.x - oldCropStartX) * textScaleX;
            textObj.y = (textObj.y - oldCropStartY) * textScaleY;

            // Scale text size proportionally
            const scaleFactor = Math.min(textScaleX, textScaleY);
            textObj.fontSize = (textObj.fontSize || 32) * scaleFactor;
          }

          return isWithinCrop;
        });

        // Update canvas dimensions after textObjects are adjusted
        this.canvasWidth = newCanvasWidth;
        this.canvasHeight = newCanvasHeight;
        this.canvas.width = newCanvasWidth;
        this.canvas.height = newCanvasHeight;

        // Reset zoom and pan
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;
        this.isCropping = false;

        this.draw();
        this.saveState();

        console.log('Image Editor - Crop applied to preview:', {
          crop: this.desktopCrop,
          newCanvasSize: { width: newCanvasWidth, height: newCanvasHeight },
          croppedImageSize: { width: finalCropWidth, height: finalCropHeight },
        });
      };
      croppedImg.src = croppedCanvas.toDataURL();
    },

    /**
     * Draw brush (for eraser tool)
     */
    drawBrush(x1, y1, x2, y2) {
      this.ctx.save();

      // Only eraser tool uses this function now
      this.ctx.globalCompositeOperation = 'destination-out';
      this.ctx.globalAlpha = this.brushOpacity / 100;

      this.ctx.lineWidth = this.brushSize;
      this.ctx.lineCap = 'round';
      this.ctx.lineJoin = 'round';

      // Brush hardness (softness)
      if (this.brushHardness < 100) {
        const gradient = this.ctx.createRadialGradient(x2, y2, 0, x2, y2, this.brushSize / 2);
        const alpha = this.brushOpacity / 100;
        gradient.addColorStop(0, `rgba(0, 0, 0, ${alpha})`);
        gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(x2 - this.brushSize / 2, y2 - this.brushSize / 2, this.brushSize, this.brushSize);
      } else {
        this.ctx.beginPath();
        this.ctx.moveTo(x1, y1);
        this.ctx.lineTo(x2, y2);
        this.ctx.stroke();
      }

      this.ctx.restore();
    },

    /**
     * Select object at coordinates
     */
    selectObject(x, y) {
      // Check text objects
      for (let i = this.textObjects.length - 1; i >= 0; i--) {
        const text = this.textObjects[i];
        this.ctx.font = `${text.textItalic ? 'italic ' : ''}${text.textBold ? 'bold ' : ''}${text.fontSize}px ${text.fontFamily}`;
        const metrics = this.ctx.measureText(text.text);
        const textWidth = metrics.width;
        const textHeight = text.fontSize;

        if (x >= text.x && x <= text.x + textWidth &&
            y >= text.y - textHeight && y <= text.y) {
          this.activeTextIndex = i;
          this.draw();
          return;
        }
      }

      this.activeTextIndex = null;
      this.draw();
    },

    /**
     * Update cursor based on active tool
     */
    updateCursor(x, y) {
      if (this.activeTool === 'pan') {
        this.canvas.style.cursor = this.isPanning ? 'grabbing' : 'grab';
      } else if (this.activeTool === 'eraser') {
        this.canvas.style.cursor = 'crosshair';
      } else if (this.activeTool === 'crop') {
        this.canvas.style.cursor = 'crosshair';
      } else if (this.activeTool === 'text') {
        this.canvas.style.cursor = 'text';
      } else if (this.activeTool === 'select') {
        this.canvas.style.cursor = 'default';
      } else {
        this.canvas.style.cursor = 'default';
      }
    },

    /**
     * Delete selected object
     */
    deleteSelected() {
      if (this.activeTextIndex !== null) {
        this.textObjects.splice(this.activeTextIndex, 1);
        this.activeTextIndex = null;
        this.draw();
        this.saveState();
        // Auto-save textObjects changes to Livewire
        this.autoSaveEditorData();
      }
    },

    /**
     * Rotate image
     */
    rotate(angle) {
      if (!this.image) return;

      const tempCanvas = document.createElement('canvas');
      const tempCtx = tempCanvas.getContext('2d', { willReadFrequently: true });

      const radians = (angle * Math.PI) / 180;
      const cos = Math.abs(Math.cos(radians));
      const sin = Math.abs(Math.sin(radians));
      const newWidth = this.image.width * cos + this.image.height * sin;
      const newHeight = this.image.width * sin + this.image.height * cos;

      tempCanvas.width = newWidth;
      tempCanvas.height = newHeight;

      tempCtx.translate(newWidth / 2, newHeight / 2);
      tempCtx.rotate(radians);
      tempCtx.drawImage(this.image, -this.image.width / 2, -this.image.height / 2);

      const rotatedImg = new Image();
      rotatedImg.onload = () => {
        this.image = rotatedImg;
        this.layers[0].image = rotatedImg;
        this.canvasWidth = newWidth;
        this.canvasHeight = newHeight;
        this.canvas.width = newWidth;
        this.canvas.height = newHeight;
        this.draw();
        this.saveState();
      };
      rotatedImg.src = tempCanvas.toDataURL();
    },

    /**
     * Flip image horizontally
     */
    flipHorizontal() {
      if (!this.image) return;
      const tempCanvas = document.createElement('canvas');
      tempCanvas.width = this.image.width;
      tempCanvas.height = this.image.height;
      const tempCtx = tempCanvas.getContext('2d', { willReadFrequently: true });
      tempCtx.translate(this.image.width, 0);
      tempCtx.scale(-1, 1);
      tempCtx.drawImage(this.image, 0, 0);

      const flippedImg = new Image();
      flippedImg.onload = () => {
        this.image = flippedImg;
        this.layers[0].image = flippedImg;
        this.draw();
        this.saveState();
      };
      flippedImg.src = tempCanvas.toDataURL();
    },

    /**
     * Flip image vertically
     */
    flipVertical() {
      if (!this.image) return;
      const tempCanvas = document.createElement('canvas');
      tempCanvas.width = this.image.width;
      tempCanvas.height = this.image.height;
      const tempCtx = tempCanvas.getContext('2d', { willReadFrequently: true });
      tempCtx.translate(0, this.image.height);
      tempCtx.scale(1, -1);
      tempCtx.drawImage(this.image, 0, 0);

      const flippedImg = new Image();
      flippedImg.onload = () => {
        this.image = flippedImg;
        this.layers[0].image = flippedImg;
        this.draw();
        this.saveState();
      };
      flippedImg.src = tempCanvas.toDataURL();
    },

    /**
     * Reset zoom to 100%
     */
    resetZoom() {
      this.zoom = 1;
      this.panX = 0;
      this.panY = 0;
      this.updateCanvasTransform();
      this.draw();
    },

    /**
     * Set zoom from percentage
     */
    setZoomFromPercent(percent) {
      const zoomValue = parseFloat(percent) / 100;

      if (isNaN(zoomValue) || zoomValue <= 0) {
        return;
      }

      this.zoom = Math.max(this.minZoom, Math.min(this.maxZoom, zoomValue));
      this.updateCanvasTransform();
      this.draw();
    },
  };
}

