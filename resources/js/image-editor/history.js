/**
 * Image Editor - History Management
 * Undo/Redo operations
 */

export function createHistoryMethods() {
  return {
    /**
     * Save current state to history
     */
    saveState() {
      if (!this.ctx || !this.canvas) return;

      // Don't save state if canvas is empty or not initialized
      if (this.canvas.width === 0 || this.canvas.height === 0) return;

      if (this.historyIndex < this.history.length - 1) {
        this.history = this.history.slice(0, this.historyIndex + 1);
      }

      // Save current canvas state as image data
      let imageData = null;
      try {
        imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
      } catch (e) {
        console.warn('Failed to get image data:', e);
        return;
      }

      const state = {
        imageData: imageData,
        textObjects: JSON.parse(JSON.stringify(this.textObjects)),
        layers: JSON.parse(JSON.stringify(this.layers.map(layer => {
          // Don't save image objects in layers, they're too large
          return {
            name: layer.name,
            visible: layer.visible,
            opacity: layer.opacity,
            locked: layer.locked,
            type: layer.type
          };
        }))),
        zoom: this.zoom,
        panX: this.panX,
        panY: this.panY,
        brightness: this.brightness,
        contrast: this.contrast,
        saturation: this.saturation,
        canvasWidth: this.canvasWidth,
        canvasHeight: this.canvasHeight
      };

      this.history.push(state);

      if (this.history.length > this.maxHistory) {
        this.history.shift();
      } else {
        this.historyIndex++;
      }
    },

    /**
     * Undo last operation
     */
    undo() {
      if (this.historyIndex > 0) {
        this.historyIndex--;
        this.restoreState(this.history[this.historyIndex]);
      }
    },

    /**
     * Redo last undone operation
     */
    redo() {
      if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        this.restoreState(this.history[this.historyIndex]);
      }
    },

    /**
     * Restore state from history
     */
    restoreState(state) {
      if (!this.ctx || !this.canvas || !state) return;

      // Restore canvas dimensions if they changed
      if (state.canvasWidth && state.canvasHeight) {
        if (this.canvas.width !== state.canvasWidth || this.canvas.height !== state.canvasHeight) {
          this.canvasWidth = state.canvasWidth;
          this.canvasHeight = state.canvasHeight;
          this.canvas.width = state.canvasWidth;
          this.canvas.height = state.canvasHeight;
          // Re-enable image smoothing
          this.ctx.imageSmoothingEnabled = true;
          this.ctx.imageSmoothingQuality = 'high';
        }
      }

      // Restore objects first
      this.textObjects = JSON.parse(JSON.stringify(state.textObjects || []));

      // Restore layers (but keep image reference)
      if (state.layers && state.layers.length > 0) {
        this.layers = state.layers.map((savedLayer, index) => {
          // Keep existing layer's image if it exists
          const existingLayer = this.layers[index];
          return {
            name: savedLayer.name || `Layer ${index + 1}`,
            visible: savedLayer.visible !== undefined ? savedLayer.visible : true,
            opacity: savedLayer.opacity !== undefined ? savedLayer.opacity : 100,
            locked: savedLayer.locked !== undefined ? savedLayer.locked : false,
            type: savedLayer.type || 'image',
            image: existingLayer && existingLayer.image ? existingLayer.image : this.image
          };
        });
      }

      // Restore view settings
      this.zoom = state.zoom !== undefined ? state.zoom : 1;
      this.panX = state.panX !== undefined ? state.panX : 0;
      this.panY = state.panY !== undefined ? state.panY : 0;
      this.brightness = state.brightness !== undefined ? state.brightness : 100;
      this.contrast = state.contrast !== undefined ? state.contrast : 100;
      this.saturation = state.saturation !== undefined ? state.saturation : 100;

      // Restore image data AFTER restoring all other state
      // This ensures the canvas has the correct dimensions
      if (state.imageData) {
        try {
          // Check if imageData dimensions match canvas
          if (state.imageData.width === this.canvas.width && state.imageData.height === this.canvas.height) {
            // putImageData restores the entire canvas including image, text, and shapes
            // So we don't need to call draw() which would clear and redraw everything
            this.ctx.putImageData(state.imageData, 0, 0);
            // Don't call draw() here - putImageData already restored everything
          } else {
            // Dimensions don't match, need to redraw from scratch
            console.warn('ImageData dimensions mismatch, redrawing from state');
            this.draw();
          }
        } catch (e) {
          console.warn('Failed to restore image data:', e);
          // If image data restore fails, redraw from current state
          this.draw();
          return;
        }
      } else {
        // No imageData in state, redraw from current state
        this.draw();
      }
    },
  };
}

