import { ref } from 'vue';
 import personalScheduleData from '../schedule_data.json';
 import personalTimingData from '../schedule_timing.json';
 import masterTimetableData from '../data/master_timetable_data.json';
 import stageDayTimingsData from '../data/stage_day_timings.json';

export function useDataImportExport() {
  const isImporting = ref(false);
  const isExporting = ref(false);
  const importProgress = ref(0);
  const exportProgress = ref(0);
  const stageOptions = ['prim', 'middle', 'sec'];
  const dayOptions = ['d1', 'd2', 'd3', 'd4', 'd5', 'd6'];
  const importTargets = [
    {
      id: 'personal_schedule',
      label: 'Personal Schedule',
      description: 'Schedule, timings, and personal preferences',
      example: 'Exported personal_schedule backup or raw { schedule, timings } JSON'
    },
    {
      id: 'school_timetable',
      label: 'School Timetable',
      description: 'Stages, teacher timetable, and school timing overrides',
      example: 'Exported school_timetable backup or raw { stages } JSON'
    },
    {
      id: 'stage_day_timings',
      label: 'Timing Only',
      description: 'Default timings and custom stage/day timing overrides',
      example: 'Exported timings object or raw { default, overrides } JSON'
    },
    {
      id: 'app_settings',
      label: 'App Settings',
      description: 'View mode, preferences, and saved app configuration',
      example: 'Exported app_settings backup or raw settings object'
    }
  ];

  const clone = (value) => JSON.parse(JSON.stringify(value));

  const createBaseTimingConfig = () => clone(stageDayTimingsData);

  const normalizeTimingConfig = (value) => {
    const base = createBaseTimingConfig();
    const source = isPlainObject(value) ? value : {};

    if (Array.isArray(source.default)) {
      base.default = clone(source.default);
    }

    Object.entries(source.overrides || {}).forEach(([stageKey, stageValue]) => {
      if (!base.overrides[stageKey]) {
        base.overrides[stageKey] = { default: null, days: { d1: null, d2: null, d3: null, d4: null, d5: null, d6: null } };
      }

      if (Array.isArray(stageValue?.default)) {
        base.overrides[stageKey].default = clone(stageValue.default);
      }

      Object.entries(stageValue?.days || {}).forEach(([dayKey, dayValue]) => {
        base.overrides[stageKey].days[dayKey] = Array.isArray(dayValue) ? clone(dayValue) : dayValue;
      });
    });

    return base;
  };

  const loadStoredJSON = (key, fallback = null) => {
    try {
      const raw = localStorage.getItem(key);
      return raw ? JSON.parse(raw) : fallback;
    } catch (error) {
      return fallback;
    }
  };

  const getCurrentTimingConfig = () => {
    return normalizeTimingConfig(loadStoredJSON('school-timings-v2', createBaseTimingConfig()));
  };

  const getCurrentPersonalData = () => {
    return {
      schedule: loadStoredJSON('imported-personal-schedule', clone(personalScheduleData)),
      timings: loadStoredJSON('imported-personal-timings', clone(personalTimingData))
    };
  };

  const getCurrentSchoolData = () => {
    const importedSchool = loadStoredJSON('imported-school-timetable', null);
    const timingConfig = getCurrentTimingConfig();

    return {
      stages: importedSchool?.stages || clone(masterTimetableData.stages),
      defaultTimings: timingConfig.default,
      customTimings: timingConfig,
      overrides: timingConfig.overrides
    };
  };

  const isPlainObject = (value) => {
    return !!value && typeof value === 'object' && !Array.isArray(value);
  };

  const isTimeString = (value) => {
    return typeof value === 'string' && /^([01]\d|2[0-3]):([0-5]\d)$/.test(value);
  };

  const validateTimingSlots = (slots, label = 'timings') => {
    if (!Array.isArray(slots) || slots.length === 0) {
      throw new Error(`Invalid ${label}. Expected a non-empty array of timing slots.`);
    }

    slots.forEach((slot, index) => {
      if (!isPlainObject(slot)) {
        throw new Error(`Invalid ${label} slot at position ${index + 1}.`);
      }

      if (!('id' in slot) || typeof slot.title !== 'string' || !isTimeString(slot.start) || !isTimeString(slot.end)) {
        throw new Error(`Invalid ${label} slot "${slot.title || index + 1}". Each slot needs id, title, start, and end.`);
      }
    });
  };

  const validatePersonalPayload = (jsonData) => {
    const data = jsonData?.type === 'personal_schedule' ? jsonData.data : jsonData;

    if (!isPlainObject(data)) {
      throw new Error('Invalid personal schedule data. Expected an object payload.');
    }

    if (!Array.isArray(data.schedule)) {
      throw new Error('Invalid personal schedule data. Missing schedule array.');
    }

    validateTimingSlots(data.timings, 'personal timings');

    data.schedule.forEach((day, index) => {
      if (!isPlainObject(day) || typeof day.day !== 'string' || typeof day.dayIndex !== 'number' || !Array.isArray(day.classes)) {
        throw new Error(`Invalid personal day entry at position ${index + 1}.`);
      }
    });

    return {
      schedule: data.schedule,
      timings: data.timings,
      preferences: isPlainObject(data.preferences) ? data.preferences : {}
    };
  };

  const validateSchoolPayload = (jsonData) => {
    const data = jsonData?.type === 'school_timetable' ? jsonData.data : jsonData;

    if (!isPlainObject(data) || !isPlainObject(data.stages)) {
      throw new Error('Invalid school timetable data. Missing stages object.');
    }

    Object.entries(data.stages).forEach(([stageKey, stageValue]) => {
      if (!isPlainObject(stageValue) || !isPlainObject(stageValue.days)) {
        throw new Error(`Invalid school timetable stage "${stageKey}". Missing days object.`);
      }
    });

    if (data.customTimings) {
      validateTimingConfigPayload(data.customTimings);
    }

    if (data.defaultTimings) {
      validateTimingSlots(data.defaultTimings, 'school default timings');
    }

    return {
      stages: data.stages,
      defaultTimings: Array.isArray(data.defaultTimings) ? data.defaultTimings : clone(stageDayTimingsData.default),
      customTimings: data.customTimings || null,
      overrides: isPlainObject(data.overrides) ? data.overrides : {}
    };
  };

  const validateTimingConfigPayload = (jsonData) => {
    if (jsonData?.type === 'stage_day_timings' && isPlainObject(jsonData.data) && typeof jsonData.data.mode === 'string') {
      const mode = jsonData.data.mode;

      if (mode === 'full_config') {
        return validateTimingConfigPayload(jsonData.data.config);
      }

      if (mode === 'same_for_all') {
        validateTimingSlots(jsonData.data.default, 'same for all timings');
        return {
          mode,
          default: jsonData.data.default
        };
      }

      if (mode === 'stage_default') {
        if (!stageOptions.includes(jsonData.data.stage)) {
          throw new Error('Invalid timing stage.');
        }

        validateTimingSlots(jsonData.data.default, `${jsonData.data.stage} default timings`);
        return {
          mode,
          stage: jsonData.data.stage,
          default: jsonData.data.default
        };
      }

      if (mode === 'stage_day') {
        if (!stageOptions.includes(jsonData.data.stage)) {
          throw new Error('Invalid timing stage.');
        }

        if (!dayOptions.includes(jsonData.data.day)) {
          throw new Error('Invalid timing day.');
        }

        validateTimingSlots(jsonData.data.slots, `${jsonData.data.stage} ${jsonData.data.day} timings`);
        return {
          mode,
          stage: jsonData.data.stage,
          day: jsonData.data.day,
          slots: jsonData.data.slots
        };
      }

      throw new Error('Invalid timing import mode.');
    }

    const data = jsonData?.type === 'stage_day_timings'
      ? jsonData.data
      : jsonData?.default || jsonData?.overrides
        ? jsonData
        : jsonData?.data?.default || jsonData?.data?.overrides
          ? jsonData.data
          : null;

    if (!isPlainObject(data)) {
      throw new Error('Invalid timing data. Expected an object with default and overrides.');
    }

    validateTimingSlots(data.default, 'default timings');

    if (!isPlainObject(data.overrides)) {
      throw new Error('Invalid timing data. Missing overrides object.');
    }

    Object.entries(data.overrides).forEach(([stageKey, stageValue]) => {
      if (!isPlainObject(stageValue)) {
        throw new Error(`Invalid timing override for stage "${stageKey}".`);
      }

      if (stageValue.default) {
        validateTimingSlots(stageValue.default, `${stageKey} default timings`);
      }

      if (stageValue.days && !isPlainObject(stageValue.days)) {
        throw new Error(`Invalid timing day overrides for stage "${stageKey}".`);
      }

      Object.entries(stageValue.days || {}).forEach(([dayKey, dayValue]) => {
        if (dayValue) {
          validateTimingSlots(dayValue, `${stageKey} ${dayKey} timings`);
        }
      });
    });

    return {
      mode: 'full_config',
      default: data.default,
      overrides: data.overrides
    };
  };

  const validateSettingsPayload = (jsonData) => {
    const data = jsonData?.type === 'app_settings' ? jsonData.data : jsonData;

    if (!isPlainObject(data)) {
      throw new Error('Invalid app settings data. Expected an object payload.');
    }

    if (data.customTimings) {
      try {
        const timingsValue = typeof data.customTimings === 'string' ? JSON.parse(data.customTimings) : data.customTimings;
        validateTimingConfigPayload(timingsValue);
      } catch (error) {
        throw new Error(`Invalid app settings custom timings. ${error.message}`);
      }
    }

    return data;
  };

  const applyPersonalPayload = (payload) => {
    localStorage.setItem('schedule-v4-personal-schedule', JSON.stringify(payload.schedule));
    localStorage.setItem('schedule-v4-personal-timings', JSON.stringify(payload.timings));

    Object.entries(payload.preferences || {}).forEach(([key, value]) => {
      if (value !== null && value !== undefined) {
        localStorage.setItem(`schedule-v4-${key}`, typeof value === 'string' ? value : JSON.stringify(value));
      }
    });

    return {
      schedule: payload.schedule,
      timings: payload.timings,
      preferences: payload.preferences
    };
  };

  const applySchoolPayload = (payload) => {
    localStorage.setItem('schedule-v4-school-timetable', JSON.stringify({
      stages: payload.stages,
      defaultTimings: payload.defaultTimings,
      customTimings: payload.customTimings,
      overrides: payload.overrides
    }));

    if (payload.customTimings) {
      localStorage.setItem('schedule-v4-school-timings', JSON.stringify(payload.customTimings));
    }

    return payload;
  };

  const applyTimingPayload = (payload) => {
    const current = getCurrentTimingConfig();

    if (payload.mode === 'same_for_all') {
      current.default = clone(payload.default);
      localStorage.setItem('schedule-v4-stage-timings', JSON.stringify(current));
      return current;
    }

    if (payload.mode === 'stage_default') {
      current.overrides[payload.stage].default = clone(payload.default);
      localStorage.setItem('schedule-v4-stage-timings', JSON.stringify(current));
      return current;
    }

    if (payload.mode === 'stage_day') {
      current.overrides[payload.stage].days[payload.day] = clone(payload.slots);
      localStorage.setItem('schedule-v4-stage-timings', JSON.stringify(current));
      return current;
    }

    const normalized = normalizeTimingConfig(payload);
    localStorage.setItem('schedule-v4-stage-timings', JSON.stringify(normalized));
    return normalized;
  };

  const buildExportPayload = async (target, options = {}) => {
    const { namecode = '', timingScope = 'full_config', stage = 'prim', day = 'd1' } = options;

    isExporting.value = true;
    exportProgress.value = 15;

    try {
      let exportData = null;
      let filenameType = target;

      if (target === 'personal_schedule') {
        const personalData = getCurrentPersonalData();
        const viewPreferences = {
          viewMode: localStorage.getItem('schedule-app-view-mode'),
          isShowingAllDays: localStorage.getItem('schedule-show-all-days'),
          notifications: localStorage.getItem('notifications-enabled'),
          customSettings: localStorage.getItem('schedule-custom-settings')
        };

        exportData = {
          type: 'personal_schedule',
          version: '2.0',
          timestamp: new Date().toISOString(),
          namecode: namecode || 'user',
          data: {
            schedule: personalData.schedule,
            timings: personalData.timings,
            preferences: viewPreferences
          },
          metadata: {
            totalDays: personalData.schedule?.length || 0,
            totalPeriods: personalData.timings?.length || 0,
            exportedAt: new Date().toISOString(),
            appVersion: 'Schedule App V2'
          }
        };
      }

      if (target === 'school_timetable') {
        const schoolData = getCurrentSchoolData();
        exportData = {
          type: 'school_timetable',
          version: '2.0',
          timestamp: new Date().toISOString(),
          namecode: namecode || 'school',
          data: {
            stages: schoolData.stages,
            defaultTimings: schoolData.defaultTimings,
            customTimings: schoolData.customTimings,
            overrides: schoolData.overrides
          },
          metadata: {
            totalStages: Object.keys(schoolData.stages || {}).length,
            totalTeachers: countTotalTeachers(schoolData.stages),
            totalDays: countTotalDays(schoolData.stages),
            exportedAt: new Date().toISOString(),
            appVersion: 'Schedule App V2'
          }
        };
      }

      if (target === 'app_settings') {
        exportData = {
          type: 'app_settings',
          version: '2.0',
          timestamp: new Date().toISOString(),
          namecode: namecode || 'settings',
          data: {
            viewMode: localStorage.getItem('schedule-app-view-mode'),
            notifications: Notification.permission,
            customTimings: localStorage.getItem('school-timings-v2'),
            userPreferences: {
              theme: localStorage.getItem('app-theme'),
              language: localStorage.getItem('app-language'),
              autoSync: localStorage.getItem('auto-sync-enabled')
            }
          },
          metadata: {
            exportedAt: new Date().toISOString(),
            appVersion: 'Schedule App V2'
          }
        };
      }

      if (target === 'stage_day_timings') {
        const timingConfig = getCurrentTimingConfig();
        filenameType = `timing_${timingScope}`;

        if (timingScope === 'same_for_all') {
          exportData = {
            type: 'stage_day_timings',
            version: '2.0',
            timestamp: new Date().toISOString(),
            namecode: namecode || 'timing',
            data: {
              mode: 'same_for_all',
              default: timingConfig.default
            },
            metadata: {
              scope: 'same_for_all',
              exportedAt: new Date().toISOString(),
              appVersion: 'Schedule App V2'
            }
          };
        }

        if (timingScope === 'stage_default') {
          exportData = {
            type: 'stage_day_timings',
            version: '2.0',
            timestamp: new Date().toISOString(),
            namecode: namecode || `${stage}_default`,
            data: {
              mode: 'stage_default',
              stage,
              default: timingConfig.overrides[stage]?.default || timingConfig.default
            },
            metadata: {
              scope: 'stage_default',
              stage,
              exportedAt: new Date().toISOString(),
              appVersion: 'Schedule App V2'
            }
          };
        }

        if (timingScope === 'stage_day') {
          exportData = {
            type: 'stage_day_timings',
            version: '2.0',
            timestamp: new Date().toISOString(),
            namecode: namecode || `${stage}_${day}`,
            data: {
              mode: 'stage_day',
              stage,
              day,
              slots: timingConfig.overrides[stage]?.days?.[day] || timingConfig.overrides[stage]?.default || timingConfig.default
            },
            metadata: {
              scope: 'stage_day',
              stage,
              day,
              exportedAt: new Date().toISOString(),
              appVersion: 'Schedule App V2'
            }
          };
        }

        if (timingScope === 'full_config' || !exportData) {
          exportData = {
            type: 'stage_day_timings',
            version: '2.0',
            timestamp: new Date().toISOString(),
            namecode: namecode || 'timing_full',
            data: {
              mode: 'full_config',
              config: timingConfig
            },
            metadata: {
              scope: 'full_config',
              exportedAt: new Date().toISOString(),
              appVersion: 'Schedule App V2'
            }
          };
        }
      }

      if (!exportData) {
        throw new Error('Unsupported export target.');
      }

      exportProgress.value = 100;

      const filename = generateFilename(filenameType, namecode);
      const jsonString = JSON.stringify(exportData, null, 2);

      return {
        success: true,
        filename,
        data: exportData,
        jsonString,
        size: new Blob([jsonString], { type: 'application/json' }).size
      };
    } catch (error) {
      return { success: false, error: error.message };
    } finally {
      isExporting.value = false;
      exportProgress.value = 0;
    }
  };

  const downloadExportPayload = (prepared) => {
    if (!prepared?.data || !prepared?.filename) {
      throw new Error('Nothing to download.');
    }

    return downloadJSON(prepared.data, prepared.filename);
  };

  const applySettingsPayload = (payload) => {
    Object.entries(payload).forEach(([key, value]) => {
      if (value === null || value === undefined) {
        return;
      }

      if (key === 'customTimings') {
        const timingsValue = typeof value === 'string' ? value : JSON.stringify(value);
        localStorage.setItem('schedule-v4-stage-timings', timingsValue);
        return;
      }

      // Use schedule-v4 prefix for all settings
      const storageKey = key.startsWith('schedule-v4-') ? key : `schedule-v4-${key}`;
      localStorage.setItem(storageKey, typeof value === 'object' ? JSON.stringify(value) : value);
    });

    return payload;
  };

  const runImportByTarget = async (target, jsonData, options = {}) => {
    const { validateOnly = false } = options;
    isImporting.value = true;
    importProgress.value = 20;

    try {
      switch (target) {
        case 'personal_schedule': {
          const payload = validatePersonalPayload(jsonData);
          importProgress.value = 60;
          return {
            success: true,
            message: validateOnly ? 'Personal schedule JSON is valid' : 'Personal schedule imported successfully',
            imported: validateOnly ? payload : applyPersonalPayload(payload)
          };
        }
        case 'school_timetable': {
          const payload = validateSchoolPayload(jsonData);
          importProgress.value = 60;
          return {
            success: true,
            message: validateOnly ? 'School timetable JSON is valid' : 'School timetable imported successfully',
            imported: validateOnly ? payload : applySchoolPayload(payload)
          };
        }
        case 'stage_day_timings': {
          const payload = validateTimingConfigPayload(jsonData);
          importProgress.value = 60;
          return {
            success: true,
            message: validateOnly ? 'Timing JSON is valid' : 'Timing data imported successfully',
            imported: validateOnly ? payload : applyTimingPayload(payload)
          };
        }
        case 'app_settings': {
          const payload = validateSettingsPayload(jsonData);
          importProgress.value = 60;
          return {
            success: true,
            message: validateOnly ? 'App settings JSON is valid' : 'App settings imported successfully',
            imported: validateOnly ? payload : applySettingsPayload(payload)
          };
        }
        default:
          throw new Error('Unknown import target.');
      }
    } finally {
      importProgress.value = 100;
      isImporting.value = false;
      if (!validateOnly) {
        window.dispatchEvent(new CustomEvent('schedule-v2-data-imported', { detail: { target } }));
      }
      setTimeout(() => {
        importProgress.value = 0;
      }, 150);
    }
  };

  // Generate timestamp for filename
  const generateTimestamp = () => {
    const now = new Date();
    return now.toISOString()
      .replace(/[:.]/g, '-')
      .replace('T', '_')
      .split('.')[0];
  };

  // Generate filename with namecode and timestamp
  const generateFilename = (dataType, namecode = '') => {
    const timestamp = generateTimestamp();
    const cleanNamecode = namecode ? `_${namecode.replace(/[^a-zA-Z0-9]/g, '_')}` : '';
    return `${dataType}${cleanNamecode}_${timestamp}.json`;
  };

  // Export personal schedule data
  const exportPersonalSchedule = async (namecode = '') => {
    try {
      const prepared = await buildExportPayload('personal_schedule', { namecode });
      if (!prepared.success) {
        throw new Error(prepared.error);
      }
      const size = downloadExportPayload(prepared);
      return { success: true, filename: prepared.filename, data: prepared.data, size };

    } catch (error) {
      console.error('Export failed:', error);
      return { success: false, error: error.message };
    } finally {
      isExporting.value = false;
      exportProgress.value = 0;
    }
  };

  // Export school timetable data
  const exportSchoolTimetable = async (namecode = '') => {
    try {
      const prepared = await buildExportPayload('school_timetable', { namecode });
      if (!prepared.success) {
        throw new Error(prepared.error);
      }
      const size = downloadExportPayload(prepared);
      return { success: true, filename: prepared.filename, data: prepared.data, size };

    } catch (error) {
      console.error('Export failed:', error);
      return { success: false, error: error.message };
    } finally {
      isExporting.value = false;
      exportProgress.value = 0;
    }
  };

  // Export all data (complete backup)
  const exportAllData = async (namecode = '') => {
    try {
      isExporting.value = true;
      exportProgress.value = 0;

      // Export personal schedule
      exportProgress.value = 20;
      const personalResult = await exportPersonalSchedule(namecode);
      if (!personalResult.success) throw personalResult.error;

      // Export school timetable
      exportProgress.value = 60;
      const schoolResult = await exportSchoolTimetable(namecode);
      if (!schoolResult.success) throw schoolResult.error;

      // Export app settings
      exportProgress.value = 80;
      const settingsData = await exportAppSettings(namecode);

      exportProgress.value = 100;

      return {
        success: true,
        files: [
          { filename: personalResult.filename, size: personalResult.size },
          { filename: schoolResult.filename, size: schoolResult.size },
          { filename: settingsData.filename, size: settingsData.size }
        ],
        message: 'All data exported successfully'
      };

    } catch (error) {
      console.error('Full export failed:', error);
      return { success: false, error: error.message };
    } finally {
      isExporting.value = false;
      exportProgress.value = 0;
    }
  };

  // Export app settings
  const exportAppSettings = async (namecode = '') => {
    try {
      const prepared = await buildExportPayload('app_settings', { namecode });
      if (!prepared.success) {
        throw new Error(prepared.error);
      }
      const size = downloadExportPayload(prepared);
      return { success: true, filename: prepared.filename, data: prepared.data, size };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };

  // Import personal schedule data
  const importPersonalSchedule = async (file) => {
    try {
      const jsonData = await readFileAsJSON(file);
      return await runImportByTarget('personal_schedule', jsonData);

    } catch (error) {
      console.error('Import failed:', error);
      return { success: false, error: error.message };
    }
  };

  // Import school timetable data
  const importSchoolTimetable = async (file) => {
    try {
      const jsonData = await readFileAsJSON(file);
      return await runImportByTarget('school_timetable', jsonData);

    } catch (error) {
      console.error('Import failed:', error);
      return { success: false, error: error.message };
    }
  };

  const importStageDayTimings = async (file) => {
    try {
      const jsonData = await readFileAsJSON(file);
      return await runImportByTarget('stage_day_timings', jsonData);
    } catch (error) {
      console.error('Import failed:', error);
      return { success: false, error: error.message };
    }
  };

  // Import app settings
  const importAppSettings = async (file) => {
    try {
      const jsonData = await readFileAsJSON(file);
      return await runImportByTarget('app_settings', jsonData);

    } catch (error) {
      console.error('Import failed:', error);
      return { success: false, error: error.message };
    }
  };

  // Auto-detect file type and import
  const importFromFile = async (file) => {
    try {
      const jsonData = await readFileAsJSON(file);
      
      switch (jsonData.type) {
        case 'personal_schedule':
          return await importPersonalSchedule(file);
        case 'school_timetable':
          return await importSchoolTimetable(file);
        case 'stage_day_timings':
          return await importStageDayTimings(file);
        case 'app_settings':
          return await importAppSettings(file);
        default:
          throw new Error('Unknown file type. Supported types: personal_schedule, school_timetable, stage_day_timings, app_settings');
      }
    } catch (error) {
      return { success: false, error: error.message };
    }
  };

  const importFromText = async (text, target, options = {}) => {
    try {
      if (!target) {
        throw new Error('Please choose what the pasted data is for.');
      }

      const jsonData = JSON.parse(text);
      return await runImportByTarget(target, jsonData, options);
    } catch (error) {
      return { success: false, error: error.message || 'Invalid pasted JSON' };
    }
  };

  // Helper functions
  const readFileAsJSON = (file) => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        try {
          const json = JSON.parse(e.target.result);
          resolve(json);
        } catch (error) {
          reject(new Error('Invalid JSON file'));
        }
      };
      reader.onerror = () => reject(new Error('Failed to read file'));
      reader.readAsText(file);
    });
  };

  const downloadJSON = (data, filename) => {
    const jsonString = JSON.stringify(data, null, 2);
    const blob = new Blob([jsonString], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    return blob.size;
  };

  const countTotalTeachers = (stages) => {
    const teacherIds = new Set();
    Object.values(stages).forEach(stage => {
      Object.values(stage.days).forEach(day => {
        day.teachers.forEach(teacher => {
          teacherIds.add(teacher.id);
        });
      });
    });
    return teacherIds.size;
  };

  const countTotalDays = (stages) => {
    const dayIds = new Set();
    Object.values(stages).forEach(stage => {
      Object.keys(stage.days).forEach(dayId => {
        dayIds.add(dayId);
      });
    });
    return dayIds.size;
  };

  return {
    // State
    isImporting,
    isExporting,
    importProgress,
    exportProgress,
    
    // Export functions
    exportPersonalSchedule,
    exportSchoolTimetable,
    exportAllData,
    exportAppSettings,
    buildExportPayload,
    downloadExportPayload,
    
    // Import functions
    importPersonalSchedule,
    importSchoolTimetable,
    importStageDayTimings,
    importAppSettings,
    importFromFile,
    importFromText,
    importTargets,
    stageOptions,
    dayOptions,
    
    // Utilities
    generateFilename,
    generateTimestamp
  };
}
