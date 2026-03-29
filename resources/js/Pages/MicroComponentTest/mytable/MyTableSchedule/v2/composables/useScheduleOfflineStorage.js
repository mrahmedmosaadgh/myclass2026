import { ref, watch } from 'vue';
import { useOfflineDB } from './useOfflineDB';

export function useScheduleOfflineStorage() {
  const { 
    STORES, 
    initDB, 
    saveData, 
    getData, 
    getAllData, 
    deleteData,
    queryByIndex,
    addToSyncQueue,
    exportDatabase,
    importDatabase,
    getStorageInfo
  } = useOfflineDB();

  const isInitialized = ref(false);
  const lastSyncTime = ref(null);
  const syncStatus = ref('idle'); // idle, syncing, success, error

  // Initialize on first use
  const initialize = async () => {
    if (!isInitialized.value) {
      await initDB();
      isInitialized.value = true;
      await loadLastSyncTime();
    }
  };

  // Personal Schedule Methods
  const savePersonalSchedule = async (scheduleData) => {
    await initialize();
    
    const dataToSave = {
      id: 'personal-schedule',
      data: scheduleData,
      updatedAt: Date.now()
    };

    await saveData(STORES.PERSONAL_SCHEDULE, dataToSave);
    await addToSyncQueue('update_personal_schedule', dataToSave);
    
    return dataToSave;
  };

  const getPersonalSchedule = async () => {
    await initialize();
    const result = await getData(STORES.PERSONAL_SCHEDULE, 'personal-schedule');
    return result?.data || null;
  };

  // School Timetable Methods
  const saveSchoolTimetable = async (timetableData) => {
    await initialize();
    
    const dataToSave = {
      id: 'school-timetable',
      data: timetableData,
      updatedAt: Date.now()
    };

    await saveData(STORES.SCHOOL_TIMETABLE, dataToSave);
    await addToSyncQueue('update_school_timetable', dataToSave);
    
    return dataToSave;
  };

  const getSchoolTimetable = async () => {
    await initialize();
    const result = await getData(STORES.SCHOOL_TIMETABLE, 'school-timetable');
    return result?.data || null;
  };

  const saveSchoolEntry = async (entry) => {
    await initialize();
    
    const entryToSave = {
      ...entry,
      updatedAt: Date.now()
    };

    const id = await saveData(STORES.SCHOOL_TIMETABLE, entryToSave);
    await addToSyncQueue('update_school_entry', entryToSave);
    
    return id;
  };

  const getSchoolEntriesByStage = async (stage) => {
    await initialize();
    return await queryByIndex(STORES.SCHOOL_TIMETABLE, 'stage', stage);
  };

  const getSchoolEntriesByTeacher = async (teacher) => {
    await initialize();
    return await queryByIndex(STORES.SCHOOL_TIMETABLE, 'teacher', teacher);
  };

  // Timings Methods
  const saveTimings = async (stage, day, timingsData) => {
    await initialize();
    
    const dataToSave = {
      id: `timing-${stage}-${day}`,
      stage,
      day,
      data: timingsData,
      updatedAt: Date.now()
    };

    await saveData(STORES.TIMINGS, dataToSave);
    await addToSyncQueue('update_timings', dataToSave);
    
    return dataToSave;
  };

  const getTimings = async (stage, day) => {
    await initialize();
    const result = await getData(STORES.TIMINGS, `timing-${stage}-${day}`);
    return result?.data || null;
  };

  const getAllTimings = async () => {
    await initialize();
    const allTimings = await getAllData(STORES.TIMINGS);
    
    const timingsMap = {};
    allTimings.forEach(item => {
      if (!timingsMap[item.stage]) {
        timingsMap[item.stage] = {};
      }
      timingsMap[item.stage][item.day] = item.data;
    });
    
    return timingsMap;
  };

  const saveGlobalTimings = async (timingsData) => {
    await initialize();
    
    const dataToSave = {
      id: 'global-timings',
      data: timingsData,
      updatedAt: Date.now()
    };

    await saveData(STORES.TIMINGS, dataToSave);
    await addToSyncQueue('update_global_timings', dataToSave);
    
    return dataToSave;
  };

  const getGlobalTimings = async () => {
    await initialize();
    const result = await getData(STORES.TIMINGS, 'global-timings');
    return result?.data || null;
  };

  // App Settings Methods
  const saveSetting = async (key, value) => {
    await initialize();
    
    const dataToSave = {
      key,
      value,
      updatedAt: Date.now()
    };

    await saveData(STORES.APP_SETTINGS, dataToSave);
    
    return dataToSave;
  };

  const getSetting = async (key, defaultValue = null) => {
    await initialize();
    const result = await getData(STORES.APP_SETTINGS, key);
    return result?.value ?? defaultValue;
  };

  const getAllSettings = async () => {
    await initialize();
    const allSettings = await getAllData(STORES.APP_SETTINGS);
    
    const settingsMap = {};
    allSettings.forEach(item => {
      settingsMap[item.key] = item.value;
    });
    
    return settingsMap;
  };

  // Sync Methods
  const loadLastSyncTime = async () => {
    const syncTime = await getSetting('lastSyncTime');
    if (syncTime) {
      lastSyncTime.value = new Date(syncTime);
    }
  };

  const updateLastSyncTime = async () => {
    const now = Date.now();
    await saveSetting('lastSyncTime', now);
    lastSyncTime.value = new Date(now);
  };

  // Export/Import Methods
  const exportAllData = async () => {
    await initialize();
    return await exportDatabase();
  };

  const importAllData = async (data) => {
    await initialize();
    await importDatabase(data);
    await updateLastSyncTime();
  };

  // Storage Info
  const getStorageStats = async () => {
    await initialize();
    return await getStorageInfo();
  };

  // Auto-save watcher helper
  const createAutoSave = (dataRef, saveFunction, debounceMs = 1000) => {
    let timeoutId = null;

    const stopWatch = watch(
      dataRef,
      (newValue) => {
        if (timeoutId) clearTimeout(timeoutId);
        
        timeoutId = setTimeout(async () => {
          try {
            await saveFunction(newValue);
            console.log('[OfflineStorage] Auto-saved data');
          } catch (error) {
            console.error('[OfflineStorage] Auto-save failed:', error);
          }
        }, debounceMs);
      },
      { deep: true }
    );

    return stopWatch;
  };

  return {
    isInitialized,
    lastSyncTime,
    syncStatus,
    initialize,
    
    // Personal Schedule
    savePersonalSchedule,
    getPersonalSchedule,
    
    // School Timetable
    saveSchoolTimetable,
    getSchoolTimetable,
    saveSchoolEntry,
    getSchoolEntriesByStage,
    getSchoolEntriesByTeacher,
    
    // Timings
    saveTimings,
    getTimings,
    getAllTimings,
    saveGlobalTimings,
    getGlobalTimings,
    
    // Settings
    saveSetting,
    getSetting,
    getAllSettings,
    
    // Sync
    updateLastSyncTime,
    
    // Export/Import
    exportAllData,
    importAllData,
    
    // Storage Info
    getStorageStats,
    
    // Utilities
    createAutoSave
  };
}
