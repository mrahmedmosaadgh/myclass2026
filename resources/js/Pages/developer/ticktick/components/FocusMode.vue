<template>
  <div class="focus-mode-overlay">
    <!-- Hidden audio elements -->
    <audio id="audio-rain" src="/sounds/rain.mp3" preload="auto"></audio>
    <audio id="audio-white-noise" src="/sounds/white-noise.mp3" preload="auto"></audio>
    <audio id="audio-forest" src="/sounds/forest.mp3" preload="auto"></audio>

    <div class="focus-mode-content">
      <div class="focus-header">
        <q-btn flat round color="white" icon="close" @click="$emit('exit')" />
        <div class="text-h6 text-white">Focus Mode</div>
      </div>

      <div class="focus-timer">
        <q-circular-progress
          :value="progressValue"
          size="300px"
          :thickness="0.2"
          color="white"
          track-color="rgba(255, 255, 255, 0.3)"
          class="q-ma-md"
        >
          <div class="timer-content">
            <div class="text-h1 text-white">{{ formatTime(timeRemaining) }}</div>
            <div class="text-subtitle1 text-white q-mt-sm">remaining</div>

            <!-- Countdown animation -->
            <div class="countdown-animation">
              <div class="countdown-dot" :style="{ transform: `rotate(${progressValue * 3.6}deg)` }"></div>
            </div>

            <!-- Session type indicator -->
            <div class="session-type text-caption text-white q-mt-xs">
              {{ isBreak ? 'Break Time' : 'Focus Time' }}
            </div>
          </div>
        </q-circular-progress>
      </div>

      <div class="focus-task">
        <div class="text-h4 text-white text-center">{{ task ? task.title : 'Focus Session' }}</div>
        <div class="text-subtitle1 text-white text-center" v-if="task && task.description">
          {{ task.description }}
        </div>

        <!-- Debug info - remove in production -->
        <div class="text-caption text-white-8 q-mt-md" v-if="debugMode">
          <div>Session ID: {{ pomodoroSession?.id }}</div>
          <div>Duration: {{ pomodoroSession?.duration }} minutes</div>
          <div>Started at: {{ pomodoroSession?.started_at }}</div>
          <div>Time remaining: {{ timeRemaining }} seconds</div>
        </div>
      </div>

      <div class="focus-controls">
        <q-btn color="white" text-color="black" label="Pause" icon="pause" @click="pauseTimer" v-if="!isPaused" />
        <q-btn color="white" text-color="black" label="Resume" icon="play_arrow" @click="resumeTimer" v-else />
        <q-btn color="white" text-color="black" label="Complete" icon="check" @click="completeTask" />
        <q-btn color="white" text-color="black" label="Exit Focus Mode" icon="fullscreen_exit" @click="$emit('exit')" />
      </div>

      <div class="focus-ambient">
        <div class="text-subtitle1 text-white q-mb-sm">Ambient Sounds</div>
        <div class="row q-col-gutter-md">
          <div class="col-4">
            <q-btn color="white" text-color="black" label="Rain" icon="water_drop" @click="playAmbientSound('rain')" />
          </div>
          <div class="col-4">
            <q-btn color="white" text-color="black" label="White Noise" icon="waves" @click="playAmbientSound('white-noise')" />
          </div>
          <div class="col-4">
            <q-btn color="white" text-color="black" label="Forest" icon="forest" @click="playAmbientSound('forest')" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useAppStore } from '@/Stores/AppStore';
import { mapIcon } from './icons.js';
import { Notify } from 'quasar';

const props = defineProps({
  task: Object,
  pomodoroSession: Object
});

const emit = defineEmits(['exit']);

// Store
const appStore = useAppStore();

// State
const timeRemaining = ref(0);
const isPaused = ref(false);
const isBreak = ref(false); // Added for session type indicator
const timer = ref(null);
const ambientSound = ref(null);
const currentAmbientSound = ref(null);
const debugMode = ref(true); // Set to false in production

// Computed
const progressValue = computed(() => {
  let total;

  if (props.pomodoroSession) {
    total = props.pomodoroSession.duration * 60;
  } else {
    // Default to 25 minutes if no session is provided
    total = 25 * 60;
  }

  return ((total - timeRemaining.value) / total) * 100;
});

// Methods
const startTimer = () => {
  // If no pomodoro session is provided, we'll use the current timeRemaining value
  if (props.pomodoroSession) {
    // Calculate remaining time based on when the session started
    try {
      const startedAt = new Date(props.pomodoroSession.started_at);
      const now = new Date();
      const elapsedSeconds = Math.floor((now - startedAt) / 1000);
      const totalSeconds = props.pomodoroSession.duration * 60;

      timeRemaining.value = Math.max(0, totalSeconds - elapsedSeconds);
      console.log('Focus Mode Timer started with', timeRemaining.value, 'seconds remaining');
    } catch (error) {
      console.error('Error calculating time remaining:', error);
      // Default to 25 minutes if there's an error
      timeRemaining.value = 25 * 60;
    }
  }

  console.log('Starting timer with', timeRemaining.value, 'seconds remaining');

  clearInterval(timer.value);
  timer.value = setInterval(() => {
    if (!isPaused.value) {
      timeRemaining.value--;

      if (timeRemaining.value <= 0) {
        clearInterval(timer.value);
        playNotificationSound();
      }
    }
  }, 1000);
};

const pauseTimer = () => {
  isPaused.value = true;
};

const resumeTimer = () => {
  isPaused.value = false;
};

const completeTask = () => {
  if (!props.task) return;

  appStore.fetchData({
    endpoint: `/developer/tasks/${props.task.id}/toggle-complete`,
    method: 'post',
    onSuccess: () => {
      emit('exit');
    }
  });
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const playNotificationSound = () => {
  const audio = new Audio('/sounds/notification.mp3');
  audio.play().catch(e => console.error('Error playing sound:', e));
};

const playAmbientSound = (sound) => {
  // Stop current sound if playing
  if (ambientSound.value) {
    ambientSound.value.pause();
    ambientSound.value = null;
  }

  // If clicking the same sound, just stop it
  if (currentAmbientSound.value === sound) {
    currentAmbientSound.value = null;
    return;
  }

  // Play the new sound
  currentAmbientSound.value = sound;

  // Use a more reliable approach with embedded audio elements
  const audioElement = document.getElementById(`audio-${sound}`);
  if (audioElement) {
    audioElement.volume = 0.5;
    audioElement.loop = true;
    audioElement.play().catch(e => {
      console.error('Error playing ambient sound:', e);
      // Fallback to notification
      Notify.create({
        message: 'Could not play ambient sound. Please check your browser settings.',
        color: 'warning'
      });
    });
    ambientSound.value = audioElement;
  } else {
    console.error(`Audio element for ${sound} not found`);
  }
};

// Lifecycle hooks
onMounted(() => {
  startTimer();

  // Request fullscreen
  const element = document.documentElement;
  if (element.requestFullscreen) {
    element.requestFullscreen().catch(e => console.error('Error attempting to enable fullscreen:', e));
  }
});

onBeforeUnmount(() => {
  clearInterval(timer.value);

  // Stop ambient sound
  if (ambientSound.value) {
    ambientSound.value.pause();
    ambientSound.value = null;
  }

  // Exit fullscreen
  if (document.fullscreenElement) {
    document.exitFullscreen().catch(e => console.error('Error attempting to exit fullscreen:', e));
  }
});

// Watch for changes in the pomodoro session
watch(() => props.pomodoroSession, (newSession) => {
  console.log('FocusMode: pomodoroSession changed', newSession);
  if (newSession) {
    startTimer();
  } else {
    // If no session is provided, start a default 25-minute timer
    timeRemaining.value = 25 * 60;
    startTimer();
  }
}, { immediate: true });
</script>

<style scoped>
.focus-mode-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #1976d2, #004ba0);
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.focus-mode-content {
  width: 100%;
  max-width: 800px;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
}

.focus-header {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.focus-timer {
  display: flex;
  justify-content: center;
}

.focus-task {
  text-align: center;
  max-width: 600px;
}

.focus-controls {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  justify-content: center;
}

.focus-ambient {
  margin-top: 2rem;
  width: 100%;
  max-width: 500px;
}

.timer-content {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.countdown-animation {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
}

.countdown-dot {
  position: absolute;
  width: 10px;
  height: 10px;
  background-color: #fff;
  border-radius: 50%;
  top: 10px;
  left: 50%;
  margin-left: -5px;
  transform-origin: center 140px;
  transition: transform 1s linear;
}
</style>
