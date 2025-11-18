/**
 * Image Editor - Effects Operations
 * Image filters and adjustments (brightness, contrast, saturation, etc.)
 */

export function createEffectsMethods() {
  return {
    /**
     * Build CSS filter string from effect properties
     */
    buildFilterString() {
      const filters = [];
      // Combine brightness and exposure (exposure affects brightness, so we multiply them)
      // Exposure is typically -100 to +100, convert to brightness multiplier
      let effectiveBrightness = this.brightness;
      if (this.exposure !== undefined && this.exposure !== 0) {
        // Exposure: -100 to +100, convert to brightness multiplier (0.5x to 2x)
        const exposureMultiplier = 1 + (this.exposure / 100);
        effectiveBrightness = Math.max(0, Math.min(200, this.brightness * exposureMultiplier));
      }
      if (effectiveBrightness !== 100) filters.push(`brightness(${effectiveBrightness}%)`);
      if (this.contrast !== 100) filters.push(`contrast(${this.contrast}%)`);
      if (this.saturation !== 100) filters.push(`saturate(${this.saturation}%)`);
      if (this.hue !== 0) filters.push(`hue-rotate(${this.hue}deg)`);
      if (this.blur > 0) filters.push(`blur(${this.blur}px)`);
      return filters.length > 0 ? filters.join(' ') : 'none';
    },

    /**
     * Adjust filter value
     * IMPORTANT: Value comes as string from input, convert to number
     */
    adjustFilter(type, value) {
      // Convert value to number (input range returns string)
      const numValue = parseFloat(value) || 0;

      if (type === 'brightness') this.brightness = numValue;
      else if (type === 'contrast') this.contrast = numValue;
      else if (type === 'saturation') this.saturation = numValue;
      else if (type === 'hue') this.hue = numValue;
      else if (type === 'exposure') this.exposure = numValue;
      else if (type === 'gamma') this.gamma = numValue;
      else if (type === 'blur') this.blur = numValue;
      else if (type === 'sharpen') this.sharpen = numValue;

      // Force immediate redraw
      this.draw();
    },

    /**
     * Reset all filters to default values
     */
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
  };
}

