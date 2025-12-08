<template>
  <q-dialog v-model="isOpen" persistent>
    <q-card style="min-width: 500px">
      <q-card-section class="row items-center">
        <div class="text-h6">Add Multiple Tasks</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <p class="text-caption q-mb-sm">Enter one task per line. Each line will be created as a separate task.</p>
        <q-input
          v-model="tasksText"
          type="textarea"
          rows="10"
          outlined
          placeholder="Task 1&#10;Task 2&#10;Task 3"
          autofocus
        />

        <div class="row q-mt-md">
          <q-select
            v-model="classification"
            :options="classificationOptions"
            label="Classification"
            outlined
            dense
            class="col-12 col-md-6 q-pr-md-sm"
            clearable
          />

          <q-input
            v-model="dueDate"
            label="Due Date"
            outlined
            dense
            class="col-12 col-md-6 q-pl-md-sm"
          >
            <template v-slot:append>
              <q-icon name="event" class="cursor-pointer">
                <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                  <q-date v-model="dueDate">
                    <div class="row items-center justify-end">
                      <q-btn v-close-popup label="Close" color="primary" flat />
                    </div>
                  </q-date>
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>
        </div>

        <div class="row q-mt-md" v-if="parentTask">
          <div class="col-12">
            <q-chip>
              Parent Task: {{ parentTask.title }}
              <q-btn flat round dense icon="close" size="xs" @click="$emit('update:parent-task', null)" />
            </q-chip>
          </div>
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="negative" v-close-popup />
        <q-btn flat label="Add Tasks" color="positive" @click="addTasks" :loading="loading" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const isOpen = defineModel( )
    const props = defineProps({
//   isOpen: {
//     type: Boolean,
//     required: true
//   },
  parentTask: {
    type: Object,
    default: null
  },
  classificationOptions: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:is-open', 'update:parent-task', 'add-tasks']);

// State
const tasksText = ref('');
const classification = ref(null);
const dueDate = ref('');
const loading = ref(false);

// Reset form when dialog is opened
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    tasksText.value = '';
    classification.value = null;
    dueDate.value = '';
  }
});

// Methods
const addTasks = () => {
  if (!tasksText.value.trim()) {
    return;
  }

  loading.value = true;

  // Split the text by newlines and filter out empty lines
  const taskTitles = tasksText.value
    .split('\n')
    .map(title => title.trim())
    .filter(title => title.length > 0);

  // Create task objects
  const tasks = taskTitles.map(title => ({
    title,
    classification: classification.value,
    due_date: dueDate.value || null,
    parent_id: props.parentTask ? props.parentTask.id : null
  }));

  // Emit the tasks to be added
  emit('add-tasks', tasks);

  // Close the dialog
  emit('update:is-open', false);
  loading.value = false;
};
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
