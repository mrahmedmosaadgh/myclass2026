<template>
  <q-dialog 
    v-model="dialogModel" 
    persistent 
    @update:model-value="val => $emit('update:modelValue', val)"
  >
    <q-card style="min-width: 500px; max-width: 700px; width: 100%">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ isEditing ? 'Edit Question' : 'Add New Question' }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-form @submit.prevent="handleSave" class="q-gutter-md">
        <q-card-section class="q-pt-none">
          <div class="q-gutter-md">
            <!-- Title -->
            <q-input
              v-model="form.title"
              label="Question Title *"
              outlined
              dense
              :rules="[val => !!val || 'Title is required']"
              :error="!!errors.title"
              :error-message="errors.title"
            />

            <!-- Type -->
            <q-select
              v-model="form.type"
              :options="typeOptions"
              label="Question Type *"
              outlined
              dense
              emit-value
              map-options
              :rules="[val => !!val || 'Type is required']"
              :error="!!errors.type"
              :error-message="errors.type"
            />

            <!-- Category -->
            <q-select
              v-model="form.category"
              :options="categoryOptions"
              label="Category *"
              outlined
              dense
              multiple
              use-chips
              use-input
              input-debounce="0"
              new-value-mode="add-unique"
              :rules="[val => val && val.length > 0 || 'At least one category is required']"
              :error="!!errors.category"
              :error-message="errors.category"
            />

            <!-- Language -->
            <q-select
              v-model="form.language"
              :options="languageOptions"
              label="Language *"
              outlined
              dense
              emit-value
              map-options
              :rules="[val => !!val || 'Language is required']"
              :error="!!errors.language"
              :error-message="errors.language"
            />

            <!-- Tags -->
            <q-select
              v-model="form.tags"
              :options="tagOptions"
              label="Tags"
              outlined
              dense
              multiple
              use-chips
              use-input
              input-debounce="0"
              new-value-mode="add-unique"
              hint="Optional tags for categorization"
            />

            <!-- Options (for select/multi-select types) -->
            <q-input
              v-if="form.type === 'select' || form.type === 'multi-select'"
              v-model="form.options"
              label="Options (comma separated)"
              outlined
              dense
              type="textarea"
              rows="3"
              hint="Enter options separated by commas"
            />

            <!-- Default Answer -->
            <q-input
              v-model="form.default_answer"
              label="Default Answer"
              outlined
              dense
              type="textarea"
              rows="3"
              hint="Optional default answer or placeholder text"
            />

            <!-- Required Toggle -->
            <q-toggle
              v-model="form.is_required"
              label="This question is required"
              color="primary"
            />

            <!-- Description -->
            <q-input
              v-model="form.description"
              label="Description"
              outlined
              dense
              type="textarea"
              rows="2"
              hint="Optional description or instructions for this question"
            />
          </div>
        </q-card-section>

        <q-card-actions align="right" class="q-pa-md">
          <q-btn 
            flat 
            label="Cancel" 
            color="grey-7" 
            v-close-popup 
            :disable="saving"
          />
          <q-btn 
            type="submit" 
            label="Save" 
            color="primary" 
            :loading="saving"
            :disable="!isFormValid"
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useQuasar } from 'quasar';

// Props and Emits
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  question: {
    type: Object,
    default: null
  },
  categories: {
    type: Array,
    default: () => ['General', 'Technical', 'Behavioral', 'Experience', 'Education']
  },
  questionTypes: {
    type: Array,
    default: () => ['text', 'textarea', 'select', 'multi-select', 'media', 'file']
  }
});

const emit = defineEmits(['update:modelValue', 'save']);

// Composables
const $q = useQuasar();

// State
const dialogModel = ref(props.modelValue);
const saving = ref(false);
const errors = ref({});

// Form data
const form = ref({
  title: '',
  type: '',
  category: [],
  language: 'en',
  tags: [],
  options: '',
  default_answer: '',
  is_required: false,
  description: ''
});

// Options
const typeOptions = computed(() => [
  { label: 'Text Input', value: 'text' },
  { label: 'Textarea', value: 'textarea' },
  { label: 'Single Select', value: 'select' },
  { label: 'Multi Select', value: 'multi-select' },
  { label: 'Media Upload', value: 'media' },
  { label: 'File Upload', value: 'file' }
]);

const categoryOptions = computed(() => props.categories);

const languageOptions = computed(() => [
  { label: 'English', value: 'en' },
  { label: 'Spanish', value: 'es' },
  { label: 'French', value: 'fr' },
  { label: 'Arabic', value: 'ar' }
]);

const tagOptions = ref(['core', 'optional', 'advanced', 'beginner', 'intermediate']);

// Computed
const isEditing = computed(() => !!props.question);

const isFormValid = computed(() => {
  return form.value.title && 
         form.value.type && 
         form.value.category.length > 0 && 
         form.value.language;
});

// Methods
const resetForm = () => {
  form.value = {
    title: '',
    type: '',
    category: [],
    language: 'en',
    tags: [],
    options: '',
    default_answer: '',
    is_required: false,
    description: ''
  };
  errors.value = {};
};

const populateForm = (question) => {
  if (question) {
    form.value = {
      title: question.title || '',
      type: question.type || '',
      category: Array.isArray(question.category) ? question.category : [question.category].filter(Boolean),
      language: question.language || 'en',
      tags: Array.isArray(question.tags) ? question.tags : [],
      options: Array.isArray(question.options) ? question.options.join(', ') : (question.options || ''),
      default_answer: question.default_answer || '',
      is_required: question.is_required || false,
      description: question.description || ''
    };
  } else {
    resetForm();
  }
};

const handleSave = () => {
  if (!isFormValid.value) {
    $q.notify({
      type: 'negative',
      message: 'Please fill in all required fields',
      position: 'top'
    });
    return;
  }

  saving.value = true;
  errors.value = {};

  // Prepare data for API
  const formData = {
    ...form.value,
    options: form.value.options ? form.value.options.split(',').map(opt => opt.trim()).filter(Boolean) : []
  };

  // Add ID if editing
  if (isEditing.value) {
    formData.id = props.question.id;
  }

  emit('save', formData);
};

const handleSaveComplete = () => {
  saving.value = false;
  dialogModel.value = false;
};

const handleSaveError = (error) => {
  saving.value = false;
  if (error.response?.data?.errors) {
    errors.value = error.response.data.errors;
  }
};

// Watchers
watch(() => props.modelValue, (val) => {
  dialogModel.value = val;
  if (val) {
    populateForm(props.question);
  }
});

watch(() => props.question, (question) => {
  if (dialogModel.value) {
    populateForm(question);
  }
});

// Expose methods for parent component
defineExpose({
  handleSaveComplete,
  handleSaveError
});
</script>

<style scoped>
.q-card {
  border-radius: 8px;
}

.q-toggle {
  margin-top: 8px;
}
</style>
