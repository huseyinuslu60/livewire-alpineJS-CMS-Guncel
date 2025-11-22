/**
 * Image Preview Renderer - Rendering Functions
 * Core rendering logic for crop, effects, and text objects
 */

/**
 * Calculate crop coordinates and destination rectangle
 * @param {Array} crop - Crop array [x, y, width, height]
 * @param {number} originalWidth - Original image width
 * @param {number} originalHeight - Original image height
 * @param {number} imgWidth - Loaded image width
 * @param {number} imgHeight - Loaded image height
 * @param {number} canvasWidth - Canvas width
 * @param {number} canvasHeight - Canvas height
 * @returns {object} Source and destination rectangles
 */
export function calculateCropRectangles(crop, originalWidth, originalHeight, imgWidth, imgHeight, canvasWidth, canvasHeight) {
    const [cropX, cropY, cropWidth, cropHeight] = crop;

    // Ensure crop coordinates are within image bounds
    const maxX = Math.min(cropX + cropWidth, originalWidth);
    const maxY = Math.min(cropY + cropHeight, originalHeight);
    const finalCropX = Math.max(0, Math.min(cropX, originalWidth));
    const finalCropY = Math.max(0, Math.min(cropY, originalHeight));
    const finalCropWidth = Math.max(1, maxX - finalCropX);
    const finalCropHeight = Math.max(1, maxY - finalCropY);

    // Calculate scale factor from original image to loaded image
    const scaleX = imgWidth / originalWidth;
    const scaleY = imgHeight / originalHeight;

    // Convert crop coordinates from original image to loaded image coordinates
    let sx = finalCropX * scaleX;
    let sy = finalCropY * scaleY;
    let sw = finalCropWidth * scaleX;
    let sh = finalCropHeight * scaleY;

    // Ensure source coordinates are within image bounds
    sx = Math.max(0, Math.min(sx, imgWidth));
    sy = Math.max(0, Math.min(sy, imgHeight));
    sw = Math.max(1, Math.min(sw, imgWidth - sx));
    sh = Math.max(1, Math.min(sh, imgHeight - sy));

    // Maintain aspect ratio when scaling to canvas
    const cropAspect = sw / sh;
    const canvasAspect = canvasWidth / canvasHeight;

    let dw, dh, dx, dy;
    if (cropAspect > canvasAspect) {
        // Crop is wider - fit to canvas width
        dw = canvasWidth;
        dh = canvasWidth / cropAspect;
    } else {
        // Crop is taller - fit to canvas height
        dh = canvasHeight;
        dw = canvasHeight * cropAspect;
    }

    // Center the cropped image in canvas
    dx = (canvasWidth - dw) / 2;
    dy = (canvasHeight - dh) / 2;

    return { sx, sy, sw, sh, dx, dy, dw, dh };
}

/**
 * Compute auto crop when explicit crop is not provided.
 * Crops image to match canvas aspect ratio using focus hint.
 * @param {number} imgWidth - Loaded image width
 * @param {number} imgHeight - Loaded image height
 * @param {number} canvasWidth - Canvas width
 * @param {number} canvasHeight - Canvas height
 * @param {string} focus - Focus hint: 'center' | 'top' | 'bottom' | 'left' | 'right'
 * @returns {{sx:number, sy:number, sw:number, sh:number, dx:number, dy:number, dw:number, dh:number}}
 */
export function calculateAutoCropRectangles(imgWidth, imgHeight, canvasWidth, canvasHeight, focus = 'center') {
    const desiredAspect = canvasWidth / canvasHeight;
    const sourceAspect = imgWidth / imgHeight;

    let sw, sh;
    if (sourceAspect > desiredAspect) {
        // Image is wider than desired; crop width
        sh = imgHeight;
        sw = Math.floor(sh * desiredAspect);
    } else {
        // Image is taller; crop height
        sw = imgWidth;
        sh = Math.floor(sw / desiredAspect);
    }

    let sx = 0;
    let sy = 0;
    // Position crop based on focus
    switch ((focus || 'center').toLowerCase()) {
        case 'left':
            sx = 0;
            sy = Math.max(0, Math.floor((imgHeight - sh) / 2));
            break;
        case 'right':
            sx = Math.max(0, imgWidth - sw);
            sy = Math.max(0, Math.floor((imgHeight - sh) / 2));
            break;
        case 'top':
            sx = Math.max(0, Math.floor((imgWidth - sw) / 2));
            sy = 0;
            break;
        case 'bottom':
            sx = Math.max(0, Math.floor((imgWidth - sw) / 2));
            sy = Math.max(0, imgHeight - sh);
            break;
        case 'center':
        default:
            sx = Math.max(0, Math.floor((imgWidth - sw) / 2));
            sy = Math.max(0, Math.floor((imgHeight - sh) / 2));
            break;
    }

    // Destination rectangle fits canvas while keeping aspect
    let dw, dh, dx, dy;
    const cropAspect = sw / sh;
    const canvasAspect = canvasWidth / canvasHeight;
    if (cropAspect > canvasAspect) {
        dw = canvasWidth;
        dh = canvasWidth / cropAspect;
    } else {
        dh = canvasHeight;
        dw = canvasHeight * cropAspect;
    }
    dx = (canvasWidth - dw) / 2;
    dy = (canvasHeight - dh) / 2;

    return { sx, sy, sw, sh, dx, dy, dw, dh };
}

/**
 * Build CSS filter string from effects
 * @param {object} effects - Effects object
 * @returns {string} CSS filter string
 */
export function buildFilterString(effects) {
    const filters = [];

    if (effects.brightness !== undefined && effects.brightness !== 100) {
        filters.push(`brightness(${effects.brightness}%)`);
    }
    if (effects.contrast !== undefined && effects.contrast !== 100) {
        filters.push(`contrast(${effects.contrast}%)`);
    }
    if (effects.saturation !== undefined && effects.saturation !== 100) {
        filters.push(`saturate(${effects.saturation}%)`);
    }
    if (effects.hue !== undefined && effects.hue !== 0) {
        filters.push(`hue-rotate(${effects.hue}deg)`);
    }
    if (effects.blur !== undefined && effects.blur > 0) {
        filters.push(`blur(${effects.blur}px)`);
    }

    return filters.length > 0 ? filters.join(' ') : 'none';
}

/**
 * Render text objects on canvas with professional scaling and crop offset handling
 *
 * IMPORTANT: Text objects are saved relative to the CROPPED canvas in the editor.
 * When rendering in preview, we need to:
 * 1. Scale from saved cropped canvas to preview cropped area
 * 2. Add crop offset to position text in the full preview canvas
 * 3. Maintain aspect ratio for font size and padding
 *
 * @param {CanvasRenderingContext2D} ctx - Canvas context
 * @param {Array} textObjects - Array of text objects
 * @param {object} imageData - Image data from spot_data
 * @param {object} options - Rendering options
 * @param {number} options.canvasWidth - Full canvas width
 * @param {number} options.canvasHeight - Full canvas height
 * @param {number} options.cropOffsetX - X offset of cropped image in canvas (dx)
 * @param {number} options.cropOffsetY - Y offset of cropped image in canvas (dy)
 * @param {number} options.cropWidth - Width of cropped image in canvas (dw)
 * @param {number} options.cropHeight - Height of cropped image in canvas (dh)
 */
export function renderTextObjects(ctx, textObjects, imageData, options) {
    if (!textObjects || textObjects.length === 0) {
        return;
    }

    // Handle both old API (backward compatibility) and new API
    let canvasWidth, canvasHeight, cropOffsetX, cropOffsetY, cropWidth, cropHeight;

    if (typeof options === 'object' && options.canvasWidth !== undefined) {
        // New API with options object
        canvasWidth = options.canvasWidth;
        canvasHeight = options.canvasHeight;
        cropOffsetX = options.cropOffsetX || 0;
        cropOffsetY = options.cropOffsetY || 0;
        cropWidth = options.cropWidth || canvasWidth;
        cropHeight = options.cropHeight || canvasHeight;
    } else {
        // Old API (backward compatibility)
        canvasWidth = options || canvasWidth;
        canvasHeight = arguments[3] || canvasHeight;
        cropOffsetX = 0;
        cropOffsetY = 0;
        cropWidth = canvasWidth;
        cropHeight = canvasHeight;
    }

    // Get saved canvas dimensions from spot_data
    // These represent the canvas size AFTER crop was applied in the editor
    // Text objects are positioned relative to this cropped canvas
    const savedCanvas = imageData.canvas || {};
    let savedCanvasWidth = savedCanvas.width || 0;
    let savedCanvasHeight = savedCanvas.height || 0;

    // Fallback: If no saved canvas dimensions, try to calculate from original image
    // This handles legacy data or cases where canvas dimensions weren't saved
    if (!savedCanvasWidth || !savedCanvasHeight || savedCanvasWidth === 0 || savedCanvasHeight === 0) {
        const originalWidth = imageData.original?.width || 0;
        const originalHeight = imageData.original?.height || 0;

        if (originalWidth > 0 && originalHeight > 0) {
            // Calculate what the cropped canvas size would have been
            // Use the crop dimensions to estimate the saved canvas size
            const crop = imageData.variants?.desktop?.crop || imageData.variants?.mobile?.crop || null;

            if (crop && Array.isArray(crop) && crop.length === 4) {
                const [, , cropW, cropH] = crop;
                // Assume saved canvas was sized to fit the crop area
                // Use a standard max size (e.g., 1000x700) to calculate aspect ratio
                const cropAspect = cropW / cropH;
                const maxWidth = 1000;
                const maxHeight = 700;

                if (cropAspect > (maxWidth / maxHeight)) {
                    savedCanvasWidth = maxWidth;
                    savedCanvasHeight = maxWidth / cropAspect;
                } else {
                    savedCanvasHeight = maxHeight;
                    savedCanvasWidth = maxHeight * cropAspect;
                }
            } else {
                // No crop, use original dimensions
                savedCanvasWidth = originalWidth;
                savedCanvasHeight = originalHeight;
            }
        } else {
            // Last resort: use preview crop dimensions
            savedCanvasWidth = cropWidth;
            savedCanvasHeight = cropHeight;
        }
    }

    // Calculate scale factors from saved cropped canvas to preview cropped area
    // IMPORTANT: Text objects are saved relative to the cropped canvas (after crop was applied)
    // In preview, the cropped image is drawn at (dx, dy) with size (dw, dh)
    // So we need to scale text from savedCanvas to the cropped image area (dw, dh), then add offset (dx, dy)
    const scaleX = cropWidth / savedCanvasWidth;
    const scaleY = cropHeight / savedCanvasHeight;

    // Use uniform scale (minimum) to maintain aspect ratio and prevent distortion
    // This ensures text looks proportional regardless of canvas size differences
    const textScale = Math.min(scaleX, scaleY);

    textObjects.forEach(textObj => {
        ctx.save();

        // Get text alignment (affects how text is rendered, but text.x is always saved)
        const textAlign = textObj.textAlign || 'left';

        // IMPORTANT: text.x and text.y are ALWAYS saved relative to the cropped canvas
        // They are NOT affected by textAlign - textAlign only affects rendering

        // Step 1: Scale text position from saved cropped canvas to preview cropped area
        // text.x and text.y are saved relative to the cropped canvas, so scale to cropped image area
        const textXInCrop = (textObj.x / savedCanvasWidth) * cropWidth;
        const textYInCrop = (textObj.y / savedCanvasHeight) * cropHeight;

        // Step 2: Add crop offset to position text correctly in the full preview canvas
        // The cropped image is centered in the canvas at (dx, dy), so we need to add this offset
        let textX = textXInCrop + cropOffsetX;
        const textY = textYInCrop + cropOffsetY;

        // Step 3: Scale font size proportionally using uniform scale
        // For very small canvases (like 64x64px), allow smaller font sizes
        // Remove minimum font size restriction to allow proper scaling
        const fontSize = (textObj.fontSize || 32) * textScale;

        // Step 4: Scale padding proportionally
        const padding = (textObj.padding || 0) * textScale;

        // Step 5: Scale letter spacing proportionally
        const letterSpacing = (textObj.letterSpacing || 0) * textScale;

        // Set font properties
        const fontStyle = textObj.textItalic ? 'italic ' : '';
        const fontWeight = textObj.textBold ? 'bold ' : '';
        ctx.font = `${fontStyle}${fontWeight}${fontSize}px ${textObj.fontFamily || 'Arial'}`;
        ctx.fillStyle = textObj.color || textObj.textColor || '#000000';
        ctx.textAlign = textAlign;
        // IMPORTANT: Use 'alphabetic' baseline to match editor behavior
        // Editor uses 'alphabetic' baseline, so text.y is saved relative to alphabetic baseline
        ctx.textBaseline = 'alphabetic';

        // Measure text width (accounting for letter spacing)
        const text = textObj.text || '';
        let textWidth = ctx.measureText(text).width;
        if (letterSpacing !== 0 && text.length > 1) {
            textWidth += letterSpacing * (text.length - 1);
        }

        // Step 6: Adjust text X position based on alignment (matching editor logic)
        // In editor: textAlign affects the reference point for rendering
        // - 'left': textX is the left edge (already correct)
        // - 'center': textX should be the center of the cropped area
        // - 'right': textX should be the right edge of the cropped area
        // IMPORTANT: Alignment is relative to the cropped image area, not the full canvas
        let alignedTextX = textX;
        if (textAlign === 'center') {
            // Center text in the cropped area
            // In editor: alignedX = canvasWidth / 2 (of cropped canvas), so we use cropWidth / 2 + offset
            alignedTextX = (cropWidth / 2) + cropOffsetX;
        } else if (textAlign === 'right') {
            // Right align text in the cropped area
            // In editor: alignedX = canvasWidth (of cropped canvas), so we use cropWidth + offset
            alignedTextX = cropWidth + cropOffsetX;
        }
        // For 'left', alignedTextX = textX (already correct, includes cropOffsetX)

        // Draw text background if needed (with scaled padding and proper alignment)
        // IMPORTANT: Background positioning must account for 'alphabetic' baseline
        // For alphabetic baseline: text top ≈ textY - fontSize * 0.8, text bottom ≈ textY + fontSize * 0.2
        // Visual center of text (vertical) ≈ textY - fontSize * 0.3
        if (textObj.backgroundColor && textObj.backgroundColor !== 'transparent') {
            let bgX, bgY, bgWidth, bgHeight;

            // Calculate background position based on text alignment
            // Background should align with the text, not the textX position
            if (textAlign === 'center') {
                bgX = alignedTextX - (textWidth / 2) - padding;
            } else if (textAlign === 'right') {
                bgX = alignedTextX - textWidth - padding;
            } else {
                // 'left' alignment
                bgX = alignedTextX - padding;
            }

            // Calculate text visual bounds for alphabetic baseline
            // Text top is approximately textY - fontSize * 0.8
            // Text bottom is approximately textY + fontSize * 0.2
            // Visual center is approximately textY - fontSize * 0.3
            const textVisualTop = textY - (fontSize * 0.8);
            const textVisualBottom = textY + (fontSize * 0.2);
            const textVisualCenter = (textVisualTop + textVisualBottom) / 2;
            const textHeight = fontSize * (textObj.lineHeight || 1.2);

            // Center background around text's visual center (matching editor logic)
            bgY = textVisualCenter - (textHeight / 2) - padding;
            bgWidth = textWidth + (padding * 2);
            bgHeight = textHeight + (padding * 2);

            ctx.fillStyle = textObj.backgroundColor;
            ctx.fillRect(bgX, bgY, bgWidth, bgHeight);
        }

        // Draw text (Canvas textAlign will handle the alignment automatically)
        ctx.fillStyle = textObj.color || textObj.textColor || '#000000';
        ctx.fillText(text, alignedTextX, textY);

        ctx.restore();
    });
}

