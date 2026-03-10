<template>
  <div class="video-slide-container">
    <!-- Upload/Embed Selection (when no video) -->
    <div v-if="!hasVideo" class="upload-area">
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
            <q-tab name="url" icon="link" label="URL / Embed" />
            <q-tab name="local" icon="folder_open" label="Open from Device" />
            <q-tab name="upload" icon="cloud_upload" label="Upload to Server" />
          </q-tabs>

          <q-separator class="q-mb-lg" />

          <!-- URL/Embed Input Tab -->
          <q-tab-panels v-model="inputMethod" animated>
            <q-tab-panel name="url" class="q-pa-none">
              <div class="column items-center">
                <q-icon name="play_circle" size="60px" color="primary" class="q-mb-md" />
                <div class="text-h6 text-grey-8 q-mb-sm">Add Video Link</div>
                <div class="text-caption text-grey-6 q-mb-lg text-center">
                  Paste a YouTube link, Google Drive link, or direct video URL
                </div>

                <q-input
                  v-model="videoUrl"
                  outlined
                  dense
                  placeholder="https://www.youtube.com/watch?v=..."
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
                  label="Add Video"
                  @click="addVideoFromUrl"
                  :disable="!videoUrl || !isValidUrl(videoUrl)"
                  class="full-width"
                />

                <div class="q-mt-md text-caption text-grey-6">
                  <div class="q-mb-xs"><strong>Supported:</strong></div>
                  <div>• YouTube: youtube.com/watch?v=... or youtu.be/...</div>
                  <div>• Google Drive: drive.google.com/file/d/.../view</div>
                  <div>• Direct URLs: .mp4, .webm, .ogg files</div>
                </div>
              </div>
            </q-tab-panel>

            <!-- Local File Tab -->
            <q-tab-panel name="local" class="q-pa-none">
              <div class="column items-center">
                <q-icon name="folder_open" size="60px" color="orange-6" class="q-mb-md" />
                <div class="text-h6 text-grey-7 q-mb-sm">Open from Device</div>
                <div class="text-caption text-grey-6 q-mb-lg text-center">
                  Select a video file from your device to display<br/>
                  <strong>Note:</strong> File stays on your device (not uploaded)
                </div>
                
                <q-btn
                  unelevated
                  color="orange-6"
                  icon="folder_open"
                  label="Choose Video from Device"
                  @click="triggerLocalFileInput"
                  class="full-width"
                />
                
                <div class="q-mt-md text-caption text-grey-6">
                  <div class="q-mb-xs"><strong>Supported formats:</strong></div>
                  <div>MP4, WebM, OGG</div>
                  <div class="q-mt-sm text-warning">⚠️ File will only be accessible on this device</div>
                </div>
              </div>
            </q-tab-panel>

            <!-- File Upload Tab -->
            <q-tab-panel name="upload" class="q-pa-none">
              <div class="column items-center">
                <q-icon name="cloud_upload" size="60px" color="grey-5" class="q-mb-md" />
                <div class="text-h6 text-grey-7 q-mb-sm">Upload to Server</div>
                <div class="text-caption text-grey-6 q-mb-lg text-center">
                  Upload video to server for permanent storage<br/>
                  Maximum file size: 50MB
                </div>
                
                <q-btn
                  unelevated
                  color="primary"
                  icon="cloud_upload"
                  label="Upload Video to Server"
                  @click="triggerFileInput"
                  :loading="uploading"
                  class="full-width"
                />
                
                <!-- Upload Progress -->
                <div v-if="uploadProgress > 0 && uploadProgress < 100" class="upload-progress q-mt-md full-width">
                  <q-linear-progress :value="uploadProgress / 100" color="primary" class="q-mb-xs" />
                  <div class="text-caption text-center">{{ uploadProgress }}% uploaded</div>
                </div>
                
                <div class="q-mt-md text-caption text-grey-6">
                  <div>✅ Accessible from any device</div>
                  <div>✅ Permanent storage</div>
                </div>
              </div>
            </q-tab-panel>
          </q-tab-panels>
        </q-card-section>
      </q-card>
      
      <!-- Hidden file inputs -->
      <input
        ref="fileInput"
        type="file"
        accept="video/mp4,video/webm,video/ogg"
        @change="handleFileSelect"
        style="display: none"
      />
      <input
        ref="localFileInput"
        type="file"
        accept="video/mp4,video/webm,video/ogg"
        @change="handleLocalFileSelect"
        style="display: none"
      />
    </div>

    <!-- Video Player (when video exists) -->
    <div v-else class="video-viewer">
      <!-- Video Info Bar -->
      <q-card flat bordered class="info-bar q-mb-sm">
        <q-card-section class="row items-center q-pa-sm">
          <q-icon :name="videoInfo.type === 'youtube' ? 'smart_display' : 'videocam'" color="red-6" size="24px" class="q-mr-sm" />
          <div class="col">
            <div class="text-weight-medium">{{ getVideoTitle() }}</div>
            <div class="text-caption text-grey-6">{{ getVideoSource() }}</div>
          </div>
          <q-btn
            flat
            round
            dense
            icon="close"
            color="negative"
            @click="removeVideo"
          >
            <q-tooltip>Remove Video</q-tooltip>
          </q-btn>
        </q-card-section>
      </q-card>

      <!-- Video Player -->
      <q-card flat bordered class="video-preview">
        <!-- YouTube Embed -->
        <iframe
          v-if="videoInfo.type === 'youtube'"
          :src="videoInfo.embedUrl"
          class="video-iframe"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
        ></iframe>

        <!-- Google Drive Embed -->
        <iframe
          v-else-if="videoInfo.type === 'gdrive'"
          :src="videoInfo.embedUrl"
          class="video-iframe"
          frameborder="0"
          allow="autoplay"
          allowfullscreen
        ></iframe>

        <!-- Direct Video or Uploaded File -->
        <video
          v-else
          :src="videoInfo.videoUrl"
          class="video-player"
          controls
          controlsList="nodownload"
          preload="metadata"
        >
          Your browser does not support the video tag.
        </video>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:modelValue']);

const $q = useQuasar();
const fileInput = ref(null);
const localFileInput = ref(null);
const uploading = ref(false);
const uploadProgress = ref(0);
const inputMethod = ref('url');
const videoUrl = ref('');

const videoInfo = ref({
  type: props.modelValue?.type || null,
  videoUrl: props.modelValue?.videoUrl || null,
  embedUrl: props.modelValue?.embedUrl || null,
  videoPath: props.modelValue?.videoPath || null,
  originalUrl: props.modelValue?.originalUrl || null,
  filename: props.modelValue?.filename || '',
  fileSize: props.modelValue?.fileSize || 0
});

const hasVideo = computed(() => !!videoInfo.value.videoUrl || !!videoInfo.value.embedUrl);

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    videoInfo.value = {
      type: newVal.type || null,
      videoUrl: newVal.videoUrl || null,
      embedUrl: newVal.embedUrl || null,
      videoPath: newVal.videoPath || null,
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

const extractYouTubeId = (url) => {
  const patterns = [
    /(?:youtube\.com\/watch\?v=|youtu.be\/)([^&\s]+)/,
    /youtube\.com\/embed\/([^&\s]+)/
  ];
  
  for (const pattern of patterns) {
    const match = url.match(pattern);
    if (match) return match[1];
  }
  return null;
};

const extractGoogleDriveId = (url) => {
  const match = url.match(/\/file\/d\/([^\/]+)/);
  return match ? match[1] : null;
};

const addVideoFromUrl = () => {
  if (!videoUrl.value || !isValidUrl(videoUrl.value)) return;

  const url = videoUrl.value.trim();
  
  const youtubeId = extractYouTubeId(url);
  if (youtubeId) {
    videoInfo.value = {
      type: 'youtube',
      embedUrl: `https://www.youtube.com/embed/${youtubeId}`,
      originalUrl: url,
      videoUrl: null,
      videoPath: null,
      filename: 'YouTube Video',
      fileSize: 0
    };
    emit('update:modelValue', videoInfo.value);
    videoUrl.value = '';
    
    $q.notify({
      type: 'positive',
      message: 'YouTube video added successfully',
      icon: 'check_circle',
      position: 'top'
    });
    return;
  }

  const gdriveId = extractGoogleDriveId(url);
  if (gdriveId) {
    videoInfo.value = {
      type: 'gdrive',
      embedUrl: `https://drive.google.com/file/d/${gdriveId}/preview`,
      originalUrl: url,
      videoUrl: null,
      videoPath: null,
      filename: 'Google Drive Video',
      fileSize: 0
    };
    emit('update:modelValue', videoInfo.value);
    videoUrl.value = '';
    
    $q.notify({
      type: 'positive',
      message: 'Google Drive video added successfully',
      icon: 'check_circle',
      position: 'top'
    });
    return;
  }

  if (url.match(/\.(mp4|webm|ogg)$/i) || url.includes('blob:') || url.includes('data:')) {
    videoInfo.value = {
      type: 'direct',
      videoUrl: url,
      embedUrl: null,
      originalUrl: url,
      videoPath: null,
      filename: url.split('/').pop() || 'Direct Video',
      fileSize: 0
    };
    emit('update:modelValue', videoInfo.value);
    videoUrl.value = '';
    
    $q.notify({
      type: 'positive',
      message: 'Video URL added successfully',
      icon: 'check_circle',
      position: 'top'
    });
    return;
  }

  $q.notify({
    type: 'warning',
    message: 'URL format not recognized. Please use YouTube, Google Drive, or direct video links.',
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

  const validTypes = ['video/mp4', 'video/webm', 'video/ogg'];
  if (!validTypes.includes(file.type)) {
    $q.notify({
      type: 'negative',
      message: 'Please select a valid video file (MP4, WebM, or OGG)',
      icon: 'error',
      position: 'top'
    });
    return;
  }

  const maxSize = 50 * 1024 * 1024;
  if (file.size > maxSize) {
    $q.notify({
      type: 'negative',
      message: 'File size exceeds 50MB limit. Please use YouTube or Google Drive for larger files.',
      icon: 'error',
      position: 'top',
      timeout: 4000
    });
    return;
  }

  try {
    uploading.value = true;
    uploadProgress.value = 0;

    const formData = new FormData();
    formData.append('video', file);
    formData.append('type', 'lesson_video');

    const response = await axios.post('/api/upload-media', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
      }
    });

    videoInfo.value = {
      type: 'upload',
      videoUrl: response.data.url,
      videoPath: response.data.path,
      embedUrl: null,
      originalUrl: null,
      filename: file.name,
      fileSize: file.size
    };

    emit('update:modelValue', videoInfo.value);

    $q.notify({
      type: 'positive',
      message: 'Video uploaded successfully',
      icon: 'check_circle',
      position: 'top'
    });
  } catch (error) {
    console.error('Video upload error:', error);
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to upload video',
      icon: 'error',
      position: 'top'
    });
  } finally {
    uploading.value = false;
    uploadProgress.value = 0;
  }

  event.target.value = '';
};

const triggerLocalFileInput = () => {
  localFileInput.value?.click();
};

const handleLocalFileSelect = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const validTypes = ['video/mp4', 'video/webm', 'video/ogg'];
  if (!validTypes.includes(file.type)) {
    $q.notify({
      type: 'negative',
      message: 'Please select a valid video file (MP4, WebM, or OGG)',
      icon: 'error',
      position: 'top'
    });
    return;
  }

  // Create blob URL for local file
  const blobUrl = URL.createObjectURL(file);
  
  videoInfo.value = {
    type: 'local',
    videoUrl: blobUrl,
    embedUrl: null,
    videoPath: null,
    originalUrl: null,
    filename: file.name,
    fileSize: file.size
  };

  emit('update:modelValue', videoInfo.value);

  $q.notify({
    type: 'positive',
    message: 'Video loaded from device',
    icon: 'check_circle',
    position: 'top'
  });

  event.target.value = '';
};

const removeVideo = () => {
  $q.dialog({
    title: 'Remove Video',
    message: 'Are you sure you want to remove this video?',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    // Revoke blob URL if it's a local file
    if (videoInfo.value.type === 'local' && videoInfo.value.videoUrl) {
      URL.revokeObjectURL(videoInfo.value.videoUrl);
    }
    
    // Delete uploaded file from backend
    if (videoInfo.value.type === 'upload' && videoInfo.value.videoPath) {
      try {
        await axios.delete('/api/delete-media', {
          data: { path: videoInfo.value.videoPath }
        });
      } catch (error) {
        console.error('Failed to delete video from server:', error);
      }
    }

    videoInfo.value = {
      type: null,
      videoUrl: null,
      embedUrl: null,
      videoPath: null,
      originalUrl: null,
      filename: '',
      fileSize: 0
    };
    emit('update:modelValue', videoInfo.value);
    
    $q.notify({
      type: 'info',
      message: 'Video removed',
      icon: 'info',
      position: 'top'
    });
  });
};

const getVideoTitle = () => {
  if (videoInfo.value.type === 'youtube') return 'YouTube Video';
  if (videoInfo.value.type === 'gdrive') return 'Google Drive Video';
  return videoInfo.value.filename || 'Video';
};

const getVideoSource = () => {
  if (videoInfo.value.type === 'youtube') return 'Embedded from YouTube';
  if (videoInfo.value.type === 'gdrive') return 'Embedded from Google Drive';
  if (videoInfo.value.type === 'direct') return 'Direct URL';
  if (videoInfo.value.type === 'local') return `Local file - ${formatFileSize(videoInfo.value.fileSize)}`;
  if (videoInfo.value.type === 'upload') return `Uploaded - ${formatFileSize(videoInfo.value.fileSize)}`;
  return 'Video';
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
.video-slide-container {
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

.upload-progress {
  max-width: 300px;
}

.video-viewer {
  width: 100%;
}

.info-bar {
  border-radius: 8px;
}

.video-preview {
  width: 100%;
  border-radius: 8px;
  overflow: hidden;
  background: #000;
}

.video-iframe {
  width: 100%;
  height: 500px;
  border: none;
}

.video-player {
  width: 100%;
  max-height: 600px;
  display: block;
}
</style>
