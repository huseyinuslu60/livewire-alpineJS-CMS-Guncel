/**
 * Image Preview Renderer - Main Entry Point
 * Renders images with crop, effects, and textObjects from spot_data
 * Usage: Add data-has-spot-data="true" and data-spot-data attributes to img elements
 */

import { findSpotData, findPreviewCanvas, findPreviewImage, resolveImageSource } from './utils.js';
import { calculateCropRectangles, buildFilterString, renderTextObjects } from './rendering.js';
if (typeof window !== 'undefined') {
  window.__PreviewRenderCache__ = window.__PreviewRenderCache__ || {};
}

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

    const key = (typeof imgElement.getAttribute === 'function' && (imgElement.getAttribute('data-image-key') || imgElement.id)) || (typeof imgElementOrImageKey === 'string' ? imgElementOrImageKey : 'unknown');
    const prev = (typeof window !== 'undefined' && window.__PreviewRenderCache__ && window.__PreviewRenderCache__[key]) || null;
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
            // Ensure img element is visible
            imgElement.style.display = 'block';
            imgElement.style.visibility = 'visible';
            imgElement.style.opacity = '1';
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
            // Ensure img element is visible
            imgElement.style.display = 'block';
            imgElement.style.visibility = 'visible';
            imgElement.style.opacity = '1';
        }
        return;
    }

    const imageData = spotData.image;
    const spotHash = JSON.stringify(imageData).length + ':' + (imageData.original && imageData.original.path ? imageData.original.path : '') + ':' + (imgElement.src || '');
    const canvasForSkipCheck = findPreviewCanvas(imgElement);
    const canvasVisible = !!(canvasForSkipCheck && canvasForSkipCheck.style && canvasForSkipCheck.style.display === 'block');
    const imgHidden = !!(imgElement && imgElement.style && imgElement.style.display === 'none');
    if (prev && prev.lastHash === spotHash && prev.done === true && canvasVisible && imgHidden) {
        return;
    }

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

    // If imageSrc is null, it means both originalPath and imgSrc are Livewire temp URLs
    // Skip preview rendering and show regular image
    if (!imageSrc) {
        console.warn('Preview Renderer - Skipping preview render due to Livewire temp URLs', {
            originalPath: imageData.original?.path,
            imgSrc: imgElement.src,
        });
        if (canvas) {
            canvas.style.display = 'none';
        }
        if (imgElement) {
            // Ensure img element is visible
            imgElement.style.display = 'block';
            imgElement.style.visibility = 'visible';
            imgElement.style.opacity = '1';
            // Remove any inline styles that might hide it
            imgElement.style.width = '';
            imgElement.style.height = '';
        }
        return;
    }

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

            // Canvas CSS size is handled by CSS classes (w-full h-full), no inline styles needed
            // This allows CSS to control the visual size properly

            canvas.style.display = 'block';
            imgElement.style.display = 'none';

            if (typeof window !== 'undefined' && window.__PreviewRenderCache__) {
              window.__PreviewRenderCache__[key] = { lastHash: spotHash, rendering: true, done: false, ts: Date.now() };
            }
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

            if (typeof window !== 'undefined' && window.__PreviewRenderCache__ && window.__PreviewRenderCache__[key]) {
                window.__PreviewRenderCache__[key].rendering = false;
                window.__PreviewRenderCache__[key].done = true;
            }
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
    if (!hasSpotData || !spotDataJson || spotDataJson.length < 10) {
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
    const debounce = (fn) => { if (typeof window === 'undefined') return fn(); clearTimeout(window.__previewDebounceTimer); window.__previewDebounceTimer = setTimeout(fn, 150); };
    const scan = () => {
        const imagesWithSpotData = document.querySelectorAll('img[data-has-spot-data="true"][data-spot-data]');
        const previewImages = document.querySelectorAll('img[id^="preview-img-"][data-spot-data]');
        const imagesSet = new Set();
        imagesWithSpotData.forEach(img => imagesSet.add(img));
        previewImages.forEach(img => imagesSet.add(img));
        if (typeof window !== 'undefined') {
            if (!window.__PreviewObserver__) {
                window.__PreviewObserver__ = new IntersectionObserver((entries) => {
                    entries.forEach(entry => { const el = entry.target; if (entry.isIntersecting) { window.__PreviewObserver__.unobserve(el); processImageForPreview(el); } });
                }, { root: null, rootMargin: '0px', threshold: 0 });
            }
        }
        imagesSet.forEach(img => {
            if (img.tagName !== 'IMG') return;
            const spotDataJson = img.getAttribute('data-spot-data');
            if (!spotDataJson || spotDataJson.length < 20) return;
            if (img.getAttribute('data-has-spot-data') !== 'true') { img.setAttribute('data-has-spot-data', 'true'); }
            const rect = img.getBoundingClientRect();
            const visible = rect.width > 0 && rect.height > 0 && rect.bottom >= 0 && rect.right >= 0 && rect.top <= (window.innerHeight || 0) && rect.left <= (window.innerWidth || 0);
            if (visible) { processImageForPreview(img); } else if (typeof window !== 'undefined' && window.__PreviewObserver__) { window.__PreviewObserver__.observe(img); }
        });
    };
    debounce(scan);
}

/**
 * Setup event listeners for Livewire and custom events
 */
function setupEventListeners() {
    if (typeof window !== 'undefined') {
        if (window.__PREVIEW_EVT_BOUND__) return;
        window.__PREVIEW_EVT_BOUND__ = true;
    }
    // Re-render on Livewire update
    const reinit = () => initPreviewRenderer();
    ['livewire:update','livewire:updated','livewire:load','livewire:message.processed'].forEach(eventName => { document.addEventListener(eventName, () => reinit()); });

    // Listen for custom image-edited event
    document.addEventListener('image-edited', function() {
        const customDelays = [200, 500, 800];
        customDelays.forEach(delay => {
            setTimeout(() => initPreviewRenderer(), delay);
        });
    });

    // Also listen for Livewire browser event 'image-updated'
    document.addEventListener('image-updated', function() {
        setTimeout(() => initPreviewRenderer(), 200);
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

