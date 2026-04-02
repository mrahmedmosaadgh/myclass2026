import { ref, computed, watch } from 'vue';
 import masterTimetableData from '../data/master_timetable_data.json';
 import stageDayTimingsData from '../data/stage_day_timings.json';

// Default timing template
const DEFAULT_TIMING = [
  { id: 1, title: 'Period 1', type: 'lesson', start: '09:00', end: '09:30' },
  { id: 2, title: 'Period 2', type: 'lesson', start: '09:30', end: '10:00' },
  { id: 'b1', title: 'First Break', type: 'break', start: '10:00', end: '10:30' },
  { id: 3, title: 'Period 3', type: 'lesson', start: '10:30', end: '11:00' },
  { id: 4, title: 'Period 4', type: 'lesson', start: '11:00', end: '11:30' },
  { id: 'b2', title: 'Second Break', type: 'break', start: '11:30', end: '12:00' },
  { id: 5, title: 'Period 5', type: 'lesson', start: '12:00', end: '12:25' },
  { id: 6, title: 'Period 6', type: 'lesson', start: '12:25', end: '12:50' }
];

export function useSchoolTimetable(selectedStage, selectedDay) {
  // Reactive state
  const teachers = ref([]);
  const timings = ref({
    default: DEFAULT_TIMING,
    overrides: {}
  });
  const isLoading = ref(false);
  const error = ref(null);
  const currentPeriodProgress = ref(0);

  const clone = (value) => JSON.parse(JSON.stringify(value));

  // Load data from local JSON files
  const loadLocalData = async () => {
    try {
      isLoading.value = true;
      error.value = null;

      const teachersData = clone(masterTimetableData);
      
      // Extract teachers list
      const teachersMap = new Map();
      Object.keys(teachersData.stages).forEach(stageKey => {
        const stage = teachersData.stages[stageKey];
        Object.keys(stage.days).forEach(dayKey => {
          const day = stage.days[dayKey];
          day.teachers.forEach(teacher => {
            const existingTeacher = teachersMap.get(teacher.id);
            if (!existingTeacher) {
              teachersMap.set(teacher.id, {
                id: teacher.id,
                name: teacher.name,
                assignments: clone(teacher.assignments || {})
              });
            } else {
              existingTeacher.assignments = {
                ...existingTeacher.assignments,
                ...clone(teacher.assignments || {})
              };
            }
          });
        });
      });
      
      teachers.value = Array.from(teachersMap.values());
      timings.value = clone(stageDayTimingsData || {
        default: DEFAULT_TIMING,
        overrides: {}
      });

    } catch (err) {
      console.error('Error loading local data:', err);
      error.value = err.message;
      
      // Fallback to minimal data
      teachers.value = [
        { id: 't1', name: 'Sample Teacher', assignments: {} },
        { id: 't2', name: 'Another Teacher', assignments: {} }
      ];
      timings.value = {
        default: DEFAULT_TIMING,
        overrides: {}
      };
    } finally {
      isLoading.value = false;
    }
  };

  // Load data from API (optional)
  const loadApiData = async () => {
    try {
      isLoading.value = true;
      error.value = null;

      const response = await fetch(`/api/school-timetable?stage=${selectedStage.value}&day=${selectedDay.value}`);
      if (!response.ok) {
        throw new Error('API request failed');
      }

      const data = await response.json();
      
      if (data.teachers) {
        teachers.value = data.teachers;
      }
      
      if (data.timings) {
        timings.value = data.timings;
      }

    } catch (err) {
      console.warn('API load failed, falling back to local data:', err);
      await loadLocalData();
    }
  };

  // Get resolved periods for current stage and day
  const resolvedPeriods = computed(() => {
    if (!timings.value) return DEFAULT_TIMING;

    // Check for specific stage + day override
    const stageOverride = timings.value.overrides?.[selectedStage.value];
    if (stageOverride?.days?.[selectedDay.value]) {
      return stageOverride.days[selectedDay.value] || DEFAULT_TIMING;
    }

    // Check for stage default override
    if (stageOverride?.default) {
      return stageOverride.default;
    }

    // Use global default
    return timings.value.default || DEFAULT_TIMING;
  });

  // Check if current stage/day has custom timing
  const hasCustomTiming = computed(() => {
    const stageOverride = timings.value.overrides?.[selectedStage.value];
    return !!(stageOverride?.days?.[selectedDay.value] || stageOverride?.default);
  });

  // Get timing for specific stage and day
  const getTimingForStageDay = (stage, day) => {
    const stageOverride = timings.value.overrides?.[stage];
    if (stageOverride?.days?.[day]) {
      return stageOverride.days[day];
    }
    if (stageOverride?.default) {
      return stageOverride.default;
    }
    return timings.value.default || DEFAULT_TIMING;
  };

  // Update current period progress
  const updateCurrentPeriodProgress = () => {
    const now = new Date();
    const currentTotalSecs = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();
    
    for (const period of resolvedPeriods.value) {
      const startParts = period.start.split(':').map(Number);
      const endParts = period.end.split(':').map(Number);
      const startSecs = startParts[0] * 3600 + startParts[1] * 60;
      const endSecs = endParts[0] * 3600 + endParts[1] * 60;
      
      if (currentTotalSecs >= startSecs && currentTotalSecs < endSecs) {
        const totalDuration = endSecs - startSecs;
        const elapsed = currentTotalSecs - startSecs;
        currentPeriodProgress.value = (elapsed / totalDuration) * 100;
        return;
      }
    }
    
    currentPeriodProgress.value = 0;
  };

  // Main data loading function
  const loadData = async () => {
    await loadLocalData();
  };

  // Save timings to localStorage (for persistence)
  const saveTimingsToStorage = () => {
    try {
      localStorage.setItem('school-timings-v2', JSON.stringify(timings.value));
    } catch (err) {
      console.warn('Failed to save timings to localStorage:', err);
    }
  };

  // Load timings from localStorage
  const loadTimingsFromStorage = () => {
    try {
      const stored = localStorage.getItem('school-timings-v2');
      if (stored) {
        const parsedTimings = JSON.parse(stored);
        timings.value = {
          default: parsedTimings.default || timings.value.default || DEFAULT_TIMING,
          overrides: {
            ...(timings.value.overrides || {}),
            ...(parsedTimings.overrides || {})
          }
        };
      }
    } catch (err) {
      console.warn('Failed to load timings from localStorage:', err);
    }
  };

  // Watch for timing changes and save to storage
  watch(timings, saveTimingsToStorage, { deep: true });

  // Watch for stage/day changes to update progress
  watch([selectedStage, selectedDay], () => {
    updateCurrentPeriodProgress();
  });

  return {
    // Data
    teachers,
    timings,
    resolvedPeriods,
    isLoading,
    error,
    currentPeriodProgress,
    
    // Computed
    hasCustomTiming,
    
    // Methods
    loadData,
    loadTimingsFromStorage,
    getTimingForStageDay,
    updateCurrentPeriodProgress,
    
    // Constants
    DEFAULT_TIMING
  };
}
