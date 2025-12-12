<template>
  <div v-if="modelValue && element" class="modal-backdrop" @click="$emit('update:modelValue', false)">
    <div class="settings-modal" @click.stop>
      <h3>Element Settings</h3>

      <!-- Common settings for all element types -->
      <div class="settings-section">
        <h4>{{ element.type.charAt(0).toUpperCase() + element.type.slice(1) }} Element</h4>
      </div>

      <!-- Video-specific settings -->
      <div v-if="element.type === 'video'" class="settings-section">
        <div class="video-source-tabs">
          <button
            @click="videoSourceType = 'youtube'"
            :class="{ active: videoSourceType === 'youtube' }"
            class="tab-button"
          >
            YouTube
          </button>
          <button
            @click="videoSourceType = 'html5'"
            :class="{ active: videoSourceType === 'html5' }"
            class="tab-button"
          >
            HTML5 Video
          </button>
        </div>

        <!-- YouTube Video Settings -->
        <div v-if="videoSourceType === 'youtube'" class="video-source-content">
          <p>
            <label>
              YouTube URL:
              <input
                type="text"
                :value="element.videoUrl || ''"
                placeholder="https://www.youtube.com/watch?v=..."
                @input="handleVideoUrlInput"
                @change="updateVideoUrl($event.target.value)"
                class="full-width-input"
              >
            </label>
          </p>
          <p class="help-text">Enter a YouTube video URL to embed it in your presentation.</p>
          <div v-if="element.videoUrl" class="video-preview">
            <p>Video Preview:</p>
            <div class="video-container">
              <YoutubeVideoPlayer
                :video-url="element.videoUrl"
                :controls="true"
                :muted="true"
                :autoplay="false"
              />
            </div>
          </div>
        </div>

        <!-- HTML5 Video Settings -->
        <div v-if="videoSourceType === 'html5'" class="video-source-content">
          <div class="video-attributes">
            <h5>Video Attributes</h5>
            <div class="attributes-grid">
              <div>
                <label>
                  Width:
                  <input
                    type="number"
                    v-model="videoAttributes.width"
                    placeholder="640"
                    class="attribute-input"
                  >
                </label>
              </div>
              <div>
                <label>
                  Height:
                  <input
                    type="number"
                    v-model="videoAttributes.height"
                    placeholder="360"
                    class="attribute-input"
                  >
                </label>
              </div>
              <div>
                <label>
                  Poster Image URL:
                  <input
                    type="text"
                    v-model="videoAttributes.poster"
                    placeholder="thumbnail.jpg"
                    class="attribute-input"
                  >
                </label>
              </div>
              <div>
                <label>
                  Preload:
                  <select v-model="videoAttributes.preload" class="attribute-input">
                    <option value="auto">Auto</option>
                    <option value="metadata">Metadata</option>
                    <option value="none">None</option>
                  </select>
                </label>
              </div>
            </div>
            <div class="video-options">
              <label class="checkbox-label">
                <input type="checkbox" v-model="videoAttributes.controls">
                Controls
              </label>
              <label class="checkbox-label">
                <input type="checkbox" v-model="videoAttributes.autoplay">
                Autoplay
              </label>
              <label class="checkbox-label">
                <input type="checkbox" v-model="videoAttributes.muted">
                Muted
              </label>
              <label class="checkbox-label">
                <input type="checkbox" v-model="videoAttributes.loop">
                Loop
              </label>
            </div>
          </div>

          <div class="video-sources">
            <h5>Video Sources</h5>
            <div v-for="(source, index) in videoSources" :key="index" class="source-input">
              <div class="source-row">
                <div class="source-url">
                  <label>
                    URL:
                    <input
                      type="text"
                      v-model="source.src"
                      placeholder="video.mp4"
                      class="full-width-input"
                      @change="updateHtml5Video"
                    >
                  </label>
                </div>
                <div class="source-type">
                  <label>
                    Type:
                    <select v-model="source.type" @change="updateHtml5Video">
                      <option value="video/mp4">MP4</option>
                      <option value="video/webm">WebM</option>
                      <option value="video/ogg">OGG</option>
                    </select>
                  </label>
                </div>
                <button
                  @click="removeSource(index)"
                  class="remove-source-btn"
                  v-if="videoSources.length > 1"
                >
                  ✕
                </button>
              </div>
            </div>
            <button @click="addSource" class="add-source-btn">+ Add Source</button>
          </div>

          <div class="fallback-text">
            <label>
              Fallback Text:
              <input
                type="text"
                v-model="videoAttributes.fallbackText"
                placeholder="Your browser does not support the video tag."
                class="full-width-input"
                @change="updateHtml5Video"
              >
            </label>
          </div>

          <div v-if="hasValidSource" class="video-preview">
            <p>Video Preview:</p>
            <div class="video-container">
              <video
                :width="videoAttributes.width || 640"
                :height="videoAttributes.height || 360"
                :controls="videoAttributes.controls"
                :autoplay="videoAttributes.autoplay"
                :muted="videoAttributes.muted"
                :loop="videoAttributes.loop"
                :poster="videoAttributes.poster"
                :preload="videoAttributes.preload"
              >
                <source
                  v-for="(source, index) in validVideoSources"
                  :key="index"
                  :src="source.src"
                  :type="source.type"
                >
                {{ videoAttributes.fallbackText }}
              </video>
            </div>
          </div>
        </div>
      </div>

      <!-- Text and List styling options -->
      <div v-if="element.type !== 'video'" class="settings-section">
        <p>
          <label>
            Content:
            <textarea
              :value="element.content"
              @input="updateContent($event.target.value)"
              class="content-input"
            ></textarea>
          </label>
        </p>
        <p>
          <label>
            Text Color:
            <input
              type="color"
              :value="element.style.color"
              @change="updateStyle('color', $event.target.value)"
            >
          </label>
        </p>
        <p>
          <label>
            Background Color:
            <input
              type="color"
              :value="element.style.backgroundColor"
              @change="updateStyle('backgroundColor', $event.target.value)"
            >
          </label>
        </p>
        <p>
          <label>
            Font Size:
            <input
              type="number"
              :value="parseInt(element.style.fontSize)"
              min="8"
              max="72"
              step="1"
              style="width: 60px"
              @change="updateStyle('fontSize', $event.target.value + 'px')"
            >
            px
          </label>
        </p>
      </div>

      <div class="modal-footer">
        <button @click="$emit('update:modelValue', false)" class="close-button">Close</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import YoutubeVideoPlayer from './youtube_VideoPlayer.vue';

const props = defineProps({
  modelValue: Boolean,
  element: {
    type: Object,
    required: true,
    default: () => ({
      type: 'text',
      style: {
        color: '#000000',
        backgroundColor: '#ffffff',
        fontSize: '16px'
      },
      videoUrl: '',
      videoType: 'youtube', // 'youtube' or 'html5'
      videoAttributes: {
        width: 640,
        height: 360,
        controls: true,
        autoplay: false,
        muted: false,
        loop: false,
        poster: '',
        preload: 'auto',
        fallbackText: 'Your browser does not support the video tag.'
      },
      videoSources: [
        { src: '', type: 'video/mp4' }
      ]
    })
  }
});

// Video source type (youtube or html5)
const videoSourceType = ref(props.element.videoType || 'youtube');

// Video attributes for HTML5 video
const videoAttributes = ref({
  width: props.element.videoAttributes?.width || 640,
  height: props.element.videoAttributes?.height || 360,
  controls: props.element.videoAttributes?.controls !== false,
  autoplay: props.element.videoAttributes?.autoplay || false,
  muted: props.element.videoAttributes?.muted || false,
  loop: props.element.videoAttributes?.loop || false,
  poster: props.element.videoAttributes?.poster || '',
  preload: props.element.videoAttributes?.preload || 'auto',
  fallbackText: props.element.videoAttributes?.fallbackText || 'Your browser does not support the video tag.'
});

// Video sources for HTML5 video
const videoSources = ref(props.element.videoSources?.length ?
  [...props.element.videoSources] :
  [{ src: '', type: 'video/mp4' }]
);

// Computed property to check if there's at least one valid source
const hasValidSource = computed(() => {
  return videoSources.value.some(source => source.src);
});

// Computed property to filter out sources without src
const validVideoSources = computed(() => {
  return videoSources.value.filter(source => source.src);
});

// Ref for video preview URL to enable real-time preview
const videoPreviewUrl = ref(props.element.videoUrl ? getEmbedUrl(props.element.videoUrl) : '');

const emit = defineEmits(['update:modelValue', 'updateStyle', 'update:element']);

const updateStyle = (property, value) => {
  emit('updateStyle', { property, value });
};

const updateVideoUrl = (url) => {
  // Create a copy of the element with the updated videoUrl
  const updatedElement = {
    ...props.element,
    videoUrl: url,
    videoType: 'youtube'
  };

  // Update the content to display the embedded video
  if (url) {
    updatedElement.content = `<div class="video-embed-container">${getEmbedHtml(url)}</div>`;
  } else {
    updatedElement.content = '<div class="video-placeholder">Enter YouTube URL</div>';
  }

  // Emit the updated element
  emit('update:element', updatedElement);
};

// Handle input for real-time preview
const handleVideoUrlInput = (event) => {
  const url = event.target.value;
  if (url) {
    videoPreviewUrl.value = getEmbedUrl(url);
  } else {
    videoPreviewUrl.value = '';
  }
};

// Add a new source for HTML5 video
const addSource = () => {
  videoSources.value.push({ src: '', type: 'video/mp4' });
  updateHtml5Video();
};

// Remove a source
const removeSource = (index) => {
  videoSources.value.splice(index, 1);
  updateHtml5Video();
};

// Update HTML5 video content
const updateHtml5Video = () => {
  // Create a copy of the element with updated HTML5 video properties
  const updatedElement = {
    ...props.element,
    videoType: 'html5',
    videoAttributes: { ...videoAttributes.value },
    videoSources: [...videoSources.value]
  };

  // Generate HTML5 video tag
  const videoHtml = generateHtml5VideoTag();
  updatedElement.content = `<div class="video-embed-container">${videoHtml}</div>`;

  // Emit the updated element
  emit('update:element', updatedElement);
};

// Generate HTML5 video tag
const generateHtml5VideoTag = () => {
  const attrs = videoAttributes.value;
  let html = `<video`;

  // Add attributes
  if (attrs.width) html += ` width="${attrs.width}"`;
  if (attrs.height) html += ` height="${attrs.height}"`;
  if (attrs.controls) html += ` controls`;
  if (attrs.autoplay) html += ` autoplay`;
  if (attrs.muted) html += ` muted`;
  if (attrs.loop) html += ` loop`;
  if (attrs.poster) html += ` poster="${attrs.poster}"`;
  if (attrs.preload) html += ` preload="${attrs.preload}"`;

  html += `>\n`;

  // Add sources
  videoSources.value.forEach(source => {
    if (source.src) {
      html += `  <source src="${source.src}" type="${source.type}">\n`;
    }
  });

  // Add fallback text
  html += `  ${attrs.fallbackText}\n`;
  html += `</video>`;

  return html;
};

// Watch for changes to the element prop
watch(() => props.element.videoUrl, (newUrl) => {
  if (newUrl) {
    videoPreviewUrl.value = getEmbedUrl(newUrl);
  } else {
    videoPreviewUrl.value = '';
  }
});

// Watch for changes to videoSourceType
watch(videoSourceType, (newType) => {
  if (newType === 'html5' && hasValidSource.value) {
    updateHtml5Video();
  }
});

const getEmbedUrl = (url) => {
  // Extract video ID from various YouTube URL formats
  let videoId = '';

  if (url.includes('youtube.com/watch')) {
    const urlParams = new URLSearchParams(new URL(url).search);
    videoId = urlParams.get('v');
  } else if (url.includes('youtu.be/')) {
    videoId = url.split('youtu.be/')[1]?.split('?')[0];
  } else if (url.includes('youtube.com/embed/')) {
    videoId = url.split('youtube.com/embed/')[1]?.split('?')[0];
  }

  if (!videoId) return '';

  // Return the embed URL with the video ID and parameters for better control
  // enablejsapi=1 allows JavaScript API control
  // rel=0 prevents showing related videos
  // modestbranding=1 shows minimal YouTube branding
  return `https://www.youtube.com/embed/${videoId}?enablejsapi=1&rel=0&modestbranding=1&controls=1`;
};

const getEmbedHtml = (url) => {
  if (!url) return '';

  // For YouTube videos, use our custom component
  if (url.includes('youtube.com') || url.includes('youtu.be')) {
    // Extract video ID
    let videoId = '';
    if (url.includes('youtube.com/watch')) {
      const urlParams = new URLSearchParams(new URL(url).search);
      videoId = urlParams.get('v');
    } else if (url.includes('youtu.be/')) {
      videoId = url.split('youtu.be/')[1]?.split('?')[0];
    } else if (url.includes('youtube.com/embed/')) {
      videoId = url.split('youtube.com/embed/')[1]?.split('?')[0];
    }

    if (!videoId) return '';

    // Create a responsive YouTube embed with our custom player
    return `<div class="video-embed-wrapper">
      <div class="youtube-player-container">
        <iframe
          src="https://www.youtube.com/embed/${videoId}?enablejsapi=1&rel=0&modestbranding=1&controls=1"
          width="100%"
          height="100%"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
        ></iframe>
      </div>
    </div>`;
  }
  // For HTML5 videos, use the native video tag
  else if (videoSourceType.value === 'html5') {
    return generateHtml5VideoTag();
  }

  return '';
};

const updateContent = (value) => {
  const updatedElement = {
    ...props.element,
    content: value
  };
  emit('update:element', updatedElement);
};
</script>

<style scoped>
.settings-modal {
  max-width: 600px;
  max-height: 80vh;
  overflow-y: auto;
}

.settings-section {
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #eee;
}

.settings-section h4 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #333;
}

.settings-section h5 {
  margin-top: 15px;
  margin-bottom: 10px;
  color: #444;
  font-size: 1em;
}

.full-width-input {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.attribute-input {
  width: 100%;
  padding: 6px;
  margin-top: 5px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.help-text {
  font-size: 0.9em;
  color: #666;
  margin-top: 5px;
}

.video-preview {
  margin-top: 15px;
}

.video-container {
  position: relative;
  padding-bottom: 56.25%; /* 16:9 aspect ratio */
  height: 0;
  overflow: hidden;
  margin-top: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.video-container iframe,
.video-container video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

/* YouTube embed styles */
.video-embed-wrapper {
  width: 100%;
  position: relative;
}

.youtube-player-container {
  position: relative;
  width: 100%;
  height: 0;
  padding-bottom: 56.25%; /* 16:9 aspect ratio */
  overflow: hidden;
  background-color: #000;
  border-radius: 4px;
}

.youtube-player-container iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: none;
}

.modal-footer {
  margin-top: 20px;
  text-align: right;
}

.close-button {
  padding: 8px 16px;
  background-color: #4a5568;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.close-button:hover {
  background-color: #2d3748;
}

/* Video source tabs */
.video-source-tabs {
  display: flex;
  margin-bottom: 15px;
  border-bottom: 1px solid #ddd;
}

.tab-button {
  padding: 8px 16px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  font-weight: 500;
  color: #666;
  transition: all 0.2s ease;
}

.tab-button:hover {
  color: #333;
}

.tab-button.active {
  color: #4a5568;
  border-bottom-color: #4a5568;
}

.video-source-content {
  padding: 10px 0;
}

/* Video attributes */
.attributes-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 15px;
}

.video-options {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 20px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 5px;
  cursor: pointer;
}

/* Video sources */
.video-sources {
  margin-bottom: 20px;
}

.source-input {
  margin-bottom: 10px;
}

.source-row {
  display: flex;
  gap: 10px;
  align-items: flex-end;
}

.source-url {
  flex: 1;
}

.source-type {
  /* width: 100px; */
}

.source-type select {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.remove-source-btn {
  background: #f56565;
  color: white;
  border: none;
  border-radius: 4px;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s ease;
}

.remove-source-btn:hover {
  background: #e53e3e;
}

.add-source-btn {
  background: #4299e1;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 8px 12px;
  cursor: pointer;
  transition: background 0.2s ease;
  margin-top: 5px;
}

.add-source-btn:hover {
  background: #3182ce;
}

.fallback-text {
  margin-bottom: 20px;
}
</style>

