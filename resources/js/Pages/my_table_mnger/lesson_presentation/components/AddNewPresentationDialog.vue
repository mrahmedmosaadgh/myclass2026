<template>
  <q-dialog v-model="isOpen" persistent>
    <q-card style="min-width: 500px">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Create New Lesson</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup @click="closeDialog" />
      </q-card-section>

      <q-card-section>
        <q-form @submit.prevent="submitForm" class="q-gutter-md">
          <!-- Grade Selection -->
          <q-select
            v-model="formData.grade"
            :options="grades"
            option-value="id"
            option-label="name"
            label="Select Grade *"
            outlined
            dense
            emit-value
            map-options
            :rules="[v => !!v || 'Grade is required']"
            @update:model-value="onGradeChange"
          />

          <!-- Subject Selection -->
          <q-select
            v-model="formData.subject"
            :options="availableSubjects"
            option-value="id"
            option-label="name"
            label="Select Subject *"
            outlined
            dense
            emit-value
            map-options
            :rules="[v => !!v || 'Subject is required']"
            :disable="!formData.grade"
          />

          <!-- Lesson Name (Optional) -->
          <q-input
            v-model="formData.name"
            label="Lesson Name (Optional)"
            outlined
            dense
            placeholder="Enter a name or leave blank for default"
          />

          <!-- Description (Optional) -->
          <q-input
            v-model="formData.description"
            label="Description (Optional)"
            outlined
            dense
            type="textarea"
            placeholder="Add a brief description..."
          />

          <!-- Action Buttons -->
          <div class="row items-center justify-end q-gutter-sm q-mt-lg">
            <q-btn
              flat
              label="Cancel"
              color="grey"
              @click="closeDialog"
            />
            <q-btn
              unelevated
              label="Create Lesson"
              color="primary"
              type="submit"
              :loading="isSubmitting"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useQuasar } from 'quasar';
import { useTeacherStore } from '@/Stores/teacherStore';

const $q = useQuasar();
const teacherStore = useTeacherStore();

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'submit']);

const isOpen = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const formData = ref({
  grade: null,
  subject: null,
  name: '',
  description: ''
});

const isSubmitting = ref(false);

const grades = computed(() => teacherStore.grades || []);

const availableSubjects = computed(() => {
  if (!formData.value.grade) return [];
  const selectedGrade = grades.value.find(g => g.id === formData.value.grade);
  return selectedGrade?.subjects || [];
});

const onGradeChange = () => {
  // Reset subject when grade changes
  formData.value.subject = null;
};

const submitForm = () => {
  if (!formData.value.grade || !formData.value.subject) {
    $q.notify({
      type: 'negative',
      message: 'Please select both grade and subject',
      position: 'top'
    });
    return;
  }

  isSubmitting.value = true;
  
  // Emit the form data so parent can handle navigation
  emit('submit', {
    grade_id: formData.value.grade,
    subject_id: formData.value.subject,
    name: formData.value.name || 'New Lesson',
    description: formData.value.description
  });

  // Close dialog after a short delay
  setTimeout(() => {
    isSubmitting.value = false;
    closeDialog();
  }, 500);
};

const closeDialog = () => {
  resetForm();
  isOpen.value = false;
};

const resetForm = () => {
  formData.value = {
    grade: null,
    subject: null,
    name: '',
    description: ''
  };
};

// Ensure teacher data is loaded
if (!teacherStore.grades || teacherStore.grades.length === 0) {
  teacherStore.fetchTeacherData();
}
</script>

<style scoped>
.q-field {
  margin-bottom: 0.5rem;
}
</style>
