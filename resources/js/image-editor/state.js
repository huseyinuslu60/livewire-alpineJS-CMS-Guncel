/**
 * Image Editor - State Management
 * All state variables and initial state definitions
 */

/**
 * Image Key State Management
 * Centralized state for all images (existing and temp)
 * imageKey format: "existing:<fileId>" or "temp:<index>"
 */
const imageState = {
  currentImageKey: null,
  images: {}, // [imageKey]: { type, fileId, index, url, spotData }
};

/**
 * Register an image in the state
 * @param {object} config - Image configuration
 * @param {string} config.imageKey - Unique image key (existing:<fileId> or temp:<index>)
 * @param {string} config.type - 'existing' or 'temp'
 * @param {string|number|null} config.fileId - File ID (for existing images)
 * @param {number|null} config.index - Index (for temp images)
 * @param {string} config.url - Image URL
 * @param {object|null} config.spotData - Spot data (crop/effect/text)
 */
export function registerImage(config) {
  if (!config.imageKey) {
    console.error('Image State - registerImage: imageKey is required');
    return;
  }

  imageState.images[config.imageKey] = {
    imageKey: config.imageKey,
    type: config.type || (config.imageKey.startsWith('existing:') ? 'existing' : 'temp'),
    fileId: config.fileId || null,
    index: config.index !== undefined ? config.index : null,
    url: config.url || '',
    spotData: config.spotData || null,
  };

  // Image registered
}

/**
 * Set current image key
 * @param {string} imageKey - Image key to set as current
 */
export function setCurrentImage(imageKey) {
  if (imageKey && !imageState.images[imageKey]) {
    console.warn('Image State - setCurrentImage: Image not registered:', imageKey);
  }
  imageState.currentImageKey = imageKey;
}

/**
 * Update spot data for an image
 * @param {string} imageKey - Image key
 * @param {object|null} spotData - Spot data to update
 */
export function updateImageSpotData(imageKey, spotData) {
  if (!imageState.images[imageKey]) {
    console.warn('Image State - updateImageSpotData: Image not registered:', imageKey);
    return;
  }

  imageState.images[imageKey].spotData = spotData;
  console.log('Image State - Updated spotData for:', imageKey);
}

/**
 * Get image configuration
 * @param {string} imageKey - Image key
 * @returns {object|null} Image configuration or null
 */
export function getImageConfig(imageKey) {
  return imageState.images[imageKey] || null;
}

/**
 * Get current image configuration
 * @returns {object|null} Current image configuration or null
 */
export function getCurrentImageConfig() {
  return imageState.currentImageKey ? getImageConfig(imageState.currentImageKey) : null;
}

/**
 * Parse imageKey to extract type, fileId, or index
 * @param {string} imageKey - Image key
 * @returns {object} Parsed image key data
 */
export function parseImageKey(imageKey) {
  if (!imageKey) {
    return { type: null, fileId: null, index: null };
  }

  if (imageKey.startsWith('existing:')) {
    const fileId = imageKey.replace('existing:', '');
    return { type: 'existing', fileId, index: null };
  } else if (imageKey.startsWith('temp:')) {
    const index = parseInt(imageKey.replace('temp:', ''), 10);
    return { type: 'temp', fileId: null, index: isNaN(index) ? null : index };
  }

  // Fallback: try to determine from format
  const numKey = parseInt(imageKey, 10);
  if (!isNaN(numKey) && numKey.toString() === imageKey && imageKey.length <= 3) {
    // Small number = temp index
    return { type: 'temp', fileId: null, index: numKey };
  } else {
    // Assume existing fileId
    return { type: 'existing', fileId: imageKey, index: null };
  }
}

export function createInitialState() {
  return {
    // Editor State
    isOpen: false,
    currentIndex: null,
    currentFileId: null,
    currentImageKey: null, // NEW: Image key for current editing session
    imageUrl: null,
    spotData: null, // Store spot_data for loading saved edits
    savedTextObjects: [], // Store textObjects from spot_data before scaling
    savedCanvasWidth: 0, // Store canvas width when textObjects were saved
    savedCanvasHeight: 0, // Store canvas height when textObjects were saved

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
    activeTool: 'select', // select, crop, eraser, text, pan
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

    // Crop data for desktop and mobile
    desktopCrop: [],
    mobileCrop: [],
    desktopFocus: 'center',
    mobileFocus: 'center',

    // Original image dimensions (before any crop operations)
    originalImageWidth: 0,
    originalImageHeight: 0,
    originalImagePath: null, // Store original image path for spot_data

    // Image metadata
    imageMeta: {},
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

    // Transform
    isTransforming: false,
    transformHandle: null,
    transformStartX: 0,
    transformStartY: 0,

    // Selection
    selection: null,
    isSelecting: false,

    // UI State
    showLayersPanel: false,
    showHistoryPanel: false,
    showPropertiesPanel: true,
    showToolbar: true,
    showZoomSlider: false,

    // Keyboard shortcuts
    shortcuts: {},
  };
}

// Export imageState (functions are already exported above)
export { imageState };

