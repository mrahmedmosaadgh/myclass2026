/**
 * QR Payload Codec — Robust encode/decode with checksum validation.
 *
 * Format: {groupId}_{optionId}_{checksum}
 *   groupId    : alphanumeric, case-insensitive (e.g. g1, teamA, group_01)
 *   optionId   : single letter A–F (case-insensitive)
 *   checksum   : 2-char base36 hash of groupId+optionId
 *
 * Backward-compatible: if no checksum segment is present the payload is
 * still accepted but marked as `checksumValidated: false`.
 */

function generateChecksum(groupId, optionId) {
  const str = String(groupId) + String(optionId).toUpperCase()
  let hash = 0
  for (let i = 0; i < str.length; i++) {
    hash = ((hash << 5) - hash) + str.charCodeAt(i)
    hash |= 0 // 32-bit signed integer
  }
  return Math.abs(hash).toString(36).slice(0, 2).padStart(2, '0')
}

export function useQrCodec() {
  /**
   * Encode a group ID + option ID into a QR payload string.
   */
  function encodePayload(groupId, optionId) {
    const g = String(groupId).trim()
    const o = String(optionId).toUpperCase().trim()
    const checksum = generateChecksum(g, o)
    return `${g}_${o.toLowerCase()}_${checksum}`
  }

  /**
   * Decode a raw scanned string.
   *
   * Returns:
   *   { ok: true,  groupId, optionId, checksumValidated: boolean }
   *   { ok: false, error: string, raw }
   */
  function decodePayload(raw) {
    const input = String(raw || '').trim()
    if (!input) {
      return { ok: false, error: 'Empty payload', raw: input }
    }

    const segments = input.split('_')

    // Legacy fallback: g1_a (no checksum)
    if (segments.length === 2) {
      const groupId = segments[0]
      const optionId = String(segments[1]).toUpperCase()
      if (!/^[A-F]$/.test(optionId)) {
        return { ok: false, error: `Invalid option "${optionId}"`, raw: input }
      }
      return {
        ok: true,
        groupId,
        optionId,
        checksumValidated: false
      }
    }

    // Standard format: g1_a_4k
    if (segments.length >= 3) {
      const groupId = segments[0]
      const optionId = String(segments[1]).toUpperCase()
      const providedChecksum = segments[2]

      if (!/^[A-F]$/.test(optionId)) {
        return { ok: false, error: `Invalid option "${optionId}"`, raw: input }
      }

      const expected = generateChecksum(groupId, optionId)
      const checksumValid = providedChecksum === expected

      if (!checksumValid) {
        return {
          ok: false,
          error: `Checksum mismatch (expected ${expected}, got ${providedChecksum})`,
          raw: input,
          groupId,
          optionId
        }
      }

      return {
        ok: true,
        groupId,
        optionId,
        checksumValidated: true
      }
    }

    // Single segment: try to match as groupId only (e.g. "g1")
    if (segments.length === 1) {
      return {
        ok: true,
        groupId: segments[0],
        optionId: null,
        checksumValidated: false
      }
    }

    return { ok: false, error: 'Unrecognized payload format', raw: input }
  }

  /**
   * Convenience: encode all option payloads for a single group.
   * Returns an array of { optionId, payload } objects.
   */
  function encodeGroupOptions(groupId, optionIds = ['A', 'B', 'C', 'D']) {
    return optionIds.map(optId => ({
      optionId: optId,
      payload: encodePayload(groupId, optId)
    }))
  }

  return {
    encodePayload,
    decodePayload,
    encodeGroupOptions
  }
}
