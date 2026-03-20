<template>
  <q-page padding class="column bg-white">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-xl q-pt-md px-lg">
      <div class="text-h4 text-weight-bolder text-dark tracking-tight">Focus Grid</div>
      <div class="row q-gutter-sm">
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

  </q-page>
</template>

<script setup>
import { onMounted } from 'vue'
import { useFgUiStore } from './stores/fg-ui.store'
import { useFgTasksStore } from './stores/fg-tasks.store'
import { useFgDomainsStore } from './stores/fg-domains.store'

import FgVentingArea from './components/FgVentingArea.vue'
import FgNowView from './components/FgNowView.vue'
import FgPlanningView from './FgPlanningView.vue'
import FgReviewView from './FgReviewView.vue'
import FgQuickCapture from './components/FgQuickCapture.vue'
import FgAiReviewModal from './components/FgAiReviewModal.vue'

const uiStore = useFgUiStore()
const tasksStore = useFgTasksStore()
const domainsStore = useFgDomainsStore()

onMounted(() => {
  // Initial load
  domainsStore.fetchDomains()
  tasksStore.fetchTasks()
})
</script>

<style scoped>
.tracking-tight {
  letter-spacing: -0.025em;
}
</style>
