/**
 * Media Factory
 * Creates standardized v8 media objects for rich question content.
 */

import { MEDIA_TYPES } from '../schema.js'
import { generateShortId } from '../utils/generateId.js'

/**
 * Create a v8 media object
 * @param {Object} params
 * @param {string} [params.id] — Unique media ID (auto-generated if not provided)
 * @param {'image'|'audio'|'video'|'link'|'embed'} params.type — Media type
 * @param {string} params.url — URL or path to media
 * @param {string} [params.alt] — Alt text for accessibility
 * @param {string} [params.placement='prompt'] — Where to display: 'prompt'|'option'|'explanation'|'hint'|'stimulus'
 * @param {number} [params.width] — Display width
 * @param {number} [params.height] — Display height
 * @param {string} [params.caption] — Image caption
 * @param {string} [params.thumbnail] — Thumbnail URL for images/videos
 * @param {boolean} [params.autoplay] — Auto-play audio/video
 * @param {boolean} [params.controls=true] — Show media controls
 * @param {boolean} [params.loop] — Loop playback
 * @param {boolean} [params.muted] — Start muted
 * @param {number} [params.duration] — Duration in seconds
 * @param {string} [params.transcript] — URL to transcript/captions
 * @param {string} [params.title] — Link title
 * @param {'_blank'|'_self'} [params.target='_blank'] — Link target
 * @param {string} [params.embed_code] — HTML embed code
 * @param {'youtube'|'vimeo'|'custom'} [params.provider] — Embed provider
 * @param {string} [params.mime_type] — MIME type
 * @param {number} [params.file_size] — File size in bytes
 * @param {Object} [params.metadata] — Additional metadata
 * @returns {Object} v8 media object
 */
export function createMedia({
  id = null,
  type,
  url,
  alt = '',
  placement = 'prompt',
  width,
  height,
  caption,
  thumbnail,
  autoplay = false,
  controls = true,
  loop = false,
  muted = false,
  duration,
  transcript,
  title,
  target = '_blank',
  embed_code,
  provider,
  mime_type,
  file_size,
  metadata,
} = {}) {
  if (!type || !Object.values(MEDIA_TYPES).includes(type)) {
    throw new Error(`Invalid media type: ${type}`)
  }
  if (!url) {
    throw new Error('Media URL is required')
  }

  const media = {
    id: id || generateShortId('media'),
    type,
    url: String(url).trim(),
  }

  // Accessibility
  if (alt) media.alt = String(alt).trim()

  // Placement
  const validPlacements = ['prompt', 'option', 'explanation', 'hint', 'stimulus']
  if (placement && validPlacements.includes(placement)) {
    media.placement = placement
  }

  // Image-specific
  if (type === MEDIA_TYPES.IMAGE) {
    if (width) media.width = Number(width)
    if (height) media.height = Number(height)
    if (caption) media.caption = String(caption).trim()
    if (thumbnail) media.thumbnail = String(thumbnail).trim()
  }

  // Audio/Video-specific
  if (type === MEDIA_TYPES.AUDIO || type === MEDIA_TYPES.VIDEO) {
    if (autoplay) media.autoplay = Boolean(autoplay)
    if (controls !== true) media.controls = Boolean(controls)
    if (loop) media.loop = Boolean(loop)
    if (muted) media.muted = Boolean(muted)
    if (duration) media.duration = Number(duration)
    if (transcript) media.transcript = String(transcript).trim()
  }

  // Video-only dimensions
  if (type === MEDIA_TYPES.VIDEO) {
    if (width) media.width = Number(width)
    if (height) media.height = Number(height)
  }

  // Link-specific
  if (type === MEDIA_TYPES.LINK) {
    if (title) media.title = String(title).trim()
    if (target !== '_blank') media.target = target
  }

  // Embed-specific
  if (type === MEDIA_TYPES.EMBED) {
    if (embed_code) media.embed_code = String(embed_code).trim()
    if (provider) media.provider = provider
  }

  // Common
  if (mime_type) media.mime_type = String(mime_type)
  if (file_size) media.file_size = Number(file_size)
  if (metadata && Object.keys(metadata).length > 0) media.metadata = metadata

  return media
}
