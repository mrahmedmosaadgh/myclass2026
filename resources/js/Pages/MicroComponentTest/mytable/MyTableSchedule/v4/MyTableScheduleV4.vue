<template>
  <div class="schedule-app-v4">
    <!-- Auto-Save Status Indicator -->
    <div v-if="showAutoSaveIndicator" class="auto-save-indicator" :class="autoSaveStatus">
      <div class="auto-save-content">
        <span class="auto-save-icon">{{ getAutoSaveIcon() }}</span>
        <span class="auto-save-text">{{ autoSaveMessage }}</span>
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
      @data-changed="handleDataChange"
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

// Emits
const emit = defineEmits(['data-changed']);

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

// Offline Auto-Save State
const userId = ref('anonymous');
const autoSaveStatus = ref('idle');
const autoSaveMessage = ref('');
const showAutoSaveIndicator = ref(false);
const lastSaveTime = ref(null);
const isOnline = ref(navigator.onLine);
const autoSaveQueue = ref([]);
const isAutoSaving = ref(false);

let timerInterval = null;
let alertSound = null;
let autoSaveTimeout = null;

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

// Auto-Save Methods
const getAutoSaveIcon = () => {
  switch (autoSaveStatus.value) {
    case 'saving': return '💾';
    case 'saved': return '✅';
    case 'error': return '❌';
    case 'offline': return '📴';
    default: return '⏳';
  }
};

const showAutoSaveStatus = (status, message) => {
  autoSaveStatus.value = status;
  autoSaveMessage.value = message;
  showAutoSaveIndicator.value = true;
  
  clearTimeout(autoSaveTimeout);
  autoSaveTimeout = setTimeout(() => {
    showAutoSaveIndicator.value = false;
  }, 3000);
};

const generateUserId = () => {
  let id = localStorage.getItem('schedule-v4-user-id');
  if (!id) {
    id = 'user-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('schedule-v4-user-id', id);
  }
  return id;
};

const saveDataToServer = async (data) => {
  if (isAutoSaving.value) return;
  
  isAutoSaving.value = true;
  autoSaveStatus.value = 'saving';
  autoSaveMessage.value = 'Saving to cloud...';
  
  try {
    const response = await fetch('/api/v4/save-data', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-User-ID': userId.value
      },
      body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.success) {
      lastSaveTime.value = new Date().toISOString();
      autoSaveStatus.value = 'saved';
      autoSaveMessage.value = 'Saved to cloud';
      
      // Also save to localStorage as backup
      localStorage.setItem('schedule-v4-data-backup', JSON.stringify({
        data,
        timestamp: lastSaveTime.value,
        version: '4.0'
      }));
    } else {
      throw new Error(result.error || 'Save failed');
    }
  } catch (error) {
    console.error('Auto-save failed:', error);
    autoSaveStatus.value = 'error';
    autoSaveMessage.value = 'Save failed - saved locally';
    
    // Save to localStorage as fallback
    localStorage.setItem('schedule-v4-data-backup', JSON.stringify({
      data,
      timestamp: new Date().toISOString(),
      version: '4.0',
      error: error.message
    }));
    
    // Add to queue for retry when online
    if (isOnline.value) {
      autoSaveQueue.value.push({ data, timestamp: Date.now() });
    }
  } finally {
    isAutoSaving.value = false;
    
    setTimeout(() => {
      if (autoSaveStatus.value !== 'error') {
        showAutoSaveIndicator.value = false;
      }
    }, 2000);
  }
};

const loadDataFromServer = async () => {
  const localTimingsRaw = localStorage.getItem('schedule-v4-stage-timings') || localStorage.getItem('school-timings-v4');
  const hasLocalTimings = !!localTimingsRaw;

  try {
    const response = await fetch('/api/v4/load-data', {
      headers: {
        'X-User-ID': userId.value
      }
    });
    
    const result = await response.json();
    
    if (result.success && result.data) {
      // Apply loaded data
      if (!hasLocalTimings && result.data.timingsConfig) {
        timingsConfig.value = result.data.timingsConfig;
      }
      if (result.data.selectedStage) {
        selectedStage.value = result.data.selectedStage;
      }
      if (result.data.selectedDay) {
        selectedDay.value = result.data.selectedDay;
      }
      if (result.data.currentViewMode) {
        currentViewMode.value = result.data.currentViewMode;
      }
      
      showAutoSaveStatus('saved', 'Data loaded from cloud');
    }
  } catch (error) {
    console.error('Failed to load data from server:', error);
    
    // Try to load from localStorage backup
    const backup = localStorage.getItem('schedule-v4-data-backup');
    if (backup) {
      try {
        const parsed = JSON.parse(backup);
        if (parsed.data) {
          if (!hasLocalTimings && parsed.data.timingsConfig) {
            timingsConfig.value = parsed.data.timingsConfig;
          }
          if (parsed.data.selectedStage) {
            selectedStage.value = parsed.data.selectedStage;
          }
          if (parsed.data.selectedDay) {
            selectedDay.value = parsed.data.selectedDay;
          }
          if (parsed.data.currentViewMode) {
            currentViewMode.value = parsed.data.currentViewMode;
          }
        }
        showAutoSaveStatus('saved', 'Data loaded from local backup');
      } catch (e) {
        console.warn('Failed to parse local backup:', e);
      }
    }
  }
};

const handleDataChange = (data) => {
  // Emit to parent component
  emit('data-changed', data);
  
  // Auto-save to server and localStorage
  const saveData = {
    timingsConfig: timingsConfig.value,
    selectedStage: selectedStage.value,
    selectedDay: selectedDay.value,
    currentViewMode: currentViewMode.value,
    scheduleData: scheduleData.value,
    timestamp: new Date().toISOString()
  };
  
  // Save immediately
  saveDataToServer(saveData);
};

const processAutoSaveQueue = async () => {
  if (autoSaveQueue.value.length === 0 || !isOnline.value) return;
  
  const queue = [...autoSaveQueue.value];
  autoSaveQueue.value = [];
  
  for (const item of queue) {
    try {
      await saveDataToServer(item.data);
    } catch (error) {
      console.error('Failed to process queued save:', error);
      // Re-add to queue if it fails
      autoSaveQueue.value.push(item);
    }
  }
};

// Methods
const handleViewModeChange = (mode) => {
  if (teacherAccessConfig.value && mode === 'master') {
    currentViewMode.value = 'card';
    return;
  }

  currentViewMode.value = mode;
  
  // Trigger auto-save
  handleDataChange({ type: 'view-mode-change', mode });
  
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
  
  // Trigger auto-save
  handleDataChange({ type: 'timings-update', timings: newTimings });
};

const handleAdminStageChange = (stage) => {
  selectedStage.value = stage;
  handleDataChange({ type: 'stage-change', stage });
};

const handleAdminDayChange = (day) => {
  selectedDay.value = day;
  handleDataChange({ type: 'day-change', day });
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

// Online/Offline handling
const handleOnlineStatus = () => {
  isOnline.value = navigator.onLine;
  if (isOnline.value) {
    showAutoSaveStatus('saved', 'Back online - syncing data');
    processAutoSaveQueue();
  } else {
    showAutoSaveStatus('offline', 'Offline - data saved locally');
  }
};

// Initialize Data & Handlers
onMounted(() => {
  // Generate or get user ID
  userId.value = generateUserId();
  
  // Setup online/offline listeners
  window.addEventListener('online', handleOnlineStatus);
  window.addEventListener('offline', handleOnlineStatus);
  
  // Register Service Worker for PWA functionality
  if ('serviceWorker' in navigator && window.location.pathname.startsWith('/my-fly-schedule-app/v4')) {
    // First, unregister any existing service workers to avoid conflicts
    navigator.serviceWorker.getRegistrations().then(registrations => {
      registrations.forEach(registration => {
        if (registration.scope.includes('/my-schedule-app/') || registration.scope.includes('/my-fly-schedule-app/')) {
          registration.unregister();
          console.log('[SW] Unregistered existing service worker:', registration.scope);
        }
      });
    }).then(() => {
      // Now register the new service worker
      navigator.serviceWorker.register('/my-fly-schedule-app-v4-sw.js', {
        scope: '/my-fly-schedule-app/v4'
      })
        .then((registration) => {
          console.log('[SW] Service Worker registered:', registration);
        })
        .catch((error) => {
          console.error('[SW] Service Worker registration failed:', error);
        });
    });
  }

  // Setup audio
  try {
    // using absolute path for demo/test purposes based on original PWA
    alertSound = new Audio('/my-schedule-app/v4/notification1.mp3'); 
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

  // Load user-specific data from server
  loadDataFromServer();

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

  const savedTestTimeConfig = localStorage.getItem('schedule-v4-test-time');
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

  const savedAdminSelection = localStorage.getItem('schedule-v4-admin-selection');
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

  const savedTimings = localStorage.getItem('schedule-v4-stage-timings') || localStorage.getItem('school-timings-v4');
  if (savedTimings) {
    try {
      const parsed = JSON.parse(savedTimings);
      timingsConfig.value = {
        default: Array.isArray(parsed.default) ? parsed.default : [],
        overrides: parsed.overrides && typeof parsed.overrides === 'object' ? parsed.overrides : {}
      };
    } catch (e) {
      console.warn('Failed to restore shared timings', e);
    }
  }

  updateLiveIndicator();
  
  // Start timer to update time every second
  timerInterval = setInterval(updateLiveIndicator, 1000);
  
  // Show initial auto-save status
  showAutoSaveStatus('saved', 'Ready - auto-save enabled');
  
  // Listen for data import events
  window.addEventListener('data-refresh-required', handleDataRefresh);
});

const handleDataRefresh = (event) => {
  const { source, target } = event.detail;
  console.log('Data refresh required:', source, target);
  
  // Reload data from localStorage
  if (target === 'stage_day_timings' || target === 'school_timetable') {
    const savedTimings = localStorage.getItem('schedule-v4-stage-timings') || localStorage.getItem('school-timings-v4');
    if (savedTimings) {
      try {
        const parsed = JSON.parse(savedTimings);
        timingsConfig.value = {
          default: Array.isArray(parsed.default) ? parsed.default : [],
          overrides: parsed.overrides && typeof parsed.overrides === 'object' ? parsed.overrides : {}
        };
        handleTimingsUpdate(resolvedTimeSlots.value);
        showAutoSaveStatus('saved', 'Timing data refreshed from import');
      } catch (e) {
        console.warn('Failed to refresh timings from import', e);
      }
    }
  }
  
  if (target === 'personal_schedule') {
    // Reload personal schedule data
    loadDataFromServer();
    showAutoSaveStatus('saved', 'Personal schedule refreshed from import');
  }
};

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval);
  }
  window.removeEventListener('data-refresh-required', handleDataRefresh);
  window.removeEventListener('online', handleOnlineStatus);
  window.removeEventListener('offline', handleOnlineStatus);
  clearTimeout(autoSaveTimeout);
});

watch([testTimeEnabled, testDayIndex, testTimeValue], () => {
  localStorage.setItem('schedule-v4-test-time', JSON.stringify({
    enabled: testTimeEnabled.value,
    dayIndex: testDayIndex.value,
    timeValue: testTimeValue.value
  }));

  updateLiveIndicator();
});

watch([selectedStage, selectedDay], () => {
  localStorage.setItem('schedule-v4-admin-selection', JSON.stringify({
    stage: selectedStage.value,
    day: selectedDay.value
  }));
});

watch(timingsConfig, (newValue) => {
  // Save to both keys for compatibility
  localStorage.setItem('school-timings-v4', JSON.stringify(newValue));
  localStorage.setItem('schedule-v4-stage-timings', JSON.stringify(newValue));
  handleTimingsUpdate(resolvedTimeSlots.value);
}, { deep: true });
</script>

<style scoped>
.schedule-app-v4 {
  min-height: 100vh;
  background: #f8fafc;
}

/* Auto-Save Indicator */
.auto-save-indicator {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  pointer-events: none;
  animation: slideIn 0.3s ease-out;
}

.auto-save-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  font-size: 0.875rem;
  font-weight: 500;
}

.auto-save-indicator.saving .auto-save-content {
  background: #3b82f6;
  color: white;
}

.auto-save-indicator.saved .auto-save-content {
  background: #10b981;
  color: white;
}

.auto-save-indicator.error .auto-save-content {
  background: #ef4444;
  color: white;
}

.auto-save-indicator.offline .auto-save-content {
  background: #f59e0b;
  color: white;
}

.auto-save-icon {
  font-size: 1rem;
}

.auto-save-text {
  white-space: nowrap;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 640px) {
  .auto-save-indicator {
    top: 10px;
    right: 10px;
    left: 10px;
  }
  
  .auto-save-content {
    justify-content: center;
  }
}

/* Global styles for all views */
.schedule-app-v4 * {
  box-sizing: border-box;
}

/* Ensure proper scrolling on mobile */
.schedule-app-v4 {
  overflow-x: hidden;
}

/* Custom scrollbar for webkit browsers */
.schedule-app-v4 ::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.schedule-app-v4 ::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.schedule-app-v4 ::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.schedule-app-v4 ::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Touch-friendly tap targets */
.schedule-app-v4 button {
  min-height: 44px;
  min-width: 44px;
}

/* Focus styles for accessibility */
.schedule-app-v4 *:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .schedule-app-v4 {
    background: white;
  }
  
  .schedule-app-v4 * {
    border-color: black !important;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .schedule-app-v4 * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .schedule-app-v4 {
    background: #0f172a;
    color: #f1f5f9;
  }
  
  .schedule-app-v4 ::-webkit-scrollbar-track {
    background: #1e293b;
  }
  
  .schedule-app-v4 ::-webkit-scrollbar-thumb {
    background: #475569;
  }
  
  .schedule-app-v4 ::-webkit-scrollbar-thumb:hover {
    background: #64748b;
  }
  
  .auto-save-content {
    background: #1e293b;
    color: #f1f5f9;
  }
}
</style>
