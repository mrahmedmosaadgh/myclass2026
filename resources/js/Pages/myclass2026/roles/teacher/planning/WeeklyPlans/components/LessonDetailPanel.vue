<template>
  <div class="lesson-detail-panel">
    <div v-if="!lesson" class="empty-state">
      <p>Select a lesson to view details</p>
    </div>
    
    <div v-else class="lesson-content">
      <div class="header">
        <h3>{{ lesson.title }}</h3>
        <div class="status-badge" :class="`status-${lesson.status}`">
          {{ formatStatus(lesson.status) }}
        </div>
      </div>
      
      <div class="form-section">
        <div class="form-group">
          <label>Completion</label>
          <q-slider
            v-model="localLesson.completion_percentage"
            :min="0"
            :max="100"
            :step="5"
            label
            color="primary"
            @update:model-value="updateLesson"
          />
        </div>
        
        <div class="form-group">
          <label>Status</label>
          <q-select
            v-model="localLesson.status"
            :options="statusOptions"
            outlined
            dense
            @update:model-value="updateLesson"
          />
        </div>
        
        <div class="form-group">
          <label>Notes</label>
          <q-input
            v-model="localLesson.notes"
            type="textarea"
            outlined
            autogrow
            @update:model-value="updateLesson"
          />
        </div>
        
        <div class="form-group">
          <label>Weekly Goals</label>
          <q-input
            v-model="localLesson.weekly_goals"
            type="textarea"
            outlined
            autogrow
            @update:model-value="updateLesson"
          />
        </div>
        
        <div class="form-group" v-if="showReflection">
          <label>Reflection</label>
          <q-input
            v-model="localLesson.reflection"
            type="textarea"
            outlined
            autogrow
            placeholder="What went well? What needs improvement?"
            @update:model-value="updateLesson"
          />
        </div>
      </div>
      
      <div class="action-buttons">
        <q-btn
          color="primary"
          icon="save"
          label="Save"
          @click="saveLesson"
        />
        <q-btn
          outline
          color="grey"
          icon="close"
          label="Close"
          @click="$emit('close')"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  lesson: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['update:lesson', 'save', 'close']);

// Create a local copy of the lesson to edit
const localLesson = ref(null);

// Watch for changes in the lesson prop
watch(() => props.lesson, (newLesson) => {
  if (newLesson) {
    localLesson.value = { ...newLesson };
  } else {
    localLesson.value = null;
  }
}, { immediate: true });

// Status options for the dropdown
const statusOptions = [
  { label: 'Planned', value: 'planned' },
  { label: 'In Progress', value: 'in_progress' },
  { label: 'Completed', value: 'completed' },
  { label: 'Partially Completed', value: 'partially_completed' },
  { label: 'Not Completed', value: 'not_completed' }
];

// Format the status for display
const formatStatus = (status) => {
  return status.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ');
};

// Show reflection field only for completed or partially completed lessons
const showReflection = computed(() => {
  return localLesson.value && 
    ['completed', 'partially_completed', 'not_completed'].includes(localLesson.value.status);
});

// Update the local lesson
const updateLesson = () => {
  emit('update:lesson', localLesson.value);
};

// Save the lesson
const saveLesson = () => {
  emit('save', localLesson.value);
};
</script>

<style scoped>
.lesson-detail-panel {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  padding: 1.5rem;
  height: 100%;
}

.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  color: #94a3b8;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.status-planned {
  background-color: #f1f5f9;
  color: #64748b;
}

.status-in_progress {
  background-color: #dbeafe;
  color: #2563eb;
}

.status-completed {
  background-color: #dcfce7;
  color: #16a34a;
}

.status-partially_completed {
  background-color: #fef3c7;
  color: #d97706;
}

.status-not_completed {
  background-color: #fee2e2;
  color: #dc2626;
}

.form-section {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #475569;
}

.action-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}
</style>