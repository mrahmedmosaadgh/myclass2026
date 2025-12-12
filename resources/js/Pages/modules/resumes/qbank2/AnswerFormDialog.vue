<template>
  <q-dialog
    v-model="dialogModel"
    persistent
    :maximized="$q.screen.lt.md"
    :full-width="$q.screen.gt.sm"
    transition-show="slide-up"
    transition-hide="slide-down"
  >
    <q-card style="min-width: 600px; max-width: 800px; width: 100%">
      <!-- Header -->
      <q-card-section class="row items-center q-pb-none bg-primary text-white">
        <div class="text-h6">
          <q-icon name="edit" class="q-mr-sm" />
          {{ isEditing ? 'Edit Answer' : 'Add New Answer' }}
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup color="white" />
      </q-card-section>

      <!-- Form -->
      <q-form @submit="handleSubmit" class="q-pa-md">
        <!-- Answer Text -->
        <div class="q-mb-md">
          <label class="text-subtitle2 q-mb-sm block">Answer Text *</label>
          <q-input
            v-model="form.answer_text"
            type="textarea"
            rows="6"
            outlined
            placeholder="Enter your answer here..."
            :error="!!errors.answer_text"
            :error-message="errors.answer_text"
            counter
            maxlength="5000"
          />
        </div>

        <!-- Media Links -->
        <div class="q-mb-md">
          <label class="text-subtitle2 q-mb-sm block">Media Links</label>
          <q-input
            v-model="mediaLinksText"
            type="textarea"
            rows="3"
            outlined
            placeholder="Enter media URLs (one per line)"
            hint="Add YouTube, image, or other media URLs"
          />
        </div>

        <!-- File Attachments -->
        <div class="q-mb-md">
          <label class="text-subtitle2 q-mb-sm block">File Attachments</label>
          <q-file
            v-model="selectedFiles"
            multiple
            outlined
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.mp4,.mp3"
            max-file-size="10485760"
            @rejected="onRejected"
          >
            <template v-slot:prepend>
              <q-icon name="attach_file" />
            </template>
            <template v-slot:hint>
              Max 10MB per file. Supported: PDF, DOC, images, videos, audio
            </template>
          </q-file>
          
          <!-- File Preview -->
          <div v-if="selectedFiles && selectedFiles.length > 0" class="q-mt-sm">
            <div class="text-caption q-mb-xs">Selected files:</div>
            <q-chip
              v-for="(file, index) in selectedFiles"
              :key="index"
              removable
              @remove="removeFile(index)"
              color="blue-1"
              text-color="blue-8"
              :icon="getFileIcon(file.type)"
            >
              {{ file.name }} ({{ formatFileSize(file.size) }})
            </q-chip>
          </div>
        </div>

        <!-- Voice Note -->
        <div class="q-mb-md">
          <label class="text-subtitle2 q-mb-sm block">Voice Note</label>
          <div class="row q-gutter-sm items-center">
            <q-btn
              :color="isRecording ? 'negative' : 'primary'"
              :icon="isRecording ? 'stop' : 'mic'"
              :label="isRecording ? 'Stop Recording' : 'Record Voice Note'"
              @click="toggleRecording"
            />
            <div v-if="recordedAudio" class="row items-center q-gutter-sm">
              <q-btn
                icon="play_arrow"
                flat
                round
                @click="playRecording"
              />
              <q-btn
                icon="delete"
                flat
                round
                color="negative"
                @click="deleteRecording"
              />
              <span class="text-caption">{{ recordingDuration }}s</span>
            </div>
          </div>
        </div>

        <!-- Status -->
        <div class="q-mb-md">
          <label class="text-subtitle2 q-mb-sm block">Status</label>
          <q-select
            v-model="form.status"
            :options="statusOptions"
            outlined
            emit-value
            map-options
          />
        </div>

        <!-- Notes -->
        <div class="q-mb-md">
          <label class="text-subtitle2 q-mb-sm block">Private Notes</label>
          <q-input
            v-model="form.notes"
            type="textarea"
            rows="3"
            outlined
            placeholder="Add private notes (only visible to you)"
          />
        </div>

        <!-- Public/Private Toggle -->
        <div class="q-mb-md">
          <q-toggle
            v-model="form.is_public"
            label="Make this answer public"
            color="primary"
          />
          <div class="text-caption text-grey-6 q-mt-xs">
            Public answers can be viewed by other users
          </div>
        </div>

        <!-- Actions -->
        <q-card-actions align="right" class="q-pt-md">
          <q-btn
            flat
            label="Cancel"
            color="grey-7"
            v-close-popup
          />
          <q-btn
            type="submit"
            :label="isEditing ? 'Update Answer' : 'Save Answer'"
            color="primary"
            :loading="saving"
            :disable="!form.answer_text.trim()"
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useQuasar } from 'quasar';
import resumeApi from './resumeApi.js';

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
const dialogModel = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const saving = ref(false);
const errors = ref({});
const selectedFiles = ref(null);
const mediaLinksText = ref('');
const isRecording = ref(false);
const recordedAudio = ref(null);
const recordingDuration = ref(0);
const mediaRecorder = ref(null);
const audioChunks = ref([]);

const form = ref({
  answer_text: '',
  media_links: [],
  attachments: [],
  status: 'draft',
  notes: '',
  is_public: false
});

const statusOptions = [
  { label: 'Draft', value: 'draft' },
  { label: 'Published', value: 'published' },
  { label: 'Under Review', value: 'review' },
  { label: 'Archived', value: 'archived' }
];

// Computed
const isEditing = computed(() => !!props.answer);

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
  selectedFiles.value = null;
  mediaLinksText.value = '';
  recordedAudio.value = null;
  errors.value = {};
};

const populateForm = (answer) => {
  if (!answer) return;
  
  form.value = {
    answer_text: answer.answer_text || '',
    media_links: answer.media_links || [],
    attachments: answer.attachments || [],
    status: answer.status || 'draft',
    notes: answer.notes || '',
    is_public: answer.is_public || false
  };
  
  mediaLinksText.value = (answer.media_links || []).join('\n');
};

const handleSubmit = () => {
  if (!validateForm()) return;
  
  saving.value = true;
  errors.value = {};
  
  // Prepare form data
  const payload = {
    ...form.value,
    media_links: mediaLinksText.value
      .split('\n')
      .map(link => link.trim())
      .filter(link => link.length > 0)
  };
  
  // Add voice note if recorded
  if (recordedAudio.value) {
    payload.voice_note = recordedAudio.value;
  }
  
  const apiCall = isEditing.value
    ? resumeApi.updateAnswer(props.answer.id, payload)
    : resumeApi.createAnswer(props.questionId, payload);
  
  apiCall
    .then(() => {
      $q.notify({
        type: 'positive',
        message: `Answer ${isEditing.value ? 'updated' : 'created'} successfully`,
        position: 'top'
      });
      emit('save');
      dialogModel.value = false;
    })
    .catch(error => {
      console.error('Error saving answer:', error);
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors;
      }
      $q.notify({
        type: 'negative',
        message: `Failed to ${isEditing.value ? 'update' : 'create'} answer`,
        position: 'top'
      });
    })
    .finally(() => {
      saving.value = false;
    });
};

const validateForm = () => {
  errors.value = {};
  
  if (!form.value.answer_text.trim()) {
    errors.value.answer_text = 'Answer text is required';
    return false;
  }
  
  return true;
};

const onRejected = (rejectedEntries) => {
  $q.notify({
    type: 'negative',
    message: `${rejectedEntries.length} file(s) rejected. Check file size and type.`,
    position: 'top'
  });
};

const removeFile = (index) => {
  if (selectedFiles.value && Array.isArray(selectedFiles.value)) {
    selectedFiles.value.splice(index, 1);
  }
};

const getFileIcon = (mimeType) => {
  if (mimeType?.includes('image')) return 'image';
  if (mimeType?.includes('video')) return 'videocam';
  if (mimeType?.includes('audio')) return 'audiotrack';
  if (mimeType?.includes('pdf')) return 'picture_as_pdf';
  return 'description';
};

const formatFileSize = (bytes) => {
  if (!bytes) return '';
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};

const toggleRecording = async () => {
  if (isRecording.value) {
    stopRecording();
  } else {
    await startRecording();
  }
};

const startRecording = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    mediaRecorder.value = new MediaRecorder(stream);
    audioChunks.value = [];
    
    mediaRecorder.value.ondataavailable = (event) => {
      audioChunks.value.push(event.data);
    };
    
    mediaRecorder.value.onstop = () => {
      const audioBlob = new Blob(audioChunks.value, { type: 'audio/wav' });
      recordedAudio.value = audioBlob;
      stream.getTracks().forEach(track => track.stop());
    };
    
    mediaRecorder.value.start();
    isRecording.value = true;
    
    // Start timer
    const startTime = Date.now();
    const timer = setInterval(() => {
      if (!isRecording.value) {
        clearInterval(timer);
        return;
      }
      recordingDuration.value = Math.floor((Date.now() - startTime) / 1000);
    }, 1000);
    
  } catch (error) {
    console.error('Error starting recording:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to start recording. Please check microphone permissions.',
      position: 'top'
    });
  }
};

const stopRecording = () => {
  if (mediaRecorder.value && isRecording.value) {
    mediaRecorder.value.stop();
    isRecording.value = false;
  }
};

const playRecording = () => {
  if (recordedAudio.value) {
    const audio = new Audio(URL.createObjectURL(recordedAudio.value));
    audio.play();
  }
};

const deleteRecording = () => {
  recordedAudio.value = null;
  recordingDuration.value = 0;
};

// Watchers
watch(() => props.answer, (newAnswer) => {
  if (newAnswer) {
    populateForm(newAnswer);
  } else {
    resetForm();
  }
}, { immediate: true });

watch(() => props.modelValue, (isVisible) => {
  if (!isVisible) {
    resetForm();
  }
});

// Lifecycle
onUnmounted(() => {
  if (isRecording.value) {
    stopRecording();
  }
});
</script>

<style scoped>
.q-card {
  border-radius: 12px;
}

label.block {
  display: block;
}
</style>
