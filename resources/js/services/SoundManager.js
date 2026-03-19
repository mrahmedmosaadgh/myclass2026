/**
 * Sound Manager for Presentation System
 * Preloads sound effects once and provides methods to play them
 */

class SoundManager {
  constructor() {
    this.sounds = {};
    this.initialized = false;
  }

  /**
   * Initialize and preload all sounds
   * Call this once when the app loads
   */
  initialize() {
    if (this.initialized) {
      return;
    }

    // Define sounds to preload
    const soundFiles = {
      click: '/audio/click-234708.mp3',
      error: '/audio/error-010-206498.mp3',
      success: '/audio/purchase-success-384963.mp3'
    };

    // Preload each sound
    Object.entries(soundFiles).forEach(([name, path]) => {
      this.sounds[name] = new Audio(path);
      this.sounds[name].preload = 'auto';
      this.sounds[name].load();
      
      // Set default volume for click sounds
      if (name === 'click') {
        this.sounds[name].volume = 0.5; // 50% volume for click
      }
    });

    this.initialized = true;
    console.log('Sound Manager initialized with', Object.keys(this.sounds).length, 'sounds');
  }

  /**
   * Play a sound by name
   * @param {string} soundName - Name of the sound to play ('click', 'error', 'success')
   * @param {number} volume - Optional volume override (0.0 to 1.0)
   */
  play(soundName, volume = null) {
    if (!this.initialized) {
      console.warn('Sound Manager not initialized. Call initialize() first.');
      return;
    }

    const sound = this.sounds[soundName];
    if (!sound) {
      console.warn(`Sound "${soundName}" not found`);
      return;
    }

    try {
      // Instead of cloning, reset and reuse the same audio instance
      // This prevents the browser from re-fetching the file
      // Note: This means sounds can't overlap (rapid clicks will restart the sound)
      sound.currentTime = 0;
      
      if (volume !== null) {
        sound.volume = Math.max(0, Math.min(1, volume));
      }

      // Play the sound
      sound.play().catch(error => {
        // Silently handle autoplay policy errors
        if (error.name !== 'NotAllowedError') {
          console.warn('Failed to play sound:', error);
        }
      });
    } catch (error) {
      console.warn('Error playing sound:', error);
    }
  }

  /**
   * Play a sound by cloning (allows overlapping playback)
   * Use this only when you need overlapping sounds, but be aware it may cause re-fetching
   * @param {string} soundName - Name of the sound to play
   * @param {number} volume - Optional volume override (0.0 to 1.0)
   */
  playWithOverlap(soundName, volume = null) {
    if (!this.initialized) {
      console.warn('Sound Manager not initialized. Call initialize() first.');
      return;
    }

    const sound = this.sounds[soundName];
    if (!sound) {
      console.warn(`Sound "${soundName}" not found`);
      return;
    }

    try {
      // Clone the sound to allow overlapping playback
      // Note: This may cause the browser to re-fetch the audio file
      const soundClone = sound.cloneNode();
      
      if (volume !== null) {
        soundClone.volume = Math.max(0, Math.min(1, volume));
      }

      // Play the cloned sound
      soundClone.play().catch(error => {
        // Silently handle autoplay policy errors
        if (error.name !== 'NotAllowedError') {
          console.warn('Failed to play sound:', error);
        }
      });
    } catch (error) {
      console.warn('Error playing sound:', error);
    }
  }

  /**
   * Play click sound specifically
   */
  playClick(volume = 0.5) {
    this.play('click', volume);
  }

  /**
   * Set volume for a specific sound
   * @param {string} soundName - Name of the sound
   * @param {number} volume - Volume (0.0 to 1.0)
   */
  setVolume(soundName, volume) {
    if (this.sounds[soundName]) {
      this.sounds[soundName].volume = Math.max(0, Math.min(1, volume));
    }
  }

  /**
   * Check if sound manager is ready
   */
  isReady() {
    return this.initialized;
  }
}

// Export singleton instance
export const soundManager = new SoundManager();
