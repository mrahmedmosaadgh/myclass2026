/**
 * Utility functions for line plot data processing
 */

/**
 * Normalize line plot data to standard format
 * @param {Object} data - Raw data object
 * @returns {Object} Normalized data object
 */
export function normalizeLinePlotData(data) {
  // Handle different data formats
  if (data.raw && Array.isArray(data.raw)) {
    return {
      counts: calculateCounts(data.raw),
      min: Math.min(...data.raw),
      max: Math.max(...data.raw),
      step: 1
    };
  } else if (data.counts && typeof data.counts === 'object') {
    const keys = Object.keys(data.counts).map(Number);
    return {
      counts: data.counts,
      min: Math.min(...keys),
      max: Math.max(...keys),
      step: 1
    };
  } else {
    // Default empty data
    return {
      counts: {},
      min: 0,
      max: 10,
      step: 1
    };
  }
}

/**
 * Calculate counts from raw data array
 * @param {Array<number>} rawData - Array of raw data points
 * @returns {Object} Object with counts per value
 */
export function calculateCounts(rawData) {
  if (!Array.isArray(rawData) || rawData.length === 0) {
    return {};
  }

  const counts = {};
  rawData.forEach(value => {
    const key = String(value);
    counts[key] = (counts[key] || 0) + 1;
  });

  return counts;
}

/**
 * Validate line plot data structure
 * @param {Object} data - Data to validate
 * @returns {boolean} True if valid, false otherwise
 */
export function validateLinePlotData(data) {
  if (!data || typeof data !== 'object') {
    return false;
  }

  // Check for required properties
  if (data.counts && typeof data.counts === 'object') {
    return true;
  }

  if (data.raw && Array.isArray(data.raw)) {
    return true;
  }

  return false;
}

/**
 * Generate labels for number line
 * @param {number} min - Minimum value
 * @param {number} max - Maximum value
 * @param {number} step - Step size
 * @returns {Array<string>} Array of label strings
 */
export function generateLabels(min, max, step) {
  const labels = [];
  for (let i = min; i <= max; i += step) {
    labels.push(i.toString());
  }
  return labels;
}

/**
 * Get range information for data
 * @param {Object} data - Line plot data
 * @returns {Object} Range information
 */
export function getRangeInfo(data) {
  const normalized = normalizeLinePlotData(data);
  return {
    min: normalized.min,
    max: normalized.max,
    range: normalized.max - normalized.min,
    count: Object.values(normalized.counts).reduce((sum, count) => sum + count, 0)
  };
}