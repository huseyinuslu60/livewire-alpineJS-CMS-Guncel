/**
 * Image Editor - Event Handlers
 * Mouse, keyboard, and touch event handling
 */

export function createEventsMethods() {
  return {
    /**
     * Setup canvas event listeners
     */
    setupCanvasEvents() {
      if (!this.canvas) return;

      this.canvas.addEventListener('mousedown', (e) => this.handleMouseDown(e));
      this.canvas.addEventListener('mousemove', (e) => this.handleMouseMove(e));
      this.canvas.addEventListener('mouseup', (e) => this.handleMouseUp(e));
      this.canvas.addEventListener('mouseleave', () => this.handleMouseUp());
      this.canvas.addEventListener('dblclick', (e) => this.handleDoubleClick(e));
      this.canvas.addEventListener('wheel', (e) => this.handleWheel(e), { passive: false });
      this.canvas.addEventListener('contextmenu', (e) => e.preventDefault());

      // Touch events for mobile
      this.canvas.addEventListener('touchstart', (e) => {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousedown', {
          clientX: touch.clientX,
          clientY: touch.clientY
        });
        this.canvas.dispatchEvent(mouseEvent);
      });

      this.canvas.addEventListener('touchmove', (e) => {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousemove', {
          clientX: touch.clientX,
          clientY: touch.clientY
        });
        this.canvas.dispatchEvent(mouseEvent);
      });

      this.canvas.addEventListener('touchend', (e) => {
        e.preventDefault();
        const mouseEvent = new MouseEvent('mouseup', {});
        this.canvas.dispatchEvent(mouseEvent);
      });
    },

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
      document.addEventListener('keydown', (e) => {
        if (!this.isOpen) return;

        // Check if user is typing in an input/textarea
        const activeElement = document.activeElement;
        const isTyping = activeElement && (
          activeElement.tagName === 'INPUT' ||
          activeElement.tagName === 'TEXTAREA' ||
          activeElement.isContentEditable
        );

        // Don't process shortcuts when typing (except Escape and Enter for crop)
        if (isTyping && e.key !== 'Escape' && !(e.key === 'Enter' && this.isCropping)) {
          return;
        }

        // Ctrl/Cmd + Z (Undo)
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
          e.preventDefault();
          this.undo();
        }

        // Ctrl/Cmd + Shift + Z (Redo)
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) {
          e.preventDefault();
          this.redo();
        }

        // Delete key
        if (e.key === 'Delete' || e.key === 'Backspace') {
          e.preventDefault();
          this.deleteSelected();
        }

        // Escape
        if (e.key === 'Escape') {
          if (this.editingText) {
            this.editingText = false;
          } else if (this.isCropping) {
            // Cancel crop
            this.isCropping = false;
            this.draw();
          } else {
            this.closeEditor();
          }
        }

        // Enter - Apply crop if cropping
        if (e.key === 'Enter' && this.isCropping) {
          e.preventDefault();
          this.applyCrop();
        }

        // Tool shortcuts - only when not typing in input fields and not holding Shift
        if (!e.ctrlKey && !e.metaKey && !e.altKey && !e.shiftKey && !isTyping) {
          switch(e.key.toLowerCase()) {
            case 'v': this.setTool('select'); break;
            case 'c': this.setTool('crop'); break;
            case 'e': this.setTool('eraser'); break;
            case 't': this.setTool('text'); break;
          }
        }
      });
    },

    /**
     * Handle mouse down event
     */
    handleMouseDown(e) {
      // getBoundingClientRect() already accounts for CSS transform (zoom and pan)
      // So we just need to convert from screen coordinates to canvas internal coordinates
      const rect = this.canvas.getBoundingClientRect();
      const finalX = ((e.clientX - rect.left) / rect.width) * this.canvas.width;
      const finalY = ((e.clientY - rect.top) / rect.height) * this.canvas.height;

      this.startX = finalX;
      this.startY = finalY;
      this.lastX = finalX;
      this.lastY = finalY;
      this.isDrawing = true;

      if (this.activeTool === 'pan') {
        this.isPanning = true;
        this.lastPanX = e.clientX;
        this.lastPanY = e.clientY;
        this.canvas.style.cursor = 'grabbing';
      } else if (this.activeTool === 'crop') {
        this.isCropping = true;
        this.cropStartX = finalX;
        this.cropStartY = finalY;
        this.cropEndX = finalX;
        this.cropEndY = finalY;
      } else if (this.activeTool === 'eraser') {
        this.drawBrush(finalX, finalY, finalX, finalY);
      } else if (this.activeTool === 'text') {
        // Check if clicking on existing text
        let clickedOnText = false;
        for (let i = this.textObjects.length - 1; i >= 0; i--) {
          const text = this.textObjects[i];
          this.ctx.font = `${text.textItalic ? 'italic ' : ''}${text.fontWeight || (text.textBold ? 'bold' : 'normal')} ${text.fontSize}px ${text.fontFamily}`;
          const metrics = this.ctx.measureText(text.text);
          const textWidth = metrics.width;
          const textHeight = text.fontSize * (text.lineHeight || 1.2);

          // Check if click is within text bounds
          const textX = text.x;
          const textY = text.y;
          const align = text.textAlign || 'left';
          let minX = textX;
          if (align === 'center') {
            minX = textX - textWidth / 2;
          } else if (align === 'right') {
            minX = textX - textWidth;
          }

          if (finalX >= minX && finalX <= minX + textWidth &&
              finalY >= textY - textHeight && finalY <= textY) {
            // Clicked on existing text - just select it (don't enter edit mode on single click)
            this.activeTextIndex = i;
            this.editingText = false;
            clickedOnText = true;
            this.draw();
            break;
          }
        }

        // Only add new text if not clicking on existing text
        if (!clickedOnText) {
          // Use selected template if available
          this.addText(finalX, finalY, this.selectedTemplate);
          this.selectedTemplate = null; // Reset after use
        }
      } else if (this.activeTool === 'select') {
        this.selectObject(finalX, finalY);
      }
    },

    /**
     * Handle mouse move event
     */
    handleMouseMove(e) {
      // getBoundingClientRect() already accounts for CSS transform (zoom and pan)
      // So we just need to convert from screen coordinates to canvas internal coordinates
      const rect = this.canvas.getBoundingClientRect();
      const finalX = ((e.clientX - rect.left) / rect.width) * this.canvas.width;
      const finalY = ((e.clientY - rect.top) / rect.height) * this.canvas.height;

      if (this.isPanning) {
        const deltaX = e.clientX - this.lastPanX;
        const deltaY = e.clientY - this.lastPanY;
        this.panX += deltaX;
        this.panY += deltaY;
        this.lastPanX = e.clientX;
        this.lastPanY = e.clientY;
        this.updateCanvasTransform();
        this.draw();
        return;
      }

      if (!this.isDrawing) {
        this.updateCursor(finalX, finalY);
        return;
      }

      if (this.activeTool === 'crop' && this.isCropping) {
        this.cropEndX = finalX;
        this.cropEndY = finalY;
        if (this.cropAspectRatio) {
          const width = finalX - this.cropStartX;
          const height = finalY - this.cropStartY;
          if (Math.abs(width) > Math.abs(height)) {
            this.cropEndY = this.cropStartY + (width / this.cropAspectRatio) * (height < 0 ? -1 : 1);
          } else {
            this.cropEndX = this.cropStartX + (height * this.cropAspectRatio) * (width < 0 ? -1 : 1);
          }
        }
        this.draw();
      } else if (this.activeTool === 'eraser') {
        this.drawBrush(this.lastX, this.lastY, finalX, finalY);
        this.lastX = finalX;
        this.lastY = finalY;
      } else if ((this.activeTool === 'select' || this.activeTool === 'text') && this.activeTextIndex !== null && !this.editingText) {
        // Move text when dragging in select or text tool
        const text = this.textObjects[this.activeTextIndex];
        text.x = finalX;
        text.y = finalY;
        this.draw();
      }
    },

    /**
     * Handle double click event
     */
    handleDoubleClick(e) {
      if (this.activeTool === 'text' || this.activeTool === 'select') {
        // getBoundingClientRect() already accounts for CSS transform (zoom and pan)
        // So we just need to convert from screen coordinates to canvas internal coordinates
        const rect = this.canvas.getBoundingClientRect();
        const finalX = ((e.clientX - rect.left) / rect.width) * this.canvas.width;
        const finalY = ((e.clientY - rect.top) / rect.height) * this.canvas.height;

        // Check if double-clicking on text
        for (let i = this.textObjects.length - 1; i >= 0; i--) {
          const text = this.textObjects[i];
          this.ctx.font = `${text.textItalic ? 'italic ' : ''}${text.fontWeight || (text.textBold ? 'bold' : 'normal')} ${text.fontSize}px ${text.fontFamily}`;
          const metrics = this.ctx.measureText(text.text);
          const textWidth = metrics.width;
          const textHeight = text.fontSize * (text.lineHeight || 1.2);

          const align = text.textAlign || 'left';
          let minX = text.x;
          if (align === 'center') {
            minX = text.x - textWidth / 2;
          } else if (align === 'right') {
            minX = text.x - textWidth;
          }

          if (finalX >= minX && finalX <= minX + textWidth &&
              finalY >= text.y - textHeight && finalY <= text.y) {
            // Double-clicked on text - enter edit mode
            this.activeTextIndex = i;
            this.editingText = true;
            this.draw();

            // Focus the text input in the properties panel
            this.$nextTick(() => {
              const input = document.querySelector('[x-show*="activeTextIndex"] input[type="text"]');
              if (input) {
                input.focus();
                input.select();
              }
            });
            break;
          }
        }
      }
    },

    /**
     * Handle mouse up event
     */
    handleMouseUp(e) {
      if (this.activeTool === 'crop' && this.isCropping) {
        // Don't apply crop yet, let user confirm with Enter or button
        // Keep isCropping true so overlay stays visible
        this.isDrawing = false;
        // isCropping stays true to show overlay until Enter is pressed or crop is applied
      } else if (this.activeTool === 'pan') {
        this.isPanning = false;
        this.canvas.style.cursor = 'grab';
        this.isDrawing = false;
      } else if (this.isDrawing && this.activeTool === 'eraser') {
        this.saveState();
        this.isDrawing = false;
      } else if (this.activeTextIndex !== null) {
        this.saveState();
        this.isDrawing = false;
      } else {
        this.isDrawing = false;
      }

      // Only reset isCropping if not in crop tool or if crop was applied
      if (this.activeTool !== 'crop') {
        this.isCropping = false;
      }
    },

    /**
     * Handle wheel event (zoom)
     */
    handleWheel(e) {
      if (!e.ctrlKey && !e.metaKey) {
        e.preventDefault();

        // Get mouse position relative to scroll container (not canvas, because canvas is transformed)
        const scrollContainer = this.canvas.parentElement;
        if (!scrollContainer) return;

        const containerRect = scrollContainer.getBoundingClientRect();
        const mouseX = e.clientX - containerRect.left;
        const mouseY = e.clientY - containerRect.top;

        // Calculate canvas coordinates at current zoom
        // Canvas is at position (panX, panY) in the container, scaled by zoom
        const canvasX = (mouseX - this.panX) / this.zoom;
        const canvasY = (mouseY - this.panY) / this.zoom;

        // Calculate zoom change
        const delta = e.deltaY > 0 ? 0.9 : 1.1;
        const oldZoom = this.zoom;
        this.zoom *= delta;
        this.zoom = Math.max(this.minZoom, Math.min(this.maxZoom, this.zoom));

        // Adjust pan to keep the canvas point under mouse in the same screen position
        // New pan should position the canvas so that canvasX, canvasY is at mouseX, mouseY
        this.panX = mouseX - (canvasX * this.zoom);
        this.panY = mouseY - (canvasY * this.zoom);

        this.updateCanvasTransform();
        this.draw();
      }
    },
  };
}

