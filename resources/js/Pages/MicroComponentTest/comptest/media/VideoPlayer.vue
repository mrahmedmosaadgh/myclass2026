<template>
  <div class="video-player-container">
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold mb-4">{{ title || 'Video Player' }}</h3>
      
      <!-- Video Element -->
      <div class="relative bg-black rounded-lg overflow-hidden mb-4">
        <!-- Video Player for video files -->
        <video
          v-if="mediaType === 'video'"
          ref="videoRef"
          :src="currentSrc"
          :controls="showControls"
          :autoplay="autoplay"
          :loop="loop"
          :muted="muted"
          class="w-full h-auto"
          @loadedmetadata="onLoadedMetadata"
          @timeupdate="onTimeUpdate"
          @ended="onEnded"
        >
          Your browser does not support the video tag.
        </video>
        
        <!-- Audio Player for audio files -->
        <div v-else-if="mediaType === 'audio'" class="bg-gray-900 rounded-lg p-8">
          <audio
            ref="videoRef"
            :src="currentSrc"
            :controls="showControls"
            :autoplay="autoplay"
            :loop="loop"
            :muted="muted"
            class="w-full"
            @loadedmetadata="onLoadedMetadata"
            @timeupdate="onTimeUpdate"
            @ended="onEnded"
          >
            Your browser does not support the audio tag.
          </audio>
          
          <!-- Audio Visualizer -->
          <div class="mt-4 flex items-center justify-center">
            <div class="flex space-x-1">
              <div v-for="i in 20" :key="i" 
                   class="w-1 bg-blue-500 rounded-full transition-all duration-150"
                   :style="{ height: Math.random() * 40 + 10 + 'px' }">
              </div>
            </div>
          </div>
        </div>
      </div>
        
        <!-- Audio Channel Indicator -->
        <div v-if="audioChannel !== 'stereo'" class="absolute top-2 right-2 bg-black/70 text-white px-2 py-1 rounded text-xs">
          {{ audioChannel === 'left' ? '🎧 Left Only' : '🎧 Right Only' }}
        </div>
        
        <!-- Custom Overlay Controls (if showControls is false) -->
        <div v-if="!showControls" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
          <div class="flex items-center gap-3">
            <button 
              @click="togglePlay"
              class="text-white hover:text-blue-400 transition-colors"
            >
              <span v-if="isPlaying" class="text-xl">⏸️</span>
              <span v-else class="text-xl">▶️</span>
            </button>
            
            <div class="flex-1">
              <div class="bg-white/30 rounded-full h-1 relative">
                <div 
                  class="bg-blue-500 h-full rounded-full transition-all"
                  :style="{ width: progressPercentage + '%' }"
                ></div>
              </div>
            </div>
            
            <span class="text-white text-sm">
              {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
            </span>
          </div>
        </div>
      </div>
      
      <!-- Video Info -->
      <div v-if="description" class="text-sm text-gray-600 mb-4">
        {{ description }}
      </div>
      
      <!-- Countdown Timer Display -->
      <div v-if="currentRepeatMode === 'time' && isPlaying" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-yellow-800">Time Remaining:</span>
          <span class="text-lg font-bold text-yellow-900">{{ formatCountdown(remainingTime) }}</span>
        </div>
        <div class="mt-2">
          <div class="w-full bg-yellow-200 rounded-full h-2">
            <div 
              class="bg-yellow-500 h-2 rounded-full transition-all duration-100"
              :style="{ width: timeProgressPercentage + '%' }"
            ></div>
          </div>
        </div>
      </div>
      
      <!-- Repeat Counter Display -->
      <div v-if="currentRepeatMode === 'count' && (isPlaying || actualRepeatCount > 0)" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-blue-800">Repeat Progress:</span>
          <span class="text-lg font-bold text-blue-900">{{ actualRepeatCount + 1 }} / {{ currentRepeatCount }}</span>
        </div>
        <div class="mt-2">
          <div class="w-full bg-blue-200 rounded-full h-2">
            <div 
              class="bg-blue-500 h-2 rounded-full transition-all duration-300"
              :style="{ width: repeatProgressPercentage + '%' }"
            ></div>
          </div>
          <div class="mt-1 text-xs text-blue-600">
            {{ currentRepeatCount - actualRepeatCount }} repeats remaining
          </div>
        </div>
      </div>
      
      <!-- Local File Input -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Load Local Media File:</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Video Files:</label>
            <input
              type="file"
              accept="video/*"
              @change="onFileSelected"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Audio Files:</label>
            <input
              type="file"
              accept="audio/*"
              @change="onAudioFileSelected"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
            />
          </div>
        </div>
        <div v-if="localFileName" class="mt-2 text-xs text-gray-600">
          Loaded: {{ localFileName }}
        </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex flex-wrap gap-2">
        <button
          @click="togglePlay"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
        >
          {{ isPlaying ? 'Pause' : 'Play' }}
        </button>
        
        <button
          @click="restart"
          class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors"
        >
          Restart
        </button>
        
        <button
          @click="toggleMute"
          class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition-colors"
        >
          {{ isMuted ? 'Unmute' : 'Mute' }}
        </button>
        
        <!-- Audio Channel Controls -->
        <div class="flex gap-1">
          <button
            @click="setAudioChannel('left')"
            :class="['px-3 py-2 text-white rounded transition-colors', 
                     audioChannel === 'left' ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-500 hover:bg-gray-600']"
          >
            🎧 L
          </button>
          
          <button
            @click="setAudioChannel('stereo')"
            :class="['px-3 py-2 text-white rounded transition-colors',
                     audioChannel === 'stereo' ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-500 hover:bg-gray-600']"
          >
            🔊 S
          </button>
          
          <button
            @click="setAudioChannel('right')"
            :class="['px-3 py-2 text-white rounded transition-colors',
                     audioChannel === 'right' ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-500 hover:bg-gray-600']"
          >
            🎧 R
          </button>
        </div>
        
        <!-- Repeat Controls -->
        <div class="flex gap-1">
          <button
            @click="setRepeatMode('none')"
            :class="['px-3 py-2 text-white rounded transition-colors text-xs',
                     currentRepeatMode === 'none' ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-500 hover:bg-gray-600']"
          >
            🔁 No
          </button>
          
          <button
            @click="setRepeatMode('count')"
            :class="['px-3 py-2 text-white rounded transition-colors text-xs',
                     currentRepeatMode === 'count' ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-500 hover:bg-gray-600']"
          >
            🔁 {{ currentRepeatCount }}x
          </button>
          
          <button
            @click="setRepeatMode('time')"
            :class="['px-3 py-2 text-white rounded transition-colors text-xs',
                     currentRepeatMode === 'time' ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-500 hover:bg-gray-600']"
          >
            🔁 {{ currentRepeatTime }}m
          </button>
        </div>
        
        <!-- Repeat Adjustment Controls -->
        <div class="flex gap-1" v-if="currentRepeatMode === 'count' || currentRepeatMode === 'time'">
          <button
            @click="adjustRepeat(-1)"
            class="px-2 py-1 bg-gray-600 text-white rounded hover:bg-gray-700 text-xs"
          >
            -
          </button>
          <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded text-xs min-w-[40px] text-center">
            {{ currentRepeatMode === 'count' ? currentRepeatCount : currentRepeatTime }}
          </span>
          <button
            @click="adjustRepeat(1)"
            class="px-2 py-1 bg-gray-600 text-white rounded hover:bg-gray-700 text-xs"
          >
            +
          </button>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
  src: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  showControls: {
    type: Boolean,
    default: true
  },
  autoplay: {
    type: Boolean,
    default: false
  },
  loop: {
    type: Boolean,
    default: false
  },
  muted: {
    type: Boolean,
    default: false
  },
  audioChannel: {
    type: String,
    default: 'stereo',
    validator: (value) => ['stereo', 'left', 'right'].includes(value)
  },
  repeatMode: {
    type: String,
    default: 'none',
    validator: (value) => ['none', 'count', 'time'].includes(value)
  },
  repeatCount: {
    type: Number,
    default: 0
  },
  repeatTime: {
    type: Number,
    default: 0 // in minutes
  },
  disableExternalControls: {
    type: Boolean,
    default: false
  }
});

const videoRef = ref(null);
const isPlaying = ref(false);
const currentTime = ref(0);
const duration = ref(0);
const isMuted = ref(props.muted);
const audioChannel = ref(props.audioChannel);
const disableExternalControls = ref(props.disableExternalControls);

// Local file handling
const localFileName = ref('');
const currentSrc = ref(props.src);
const mediaType = ref('video'); // 'video' or 'audio'

// Repeat functionality
const currentRepeatMode = ref(props.repeatMode);
const currentRepeatCount = ref(props.repeatCount);
const currentRepeatTime = ref(props.repeatTime);
const actualRepeatCount = ref(0);
const repeatStartTime = ref(0);
const repeatEndTime = ref(0);
const timeCheckInterval = ref(null);

// Web Audio API nodes
const audioContext = ref(null);
const panner = ref(null);

const progressPercentage = computed(() => {
  if (duration.value === 0) return 0;
  return (currentTime.value / duration.value) * 100;
});

// Countdown timer computed properties
const remainingTime = computed(() => {
  if (currentRepeatMode.value !== 'time' || !repeatStartTime.value) {
    return 0;
  }
  const elapsed = (Date.now() - repeatStartTime.value) / 1000; // in seconds
  const totalSeconds = currentRepeatTime.value * 60; // convert minutes to seconds
  return Math.max(0, totalSeconds - elapsed);
});

const timeProgressPercentage = computed(() => {
  if (currentRepeatMode.value !== 'time' || !repeatStartTime.value) {
    return 0;
  }
  const elapsed = (Date.now() - repeatStartTime.value) / 1000 / 60; // in minutes
  return Math.min(100, (elapsed / currentRepeatTime.value) * 100);
});

const repeatProgressPercentage = computed(() => {
  try {
    const mode = currentRepeatMode?.value;
    const totalCount = currentRepeatCount?.value;
    const completedCount = actualRepeatCount?.value;
    
    if (!mode || mode !== 'count' || !totalCount || totalCount === 0) {
      return 0;
    }
    
    const current = completedCount || 0;
    return Math.min(100, (current / totalCount) * 100);
  } catch (error) {
    console.warn('Error calculating repeat progress:', error);
    return 0;
  }
});

const onFileSelected = (event) => {
  const file = event.target.files[0];
  if (file && file.type.startsWith('video/')) {
    localFileName.value = file.name;
    currentSrc.value = URL.createObjectURL(file);
    mediaType.value = 'video';
    
    // Reset player state
    isPlaying.value = false;
    currentTime.value = 0;
    
    // Reinitialize audio context for new video
    audioContext.value = null;
    
    console.log('Local video loaded:', file.name);
  } else {
    console.error('Invalid file type. Please select a video file.');
  }
};

const onAudioFileSelected = (event) => {
  const file = event.target.files[0];
  if (file && file.type.startsWith('audio/')) {
    localFileName.value = file.name;
    currentSrc.value = URL.createObjectURL(file);
    mediaType.value = 'audio';
    
    // Reset player state
    isPlaying.value = false;
    currentTime.value = 0;
    
    // Reinitialize audio context for new audio
    audioContext.value = null;
    
    console.log('Local audio loaded:', file.name);
  } else {
    console.error('Invalid file type. Please select an audio file.');
  }
};

const togglePlay = () => {
  if (videoRef.value) {
    // Initialize audio context on first user interaction
    if (!audioContext.value) {
      setAudioChannel(audioChannel.value);
    }
    
    if (isPlaying.value) {
      // Pausing - clear time check interval
      videoRef.value.pause();
      if (timeCheckInterval.value) {
        clearInterval(timeCheckInterval.value);
        timeCheckInterval.value = null;
      }
    } else {
      // Playing - start time check if in time mode
      videoRef.value.play();
      if (currentRepeatMode.value === 'time') {
        if (!repeatStartTime.value) {
          repeatStartTime.value = Date.now();
        }
        
        // Set up time checking interval
        timeCheckInterval.value = setInterval(() => {
          const elapsedTime = (Date.now() - repeatStartTime.value) / 1000 / 60; // in minutes
          if (elapsedTime >= currentRepeatTime.value) {
            // Time limit reached - stop playback
            videoRef.value.pause();
            isPlaying.value = false;
            clearInterval(timeCheckInterval.value);
            timeCheckInterval.value = null;
            console.log(`Time limit reached: ${elapsedTime.toFixed(2)} minutes played`);
            repeatEndTime.value = Date.now();
          }
        }, 100); // Check every 100ms for precision
      }
    }
    isPlaying.value = !isPlaying.value;
  }
};

const restart = () => {
  if (videoRef.value) {
    videoRef.value.currentTime = 0;
    currentTime.value = 0;
  }
};

const toggleMute = () => {
  if (videoRef.value) {
    videoRef.value.muted = !isMuted.value;
    isMuted.value = !isMuted.value;
  }
};

const setAudioChannel = (channel) => {
  audioChannel.value = channel;
  if (videoRef.value) {
    try {
      // Create or get audio context
      if (!audioContext.value) {
        audioContext.value = new (window.AudioContext || window.webkitAudioContext)();
        
        // Create audio source from video element
        const source = audioContext.value.createMediaElementSource(videoRef.value);
        
        // Create panner node for stereo positioning
        panner.value = audioContext.value.createStereoPanner();
        
        // Create gain node for volume control
        const gainNode = audioContext.value.createGain();
        gainNode.gain.value = 1.0;
        
        // Connect the audio graph
        source.connect(panner.value);
        panner.value.connect(gainNode);
        gainNode.connect(audioContext.value.destination);
        
        console.log('Audio graph connected successfully');
      }
      
      // Set pan position: -1 = left only, 0 = center (both), 1 = right only
      if (channel === 'left') {
        panner.value.pan.value = -1;
        console.log('Audio panned to left');
      } else if (channel === 'right') {
        panner.value.pan.value = 1;
        console.log('Audio panned to right');
      } else {
        panner.value.pan.value = 0;
        console.log('Audio centered (stereo)');
      }
      
      // Resume audio context if suspended (required by some browsers)
      if (audioContext.value.state === 'suspended') {
        audioContext.value.resume();
      }
      
    } catch (error) {
      console.warn('Audio channel switching not supported:', error);
      // Fallback: disconnect Web Audio and use native video audio
      if (videoRef.value) {
        videoRef.value.muted = false;
      }
    }
  }
};

const onLoadedMetadata = () => {
  if (videoRef.value) {
    duration.value = videoRef.value.duration;
    // Don't initialize audio channel here - wait for user interaction
    
    // Set up external control blocking
    if (disableExternalControls.value) {
      setupExternalControlBlock();
    }
  }
};

const setupExternalControlBlock = () => {
  if (!videoRef.value) return;
  
  console.log('Setting up external control blocking...');
  
  // Method 1: Completely disable Media Session API
  if ('mediaSession' in navigator) {
    try {
      // Clear all media session data
      navigator.mediaSession.metadata = null;
      
      // Override all media session methods to do nothing
      const originalSetActionHandler = navigator.mediaSession.setActionHandler.bind(navigator.mediaSession);
      
      // Block all media actions
      ['play', 'pause', 'stop', 'seekbackward', 'seekforward', 'previoustrack', 'nexttrack', 'seekto', 'skipad', 'togglemicrophone', 'togglecamera'].forEach(action => {
        originalSetActionHandler(action, () => {
          console.log(`Media Session ${action} blocked`);
          return Promise.reject(new Error('External controls disabled'));
        });
      });
      
      console.log('Media Session API completely blocked');
    } catch (error) {
      console.warn('Failed to block Media Session API:', error);
    }
  }
  
  // Method 2: Override video element methods
  if (videoRef.value) {
    const originalPlay = videoRef.value.play;
    const originalPause = videoRef.value.pause;
    
    videoRef.value.play = function(...args) {
      // Only allow play if called from our component (check call stack)
      const stack = new Error().stack;
      if (stack.includes('VideoPlayer.vue') || stack.includes('togglePlay')) {
        console.log('Internal play allowed');
        return originalPlay.apply(this, args);
      } else {
        console.log('External play blocked');
        return Promise.reject(new Error('External play blocked'));
      }
    };
    
    videoRef.value.pause = function(...args) {
      const stack = new Error().stack;
      if (stack.includes('VideoPlayer.vue') || stack.includes('togglePlay')) {
        console.log('Internal pause allowed');
        return originalPause.apply(this, args);
      } else {
        console.log('External pause blocked');
        return;
      }
    };
  }
  
  // Method 3: Global event interception
  const mediaEventTypes = [
    'play', 'pause', 'playing', 'pause', 'ended', 'timeupdate',
    'durationchange', 'loadeddata', 'loadedmetadata', 'progress',
    'canplay', 'canplaythrough', 'seeking', 'seeked', 'volumechange',
    'ratechange', 'suspend', 'emptied', 'stalled', 'waiting'
  ];
  
  const preventMediaEvents = (event) => {
    if (disableExternalControls.value) {
      if (mediaEventTypes.includes(event.type)) {
        // Check if this is an external trigger by examining the event
        if (event.isTrusted && !event.target.contains(event.relatedTarget)) {
          console.log(`External media event blocked: ${event.type}`);
          event.preventDefault();
          event.stopPropagation();
          event.stopImmediatePropagation();
          return false;
        }
      }
    }
  };
  
  // Add event listeners to block external media events
  if (videoRef.value) {
    mediaEventTypes.forEach(eventType => {
      videoRef.value.addEventListener(eventType, preventMediaEvents, true);
    });
  }
  
  // Method 4: Override global media key handling
  const overrideMediaKeys = () => {
    // Store original addEventListener
    const originalAddEventListener = EventTarget.prototype.addEventListener;
    
    // Override addEventListener for media elements
    EventTarget.prototype.addEventListener = function(type, listener, options) {
      // Block media-related event listeners on video/audio elements
      if (this instanceof HTMLVideoElement || this instanceof HTMLAudioElement) {
        const mediaEvents = ['play', 'pause', 'playing', 'ended', 'timeupdate', 'volumechange'];
        if (mediaEvents.includes(type) && disableExternalControls.value) {
          console.log(`External media event listener blocked: ${type}`);
          return;
        }
      }
      return originalAddEventListener.call(this, type, listener, options);
    };
    
    // Store for cleanup
    window._originalAddEventListener = originalAddEventListener;
  };
  
  overrideMediaKeys();
  
  console.log('External control blocking setup complete');
};

const removeExternalControlBlock = () => {
  console.log('Removing external control blocking...');
  
  // Restore original addEventListener
  if (window._originalAddEventListener) {
    EventTarget.prototype.addEventListener = window._originalAddEventListener;
    delete window._originalAddEventListener;
  }
  
  // Restore Media Session API
  if ('mediaSession' in navigator) {
    try {
      // Clear all action handlers to restore defaults
      ['play', 'pause', 'stop', 'seekbackward', 'seekforward', 'previoustrack', 'nexttrack', 'seekto', 'skipad', 'togglemicrophone', 'togglecamera'].forEach(action => {
        navigator.mediaSession.setActionHandler(action, null);
      });
      console.log('Media Session API restored');
    } catch (error) {
      console.warn('Failed to restore Media Session API:', error);
    }
  }
  
  // Restore video element methods (if possible)
  if (videoRef.value && window._originalVideoPlay && window._originalVideoPause) {
    videoRef.value.play = window._originalVideoPlay;
    videoRef.value.pause = window._originalVideoPause;
    delete window._originalVideoPlay;
    delete window._originalVideoPause;
  }
  
  // Remove global key event listeners
  if (window._mediaKeyPreventer) {
    document.removeEventListener('keydown', window._mediaKeyPreventer, true);
    window.removeEventListener('keydown', window._mediaKeyPreventer, true);
    delete window._mediaKeyPreventer;
  }
  
  // Remove WebKit media event listeners
  if (window._webkitMediaPreventer && videoRef.value) {
    videoRef.value.removeEventListener('webkitplaybacktargetavailable', window._webkitMediaPreventer, true);
    videoRef.value.removeEventListener('webkitcurrentplaybacktargetiswirelesschanged', window._webkitMediaPreventer, true);
    delete window._webkitMediaPreventer;
  }
  
  // Restore video attributes
  if (videoRef.value) {
    videoRef.value.removeAttribute('data-no-media-session');
    videoRef.value.preload = 'metadata';
  }
  
  console.log('External control blocking removed');
};

const onTimeUpdate = () => {
  if (videoRef.value) {
    currentTime.value = videoRef.value.currentTime;
  }
};

const onEnded = () => {
  isPlaying.value = false;
  
  // Handle repeat functionality
  if (currentRepeatMode.value === 'count') {
    actualRepeatCount.value++;
    if (actualRepeatCount.value < currentRepeatCount.value) {
      // Repeat for count mode
      videoRef.value.currentTime = 0;
      videoRef.value.play();
      isPlaying.value = true;
    } else {
      // Reset count after completing repeats
      actualRepeatCount.value = 0;
    }
  } else if (currentRepeatMode.value === 'time') {
    const elapsedTime = (Date.now() - repeatStartTime.value) / 1000 / 60; // in minutes
    if (elapsedTime < currentRepeatTime.value) {
      // Check if there's enough time left for another full play
      const remainingTime = currentRepeatTime.value - elapsedTime;
      const mediaDuration = videoRef.value.duration / 60; // duration in minutes
      
      if (remainingTime >= mediaDuration) {
        // Enough time for another full play
        videoRef.value.currentTime = 0;
        videoRef.value.play();
        isPlaying.value = true;
      } else {
        // Not enough time for full play, stop here
        console.log(`Time limit reached: ${elapsedTime.toFixed(2)} minutes played`);
        repeatEndTime.value = Date.now();
      }
    } else {
      // Time limit reached exactly
      console.log(`Time limit reached: ${elapsedTime.toFixed(2)} minutes played`);
      repeatEndTime.value = Date.now();
    }
  } else if (props.loop && videoRef.value) {
    // Original loop functionality
    videoRef.value.play();
    isPlaying.value = true;
  }
};

const setRepeatMode = (mode) => {
  // Clear any existing time check interval
  if (timeCheckInterval.value) {
    clearInterval(timeCheckInterval.value);
    timeCheckInterval.value = null;
  }
  
  currentRepeatMode.value = mode;
  actualRepeatCount.value = 0;
  
  if (mode === 'time') {
    repeatStartTime.value = 0; // Reset start time
    repeatEndTime.value = 0; // Reset end time
  }
};

const adjustRepeat = (delta) => {
  if (currentRepeatMode.value === 'count') {
    currentRepeatCount.value = Math.max(1, currentRepeatCount.value + delta);
  } else if (currentRepeatMode.value === 'time') {
    currentRepeatTime.value = Math.max(1, currentRepeatTime.value + delta);
  }
};

const formatTime = (seconds) => {
  if (isNaN(seconds)) return '0:00';
  
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = Math.floor(seconds % 60);
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};

const formatCountdown = (seconds) => {
  if (seconds <= 0) return '0:00';
  
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = Math.floor(seconds % 60);
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};

// Watch for prop changes
watch(() => props.muted, (newValue) => {
  isMuted.value = newValue;
  if (videoRef.value) {
    videoRef.value.muted = newValue;
  }
});

watch(() => props.audioChannel, (newValue) => {
  audioChannel.value = newValue;
  setAudioChannel(newValue);
});

watch(() => props.disableExternalControls, (newValue) => {
  disableExternalControls.value = newValue;
  if (newValue) {
    setupExternalControlBlock();
  } else {
    removeExternalControlBlock();
  }
});

onMounted(() => {
  if (videoRef.value) {
    videoRef.value.muted = props.muted;
  }
});
</script>

<style scoped>
.video-player-container {
  max-width: 800px;
  margin: 0 auto;
}
</style>
