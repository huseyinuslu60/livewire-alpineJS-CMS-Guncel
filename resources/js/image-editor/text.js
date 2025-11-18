/**
 * Image Editor - Text Operations
 * Text adding, editing, scaling, and rendering
 */

export function createTextMethods() {
  return {
    /**
     * Scale and load textObjects from saved data (for full image, no crop)
     */
    scaleAndLoadTextObjects(canvasWidth, canvasHeight, savedCanvasWidth, savedCanvasHeight, scaleX = null, scaleY = null) {
      if (!this.savedTextObjects || this.savedTextObjects.length === 0) {
        this.textObjects = [];
        return;
      }

      // Calculate scale factors
      let textScaleX = scaleX || 1;
      let textScaleY = scaleY || 1;

      if (savedCanvasWidth && savedCanvasHeight) {
        textScaleX = canvasWidth / savedCanvasWidth;
        textScaleY = canvasHeight / savedCanvasHeight;
      }

      console.log('Image Editor - Scaling textObjects (full image):', {
        savedCount: this.savedTextObjects.length,
        textScaleX: textScaleX,
        textScaleY: textScaleY,
        canvasSize: { width: canvasWidth, height: canvasHeight },
        savedCanvasSize: { width: savedCanvasWidth, height: savedCanvasHeight },
      });

      // Scale textObjects
      this.textObjects = this.savedTextObjects.map(textObj => {
        const scaled = JSON.parse(JSON.stringify(textObj));
        scaled.x = (scaled.x || 0) * textScaleX;
        scaled.y = (scaled.y || 0) * textScaleY;
        scaled.fontSize = (scaled.fontSize || 32) * Math.min(textScaleX, textScaleY);
        return scaled;
      });

      // Clear saved textObjects
      this.savedTextObjects = [];
    },

    /**
     * Scale and load textObjects from saved data with crop offset adjustment
     * IMPORTANT: Text objects in spot_data are saved relative to the CROPPED canvas (after crop was applied)
     * So we only need to scale them to the new canvas size, NOT adjust for crop offset
     */
    scaleAndLoadTextObjectsWithCrop(canvasWidth, canvasHeight, savedCanvasWidth, savedCanvasHeight, cropX, cropY, cropWidth, cropHeight) {
      if (!this.savedTextObjects || this.savedTextObjects.length === 0) {
        this.textObjects = [];
        return;
      }

      // Calculate scale factors from saved canvas to new cropped canvas
      // savedCanvasWidth/Height is the canvas size when textObjects were saved (AFTER crop was applied)
      // canvasWidth/Height is the new canvas size (also after crop)
      let textScaleX = 1;
      let textScaleY = 1;

      if (savedCanvasWidth && savedCanvasHeight && savedCanvasWidth > 0 && savedCanvasHeight > 0) {
        // Scale from saved canvas to new canvas
        textScaleX = canvasWidth / savedCanvasWidth;
        textScaleY = canvasHeight / savedCanvasHeight;
      } else {
        // Fallback: if no saved canvas dimensions, assume textObjects were saved relative to crop dimensions
        // Calculate scale from crop dimensions to new canvas
        const cropAspect = cropWidth / cropHeight;
        const canvasAspect = canvasWidth / canvasHeight;

        // Calculate what the saved canvas size would have been (based on crop dimensions)
        const maxWidth = 1000;
        const maxHeight = 700;

        let calculatedSavedCanvasWidth, calculatedSavedCanvasHeight;
        if (cropAspect > canvasAspect) {
          calculatedSavedCanvasWidth = maxWidth;
          calculatedSavedCanvasHeight = maxWidth / cropAspect;
        } else {
          calculatedSavedCanvasHeight = maxHeight;
          calculatedSavedCanvasWidth = maxHeight * cropAspect;
        }

        textScaleX = canvasWidth / calculatedSavedCanvasWidth;
        textScaleY = canvasHeight / calculatedSavedCanvasHeight;
      }

      console.log('Image Editor - Scaling textObjects with crop (crop-relative):', {
        savedCount: this.savedTextObjects.length,
        textScaleX: textScaleX,
        textScaleY: textScaleY,
        crop: { x: cropX, y: cropY, width: cropWidth, height: cropHeight },
        canvasSize: { width: canvasWidth, height: canvasHeight },
        savedCanvasSize: { width: savedCanvasWidth, height: savedCanvasHeight },
      });

      // Scale textObjects (no crop offset adjustment needed - they're already relative to cropped canvas)
      this.textObjects = this.savedTextObjects.map(textObj => {
        const scaled = JSON.parse(JSON.stringify(textObj));

        // Just scale to new canvas size (text objects are already relative to cropped canvas)
        scaled.x = (scaled.x || 0) * textScaleX;
        scaled.y = (scaled.y || 0) * textScaleY;
        scaled.fontSize = (scaled.fontSize || 32) * Math.min(textScaleX, textScaleY);

        return scaled;
      });

      // Clear saved textObjects
      this.savedTextObjects = [];
    },

    /**
     * Draw text on canvas
     */
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

      // Set textBaseline first to get accurate measurements
      this.ctx.textBaseline = 'alphabetic';

      // Calculate text position and width
      let textWidth = 0;
      let textX = text.x;
      const textY = text.y;
      const textHeight = text.fontSize * (text.lineHeight || 1.2);

      // Get actual text metrics for better vertical centering
      const tempMetrics = this.ctx.measureText(displayText);
      const actualTextHeight = text.fontSize; // Approximate text height

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

      this.ctx.restore();
    },

    /**
     * Add text to canvas
     */
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

    /**
     * Apply template to text
     */
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

    /**
     * Add text from template
     */
    addTextFromTemplate(x, y, templateIndex) {
      if (this.textTemplates[templateIndex]) {
        const template = this.textTemplates[templateIndex];
        this.addText(x, y, template);
      }
    },

    /**
     * Edit text
     */
    editText() {
      if (this.activeTextIndex !== null) {
        this.editingText = true;
        this.draw();
      }
    },

    /**
     * Finish editing text
     */
    finishEditingText() {
      this.editingText = false;
      this.draw();
    },

    /**
     * Update text content
     */
    updateTextContent(newText) {
      if (this.activeTextIndex !== null && newText !== null && newText !== '') {
        this.textObjects[this.activeTextIndex].text = newText;
        this.draw();
        this.saveState();
      }
    },

    /**
     * Change text color
     */
    changeTextColor(color) {
      if (this.activeTextIndex !== null) {
        this.textObjects[this.activeTextIndex].color = color;
        this.draw();
      } else {
        this.textColor = color;
      }
    },

    /**
     * Change text background color
     */
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

    /**
     * Change font size
     */
    changeFontSize(size) {
      if (this.activeTextIndex !== null) {
        this.textObjects[this.activeTextIndex].fontSize = parseInt(size);
        this.draw();
      } else {
        this.fontSize = parseInt(size);
      }
    },

    /**
     * Change font family
     */
    changeFontFamily(family) {
      if (this.activeTextIndex !== null) {
        this.textObjects[this.activeTextIndex].fontFamily = family;
        this.draw();
      } else {
        this.fontFamily = family;
      }
    },

    /**
     * Update text property
     */
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

    /**
     * Update text shadow
     */
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

    /**
     * Update text stroke
     */
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
  };
}

