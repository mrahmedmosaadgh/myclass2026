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

  const clone = (value) => JSON.parse(JSON.stringify(value));

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
      isExporting.value = true;
      exportProgress.value = 0;

      const scheduleData = clone(personalScheduleData);
      const timingData = clone(personalTimingData);

      exportProgress.value = 25;

      // Load view preferences from localStorage
      const viewPreferences = {
        viewMode: localStorage.getItem('schedule-app-view-mode'),
        isShowingAllDays: localStorage.getItem('schedule-show-all-days'),
        notifications: localStorage.getItem('notifications-enabled'),
        customSettings: localStorage.getItem('schedule-custom-settings')
      };

      exportProgress.value = 50;

      const exportData = {
        type: 'personal_schedule',
        version: '2.0',
        timestamp: new Date().toISOString(),
        namecode: namecode || 'user',
        data: {
          schedule: scheduleData,
          timings: timingData,
          preferences: viewPreferences
        },
        metadata: {
          totalDays: scheduleData?.length || 0,
          totalPeriods: timingData?.length || 0,
          exportedAt: new Date().toISOString(),
          appVersion: 'Schedule App V2'
        }
      };

      exportProgress.value = 75;

      // Download file
      const filename = generateFilename('personal_schedule', namecode);
      const size = downloadJSON(exportData, filename);

      exportProgress.value = 100;
      return { success: true, filename, data: exportData, size };

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
      isExporting.value = true;
      exportProgress.value = 0;

      const schoolData = clone(masterTimetableData);

      exportProgress.value = 25;

      const timingData = clone(stageDayTimingsData);

      exportProgress.value = 50;

      // Load custom timing overrides from localStorage
      const customTimings = localStorage.getItem('school-timings-v2');
      const parsedCustomTimings = customTimings ? JSON.parse(customTimings) : null;

      exportProgress.value = 75;

      const exportData = {
        type: 'school_timetable',
        version: '2.0',
        timestamp: new Date().toISOString(),
        namecode: namecode || 'school',
        data: {
          stages: schoolData.stages,
          defaultTimings: timingData.default,
          customTimings: parsedCustomTimings,
          overrides: timingData.overrides
        },
        metadata: {
          totalStages: Object.keys(schoolData.stages || {}).length,
          totalTeachers: countTotalTeachers(schoolData.stages),
          totalDays: countTotalDays(schoolData.stages),
          exportedAt: new Date().toISOString(),
          appVersion: 'Schedule App V2'
        }
      };

      // Download file
      const filename = generateFilename('school_timetable', namecode);
      const size = downloadJSON(exportData, filename);

      exportProgress.value = 100;
      return { success: true, filename, data: exportData, size };

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
      const settingsData = {
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

      const filename = generateFilename('app_settings', namecode);
      const size = downloadJSON(settingsData, filename);

      return { success: true, filename, data: settingsData, size };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };

  // Import personal schedule data
  const importPersonalSchedule = async (file) => {
    try {
      isImporting.value = true;
      importProgress.value = 0;

      const jsonData = await readFileAsJSON(file);
      importProgress.value = 25;

      // Validate data structure
      if (jsonData.type !== 'personal_schedule') {
        throw new Error('Invalid file format. Expected personal_schedule data.');
      }

      if (!jsonData.data?.schedule || !jsonData.data?.timings) {
        throw new Error('Invalid data structure. Missing schedule or timings.');
      }

      importProgress.value = 50;

      // Here you would typically send data to server to update files
      // For now, we'll store in localStorage as backup
      localStorage.setItem('imported-personal-schedule', JSON.stringify(jsonData.data.schedule));
      localStorage.setItem('imported-personal-timings', JSON.stringify(jsonData.data.timings));

      // Import preferences
      if (jsonData.data.preferences) {
        Object.entries(jsonData.data.preferences).forEach(([key, value]) => {
          if (value !== null && value !== undefined) {
            localStorage.setItem(key, value);
          }
        });
      }

      importProgress.value = 100;

      return {
        success: true,
        message: 'Personal schedule imported successfully',
        imported: {
          schedule: jsonData.data.schedule,
          timings: jsonData.data.timings,
          preferences: jsonData.data.preferences
        }
      };

    } catch (error) {
      console.error('Import failed:', error);
      return { success: false, error: error.message };
    } finally {
      isImporting.value = false;
      importProgress.value = 0;
    }
  };

  // Import school timetable data
  const importSchoolTimetable = async (file) => {
    try {
      isImporting.value = true;
      importProgress.value = 0;

      const jsonData = await readFileAsJSON(file);
      importProgress.value = 25;

      // Validate data structure
      if (jsonData.type !== 'school_timetable') {
        throw new Error('Invalid file format. Expected school_timetable data.');
      }

      if (!jsonData.data?.stages) {
        throw new Error('Invalid data structure. Missing stages data.');
      }

      importProgress.value = 50;

      // Store imported data in localStorage
      localStorage.setItem('imported-school-timetable', JSON.stringify(jsonData.data));

      // Import custom timings
      if (jsonData.data.customTimings) {
        localStorage.setItem('school-timings-v2', JSON.stringify(jsonData.data.customTimings));
      }

      importProgress.value = 100;

      return {
        success: true,
        message: 'School timetable imported successfully',
        imported: {
          stages: jsonData.data.stages,
          timings: jsonData.data.defaultTimings,
          customTimings: jsonData.data.customTimings
        }
      };

    } catch (error) {
      console.error('Import failed:', error);
      return { success: false, error: error.message };
    } finally {
      isImporting.value = false;
      importProgress.value = 0;
    }
  };

  // Import app settings
  const importAppSettings = async (file) => {
    try {
      const jsonData = await readFileAsJSON(file);

      if (jsonData.type !== 'app_settings') {
        throw new Error('Invalid file format. Expected app_settings data.');
      }

      // Import settings to localStorage
      if (jsonData.data) {
        Object.entries(jsonData.data).forEach(([key, value]) => {
          if (typeof value === 'object' && value !== null) {
            localStorage.setItem(key, JSON.stringify(value));
          } else if (value !== null && value !== undefined) {
            localStorage.setItem(key, value);
          }
        });
      }

      return {
        success: true,
        message: 'App settings imported successfully',
        imported: jsonData.data
      };

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
        case 'app_settings':
          return await importAppSettings(file);
        default:
          throw new Error('Unknown file type. Supported types: personal_schedule, school_timetable, app_settings');
      }
    } catch (error) {
      return { success: false, error: error.message };
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
    
    // Import functions
    importPersonalSchedule,
    importSchoolTimetable,
    importAppSettings,
    importFromFile,
    
    // Utilities
    generateFilename,
    generateTimestamp
  };
}
