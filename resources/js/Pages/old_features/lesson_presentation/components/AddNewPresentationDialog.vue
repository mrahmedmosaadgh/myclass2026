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
            :disable="!!props.preselectedGrade"
            @update:model-value="onGradeChange"
          >
            <template v-if="props.preselectedGrade" v-slot:prepend>
              <q-icon name="lock" color="grey-6" />
            </template>
          </q-select>

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
            :disable="!formData.grade || !!props.preselectedSubject"
          >
            <template v-if="props.preselectedSubject" v-slot:prepend>
              <q-icon name="lock" color="grey-6" />
            </template>
          </q-select>

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

          <!-- Template Selection (Optional) -->
          <q-select
            v-model="formData.template_id"
            :options="templateOptions"
            option-value="value"
            option-label="label"
            label="Select Template (Optional)"
            outlined
            dense
            emit-value
            map-options
            clearable
            :disable="!formData.subject || lessonPresentationStore.templates.length === 0"
          >
            <template v-slot:prepend>
              <q-icon name="description" />
            </template>
            <template v-if="lessonPresentationStore.templates.length === 0" v-slot:hint>
              No templates available for this subject
            </template>
          </q-select>

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
import { ref, computed, watch } from 'vue';
import { useQuasar } from 'quasar';
import { useTeacherStore } from '@/Stores/teacherStore';
import { useLessonPresentationStore } from '@/Stores/lessonPresentationStore';
import axios from 'axios';

const $q = useQuasar();
const teacherStore = useTeacherStore();
const lessonPresentationStore = useLessonPresentationStore();

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  preselectedGrade: {
    type: Object,
    default: null
  },
  preselectedSubject: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['update:modelValue', 'submit']);

const isOpen = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
    // Reset preselected values when dialog closes
    if (!value) {
      if (props.preselectedGrade) {
        formData.value.grade = null;
      }
      if (props.preselectedSubject) {
        formData.value.subject = null;
      }
    }
  }
});

const formData = ref({
  grade: null,
  subject: null,
  name: '',
  description: '',
  template_id: null
});

const isSubmitting = ref(false);

const grades = computed(() => teacherStore.grades || []);

const availableSubjects = computed(() => {
  if (!formData.value.grade) return [];
  const selectedGrade = grades.value.find(g => g.id === formData.value.grade);
  return selectedGrade?.subjects || [];
});

const templateOptions = computed(() => {
  return lessonPresentationStore.templates.map(t => ({
    label: t.name,
    value: t.id,
    description: t.description
  }));
});

const onGradeChange = () => {
  // Reset subject and template when grade changes
  formData.value.subject = null;
  formData.value.template_id = null;
};

const onSubjectChange = async () => {
  // Reset template when subject changes
  formData.value.template_id = null;

  // Fetch templates for the selected subject
  if (formData.value.subject) {
    await lessonPresentationStore.fetchTemplates(formData.value.subject);

    // Auto-select the default template
    if (!lessonPresentationStore.selectedTemplate && lessonPresentationStore.templates.length > 0) {
      lessonPresentationStore.selectedTemplate = lessonPresentationStore.templates[0];
      lessonPresentationStore.saveSelectedTemplate();
    }
  }
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
    description: formData.value.description,
    template_id: lessonPresentationStore.selectedTemplate?.id || null // Use selected template
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
    description: '',
    template_id: null
  };
};

// Watch for preselected grade and set it when dialog opens
watch(() => props.preselectedGrade, (newGrade) => {
  if (newGrade) {
    formData.value.grade = newGrade.id;
    // Trigger subject loading
    onGradeChange();
  }
}, { immediate: true });

// Watch for preselected subject and set it when dialog opens
watch(() => props.preselectedSubject, (newSubject) => {
  if (newSubject) {
    formData.value.subject = newSubject.id;
    // Fetch templates for preselected subject
    lessonPresentationStore.fetchTemplates(newSubject.id);
  }
}, { immediate: true });

// Watch for subject changes to fetch templates
watch(() => formData.value.subject, (newSubjectId) => {
  if (newSubjectId && !props.preselectedSubject) {
    onSubjectChange();
  }
});

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
