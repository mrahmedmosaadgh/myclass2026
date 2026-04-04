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
          <button @click="showImportAllDialog = true" class="import-all-btn">📥 Import All</button>
          <button @click="exportAllData" class="export-all-btn">📤 Export All</button>
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
              <label>CW <span v-if="!getSlotFieldValue(slot.dayId, slot.periodId, 'cw')" class="needs-update">⚠️ Needs update</span></label>
              <input
                :value="getSlotFieldValue(slot.dayId, slot.periodId, 'cw')"
                @input="updateSlot(slot.dayId, slot.periodId, 'cw', $event.target.value)"
                placeholder="Classwork topic..."
                class="field-input"
                :class="{ 'empty-field': !getSlotFieldValue(slot.dayId, slot.periodId, 'cw') }"
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
              <label>HW <span v-if="!getSlotFieldValue(slot.dayId, slot.periodId, 'hw')" class="needs-update">⚠️ Needs update</span></label>
              <input
                :value="getSlotFieldValue(slot.dayId, slot.periodId, 'hw')"
                @input="updateSlot(slot.dayId, slot.periodId, 'hw', $event.target.value)"
                placeholder="Homework assignment..."
                class="field-input"
                :class="{ 'empty-field': !getSlotFieldValue(slot.dayId, slot.periodId, 'hw') }"
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
            <div class="field-group">
              <label class="checkbox-label">
                <input
                  type="checkbox"
                  :checked="getSlotFieldValue(slot.dayId, slot.periodId, 'done')"
                  @change="updateSlot(slot.dayId, slot.periodId, 'done', $event.target.checked)"
                  class="field-checkbox"
                />
                Done
              </label>
            </div>
            <div class="field-group">
              <label>Rating (1-5)</label>
              <div class="rating-container">
                <button
                  v-for="star in 5"
                  :key="star"
                  type="button"
                  @click="updateSlot(slot.dayId, slot.periodId, 'rating', star)"
                  class="star-btn"
                  :class="{ active: getSlotFieldValue(slot.dayId, slot.periodId, 'rating') >= star }"
                >
                  ⭐
                </button>
              </div>
              <span class="rating-text">{{ getSlotFieldValue(slot.dayId, slot.periodId, 'rating') || 'Not rated' }}/5</span>
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
            <div class="import-buttons">
              <button @click="importFromPaste" class="import-btn">📥 Import</button>
              <button @click="pasteFromClipboard" class="paste-btn">📋 Paste</button>
            </div>
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
        <div v-if="importSuccess" class="import-success">
          {{ importSuccess }}
        </div>
      </div>
    </div>

    <!-- Import All Dialog -->
    <div v-if="showImportAllDialog" class="import-overlay" @click.self="closeImportAllDialog">
      <div class="import-dialog">
        <div class="dialog-header">
          <h4>Import All Classes</h4>
          <button @click="closeImportAllDialog" class="close-btn">✕</button>
        </div>
        
        <div class="import-methods">
          <div class="import-method">
            <h5>Paste JSON (All Classes)</h5>
            <textarea
              v-model="pastedAllJson"
              placeholder="Paste JSON for all classes here..."
              class="json-textarea"
              rows="8"
            ></textarea>
            <div class="import-buttons">
              <button @click="importAllFromPaste" class="import-btn">📥 Import</button>
              <button @click="pasteAllFromClipboard" class="paste-btn">📋 Paste</button>
            </div>
          </div>
          
          <div class="import-method">
            <h5>Upload File (All Classes)</h5>
            <input
              type="file"
              ref="allFileInput"
              @change="handleAllFileUpload"
              accept=".json"
              class="file-input"
              style="display: none"
            />
            <button @click="$refs.allFileInput.click()" class="import-btn">
              Choose File
            </button>
          </div>
        </div>

        <div v-if="importAllError" class="import-error">
          {{ importAllError }}
        </div>
        <div v-if="importAllSuccess" class="import-success">
          {{ importAllSuccess }}
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
const importSuccess = ref('');

// Import All state
const showImportAllDialog = ref(false);
const pastedAllJson = ref('');
const importAllError = ref('');
const importAllSuccess = ref('');
const allFileInput = ref(null);

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
  const value = slotData.value[dayId]?.[periodId]?.[field];
  if (field === 'done') return value || false;
  if (field === 'rating') return value || 0;
  return value || '';
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
  
  // Get all scheduled slots for this class
  const scheduledSlots = store.getScheduledSlotsForClass(selectedClass.value);
  
  // Initialize all scheduled days and periods with empty data
  scheduledSlots.forEach(slot => {
    if (!data.days[slot.dayId]) {
      data.days[slot.dayId] = {};
    }
    
    // Get current data for this slot (if any)
    const currentEntry = slotData.value[slot.dayId]?.[slot.periodId] || {};
    
    // Include all fields, even if empty
    data.days[slot.dayId][slot.periodId] = {
      cw: currentEntry.cw || '',
      cwPages: currentEntry.cwPages || '',
      hw: currentEntry.hw || '',
      hwPages: currentEntry.hwPages || '',
      presentationLink: currentEntry.presentationLink || '',
      materialLink: currentEntry.materialLink || '',
      notesHtml: currentEntry.notesHtml || '',
      done: currentEntry.done || false,
      rating: currentEntry.rating || 0
    };
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
  importSuccess.value = '';
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
    
    // Show success message
    const importedCount = importMode.value === 'class' 
      ? Object.keys(data.days).length 
      : Object.keys(data.classes).length;
    importSuccess.value = `✅ Successfully imported ${importedCount} ${importMode.value === 'class' ? 'period' : 'class'}(ies)`;
    
    // Auto-close after success
    setTimeout(() => {
      closeImportDialog();
    }, 2000);
    
  } catch (error) {
    importError.value = `Import failed: ${error.message}`;
  }
};

const pasteFromClipboard = async () => {
  try {
    const text = await navigator.clipboard.readText();
    pastedJson.value = text;
  } catch (error) {
    importError.value = 'Failed to read clipboard. Please paste manually.';
  }
};

const pasteAllFromClipboard = async () => {
  try {
    const text = await navigator.clipboard.readText();
    pastedAllJson.value = text;
  } catch (error) {
    importAllError.value = 'Failed to read clipboard. Please paste manually.';
  }
};

// Import All functions
const closeImportAllDialog = () => {
  showImportAllDialog.value = false;
  pastedAllJson.value = '';
  importAllError.value = '';
  importAllSuccess.value = '';
};

const handleAllFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  const reader = new FileReader();
  reader.onload = (e) => {
    pastedAllJson.value = e.target.result;
    importAllFromPaste();
  };
  reader.readAsText(file);
};

const importAllFromPaste = () => {
  importAllError.value = '';
  
  try {
    const data = JSON.parse(pastedAllJson.value);
    
    // Validate all classes format
    if (!data.week || !data.classes || typeof data.classes !== 'object') {
      throw new Error('Invalid format for all classes import');
    }
    
    // Import all classes data
    Object.entries(data.classes).forEach(([className, classData]) => {
      if (classData && classData.days) {
        Object.entries(classData.days).forEach(([dayId, periods]) => {
          Object.entries(periods).forEach(([periodId, entry]) => {
            store.updateWeeklyPlanEntry(currentWeekKey.value, className, dayId, periodId, entry);
          });
        });
      }
    });
    
    // Update week title if provided
    if (data.weekTitle) {
      editableWeekTitle.value = data.weekTitle;
      saveWeekTitle();
    }
    
    // Show success message
    const importedCount = Object.keys(data.classes).length;
    importAllSuccess.value = `✅ Successfully imported ${importedCount} classes`;
    
    // Auto-close after success
    setTimeout(() => {
      closeImportAllDialog();
    }, 2000);
    
  } catch (error) {
    importAllError.value = `Import failed: ${error.message}`;
  }
};

const exportAllData = () => {
  const data = {
    week: currentWeekKey.value,
    weekTitle: editableWeekTitle.value,
    classes: {}
  };
  
  // Get all classes from schedule
  const allClasses = store.getScheduleClasses();
  
  // Export data for each class
  allClasses.forEach(className => {
    const scheduledSlots = store.getScheduledSlotsForClass(className);
    data.classes[className] = {
      days: {}
    };
    
    // Initialize all scheduled days and periods with empty data
    scheduledSlots.forEach(slot => {
      if (!data.classes[className].days[slot.dayId]) {
        data.classes[className].days[slot.dayId] = {};
      }
      
      // Get current data for this slot (if any)
      const currentEntry = store.getWeeklyPlanEntry(currentWeekKey.value, className, slot.dayId, slot.periodId) || {};
      
      // Include all fields, even if empty
      data.classes[className].days[slot.dayId][slot.periodId] = {
        cw: currentEntry.cw || '',
        cwPages: currentEntry.cwPages || '',
        hw: currentEntry.hw || '',
        hwPages: currentEntry.hwPages || '',
        presentationLink: currentEntry.presentationLink || '',
        materialLink: currentEntry.materialLink || '',
        notesHtml: currentEntry.notesHtml || '',
        done: currentEntry.done || false,
        rating: currentEntry.rating || 0
      };
    });
  });
  
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `weekly-plan-all-classes-${currentWeekKey.value}.json`;
  a.click();
  URL.revokeObjectURL(url);
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
  width: 100%;
  min-width: 100%;
  color: #333;
  background: #fff;
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
  color: #333;
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
  color: #333;
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
  color: #333;
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
  color: #333;
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
  font-size: 0.8125rem;
  cursor: pointer;
}

.export-btn {
  background: #6c757d;
}

.import-all-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  font-size: 0.8125rem;
  cursor: pointer;
}

.export-all-btn {
  background: #dc3545;
  color: white;
  border: none;
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  font-size: 0.8125rem;
  cursor: pointer;
}

.import-btn:hover, .export-btn:hover, .import-all-btn:hover, .export-all-btn:hover {
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
  color: #333;
  background: #fff;
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
  color: #333;
}

.import-options {
  margin-bottom: 1rem;
}

.radio-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  color: #333;
}

.import-methods {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.import-method h5 {
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
  color: #333;
}

.json-textarea {
  width: 100%;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 0.5rem;
  font-family: monospace;
  font-size: 0.75rem;
  resize: vertical;
  color: #333;
  background: #fff;
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

/* Checkbox and Rating Styles */
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.field-checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.rating-container {
  display: flex;
  gap: 0.25rem;
  margin-bottom: 0.5rem;
}

.star-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0;
  transition: transform 0.2s;
}

.star-btn:hover {
  transform: scale(1.1);
}

.star-btn.active {
  color: #ffc107;
}

.rating-text {
  font-size: 0.875rem;
  color: #666;
}

/* Needs Update Indicators */
.needs-update {
  font-size: 0.75rem;
  color: #dc3545;
  font-weight: 500;
  margin-left: 0.5rem;
}

.empty-field {
  border-color: #dc3545 !important;
  background-color: #fff5f5 !important;
}

.empty-field:focus {
  border-color: #dc3545 !important;
  box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2) !important;
}

/* Import Buttons */
.import-buttons {
  display: flex;
  gap: 0.5rem;
}

.paste-btn {
  background: #6c757d;
  color: white;
  border: none;
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  font-size: 0.8125rem;
  cursor: pointer;
}

.paste-btn:hover {
  opacity: 0.9;
}

/* Success Message */
.import-success {
  margin-top: 1rem;
  padding: 0.75rem;
  background: #d4edda;
  color: #155724;
  border-radius: 4px;
  font-size: 0.875rem;
  border: 1px solid #c3e6cb;
}
</style>
