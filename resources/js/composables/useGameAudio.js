import { ref, onMounted, onUnmounted } from 'vue';

// Shared audio cache across ALL instances (singleton pattern)
const SHARED_AUDIO_CACHE = new Map();
const SHARED_BACKGROUND_TRACKS = {};

export function useGameAudio() {
  const isMuted = ref(false);
  const volume = ref(0.5);
  const backgroundMusicPlaying = ref(false);
  
  // Audio cache
  const audioCache = new Map();
  let backgroundMusic = null;
  
  // Sound effect paths
  const soundEffects = {
    click: '/audio/click/mixkit-gear-fast-lock-tap-2857.wav',
    correct: '/audio/purchase-success-384963.mp3',
    wrong: '/audio/error-010-206498.mp3',
    combo: '/audio/purchase-success-384963.mp3',
    powerUp: '/audio/purchase-success-384963.mp3',
    levelUp: '/audio/purchase-success-384963.mp3',
    gameOver: '/audio/error-010-206498.mp3',
    timeWarning: '/audio/click/mixkit-gear-fast-lock-tap-2857.wav',
  };
  
  // Background music paths
  const backgroundTracks = {
    falling: '/audio/background_music1.mp3',
    orbiting: '/audio/background_music2.mp3',
    space: '/audio/timer/ticking-clock_1-27477.mp3',
  };
  
  const loadSound = (soundName) => {
    if (!SHARED_AUDIO_CACHE.has(soundName)) {
      const audio = new Audio(soundEffects[soundName]);
      audio.preload = 'auto';
      
      // Force actual loading by setting src and waiting for canplaythrough
      audio.src = soundEffects[soundName];
      audio.load();
      
      // Wait for audio to be ready
      audio.addEventListener('canplaythrough', () => {
        console.log(`✅ Audio ready: ${soundName}`);
      }, { once: true });
      
      audio.addEventListener('error', (e) => {
        console.error(`❌ Audio load error: ${soundName}`, e);
      }, { once: true });
      
      SHARED_AUDIO_CACHE.set(soundName, audio);
      console.log(`📦 Audio cached: ${soundName}`);
    }
  };
  
  const playSound = (soundName, overrideVolume = null) => {
    if (isMuted.value) return null;
    
    let audio = SHARED_AUDIO_CACHE.get(soundName);
    
    if (!audio) {
      loadSound(soundName);
      audio = SHARED_AUDIO_CACHE.get(soundName);
    }
    
    if (audio) {
      // Clone for simultaneous playback
      const audioClone = audio.cloneNode();
      audioClone.volume = overrideVolume || volume.value;
      
      audioClone.play().catch(error => {
        console.warn('Audio playback failed:', error);
      });
      
      // Clean up after playback
      audioClone.addEventListener('ended', () => {
        audioClone.remove();
      });
      
      return audioClone;
    }
    
    return null;
  };
  
  const playCorrectSound = () => {
    playSound('correct');
  };
  
  const playWrongSound = () => {
    playSound('wrong');
  };
  
  const playComboSound = () => {
    playSound('combo');
  };
  
  const playPowerUpSound = () => {
    playSound('powerUp');
  };
  
  const playLevelUpSound = () => {
    playSound('levelUp');
  };
  
  const playGameOverSound = () => {
    playSound('gameOver');
  };
  
  const playTimeWarningSound = () => {
    playSound('timeWarning');
  };
  
  const startBackgroundMusic = (trackName = 'falling') => {
    stopBackgroundMusic();
    
    const trackPath = backgroundTracks[trackName];
    if (!trackPath) return;
    
    // Check if we already have this track cached
    if (!SHARED_BACKGROUND_TRACKS[trackName]) {
      console.log(`🎵 Loading background music: ${trackName}`);
      const bgMusic = new Audio(trackPath);
      bgMusic.loop = true;
      bgMusic.preload = 'auto';
      bgMusic.src = trackPath;
      bgMusic.load();
      
      // Cache it for reuse
      SHARED_BACKGROUND_TRACKS[trackName] = bgMusic;
      
      bgMusic.addEventListener('canplaythrough', () => {
        console.log(`✅ Background music ready: ${trackName}`);
      }, { once: true });
    }
    
    backgroundMusic = SHARED_BACKGROUND_TRACKS[trackName];
    backgroundMusic.volume = volume.value * 0.3; // Lower volume for background
    
    // Handle audio loading errors gracefully
    backgroundMusic.addEventListener('error', (error) => {
      console.warn(`Background audio '${trackName}' not found or failed to load:`, error);
      backgroundMusicPlaying.value = false;
    });
    
    backgroundMusic.play().then(() => {
      backgroundMusicPlaying.value = true;
    }).catch(error => {
      console.warn('Background music playback failed:', error);
      backgroundMusicPlaying.value = false;
    });
  };
  
  const stopBackgroundMusic = () => {
    if (backgroundMusic) {
      backgroundMusic.pause();
      backgroundMusic.currentTime = 0;
      backgroundMusic = null;
      backgroundMusicPlaying.value = false;
    }
  };
  
  const pauseBackgroundMusic = () => {
    if (backgroundMusic) {
      backgroundMusic.pause();
      backgroundMusicPlaying.value = false;
    }
  };
  
  const resumeBackgroundMusic = () => {
    if (backgroundMusic) {
      backgroundMusic.play().then(() => {
        backgroundMusicPlaying.value = true;
      });
    }
  };
  
  const setVolume = (newVolume) => {
    volume.value = Math.max(0, Math.min(1, newVolume));
    
    if (backgroundMusic) {
      backgroundMusic.volume = volume.value * 0.3;
    }
  };
  
  const toggleMute = () => {
    isMuted.value = !isMuted.value;
    
    if (isMuted.value && backgroundMusic) {
      backgroundMusic.pause();
    } else if (!isMuted.value && backgroundMusicPlaying.value) {
      backgroundMusic.play();
    }
  };
  
  const preloadAllSounds = () => {
    console.log('🎵 Starting audio preloading...');
    const audioFiles = Object.keys(soundEffects);
    console.log(`📚 Total audio files to preload: ${audioFiles.length}`);
    
    let loadedCount = 0;
    
    audioFiles.forEach((key, index) => {
      loadSound(key);
      
      // Track loading progress
      const audio = SHARED_AUDIO_CACHE.get(key);
      if (audio) {
        audio.addEventListener('canplaythrough', () => {
          loadedCount++;
          console.log(` Progress: ${loadedCount}/${audioFiles.length} audio files loaded`);
          
          if (loadedCount === audioFiles.length) {
            console.log('✅ All audio files preloaded successfully!');
          }
        }, { once: true });
      }
    });
  };
  
  onMounted(() => {
    preloadAllSounds();
  });
  
  onUnmounted(() => {
    stopBackgroundMusic();
    // Don't clear shared cache - keep audio loaded for reuse
  });
  
  return {
    // State
    isMuted,
    volume,
    backgroundMusicPlaying,
    
    // Methods
    playSound,
    playCorrectSound,
    playWrongSound,
    playComboSound,
    playPowerUpSound,
    playLevelUpSound,
    playGameOverSound,
    playTimeWarningSound,
    startBackgroundMusic,
    stopBackgroundMusic,
    pauseBackgroundMusic,
    resumeBackgroundMusic,
    setVolume,
    toggleMute,
    preloadAllSounds,
  };
}
