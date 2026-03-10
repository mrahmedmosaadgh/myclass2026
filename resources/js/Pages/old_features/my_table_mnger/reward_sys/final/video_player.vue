<template>
  <div class="video-player-container">
    <!-- File Input (styled as a button) -->
    <div v-if="!videoSrc" class="upload-area">
      <label for="video-input" class="custom-file-upload">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
          <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
          <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
        </svg>
        <span>Click to select a video file</span>
      </label>
      <input
        id="video-input"
        type="file"
        accept="video/mp4,video/webm,video/ogg,video/avi,video/mov"
        @change="onFileChange"
        hidden
      />
    </div>

    <!-- Video Player -->
    <div v-else class="video-wrapper">
      <video
        ref="videoElement"
        :src="videoSrc"
        controls
        controlsList="nodownload"
        class="video-element"
        @contextmenu.prevent
      >
        Your browser does not support the video tag.
      </video>

      <!-- Custom Fullscreen Button (in case controls are hidden) -->
      <button class="fullscreen-btn" @click="toggleFullscreen" title="Fullscreen">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
          <path d="M5.5 0a.5.5 0 0 1 .5.5v4A1.5 1.5 0 0 1 4.5 6h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5zm5 0a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 10.5 4.5v-4a.5.5 0 0 1 .5-.5zm-5 10a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 4.5 11.5v-4a.5.5 0 0 1 .5-.5zm5 0a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4a1.5 1.5 0 0 1-1.5-1.5v-4a.5.5 0 0 1 .5-.5z"/>
        </svg>
      </button>

      <!-- Reset Button -->
      <button class="reset-btn" @click="resetVideo" title="Choose another video">
        ×
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const videoSrc = ref('')
const videoElement = ref(null)

const onFileChange = (e) => {
  const file = e.target.files[0]
  if (file && file.type.startsWith('video/')) {
    videoSrc.value = URL.createObjectURL(file)
  } else {
    alert('Please select a valid video file.')
  }
}

const toggleFullscreen = () => {
  if (!videoElement.value) return

  if (document.fullscreenElement) {
    document.exitFullscreen()
  } else {
    videoElement.value.requestFullscreen?.() ||
    videoElement.value.webkitRequestFullscreen?.() ||
    videoElement.value.msRequestFullscreen?.()
  }
}

const resetVideo = () => {
  if (videoSrc.value) {
    URL.revokeObjectURL(videoSrc.value)
  }
  videoSrc.value = ''
  // Reset the input so the same file can be selected again
  const input = document.getElementById('video-input')
  if (input) input.value = ''
}
</script>

<style scoped>
.video-player-container {
  max-width: 100%;
  margin: 2rem auto;
  padding: 1rem;
  font-family: system-ui, sans-serif;
}

.upload-area {
  text-align: center;
  padding: 4rem 2rem;
  border: 3px dashed #ccc;
  border-radius: 12px;
  background-color: #f9f9f9;
  cursor: pointer;
  transition: all 0.3s ease;
}

.upload-area:hover {
  border-color: #007bff;
  background-color: #f0f8ff;
}

.custom-file-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  color: #555;
  cursor: pointer;
}

.custom-file-upload svg {
  color: #007bff;
}

.video-wrapper {
  position: relative;
  background: #000;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.video-element {
  width: 100%;
  max-height: 80vh;
  display: block;
}

.fullscreen-btn,
.reset-btn {
  position: absolute;
  background: rgba(0, 0, 0, 0.6);
  color: white;
  border: none;
  border-radius: 50%;
  width: 44px;
  height: 44px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  z-index: 10;
}

.fullscreen-btn {
  bottom: 20px;
  right: 70px;
}

.reset-btn {
  top: 15px;
  right: 15px;
  font-size: 28px;
  font-weight: bold;
}

.fullscreen-btn:hover,
.reset-btn:hover {
  background: rgba(0, 0, 0, 0.8);
  transform: scale(1.1);
}
</style>