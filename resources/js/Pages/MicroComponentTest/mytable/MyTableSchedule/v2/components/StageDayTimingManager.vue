<template>
  <div class="timing-manager-overlay" @click="closeManager">
    <div class="timing-manager-sheet" @click.stop>
      <!-- Header -->
      <div class="sheet-header">
        <div class="header-info">
          <h3 class="sheet-title">Timing Settings</h3>
          <p class="sheet-subtitle">
            {{ getModeDescription() }}
          </p>
        </div>
        <button @click="closeManager" class="close-btn">×</button>
      </div>

      <!-- Mode Tabs -->
      <div class="mode-tabs">
        <button
          v-for="mode in modes"
          :key="mode.id"
          @click="selectMode(mode.id)"
          :class="['mode-tab', { active: currentMode === mode.id }]"
        >
          <span class="mode-icon">{{ mode.icon }}</span>
          <span class="mode-label">{{ mode.label }}</span>
        </button>
      </div>

      <!-- Mode Content -->
      <div class="mode-content">
        <!-- Global Mode -->
        <div v-if="currentMode === 'global'" class="global-mode">
          <div class="mode-description">
            <h4>Global Timing</h4>
            <p>Set timing that applies to ALL stages and ALL days</p>
          </div>
          
          <div class="timing-list">
            <div
              v-for="(period, index) in globalTimings"
              :key="period.id || index"
              class="timing-item"
            >
              <div class="period-info">
                <input
                  v-model="period.title"
                  type="text"
                  class="period-title-input"
                  placeholder="Period title"
                />
                <select v-model="period.type" class="period-type-select">
                  <option value="lesson">Lesson</option>
                  <option value="break">Break</option>
                  <option value="activity">Activity</option>
                </select>
              </div>
              
              <div class="time-inputs">
                <input
                  v-model="period.start"
                  type="time"
                  class="time-input"
                  @change="validateTime(period, 'start')"
                />
                <span class="time-separator">−</span>
                <input
                  v-model="period.end"
                  type="time"
                  class="time-input"
                  @change="validateTime(period, 'end')"
                />
              </div>
              
              <div class="period-actions">
                <button @click="movePeriodUp(index)" class="action-btn" :disabled="index === 0">
                  ↑
                </button>
                <button @click="movePeriodDown(index)" class="action-btn" :disabled="index === globalTimings.length - 1">
                  ↓
                </button>
                <button @click="removePeriod(index)" class="action-btn danger">
                  ×
                </button>
              </div>
            </div>
          </div>
          
          <div class="timing-actions">
            <button @click="addNewPeriod" class="add-btn">
              + Add Period
            </button>
            <button @click="resetToDefault" class="reset-btn">
              Reset to Default
            </button>
          </div>
        </div>

        <!-- Stage Override Mode -->
        <div v-else-if="currentMode === 'stage'" class="stage-mode">
          <div class="mode-description">
            <h4>Stage Override</h4>
            <p>Set custom timing for a specific stage (applies to all days in that stage)</p>
          </div>
          
          <div class="stage-selector">
            <label class="selector-label">Select Stage:</label>
            <select v-model="selectedStageForOverride" class="stage-select">
              <option value="">Use Global Timing</option>
              <option value="prim">Primary</option>
              <option value="middle">Middle</option>
              <option value="sec">Secondary</option>
            </select>
          </div>
          
          <div v-if="selectedStageForOverride" class="stage-timing-editor">
            <div class="timing-list">
              <div
                v-for="(period, index) in stageTimings"
                :key="period.id || index"
                class="timing-item"
              >
                <div class="period-info">
                  <input
                    v-model="period.title"
                    type="text"
                    class="period-title-input"
                    placeholder="Period title"
                  />
                  <select v-model="period.type" class="period-type-select">
                    <option value="lesson">Lesson</option>
                    <option value="break">Break</option>
                    <option value="activity">Activity</option>
                  </select>
                </div>
                
                <div class="time-inputs">
                  <input
                    v-model="period.start"
                    type="time"
                    class="time-input"
                    @change="validateTime(period, 'start')"
                  />
                  <span class="time-separator">−</span>
                  <input
                    v-model="period.end"
                    type="time"
                    class="time-input"
                    @change="validateTime(period, 'end')"
                  />
                </div>
                
                <div class="period-actions">
                  <button @click="moveStagePeriodUp(index)" class="action-btn" :disabled="index === 0">
                    ↑
                  </button>
                  <button @click="moveStagePeriodDown(index)" class="action-btn" :disabled="index === stageTimings.length - 1">
                    ↓
                  </button>
                  <button @click="removeStagePeriod(index)" class="action-btn danger">
                    ×
                  </button>
                </div>
              </div>
            </div>
            
            <div class="timing-actions">
              <button @click="addNewStagePeriod" class="add-btn">
                + Add Period
              </button>
              <button @click="clearStageTiming" class="reset-btn">
                Clear Stage Timing
              </button>
            </div>
          </div>
        </div>

        <!-- Stage + Day Override Mode -->
        <div v-else-if="currentMode === 'stageDay'" class="stage-day-mode">
          <div class="mode-description">
            <h4>Specific Day Override</h4>
            <p>Set custom timing for a specific stage on a specific day</p>
          </div>
          
          <div class="override-selectors">
            <div class="selector-group">
              <label class="selector-label">Stage:</label>
              <select v-model="selectedStageForSpecific" class="stage-select">
                <option value="">Select Stage</option>
                <option value="prim">Primary</option>
                <option value="middle">Middle</option>
                <option value="sec">Secondary</option>
              </select>
            </div>
            
            <div class="selector-group">
              <label class="selector-label">Day:</label>
              <select v-model="selectedDayForSpecific" class="day-select" :disabled="!selectedStageForSpecific">
                <option value="">Select Day</option>
                <option value="d1">Day 1</option>
                <option value="d2">Day 2</option>
                <option value="d3">Day 3</option>
                <option value="d4">Day 4</option>
                <option value="d5">Day 5</option>
                <option value="d6">Day 6</option>
              </select>
            </div>
          </div>
          
          <div v-if="selectedStageForSpecific && selectedDayForSpecific" class="specific-timing-editor">
            <div class="timing-list">
              <div
                v-for="(period, index) in specificTimings"
                :key="period.id || index"
                class="timing-item"
              >
                <div class="period-info">
                  <input
                    v-model="period.title"
                    type="text"
                    class="period-title-input"
                    placeholder="Period title"
                  />
                  <select v-model="period.type" class="period-type-select">
                    <option value="lesson">Lesson</option>
                    <option value="break">Break</option>
                    <option value="activity">Activity</option>
                  </select>
                </div>
                
                <div class="time-inputs">
                  <input
                    v-model="period.start"
                    type="time"
                    class="time-input"
                    @change="validateTime(period, 'start')"
                  />
                  <span class="time-separator">−</span>
                  <input
                    v-model="period.end"
                    type="time"
                    class="time-input"
                    @change="validateTime(period, 'end')"
                  />
                </div>
                
                <div class="period-actions">
                  <button @click="moveSpecificPeriodUp(index)" class="action-btn" :disabled="index === 0">
                    ↑
                  </button>
                  <button @click="moveSpecificPeriodDown(index)" class="action-btn" :disabled="index === specificTimings.length - 1">
                    ↓
                  </button>
                  <button @click="removeSpecificPeriod(index)" class="action-btn danger">
                    ×
                  </button>
                </div>
              </div>
            </div>
            
            <div class="timing-actions">
              <button @click="addNewSpecificPeriod" class="add-btn">
                + Add Period
              </button>
              <button @click="clearSpecificTiming" class="reset-btn">
                Clear This Timing
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="sheet-footer">
        <button @click="saveTimings" class="save-btn" :disabled="!hasChanges">
          Save Changes
        </button>
        <button @click="closeManager" class="cancel-btn">
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
  modelValue: { type: Object, required: true },
  stage: { type: String, required: true },
  day: { type: String, required: true }
});

const emit = defineEmits(['update:modelValue', 'close']);

// Component state
const currentMode = ref('global');
const selectedStageForOverride = ref('');
const selectedStageForSpecific = ref('');
const selectedDayForSpecific = ref('');
const hasChanges = ref(false);

// Mode definitions
const modes = [
  { id: 'global', label: 'Global', icon: '🌍' },
  { id: 'stage', label: 'Per Stage', icon: '🏫' },
  { id: 'stageDay', label: 'Specific Day', icon: '📅' }
];

// Default timing template
const defaultTiming = [
  { id: 1, title: 'Period 1', type: 'lesson', start: '09:00', end: '09:30' },
  { id: 2, title: 'Period 2', type: 'lesson', start: '09:30', end: '10:00' },
  { id: 'b1', title: 'First Break', type: 'break', start: '10:00', end: '10:30' },
  { id: 3, title: 'Period 3', type: 'lesson', start: '10:30', end: '11:00' },
  { id: 4, title: 'Period 4', type: 'lesson', start: '11:00', end: '11:30' },
  { id: 'b2', title: 'Second Break', type: 'break', start: '11:30', end: '12:00' },
  { id: 5, title: 'Period 5', type: 'lesson', start: '12:00', end: '12:25' },
  { id: 6, title: 'Period 6', type: 'lesson', start: '12:25', end: '12:50' }
];

// Reactive timing data
const globalTimings = ref([]);
const stageTimings = ref([]);
const specificTimings = ref([]);

// Computed properties
const timingsData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

// Methods
const getModeDescription = () => {
  switch (currentMode.value) {
    case 'global':
      return 'Edit timing that applies to all stages and days';
    case 'stage':
      return selectedStageForOverride.value 
        ? `Custom timing for ${selectedStageForOverride.value === 'prim' ? 'Primary' : selectedStageForOverride.value === 'middle' ? 'Middle' : 'Secondary'}`
        : 'Select a stage to customize';
    case 'stageDay':
      return selectedStageForSpecific.value && selectedDayForSpecific.value
        ? `Custom timing for ${selectedStageForSpecific.value === 'prim' ? 'Primary' : selectedStageForSpecific.value === 'middle' ? 'Middle' : 'Secondary'} - ${selectedDayForSpecific.value.toUpperCase()}`
        : 'Select stage and day to customize';
    default:
      return '';
  }
};

const selectMode = (modeId) => {
  currentMode.value = modeId;
  // Reset selections when switching modes
  if (modeId !== 'stage') selectedStageForOverride.value = '';
  if (modeId !== 'stageDay') {
    selectedStageForSpecific.value = '';
    selectedDayForSpecific.value = '';
  }
};

const validateTime = (period, field) => {
  // Basic time validation
  const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
  if (!timeRegex.test(period[field])) {
    // Reset to default if invalid
    if (field === 'start') period[field] = '09:00';
    if (field === 'end') period[field] = '09:30';
  }
  hasChanges.value = true;
};

const addNewPeriod = () => {
  const newId = Math.max(...globalTimings.value.map(p => typeof p.id === 'number' ? p.id : 0), 0) + 1;
  globalTimings.value.push({
    id: newId,
    title: `Period ${newId}`,
    type: 'lesson',
    start: '09:00',
    end: '09:30'
  });
  hasChanges.value = true;
};

const removePeriod = (index) => {
  globalTimings.value.splice(index, 1);
  hasChanges.value = true;
};

const movePeriodUp = (index) => {
  if (index > 0) {
    [globalTimings.value[index], globalTimings.value[index - 1]] = 
    [globalTimings.value[index - 1], globalTimings.value[index]];
    hasChanges.value = true;
  }
};

const movePeriodDown = (index) => {
  if (index < globalTimings.value.length - 1) {
    [globalTimings.value[index], globalTimings.value[index + 1]] = 
    [globalTimings.value[index + 1], globalTimings.value[index]];
    hasChanges.value = true;
  }
};

// Stage timing methods
const addNewStagePeriod = () => {
  const newId = Math.max(...stageTimings.value.map(p => typeof p.id === 'number' ? p.id : 0), 0) + 1;
  stageTimings.value.push({
    id: newId,
    title: `Period ${newId}`,
    type: 'lesson',
    start: '09:00',
    end: '09:30'
  });
  hasChanges.value = true;
};

const removeStagePeriod = (index) => {
  stageTimings.value.splice(index, 1);
  hasChanges.value = true;
};

const moveStagePeriodUp = (index) => {
  if (index > 0) {
    [stageTimings.value[index], stageTimings.value[index - 1]] = 
    [stageTimings.value[index - 1], stageTimings.value[index]];
    hasChanges.value = true;
  }
};

const moveStagePeriodDown = (index) => {
  if (index < stageTimings.value.length - 1) {
    [stageTimings.value[index], stageTimings.value[index + 1]] = 
    [stageTimings.value[index + 1], stageTimings.value[index]];
    hasChanges.value = true;
  }
};

const clearStageTiming = () => {
  stageTimings.value = [];
  hasChanges.value = true;
};

// Specific timing methods
const addNewSpecificPeriod = () => {
  const newId = Math.max(...specificTimings.value.map(p => typeof p.id === 'number' ? p.id : 0), 0) + 1;
  specificTimings.value.push({
    id: newId,
    title: `Period ${newId}`,
    type: 'lesson',
    start: '09:00',
    end: '09:30'
  });
  hasChanges.value = true;
};

const removeSpecificPeriod = (index) => {
  specificTimings.value.splice(index, 1);
  hasChanges.value = true;
};

const moveSpecificPeriodUp = (index) => {
  if (index > 0) {
    [specificTimings.value[index], specificTimings.value[index - 1]] = 
    [specificTimings.value[index - 1], specificTimings.value[index]];
    hasChanges.value = true;
  }
};

const moveSpecificPeriodDown = (index) => {
  if (index < specificTimings.value.length - 1) {
    [specificTimings.value[index], specificTimings.value[index + 1]] = 
    [specificTimings.value[index + 1], specificTimings.value[index]];
    hasChanges.value = true;
  }
};

const clearSpecificTiming = () => {
  specificTimings.value = [];
  hasChanges.value = true;
};

const resetToDefault = () => {
  globalTimings.value = JSON.parse(JSON.stringify(defaultTiming));
  hasChanges.value = true;
};

const saveTimings = () => {
  const newTimingsData = {
    ...timingsData.value,
    default: globalTimings.value,
    overrides: {
      ...timingsData.value.overrides,
      ...(selectedStageForOverride.value && {
        [selectedStageForOverride.value]: {
          default: stageTimings.value.length > 0 ? stageTimings.value : null,
          days: {
            ...timingsData.value.overrides?.[selectedStageForOverride.value]?.days,
            ...(selectedStageForSpecific.value && selectedDayForSpecific.value && {
              [selectedDayForSpecific.value]: specificTimings.value.length > 0 ? specificTimings.value : null
            })
          }
        }
      })
    }
  };
  
  emit('update:modelValue', newTimingsData);
  closeManager();
};

const closeManager = () => {
  emit('close');
};

// Watch for stage/day selection changes
watch(selectedStageForOverride, (newStage) => {
  if (newStage) {
    // Load existing stage timing or copy from global
    const existingStageTiming = timingsData.value.overrides?.[newStage]?.default;
    stageTimings.value = existingStageTiming 
      ? JSON.parse(JSON.stringify(existingStageTiming))
      : JSON.parse(JSON.stringify(globalTimings.value));
  }
});

watch([selectedStageForSpecific, selectedDayForSpecific], ([stage, day]) => {
  if (stage && day) {
    // Load existing specific timing or copy from stage/global
    const existingSpecificTiming = timingsData.value.overrides?.[stage]?.days?.[day];
    specificTimings.value = existingSpecificTiming
      ? JSON.parse(JSON.stringify(existingSpecificTiming))
      : JSON.parse(JSON.stringify(stageTimings.value.length > 0 ? stageTimings.value : globalTimings.value));
  }
});

// Initialize on mount
onMounted(() => {
  // Load current timings
  globalTimings.value = JSON.parse(JSON.stringify(timingsData.value.default || defaultTiming));
  
  // Set current mode based on current context
  if (props.stage && props.day) {
    currentMode.value = 'stageDay';
    selectedStageForSpecific.value = props.stage;
    selectedDayForSpecific.value = props.day;
  } else if (props.stage) {
    currentMode.value = 'stage';
    selectedStageForOverride.value = props.stage;
  }
});
</script>

<style scoped>
.timing-manager-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 1rem;
}

.timing-manager-sheet {
  background: white;
  border-radius: 16px 16px 0 0;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.sheet-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.header-info {
  flex: 1;
}

.sheet-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.sheet-subtitle {
  color: #475569;
  font-size: 0.875rem;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #334155;
  cursor: pointer;
  padding: 0.25rem;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: background 0.3s ease;
}

.close-btn:hover {
  background: #f1f5f9;
}

.mode-tabs {
  display: flex;
  padding: 1rem 1.5rem 0;
  gap: 0.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.mode-tab {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  font-weight: 600;
  color: #334155;
  transition: all 0.3s ease;
}

.mode-tab:hover {
  color: #475569;
  background: #f8fafc;
}

.mode-tab.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

.mode-icon {
  font-size: 1rem;
}

.mode-label {
  font-size: 0.875rem;
  color: inherit;
}

.mode-content {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
}

.mode-description {
  margin-bottom: 1.5rem;
}

.mode-description h4 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.mode-description p {
  color: #475569;
  font-size: 0.875rem;
  margin: 0;
}

.stage-selector,
.override-selectors {
  margin-bottom: 1.5rem;
}

.selector-group {
  margin-bottom: 1rem;
}

.selector-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.5rem;
}

.stage-select,
.day-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.875rem;
  background: white;
  color: #1e293b;
}

.timing-list {
  margin-bottom: 1.5rem;
}

.timing-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  margin-bottom: 0.75rem;
}

.period-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.period-title-input {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1e293b;
  background: #ffffff;
}

.period-type-select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  background: white;
  color: #1e293b;
}

.time-inputs {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.time-input {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  width: 80px;
  color: #1e293b;
  background: #ffffff;
}

.time-separator {
  font-weight: 600;
  color: #475569;
}

.period-actions {
  display: flex;
  gap: 0.25rem;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  color: #64748b;
  transition: all 0.3s ease;
}

.action-btn:hover:not(:disabled) {
  background: #f8fafc;
  border-color: #9ca3af;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-btn.danger {
  color: #dc2626;
  border-color: #fca5a5;
}

.action-btn.danger:hover:not(:disabled) {
  background: #fef2f2;
  border-color: #f87171;
}

.timing-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.add-btn,
.reset-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.add-btn {
  background: #10b981;
  color: white;
}

.add-btn:hover {
  background: #059669;
}

.reset-btn {
  background: #f1f5f9;
  color: #334155;
  border: 1px solid #e2e8f0;
}

.reset-btn:hover {
  background: #e2e8f0;
}

.sheet-footer {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}

.save-btn,
.cancel-btn {
  flex: 1;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.save-btn {
  background: #3b82f6;
  color: white;
}

.save-btn:hover:not(:disabled) {
  background: #2563eb;
}

.save-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.cancel-btn {
  background: white;
  color: #334155;
  border: 1px solid #e2e8f0;
}

.period-title-input::placeholder,
.time-input::placeholder {
  color: #64748b;
  opacity: 1;
}

.stage-select:disabled,
.day-select:disabled,
.action-btn:disabled,
.save-btn:disabled {
  color: #475569;
}

.cancel-btn:hover {
  background: #f8fafc;
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .timing-manager-sheet {
    max-width: 100%;
    max-height: 95vh;
  }
  
  .sheet-header {
    padding: 1rem;
  }
  
  .mode-tabs {
    padding: 1rem;
    gap: 0.25rem;
  }
  
  .mode-tab {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
  }
  
  .mode-content {
    padding: 1rem;
  }
  
  .timing-item {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }
  
  .period-info {
    flex-direction: row;
    gap: 0.5rem;
  }
  
  .period-title-input,
  .period-type-select {
    flex: 1;
  }
  
  .time-inputs {
    justify-content: center;
  }
  
  .period-actions {
    justify-content: center;
  }
  
  .sheet-footer {
    padding: 1rem;
  }
}
</style>
