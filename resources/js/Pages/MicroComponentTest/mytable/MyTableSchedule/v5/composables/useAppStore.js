import { ref, reactive, provide, inject, readonly, watch, toRaw } from 'vue';
import { useOfflineDB } from './useOfflineDB';
import { useCloudSync } from './useCloudSync';
import defaultScheduleData from '../schedule_data.json';
import defaultTimingData from '../schedule_timing.json';
import defaultStageDayTimings from '../data/stage_day_timings.json';
import masterTimetableData from '../data/master_timetable_data.json';

const STORE_KEY = Symbol('AppStoreV5');

function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

/**
 * Creates the central V5 app store. Call once in the root component and provide().
 * All state is loaded from IndexedDB on init; writes go through update helpers → IDB.
 */
export function createAppStore() {
  const db = useOfflineDB();
  const cloud = useCloudSync();

  // ── Reactive State ──

  const timingsConfig = ref(clone(defaultStageDayTimings));
  const scheduleData = ref(clone(defaultScheduleData));
  const schoolTimetable = ref(clone(masterTimetableData));

  const selectedStage = ref('prim');
  const selectedDay = ref('d1');
  const currentViewMode = ref('card');

  const testTimeEnabled = ref(false);
  const testDayIndex = ref(0);
  const testTimeValue = ref('09:00');

  const currentDayIndex = ref(-1);
  const currentTotalSecs = ref(0);
  const currentTimeDisplay = ref('00:00:00');

  const isInitialized = ref(false);
  const storeError = ref(null);

  // ── Helpers ──

  const dayIndexToId = (dayIndex) => {
    const mapping = ['d1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd1'];
    return mapping[dayIndex] || 'd1';
  };

  const getSecondsFromTimeValue = (timeValue) => {
    const [hours = 0, minutes = 0] = timeValue.split(':').map(Number);
    return (hours * 3600) + (minutes * 60);
  };

  // ── Time Ticker ──

  let timerInterval = null;

  const updateLiveIndicator = () => {
    if (testTimeEnabled.value) {
      currentDayIndex.value = Number(testDayIndex.value);
      currentTotalSecs.value = getSecondsFromTimeValue(testTimeValue.value);
      currentTimeDisplay.value = `${testTimeValue.value}:00`;
      return;
    }
    const now = new Date();
    currentDayIndex.value = now.getDay();
    currentTotalSecs.value = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();
    currentTimeDisplay.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  };

  const startTimer = () => {
    updateLiveIndicator();
    timerInterval = setInterval(updateLiveIndicator, 1000);
  };

  const stopTimer = () => {
    if (timerInterval) {
      clearInterval(timerInterval);
      timerInterval = null;
    }
  };

  // ── Persistence Helpers ──

  const saveTimingsToIDB = async () => {
    try {
      await db.saveTimingConfig(toRaw(timingsConfig.value));
    } catch (e) {
      console.warn('[Store] Failed to save timings to IDB:', e);
    }
  };

  const saveSettingsToIDB = async () => {
    try {
      await db.saveSetting('selectedStage', selectedStage.value);
      await db.saveSetting('selectedDay', selectedDay.value);
      await db.saveSetting('currentViewMode', currentViewMode.value);
      await db.saveSetting('testTimeConfig', {
        enabled: testTimeEnabled.value,
        dayIndex: testDayIndex.value,
        timeValue: testTimeValue.value
      });
    } catch (e) {
      console.warn('[Store] Failed to save settings to IDB:', e);
    }
  };

  const pushCloudSnapshot = async () => {
    const snapshot = {
      timingsConfig: toRaw(timingsConfig.value),
      selectedStage: selectedStage.value,
      selectedDay: selectedDay.value,
      currentViewMode: currentViewMode.value,
      scheduleData: toRaw(scheduleData.value),
      lastModified: Date.now()
    };

    const result = await cloud.pushToServer(snapshot);
    if (!result.success && result.error !== 'offline' && result.error !== 'already syncing') {
      await db.addToSyncQueue('save-snapshot', snapshot);
    }
  };

  // ── Public Mutations ──

  const setTimingsConfig = async (config) => {
    timingsConfig.value = clone(config);
    await saveTimingsToIDB();
    pushCloudSnapshot();
  };

  const setSelectedStage = async (stage) => {
    selectedStage.value = stage;
    await db.saveSetting('selectedStage', stage);
    pushCloudSnapshot();
  };

  const setSelectedDay = async (day) => {
    selectedDay.value = day;
    await db.saveSetting('selectedDay', day);
    pushCloudSnapshot();
  };

  const setViewMode = async (mode) => {
    currentViewMode.value = mode;
    await db.saveSetting('currentViewMode', mode);
    pushCloudSnapshot();
  };

  const setScheduleData = async (data) => {
    scheduleData.value = clone(data);
    await db.savePersonalSchedule({ schedule: toRaw(scheduleData.value), timings: [] });
    pushCloudSnapshot();
  };

  const setSchoolTimetable = async (data) => {
    schoolTimetable.value = clone(data);
    await db.saveSchoolTimetable({ stages: toRaw(schoolTimetable.value).stages || toRaw(schoolTimetable.value) });
    pushCloudSnapshot();
  };

  const setTestTimeConfig = async (config) => {
    testTimeEnabled.value = !!config.enabled;
    testDayIndex.value = Number(config.dayIndex ?? 0);
    testTimeValue.value = config.timeValue || '09:00';
    await db.saveSetting('testTimeConfig', {
      enabled: testTimeEnabled.value,
      dayIndex: testDayIndex.value,
      timeValue: testTimeValue.value
    });
    updateLiveIndicator();
  };

  // ── Init: Load everything from IDB ──

  const initialize = async () => {
    try {
      await db.init();

      // Load timing config
      const savedTimings = await db.getTimingConfig();
      if (savedTimings && Array.isArray(savedTimings.default) && savedTimings.default.length > 0) {
        timingsConfig.value = {
          default: savedTimings.default,
          overrides: savedTimings.overrides || {}
        };
      }

      // Load personal schedule
      const savedSchedule = await db.getPersonalSchedule();
      if (savedSchedule?.schedule?.length) {
        scheduleData.value = savedSchedule.schedule;
      }

      // Load school timetable
      const savedSchool = await db.getSchoolTimetable();
      if (savedSchool?.stages && Object.keys(savedSchool.stages).length > 0) {
        schoolTimetable.value = { stages: savedSchool.stages };
      }

      // Load settings
      const savedStage = await db.getSetting('selectedStage');
      if (savedStage) selectedStage.value = savedStage;

      const savedDay = await db.getSetting('selectedDay');
      if (savedDay) selectedDay.value = savedDay;
      else selectedDay.value = dayIndexToId(new Date().getDay());

      const savedViewMode = await db.getSetting('currentViewMode');
      if (savedViewMode) currentViewMode.value = savedViewMode;

      const savedTestTime = await db.getSetting('testTimeConfig');
      if (savedTestTime) {
        testTimeEnabled.value = !!savedTestTime.enabled;
        testDayIndex.value = Number(savedTestTime.dayIndex ?? 0);
        testTimeValue.value = savedTestTime.timeValue || '09:00';
      }

      // Cloud sync: pull with local-wins
      const localLastModified = savedTimings?.lastModified || 0;
      const pullResult = await cloud.pullFromServer(localLastModified);

      if (pullResult.success && pullResult.source === 'server' && pullResult.data) {
        // Server data is newer — apply it
        if (pullResult.data.timingsConfig) {
          timingsConfig.value = clone(pullResult.data.timingsConfig);
          await saveTimingsToIDB();
        }
        if (pullResult.data.selectedStage) {
          selectedStage.value = pullResult.data.selectedStage;
        }
        if (pullResult.data.selectedDay) {
          selectedDay.value = pullResult.data.selectedDay;
        }
        if (pullResult.data.currentViewMode) {
          currentViewMode.value = pullResult.data.currentViewMode;
        }
        await saveSettingsToIDB();
      } else if (pullResult.success && pullResult.source === 'local') {
        // Local is newer — push local to server
        pushCloudSnapshot();
      }

      // Process any pending sync queue items
      const pending = await db.getPendingSyncItems();
      if (pending.length > 0) {
        await cloud.processQueue(pending, db.markSynced);
      }

      isInitialized.value = true;
      startTimer();
    } catch (e) {
      console.error('[Store] Initialization failed:', e);
      storeError.value = e.message;
      isInitialized.value = true; // Still mark as init so UI can render with defaults
      startTimer();
    }
  };

  const destroy = () => {
    stopTimer();
  };

  // ── Watch test time changes ──
  watch([testTimeEnabled, testDayIndex, testTimeValue], () => {
    updateLiveIndicator();
  });

  // ── Store Object ──

  const store = {
    // State (readonly for consumers)
    timingsConfig: readonly(timingsConfig),
    scheduleData: readonly(scheduleData),
    schoolTimetable: readonly(schoolTimetable),
    selectedStage: readonly(selectedStage),
    selectedDay: readonly(selectedDay),
    currentViewMode: readonly(currentViewMode),
    testTimeEnabled: readonly(testTimeEnabled),
    testDayIndex: readonly(testDayIndex),
    testTimeValue: readonly(testTimeValue),
    currentDayIndex: readonly(currentDayIndex),
    currentTotalSecs: readonly(currentTotalSecs),
    currentTimeDisplay: readonly(currentTimeDisplay),
    isInitialized: readonly(isInitialized),
    storeError: readonly(storeError),

    // Cloud sync state
    syncStatus: cloud.syncStatus,
    syncMessage: cloud.syncMessage,
    isOnline: cloud.isOnline,

    // Writable refs (for menu components that need v-model)
    _timingsConfig: timingsConfig,
    _selectedStage: selectedStage,
    _selectedDay: selectedDay,

    // Mutations
    setTimingsConfig,
    setSelectedStage,
    setSelectedDay,
    setViewMode,
    setScheduleData,
    setSchoolTimetable,
    setTestTimeConfig,

    // DB access (for DataManager)
    db,

    // Lifecycle
    initialize,
    destroy,
    pushCloudSnapshot,

    // Helpers
    dayIndexToId,
    masterTimetableData
  };

  return store;
}

/**
 * Provide the store from the root component.
 */
export function provideAppStore(store) {
  provide(STORE_KEY, store);
}

/**
 * Inject the store in any child component.
 */
export function useAppStore() {
  const store = inject(STORE_KEY);
  if (!store) {
    throw new Error('[useAppStore] Store not provided. Call provideAppStore() in root component.');
  }
  return store;
}
