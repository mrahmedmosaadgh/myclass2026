<template>
    <!-- 'element-editable': isEditable, -->
    <!-- :contenteditable="isEditable" -->

  <div v-if="shouldShowElement"
    class="element-content"
    :class="{
      'element-hidden': !element?.visible && !readonly,
      'video-element': element.type === 'video',
      'presentation-mode': readonly,
      'text-element': element.type === 'text',
      'hover-effect': !readonly,
      'is-editing': isEditing
    }"
    :style="{
      ...element.style,
      opacity: (!element?.visible && !readonly) ? '0.3' : '1',
      filter: (!element?.visible && !readonly) ? 'blur(10px)' : 'none',
      transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
      height: element.height ? `${element.height}px` : 'auto',
      minWidth: element.type === 'text' ? '50px' : '320px',
      minHeight: element.type === 'text' ? '30px' : '180px',
      display: element.type === 'text' ? 'inline-block' : 'block'
    }"
  >
    <!-- Video content -->
    <div v-if="element.type === 'video'" class="video-wrapper">
      <!-- Video Display Section -->
      <div v-if="element.videoUrl" class="video-display-section">
        <!-- YouTube Player -->
        <YoutubeVideoPlayer
          v-if="isYouTubeUrl(element.videoUrl)"
          :video-url="element.videoUrl"
          :controls="true"
          :muted="false"
          :autoplay="false"
          :width="readonly ? '100%' : '640'"
          :height="readonly ? '100%' : '360'"
          class="video-player"
        />
        <!-- Regular Video Player -->
        <VideoPlayer
          v-else
          :video-url="element.videoUrl"
          :poster="element.poster || '/images/preview.jpg'"
          :width="readonly ? '100%' : '640'"
          :height="readonly ? '100%' : '360'"
          class="video-player"
        />
      </div>

      <!-- Video Controls Section -->
      <div v-if="!readonly" class="video-controls-wrapper">
        <div class="video-controls">
          <!-- Type Selection Tabs -->
          <div class="video-type-tabs">
            <button
              @click="setVideoType('youtube')"
              class="tab-button"
              :class="{ 'active': videoType === 'youtube' }"
            >
              <svg class="icon youtube" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
              </svg>
              YouTube
            </button>
            <button
              @click="setVideoType('regular')"
              class="tab-button"
              :class="{ 'active': videoType === 'regular' }"
            >
              <svg class="icon video" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M4 8h16v12H4zm16-2a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h3l2-2h6l2 2h3z"/>
              </svg>
              Video File
            </button>
          </div>

          <!-- Input Section -->
          <div class="video-input-container">
            <div class="input-group">
              <input
                type="text"
                v-model="videoUrl"
                :placeholder="videoType === 'youtube' ? 'Paste YouTube URL...' : 'Enter video URL or path...'"
                class="video-url-input"
                :class="videoType"
                @keyup.enter="handleVideoSubmit"
              />
              <button
                @click="handleVideoSubmit"
                class="submit-button"
                :class="videoType"
                :disabled="!videoUrl.trim()"
              >
                <span class="button-text">Add {{ videoType === 'youtube' ? 'YouTube Video' : 'Video' }}</span>
                <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Placeholder -->
        <div v-if="!element.videoUrl" class="video-placeholder">
          <div class="placeholder-content">
            <div class="icon-container">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="placeholder-icon">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/>
              </svg>
            </div>
            <h3>Add Video Content</h3>
            <p>Supports YouTube and video file URLs</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Text content -->
    <div class="text-content" v-else>
      <text_el
        :content="element.content"
        :readonly="readonly"
        :element-id="element.id"
        @update:content="handleContentInput"
      />
      element.content:{{ element.content }}
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue';
import { usePresentationStore } from '@/Stores/presentationStore';
import YoutubeVideoPlayer from './youtube_VideoPlayer.vue';
import VideoPlayer from './VideoPlayer.vue';
import text_el from './text_el.vue';

// Initialize the presentation store
const store = usePresentationStore();

const videoUrl = ref('');
const videoType = ref('');
const contentEditable = ref(null);

const props = defineProps({
  element: {
    type: Object,
    required: true,
    default:  {
      type: 'text',
      content: '',
      visible: true,
      style: {
        color: '#000000',
        backgroundColor: '#ffffff',
        fontSize: '16px'
      }
    }
  },
  readonly: {
    type: Boolean,
    default: false
  }
});

const isEditable = computed(() => !props.readonly && props.element.type !== 'video');

const emit = defineEmits(['update:element']);
const isEditing = ref(false);

// Move isYouTubeUrl function before the watch
const isYouTubeUrl = (url) => {
  if (!url) return false;
  return url.includes('youtube.com') || url.includes('youtu.be');
};

// Add null check in the watch
watch(() => props.element?.videoUrl, (newUrl) => {
  if (newUrl) {
    videoUrl.value = newUrl;
    videoType.value = isYouTubeUrl(newUrl) ? 'youtube' : 'regular';
  }
}, { immediate: true });

const setVideoType = (type) => {
  videoType.value = type;
  videoUrl.value = '';
};

const handleVideoSubmit = () => {
  if (!videoUrl.value.trim()) return;

  let processedUrl = videoUrl.value.trim();

  // Handle YouTube URLs
  if (isYouTubeUrl(processedUrl)) {
    // Keep YouTube URLs as-is
    const updatedElement = {
      ...props.element,
      videoUrl: processedUrl,
      videoType: 'youtube'
    };

    // Update element in the store if it has an ID
    if (updatedElement.id && store.currentSlide) {
      store.updateElementInCurrentSlide(updatedElement.id, updatedElement);
    }

    // Also emit for backward compatibility
    emit('update:element', updatedElement);
    return;
  }

  // Handle local files
  if (!processedUrl.startsWith('http') && !processedUrl.startsWith('//')) {
    // Remove any leading slashes and add storage prefix
    processedUrl = `/storage/${processedUrl.replace(/^\/+/, '')}`;
  }

  const updatedElement = {
    ...props.element,
    videoUrl: processedUrl,
    videoType: 'regular'
  };

  // Update element in the store if it has an ID
  if (updatedElement.id && store.currentSlide) {
    store.updateElementInCurrentSlide(updatedElement.id, updatedElement);
  }

  // Also emit for backward compatibility
  emit('update:element', updatedElement);
};

const validateUrl = (url) => {
  if (isYouTubeUrl(url)) {
    return /^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)[\w-]{11}/.test(url);
  } else {
    return /\.(mp4|webm|ogg)(\?.*)?$/.test(url);
  }
};

const handleContentInput = (newContent) => {
    console.log('handleContentInput in ElementContent', {
        elementId: props.element.id,
        newContent
    });

    const updatedElement = {
        ...props.element,
        content: newContent
    };

    // Update element in the store
    if (props.element.id && store.currentSlide) {
        store.updateElementInCurrentSlide(props.element.id, updatedElement);
    }

    // Also emit for backward compatibility
    emit('update:element', updatedElement);
};

const handleContentBlur = (event) => {
    console.log('handleContentBlur');

  // Optional: Additional validation or cleanup on blur
  handleContentInput(event);
};

watch(() => props.element?.content, async (newContent) => {
  // Wait for next DOM update cycle
  await nextTick();

  // Check if element exists and is not video type
  if (contentEditable.value && props.element?.type !== 'video') {
    const currentContent = contentEditable.value.innerHTML;
    const newContentValue = newContent || '';

    // Only update if content is different
    if (currentContent !== newContentValue) {
      contentEditable.value.innerHTML = newContentValue;
    }
  }
}, {
  immediate: true,
  deep: true
});

// Compute whether the element should be shown
const shouldShowElement = computed(() => {
  // In presentation mode (readonly), only show visible elements
  if (props.readonly) {
    return props.element.visible;
  }
  // In edit mode, show all elements
  return true;
});
</script>

<style scoped>
.element-content {
  position: relative;
  margin: 4px;
  cursor: default;
}

.presentation-mode {
  cursor: default;
  user-select: none;
}

.element-hidden {
  opacity: 0.3;
  filter: blur(10px);
}

.hover-effect:hover:not(.is-editing) {
  transform: translateY(-2px);
  box-shadow:
    0 12px 20px -4px rgba(0, 0, 0, 0.1),
    0 8px 12px -4px rgba(0, 0, 0, 0.05),
    inset 0 0 0 1px rgba(255, 255, 255, 0.2);
}

.video-wrapper {
  @apply w-full overflow-hidden;
}

.video-display-section {
  @apply relative w-full overflow-hidden rounded-lg;
  background: #000;
  aspect-ratio: 16/9;
}

.video-player {
  @apply w-full h-full object-cover;
}

.video-controls-wrapper {
  @apply p-6;
  background: linear-gradient(to bottom, rgba(248, 250, 252, 0.8), rgba(248, 250, 252, 0.95));
  backdrop-filter: blur(10px);
}

.video-controls {
  @apply rounded-xl overflow-hidden;
  background: white;
  box-shadow:
    0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.video-type-tabs {
  @apply flex border-b border-gray-200;
}

.tab-button {
  @apply flex-1 flex items-center justify-center gap-2 py-4 px-6 text-sm font-medium transition-all duration-200;
  color: #64748b;
}

.tab-button:hover {
  color: #334155;
  background: rgba(248, 250, 252, 0.8);
}

.tab-button.active {
  color: #3b82f6;
  background: white;
  border-bottom: 2px solid #3b82f6;
}

.tab-button .icon {
  @apply w-5 h-5;
}

.icon.youtube {
  color: #ff0000;
}

.icon.video {
  color: #3b82f6;
}

.video-input-container {
  @apply p-6;
}

.input-group {
  @apply flex gap-3;
}

.video-url-input {
  @apply flex-1 px-4 py-3 rounded-lg text-sm transition-all duration-200;
  border: 2px solid transparent;
  background: #f8fafc;
}

.video-url-input:focus {
  @apply outline-none;
  border-color: #3b82f6;
  background: white;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.video-url-input.youtube:focus {
  border-color: #ff0000;
  box-shadow: 0 0 0 3px rgba(255, 0, 0, 0.1);
}

.submit-button {
  @apply px-6 py-3 rounded-lg font-medium text-sm text-white flex items-center gap-2 transition-all duration-200;
  background: #3b82f6;
}

.submit-button:hover:not(:disabled) {
  @apply transform -translate-y-0.5;
  background: #2563eb;
  box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
}

.submit-button.youtube {
  background: #ff0000;
}

.submit-button.youtube:hover:not(:disabled) {
  background: #dc2626;
  box-shadow: 0 4px 6px -1px rgba(255, 0, 0, 0.2);
}

.submit-button:disabled {
  @apply opacity-50 cursor-not-allowed;
}

.arrow-icon {
  @apply w-4 h-4;
}

.video-placeholder {
  @apply mt-6 p-12 rounded-xl text-center;
  background: white;
  border: 2px dashed #e2e8f0;
}

.placeholder-icon {
  @apply w-16 h-16 mx-auto mb-4;
  color: #3b82f6;
}

.placeholder-content h3 {
  @apply text-xl font-semibold mb-2;
  color: #1e293b;
}

.placeholder-content p {
  @apply text-sm;
  color: #64748b;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .element-content {
    background: rgba(30, 41, 59, 0.98);
  }

  .video-controls-wrapper {
    background: linear-gradient(to bottom, rgba(30, 41, 59, 0.8), rgba(30, 41, 59, 0.95));
  }

  .video-controls {
    background: rgba(30, 41, 59, 0.95);
  }

  .tab-button {
    color: #94a3b8;
  }

  .tab-button:hover {
    color: #e2e8f0;
    background: rgba(30, 41, 59, 0.8);
  }

  .video-url-input {
    background: rgba(30, 41, 59, 0.5);
    color: #e2e8f0;
  }

  .video-url-input:focus {
    background: rgba(30, 41, 59, 0.8);
  }

  .video-placeholder {
    background: rgba(30, 41, 59, 0.5);
    border-color: #475569;
  }

  .placeholder-content h3 {
    color: #e2e8f0;
  }

  .placeholder-content p {
    color: #94a3b8;
  }
}

/* Mobile optimization */
@media (max-width: 640px) {
  .video-controls-wrapper {
    @apply p-4;
  }

  .video-input-container {
    @apply p-4;
  }

  .input-group {
    @apply flex-col;
  }

  .submit-button {
    @apply justify-center;
  }

  .video-placeholder {
    @apply p-8;
  }
}
</style>































