/**
 * Professional Image Editor - Photoshop-like Advanced Editor
 * Canvas-based professional image editing tool with layers, advanced tools, and filters
 */

// Alpine.js will be available globally from app.js
// Access it from window at runtime - Vite will handle this via define config
export function registerImageEditor() {
  // Alpine.js is loaded globally in app.js, we'll access it from window
  // This function is called after Alpine is initialized (in alpine:init event)
  const Alpine = window.Alpine;

  if (!Alpine) {
    console.error('Alpine.js is not loaded. Make sure app.js is loaded first.');
    return;
  }

  // Global helper function to open image editor
  if (typeof window !== 'undefined') {
    window.openImageEditor = function(index, url) {
      // Try to find the editor instance
      const editorEl = document.querySelector('[x-data*="imageEditor"]');
      if (editorEl && editorEl._x_dataStack && editorEl._x_dataStack[0]) {
        editorEl._x_dataStack[0].openEditor(index, url);
        return;
      }

      // Fallback: use global reference
      if (window.postsImageEditor && typeof window.postsImageEditor.openEditor === 'function') {
        window.postsImageEditor.openEditor(index, url);
        return;
      }

      // Last resort: dispatch event
      window.dispatchEvent(new CustomEvent('open-image-editor', {
        detail: { index, url }
      }));
    };
  }

  // Alpine.js component'ini direkt kaydet (alpine:init içinde çağrıldığı için)
  // Use window.Alpine directly to avoid Vite parsing issues
  window.Alpine.data('imageEditor', () => ({
      // State
      isOpen: false,
      currentIndex: null,
      currentFileId: null,
      imageUrl: null,

      // Canvas
      canvas: null,
      ctx: null,
      image: null,
      canvasWidth: 800,
      canvasHeight: 600,

      // Layers System
      layers: [],
      activeLayerIndex: 0,

      // Tools
      activeTool: 'select', // select, crop, brush, eraser, text, rectangle, circle, line, clone, blur, sharpen
      isDrawing: false,
      startX: 0,
      startY: 0,
      lastX: 0,
      lastY: 0,

      // Brush
      brushSize: 20,
      brushColor: '#000000',
      brushOpacity: 100,
      brushHardness: 50,

      // Text
      textColor: '#000000',
      textBackgroundColor: 'transparent',
      fontSize: 32,
      fontFamily: 'Arial',
      fontWeight: 'normal', // normal, bold, 100-900
      textBold: false,
      textItalic: false,
      textUnderline: false,
      textStrikethrough: false,
      textAlign: 'left', // left, center, right, justify
      letterSpacing: 0,
      lineHeight: 1.2,
      textShadow: {
        enabled: false,
        color: '#000000',
        blur: 0,
        offsetX: 0,
        offsetY: 0
      },
      textStroke: {
        enabled: false,
        color: '#000000',
        width: 1
      },
      textTransform: 'none', // none, uppercase, lowercase, capitalize
      textObjects: [],
      activeTextIndex: null,
      editingText: false,
      selectedTemplate: null, // Selected template for next text

      // Text Templates/Presets
      textTemplates: [
        {
          name: 'Sarı Metin',
          textColor: '#FFD700',
          fontSize: 48,
          fontFamily: 'Arial',
          fontWeight: 'bold',
          textBold: true,
          textItalic: false,
          textUnderline: false,
          textStrikethrough: false,
          textAlign: 'left',
          letterSpacing: 2,
          lineHeight: 1.2,
          textShadow: {
            enabled: true,
            color: '#000000',
            blur: 4,
            offsetX: 2,
            offsetY: 2
          },
          textStroke: {
            enabled: false,
            color: '#000000',
            width: 1
          },
          textTransform: 'none',
          backgroundColor: 'transparent',
          padding: 0
        },
        {
          name: 'Beyaz Metin - Koyu Arka Plan',
          textColor: '#FFFFFF',
          fontSize: 48,
          fontFamily: 'Arial',
          fontWeight: 'bold',
          textBold: true,
          textItalic: false,
          textUnderline: false,
          textStrikethrough: false,
          textAlign: 'left',
          letterSpacing: 1,
          lineHeight: 1.2,
          textShadow: {
            enabled: true,
            color: '#000000',
            blur: 6,
            offsetX: 3,
            offsetY: 3
          },
          textStroke: {
            enabled: false,
            color: '#000000',
            width: 1
          },
          textTransform: 'none',
          backgroundColor: '#2C3E50',
          padding: 15
        },
        {
          name: 'Beyaz Metin - Siyah Arka Plan',
          textColor: '#FFFFFF',
          fontSize: 48,
          fontFamily: 'Arial',
          fontWeight: 'bold',
          textBold: true,
          textItalic: false,
          textUnderline: false,
          textStrikethrough: false,
          textAlign: 'left',
          letterSpacing: 1,
          lineHeight: 1.2,
          textShadow: {
            enabled: true,
            color: '#000000',
            blur: 4,
            offsetX: 2,
            offsetY: 2
          },
          textStroke: {
            enabled: false,
            color: '#000000',
            width: 1
          },
          textTransform: 'none',
          backgroundColor: '#000000',
          padding: 15
        },
        {
          name: 'Koyu Metin - Turuncu Arka Plan',
          textColor: '#2C3E50',
          fontSize: 48,
          fontFamily: 'Arial',
          fontWeight: 'bold',
          textBold: true,
          textItalic: false,
          textUnderline: false,
          textStrikethrough: false,
          textAlign: 'left',
          letterSpacing: 1,
          lineHeight: 1.2,
          textShadow: {
            enabled: true,
            color: '#000000',
            blur: 4,
            offsetX: 2,
            offsetY: 2
          },
          textStroke: {
            enabled: false,
            color: '#000000',
            width: 1
          },
          textTransform: 'none',
          backgroundColor: '#FF6B35',
          padding: 15
        },
        {
          name: 'Beyaz Metin - Kırmızı Arka Plan',
          textColor: '#FFFFFF',
          fontSize: 48,
          fontFamily: 'Arial',
          fontWeight: 'bold',
          textBold: true,
          textItalic: false,
          textUnderline: false,
          textStrikethrough: false,
          textAlign: 'left',
          letterSpacing: 1,
          lineHeight: 1.2,
          textShadow: {
            enabled: true,
            color: '#000000',
            blur: 4,
            offsetX: 2,
            offsetY: 2
          },
          textStroke: {
            enabled: false,
            color: '#000000',
            width: 1
          },
          textTransform: 'none',
          backgroundColor: '#E74C3C',
          padding: 15
        },
        {
          name: 'Siyah Metin - Beyaz Arka Plan',
          textColor: '#000000',
          fontSize: 48,
          fontFamily: 'Arial',
          fontWeight: 'bold',
          textBold: true,
          textItalic: false,
          textUnderline: false,
          textStrikethrough: false,
          textAlign: 'left',
          letterSpacing: 1,
          lineHeight: 1.2,
          textShadow: {
            enabled: false,
            color: '#000000',
            blur: 0,
            offsetX: 0,
            offsetY: 0
          },
          textStroke: {
            enabled: false,
            color: '#000000',
            width: 1
          },
          textTransform: 'none',
          backgroundColor: '#FFFFFF',
          padding: 15
        }
      ],

      // History (Undo/Redo)
      history: [],
      historyIndex: -1,
      maxHistory: 50,

      // Zoom & Pan
      zoom: 1,
      panX: 0,
      panY: 0,
      isPanning: false,
      lastPanX: 0,
      lastPanY: 0,
      minZoom: 0.1,
      maxZoom: 10,

      // Filters & Adjustments
      brightness: 100,
      contrast: 100,
      saturation: 100,
      hue: 0,
      exposure: 0,
      gamma: 1,
      blur: 0,
      sharpen: 0,

      // Crop
      cropStartX: 0,
      cropStartY: 0,
      cropEndX: 0,
      cropEndY: 0,
      isCropping: false,
      cropAspectRatio: null,

      // Shapes
      shapes: [],
      activeShapeIndex: null,
      shapeColor: '#000000',
      shapeFillColor: 'transparent',
      shapeStrokeWidth: 2,
      shapeFilled: false,

      // Transform
      isTransforming: false,
      transformHandle: null,
      transformStartX: 0,
      transformStartY: 0,

      // Selection
      selection: null,
      isSelecting: false,

      // Clone Stamp
      cloneSource: null,
      cloneOffsetX: 0,
      cloneOffsetY: 0,

      // UI State
      showLayersPanel: false,
      showHistoryPanel: false,
      showPropertiesPanel: true,
      showToolbar: true,
      showZoomSlider: false,

      // Keyboard shortcuts
      shortcuts: {},

      init() {
        this.setupKeyboardShortcuts();

        // Set global reference immediately
        if (typeof window !== 'undefined') {
          window.postsImageEditor = this;
        }
      },

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
          // Only apply tool shortcuts if not typing and not holding Shift
          if (!e.ctrlKey && !e.metaKey && !e.altKey && !e.shiftKey && !isTyping) {
            switch(e.key.toLowerCase()) {
              case 'v': this.setTool('select'); break;
              case 'c': this.setTool('crop'); break;
              case 'b': this.setTool('brush'); break;
              case 'e': this.setTool('eraser'); break;
              case 't': this.setTool('text'); break;
              case 'r': this.setTool('rectangle'); break;
              case 'o': this.setTool('circle'); break;
              case 'l': this.setTool('line'); break;
              case 's': this.setTool('clone'); break;
            }
          }
        });
      },

      openEditor(identifier, url) {
        if (!url) {
          console.error('ImageEditor: URL is required');
          return;
        }

        this.isOpen = true;
        this.currentIndex = typeof identifier === 'string' ? null : identifier;
        this.currentFileId = typeof identifier === 'string' ? identifier : null;
        this.imageUrl = url;
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;
        this.history = [];
        this.historyIndex = -1;
        this.textObjects = [];
        this.shapes = [];
        this.layers = [];
        this.activeTool = 'select';
        this.selectedTemplate = null;
        this.resetFilters();

        // Ensure global reference is set
        if (typeof window !== 'undefined') {
          window.postsImageEditor = this;
        }

        this.$nextTick(() => {
          this.initCanvas();
        });
      },

      closeEditor() {
        this.isOpen = false;
        this.canvas = null;
        this.ctx = null;
        this.image = null;
        this.currentIndex = null;
        this.currentFileId = null;
        this.imageUrl = null;
        this.editingText = false;
      },

      initCanvas() {
        const canvasEl = document.getElementById('image-editor-canvas');
        if (!canvasEl) return;

        this.canvas = canvasEl;
        this.ctx = canvasEl.getContext('2d', { willReadFrequently: true });

        // Enable image smoothing
        this.ctx.imageSmoothingEnabled = true;
        this.ctx.imageSmoothingQuality = 'high';

        // Load image
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
          // Calculate canvas size to fit image
          const maxWidth = 1000;
          const maxHeight = 700;
          const imgAspect = img.width / img.height;
          const canvasAspect = maxWidth / maxHeight;

          if (imgAspect > canvasAspect) {
            this.canvasWidth = maxWidth;
            this.canvasHeight = maxWidth / imgAspect;
          } else {
            this.canvasHeight = maxHeight;
            this.canvasWidth = maxHeight * imgAspect;
          }

          this.canvas.width = this.canvasWidth;
          this.canvas.height = this.canvasHeight;

          this.image = img;

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

          this.draw();
          this.saveState();

          // Set initial zoom to 100% (don't auto-fit on load)
          this.zoom = 1;
          this.panX = 0;
          this.panY = 0;
          this.updateCanvasTransform();
          this.draw();
        };
        img.src = this.imageUrl;

        // Event listeners
        this.setupCanvasEvents();
      },

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
        } else if (this.activeTool === 'brush' || this.activeTool === 'eraser') {
          this.drawBrush(finalX, finalY, finalX, finalY);
        } else if (this.activeTool === 'clone') {
          if (!this.cloneSource) {
            this.cloneSource = { x: finalX, y: finalY };
            this.canvas.style.cursor = 'crosshair';
          } else {
            this.cloneOffsetX = finalX - this.cloneSource.x;
            this.cloneOffsetY = finalY - this.cloneSource.y;
            this.drawClone(finalX, finalY);
          }
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
        } else if (this.activeTool === 'rectangle') {
          this.shapes.push({
            type: 'rectangle',
            x: finalX,
            y: finalY,
            width: 0,
            height: 0,
            color: this.shapeColor,
            fillColor: this.shapeFillColor,
            strokeWidth: this.shapeStrokeWidth,
            filled: this.shapeFilled
          });
          this.activeShapeIndex = this.shapes.length - 1;
        } else if (this.activeTool === 'circle') {
          this.shapes.push({
            type: 'circle',
            x: finalX,
            y: finalY,
            radius: 0,
            color: this.shapeColor,
            fillColor: this.shapeFillColor,
            strokeWidth: this.shapeStrokeWidth,
            filled: this.shapeFilled
          });
          this.activeShapeIndex = this.shapes.length - 1;
        } else if (this.activeTool === 'line') {
          this.shapes.push({
            type: 'line',
            x1: finalX,
            y1: finalY,
            x2: finalX,
            y2: finalY,
            color: this.shapeColor,
            strokeWidth: this.shapeStrokeWidth
          });
          this.activeShapeIndex = this.shapes.length - 1;
        } else if (this.activeTool === 'select') {
          this.selectObject(finalX, finalY);
        } else if (this.activeTool === 'blur' || this.activeTool === 'sharpen') {
          this.applyFilterBrush(finalX, finalY);
        }
      },

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
        } else if (this.activeTool === 'brush' || this.activeTool === 'eraser') {
          this.drawBrush(this.lastX, this.lastY, finalX, finalY);
          this.lastX = finalX;
          this.lastY = finalY;
        } else if (this.activeTool === 'clone' && this.cloneSource) {
          this.drawClone(finalX, finalY);
        } else if (this.activeTool === 'rectangle' && this.activeShapeIndex !== null) {
          const shape = this.shapes[this.activeShapeIndex];
          shape.width = finalX - shape.x;
          shape.height = finalY - shape.y;
          this.draw();
        } else if (this.activeTool === 'circle' && this.activeShapeIndex !== null) {
          const shape = this.shapes[this.activeShapeIndex];
          const dx = finalX - shape.x;
          const dy = finalY - shape.y;
          shape.radius = Math.sqrt(dx * dx + dy * dy);
          this.draw();
        } else if (this.activeTool === 'line' && this.activeShapeIndex !== null) {
          const shape = this.shapes[this.activeShapeIndex];
          shape.x2 = finalX;
          shape.y2 = finalY;
          this.draw();
        } else if ((this.activeTool === 'select' || this.activeTool === 'text') && this.activeTextIndex !== null && !this.editingText) {
          // Move text when dragging in select or text tool
          const text = this.textObjects[this.activeTextIndex];
          text.x = finalX;
          text.y = finalY;
          this.draw();
        } else if (this.activeTool === 'blur' || this.activeTool === 'sharpen') {
          this.applyFilterBrush(finalX, finalY);
        }
      },

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
        } else if (this.isDrawing && (this.activeTool === 'brush' || this.activeTool === 'eraser' ||
                 this.activeTool === 'clone' || this.activeTool === 'blur' || this.activeTool === 'sharpen')) {
          this.saveState();
          this.isDrawing = false;
        } else if (this.activeShapeIndex !== null || this.activeTextIndex !== null) {
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

      updateCursor(x, y) {
        if (this.activeTool === 'pan') {
          this.canvas.style.cursor = this.isPanning ? 'grabbing' : 'grab';
        } else if (this.activeTool === 'brush' || this.activeTool === 'eraser') {
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

      drawBrush(x1, y1, x2, y2) {
        this.ctx.save();

        if (this.activeTool === 'brush') {
          this.ctx.globalCompositeOperation = 'source-over';
          this.ctx.globalAlpha = this.brushOpacity / 100;
          this.ctx.strokeStyle = this.brushColor;
        } else {
          this.ctx.globalCompositeOperation = 'destination-out';
          this.ctx.globalAlpha = this.brushOpacity / 100;
        }

        this.ctx.lineWidth = this.brushSize;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';

        // Brush hardness (softness)
        if (this.brushHardness < 100) {
          const gradient = this.ctx.createRadialGradient(x2, y2, 0, x2, y2, this.brushSize / 2);
          const alpha = this.brushOpacity / 100;
          gradient.addColorStop(0, this.activeTool === 'brush' ?
            `rgba(${this.hexToRgb(this.brushColor).r}, ${this.hexToRgb(this.brushColor).g}, ${this.hexToRgb(this.brushColor).b}, ${alpha})` :
            `rgba(0, 0, 0, ${alpha})`);
          gradient.addColorStop(1, this.activeTool === 'brush' ?
            `rgba(${this.hexToRgb(this.brushColor).r}, ${this.hexToRgb(this.brushColor).g}, ${this.hexToRgb(this.brushColor).b}, 0)` :
            'rgba(0, 0, 0, 0)');
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

      drawClone(x, y) {
        if (!this.cloneSource || !this.image) return;

        const sourceX = this.cloneSource.x - this.cloneOffsetX;
        const sourceY = this.cloneSource.y - this.cloneOffsetY;
        const size = this.brushSize;

        this.ctx.save();
        this.ctx.globalCompositeOperation = 'source-over';

        // Draw circular clone area
        this.ctx.beginPath();
        this.ctx.arc(x, y, size / 2, 0, Math.PI * 2);
        this.ctx.clip();

        this.ctx.drawImage(
          this.image,
          sourceX - size / 2, sourceY - size / 2, size, size,
          x - size / 2, y - size / 2, size, size
        );

        this.ctx.restore();
      },

      applyFilterBrush(x, y) {
        // This is a simplified version - in a real implementation, you'd use convolution filters
        const size = this.brushSize;
        const imageData = this.ctx.getImageData(x - size / 2, y - size / 2, size, size);
        const data = imageData.data;

        if (this.activeTool === 'blur') {
          // Simple box blur
          for (let i = 0; i < data.length; i += 4) {
            const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
            data[i] = avg;
            data[i + 1] = avg;
            data[i + 2] = avg;
          }
        } else if (this.activeTool === 'sharpen') {
          // Simple sharpen
          for (let i = 0; i < data.length; i += 4) {
            data[i] = Math.min(255, data[i] * 1.2);
            data[i + 1] = Math.min(255, data[i + 1] * 1.2);
            data[i + 2] = Math.min(255, data[i + 2] * 1.2);
          }
        }

        this.ctx.putImageData(imageData, x - size / 2, y - size / 2);
      },

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
            this.activeShapeIndex = null;
            this.draw();
            return;
          }
        }

        // Check shapes
        for (let i = this.shapes.length - 1; i >= 0; i--) {
          const shape = this.shapes[i];
          if (this.isPointInShape(x, y, shape)) {
            this.activeShapeIndex = i;
            this.activeTextIndex = null;
            this.draw();
            return;
          }
        }

        this.activeTextIndex = null;
        this.activeShapeIndex = null;
        this.draw();
      },

      isPointInShape(x, y, shape) {
        if (shape.type === 'rectangle') {
          const minX = Math.min(shape.x, shape.x + shape.width);
          const maxX = Math.max(shape.x, shape.x + shape.width);
          const minY = Math.min(shape.y, shape.y + shape.height);
          const maxY = Math.max(shape.y, shape.y + shape.height);
          return x >= minX && x <= maxX && y >= minY && y <= maxY;
        } else if (shape.type === 'circle') {
          const dx = x - shape.x;
          const dy = y - shape.y;
          return Math.sqrt(dx * dx + dy * dy) <= shape.radius;
        } else if (shape.type === 'line') {
          const dist = this.distanceToLine(x, y, shape.x1, shape.y1, shape.x2, shape.y2);
          return dist <= 5;
        }
        return false;
      },

      distanceToLine(px, py, x1, y1, x2, y2) {
        const A = px - x1;
        const B = py - y1;
        const C = x2 - x1;
        const D = y2 - y1;
        const dot = A * C + B * D;
        const lenSq = C * C + D * D;
        let param = -1;
        if (lenSq !== 0) param = dot / lenSq;
        let xx, yy;
        if (param < 0) {
          xx = x1;
          yy = y1;
        } else if (param > 1) {
          xx = x2;
          yy = y2;
        } else {
          xx = x1 + param * C;
          yy = y1 + param * D;
        }
        const dx = px - xx;
        const dy = py - yy;
        return Math.sqrt(dx * dx + dy * dy);
      },

      draw() {
        if (!this.ctx || !this.image) return;

        // Update canvas transform (zoom and pan)
        this.updateCanvasTransform();

        // Clear canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // Draw background pattern (transparency indicator)
        this.drawCheckerboard();

        // Apply global filters
        this.ctx.save();
        this.ctx.filter = this.buildFilterString();

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

        this.ctx.restore();

        // Draw shapes
        this.shapes.forEach((shape, index) => {
          this.drawShape(shape, index === this.activeShapeIndex);
        });

        // Draw text
        this.textObjects.forEach((text, index) => {
          this.drawText(text, index === this.activeTextIndex);
        });

        // Draw crop overlay
        if (this.isCropping) {
          this.drawCropOverlay();
        }

        // Draw selection
        if (this.selection) {
          this.drawSelection();
        }
      },

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

      buildFilterString() {
        const filters = [];
        if (this.brightness !== 100) filters.push(`brightness(${this.brightness}%)`);
        if (this.contrast !== 100) filters.push(`contrast(${this.contrast}%)`);
        if (this.saturation !== 100) filters.push(`saturate(${this.saturation}%)`);
        if (this.hue !== 0) filters.push(`hue-rotate(${this.hue}deg)`);
        if (this.blur > 0) filters.push(`blur(${this.blur}px)`);
        return filters.length > 0 ? filters.join(' ') : 'none';
      },

      drawShape(shape, isActive) {
        this.ctx.save();

        if (isActive) {
          this.ctx.strokeStyle = '#0066ff';
          this.ctx.lineWidth = shape.strokeWidth + 2;
        } else {
          this.ctx.strokeStyle = shape.color;
          this.ctx.lineWidth = shape.strokeWidth;
        }

        if (shape.filled && shape.fillColor !== 'transparent') {
          this.ctx.fillStyle = shape.fillColor;
        }

        if (shape.type === 'rectangle') {
          if (shape.filled) {
            this.ctx.fillRect(shape.x, shape.y, shape.width, shape.height);
          }
          this.ctx.strokeRect(shape.x, shape.y, shape.width, shape.height);
        } else if (shape.type === 'circle') {
          this.ctx.beginPath();
          this.ctx.arc(shape.x, shape.y, shape.radius, 0, Math.PI * 2);
          if (shape.filled) {
            this.ctx.fill();
          }
          this.ctx.stroke();
        } else if (shape.type === 'line') {
          this.ctx.beginPath();
          this.ctx.moveTo(shape.x1, shape.y1);
          this.ctx.lineTo(shape.x2, shape.y2);
          this.ctx.stroke();
        }

        this.ctx.restore();
      },

      drawText(text, isActive) {
        this.ctx.save();

        // Calculate text dimensions first for background (will be recalculated later, but needed for background)
        const tempFont = `${text.textItalic ? 'italic ' : ''}${text.fontWeight || (text.textBold ? 'bold' : 'normal')} ${text.fontSize}px ${text.fontFamily}`;
        this.ctx.font = tempFont;
        const textAlignValue = text.textAlign || 'left';
        const letterSpacingValue = text.letterSpacing || 0;

        // Calculate text width
        let tempTextWidth = 0;
        if (letterSpacingValue === 0) {
          const tempMetrics = this.ctx.measureText(text.text);
          tempTextWidth = tempMetrics.width;
        } else {
          for (let i = 0; i < text.text.length; i++) {
            const charMetrics = this.ctx.measureText(text.text[i]);
            tempTextWidth += charMetrics.width;
            if (i < text.text.length - 1) {
              tempTextWidth += letterSpacingValue;
            }
          }
        }
        const tempTextHeight = text.fontSize * (text.lineHeight || 1.2);

        // Draw background if exists - will be recalculated after text width is determined
        // We'll draw it after calculating the actual text dimensions

        // Apply text transform
        let displayText = text.text;
        if (text.textTransform === 'uppercase') {
          displayText = displayText.toUpperCase();
        } else if (text.textTransform === 'lowercase') {
          displayText = displayText.toLowerCase();
        } else if (text.textTransform === 'capitalize') {
          displayText = displayText.split(' ').map(word =>
            word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
          ).join(' ');
        }

        // Build font string
        const fontWeight = text.fontWeight || (text.textBold ? 'bold' : 'normal');
        const fontStyle = `${text.textItalic ? 'italic ' : ''}${fontWeight} ${text.fontSize}px ${text.fontFamily}`;
        this.ctx.font = fontStyle;
        this.ctx.textBaseline = 'alphabetic';

        // Letter spacing - Canvas API doesn't support it directly, so we'll draw characters individually
        // Use the already defined variables

        // Set textBaseline first to get accurate measurements
        this.ctx.textBaseline = 'alphabetic';

        // Calculate text position and width
        let textWidth = 0;
        let textX = text.x;
        const textY = text.y;
        const textHeight = text.fontSize * (text.lineHeight || 1.2);

        // Get actual text metrics for better vertical centering
        // For alphabetic baseline, we need to account for descenders
        const tempMetrics = this.ctx.measureText(displayText);
        const actualTextHeight = text.fontSize; // Approximate text height
        const textAscent = actualTextHeight * 0.5; // Approximate ascent (top part)
        const textDescent = actualTextHeight * 0.5; // Approximate descent (bottom part)

        // Calculate text width first
        if (letterSpacingValue === 0) {
          const metrics = this.ctx.measureText(displayText);
          textWidth = metrics.width;
        } else {
          // Calculate total width with letter spacing
          for (let i = 0; i < displayText.length; i++) {
            const charMetrics = this.ctx.measureText(displayText[i]);
            textWidth += charMetrics.width;
            if (i < displayText.length - 1) {
              textWidth += letterSpacingValue;
            }
          }
        }

        // Set textAlign and calculate textX based on alignment
        // Align relative to image width (canvasWidth), not absolute position
        let alignedX = text.x;

        // Calculate alignment based on image width
        if (textAlignValue === 'center') {
          alignedX = this.canvasWidth / 2;
        } else if (textAlignValue === 'right') {
          alignedX = this.canvasWidth;
        } else if (textAlignValue === 'justify') {
          // Justify uses full image width, but keep original x for now
          alignedX = text.x;
        } else {
          // left - keep original x position
          alignedX = text.x;
        }

        // For letter spacing, we need to manually calculate positions
        if (letterSpacingValue === 0) {
          // Use Canvas textAlign for proper alignment
          this.ctx.textAlign = textAlignValue;
          textX = alignedX; // Use aligned x based on image width
        } else {
          // Manual calculation for letter spacing
          this.ctx.textAlign = 'left'; // Always left for character-by-character rendering
          if (textAlignValue === 'center') {
            textX = alignedX - textWidth / 2;
          } else if (textAlignValue === 'right') {
            textX = alignedX - textWidth;
          } else {
            textX = alignedX;
          }
        }

        // Get text color (support both 'color' and 'textColor' properties)
        const textColor = text.color || text.textColor || '#000000';

        // Draw background if exists - now that we have the actual text dimensions
        // Draw BEFORE text so text appears on top
        const bgColor = text.backgroundColor || 'transparent';
        const bgPadding = text.padding || 0;
        if (bgColor && bgColor !== 'transparent' && bgPadding > 0) {
          this.ctx.fillStyle = bgColor;
          // Calculate background position to center text vertically
          // textY is the baseline (alphabetic), so text's top is approximately textY - fontSize * 0.8
          // We want to center the background around the text's visual center

          // Calculate background X position based on text alignment
          // Use alignedX (based on image width) instead of text.x
          let bgX;
          if (letterSpacingValue === 0) {
            // For normal text, use alignedX as reference point (Canvas textAlign handles alignment)
            if (textAlignValue === 'center') {
              bgX = alignedX - textWidth / 2 - bgPadding;
            } else if (textAlignValue === 'right') {
              bgX = alignedX - textWidth - bgPadding;
            } else {
              bgX = alignedX - bgPadding;
            }
          } else {
            // For letter spacing, use calculated textX
            bgX = textX - bgPadding;
          }

          // For alphabetic baseline: text top ≈ textY - fontSize * 0.8, text bottom ≈ textY + fontSize * 0.2
          // Visual center of text (vertical) ≈ textY - fontSize * 0.3
          const textVisualTop = textY - (text.fontSize * 0.8);
          const textVisualBottom = textY + (text.fontSize * 0.2);
          const textVisualCenter = (textVisualTop + textVisualBottom) / 2;
          // Center background around text's visual center
          const bgY = textVisualCenter - (textHeight / 2) - bgPadding;
          const bgWidth = textWidth + (bgPadding * 2);
          const bgHeight = textHeight + (bgPadding * 2);
          this.ctx.fillRect(bgX, bgY, bgWidth, bgHeight);
        }

        // Draw text (with or without letter spacing)
        if (letterSpacingValue === 0) {
          // Normal rendering
          // Draw text shadow first (if enabled) - shadow needs to be drawn before main text
          if (text.textShadow && text.textShadow.enabled) {
            this.ctx.save();
            this.ctx.shadowColor = text.textShadow.color;
            this.ctx.shadowBlur = text.textShadow.blur;
            this.ctx.shadowOffsetX = text.textShadow.offsetX;
            this.ctx.shadowOffsetY = text.textShadow.offsetY;
            this.ctx.fillStyle = textColor;
            this.ctx.fillText(displayText, textX, textY);
            this.ctx.restore();
          }

          // Draw text stroke (if enabled)
          if (text.textStroke && text.textStroke.enabled) {
            this.ctx.strokeStyle = text.textStroke.color;
            this.ctx.lineWidth = text.textStroke.width;
            this.ctx.strokeText(displayText, textX, textY);
          }

          // Draw main text (only if shadow is not enabled, otherwise shadow already drew it)
          if (!text.textShadow || !text.textShadow.enabled) {
            this.ctx.fillStyle = textColor;
            this.ctx.fillText(displayText, textX, textY);
          }
        } else {
          // Character-by-character rendering with letter spacing
          let currentX = textX;
          for (let i = 0; i < displayText.length; i++) {
            const char = displayText[i];

            // Draw text shadow first (if enabled)
            if (text.textShadow && text.textShadow.enabled) {
              this.ctx.save();
              this.ctx.shadowColor = text.textShadow.color;
              this.ctx.shadowBlur = text.textShadow.blur;
              this.ctx.shadowOffsetX = text.textShadow.offsetX;
              this.ctx.shadowOffsetY = text.textShadow.offsetY;
              this.ctx.fillStyle = textColor;
              this.ctx.fillText(char, currentX, textY);
              this.ctx.restore();
            }

            // Draw text stroke (if enabled)
            if (text.textStroke && text.textStroke.enabled) {
              this.ctx.strokeStyle = text.textStroke.color;
              this.ctx.lineWidth = text.textStroke.width;
              this.ctx.strokeText(char, currentX, textY);
            }

            // Draw main text (only if shadow is not enabled)
            if (!text.textShadow || !text.textShadow.enabled) {
              this.ctx.fillStyle = textColor;
              this.ctx.fillText(char, currentX, textY);
            }

            const charMetrics = this.ctx.measureText(char);
            currentX += charMetrics.width + letterSpacingValue;
          }
        }

        // Get text color for underline/strikethrough
        const textColorForLines = text.color || text.textColor || '#000000';

        // Draw underline
        if (text.textUnderline) {
          this.ctx.strokeStyle = textColorForLines;
          this.ctx.lineWidth = Math.max(1, text.fontSize / 20);
          this.ctx.beginPath();
          this.ctx.moveTo(textX, textY + 2);
          this.ctx.lineTo(textX + textWidth, textY + 2);
          this.ctx.stroke();
        }

        // Draw strikethrough
        if (text.textStrikethrough) {
          this.ctx.strokeStyle = textColorForLines;
          this.ctx.lineWidth = Math.max(1, text.fontSize / 20);
          this.ctx.beginPath();
          const strikeY = textY - textHeight / 2;
          this.ctx.moveTo(textX, strikeY);
          this.ctx.lineTo(textX + textWidth, strikeY);
          this.ctx.stroke();
        }

        // Selection border removed - no visual indicator when text is selected

        this.ctx.restore();
      },

      drawCropOverlay() {
        const x = Math.min(this.cropStartX, this.cropEndX);
        const y = Math.min(this.cropStartY, this.cropEndY);
        const w = Math.abs(this.cropEndX - this.cropStartX);
        const h = Math.abs(this.cropEndY - this.cropStartY);

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

      drawSelection() {
        // Selection rectangle
        this.ctx.save();
        this.ctx.strokeStyle = '#0066ff';
        this.ctx.lineWidth = 1;
        this.ctx.setLineDash([5, 5]);
        this.ctx.strokeRect(this.selection.x, this.selection.y, this.selection.width, this.selection.height);
        this.ctx.restore();
      },

      addText(x, y, template = null) {
        const text = prompt('Metin girin:');
        if (text) {
          let textConfig = {};

          if (template) {
            // Use template settings
            textConfig = {
              text: text,
              x: x,
              y: y,
              color: template.textColor,
              fontSize: template.fontSize,
              fontFamily: template.fontFamily,
              fontWeight: template.fontWeight,
              textBold: template.textBold,
              textItalic: template.textItalic,
              textUnderline: template.textUnderline,
              textStrikethrough: template.textStrikethrough,
              textAlign: template.textAlign,
              letterSpacing: template.letterSpacing,
              lineHeight: template.lineHeight,
              textShadow: JSON.parse(JSON.stringify(template.textShadow)),
              textStroke: JSON.parse(JSON.stringify(template.textStroke)),
              textTransform: template.textTransform,
              backgroundColor: template.backgroundColor,
              padding: template.padding
            };
          } else {
            // Use current settings
            textConfig = {
              text: text,
              x: x,
              y: y,
              color: this.textColor,
              fontSize: this.fontSize,
              fontFamily: this.fontFamily,
              fontWeight: this.textBold ? 'bold' : this.fontWeight,
              textBold: this.textBold,
              textItalic: this.textItalic,
              textUnderline: this.textUnderline,
              textStrikethrough: this.textStrikethrough,
              textAlign: this.textAlign,
              letterSpacing: this.letterSpacing,
              lineHeight: this.lineHeight,
              textShadow: JSON.parse(JSON.stringify(this.textShadow)),
              textStroke: JSON.parse(JSON.stringify(this.textStroke)),
              textTransform: this.textTransform,
              backgroundColor: this.textBackgroundColor || 'transparent',
              padding: (this.textBackgroundColor && this.textBackgroundColor !== 'transparent') ? 15 : 0
            };
          }

          this.textObjects.push(textConfig);
          this.activeTextIndex = this.textObjects.length - 1;
          this.draw();
          this.saveState();
        }
      },

      applyTemplate(templateIndex) {
        if (this.activeTool === 'text' && this.textTemplates[templateIndex]) {
          const template = this.textTemplates[templateIndex];

          // If there's an active text, update it
          if (this.activeTextIndex !== null) {
            const text = this.textObjects[this.activeTextIndex];
            text.color = template.textColor;
            text.fontSize = template.fontSize;
            text.fontFamily = template.fontFamily;
            text.fontWeight = template.fontWeight;
            text.textBold = template.textBold;
            text.textItalic = template.textItalic;
            text.textUnderline = template.textUnderline;
            text.textStrikethrough = template.textStrikethrough;
            text.textAlign = template.textAlign;
            text.letterSpacing = template.letterSpacing;
            text.lineHeight = template.lineHeight;
            text.textShadow = JSON.parse(JSON.stringify(template.textShadow));
            text.textStroke = JSON.parse(JSON.stringify(template.textStroke));
            text.textTransform = template.textTransform;
            text.backgroundColor = template.backgroundColor;
            text.padding = template.padding;
            this.draw();
            this.saveState();
          } else {
            // Update current text settings to match template (for next text)
            this.textColor = template.textColor;
            this.fontSize = template.fontSize;
            this.fontFamily = template.fontFamily;
            this.fontWeight = template.fontWeight;
            this.textBold = template.textBold;
            this.textItalic = template.textItalic;
            this.textUnderline = template.textUnderline;
            this.textStrikethrough = template.textStrikethrough;
            this.textAlign = template.textAlign;
            this.letterSpacing = template.letterSpacing;
            this.lineHeight = template.lineHeight;
            this.textShadow = JSON.parse(JSON.stringify(template.textShadow));
            this.textStroke = JSON.parse(JSON.stringify(template.textStroke));
            this.textTransform = template.textTransform;

            // Set flag to use template when adding next text
            this.selectedTemplate = template;
          }
        }
      },

      addTextFromTemplate(x, y, templateIndex) {
        if (this.textTemplates[templateIndex]) {
          const template = this.textTemplates[templateIndex];
          this.addText(x, y, template);
        }
      },

      editText() {
        if (this.activeTextIndex !== null) {
          this.editingText = true;
          this.draw();
        }
      },

      finishEditingText() {
        this.editingText = false;
        this.draw();
      },

      updateTextContent(newText) {
        if (this.activeTextIndex !== null && newText !== null && newText !== '') {
          this.textObjects[this.activeTextIndex].text = newText;
          this.draw();
          this.saveState();
        }
      },

      applyCrop() {
        if (!this.image) return;

        const x = Math.min(this.cropStartX, this.cropEndX);
        const y = Math.min(this.cropStartY, this.cropEndY);
        const w = Math.abs(this.cropEndX - this.cropStartX);
        const h = Math.abs(this.cropEndY - this.cropStartY);

        if (w < 10 || h < 10) return;

        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = w;
        tempCanvas.height = h;
        const tempCtx = tempCanvas.getContext('2d', { willReadFrequently: true });

        const scaleX = this.image.width / this.canvasWidth;
        const scaleY = this.image.height / this.canvasHeight;
        const sx = (x - this.panX) / this.zoom * scaleX;
        const sy = (y - this.panY) / this.zoom * scaleY;
        const sw = w / this.zoom * scaleX;
        const sh = h / this.zoom * scaleY;

        tempCtx.drawImage(this.image, sx, sy, sw, sh, 0, 0, w, h);

        const croppedImg = new Image();
        croppedImg.onload = () => {
          this.image = croppedImg;
          this.layers[0].image = croppedImg;
          this.canvasWidth = w;
          this.canvasHeight = h;
          this.canvas.width = w;
          this.canvas.height = h;
          this.zoom = 1;
          this.panX = 0;
          this.panY = 0;
          this.isCropping = false;
          this.draw();
          this.saveState();
        };
        croppedImg.src = tempCanvas.toDataURL();
      },

      setTool(tool) {
        this.activeTool = tool;
        this.activeTextIndex = null;
        this.activeShapeIndex = null;
        this.cloneSource = null;
        if (this.canvas) {
          this.updateCursor(0, 0);
        }
      },

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

      deleteSelected() {
        if (this.activeTextIndex !== null) {
          this.textObjects.splice(this.activeTextIndex, 1);
          this.activeTextIndex = null;
          this.draw();
          this.saveState();
        } else if (this.activeShapeIndex !== null) {
          this.shapes.splice(this.activeShapeIndex, 1);
          this.activeShapeIndex = null;
          this.draw();
          this.saveState();
        }
      },

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

      adjustFilter(type, value) {
        if (type === 'brightness') this.brightness = value;
        else if (type === 'contrast') this.contrast = value;
        else if (type === 'saturation') this.saturation = value;
        else if (type === 'hue') this.hue = value;
        else if (type === 'exposure') this.exposure = value;
        else if (type === 'gamma') this.gamma = value;
        else if (type === 'blur') this.blur = value;
        else if (type === 'sharpen') this.sharpen = value;
        this.draw();
      },

      resetFilters() {
        this.brightness = 100;
        this.contrast = 100;
        this.saturation = 100;
        this.hue = 0;
        this.exposure = 0;
        this.gamma = 1;
        this.blur = 0;
        this.sharpen = 0;
        this.draw();
      },

      resetZoom() {
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;
        this.updateCanvasTransform();
        this.draw();
      },

      setZoomFromPercent(percent) {
        // Convert percentage to zoom value (e.g., 100% = 1.0, 50% = 0.5)
        const zoomValue = parseFloat(percent) / 100;

        if (isNaN(zoomValue) || zoomValue <= 0) {
          return; // Invalid input, ignore
        }

        // Clamp zoom value within bounds
        this.zoom = Math.max(this.minZoom, Math.min(this.maxZoom, zoomValue));

        // Update transform and redraw
        this.updateCanvasTransform();
        this.draw();
      },

      updateCanvasTransform() {
        if (this.canvas) {
          const transform = `translate(${this.panX}px, ${this.panY}px) scale(${this.zoom})`;
          this.canvas.style.transform = transform;
          this.canvas.style.transformOrigin = '0 0';
          // Force a reflow to ensure transform is applied
          this.canvas.offsetHeight;
        }
      },

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
          shapes: JSON.parse(JSON.stringify(this.shapes)),
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

      undo() {
        if (this.historyIndex > 0) {
          this.historyIndex--;
          this.restoreState(this.history[this.historyIndex]);
        }
      },

      redo() {
        if (this.historyIndex < this.history.length - 1) {
          this.historyIndex++;
          this.restoreState(this.history[this.historyIndex]);
        }
      },

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
        this.shapes = JSON.parse(JSON.stringify(state.shapes || []));

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

      hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
          r: parseInt(result[1], 16),
          g: parseInt(result[2], 16),
          b: parseInt(result[3], 16)
        } : { r: 0, g: 0, b: 0 };
      },

      async saveEditedImage() {
        if (!this.canvas) return;

        try {
          // Loading state
          if (window.showToast) {
            window.showToast('Resim kaydediliyor...', 'info');
          }

          const dataURL = this.canvas.toDataURL('image/jpeg', 0.92);
          const blob = await this.dataURLToBlob(dataURL);

          // Blob boyutunu kontrol et (10MB limit)
          if (blob.size > 10 * 1024 * 1024) {
            throw new Error('Resim boyutu çok büyük (maksimum 10MB)');
          }

          const formData = new FormData();
          formData.append('image', blob, 'edited-image.jpg');

          if (this.currentFileId) {
            formData.append('file_id', this.currentFileId);
          } else if (this.currentIndex !== null) {
            formData.append('index', this.currentIndex);
          }

          // Use fetch to call Livewire endpoint (Livewire handles file uploads automatically)
          const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          if (!token) {
            throw new Error('CSRF token bulunamadı');
          }

          const controller = new AbortController();
          const timeoutId = setTimeout(() => controller.abort(), 60000);

          let response;
          try {
            response = await fetch('/admin/files/edit-image', {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: formData,
              signal: controller.signal
            });
            clearTimeout(timeoutId);
          } catch (fetchError) {
            clearTimeout(timeoutId);
            if (fetchError.name === 'AbortError') {
              throw new Error('İstek zaman aşımına uğradı. Lütfen tekrar deneyin.');
            }
            throw new Error('Sunucuya bağlanılamadı. Lütfen internet bağlantınızı kontrol edin.');
          }

          if (!response.ok) {
            const errorText = await response.text();
            let errorMessage = 'Resim kaydedilemedi';
            try {
              const errorData = JSON.parse(errorText);
              errorMessage = errorData.message || errorMessage;
            } catch (e) {
              errorMessage = `Sunucu hatası: ${response.status} ${response.statusText}`;
            }
            throw new Error(errorMessage);
          }

          const data = await response.json();

          if (data.success && data.data) {
            const result = data.data;

            // Find parent Livewire component (PostCreateNews, PostCreateGallery, etc.)
            let parentWire = null;

            // Try to find Livewire component using Livewire API
            if (window.Livewire) {
              const livewireComponents = window.Livewire.all();
              if (livewireComponents && livewireComponents.length > 0) {
                // Find a component that has updateFilePreview method
                for (const component of livewireComponents) {
                  if (component.__instance && typeof component.__instance.updateFilePreview === 'function') {
                    parentWire = component;
                    break;
                  }
                }
              }
            }

            // Fallback: Try to find by traversing DOM
            if (!parentWire) {
              const editorEl = document.querySelector('[x-data*="imageEditor"]');
              if (editorEl) {
                // Traverse up the DOM tree to find Livewire component
                let current = editorEl.parentElement;
                while (current && current !== document.body) {
                  // Check if element has wire:id attribute
                  if (current.hasAttribute && current.hasAttribute('wire:id')) {
                    const wireId = current.getAttribute('wire:id');
                    if (window.Livewire && window.Livewire.find) {
                      parentWire = window.Livewire.find(wireId);
                      if (parentWire && parentWire.__instance && typeof parentWire.__instance.updateFilePreview === 'function') {
                        break;
                      }
                    }
                  }
                  current = current.parentElement;
                }
              }
            }

            // Call updateFilePreview on parent component
            if (parentWire && typeof parentWire.call === 'function') {
              try {
                if (this.currentFileId) {
                  parentWire.call('updateFilePreview', this.currentFileId, result.image_url, result.temp_path || null);
                } else if (this.currentIndex !== null) {
                  parentWire.call('updateFilePreview', this.currentIndex, result.image_url, result.temp_path || null);
                }
              } catch (e) {
                console.warn('Could not call updateFilePreview on parent component:', e);
              }
            }

            // Dispatch event for any listeners
            window.dispatchEvent(new CustomEvent('image-edited', {
              detail: {
                fileId: this.currentFileId,
                index: this.currentIndex,
                url: result.image_url,
                temp_path: result.temp_path
              }
            }));

            this.closeEditor();

            if (window.showToast) {
              window.showToast('Resim başarıyla kaydedildi', 'success');
            }
          } else {
            throw new Error(data.message || 'Resim kaydedilemedi');
          }
        } catch (error) {
          console.error('Error saving image:', error);
          if (window.showToast) {
            const errorMessage = error.message || 'Bilinmeyen bir hata oluştu';
            window.showToast('Resim kaydedilirken bir hata oluştu: ' + errorMessage, 'error');
          }
        }
      },

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
      }
    }));
}
