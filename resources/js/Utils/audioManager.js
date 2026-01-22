/**
 * Audio Manager - Lazy load sound effects
 * Manages audio playback with on-demand loading and caching
 */

const audioCache = new Map()

/**
 * Play a sound effect
 * @param {string} soundKey - The key identifying the sound
 * @param {Object} soundFiles - Map of sound keys to file paths
 * @returns {Promise<void>}
 */
export async function playSound(soundKey, soundFiles) {
    if (!soundFiles[soundKey]) {
        console.warn(`Sound key "${soundKey}" not found in soundFiles`)
        return
    }

    // Load and cache the audio if not already cached
    if (!audioCache.has(soundKey)) {
        const audio = new Audio(soundFiles[soundKey])
        await new Promise((resolve, reject) => {
            audio.addEventListener('canplaythrough', resolve, { once: true })
            audio.addEventListener('error', reject, { once: true })
            audio.load()
        }).catch(e => {
            console.warn(`Failed to load sound "${soundKey}":`, e)
            return
        })
        audioCache.set(soundKey, audio)
    }

    const audio = audioCache.get(soundKey)
    if (audio) {
        audio.currentTime = 0
        return audio.play().catch(e => console.log('Sound play error:', e))
    }
}

/**
 * Preload multiple sound effects
 * Useful for preloading sounds during idle time
 * @param {Object} soundFiles - Map of sound keys to file paths
 * @returns {Promise<void>}
 */
export async function preloadSounds(soundFiles) {
    const promises = Object.entries(soundFiles).map(([key, src]) => {
        if (audioCache.has(key)) return Promise.resolve()

        return new Promise((resolve) => {
            const audio = new Audio(src)
            audio.addEventListener('canplaythrough', () => {
                audioCache.set(key, audio)
                resolve()
            }, { once: true })
            audio.addEventListener('error', () => {
                console.warn(`Failed to preload sound "${key}"`)
                resolve() // Resolve anyway to not block other sounds
            }, { once: true })
            audio.load()
        })
    })

    await Promise.all(promises)
}

/**
 * Clear the audio cache
 * Useful for memory management
 */
export function clearAudioCache() {
    audioCache.clear()
}

/**
 * Check if a sound is cached
 * @param {string} soundKey - The key identifying the sound
 * @returns {boolean}
 */
export function isSoundCached(soundKey) {
    return audioCache.has(soundKey)
}

/**
 * Preload sounds when browser is idle
 * @param {Object} soundFiles - Map of sound keys to file paths
 */
export function preloadSoundsWhenIdle(soundFiles) {
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => preloadSounds(soundFiles), { timeout: 2000 })
    } else {
        // Fallback for browsers without requestIdleCallback
        setTimeout(() => preloadSounds(soundFiles), 1000)
    }
}
