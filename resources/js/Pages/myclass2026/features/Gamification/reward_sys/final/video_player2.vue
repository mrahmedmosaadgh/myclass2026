<template>
  <div
    class="video-player-container"
    @drop.prevent="onDrop"
    @dragover.prevent
    @dragenter.prevent="isDragging = true"
    @dragleave.prevent="isDragging = false"
    :class="{ 'drag-over': isDragging }"
  >
    <!-- BIG DRAG & DROP OVERLAY -->
    <div v-if="isDragging" class="drag-overlay">
      <div class="drop-hint">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" viewBox="0 0 16 16">
          <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
          <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
        </svg>
        <p style="font-size: 2rem; margin: 1rem 0 0;">Drop the video here!</p>
      </div>
    </div>

    <!-- WHEN NO VIDEO YET -->
    <div v-if="!videoSrc" class="upload-area">
      <h2>Send me any video in 1 second</h2>

      <!-- 1. Paste from WhatsApp/Telegram (Most Popular in Saudi) -->
      <button @click="pasteFromClipboard" class="big-btn whatsapp">
        Paste from Clipboard (WhatsApp)
      </button>

      <!-- 2. Record directly with camera -->
      <button @click="openCamera" class="big-btn camera">
        Record Video Now
      </button>

      <!-- 3. Choose from gallery -->
      <label for="video-input" class="big-btn gallery">
        Choose from Gallery
      </label>

      <!-- Hidden inputs -->
      <input
        id="video-input"
        type="file"
        accept="video/*"
        @change="onFileChange"
        hidden
      />
      <input
        ref="cameraInput"
        type="file"
        accept="video/*"
        capture="environment"
        @change="onFileChange"
        hidden
      />
    </div>

    <!-- VIDEO PLAYER -->
    <div v-else class="video-wrapper">
      <video
        ref="videoElement"
        :src="videoSrc"
        controls
        controlsList="nodownload"
        class="video-element"
        autoplay
        @contextmenu.prevent
      ></video>

      <button class="fullscreen-btn" @click="toggleFullscreen">
        Fullscreen
      </button>

      <button class="reset-btn" @click="resetVideo">×</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const videoSrc = ref('')
const videoElement = ref(null)
const cameraInput = ref(null)
const isDragging = ref(false)

// 1. Normal file picker + camera
const onFileChange = (e) => {
  const file = e.target.files[0]
  if (file && file.type.startsWith('video/')) {
    loadVideo(file)
  }
}

// 2. Drag & Drop
const onDrop = (e) => {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  if (file && file.type.startsWith('video/')) {
    loadVideo(file)
  }
}

// 3. Paste from clipboard (WhatsApp magic)
const pasteFromClipboard = async () => {
  try {
    const items = await navigator.clipboard.read()
    for (const item of items) {
      const videoType = item.types.find(t => t.startsWith('video/'))
      if (videoType) {
        const blob = await item.getType(videoType)
        loadVideo(blob)
        return
      }
    }
    alert('No video in clipboard')
  } catch (err) {
    alert('Please allow clipboard permission')
  }
}

// 4. Direct camera
const openCamera = () => cameraInput.value?.click()

// Helper
const loadVideo = (fileOrBlob) => {
  if (videoSrc.value) URL.revokeObjectURL(videoSrc.value)
  videoSrc.value = URL.createObjectURL(fileOrBlob)
}

// Fullscreen
const toggleFullscreen = () => {
  if (!videoElement.value) return
  if (document.fullscreenElement) {
    document.exitFullscreen()
  } else {
    videoElement.value.requestFullscreen?.() ||
    videoElement.value.webkitRequestFullscreen?.()
  }
}

// Reset
const resetVideo = () => {
  if (videoSrc.value) URL.revokeObjectURL(videoSrc.value)
  videoSrc.value = ''
  document.getElementById('video-input').value = ''
}
</script>

<style scoped>
.video-player-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
  text-align: center;
  color: white;
  position: relative;
}

.upload-area h2 {
  font-size: 2rem;
  margin-bottom: 2rem;
}

.big-btn {
  display: block;
  width: 90%;
  max-width: 400px;
  margin: 1rem auto;
  padding: 1.5rem;
  font-size: 1.4rem;
  font-weight: bold;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
  transition: all 0.3s;
}

.big-btn:active { transform: scale(0.95); }

.whatsapp { background: #25D366; }
.camera   { background: #FF3B30; }
.gallery  { background: #007AFF; }

.drag-over { border: 6px dashed rgba(255,255,255,0.8) !important; }

.drag-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  border-radius: 20px;
}

.video-wrapper {
  position: relative;
  max-width: 100%;
  margin: 2rem auto;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.5);
}

.video-element {
  width: 100%;
  max-height: 85vh;
  background: #000;
}

.fullscreen-btn, .reset-btn {
  position: absolute;
  background: rgba(0,0,0,0.7);
  color: white;
  border: none;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  font-size: 24px;
  cursor: pointer;
  z-index: 10;
}

.fullscreen-btn { bottom: 30px; right: 30px; }
.reset-btn      { top: 20px; right: 20px; font-size: 36px; }
</style>