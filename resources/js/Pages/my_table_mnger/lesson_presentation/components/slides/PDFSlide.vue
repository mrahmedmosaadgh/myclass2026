<template>
  <div class="pdf-slide-container">
    <!-- Upload/Link Selection (when no PDF) -->
    <div v-if="!hasPDF" class="upload-area">
      <q-card flat bordered class="upload-card">
        <q-card-section class="q-pa-lg">
          <!-- Tab Selection -->
          <q-tabs
            v-model="inputMethod"
            dense
            class="text-grey-7 q-mb-md"
            active-color="primary"
            indicator-color="primary"
            align="justify"
          >
            <q-tab name="url" icon="link" label="Google Drive Link" />
            <q-tab name="upload" icon="upload_file" label="Upload File" />
          </q-tabs>

          <q-separator class="q-mb-lg" />

          <!-- URL Input Tab -->
          <q-tab-panels v-model="inputMethod" animated>
            <q-tab-panel name="url" class="q-pa-none">
              <div class="column items-center">
                <q-icon name="cloud" size="60px" color="primary" class="q-mb-md" />
                <div class="text-h6 text-grey-8 q-mb-sm">Add PDF Link</div>
                <div class="text-caption text-grey-6 q-mb-lg text-center">
                  Paste a Google Drive PDF link or direct PDF URL
                </div>

                <q-input
                  v-model="pdfUrl"
                  outlined
                  dense
                  placeholder="https://drive.google.com/file/d/.../view"
                  class="full-width q-mb-md"
                  :rules="[val => !val || isValidUrl(val) || 'Please enter a valid URL']"
                >
                  <template v-slot:prepend>
                    <q-icon name="link" />
                  </template>
                </q-input>

                <q-btn
                  unelevated
                  color="primary"
                  icon="add"
                  label="Add PDF"
                  @click="addPDFFromUrl"
                  :disable="!pdfUrl || !isValidUrl(pdfUrl)"
                  class="full-width"
                />

                <div class="q-mt-md text-caption text-grey-6">
                  <div class="q-mb-xs"><strong>Supported:</strong></div>
                  <div>• Google Drive: drive.google.com/file/d/.../view</div>
                  <div>• Direct PDF URLs: ending with .pdf</div>
                </div>
              </div>
            </q-tab-panel>

            <!-- File Upload Tab -->
            <q-tab-panel name="upload" class="q-pa-none">
              <div class="column items-center">
                <q-icon name="picture_as_pdf" size="60px" color="grey-5" class="q-mb-md" />
                <div class="text-h6 text-grey-7 q-mb-sm">Upload PDF File</div>
                <div class="text-caption text-grey-6 q-mb-lg">Maximum file size: 3MB</div>
                
                <q-btn
                  unelevated
                  color="primary"
                  icon="upload_file"
                  label="Choose PDF File"
                  @click="triggerFileInput"
                  class="full-width"
                />
                
                <div class="text-caption text-grey-5 q-mt-sm">or drag and drop here</div>
              </div>
            </q-tab-panel>
          </q-tab-panels>
        </q-card-section>
      </q-card>
      
      <!-- Hidden file input -->
      <input
        ref="fileInput"
        type="file"
        accept=".pdf,application/pdf"
        @change="handleFileSelect"
        style="display: none"
      />
    </div>

    <!-- PDF Viewer (when PDF exists) -->
    <div v-else class="pdf-viewer">
      <!-- PDF Info Bar -->
      <q-card flat bordered class="info-bar q-mb-sm">
        <q-card-section class="row items-center q-pa-sm">
          <q-icon name="picture_as_pdf" color="red-6" size="24px" class="q-mr-sm" />
          <div class="col">
            <div class="text-weight-medium">{{ getPDFTitle() }}</div>
            <div class="text-caption text-grey-6">{{ getPDFSource() }}</div>
          </div>
          <q-btn
            flat
            round
            dense
            icon="close"
            color="negative"
            @click="removePDF"
          >
            <q-tooltip>Remove PDF</q-tooltip>
          </q-btn>
        </q-card-section>
      </q-card>

      <!-- PDF Preview -->
      <q-card flat bordered class="pdf-preview">
        <iframe
          :src="pdfInfo.pdfData || pdfInfo.embedUrl"
          class="pdf-iframe"
          frameborder="0"
        ></iframe>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useQuasar } from 'quasar';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue']);

const $q = useQuasar();
const fileInput = ref(null);
const inputMethod = ref('url');
const pdfUrl = ref('');

const pdfInfo = ref({
  type: props.modelValue?.type || null, // 'gdrive', 'direct', 'upload'
  pdfData: props.modelValue?.pdfData || null,
  embedUrl: props.modelValue?.embedUrl || null,
  originalUrl: props.modelValue?.originalUrl || null,
  filename: props.modelValue?.filename || '',
  fileSize: props.modelValue?.fileSize || 0
});

const hasPDF = computed(() => !!pdfInfo.value.pdfData || !!pdfInfo.value.embedUrl);

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    pdfInfo.value = {
      type: newVal.type || null,
      pdfData: newVal.pdfData || null,
      embedUrl: newVal.embedUrl || null,
      originalUrl: newVal.originalUrl || null,
      filename: newVal.filename || '',
      fileSize: newVal.fileSize || 0
    };
  }
}, { deep: true });

const isValidUrl = (url) => {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
};

const extractGoogleDriveId = (url) => {
  const match = url.match(/\/file\/d\/([^\/]+)/);
  return match ? match[1] : null;
};

const addPDFFromUrl = () => {
  if (!pdfUrl.value || !isValidUrl(pdfUrl.value)) return;

  const url = pdfUrl.value.trim();
  
  // Check if Google Drive
  const gdriveId = extractGoogleDriveId(url);
  if (gdriveId) {
    pdfInfo.value = {
      type: 'gdrive',
      embedUrl: `https://drive.google.com/file/d/${gdriveId}/preview`,
      pdfData: null,
      originalUrl: url,
      filename: 'Google Drive PDF',
      fileSize: 0
    };
    emit('update:modelValue', pdfInfo.value);
    pdfUrl.value = '';
    
    $q.notify({
      type: 'positive',
      message: 'Google Drive PDF added successfully',
      icon: 'check_circle',
      position: 'top'
    });
    return;
  }

  // Check if direct PDF URL
  if (url.match(/\.pdf$/i) || url.includes('pdf')) {
    pdfInfo.value = {
      type: 'direct',
      pdfData: url,
      embedUrl: null,
      originalUrl: url,
      filename: url.split('/').pop() || 'Direct PDF',
      fileSize: 0
    };
    emit('update:modelValue', pdfInfo.value);
    pdfUrl.value = '';
    
    $q.notify({
      type: 'positive',
      message: 'PDF URL added successfully',
      icon: 'check_circle',
      position: 'top'
    });
    return;
  }

  // Unknown format
  $q.notify({
    type: 'warning',
    message: 'URL format not recognized. Please use Google Drive or direct PDF links.',
    icon: 'warning',
    position: 'top',
    timeout: 3000
  });
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  if (file.type !== 'application/pdf') {
    $q.notify({
      type: 'negative',
      message: 'Please select a PDF file',
      icon: 'error',
      position: 'top'
    });
    return;
  }

  // Validate file size (3MB = 3 * 1024 * 1024 bytes)
  const maxSize = 3 * 1024 * 1024;
  if (file.size > maxSize) {
    $q.notify({
      type: 'negative',
      message: 'File size exceeds 3MB limit. Please use Google Drive for larger files.',
      icon: 'error',
      position: 'top',
      timeout: 3000
    });
    return;
  }

  try {
    // Show loading
    $q.loading.show({
      message: 'Loading PDF...'
    });

    // Convert to base64
    const base64 = await fileToBase64(file);
    
    pdfInfo.value = {
      type: 'upload',
      pdfData: base64,
      embedUrl: null,
      originalUrl: null,
      filename: file.name,
      fileSize: file.size
    };

    // Emit update
    emit('update:modelValue', pdfInfo.value);

    $q.loading.hide();
    
    $q.notify({
      type: 'positive',
      message: 'PDF uploaded successfully',
      icon: 'check_circle',
      position: 'top'
    });
  } catch (error) {
    $q.loading.hide();
    console.error('PDF upload error:', error);
    $q.notify({
      type: 'negative',
      message: 'Failed to upload PDF',
      icon: 'error',
      position: 'top'
    });
  }

  // Reset input
  event.target.value = '';
};

const fileToBase64 = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
};

const removePDF = () => {
  $q.dialog({
    title: 'Remove PDF',
    message: 'Are you sure you want to remove this PDF?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    pdfInfo.value = {
      type: null,
      pdfData: null,
      embedUrl: null,
      originalUrl: null,
      filename: '',
      fileSize: 0
    };
    emit('update:modelValue', pdfInfo.value);
    
    $q.notify({
      type: 'info',
      message: 'PDF removed',
      icon: 'info',
      position: 'top'
    });
  });
};

const getPDFTitle = () => {
  if (pdfInfo.value.type === 'gdrive') return 'Google Drive PDF';
  return pdfInfo.value.filename || 'PDF Document';
};

const getPDFSource = () => {
  if (pdfInfo.value.type === 'gdrive') return 'Embedded from Google Drive';
  if (pdfInfo.value.type === 'direct') return 'Direct URL';
  if (pdfInfo.value.type === 'upload') return formatFileSize(pdfInfo.value.fileSize);
  return 'PDF';
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<style scoped>
.pdf-slide-container {
  width: 100%;
  min-height: 500px;
}

.upload-area {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 500px;
}

.upload-card {
  width: 100%;
  max-width: 600px;
  border-radius: 8px;
}

.pdf-viewer {
  width: 100%;
}

.info-bar {
  border-radius: 8px;
}

.pdf-preview {
  width: 100%;
  height: 600px;
  border-radius: 8px;
  overflow: hidden;
}

.pdf-iframe {
  width: 100%;
  height: 100%;
  border: none;
}
</style>
