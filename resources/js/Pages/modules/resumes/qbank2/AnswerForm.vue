<template>
  <q-dialog 
    v-model="dialogModel" 
    persistent 
    @update:model-value="val => $emit('update:modelValue', val)"
  >
    <q-card style="min-width: 500px; max-width: 700px; width: 100%">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ isEditing ? 'Edit Answer' : 'Add New Answer' }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-form @submit.prevent="handleSave" class="q-gutter-md">
        <q-card-section class="q-pt-none">
          <div class="q-gutter-md">
            <!-- User ID - Hidden, will be set by backend automatically -->
            <!-- The authenticated user's ID will be used by the backend controller -->

            <!-- Answer Text -->
            <q-input
              v-model="form.answer_text"
              label="Answer Text *"
              outlined
              dense
              type="textarea"
              rows="5"
              :rules="[val => !!val || 'Answer text is required']"
              :error="!!errors.answer_text"
              :error-message="errors.answer_text"
              hint="The main answer content"
            />

            <!-- Media Links -->
            <q-input
              v-model="mediaLinksText"
              label="Media Links"
              outlined
              dense
              type="textarea"
              rows="3"
              hint="Enter media URLs separated by commas (optional)"
              :error="!!errors.media_links"
              :error-message="errors.media_links"
            />

            <!-- File Upload Section -->
            <div class="q-gutter-sm">
              <div class="text-subtitle2">Attachments</div>
              
              <!-- File Upload -->
              <q-file
                v-model="selectedFiles"
                label="Upload Files"
                outlined
                dense
                multiple
                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.mp4,.mp3"
                max-files="5"
                max-file-size="10485760"
                @rejected="onFileRejected"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_file" />
                </template>
              </q-file>

              <!-- Uploaded Files List -->
              <div v-if="form.attachments && form.attachments.length > 0" class="q-mt-sm">
                <div class="text-caption text-grey-7">Current attachments:</div>
                <q-list dense>
                  <q-item 
                    v-for="(file, index) in form.attachments" 
                    :key="index"
                    dense
                  >
                    <q-item-section avatar>
                      <q-icon :name="getFileIcon(file.type)" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ file.name }}</q-item-label>
                      <q-item-label caption>{{ formatFileSize(file.size) }}</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-btn 
                        icon="delete" 
                        flat 
                        round 
                        dense 
                        size="sm" 
                        color="negative"
                        @click="removeAttachment(index)"
                      />
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
            </div>

            <!-- Status -->
            <q-select
              v-model="form.status"
              :options="statusOptions"
              label="Status"
              outlined
              dense
              emit-value
              map-options
            />

            <!-- Notes -->
            <q-input
              v-model="form.notes"
              label="Notes"
              outlined
              dense
              type="textarea"
              rows="2"
              hint="Optional notes or comments about this answer"
            />

            <!-- Is Public Toggle -->
            <q-toggle
              v-model="form.is_public"
              label="Make this answer public"
              color="primary"
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
import { ref, computed, watch } from 'vue';
import { useQuasar } from 'quasar';

// Props and Emits
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  answer: {
    type: Object,
    default: null
  },
  questionId: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'save']);

// Composables
const $q = useQuasar();

// State
const dialogModel = ref(props.modelValue);
const saving = ref(false);
const errors = ref({});
const selectedFiles = ref(null);

// Form data
const form = ref({
  answer_text: '',
  media_links: [],
  attachments: [],
  status: 'draft',
  notes: '',
  is_public: false
});

// Computed for media links text area
const mediaLinksText = computed({
  get: () => form.value.media_links.join(', '),
  set: (value) => {
    form.value.media_links = value ? value.split(',').map(link => link.trim()).filter(Boolean) : [];
  }
});

// Options
const statusOptions = computed(() => [
  { label: 'Draft', value: 'draft' },
  { label: 'Published', value: 'published' },
  { label: 'Under Review', value: 'review' },
  { label: 'Archived', value: 'archived' }
]);

// Computed
const isEditing = computed(() => !!props.answer);

const isFormValid = computed(() => {
  return form.value.answer_text; // Only answer_text is required from frontend
});

// Methods
const resetForm = () => {
  form.value = {
    answer_text: '',
    media_links: [],
    attachments: [],
    status: 'draft',
    notes: '',
    is_public: false
  };
  errors.value = {};
  selectedFiles.value = null;
};

const populateForm = (answer) => {
  if (answer) {
    form.value = {
      answer_text: answer.answer_text || '',
      media_links: Array.isArray(answer.media_links) ? answer.media_links : [],
      attachments: Array.isArray(answer.attachments) ? answer.attachments : [],
      status: answer.status || 'draft',
      notes: answer.notes || '',
      is_public: answer.is_public || false
    };
  } else {
    resetForm();
  }
};

const handleSave = () => {
  if (!isFormValid.value) {
    $q.notify({
      type: 'negative',
      message: 'Please provide an answer text',
      position: 'top'
    });
    return;
  }

  saving.value = true;
  errors.value = {};

  // Prepare data for API
  const formData = {
    ...form.value,
    question_id: props.questionId
  };

  // Add ID if editing
  if (isEditing.value) {
    formData.id = props.answer.id;
  }

  // Handle file uploads if any
  if (selectedFiles.value && selectedFiles.value.length > 0) {
    formData.new_files = selectedFiles.value;
  }

  emit('save', formData);
};

const handleSaveComplete = () => {
  saving.value = false;
  dialogModel.value = false;
  selectedFiles.value = null;
};

const handleSaveError = (error) => {
  saving.value = false;
  if (error.response?.data?.errors) {
    errors.value = error.response.data.errors;
  }
};

const removeAttachment = (index) => {
  form.value.attachments.splice(index, 1);
};

const onFileRejected = (rejectedEntries) => {
  rejectedEntries.forEach(entry => {
    $q.notify({
      type: 'negative',
      message: `File "${entry.file.name}" was rejected: ${entry.failedPropValidation}`,
      position: 'top'
    });
  });
};

const getFileIcon = (type) => {
  if (type?.includes('image')) return 'image';
  if (type?.includes('video')) return 'videocam';
  if (type?.includes('audio')) return 'audiotrack';
  if (type?.includes('pdf')) return 'picture_as_pdf';
  if (type?.includes('word') || type?.includes('document')) return 'description';
  return 'insert_drive_file';
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Watchers
watch(() => props.modelValue, (val) => {
  dialogModel.value = val;
  if (val) {
    populateForm(props.answer);
  }
});

watch(() => props.answer, (answer) => {
  if (dialogModel.value) {
    populateForm(answer);
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

.q-list {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
}
</style>
