/**
 * Image Preview Renderer - Main Entry Point
 * Renders images with crop, effects, and textObjects from spot_data
 * Usage: Add data-has-spot-data="true" and data-spot-data attributes to img elements
 */

import { findSpotData, findPreviewCanvas, findPreviewImage, resolveImageSource } from './utils.js';
import { calculateCropRectangles, buildFilterString, renderTextObjects } from './rendering.js';

/**
 * Render preview image with spot_data (crop, effects, textObjects)
 * NEW: Can be called with imageKey or imgElement
 * @param {HTMLElement|string} imgElementOrImageKey - The img element or imageKey
 */
export function renderPreviewWithSpotData(imgElementOrImageKey) {
    // Support both imgElement and imageKey
    let imgElement = null;
    if (typeof imgElementOrImageKey === 'string') {
        // imageKey provided, find img element
        imgElement = findPreviewImage(imgElementOrImageKey);
        if (!imgElement) {
            console.warn('Preview Renderer - renderPreviewWithSpotData: Image not found for imageKey:', imgElementOrImageKey);
            return;
        }
    } else {
        imgElement = imgElementOrImageKey;
    }
    // Validate imgElement
    if (!imgElement || !imgElement.tagName || imgElement.tagName !== 'IMG') {
        console.warn('Preview Renderer - Invalid imgElement:', imgElement);
        return;
    }

    // Find and parse spot_data
    // IMPORTANT: Always parse with try/catch to prevent UI crashes
    let spotData = null;
    try {
        spotData = findSpotData(imgElement);
    } catch (e) {
        console.error('Preview Renderer - Error finding spot_data:', e);
        // Show regular image as fallback
        const canvas = findPreviewCanvas(imgElement);
        if (canvas) {
            canvas.style.display = 'none';
        }
        if (imgElement) {
            imgElement.style.display = 'block';
        }
        return;
    }

    if (!spotData || !spotData.image) {
        // No spot_data, show regular image
        const canvas = findPreviewCanvas(imgElement);
        if (canvas) {
            canvas.style.display = 'none';
        }
        if (imgElement) {
            imgElement.style.display = 'block';
        }
        return;
    }

    const imageData = spotData.image;

    // Find canvas element
    const canvas = findPreviewCanvas(imgElement);
    if (!canvas) {
        console.warn('Preview Renderer - Canvas not found for image:', imgElement.id);
        // Show regular image as fallback
        if (imgElement) {
            imgElement.style.display = 'block';
        }
        return;
    }

    // Resolve image source
    const imageSrc = resolveImageSource(imgElement, imageData);

    // Preview rendering started

    // Load and render image
    const img = new Image();
    img.crossOrigin = 'anonymous';

    img.onload = function() {
        // Image loaded - wait for layout to settle before getting dimensions
        // Use requestAnimationFrame to ensure DOM has calculated dimensions
        requestAnimationFrame(() => {
            // IMPORTANT: Use img element's actual rendered size, not container size
            // This ensures canvas matches the visual size of the image
            let canvasWidth = imgElement.clientWidth || imgElement.offsetWidth || 0;
            let canvasHeight = imgElement.clientHeight || imgElement.offsetHeight || 0;

            // If img element dimensions are 0, try computed style
            if (canvasWidth === 0 || canvasHeight === 0) {
                const computedStyle = window.getComputedStyle(imgElement);
                canvasWidth = parseFloat(computedStyle.width) || 0;
                canvasHeight = parseFloat(computedStyle.height) || 0;
            }

            // If still 0, try container as fallback
            if (canvasWidth === 0 || canvasHeight === 0) {
                const container = imgElement.parentElement;
                canvasWidth = container ? container.clientWidth : 0;
                canvasHeight = container ? container.clientHeight : 0;
            }

            // Ensure minimum dimensions
            if (canvasWidth === 0) canvasWidth = 64;
            if (canvasHeight === 0) canvasHeight = 64;

            // For posts index table cells (small fixed size), ensure square canvas
            // This prevents aspect ratio distortion in small table cells
            const isTableCell = canvasWidth <= 100 && canvasHeight <= 100;
            if (isTableCell) {
                // Use the larger dimension to create a square canvas
                const size = Math.max(canvasWidth, canvasHeight);
                canvasWidth = size;
                canvasHeight = size;
            }

            // Set canvas internal size (this is the actual pixel dimensions)
            canvas.width = canvasWidth;
            canvas.height = canvasHeight;

            // Set canvas CSS size to match img element (visual size)
            // Use the actual img element dimensions for CSS, not the calculated canvasWidth
            const imgDisplayWidth = imgElement.clientWidth || imgElement.offsetWidth || canvasWidth;
            const imgDisplayHeight = imgElement.clientHeight || imgElement.offsetHeight || canvasHeight;
            canvas.style.width = imgDisplayWidth + 'px';
            canvas.style.height = imgDisplayHeight + 'px';

            canvas.style.display = 'block';
            imgElement.style.display = 'none';

            // Continue with rendering
            renderImage();
        });

        function renderImage() {
            const ctx = canvas.getContext('2d');

        // CRITICAL: Clear canvas first to prevent showing old preview from other posts
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        // Get crop data
        const crop = imageData.variants?.desktop?.crop || imageData.variants?.mobile?.crop || null;
        const originalWidth = imageData.original?.width || img.width;
        const originalHeight = imageData.original?.height || img.height;

        // Crop and dimensions calculated

        // Calculate source and destination rectangles
        let sx = 0, sy = 0, sw = img.width, sh = img.height;
        let dx = 0, dy = 0, dw = canvas.width, dh = canvas.height;

        if (crop && Array.isArray(crop) && crop.length === 4 && crop[2] > 0 && crop[3] > 0) {
            const cropRects = calculateCropRectangles(
                crop, originalWidth, originalHeight,
                img.width, img.height,
                canvas.width, canvas.height
            );
            sx = cropRects.sx;
            sy = cropRects.sy;
            sw = cropRects.sw;
            sh = cropRects.sh;
            dx = cropRects.dx;
            dy = cropRects.dy;
            dw = cropRects.dw;
            dh = cropRects.dh;
        }

        // Apply effects
        const effects = imageData.effects || {};
        ctx.save();
        const filterString = buildFilterString(effects);
        // Effects applied
        if (filterString !== 'none') {
            ctx.filter = filterString;
        }

        // Draw cropped and filtered image
        // Drawing image to canvas
        ctx.drawImage(img, sx, sy, sw, sh, dx, dy, dw, dh);
        ctx.restore();

            // Draw text objects with proper scaling and crop offset handling
            const textObjects = imageData.textObjects || [];
            renderTextObjects(ctx, textObjects, imageData, {
                canvasWidth: canvas.width,
                canvasHeight: canvas.height,
                cropOffsetX: dx, // Crop offset in canvas coordinates
                cropOffsetY: dy, // Crop offset in canvas coordinates
                cropWidth: dw,   // Cropped image width in canvas
                cropHeight: dh   // Cropped image height in canvas
            });

            // Preview rendering complete
        }
    };

    img.onerror = function() {
        console.error('Preview Renderer - Failed to load image:', imageSrc);
        // Show regular image as fallback
        if (canvas) {
            canvas.style.display = 'none';
        }
        if (imgElement) {
            imgElement.style.display = 'block';
        }
    };

    img.src = imageSrc;
}

/**
 * Process a single image element for preview rendering
 * IMPORTANT: Only process images that have their own spot_data attribute
 * Do NOT use parent element's spot_data, as it might belong to another post
 * @param {HTMLImageElement} img - Image element
 */
function processImageForPreview(img) {
    // CRITICAL: Only process if image has its own spot_data attribute
    // Do NOT search in parent elements, as this could apply wrong spot_data from another post
    const hasSpotData = img.getAttribute('data-has-spot-data') === 'true';
    const spotDataJson = img.getAttribute('data-spot-data');

    // Validate spot_data exists and is valid
    if (!hasSpotData || !spotDataJson || spotDataJson.length < 50) {
        // No valid spot_data on this image, skip it
        return;
    }

    // Validate spot_data is valid JSON
    try {
        const parsed = JSON.parse(spotDataJson);
        if (!parsed || !parsed.image) {
            // Invalid spot_data structure, skip it
            return;
        }
    } catch (e) {
        // Invalid JSON, skip it
        return;
    }

    // Image has valid spot_data, render it
    renderImagePreview(img);
}

/**
 * Render image preview (handle loading state)
 * @param {HTMLImageElement} img - Image element
 */
function renderImagePreview(img) {
    if (img.complete) {
        // Image already loaded, render immediately
        renderPreviewWithSpotData(img);
    } else {
        // Image not loaded yet, wait for load event
        img.addEventListener('load', function() {
            renderPreviewWithSpotData(this);
        }, { once: true });
    }
}

/**
 * Initialize preview renderer for all images with spot_data
 * IMPORTANT: Only processes images that have their own valid spot_data attribute
 * Call this after DOM is loaded or after Livewire updates
 */
export function initPreviewRenderer() {
    // Find all IMG elements with spot_data attribute (must be IMG elements, not other elements)
    const imagesWithSpotData = document.querySelectorAll('img[data-has-spot-data="true"][data-spot-data]');

    // Process each image that has valid spot_data
    imagesWithSpotData.forEach(img => {
        // Verify it's actually an IMG element
        if (img.tagName !== 'IMG') {
            return;
        }

        // Verify spot_data exists and is valid
        // Note: Minimum length check is lenient (20 chars) to allow small but valid JSON
        // Very small JSONs like {"image":{}} are still valid, just unlikely to have meaningful data
        const spotDataJson = img.getAttribute('data-spot-data');
        if (!spotDataJson || spotDataJson.length < 20) {
            return;
        }

        // Process this image
        processImageForPreview(img);
    });

    // Also check preview-img-* images that might not have data-has-spot-data set yet
    // But only if they have data-spot-data attribute
    const previewImages = document.querySelectorAll('img[id^="preview-img-"][data-spot-data]');
    // Convert NodeList to Array for includes check
    const imagesWithSpotDataArray = Array.from(imagesWithSpotData);
    previewImages.forEach(img => {
        // Skip if already processed
        if (imagesWithSpotDataArray.includes(img)) {
            return;
        }

        // Verify spot_data exists and is valid
        // Note: Minimum length check is lenient (20 chars) to allow small but valid JSON
        // Very small JSONs like {"image":{}} are still valid, just unlikely to have meaningful data
        const spotDataJson = img.getAttribute('data-spot-data');
        if (!spotDataJson || spotDataJson.length < 20) {
            return;
        }

        // Set data-has-spot-data if not set
        if (img.getAttribute('data-has-spot-data') !== 'true') {
            img.setAttribute('data-has-spot-data', 'true');
        }

        // Process this image
        processImageForPreview(img);
    });
}

/**
 * Setup event listeners for Livewire and custom events
 */
function setupEventListeners() {
    // Re-render on Livewire update
    const livewireEvents = ['livewire:update', 'livewire:updated', 'livewire:load'];
    const renderDelays = [100, 300, 600]; // Multiple delays to catch elements created at different times

    livewireEvents.forEach(eventName => {
        document.addEventListener(eventName, function() {
            renderDelays.forEach(delay => {
                setTimeout(() => initPreviewRenderer(), delay);
            });
        });
    });

    // Listen for custom image-edited event
    document.addEventListener('image-edited', function() {
        const customDelays = [200, 500, 800];
        customDelays.forEach(delay => {
            setTimeout(() => initPreviewRenderer(), delay);
        });
    });
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPreviewRenderer);
} else {
    initPreviewRenderer();
}

// Setup event listeners
setupEventListeners();

// Make functions available globally for inline onload handlers and external calls
window.renderPreviewWithSpotData = renderPreviewWithSpotData;
window.initPreviewRenderer = initPreviewRenderer;

