<template>
  <div class="image-uploader">
    <input type="file" accept="image/*" @change="onFileChange" />

    <div v-if="preview" class="preview-wrapper">
      <canvas ref="canvas" class="preview"></canvas>
      <div class="controls">
        <q-btn color="primary" icon="check" label="Use Image" @click="confirm" />
        <q-btn flat color="warning" label="Choose Another" @click="reset" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const canvas = ref(null)
const preview = ref(false)
let imageData = null

const emit = defineEmits(['uploaded'])

function onFileChange(e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = () => {
    const img = new Image()
    img.onload = () => {
      const c = canvas.value
      const ctx = c.getContext('2d')

      // Crop to square (center)
      const size = Math.min(img.width, img.height)
      const sx = (img.width - size) / 2
      const sy = (img.height - size) / 2

      // Resize down to avatar size (e.g. 256x256)
      const targetSize = 256
      c.width = targetSize
      c.height = targetSize

      ctx.drawImage(img, sx, sy, size, size, 0, 0, targetSize, targetSize)

      preview.value = true
    }
    img.src = reader.result
  }
  reader.readAsDataURL(file)
}

function reset() {
  preview.value = false
  imageData = null
}

function confirm() {
  const c = canvas.value
  c.toBlob(
    blob => {
      const reader = new FileReader()
      reader.onloadend = () => {
        imageData = reader.result
        emit('uploaded', { blob, base64: imageData })
      }
      reader.readAsDataURL(blob)
    },
    'image/jpeg',
    0.8 // compression quality
  )
}
</script>

<style scoped>
.image-uploader {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.preview-wrapper {
  margin-top: 10px;
}
.preview {
  width: 256px;
  height: 256px;
  border-radius: 8px;
  border: 2px solid #ccc;
  object-fit: cover;
}
.controls {
  margin-top: 10px;
  display: flex;
  gap: 10px;
}
</style>
