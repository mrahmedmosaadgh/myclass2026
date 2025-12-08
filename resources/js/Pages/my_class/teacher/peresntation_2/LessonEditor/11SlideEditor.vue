<template>
  <div class="q-pa-md column gap-md">

    <!-- Slide Type Selector -->
    <q-select
      v-model="slide.type"
      :options="slideTypes"
      label="Slide Type"
      dense
      filled
      class="q-mb-md"
    />

    <!-- VIDEO SLIDE -->
    <div v-if="slide.type === 'video'" class="q-pa-sm bg-grey-2 rounded-borders">

      <div class="row items-center q-gutter-sm">
        <q-input
          v-model="slide.video.src"
          label="Video URL or local path"
          dense
          filled
          class="col"
          @change="handleUrlChange"
        />
        <q-btn
          flat
          dense
          icon="upload"
          color="primary"
          @click="selectLocalFile"
          title="Select local video file"
        />
      </div>

      <div class="row q-mt-sm q-col-gutter-sm">
        <q-input v-model.number="slide.video.start" type="number" label="Start (sec)" dense outlined class="col" />
        <q-input v-model.number="slide.video.end" type="number" label="End (sec)" dense outlined class="col" />
      </div>

      <q-toggle
        v-model="slide.video.autoNext"
        label="Move to next slide automatically after video ends"
        color="primary"
        class="q-mt-md"
      />

      <video
        v-if="previewSrc"
        ref="videoRef"
        :src="previewSrc"
        controls
        class="q-mt-md rounded-borders shadow-2"
        style="width: 100%; max-height: 400px;"
        @timeupdate="checkVideoEnd"
      />
    </div>

    <!-- QUESTION SLIDE -->
    <div v-else-if="slide.type === 'question'" class="q-pa-sm bg-blue-1 rounded-borders">
      <q-input
        v-model="slide.question.text"
        label="Question Text"
        autogrow
        dense
        filled
        class="q-mb-sm"
      />

      <q-select
        v-model="slide.question.type"
        :options="questionTypes"
        label="Question Type"
        dense
        filled
        class="q-mb-sm"
      />

      <!-- Multiple Choice -->
      <div v-if="slide.question.type === 'multiple'">
        <div
          v-for="(opt, index) in slide.question.options"
          :key="index"
          class="row items-center q-gutter-sm q-mb-xs"
        >
          <q-input v-model="opt.text" label="Option" dense filled class="col" />
          <q-checkbox v-model="opt.correct" label="Correct" color="green" />
          <q-btn flat round icon="delete" color="red" @click="removeOption(index)" />
        </div>
        <q-btn flat icon="add" label="Add Option" color="primary" @click="addOption" />
      </div>

      <!-- True / False -->
      <div v-if="slide.question.type === 'truefalse'">
        <q-option-group
          v-model="slide.question.correct"
          :options="[
            { label: 'True', value: true },
            { label: 'False', value: false }
          ]"
          color="primary"
          inline
        />
      </div>

      <!-- Open Ended -->
      <div v-if="slide.question.type === 'open'">
        <q-input
          v-model="slide.question.answer"
          label="Correct Answer"
          dense
          filled
        />
      </div>
    </div>

    <!-- FOOTER PREVIEW CONTROLS -->
    <div class="row justify-between q-mt-lg">
      <q-btn
        icon="play_arrow"
        label="Preview"
        color="primary"
        @click="previewSlide"
      />
      <q-btn
        icon="save"
        label="Save Slide"
        color="green"
        @click="emitSave"
      />
    </div>

  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  modelValue: Object
})
const emit = defineEmits(['update:modelValue', 'save'])

const slide = ref(JSON.parse(JSON.stringify(props.modelValue || {})))
const previewSrc = ref('')
const videoRef = ref(null)

const slideTypes = [
  { label: 'Video Slide', value: 'video' },
  { label: 'Question Slide', value: 'question' }
]

const questionTypes = [
  { label: 'Multiple Choice', value: 'multiple' },
  { label: 'True / False', value: 'truefalse' },
  { label: 'Open Ended', value: 'open' }
]

// Watch for external updates
watch(() => props.modelValue, (val) => {
  if (val) slide.value = JSON.parse(JSON.stringify(val))
}, { deep: true })

watch(slide, (val) => {
  emit('update:modelValue', val)
}, { deep: true })

// ============ VIDEO =============
const handleUrlChange = () => {
  if (slide.value.video?.src?.startsWith('http')) {
    previewSrc.value = slide.value.video.src
  }
}

const selectLocalFile = () => {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'video/*'
  input.onchange = (e) => {
    const file = e.target.files[0]
    if (file) {
      const blobUrl = URL.createObjectURL(file)
      previewSrc.value = blobUrl
      slide.value.video.src = file.name
      slide.value.video.file = file
    }
  }
  input.click()
}

const checkVideoEnd = () => {
  const v = videoRef.value
  const { start, end, autoNext } = slide.value.video
  if (v && end && v.currentTime >= end) {
    v.pause()
    if (autoNext) emit('nextSlide')
  }
}

// ============ QUESTION ============
const addOption = () => {
  slide.value.question.options.push({ text: '', correct: false })
}
const removeOption = (index) => {
  slide.value.question.options.splice(index, 1)
}

// ============ PREVIEW / SAVE ============
const previewSlide = () => {
  if (slide.value.type === 'video') {
    const v = videoRef.value
    if (v) {
      v.currentTime = slide.value.video.start || 0
      v.play()
    }
  } else if (slide.value.type === 'question') {
    const question = slide.value.question
    if (!question || !question.text) {
      alert('No question text defined yet.')
      return
    }
    alert(`Question Preview:\n${question.text}`)
  }
}










const emitSave = () => {
  emit('save', slide.value)
}
</script>
