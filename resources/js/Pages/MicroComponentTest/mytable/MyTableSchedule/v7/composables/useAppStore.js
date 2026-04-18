import { ref, reactive, provide, inject, readonly, watch, toRaw } from 'vue';
import { useOfflineDB } from './useOfflineDB';
import { useCloudSync } from './useCloudSync';
import defaultScheduleData from '../schedule_data.json';
import defaultTimingData from '../schedule_timing.json';
import masterTimetableData from '../data/master_timetable_data.json';
import defaultWeeklyPlanData from '../data/weekly_plan_data.json';

const STORE_KEY = Symbol('AppStoreV5');

function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

/**
 * Migrate old timing structure to new per-stage structure
 */
function migrateOldTimings(oldConfig) {
  const newConfig = {
    stages: ['prim', 'middle', 'sec'],
    overrides: {}
  };

  // If old config had default timings, use them for all stages
  const defaultTimings = oldConfig.default || [];
  
  ['prim', 'middle', 'sec'].forEach(stage => {
    // Use stage-specific default if it exists, otherwise use global default
    const stageDefault = oldConfig.overrides?.[stage]?.default || defaultTimings;
    
    newConfig.overrides[stage] = {
      default: stageDefault,
      days: {}
    };

    // Copy any day-specific overrides
    ['d1', 'd2', 'd3', 'd4', 'd5', 'd6'].forEach(day => {
      const dayTiming = oldConfig.overrides?.[stage]?.days?.[day];
      if (dayTiming) {
        newConfig.overrides[stage].days[day] = dayTiming;
      } else {
        newConfig.overrides[stage].days[day] = null; // inherit stage default
      }
    });
  });

  return newConfig;
}

/**
 * Creates the central V5 app store. Call once in the root component and provide().
 * All state is loaded from IndexedDB on init; writes go through update helpers → IDB.
 */
export function createAppStore() {
  const db = useOfflineDB();
  const cloud = useCloudSync();

  // ── Reactive State ──

  const timingsConfig = ref({ stages: [], overrides: {} });
  const scheduleData = ref(clone(defaultScheduleData));
  const schoolTimetable = ref(clone(masterTimetableData));

  const selectedStage = ref('prim');
  const selectedDay = ref('d1');
  const currentViewMode = ref('tablev3');
  const showTodayOnly = ref(false);

  const testTimeEnabled = ref(false);
  const testDayIndex = ref(0);
  const testTimeValue = ref('09:00');

  const currentDayIndex = ref(-1);
  const currentTotalSecs = ref(0);
  const currentTimeDisplay = ref('00:00:00');

  const isInitialized = ref(false);
  const storeError = ref(null);

  // ── Weekly Plans State ──
  const weeklyPlans = ref(clone(defaultWeeklyPlanData));

  // ── Weekly Plan Helpers ──

  const getWeekKey = (date = new Date()) => {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
    return `${d.getUTCFullYear()}-W${String(weekNo).padStart(2, '0')}`;
  };

  const getWeekTitle = (weekKey) => {
    return weeklyPlans.value[weekKey]?.meta?.title || weekKey;
  };

  const getWeeklyPlanEntry = (weekKey, className, dayId, periodId) => {
    return weeklyPlans.value[weekKey]?.classes?.[className]?.[dayId]?.[String(periodId)] || null;
  };

  const getScheduleClasses = () => {
    const classes = new Set();
    const data = Array.isArray(scheduleData.value) ? scheduleData.value : [];
    data.forEach(day => {
      (day.classes || []).forEach(slot => {
        if (slot.sub && slot.sub.trim()) classes.add(slot.sub.trim());
      });
    });
    return Array.from(classes).sort();
  };

  const dayIdToName = (dayId) => {
    const map = { d1: 'Sunday', d2: 'Monday', d3: 'Tuesday', d4: 'Wednesday', d5: 'Thursday', d6: 'Friday' };
    return map[dayId] || dayId;
  };

  const getScheduledSlotsForClass = (className) => {
    const slots = [];
    const data = Array.isArray(scheduleData.value) ? scheduleData.value : [];
    data.forEach(day => {
      const dayId = `d${day.dayIndex + 1}`;
      (day.classes || []).forEach(slot => {
        if (slot.sub === className) {
          slots.push({ dayId, dayName: day.day, periodId: String(slot.p) });
        }
      });
    });
    return slots;
  };

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
      await db.saveSetting('showTodayOnly', showTodayOnly.value);
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
      showTodayOnly: showTodayOnly.value,
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

  const setShowTodayOnly = async (value) => {
    showTodayOnly.value = !!value;
    await db.saveSetting('showTodayOnly', showTodayOnly.value);
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

  // ── Weekly Plan Mutations ──

  const saveWeeklyPlansToIDB = async () => {
    try {
      await db.saveSetting('weeklyPlans', toRaw(weeklyPlans.value));
    } catch (e) {
      console.warn('[Store] Failed to save weeklyPlans to IDB:', e);
    }
  };

  const setWeeklyPlans = async (data) => {
    weeklyPlans.value = clone(data);
    await saveWeeklyPlansToIDB();
  };

  const setWeekTitle = async (weekKey, title) => {
    if (!weeklyPlans.value[weekKey]) {
      weeklyPlans.value[weekKey] = { meta: { title }, classes: {} };
    } else {
      if (!weeklyPlans.value[weekKey].meta) weeklyPlans.value[weekKey].meta = {};
      weeklyPlans.value[weekKey].meta.title = title;
    }
    await saveWeeklyPlansToIDB();
  };

  const updateWeeklyPlanEntry = async (weekKey, className, dayId, periodId, payload) => {
    const pid = String(periodId);
    if (!weeklyPlans.value[weekKey]) {
      weeklyPlans.value[weekKey] = { meta: { title: weekKey }, classes: {} };
    }
    const wk = weeklyPlans.value[weekKey];
    if (!wk.classes) wk.classes = {};
    if (!wk.classes[className]) wk.classes[className] = {};
    if (!wk.classes[className][dayId]) wk.classes[className][dayId] = {};
    wk.classes[className][dayId][pid] = { ...wk.classes[className][dayId][pid], ...payload };
    await saveWeeklyPlansToIDB();
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

      // Load timings config with migration support
      let savedTimings = await db.getTimingConfig();
      if (!savedTimings) {
        // First load - build config from new structure
        const stagesConfig = await import('../data/stages.json');
        const primTimings = await import('../data/timings/prim.json');
        const middleTimings = await import('../data/timings/middle.json');
        const secTimings = await import('../data/timings/sec.json');
        
        timingsConfig.value = {
          stages: stagesConfig.default,
          overrides: {
            prim: primTimings.default,
            middle: middleTimings.default,
            sec: secTimings.default
          }
        };
        
        await saveTimingsToIDB();
      } else {
        // Check if we need to migrate from old structure
        if (savedTimings.default || savedTimings.autoApplyToAll) {
          // Migrate old structure to new
          timingsConfig.value = migrateOldTimings(savedTimings);
          await saveTimingsToIDB();
        } else {
          timingsConfig.value = savedTimings;
        }
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
      else currentViewMode.value = 'tablev3';

      const savedShowTodayOnly = await db.getSetting('showTodayOnly');
      if (savedShowTodayOnly != null) showTodayOnly.value = !!savedShowTodayOnly;

      const savedTestTime = await db.getSetting('testTimeConfig');
      if (savedTestTime) {
        testTimeEnabled.value = !!savedTestTime.enabled;
        testDayIndex.value = Number(savedTestTime.dayIndex ?? 0);
        testTimeValue.value = savedTestTime.timeValue || '09:00';
      }

      // Load weekly plans
      const savedWeeklyPlans = await db.getSetting('weeklyPlans');
      if (savedWeeklyPlans && Object.keys(savedWeeklyPlans).length > 0) {
        weeklyPlans.value = savedWeeklyPlans;
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
        if (typeof pullResult.data.showTodayOnly !== 'undefined') {
          showTodayOnly.value = !!pullResult.data.showTodayOnly;
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
    weeklyPlans: readonly(weeklyPlans),
    schoolTimetable: readonly(schoolTimetable),
    selectedStage: readonly(selectedStage),
    selectedDay: readonly(selectedDay),
    currentViewMode: readonly(currentViewMode),
    showTodayOnly: readonly(showTodayOnly),
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
    setShowTodayOnly,
    setScheduleData,
    setSchoolTimetable,
    setTestTimeConfig,
    setWeeklyPlans,
    setWeekTitle,
    updateWeeklyPlanEntry,

    // DB access (for DataManager)
    db,

    // Lifecycle
    initialize,
    destroy,
    pushCloudSnapshot,

    // Helpers
    dayIndexToId,
    dayIdToName,
    getWeekKey,
    getWeekTitle,
    getWeeklyPlanEntry,
    getScheduleClasses,
    getScheduledSlotsForClass,
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
