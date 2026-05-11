/**
 * ID Generation Utilities
 * Stable, readable IDs for v8 question components.
 */

let counter = 0
const timestamp = Date.now()

/**
 * Generate a UUID-style unique ID
 * @param {string} [prefix='id'] — Optional prefix for readability
 * @returns {string} e.g., "q_abc123def456"
 */
export function generateUUID(prefix = 'id') {
  const time = Date.now()
  const random = Math.random().toString(36).substring(2, 8)
  const seq = (++counter).toString(36)
  return `${prefix}_${time.toString(36)}_${random}_${seq}`
}

/**
 * Generate a short, readable ID for options/media
 * @param {string} [prefix='item'] — Prefix
 * @returns {string} e.g., "opt_a1b2c3"
 */
export function generateShortId(prefix = 'item') {
  const random = Math.random().toString(36).substring(2, 7)
  return `${prefix}_${random}`
}

/**
 * Generate sequential letter IDs for options (a, b, c, ...)
 * @param {number} index — Zero-based index
 * @returns {string} e.g., "a", "b", "c" ... "aa", "ab"
 */
export function generateLetterId(index) {
  if (index < 26) {
    return String.fromCharCode(97 + index) // a-z
  }
  // For >26: aa, ab, ac, ...
  const first = Math.floor(index / 26) - 1
  const second = index % 26
  return String.fromCharCode(97 + first, 97 + second)
}

/**
 * Generate stable IDs from text content (deterministic)
 * @param {string} text — Content to hash
 * @param {string} [prefix='id'] — ID prefix
 * @returns {string} Stable ID based on content
 */
export function generateStableId(text, prefix = 'id') {
  let hash = 0
  const str = String(text)
  for (let i = 0; i < str.length; i++) {
    const char = str.charCodeAt(i)
    hash = ((hash << 5) - hash) + char
    hash = hash & hash // Convert to 32bit integer
  }
  const absHash = Math.abs(hash).toString(36)
  return `${prefix}_${absHash}`
}
