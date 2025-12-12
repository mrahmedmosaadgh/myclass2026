<template>
  <q-card style="width: 400px; max-width: 90vw;">
    <q-card-section class="text-center">
      <div class="text-h6">{{ isBreak ? 'Break Time' : 'Pomodoro Timer' }}</div>
      <div class="text-subtitle2">{{ isBreak ? 'Relax and recharge' : 'Stay focused and productive' }}</div>
    </q-card-section>

    <q-card-section class="text-center">
      <q-circular-progress
        :value="progressValue"
        size="200px"
        :thickness="0.2"
        color="primary"
        track-color="grey-3"
        class="q-ma-md"
      >
        <div class="text-h3">{{ formatTime(timeRemaining) }}</div>
      </q-circular-progress>
    </q-card-section>

    <q-card-section v-if="!isBreak && !isRunning && !isCompleted">
      <q-select
        v-model="selectedTask"
        :options="taskOptions"
        label="Select Task"
        option-value="id"
        option-label="title"
        emit-value
        map-options
        clearable
      />

      <div class="row q-mt-md">
        <q-input
          v-model.number="duration"
          type="number"
          label="Duration (minutes)"
          min="1"
          max="60"
          class="col-6 q-pr-sm"
        />
        <q-input
          v-model.number="breakDuration"
          type="number"
          label="Break (minutes)"
          min="1"
          max="30"
          class="col-6 q-pl-sm"
        />
      </div>
    </q-card-section>

    <q-card-section v-if="isCompleted">
      <div class="text-h6 text-center q-mb-md">Session Completed!</div>

      <q-input
        v-model="sessionNotes"
        type="textarea"
        label="What did you accomplish?"
        rows="3"
      />

      <div class="row q-mt-md justify-center">
        <q-btn flat color="grey" label="Need more time" @click="extendSession" />
        <q-btn flat color="positive" label="Task completed" @click="markTaskCompleted" />
        <q-btn flat color="primary" label="Take a longer break" @click="startLongerBreak" />
      </div>
    </q-card-section>

    <q-card-section v-if="isRunning">
      <div class="text-center q-mb-md">
        <div v-if="selectedTaskObject">
          <div class="text-subtitle1">Working on:</div>
          <div class="text-h6">{{ selectedTaskObject.title }}</div>
        </div>
        <div v-else>
          <div class="text-subtitle1">{{ isBreak ? 'Taking a break' : 'Focus session' }}</div>
        </div>
      </div>
    </q-card-section>

    <q-card-actions align="center">
      <template v-if="!isRunning && !isCompleted">
        <q-btn color="primary" label="Start" icon="play_arrow" @click="startTimer" />
      </template>

      <template v-if="isRunning">
        <q-btn color="negative" label="Stop" icon="stop" @click="stopTimer" />
        <q-btn color="grey" label="Pause" icon="pause" @click="pauseTimer" v-if="!isPaused" />
        <q-btn color="primary" label="Resume" icon="play_arrow" @click="resumeTimer" v-else />
        <q-btn color="primary" flat label="Focus Mode" icon="fullscreen" @click="enterFocusMode" />
      </template>

      <template v-if="isCompleted">
        <q-btn color="primary" label="Start New Session" icon="refresh" @click="resetTimer" />
      </template>

      <q-btn flat label="Close" color="grey" @click="closeTimer" v-if="!isRunning" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { ref, computed, onBeforeUnmount, watch } from 'vue';
import { useAppStore } from '@/Stores/AppStore';
import { mapIcon } from './icons.js';

const props = defineProps({
  tasks: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'session-completed', 'enter-focus-mode']);

// Store
const appStore = useAppStore();

// State
const isRunning = ref(false);
const isPaused = ref(false);
const isCompleted = ref(false);
const isBreak = ref(false);
const timeRemaining = ref(0);
const duration = ref(25);
const breakDuration = ref(5);
const selectedTask = ref(null);
const sessionNotes = ref('');
const currentSession = ref(null);
const timer = ref(null);
const startTime = ref(null);
const pausedTime = ref(null);
const totalPausedTime = ref(0);

// Computed
const taskOptions = computed(() => {
  return props.tasks.map(task => ({
    id: task.id,
    title: task.title
  }));
});

const selectedTaskObject = computed(() => {
  if (!selectedTask.value) return null;
  return props.tasks.find(task => task.id === selectedTask.value);
});

const progressValue = computed(() => {
  if (isBreak.value) {
    const total = breakDuration.value * 60;
    return ((total - timeRemaining.value) / total) * 100;
  } else {
    const total = duration.value * 60;
    return ((total - timeRemaining.value) / total) * 100;
  }
});

// Methods
const startTimer = () => {
  isRunning.value = true;
  isPaused.value = false;
  isCompleted.value = false;
  isBreak.value = false;
  timeRemaining.value = duration.value * 60;
  startTime.value = Date.now();
  totalPausedTime.value = 0;

  // Create a new pomodoro session
  appStore.fetchData({
    endpoint: '/developer/pomodoro/start',
    method: 'post',
    data: {
      task_id: selectedTask.value,
      type: 'work',
      duration: duration.value
    },
    onSuccess: (response) => {
      currentSession.value = response.session;
      startTimerInterval();
    }
  });
};

const startBreak = () => {
  isRunning.value = true;
  isPaused.value = false;
  isCompleted.value = false;
  isBreak.value = true;
  timeRemaining.value = breakDuration.value * 60;
  startTime.value = Date.now();
  totalPausedTime.value = 0;

  // Create a new break session
  appStore.fetchData({
    endpoint: '/developer/pomodoro/start',
    method: 'post',
    data: {
      task_id: null,
      type: 'break',
      duration: breakDuration.value
    },
    onSuccess: (response) => {
      currentSession.value = response.session;
      startTimerInterval();
    }
  });
};

const startTimerInterval = () => {
  clearInterval(timer.value);
  timer.value = setInterval(() => {
    if (!isPaused.value) {
      timeRemaining.value--;

      if (timeRemaining.value <= 0) {
        completeTimer();
      }
    }
  }, 1000);
};

const pauseTimer = () => {
  isPaused.value = true;
  pausedTime.value = Date.now();
};

const resumeTimer = () => {
  isPaused.value = false;
  totalPausedTime.value += (Date.now() - pausedTime.value);
  pausedTime.value = null;
};

const stopTimer = () => {
  clearInterval(timer.value);

  // End the current session
  if (currentSession.value) {
    appStore.fetchData({
      endpoint: `/developer/pomodoro/${currentSession.value.id}/end`,
      method: 'post',
      data: {
        notes: 'Session interrupted by user',
        status: 'interrupted'
      },
      onSuccess: () => {
        emit('session-completed');
      }
    });
  }

  resetTimer();
};

const completeTimer = () => {
  clearInterval(timer.value);
  isRunning.value = false;

  // Play sound notification
  playNotificationSound();

  if (isBreak.value) {
    // Break is over, reset for a new work session
    isBreak.value = false;
    isCompleted.value = false;

    // End the break session
    if (currentSession.value) {
      appStore.fetchData({
        endpoint: `/developer/pomodoro/${currentSession.value.id}/end`,
        method: 'post',
        data: {
          notes: 'Break completed',
          status: 'completed'
        },
        onSuccess: () => {
          emit('session-completed');
          currentSession.value = null;
        }
      });
    }
  } else {
    // Work session is over
    isCompleted.value = true;
  }
};

const resetTimer = () => {
  clearInterval(timer.value);
  isRunning.value = false;
  isPaused.value = false;
  isCompleted.value = false;
  isBreak.value = false;
  timeRemaining.value = 0;
  sessionNotes.value = '';
  currentSession.value = null;
};

const closeTimer = () => {
  resetTimer();
  emit('close');
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

const extendSession = () => {
  // End the current session
  if (currentSession.value) {
    appStore.fetchData({
      endpoint: `/developer/pomodoro/${currentSession.value.id}/end`,
      method: 'post',
      data: {
        notes: sessionNotes.value || 'Need more time to complete',
        status: 'extended'
      },
      onSuccess: () => {
        emit('session-completed');
        // Start a new session with the same task
        startTimer();
      }
    });
  }
};

const markTaskCompleted = () => {
  // End the current session
  if (currentSession.value) {
    appStore.fetchData({
      endpoint: `/developer/pomodoro/${currentSession.value.id}/end`,
      method: 'post',
      data: {
        notes: sessionNotes.value || 'Task completed',
        status: 'completed'
      },
      onSuccess: () => {
        emit('session-completed');

        // Mark the task as completed if one was selected
        if (selectedTask.value) {
          appStore.fetchData({
            endpoint: `/developer/tasks/${selectedTask.value}/toggle-complete`,
            method: 'post',
            onSuccess: () => {
              // Start break
              startBreak();
            }
          });
        } else {
          // Start break
          startBreak();
        }
      }
    });
  }
};

const startLongerBreak = () => {
  // End the current session
  if (currentSession.value) {
    appStore.fetchData({
      endpoint: `/developer/pomodoro/${currentSession.value.id}/end`,
      method: 'post',
      data: {
        notes: sessionNotes.value || 'Taking a longer break',
        status: 'completed'
      },
      onSuccess: () => {
        emit('session-completed');

        // Start a longer break
        breakDuration.value = breakDuration.value * 2;
        startBreak();
      }
    });
  }
};

const enterFocusMode = () => {
  console.log('Entering Focus Mode with task:', selectedTaskObject.value);
  console.log('Current session:', currentSession.value);

  // Emit the event to enter focus mode
  emit('enter-focus-mode', selectedTaskObject.value, currentSession.value);

  // Close the dialog by emitting the close event
  emit('close');
};

// Cleanup
onBeforeUnmount(() => {
  clearInterval(timer.value);
});
</script>
