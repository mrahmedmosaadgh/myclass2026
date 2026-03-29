<template>
  <div class="schedule-app-v2">
    <!-- PWA Install Banner -->
    <div v-if="showInstallBanner" class="pwa-install-banner">
      <div class="install-banner-content">
        <div class="install-banner-info">
          <div class="install-banner-title">Install Schedule V2</div>
          <div class="install-banner-desc">Add to home screen for quick access</div>
        </div>
        <div class="install-banner-actions">
          <button @click="dismissInstallBanner" class="install-btn dismiss">Not now</button>
          <button @click="installPWA" class="install-btn primary">Install</button>
        </div>
      </div>
    </div>

    <!-- View Mode Switcher -->
    <ViewModeSwitcher 
      :default-mode="currentViewMode"
      :available-modes="availableModes"
      @mode-change="handleViewModeChange"
    />

    <AdminTimingBar
      v-if="!teacherAccessConfig"
      :selected-stage="selectedStage"
      :selected-day="selectedDay"
      :today-day-id="todayDayId"
      :current-date-label="adminDateTimeLabel"
      :custom-timing-days="customTimingDaysForSelectedStage"
      @update:stage="handleAdminStageChange"
      @update:day="handleAdminDayChange"
      @day-click="openTimingManagerForDay"
      @open-timing="openTimingManagerForDay"
      @open-timing-all="openTimingManagerForAllDays"
      @go-today="goToToday"
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
      @update:selected-stage="handleAdminStageChange"
      @update:selected-day="handleAdminDayChange"
      @timings-update="handleSharedTimingsUpdate"
    />

    <!-- Timing Manager Modal -->
    <StageDayTimingManager
      v-if="showTimingManager"
      v-model="timingsConfig"
      :stage="timingManagerStage"
      :day="timingManagerDay"
      @update:modelValue="handleSharedTimingsUpdate"
      @close="showTimingManager = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import ViewModeSwitcher from './components/ViewModeSwitcher.vue';
import AdminTimingBar from './components/AdminTimingBar.vue';
import CardView from './components/CardView.vue';
import TableView from './components/TableView.vue';
import ListView from './components/ListView.vue';
import MasterTimetableView from './components/MasterTimetableView.vue';
import StageDayTimingManager from './components/StageDayTimingManager.vue';
import TestTimeOverride from './components/TestTimeOverride.vue';
import scheduleTimingData from './schedule_timing.json';
import scheduleItemsData from './schedule_data.json';
import masterTimetableData from './data/master_timetable_data.json';
import stageDayTimingsData from './data/stage_day_timings.json';

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
const selectedStage = ref('prim');
const selectedDay = ref('d1');
const timingManagerStage = ref('prim');
const timingManagerDay = ref('d1');
const timingsConfig = ref({
  default: [],
  overrides: {}
});

// PWA Install State
const showInstallBanner = ref(false);
const deferredPrompt = ref(null);

let timerInterval = null;
let alertSound = null;

const getSecondsFromTimeValue = (timeValue) => {
  const [hours = 0, minutes = 0] = timeValue.split(':').map(Number);
  return (hours * 3600) + (minutes * 60);
};

const cloneValue = (value) => JSON.parse(JSON.stringify(value));

const normalizeSlots = (slots) => {
  return (slots || []).map(slot => {
    const startParts = slot.start.split(':').map(Number);
    const endParts = slot.end.split(':').map(Number);
    return {
      ...slot,
      startMin: startParts[0] * 60 + startParts[1],
      endMin: endParts[0] * 60 + endParts[1]
    };
  });
};

const dayIndexToId = (dayIndex) => {
  const mapping = ['d1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd1'];
  return mapping[dayIndex] || 'd1';
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

const stageLabelMap = {
  prim: 'Primary',
  middle: 'Middle',
  sec: 'Secondary'
};

const dayLabelMap = {
  d1: 'Day 1',
  d2: 'Day 2',
  d3: 'Day 3',
  d4: 'Day 4',
  d5: 'Day 5',
  d6: 'Day 6'
};

const todayDayId = computed(() => dayIndexToId(currentDayIndex.value));

const resolvedTimeSlots = computed(() => {
  const config = timingsConfig.value;
  const stageOverride = config?.overrides?.[selectedStage.value];

  if (stageOverride?.days?.[selectedDay.value]) {
    return normalizeSlots(stageOverride.days[selectedDay.value]);
  }

  if (stageOverride?.default) {
    return normalizeSlots(stageOverride.default);
  }

  if (config?.default?.length) {
    return normalizeSlots(config.default);
  }

  return normalizeSlots(scheduleTimingData);
});

const customTimingDaysForSelectedStage = computed(() => {
  const stageOverride = timingsConfig.value?.overrides?.[selectedStage.value]?.days || {};
  return Object.entries(stageOverride)
    .filter(([, value]) => Array.isArray(value) && value.length > 0)
    .map(([dayId]) => dayId);
});

const adminDateTimeLabel = computed(() => {
  const dayMeta = dayIdToMeta[selectedDay.value];
  const stageLabel = stageLabelMap[selectedStage.value] || selectedStage.value;

  if (testTimeEnabled.value) {
    return `${dayMeta?.day || dayLabelMap[selectedDay.value]} • ${currentTimeDisplay.value} • ${stageLabel}`;
  }

  const now = new Date();
  const dateText = now.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  });

  return `${dateText} • ${currentTimeDisplay.value} • ${dayMeta?.day || dayLabelMap[selectedDay.value]} • ${stageLabel}`;
});

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
      classes: resolvedTimeSlots.value
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
    timeSlots: resolvedTimeSlots.value,
    currentDayIndex: currentDayIndex.value,
    currentTotalSecs: currentTotalSecs.value,
    currentTimeDisplay: currentTimeDisplay.value,
    isTestTimeEnabled: testTimeEnabled.value,
    teacherAccessMode: !!teacherAccessConfig.value,
    teacherAccessName: teacherAccessConfig.value?.teacherName || '',
    selectedStage: selectedStage.value,
    selectedDay: selectedDay.value,
    todayDayId: todayDayId.value,
    timingsData: timingsConfig.value,
    customTimingDays: customTimingDaysForSelectedStage.value
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
  timeSlots.value = normalizeSlots(newSlots);
};

const handleSharedTimingsUpdate = (newTimings) => {
  timingsConfig.value = cloneValue(newTimings);
  handleTimingsUpdate(resolvedTimeSlots.value);
};

const handleAdminStageChange = (stage) => {
  selectedStage.value = stage;
};

const handleAdminDayChange = (day) => {
  selectedDay.value = day;
};

const openTimingManagerForDay = (day) => {
  timingManagerStage.value = selectedStage.value;
  timingManagerDay.value = day || selectedDay.value;
  selectedDay.value = timingManagerDay.value;
  showTimingManager.value = true;
};

const openTimingManagerForAllDays = () => {
  timingManagerStage.value = selectedStage.value;
  timingManagerDay.value = '';
  showTimingManager.value = true;
};

const goToToday = () => {
  selectedDay.value = todayDayId.value;
};

// --- PWA Install Methods ---
const installPWA = async () => {
  if (!deferredPrompt.value) {
    console.log('[PWA] No install prompt available');
    return;
  }

  try {
    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    
    if (outcome === 'accepted') {
      console.log('[PWA] User accepted the install prompt');
    } else {
      console.log('[PWA] User dismissed the install prompt');
    }
    
    deferredPrompt.value = null;
    showInstallBanner.value = false;
  } catch (error) {
    console.error('[PWA] Error during install:', error);
  }
};

const dismissInstallBanner = () => {
  showInstallBanner.value = false;
  deferredPrompt.value = null;
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
  // Register Service Worker for PWA functionality
  if ('serviceWorker' in navigator && window.location.pathname.startsWith('/my-schedule-app/v2')) {
    // First, unregister any existing service workers to avoid conflicts
    navigator.serviceWorker.getRegistrations().then(registrations => {
      registrations.forEach(registration => {
        if (registration.scope.includes('/my-schedule-app/')) {
          registration.unregister();
          console.log('[SW] Unregistered existing service worker:', registration.scope);
        }
      });
    }).then(() => {
      // Now register the new service worker
      navigator.serviceWorker.register('/my-schedule-app-v2-sw.js', {
        scope: '/my-schedule-app/v2'
      })
        .then((registration) => {
          console.log('[SW] Service Worker registered:', registration);
        })
        .catch((error) => {
          console.error('[SW] Service Worker registration failed:', error);
        });
    });
  }

  // Setup PWA install prompt
  const handleBeforeInstallPrompt = (e) => {
    e.preventDefault();
    deferredPrompt.value = e;
    showInstallBanner.value = true;
    console.log('[PWA] Install prompt ready');
  };

  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

  // Handle app installed event
  window.addEventListener('appinstalled', () => {
    console.log('[PWA] App was installed');
    deferredPrompt.value = null;
    showInstallBanner.value = false;
  });

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
    timingsConfig.value = cloneValue(stageDayTimingsData);
    timeSlots.value = normalizeSlots(scheduleTimingData);
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
      selectedStage.value = stage;

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

  const savedAdminSelection = localStorage.getItem('schedule-v2-admin-selection');
  if (savedAdminSelection) {
    try {
      const parsed = JSON.parse(savedAdminSelection);
      selectedStage.value = parsed.stage || selectedStage.value;
      selectedDay.value = parsed.day || selectedDay.value;
    } catch (e) {
      console.warn('Failed to restore admin selection', e);
    }
  } else {
    selectedDay.value = dayIndexToId(new Date().getDay());
  }

  const savedTimings = localStorage.getItem('school-timings-v2');
  if (savedTimings) {
    try {
      const parsed = JSON.parse(savedTimings);
      timingsConfig.value = {
        default: parsed.default || timingsConfig.value.default,
        overrides: {
          ...(timingsConfig.value.overrides || {}),
          ...(parsed.overrides || {})
        }
      };
    } catch (e) {
      console.warn('Failed to restore shared timings', e);
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

watch([selectedStage, selectedDay], () => {
  localStorage.setItem('schedule-v2-admin-selection', JSON.stringify({
    stage: selectedStage.value,
    day: selectedDay.value
  }));
});

watch(timingsConfig, (newValue) => {
  localStorage.setItem('school-timings-v2', JSON.stringify(newValue));
  handleTimingsUpdate(resolvedTimeSlots.value);
}, { deep: true });
</script>

<style scoped>
.schedule-app-v2 {
  min-height: 100vh;
  background: #f8fafc;
}

/* PWA Install Banner */
.pwa-install-banner {
  position: sticky;
  top: 0;
  z-index: 50;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.install-banner-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.25rem;
  max-width: 100%;
}

.install-banner-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  min-width: 0;
  flex: 1;
}

.install-banner-title {
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.2;
}

.install-banner-desc {
  font-size: 0.875rem;
  opacity: 0.9;
  line-height: 1.3;
}

.install-banner-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.install-btn {
  padding: 0.5rem 0.875rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
  white-space: nowrap;
}

.install-btn.dismiss {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.install-btn.dismiss:hover {
  background: rgba(255, 255, 255, 0.3);
}

.install-btn.primary {
  background: white;
  color: #2563eb;
}

.install-btn.primary:hover {
  background: #f8fafc;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

@media (max-width: 640px) {
  .install-banner-content {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
  }

  .install-banner-actions {
    justify-content: space-between;
  }

  .install-btn {
    flex: 1;
    padding: 0.625rem 1rem;
  }
}
</style>

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
