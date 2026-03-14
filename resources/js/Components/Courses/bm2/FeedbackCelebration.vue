<template>
  <div v-if="isVisible" class="fixed inset-0 z-50 flex items-center justify-center pointer-events-none">
    <!-- Background Overlay with Gradient -->
    <div 
      class="absolute inset-0 bg-gradient-to-br from-purple-600/90 via-blue-600/90 to-pink-600/90 transition-opacity duration-500"
      :class="opacityClass"
    ></div>

    <!-- Confetti Canvas -->
    <canvas ref="confettiCanvas" class="absolute inset-0 w-full h-full"></canvas>

    <!-- Main Content -->
    <div class="relative z-10 text-center px-4 pointer-events-auto">
      <!-- Animated Icon/Emoji -->
      <div 
        v-if="showIcon"
        class="text-8xl md:text-9xl mb-6 animate-bounce"
        :class="iconAnimationClass"
      >
        {{ icon }}
      </div>

      <!-- Title Text -->
      <h2 
        v-if="title"
        class="text-4xl md:text-6xl font-bold text-white mb-4 drop-shadow-lg"
        :class="titleAnimationClass"
      >
        {{ title }}
      </h2>

      <!-- Message -->
      <p 
        v-if="message"
        class="text-xl md:text-3xl text-white/95 mb-8 font-semibold drop-shadow-md"
        :class="messageAnimationClass"
      >
        {{ message }}
      </p>

      <!-- Score Display -->
      <div 
        v-if="showScore && pointsEarned"
        class="inline-block bg-white/20 backdrop-blur-sm rounded-2xl px-8 py-4 mb-6 border-4 border-yellow-400 shadow-2xl"
        :class="scoreAnimationClass"
      >
        <div class="text-yellow-300 text-2xl md:text-4xl font-bold">
          🌟 +{{ pointsEarned }} Points! 🌟
        </div>
      </div>

      <!-- Stars Animation -->
      <div v-if="showStars" class="flex justify-center gap-4 mb-6">
        <span 
          v-for="star in stars" 
          :key="star.id"
          class="text-5xl md:text-6xl animate-pulse"
          :style="{ animationDelay: star.delay + 'ms' }"
        >
          {{ star.icon }}
        </span>
      </div>

      <!-- Progress Bar (for streak/combos) -->
      <div v-if="showProgress && progressValue" class="w-64 md:w-80 mx-auto mb-6">
        <div class="bg-white/20 rounded-full h-4 overflow-hidden">
          <div 
            class="h-full bg-gradient-to-r from-yellow-400 via-orange-400 to-red-400 transition-all duration-500"
            :style="{ width: progressValue + '%' }"
          ></div>
        </div>
        <p v-if="progressLabel" class="text-white text-sm mt-2 font-semibold">
          {{ progressLabel }}
        </p>
      </div>

      <!-- Buttons -->
      <div v-if="showButtons" class="flex gap-4 justify-center mt-6 pointer-events-auto">
        <button
          v-if="showContinueButton"
          @click="handleContinue"
          class="px-8 py-4 bg-gradient-to-r from-green-400 to-blue-500 hover:from-green-500 hover:to-blue-600 text-white text-xl font-bold rounded-full shadow-lg transform hover:scale-105 transition-all duration-200 border-4 border-white/50"
        >
          {{ continueButtonText }}
        </button>
        
        <button
          v-if="showCloseButton"
          @click="handleClose"
          class="px-8 py-4 bg-white/20 hover:bg-white/30 text-white text-xl font-semibold rounded-full backdrop-blur-sm transition-all duration-200 border-2 border-white/50"
        >
          {{ closeButtonText }}
        </button>
      </div>
    </div>

    <!-- Floating Elements (Optional) -->
    <div v-if="showFloatingElements" class="absolute inset-0 pointer-events-none">
      <div 
        v-for="element in floatingElements" 
        :key="element.id"
        class="absolute text-4xl animate-float"
        :style="{
          left: element.left + '%',
          top: element.top + '%',
          animationDelay: element.delay + 'ms',
          animationDuration: element.duration + 's'
        }"
      >
        {{ element.icon }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import confetti from 'canvas-confetti';

const props = defineProps({
  // Visibility
  isVisible: {
    type: Boolean,
    default: false
  },
  
  // Content
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: '🎉'
  },
  
  // Scoring
  showScore: {
    type: Boolean,
    default: true
  },
  pointsEarned: {
    type: Number,
    default: 0
  },
  
  // Animations
  showIcon: {
    type: Boolean,
    default: true
  },
  showStars: {
    type: Boolean,
    default: true
  },
  stars: {
    type: Array,
    default: () => [
      { id: 1, icon: '⭐', delay: 0 },
      { id: 2, icon: '⭐', delay: 200 },
      { id: 3, icon: '⭐', delay: 400 }
    ]
  },
  
  // Progress
  showProgress: {
    type: Boolean,
    default: false
  },
  progressValue: {
    type: Number,
    default: 0
  },
  progressLabel: {
    type: String,
    default: ''
  },
  
  // Buttons
  showButtons: {
    type: Boolean,
    default: false
  },
  showContinueButton: {
    type: Boolean,
    default: true
  },
  continueButtonText: {
    type: String,
    default: 'Continue →'
  },
  showCloseButton: {
    type: Boolean,
    default: false
  },
  closeButtonText: {
    type: String,
    default: 'Close'
  },
  
  // Confetti
  showConfetti: {
    type: Boolean,
    default: true
  },
  confettiIntensity: {
    type: String,
    default: 'normal', // 'low', 'normal', 'high', 'extreme'
    validator: (value) => ['low', 'normal', 'high', 'extreme'].includes(value)
  },
  
  // Floating Elements
  showFloatingElements: {
    type: Boolean,
    default: true
  },
  floatingElements: {
    type: Array,
    default: () => []
  },
  
  // Sound
  playSound: {
    type: Boolean,
    default: true
  },
  soundType: {
    type: String,
    default: 'success', // 'success', 'achievement', 'combo', 'perfect'
    validator: (value) => ['success', 'achievement', 'combo', 'perfect'].includes(value)
  },
  
  // Duration
  autoHide: {
    type: Boolean,
    default: false
  },
  autoHideDelay: {
    type: Number,
    default: 3000
  },
  
  // Customization
  variant: {
    type: String,
    default: 'default', // 'default', 'success', 'achievement', 'combo', 'perfect'
    validator: (value) => ['default', 'success', 'achievement', 'combo', 'perfect'].includes(value)
  }
});

const emit = defineEmits(['continue', 'close', 'hidden']);

const confettiCanvas = ref(null);
const isVisible = ref(props.isVisible);
const opacity = ref(1);

// Computed styles based on variant
const variantConfig = computed(() => {
  const configs = {
    default: {
      icon: '🎉',
      title: 'Great Job!',
      soundType: 'success',
      confettiIntensity: 'normal',
      stars: 3
    },
    success: {
      icon: '✅',
      title: 'Correct!',
      soundType: 'success',
      confettiIntensity: 'normal',
      stars: 3
    },
    achievement: {
      icon: '🏆',
      title: 'Achievement Unlocked!',
      soundType: 'achievement',
      confettiIntensity: 'high',
      stars: 5
    },
    combo: {
      icon: '🔥',
      title: 'Combo!',
      soundType: 'combo',
      confettiIntensity: 'high',
      stars: 4
    },
    perfect: {
      icon: '💎',
      title: 'Perfect!',
      soundType: 'perfect',
      confettiIntensity: 'extreme',
      stars: 5
    }
  };
  
  return configs[props.variant] || configs.default;
});

const opacityClass = computed(() => {
  return `opacity-${Math.round(opacity.value * 10)}`;
});

const iconAnimationClass = computed(() => {
  const animations = {
    default: 'animate-bounce',
    success: 'animate-bounce',
    achievement: 'animate-spin-slow',
    combo: 'animate-pulse',
    perfect: 'animate-bounce'
  };
  return animations[props.variant] || animations.default;
});

const titleAnimationClass = computed(() => {
  return 'animate__animated animate__zoomIn';
});

const messageAnimationClass = computed(() => {
  return 'animate__animated animate__fadeInUp';
});

const scoreAnimationClass = computed(() => {
  return 'animate__animated animate__heartBeat';
});

// Confetti presets
const confettiPresets = {
  low: { particleCount: 50, spread: 30 },
  normal: { particleCount: 100, spread: 50 },
  high: { particleCount: 200, spread: 70 },
  extreme: { particleCount: 400, spread: 100 }
};

// Sound effects (you can add actual audio files)
const soundEffects = {
  // Using your existing audio files from /public/audio/
  success: '/audio/purchase-success-384963.mp3',        // Your success sound
  achievement: '/audio/purchase-success-384963.mp3',    // Reuse for achievements
  combo: '/audio/purchase-success-384963.mp3',          // Reuse for combos
  perfect: '/audio/purchase-success-384963.mp3',        // Same sound, more intense visuals
  click: '/audio/click/mixkit-gear-fast-lock-tap-2857.wav', // Your click sound (for UI)
  error: '/audio/error-010-206498.mp3'                  // Your error sound (optional)
};

// Methods
const launchConfetti = () => {
  if (!props.showConfetti || !confettiCanvas.value) return;
  
  const preset = confettiPresets[props.confettiIntensity];
  
  confetti({
    particleCount: preset.particleCount,
    spread: preset.spread,
    origin: { y: 0.6 },
    colors: ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4'],
    disableForReducedMotion: true,
    zIndex: 100
  });
  
  // Additional confetti burst
  setTimeout(() => {
    confetti({
      particleCount: preset.particleCount / 2,
      spread: preset.spread * 1.5,
      origin: { y: 0.6 },
      colors: ['#FFD700', '#FF69B4', '#00CED1'],
      disableForReducedMotion: true,
      zIndex: 100
    });
  }, 300);
};

const playSoundEffect = () => {
  if (!props.playSound) return;
  
  const soundUrl = soundEffects[props.soundType] || soundEffects.success;
  
  try {
    // Create new audio element
    const audio = new Audio(soundUrl);
    
    // Set volume (50% - not too loud)
    audio.volume = 0.5;
    
    // Play the sound
    audio.play().catch(error => {
      console.warn('Sound playback failed:', error);
    });
    
    // Clean up after playing
    audio.addEventListener('ended', () => {
      audio.remove();
    });
  } catch (error) {
    console.warn('Failed to create audio element:', error);
  }
};

const handleClose = () => {
  emit('close');
};

const handleContinue = () => {
  emit('continue');
};

const hide = () => {
  opacity.value = 0;
  setTimeout(() => {
    isVisible.value = false;
    emit('hidden');
  }, 500);
};

// Watch for visibility changes
watch(() => props.isVisible, (newVal) => {
  isVisible.value = newVal;
  if (newVal) {
    // Show effects
    setTimeout(() => {
      launchConfetti();
      playSoundEffect();
    }, 100);
    
    // Auto-hide if enabled
    if (props.autoHide) {
      setTimeout(() => {
        hide();
      }, props.autoHideDelay);
    }
  }
});

// Lifecycle
onMounted(() => {
  if (props.isVisible) {
    launchConfetti();
    playSoundEffect();
  }
});
</script>

<style scoped>
@keyframes float {
  0%, 100% {
    transform: translateY(0) rotate(0deg);
    opacity: 1;
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
    opacity: 0.7;
  }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

.animate-spin-slow {
  animation: spin 3s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Custom animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
