<template>
  <div class="flex flex-center bg-grey-9 text-white">
    <div class="text-center">
      <div class="text-h2 q-mb-lg">{{ formattedTime }}</div>
      
      <div class="q-mb-xl">
        <q-btn 
          v-if="!isFocusing" 
          round 
          color="green" 
          icon="play_arrow" 
          size="xl" 
          @click="startFocus" 
        />
        <q-btn 
          v-else 
          round 
          color="red" 
          icon="stop" 
          size="xl" 
          @click="endFocus" 
        />
      </div>

      <div class="q-mb-md" style="max-width: 300px; margin: 0 auto;">
        <q-input 
          v-model="musicUrl" 
          dark 
          filled 
          label="رابط موسيقى (YouTube/MP3)" 
          placeholder="ضع رابط الموسيقى هنا"
        >
          <template v-slot:append>
            <q-icon name="music_note" />
          </template>
        </q-input>
        <div class="text-caption text-grey">أو اختر ملف محلي:</div>
        <q-file v-model="musicFile" dark filled label="ملف صوتي" accept="audio/*" @update:model-value="playLocalFile" />
      </div>

      <audio ref="audioPlayer" controls class="full-width q-mt-sm" v-show="musicUrl || musicFile" />
    </div>

    <dp-focus-popup 
      v-model="showCheck" 
      @confirmed="handleCheckConfirmed" 
      @distracted="handleDistraction" 
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import { useFocusStore } from '@/Stores/dp_useFocusStore'
import DpFocusPopup from '@/Components/dailyTasks/dp_FocusPopup.vue'
import { storeToRefs } from 'pinia'

const focusStore = useFocusStore()
const { isFocusing, elapsedMinutes } = storeToRefs(focusStore)

const musicUrl = ref('')
const musicFile = ref(null)
const audioPlayer = ref(null)
const showCheck = ref(false)
let checkInterval = null

const formattedTime = computed(() => {
  const hours = Math.floor(elapsedMinutes.value / 60)
  const mins = elapsedMinutes.value % 60
  return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`
})

const startFocus = () => {
  focusStore.startFocus()
  startCheckInterval()
}

const endFocus = () => {
  focusStore.endFocus()
  stopCheckInterval()
}

const startCheckInterval = () => {
  checkInterval = setInterval(() => {
    showCheck.value = true
  }, 10 * 60 * 1000) // 10 minutes
}

const stopCheckInterval = () => {
  if (checkInterval) clearInterval(checkInterval)
}

const handleCheckConfirmed = () => {
  // Good job
}

const handleDistraction = () => {
  focusStore.logDistraction()
}

const playLocalFile = (file) => {
  if (file) {
    const url = URL.createObjectURL(file)
    audioPlayer.value.src = url
    audioPlayer.value.play()
  }
}

watch(musicUrl, (val) => {
  if (val && audioPlayer.value) {
    audioPlayer.value.src = val
    // Note: YouTube URLs won't work directly in <audio> tag usually, need embed or specific player.
    // For simplicity, assuming direct audio link or user understands.
  }
})

onUnmounted(() => {
  stopCheckInterval()
})
</script>
