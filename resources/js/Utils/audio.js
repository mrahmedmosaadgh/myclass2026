const sounds = {
    click: '/audio/click-234708.mp3',
    success: '/audio/purchase-success-384963.mp3',
    error: '/audio/error-010-206498.mp3',
};

const audioMap = new Map();

// Preload sounds
if (typeof window !== 'undefined') {
    for (const [key, src] of Object.entries(sounds)) {
        const audio = new Audio(src);
        audio.preload = 'auto';
        audioMap.set(key, audio);
    }
}

export const playSound = (type) => {
    const baseAudio = audioMap.get(type);
    if (!baseAudio) return;

    // Use cloneNode to allow overlapping sounds without network overhead
    // (Browsers typically cache the media resource for clones)
    // If strict single-instance is required to verify network silence, we can reuse baseAudio.
    // However, cloning is better for UX (rapid clicks).
    // Let's try reusing baseAudio first if user is very sensitive to network, 
    // but cloning is usually the standard way to fix "cut off" sounds.
    // Given the user specifically said "network... load again", reusing the object is the safest bet to prove it's loaded once.

    // Approach: Reuse single instance, reset time. 
    // This strictly prevents multiple requests/instances.

    baseAudio.currentTime = 0;
    baseAudio.volume = type === 'click' ? 0.5 : 1.0;
    baseAudio.play().catch(e => console.debug('Audio play failed', e));
};

export const playClick = () => playSound('click');
export const playHover = () => { }; // Disabled per request
export const playSuccess = () => playSound('success');
export const playError = () => playSound('error');
