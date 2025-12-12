<template>
  <div class="avatar-manager relative inline-block" @mouseenter="hover = true" @mouseleave="hover = false">
    <!-- Avatar Container with Grade 4 Styling -->
    <div 
      class="avatar-container relative rounded-full border-4 transition-all duration-300 transform pointer-events-none"
      :class="[
        sizeClasses,
        hover ? 'scale-105 shadow-xl border-yellow-400' : 'shadow-lg border-blue-300',
        editEnabled ? '' : ''
      ]"
    >
      <!-- The Avatar Image -->
      <q-avatar :size="size" class="bg-gray-200">
        <img 
          :src="currentAvatar || placeholderAvatar" 
          class="object-cover w-full h-full rounded-full"
          alt="Student Avatar"
        />
      </q-avatar>

      <!-- Edit Button (Visible on Hover + Enabled) -->
      <div 
        v-if="editEnabled && hover"
        class="absolute bottom-0 right-0 flex gap-1 z-20 pointer-events-auto"
      >
        <!-- Remove Button (Only if avatar exists) -->
        <div 
          v-if="currentAvatar"
          class="bg-red-500 text-white rounded-full p-1.5 shadow-lg border-2 border-white cursor-pointer hover:bg-red-600 transition-colors"
          @click.stop="confirmRemoveAvatar"
        >
          <q-icon name="delete" size="xs" />
        </div>

        <!-- Edit Button -->
        <div 
          class="bg-blue-600 text-white rounded-full p-1.5 shadow-lg border-2 border-white cursor-pointer hover:bg-blue-700 transition-colors"
          @click.stop="showCamera = true"
        >
          <q-icon name="edit" size="xs" />
        </div>
      </div>
    </div>

    <!-- Camera/Upload Dialog -->
    <q-dialog v-model="showCamera">
      <q-card style="min-width: 500px" class="rounded-2xl overflow-hidden">
        <q-card-section class="bg-blue-500 text-white p-4">
          <div class="text-h6 font-bold">📸 Update Photo</div>
        </q-card-section>
        
        <q-card-section class="p-0">
          <CameraCapture @captured="handleCapture" />
        </q-card-section>

        <q-card-actions align="right" class="bg-gray-100">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import CameraCapture from './CameraCapture.vue'

const props = defineProps({
  student: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: '80px'
  },
  editEnabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:avatar'])

const $q = useQuasar()
const hover = ref(false)
const showCamera = ref(false)
const localAvatar = ref(null)

const currentAvatar = computed(() => {
  return localAvatar.value || props.student.avatar
})

const sizeClasses = computed(() => {
  // Map size prop to tailwind classes if needed, or just rely on q-avatar size
  // This is a placeholder for potential custom sizing logic
  return '' 
})

const placeholderAvatar = computed(() => {
  const name = props.student.name || 'Student'
  const initials = name.split(' ').map(s => s[0] || '').slice(0, 2).join('').toUpperCase()
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96">
    <rect width="96" height="96" fill="#e2e8f0" rx="48" ry="48"/>
    <text x="48" y="48" dy="8" text-anchor="middle" font-family="Comic Sans MS, Arial" font-size="32" font-weight="bold" fill="#475569">${initials}</text>
  </svg>`
  return `data:image/svg+xml,${encodeURIComponent(svg)}`
})

function confirmRemoveAvatar() {
  $q.dialog({
    title: 'Remove Avatar',
    message: 'Are you sure you want to remove this photo?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    removeAvatar()
  })
}

async function removeAvatar() {
  try {
    await axios.delete(`/api/students/${props.student.id}/avatar`)
    
    localAvatar.value = null
    props.student.avatar = null // Optimistic update
    emit('update:avatar', null)
    
    $q.notify({
      message: 'Avatar removed',
      color: 'positive',
      position: 'top'
    })
  } catch (error) {
    console.error('Failed to remove avatar', error)
    $q.notify({
      message: 'Failed to remove avatar',
      color: 'negative',
      position: 'top'
    })
  }
}

async function handleCapture({ dataUrl }) {
  try {
    // Convert dataUrl to Blob
    const res = await fetch(dataUrl)
    const blob = await res.blob()
    
    // Upload
    const fd = new FormData()
    fd.append('avatar', blob, 'avatar.png')
    fd.append('student_id', props.student.id)
    
    const response = await axios.post(`/api/students/${props.student.id}/avatar`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    // Update local view
    localAvatar.value = response.data.avatar || response.data.url || dataUrl
    emit('update:avatar', localAvatar.value)
    
    showCamera.value = false
    $q.notify({
      message: 'Awesome new photo! 🌟',
      color: 'positive',
      icon: 'star',
      position: 'top'
    })
  } catch (error) {
    console.error('Avatar upload failed', error)
    $q.notify({
      message: 'Oops! Could not save photo.',
      color: 'negative',
      position: 'top'
    })
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.avatar-container {
  /* "Grade 4" Style: Thick borders, soft shadows, playful colors */
  border-width: 4px;
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}
</style>
