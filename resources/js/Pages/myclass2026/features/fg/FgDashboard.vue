<template>
  <!-- Render V2 if the user has chosen it -->
  <FgDashboardV2 v-if="uiStore.layout === 'v2'" />

  <!-- Render V3 if the user has chosen it -->
  <FgDashboardV3 v-else-if="uiStore.layout === 'v3'" />

  <!-- V1 (Original) Layout -->
  <div v-else class="q-pa-md column bg-white min-h-screen">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-xl q-pt-md px-lg">
      <div class="text-h4 text-weight-bolder text-dark tracking-tight">Focus Grid</div>
      <div class="row q-gutter-sm items-center">
        <FgLayoutSwitcher />
        <q-btn flat color="primary" label="Planning" icon="event_note" @click="uiStore.toggleMode('planning')" :disable="uiStore.isPlanningMode" />
        <q-btn flat color="secondary" label="Review" icon="history" @click="uiStore.toggleMode('review')" :disable="uiStore.isReviewMode" />
      </div>
    </div>

    <!-- Active Mode Routing -->
    <div v-if="uiStore.isPlanningMode" class="col">
      <FgPlanningView />
    </div>

    <div v-else-if="uiStore.isReviewMode" class="col">
      <FgReviewView />
    </div>

    <!-- Default Dashboard Flow -->
    <div v-else class="col row q-col-gutter-xl">
      <!-- Left: Venting Area -->
      <div class="col-12 col-md-5">
        <FgVentingArea />
      </div>
      
      <!-- Right: Now Focus Area -->
      <div class="col-12 col-md-7">
        <FgNowView />
      </div>
    </div>

    <!-- Global Floating Action & Modals -->
    <FgQuickCapture />
    <FgAiReviewModal />
  </div>

  <!-- Hidden Audio Engine for SFX -->
  <div v-show="false">
    <AudioPlayerInner ref="globalAudioPlayer" />
  </div>
</template>

<script setup>
import {ref, onMounted, onUnmounted } from 'vue'
import { useFgUiStore } from './stores/fg-ui.store'
import { useFgTasksStore } from './stores/fg-tasks.store'
import { useFgDomainsStore } from './stores/fg-domains.store'
import { useFgSync } from './composables/fg-use-sync'

import FgLayoutSwitcher from './components/FgLayoutSwitcher.vue'
import FgVentingArea from './components/FgVentingArea.vue'
import FgNowView from './components/FgNowView.vue'
import FgPlanningView from './FgPlanningView.vue'
import FgReviewView from './FgReviewView.vue'
import FgDashboardV2 from './FgDashboardV2.vue'
import FgDashboardV3 from './FgDashboardV3.vue'
import AudioPlayerInner from './audio_player/AudioPlayerInner.vue'
import { provide } from 'vue'

const uiStore = useFgUiStore()
const tasksStore = useFgTasksStore()
const domainsStore = useFgDomainsStore()
const { initSyncListeners } = useFgSync()

const globalAudioPlayer = ref(null)

const playClick = () => {
  if (globalAudioPlayer.value) {
    globalAudioPlayer.value.playSfx('/audio/click-234708.mp3', 0.2)
  }
}

const playTick = () => {
  if (globalAudioPlayer.value) {
    globalAudioPlayer.value.playSfx('/audio/timer/ticking-clock_1-27477.mp3', 0.05)
  }
}

provide('fgSoundEffects', { playClick, playTick })

const clickHandler = (e) => {
  const isBtn = e.target.closest('button, .q-btn, .q-tab, .clickable, .ap-track-row')
  if (isBtn) playClick()
}

let stopSync = null

onMounted(() => {
  // Only load data in V1 context; V2/V3 shell handles its own loading
  if (uiStore.layout === 'v1') {
    domainsStore.fetchDomains()
    tasksStore.fetchTasks()
  }

  stopSync = initSyncListeners()
})

onUnmounted(() => {
  if (stopSync) stopSync()
})
</script>

<style scoped>
.tracking-tight {
  letter-spacing: -0.025em;
}
</style>
