<template>
  <div class="schedule-app-v2">
    <!-- View Mode Switcher -->
    <ViewModeSwitcher 
      :default-mode="currentViewMode"
      :available-modes="availableModes"
      @mode-change="handleViewModeChange"
    />

    <TestTimeOverride
      v-model:enabled="testTimeEnabled"
      v-model:dayIndex="testDayIndex"
      v-model:timeValue="testTimeValue"
    />

    <!-- Dynamic View Component -->
    <component
      :is="currentViewComponent"
      v-bind="currentViewProps"
      @play-alert="playAlertSound"
      @notify="sendNotification"
      @active-period-update="handleActivePeriodUpdate"
      @view-mode-change="toggleViewMode"
    />

    <!-- Timing Manager Modal -->
    <ScheduleTimingManager
      v-if="showTimingManager"
      v-model="timeSlots"
      @update:modelValue="handleTimingsUpdate"
      @close="showTimingManager = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import ViewModeSwitcher from './components/ViewModeSwitcher.vue';
import CardView from './components/CardView.vue';
import TableView from './components/TableView.vue';
import ListView from './components/ListView.vue';
import MasterTimetableView from './components/MasterTimetableView.vue';
import ScheduleTimingManager from './components/ScheduleTimingManager.vue';
import TestTimeOverride from './components/TestTimeOverride.vue';
import scheduleTimingData from './schedule_timing.json';
import scheduleItemsData from './schedule_data.json';
import masterTimetableData from './data/master_timetable_data.json';

// State
const scheduleData = ref([]);
const timeSlots = ref([]);
const error = ref('');
const currentViewMode = ref('card');
const isShowingAllDays = ref(true);
const currentDayIndex = ref(-1);
const currentTotalSecs = ref(0);
const currentTimeDisplay = ref('00:00:00');
const showNotifyBtn = ref(false);
const activePeriodInfo = ref(null);
const showTimingManager = ref(false);
const testTimeEnabled = ref(false);
const testDayIndex = ref(0);
const testTimeValue = ref('09:00');
const teacherAccessConfig = ref(null);

let timerInterval = null;
let alertSound = null;

const getSecondsFromTimeValue = (timeValue) => {
  const [hours = 0, minutes = 0] = timeValue.split(':').map(Number);
  return (hours * 3600) + (minutes * 60);
};

// View Components Mapping
const viewComponents = {
  card: CardView,
  table: TableView,
  list: ListView,
  master: MasterTimetableView
};

const dayIdToMeta = {
  d1: { day: 'Sunday', dayIndex: 0 },
  d2: { day: 'Monday', dayIndex: 1 },
  d3: { day: 'Tuesday', dayIndex: 2 },
  d4: { day: 'Wednesday', dayIndex: 3 },
  d5: { day: 'Thursday', dayIndex: 4 },
  d6: { day: 'Friday', dayIndex: 5 }
};

const teacherScheduleData = computed(() => {
  if (!teacherAccessConfig.value) {
    return scheduleData.value;
  }

  const { teacherId, stage } = teacherAccessConfig.value;
  const stageData = masterTimetableData?.stages?.[stage];
  if (!stageData?.days) {
    return [];
  }

  return Object.entries(stageData.days).map(([dayId, dayData]) => {
    const teacher = dayData.teachers.find(item => item.id === teacherId);
    const assignments = teacher?.assignments?.[dayId] || {};
    const dayMeta = dayIdToMeta[dayId] || { day: dayId.toUpperCase(), dayIndex: 0 };

    return {
      day: dayMeta.day,
      dayIndex: dayMeta.dayIndex,
      classes: timeSlots.value
        .filter(slot => slot.type !== 'break' && slot.type !== 'activity')
        .map(slot => {
          const assignment = assignments[String(slot.id)] || assignments[slot.id] || null;
          return {
            p: slot.id,
            sub: assignment ? `${assignment.class} · ${assignment.subject}` : ''
          };
        })
    };
  });
});

const availableModes = computed(() => {
  return teacherAccessConfig.value
    ? ['card', 'table', 'list']
    : ['card', 'table', 'list', 'master'];
});

// Computed Properties
const currentViewComponent = computed(() => {
  if (teacherAccessConfig.value && currentViewMode.value === 'master') {
    return CardView;
  }

  return viewComponents[currentViewMode.value] || CardView;
});

const currentViewProps = computed(() => {
  const baseProps = {
    scheduleData: teacherScheduleData.value,
    timeSlots: timeSlots.value,
    currentDayIndex: currentDayIndex.value,
    currentTotalSecs: currentTotalSecs.value,
    currentTimeDisplay: currentTimeDisplay.value,
    isTestTimeEnabled: testTimeEnabled.value,
    teacherAccessMode: !!teacherAccessConfig.value,
    teacherAccessName: teacherAccessConfig.value?.teacherName || ''
  };

  // Add view-specific props
  if (currentViewMode.value === 'table') {
    return {
      ...baseProps,
      isShowingAllDays: isShowingAllDays.value
    };
  }

  return baseProps;
});

// Methods
const handleViewModeChange = (mode) => {
  if (teacherAccessConfig.value && mode === 'master') {
    currentViewMode.value = 'card';
    return;
  }

  currentViewMode.value = mode;
  
  // Haptic feedback on mobile
  if (navigator.vibrate) {
    navigator.vibrate(50);
  }
};

const toggleViewMode = () => {
  // Used by TableView to toggle between all days and today only
  isShowingAllDays.value = !isShowingAllDays.value;
};

const updateLiveIndicator = () => {
  if (testTimeEnabled.value) {
    currentDayIndex.value = Number(testDayIndex.value);
    currentTotalSecs.value = getSecondsFromTimeValue(testTimeValue.value);
    currentTimeDisplay.value = `${testTimeValue.value}:00`;
    return;
  }

  const now = new Date();
  
  // Calculate current day index (0-based, Sunday = 0)
  currentDayIndex.value = now.getDay(); 
  
  // current total seconds from midnight
  currentTotalSecs.value = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();
  
  currentTimeDisplay.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

const handleActivePeriodUpdate = (info) => {
  activePeriodInfo.value = info;
};

const handleTimingsUpdate = (newSlots) => {
  timeSlots.value = newSlots.map(slot => {
    const startParts = slot.start.split(':').map(Number);
    const endParts = slot.end.split(':').map(Number);
    return {
      ...slot,
      startMin: startParts[0] * 60 + startParts[1],
      endMin: endParts[0] * 60 + endParts[1]
    };
  });
};

// --- Notifications & Audio ---
const checkNotificationStatus = () => {
  if ("Notification" in window) {
    if (Notification.permission === "default") {
      showNotifyBtn.value = true;
    }
  }
};

const requestNotificationPermission = () => {
  if ("Notification" in window) {
    Notification.requestPermission().then(permission => {
      if (permission === "granted") {
        showNotifyBtn.value = false;
        playAlertSound(true); // Ignore error on test play
        sendNotification("Alerts Enabled", "You will be notified when classes start!");
      }
    });
  }
};

const playAlertSound = (silentFail = false) => {
  if (alertSound) {
    alertSound.play().catch(e => {
        if(!silentFail) console.log("Audio blocked by browser autoplay rules. Please interact with the page first.", e);
    });
  }
};

const sendNotification = (title, bodyText) => {
  if ("Notification" in window && Notification.permission === "granted") {
    // Attempt standard notification if service worker not available
    try {
        new Notification(title, {
            body: bodyText,
            vibrate: [200, 100, 200]
        });
    } catch(e) {
        console.warn("Notification error", e)
    }
  }
};

// Initialize Data & Handlers
onMounted(() => {
  // Setup audio
  try {
    // using absolute path for demo/test purposes based on original PWA
    alertSound = new Audio('/my-schedule-app/v2/notification1.mp3'); 
  } catch (e) {
    console.warn("Could not initialize audio", e);
  }

  // Load Data
  try {
    scheduleData.value = scheduleItemsData;
    // Process time slots to have start/end in minutes from start of day
    timeSlots.value = scheduleTimingData.map(slot => {
      const startParts = slot.start.split(':').map(Number);
      const endParts = slot.end.split(':').map(Number);
      return {
        ...slot,
        startMin: startParts[0] * 60 + startParts[1],
        endMin: endParts[0] * 60 + endParts[1]
      };
    });
  } catch (err) {
    error.value = "Failed to load schedule data.";
    console.error(err);
  }

  try {
    const params = new URLSearchParams(window.location.search);
    const teacherId = params.get('teacherId');
    const stage = params.get('stage');
    const teacherName = params.get('teacherName');

    if (teacherId && stage && masterTimetableData?.stages?.[stage]) {
      teacherAccessConfig.value = {
        teacherId,
        stage,
        teacherName: teacherName || 'Teacher'
      };

      if (currentViewMode.value === 'master') {
        currentViewMode.value = 'card';
      }
    }
  } catch (e) {
    console.warn('Failed to parse teacher access params', e);
  }

  checkNotificationStatus();

  const savedTestTimeConfig = localStorage.getItem('schedule-v2-test-time');
  if (savedTestTimeConfig) {
    try {
      const parsed = JSON.parse(savedTestTimeConfig);
      testTimeEnabled.value = !!parsed.enabled;
      testDayIndex.value = Number(parsed.dayIndex ?? 0);
      testTimeValue.value = parsed.timeValue || '09:00';
    } catch (e) {
      console.warn('Failed to restore test time config', e);
    }
  }

  updateLiveIndicator();
  
  // Start timer to update time every second
  timerInterval = setInterval(updateLiveIndicator, 1000);
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});

watch([testTimeEnabled, testDayIndex, testTimeValue], () => {
  localStorage.setItem('schedule-v2-test-time', JSON.stringify({
    enabled: testTimeEnabled.value,
    dayIndex: testDayIndex.value,
    timeValue: testTimeValue.value
  }));

  updateLiveIndicator();
});
</script>

<style scoped>
.schedule-app-v2 {
  min-height: 100vh;
  background: #f8fafc;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Global styles for all views */
.schedule-app-v2 * {
  box-sizing: border-box;
}

/* Ensure proper scrolling on mobile */
.schedule-app-v2 {
  overflow-x: hidden;
}

/* Custom scrollbar for webkit browsers */
.schedule-app-v2 ::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.schedule-app-v2 ::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.schedule-app-v2 ::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.schedule-app-v2 ::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Touch-friendly tap targets */
.schedule-app-v2 button {
  min-height: 44px;
  min-width: 44px;
}

/* Focus styles for accessibility */
.schedule-app-v2 *:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .schedule-app-v2 {
    background: white;
  }
  
  .schedule-app-v2 * {
    border-color: black !important;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .schedule-app-v2 * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .schedule-app-v2 {
    background: #0f172a;
    color: #f1f5f9;
  }
  
  .schedule-app-v2 ::-webkit-scrollbar-track {
    background: #1e293b;
  }
  
  .schedule-app-v2 ::-webkit-scrollbar-thumb {
    background: #475569;
  }
  
  .schedule-app-v2 ::-webkit-scrollbar-thumb:hover {
    background: #64748b;
  }
}
</style>
