<template>
  <q-card style="width: 500px; max-width: 80vw;">
    <q-card-section>
      <div class="text-h6">{{ task.id ? 'Edit Task' : 'New Task' }}</div>
    </q-card-section>

    <q-card-section>
      <q-form @submit="saveTask" class="q-gutter-md">
        <q-input
          v-model="formData.title"
          label="Title *"
          :rules="[val => !!val || 'Title is required']"
          autofocus
        />

        <q-input
          v-model="formData.description"
          label="Description"
          type="textarea"
          rows="3"
        />

        <q-select
          v-model="formData.parent_id"
          :options="parentOptions"
          label="Parent Task"
          option-value="id"
          option-label="title"
          emit-value
          map-options
          clearable
        />

        <q-select
          v-model="formData.classification"
          :options="classificationOptions"
          label="Classification"
          use-input
          input-debounce="0"
          new-value-mode="add-unique"
          clearable
        />

        <q-input
          v-model="formData.due_date"
          label="Due Date"
          clearable
        >
          <template v-slot:append>
            <q-icon name="event" class="cursor-pointer">
              <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                <q-date v-model="formData.due_date" mask="YYYY-MM-DD">
                  <div class="row items-center justify-end">
                    <q-btn v-close-popup label="Close" color="primary" flat />
                  </div>
                </q-date>
              </q-popup-proxy>
            </q-icon>
          </template>
        </q-input>

        <div class="row justify-end q-mt-md">
          <q-btn label="Cancel" color="grey-7" flat v-close-popup @click="$emit('cancel')" />
          <q-btn label="Save" type="submit" color="primary" />
        </div>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  task: {
    type: Object,
    required: true
  },
  parentTasks: {
    type: Array,
    default: () => []
  },
  classifications: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['save', 'cancel']);

// Form data
const formData = ref({
  id: props.task.id || null,
  title: props.task.title || '',
  description: props.task.description || '',
  parent_id: props.task.parent_id || null,
  classification: props.task.classification || null,
  due_date: props.task.due_date || null,
  completed_at: props.task.completed_at || null
});

// Computed
const parentOptions = computed(() => {
  // Filter out the current task and its children to prevent circular references
  return props.parentTasks.filter(task => {
    if (formData.value.id === task.id) return false;
    
    // Check if this task is a child of the current task
    let current = task;
    while (current.parent_id) {
      if (current.parent_id === formData.value.id) return false;
      const parent = props.parentTasks.find(t => t.id === current.parent_id);
      if (!parent) break;
      current = parent;
    }
    
    return true;
  });
});

const classificationOptions = computed(() => {
  return [...new Set([...props.classifications, formData.value.classification].filter(Boolean))];
});

// Methods
const saveTask = () => {
  emit('save', { ...formData.value });
};

// Lifecycle hooks
onMounted(() => {
  // Initialize form data with task props
  formData.value = {
    id: props.task.id || null,
    title: props.task.title || '',
    description: props.task.description || '',
    parent_id: props.task.parent_id || null,
    classification: props.task.classification || null,
    due_date: props.task.due_date || null,
    completed_at: props.task.completed_at || null
  };
});
</script>
