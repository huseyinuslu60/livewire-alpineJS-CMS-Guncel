/**
 * Image Preview Renderer - Utility Functions
 * Helper functions for finding and parsing spot_data
 */

/**
 * Find and parse spot_data from img element
 * IMPORTANT: Only use spot_data from the img element itself, NOT from parent elements
 * This prevents applying wrong spot_data from another post
 * @param {HTMLElement} imgElement - The img element
 * @returns {object|null} Parsed spot_data or null
 */
export function findSpotData(imgElement) {
    // CRITICAL: Only use spot_data from the img element itself
    // Do NOT search in parent elements, as this could apply wrong spot_data from another post
    const hasSpotData = imgElement.getAttribute('data-has-spot-data') === 'true';
    const spotDataJson = imgElement.getAttribute('data-spot-data');

    // Note: Minimum length check is lenient (20 chars) to allow small but valid JSON
    // Very small JSONs like {"image":{}} are still valid, just unlikely to have meaningful data
    if (!hasSpotData || !spotDataJson || spotDataJson.length < 20) {
        // No valid spot_data on this image
        return null;
    }

    try {
        // Handle HTML-escaped JSON (common in Blade templates)
        // Try parsing as-is first, then try unescaping if needed
        let parsedJson = spotDataJson;
        
        // If the JSON appears to be HTML-escaped (contains &quot; or similar), try to unescape
        if (spotDataJson.includes('&quot;') || spotDataJson.includes('&amp;') || spotDataJson.includes('&lt;') || spotDataJson.includes('&gt;')) {
            // Create a temporary DOM element to unescape HTML entities
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = spotDataJson;
            parsedJson = tempDiv.textContent || tempDiv.innerText || spotDataJson;
        }
        
        const spotData = JSON.parse(parsedJson);
        // Validate structure
        if (!spotData || !spotData.image) {
            return null;
        }
        return spotData;
    } catch (e) {
        console.error('Preview Renderer - Failed to parse spot_data:', e, {
            spotDataJsonLength: spotDataJson?.length,
            spotDataJsonPreview: spotDataJson?.substring(0, 100),
        });
        return null;
    }
}

/**
 * Find canvas element for preview rendering
 * NEW: Uses data-image-key for exact matching (single source of truth)
 * Priority: 1) data-image-key exact match, 2) Sibling canvas, 3) ID-based search
 * @param {HTMLElement} imgElement - The img element
 * @returns {HTMLCanvasElement|null} Canvas element or null
 */
export function findPreviewCanvas(imgElement) {
    if (!imgElement) {
        console.warn('Preview Renderer - findPreviewCanvas: imgElement is null');
        return null;
    }

    // 1) PRIORITY: Find canvas by data-image-key (exact match - single source of truth)
    const imageKey = imgElement.getAttribute('data-image-key');
    if (imageKey) {
        const canvas = document.querySelector(`canvas[data-image-key="${imageKey}"]`);
        if (canvas) {
            console.log('Preview Renderer - Found canvas by data-image-key:', imageKey);
            return canvas;
        }
    }

    // 2) Fallback: Try to find canvas as direct sibling (most common layout)
    // Check previous sibling
    if (imgElement.previousElementSibling && 
        imgElement.previousElementSibling.tagName === 'CANVAS') {
        const canvas = imgElement.previousElementSibling;
        // Verify it's a preview canvas (has id starting with preview-canvas- or data-image-key)
        if ((canvas.id && canvas.id.startsWith('preview-canvas-')) || 
            canvas.getAttribute('data-image-key')) {
            // If img has imageKey, verify canvas matches
            if (imageKey) {
                const canvasImageKey = canvas.getAttribute('data-image-key');
                if (canvasImageKey === imageKey) {
                    return canvas;
                }
            } else {
                return canvas;
            }
        }
    }

    // Check next sibling
    if (imgElement.nextElementSibling && 
        imgElement.nextElementSibling.tagName === 'CANVAS') {
        const canvas = imgElement.nextElementSibling;
        if ((canvas.id && canvas.id.startsWith('preview-canvas-')) || 
            canvas.getAttribute('data-image-key')) {
            if (imageKey) {
                const canvasImageKey = canvas.getAttribute('data-image-key');
                if (canvasImageKey === imageKey) {
                    return canvas;
                }
            } else {
                return canvas;
            }
        }
    }

    // 3) Try to find canvas by ID matching img ID (legacy support)
    const imgId = imgElement.id;
    if (imgId && imgId.startsWith('preview-img-')) {
        const fileId = imgId.replace('preview-img-', '');
        const canvasId = `preview-canvas-${fileId}`;
        const canvas = document.getElementById(canvasId);
        if (canvas) {
            console.warn('Preview Renderer - Found canvas by legacy ID pattern:', canvasId);
            return canvas;
        }
    }

    // 4) Try to find canvas by data-file-id matching img's data-file-id (legacy support)
    const imgFileId = imgElement.getAttribute('data-file-id');
    if (imgFileId) {
        const canvasId = `preview-canvas-${imgFileId}`;
        const canvas = document.getElementById(canvasId);
        if (canvas) {
            console.warn('Preview Renderer - Found canvas by legacy data-file-id:', imgFileId);
            return canvas;
        }
    }

    // 5) Last resort: Try to find canvas in the same parent element (legacy support)
    const parent = imgElement.parentElement;
    if (parent) {
        // Look for canvas with matching ID pattern
        if (imgId && imgId.startsWith('preview-img-')) {
            const fileId = imgId.replace('preview-img-', '');
            const canvas = parent.querySelector(`canvas#preview-canvas-${fileId}`);
            if (canvas) {
                console.warn('Preview Renderer - Found canvas by legacy parent search:', fileId);
                return canvas;
            }
        }
    }

    // CRITICAL: Do NOT return random canvas from document
    console.warn('Preview Renderer - Could not find matching canvas for image:', {
        imageId: imgElement.id,
        imageKey: imageKey || 'none',
    });
    return null;
}

/**
 * Find preview image element by imageKey
 * NEW: Uses data-image-key for exact matching
 * @param {string} imageKey - Image key (existing:<fileId> or temp:<index>)
 * @returns {HTMLImageElement|null} Image element or null
 */
export function findPreviewImage(imageKey) {
    if (!imageKey) {
        console.warn('Preview Renderer - findPreviewImage: imageKey is required');
        return null;
    }

    // Priority: Find by data-image-key (exact match)
    const img = document.querySelector(`img[data-image-key="${imageKey}"]`);
    if (img) {
        console.log('Preview Renderer - Found image by data-image-key:', imageKey);
        return img;
    }

    // Fallback: Try legacy methods (for backward compatibility)
    // Parse imageKey to extract fileId or index
    if (imageKey.startsWith('existing:')) {
        const fileId = imageKey.replace('existing:', '');
        const legacyImg = document.querySelector(`img[data-file-id="${fileId}"]`) ||
                         document.getElementById(`preview-img-${fileId}`);
        if (legacyImg) {
            console.warn('Preview Renderer - Found image by legacy fileId:', fileId);
            return legacyImg;
        }
    } else if (imageKey.startsWith('temp:')) {
        const index = imageKey.replace('temp:', '');
        const legacyImg = document.querySelector(`img[data-file-index="${index}"]`);
        if (legacyImg) {
            console.warn('Preview Renderer - Found image by legacy index:', index);
            return legacyImg;
        }
    }

    console.warn('Preview Renderer - Could not find image for imageKey:', imageKey);
    return null;
}

/**
 * Resolve image source URL from spot_data or img element
 * @param {HTMLElement} imgElement - The img element
 * @param {object} imageData - Image data from spot_data
 * @returns {string} Image source URL
 */
export function resolveImageSource(imgElement, imageData) {
    const originalPath = imageData.original?.path;
    if (originalPath) {
        return originalPath.startsWith('http')
            ? originalPath
            : `${window.location.origin}/storage/${originalPath}`;
    }
    return imgElement.src;
}

