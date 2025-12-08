<template>
  <div class="youtube-player-container" :class="{ 'loading': isLoading, 'error': hasError }">
    <!-- YouTube Player -->
    <div class="youtube-player" ref="playerContainer">
      <!-- Loading Indicator -->
      <div v-if="isLoading" class="loading-overlay">
        <div class="loading-spinner"></div>
      </div>

      <!-- Error Message -->
      <div v-if="hasError" class="error-overlay">
        <div class="error-message">
          <div class="error-icon">⚠️</div>
          <div>{{ errorMessage }}</div>
          <button @click="retryLoading" class="retry-button">Retry</button>
        </div>
      </div>

      <!-- YouTube iframe will be inserted here by the API -->
      <div :id="playerId"></div>

      <!-- Play/Pause Overlay -->
      <div
        v-if="!isLoading && !hasError"
        class="player-overlay"
        @click="togglePlayPause"
        :class="{ 'playing': isPlaying }"
      >
        <div class="play-button">
          <svg v-if="!isPlaying" viewBox="0 0 24 24" class="play-icon">
            <path fill="currentColor" d="M8,5.14V19.14L19,12.14L8,5.14Z" />
          </svg>
          <svg v-else viewBox="0 0 24 24" class="pause-icon">
            <path fill="currentColor" d="M14,19H18V5H14M6,19H10V5H6V19Z" />
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
  videoUrl: {
    type: String,
    required: true,
  },
  autoplay: {
    type: Boolean,
    default: false,
  },
  muted: {
    type: Boolean,
    default: false,
  },
  controls: {
    type: Boolean,
    default: true,
  },
  loop: {
    type: Boolean,
    default: false,
  },
  startAt: {
    type: Number,
    default: 0,
  },
  width: {
    type: [Number, String],
    default: '100%',
  },
  height: {
    type: [Number, String],
    default: '100%',
  },
});

// Player state
const player = ref(null);
const playerContainer = ref(null);
const isLoading = ref(true);
const hasError = ref(false);
const errorMessage = ref('Error loading video');
const isPlaying = ref(false);
const playerId = `youtube-player-${Date.now()}`;

// Extract YouTube video ID from URL
const getYouTubeId = (url) => {
  if (!url) return null;

  // Regular expressions for different YouTube URL formats
  const regexps = [
    /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^\?&"'>]+)/,
    /^([^\?&"'>]+)$/  // Direct video ID
  ];

  for (const regex of regexps) {
    const match = url.match(regex);
    if (match && match[1]) {
      return match[1];
    }
  }

  return null;
};

// Initialize YouTube player
const initPlayer = () => {
  const videoId = getYouTubeId(props.videoUrl);

  if (!videoId) {
    hasError.value = true;
    errorMessage.value = 'Invalid YouTube URL';
    isLoading.value = false;
    return;
  }

  // Reset state
  hasError.value = false;
  isLoading.value = true;

  // Load YouTube API if not already loaded
  if (!window.YT) {
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    const firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    window.onYouTubeIframeAPIReady = createPlayer.bind(null, videoId);
  } else {
    createPlayer(videoId);
  }
};

// Create YouTube player
const createPlayer = (videoId) => {
  // Destroy existing player if it exists
  if (player.value) {
    player.value.destroy();
  }

  try {
    player.value = new YT.Player(playerId, {
      height: props.height,
      width: props.width,
      videoId: videoId,
      playerVars: {
        autoplay: props.autoplay ? 1 : 0,
        controls: props.controls ? 1 : 0,
        mute: props.muted ? 1 : 0,
        loop: props.loop ? 1 : 0,
        start: props.startAt,
        modestbranding: 1,
        rel: 0,
        showinfo: 0,
        enablejsapi: 1,
        playsinline: 1,
        origin: window.location.origin,
        
      },
      events: {
        'onReady': onPlayerReady,
        'onStateChange': onPlayerStateChange,
        'onError': onPlayerError,
      },
    });
  } catch (error) {
    console.error('Error creating YouTube player:', error);
    hasError.value = true;
    errorMessage.value = 'Error initializing player';
    isLoading.value = false;
  }
};

// Player event handlers
const onPlayerReady = (event) => {
  isLoading.value = false;
  if (props.autoplay) {
    event.target.playVideo();
  }
};

const onPlayerStateChange = (event) => {
  // Update playing state based on player state
  isPlaying.value = event.data === YT.PlayerState.PLAYING;
};

const onPlayerError = (event) => {
  isLoading.value = false;
  hasError.value = true;

  // Map YouTube error codes to user-friendly messages
  const errorCodes = {
    2: 'Invalid video ID',
    5: 'HTML5 player error',
    100: 'Video not found or removed',
    101: 'Video owner does not allow embedding',
    150: 'Video owner does not allow embedding',
  };

  errorMessage.value = errorCodes[event.data] || 'Error loading video';
};

// Toggle play/pause
const togglePlayPause = () => {
  if (!player.value) return;

  try {
    const state = player.value.getPlayerState();

    if (state === YT.PlayerState.PLAYING) {
      player.value.pauseVideo();
      isPlaying.value = false;
    } else {
      player.value.playVideo();
      isPlaying.value = true;
    }
  } catch (error) {
    console.error('Error toggling play/pause:', error);
  }
};

// Retry loading the video
const retryLoading = () => {
  initPlayer();
};

// Watch for URL changes
watch(() => props.videoUrl, (newUrl) => {
  if (newUrl) {
    initPlayer();
  }
});

// Initialize player on mount
onMounted(() => {
  initPlayer();
});

// Clean up on unmount
onUnmounted(() => {
  if (player.value) {
    try {
      player.value.destroy();
    } catch (error) {
      console.error('Error destroying YouTube player:', error);
    }
  }
});
</script>

<style scoped>
.youtube-player-container {
  position: relative;
  width: 100%;
  height: 0;
  padding-bottom: 56.25%; /* 16:9 aspect ratio */
  overflow: hidden;
  background-color: #000;
  border-radius: 8px;
}

.youtube-player {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

/* Loading overlay */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.7);
  z-index: 2;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: #fff;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error overlay */
.error-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.8);
  z-index: 2;
}

.error-message {
  text-align: center;
  color: white;
  padding: 20px;
  max-width: 80%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.error-icon {
  font-size: 32px;
  margin-bottom: 10px;
}

.retry-button {
  background-color: #ff0000;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
  font-weight: bold;
}

.retry-button:hover {
  background-color: #cc0000;
}

/* Play/Pause overlay */
.player-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0);
  transition: background-color 0.3s ease;
  cursor: pointer;
  z-index: 1;
}

.player-overlay:hover {
  background-color: rgba(0, 0, 0, 0.3);
}

.play-button {
  width: 60px;
  height: 60px;
  background-color: rgba(0, 0, 0, 0.7);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease, transform 0.2s ease;
  color: white;
}

.player-overlay:hover .play-button {
  opacity: 1;
  transform: scale(1.1);
}

.play-icon, .pause-icon {
  width: 30px;
  height: 30px;
}

/* YouTube iframe */
iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: none;
}
</style>

