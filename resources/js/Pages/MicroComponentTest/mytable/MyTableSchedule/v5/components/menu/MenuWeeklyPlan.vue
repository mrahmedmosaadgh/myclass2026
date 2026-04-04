<template>
  <div class="menu-weekly">
    <div class="menu-header">
      <div>
        <h3 class="section-title">Weekly Plans</h3>
        <p class="section-desc">Edit classwork, homework, links, and notes per week and class.</p>
      </div>
      <button class="close-btn" @click="$emit('close')" title="Close">✕</button>
    </div>

    <!-- Week Selector -->
    <div class="week-selector">
      <div class="selector-group">
        <label class="selector-label">Week</label>
        <div class="week-nav">
          <button @click="changeWeek(-1)" class="nav-btn" :disabled="!canGoBack">‹</button>
          <span class="week-display">{{ weekTitle }}</span>
          <button @click="changeWeek(1)" class="nav-btn" :disabled="!canGoForward">›</button>
        </div>
      </div>
      <div class="week-title-input">
        <label class="selector-label">Week Title</label>
        <input 
          v-model="editableWeekTitle" 
          @blur="saveWeekTitle"
          @keyup.enter="saveWeekTitle"
          class="title-input"
          placeholder="Enter week title..."
        />
      </div>
    </div>

    <!-- Class Selector -->
    <div class="class-selector">
      <label class="selector-label">Class</label>
      <div class="class-btns">
        <button
          v-for="cls in scheduleClasses"
          :key="cls"
          class="class-btn"
          :class="{ active: selectedClass === cls }"
          @click="selectClass(cls)"
        >{{ cls }}</button>
      </div>
    </div>

    <!-- Weekly Slots Editor -->
    <div v-if="selectedClass" class="slots-editor">
      <div class="editor-header">
        <h4>{{ selectedClass }} Schedule</h4>
        <div class="slot-actions">
          <button @click="showImportDialog = true" class="import-btn">📥 Import</button>
          <button @click="exportClassData" class="export-btn">📤 Export</button>
        </div>
      </div>

      <div class="slots-list">
        <div
          v-for="slot in classSlots"
          :key="`${slot.dayId}-${slot.periodId}`"
          class="slot-editor"
        >
          <div class="slot-header">
            <span class="slot-title">{{ slot.dayName }} – Period {{ slot.periodId }}</span>
          </div>
          <div class="slot-fields">
            <div class="field-group">
              <label>CW</label>
              <input
                :value="getSlotFieldValue(slot.dayId, slot.periodId, 'cw')"
                @input="updateSlot(slot.dayId, slot.periodId, 'cw', $event.target.value)"
                placeholder="Classwork topic..."
                class="field-input"
              />
            </div>
            <div class="field-group">
              <label>CW Pages</label>
              <input
                :value="getSlotFieldValue(slot.dayId, slot.periodId, 'cwPages')"
                @input="updateSlot(slot.dayId, slot.periodId, 'cwPages', $event.target.value)"
                placeholder="Page numbers..."
                class="field-input small"
              />
            </div>
            <div class="field-group">
              <label>HW</label>
              <input
                :value="getSlotFieldValue(slot.dayId, slot.periodId, 'hw')"
                @input="updateSlot(slot.dayId, slot.periodId, 'hw', $event.target.value)"
                placeholder="Homework assignment..."
                class="field-input"
              />
            </div>
            <div class="field-group">
              <label>HW Pages</label>
              <input
                :value="getSlotFieldValue(slot.dayId, slot.periodId, 'hwPages')"
                @input="updateSlot(slot.dayId, slot.periodId, 'hwPages', $event.target.value)"
                placeholder="Page numbers..."
                class="field-input small"
              />
            </div>
            <div class="field-group">
              <label>Presentation Link</label>
              <input
                :value="getSlotFieldValue(slot.dayId, slot.periodId, 'presentationLink')"
                @input="updateSlot(slot.dayId, slot.periodId, 'presentationLink', $event.target.value)"
                placeholder="https://..."
                class="field-input"
              />
            </div>
            <div class="field-group">
              <label>Material Link</label>
              <input
                :value="getSlotFieldValue(slot.dayId, slot.periodId, 'materialLink')"
                @input="updateSlot(slot.dayId, slot.periodId, 'materialLink', $event.target.value)"
                placeholder="https://..."
                class="field-input"
              />
            </div>
            <div class="field-group full-width">
              <label>Notes</label>
              <textarea
                :value="getSlotFieldValue(slot.dayId, slot.periodId, 'notesHtml')"
                @input="updateSlot(slot.dayId, slot.periodId, 'notesHtml', $event.target.value)"
                placeholder="Notes (HTML allowed)..."
                class="field-textarea"
                rows="3"
              ></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Import Dialog -->
    <div v-if="showImportDialog" class="import-overlay" @click.self="closeImportDialog">
      <div class="import-dialog">
        <div class="dialog-header">
          <h4>Import Weekly Plans</h4>
          <button @click="closeImportDialog" class="close-btn">✕</button>
        </div>
        
        <div class="import-options">
          <label class="radio-label">
            <input type="radio" v-model="importMode" value="class" />
            Import for selected class only
          </label>
          <label class="radio-label">
            <input type="radio" v-model="importMode" value="all" />
            Import for all classes
          </label>
        </div>

        <div class="import-methods">
          <div class="import-method">
            <h5>Paste JSON</h5>
            <textarea
              v-model="pastedJson"
              placeholder="Paste JSON here..."
              class="json-textarea"
              rows="8"
            ></textarea>
            <button @click="importFromPaste" class="import-btn" :disabled="!pastedJson.trim()">
              Import from Paste
            </button>
          </div>

          <div class="import-method">
            <h5>Upload JSON File</h5>
            <input
              type="file"
              ref="fileInput"
              accept=".json"
              @change="handleFileSelect"
              class="file-input"
            />
            <button @click="$refs.fileInput.click()" class="import-btn">
              Choose File
            </button>
          </div>
        </div>

        <div v-if="importError" class="import-error">
          {{ importError }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useAppStore } from '../../composables/useAppStore.js';

defineEmits(['close']);

const store = useAppStore();

// State
const currentWeekOffset = ref(0);
const editableWeekTitle = ref('');
const selectedClass = ref('');
const showImportDialog = ref(false);
const importMode = ref('class');
const pastedJson = ref('');
const importError = ref('');
const fileInput = ref(null);

// Computed
const currentWeekKey = computed(() => {
  const date = new Date();
  date.setDate(date.getDate() + (currentWeekOffset.value * 7));
  return store.getWeekKey(date);
});

const weekTitle = computed(() => store.getWeekTitle(currentWeekKey.value));

const canGoBack = computed(() => currentWeekOffset.value > -52);
const canGoForward = computed(() => currentWeekOffset.value < 52);

const scheduleClasses = computed(() => store.getScheduleClasses());

const classSlots = computed(() => {
  if (!selectedClass.value) return [];
  return store.getScheduledSlotsForClass(selectedClass.value);
});

const slotData = ref({});

// Methods
const changeWeek = (direction) => {
  const newOffset = currentWeekOffset.value + direction;
  if (newOffset >= -52 && newOffset <= 52) {
    currentWeekOffset.value = newOffset;
    editableWeekTitle.value = weekTitle.value;
    loadSlotData();
  }
};

const saveWeekTitle = () => {
  store.setWeekTitle(currentWeekKey.value, editableWeekTitle.value);
};

const selectClass = (cls) => {
  selectedClass.value = cls;
  loadSlotData();
};

const loadSlotData = () => {
  if (!selectedClass.value) return;
  
  const data = {};
  classSlots.value.forEach(slot => {
    if (!data[slot.dayId]) data[slot.dayId] = {};
    const existing = store.getWeeklyPlanEntry(currentWeekKey.value, selectedClass.value, slot.dayId, slot.periodId);
    data[slot.dayId][slot.periodId] = existing || {
      cw: '',
      cwPages: '',
      hw: '',
      hwPages: '',
      presentationLink: '',
      materialLink: '',
      notesHtml: ''
    };
  });
  slotData.value = data;
};

const getSlotFieldValue = (dayId, periodId, field) => {
  return slotData.value[dayId]?.[periodId]?.[field] || '';
};

const updateSlot = (dayId, periodId, field, value) => {
  if (!slotData.value[dayId]) slotData.value[dayId] = {};
  if (!slotData.value[dayId][periodId]) slotData.value[dayId][periodId] = {};
  slotData.value[dayId][periodId][field] = value;
  
  store.updateWeeklyPlanEntry(currentWeekKey.value, selectedClass.value, dayId, periodId, {
    [field]: value
  });
};

const exportClassData = () => {
  const data = {
    week: currentWeekKey.value,
    weekTitle: editableWeekTitle.value,
    class: selectedClass.value,
    days: {}
  };
  
  Object.entries(slotData.value).forEach(([dayId, periods]) => {
    data.days[dayId] = {};
    Object.entries(periods).forEach(([periodId, entry]) => {
      const clean = { ...entry };
      Object.keys(clean).forEach(key => {
        if (clean[key] === '') delete clean[key];
      });
      if (Object.keys(clean).length > 0) {
        data.days[dayId][periodId] = clean;
      }
    });
  });
  
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `weekly-plan-${selectedClass.value}-${currentWeekKey.value}.json`;
  a.click();
  URL.revokeObjectURL(url);
};

const closeImportDialog = () => {
  showImportDialog.value = false;
  pastedJson.value = '';
  importError.value = '';
  importMode.value = 'class';
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  const reader = new FileReader();
  reader.onload = (e) => {
    pastedJson.value = e.target.result;
    importFromPaste();
  };
  reader.readAsText(file);
};

const importFromPaste = () => {
  importError.value = '';
  
  try {
    const data = JSON.parse(pastedJson.value);
    
    if (importMode.value === 'class') {
      // Selected class import
      if (!data.week || !data.class || !data.days) {
        throw new Error('Invalid format for selected class import');
      }
      
      if (data.class !== selectedClass.value) {
        throw new Error(`Import data is for class ${data.class}, but selected class is ${selectedClass.value}`);
      }
      
      // Import the data
      Object.entries(data.days).forEach(([dayId, periods]) => {
        Object.entries(periods).forEach(([periodId, entry]) => {
          store.updateWeeklyPlanEntry(currentWeekKey.value, selectedClass.value, dayId, periodId, entry);
        });
      });
      
      // Update week title if provided
      if (data.weekTitle) {
        editableWeekTitle.value = data.weekTitle;
        saveWeekTitle();
      }
      
    } else {
      // All classes import
      if (!data.week || !data.classes) {
        throw new Error('Invalid format for all classes import');
      }
      
      // Import all classes data
      Object.entries(data.classes).forEach(([className, days]) => {
        Object.entries(days).forEach(([dayId, periods]) => {
          Object.entries(periods).forEach(([periodId, entry]) => {
            store.updateWeeklyPlanEntry(currentWeekKey.value, className, dayId, periodId, entry);
          });
        });
      });
      
      // Update week title if provided
      if (data.weekTitle) {
        editableWeekTitle.value = data.weekTitle;
        saveWeekTitle();
      }
    }
    
    // Reload current class data
    loadSlotData();
    closeImportDialog();
    
  } catch (error) {
    importError.value = `Import failed: ${error.message}`;
  }
};

// Lifecycle
onMounted(() => {
  editableWeekTitle.value = weekTitle.value;
  if (scheduleClasses.value.length > 0 && !selectedClass.value) {
    selectClass(scheduleClasses.value[0]);
  }
});

watch(() => weekTitle.value, (newTitle) => {
  if (editableWeekTitle.value !== newTitle) {
    editableWeekTitle.value = newTitle;
  }
});
</script>

<style scoped>
.menu-weekly {
  padding: 1rem;
  max-height: 100vh;
  overflow-y: auto;
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.section-title {
  margin: 0 0 0.25rem 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.section-desc {
  margin: 0;
  font-size: 0.875rem;
  color: #666;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.25rem;
  color: #666;
}

.close-btn:hover {
  color: #000;
}

/* Week Selector */
.week-selector {
  margin-bottom: 1.5rem;
}

.week-nav {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.nav-btn {
  background: #f0f0f0;
  border: 1px solid #ddd;
  padding: 0.25rem 0.5rem;
  cursor: pointer;
  border-radius: 4px;
}

.nav-btn:hover:not(:disabled) {
  background: #e0e0e0;
}

.nav-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.week-display {
  font-weight: 500;
  min-width: 120px;
  text-align: center;
}

.week-title-input {
  margin-top: 0.75rem;
}

.title-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.875rem;
}

/* Class Selector */
.class-selector {
  margin-bottom: 1.5rem;
}

.class-btns {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.class-btn {
  background: #f0f0f0;
  border: 1px solid #ddd;
  padding: 0.5rem 1rem;
  cursor: pointer;
  border-radius: 4px;
  font-size: 0.875rem;
}

.class-btn:hover {
  background: #e0e0e0;
}

.class-btn.active {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

/* Slots Editor */
.slots-editor {
  border-top: 1px solid #eee;
  padding-top: 1rem;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.editor-header h4 {
  margin: 0;
  font-size: 1.125rem;
}

.slot-actions {
  display: flex;
  gap: 0.5rem;
}

.import-btn, .export-btn {
  background: #28a745;
  color: white;
  border: none;
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  font-size: 0.75rem;
  cursor: pointer;
}

.export-btn {
  background: #6c757d;
}

.import-btn:hover, .export-btn:hover {
  opacity: 0.9;
}

.slots-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.slot-editor {
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 1rem;
  background: #fafafa;
}

.slot-header {
  margin-bottom: 0.75rem;
}

.slot-title {
  font-weight: 500;
  color: #333;
}

.slot-fields {
  display: grid;
  grid-template-columns: 2fr 1fr 2fr 1fr;
  gap: 0.75rem;
  align-items: start;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.field-group.full-width {
  grid-column: 1 / -1;
}

.field-group label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #666;
}

.field-input, .field-textarea {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 0.375rem;
  font-size: 0.8125rem;
}

.field-input.small {
  font-size: 0.75rem;
}

.field-textarea {
  resize: vertical;
  min-height: 60px;
}

/* Import Dialog */
.import-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.import-dialog {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.dialog-header h4 {
  margin: 0;
}

.import-options {
  margin-bottom: 1rem;
}

.radio-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.import-methods {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.import-method h5 {
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
}

.json-textarea {
  width: 100%;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 0.5rem;
  font-family: monospace;
  font-size: 0.75rem;
  resize: vertical;
}

.file-input {
  margin-bottom: 0.5rem;
}

.import-error {
  margin-top: 1rem;
  padding: 0.75rem;
  background: #f8d7da;
  color: #721c24;
  border-radius: 4px;
  font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
  .slot-fields {
    grid-template-columns: 1fr;
  }
  
  .field-group.full-width {
    grid-column: 1;
  }
}
</style>
