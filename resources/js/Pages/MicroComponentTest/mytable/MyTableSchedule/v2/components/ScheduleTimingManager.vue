<template>
  <div v-if="modelValue" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="text-xl font-bold text-gray-800">Manage Schedule Timings</h3>
        <button @click="$emit('close')" class="close-btn">&times;</button>
      </div>

      <div class="modal-body space-y-4">
        <div v-for="(slot, index) in localSlots" :key="index" class="slot-editor bg-gray-50 border border-gray-200">
          <div class="grid-layout">
            
            <!-- Type Dropdown -->
            <div class="field-col">
              <label>Type</label>
              <select v-model="slot.type" class="form-input">
                <option value="lesson">Lesson (Class)</option>
                <option value="break">Break / Recess</option>
                <option value="activity">Special Activity</option>
              </select>
            </div>

            <!-- Title Input -->
            <div class="field-col">
              <label>Title</label>
              <input type="text" v-model="slot.title" class="form-input" placeholder="e.g. Period 1" />
            </div>

            <!-- Start Time -->
            <div class="field-col">
              <label>Start</label>
              <input type="time" v-model="slot.start" class="form-input" @change="autoSort" />
            </div>

            <!-- End Time -->
            <div class="field-col">
              <label>End</label>
              <input type="time" v-model="slot.end" class="form-input" />
            </div>

            <!-- Delete Button -->
            <div class="field-col btn-col">
              <button @click="removeSlot(index)" class="btn-delete" title="Remove">🗑️</button>
            </div>

          </div>
        </div>

        <button @click="addSlot" class="btn-add w-full">
          + Add New Timing Slot
        </button>
      </div>

      <div class="modal-footer mt-6 flex justify-end gap-3">
        <button @click="$emit('close')" class="btn-cancel">Cancel</button>
        <button @click="saveChanges" class="btn-save">💾 Save Changes</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  modelValue: { type: Array, default: () => [] }
});
const emit = defineEmits(['update:modelValue', 'close']);

const localSlots = ref([]);

onMounted(() => {
  // Deep clone to avoid mutating props directly
  localSlots.value = JSON.parse(JSON.stringify(props.modelValue));
});

watch(() => props.modelValue, (newVal) => {
  localSlots.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

const autoSort = () => {
  localSlots.value.sort((a, b) => a.start.localeCompare(b.start));
};

const addSlot = () => {
  // auto-guess start time based on last slot
  let newStart = "13:00";
  if (localSlots.value.length > 0) {
    newStart = localSlots.value[localSlots.value.length - 1].end;
  }
  
  localSlots.value.push({
    id: 'n_' + Date.now(),
    title: "New Period",
    type: "lesson",
    start: newStart,
    end: newStart
  });
};

const removeSlot = (index) => {
  localSlots.value.splice(index, 1);
};

const saveChanges = () => {
  autoSort(); // ensure sorted
  emit('update:modelValue', localSlots.value);
  emit('close');
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: white;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.close-btn {
  font-size: 1.5rem;
  line-height: 1;
  color: #334155;
  background: none;
  border: none;
  cursor: pointer;
  transition: color 0.2s;
}

.close-btn:hover {
  color: #374151;
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex-grow: 1;
}

.slot-editor {
  padding: 1rem;
  border-radius: 8px;
  transition: all 0.2s;
}

.slot-editor:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.grid-layout {
  display: grid;
  grid-template-columns: 2fr 3fr 1.5fr 1.5fr auto;
  gap: 1rem;
  align-items: end;
}

.field-col {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.field-col label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
}

.form-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  background: white;
  color: #1e293b;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input::placeholder {
  color: #64748b;
  opacity: 1;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-col {
  padding-bottom: 0.2rem;
}

.btn-delete {
  background: #fee2e2;
  color: #ef4444;
  border: none;
  padding: 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-delete:hover {
  background: #fca5a5;
}

.btn-add {
  background: #f3f4f6;
  color: #374151;
  border: 2px dashed #d1d5db;
  padding: 1rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-add:hover {
  background: #e5e7eb;
  border-color: #9ca3af;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
}

.btn-cancel {
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  color: #374151;
  font-weight: 500;
  cursor: pointer;
}

.btn-save {
  padding: 0.5rem 1rem;
  background: #3b82f6;
  border: none;
  border-radius: 6px;
  color: white;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.btn-save:hover {
  background: #2563eb;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .grid-layout {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
}
</style>
