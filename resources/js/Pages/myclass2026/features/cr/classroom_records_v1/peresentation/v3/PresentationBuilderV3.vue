<template>
  <div class="min-h-screen bg-gray-900">
    <!-- Top Bar -->
    <TopBar
      :mode="mode"
      :current-slide-index="currentSlideIndex"
      :total-slides="slides.length"
      :slide-height="slideHeight"
      @mode-change="mode = $event"
      @export="exportJSON"
      @import="importJSON"
      @delete-slide="deleteSlide"
      @height-change="slideHeight = $event"
    />

    <!-- Main Content -->
    <div class="flex h-screen pt-16">
      <!-- Edit Mode -->
      <template v-if="mode === 'edit'">
        <!-- Left Slide Panel -->
        <SlidePanel
          :slides="slides"
          :current-slide-index="currentSlideIndex"
          @slide-select="currentSlideIndex = $event"
          @add-slide="addSlide"
          @slide-delete="deleteSlide"
        />

        <!-- Editor Canvas -->
        <EditorCanvas
          :current-slide="currentSlide"
          :slide-height="slideHeight"
          @slide-update="updateSlide"
        />
      </template>

      <!-- Present Mode -->
      <PresenterV3
        v-else
        :slides="slides"
        :current-slide-index="currentSlideIndex"
        @slide-change="currentSlideIndex = $event"
        @exit="mode = 'edit'"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import TopBar from './components/TopBar.vue'
import SlidePanel from './components/SlidePanel.vue'
import EditorCanvas from './components/EditorCanvas.vue'
import PresenterV3 from './components/PresenterV3.vue'

// State
const mode = ref('edit') // 'edit' | 'present'
const currentSlideIndex = ref(0)
const slideHeight = ref(1123) // A4 default
const slides = ref([
  {
    id: 1,
    elements: []
  }
])

// Computed
const currentSlide = computed(() => slides.value[currentSlideIndex.value] || { id: 1, elements: [] })

// Methods
const addSlide = () => {
  const newSlide = {
    id: Date.now(),
    elements: []
  }
  slides.value.push(newSlide)
  currentSlideIndex.value = slides.value.length - 1
}

const deleteSlide = (index) => {
  if (slides.value.length > 1) {
    slides.value.splice(index, 1)
    if (currentSlideIndex.value >= slides.value.length) {
      currentSlideIndex.value = slides.value.length - 1
    }
  }
}

const updateSlide = (updatedSlide) => {
  const index = slides.value.findIndex(s => s.id === updatedSlide.id)
  if (index !== -1) {
    slides.value[index] = { ...updatedSlide }
  }
}

const exportJSON = () => {
  const data = {
    version: 'v3',
    timestamp: new Date().toISOString(),
    slides: slides.value,
    slideHeight: slideHeight.value
  }
  
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `presentation-v3-${Date.now()}.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

const importJSON = (file) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    try {
      const data = JSON.parse(e.target.result)
      if (data.slides && Array.isArray(data.slides)) {
        slides.value = data.slides
        if (data.slideHeight) {
          slideHeight.value = data.slideHeight
        }
        currentSlideIndex.value = 0
      }
    } catch (error) {
      console.error('Invalid JSON file:', error)
    }
  }
  reader.readAsText(file)
}
</script>
