<template>
  <div class="fg-v2-shell column" style="min-height: 100svh; background: #f5f7fa">

    <!-- Top Header -->
    <div class="fg-v2-header row items-center justify-between q-px-lg q-py-sm bg-white shadow-1">
      <div class="text-h5 text-weight-bolder text-dark tracking-tight">Focus Grid</div>
      <FgLayoutSwitcher />
    </div>

    <!-- Tab Content Area (scrollable) -->
    <div class="col overflow-auto">
      <keep-alive>
        <component
          :is="activeView"
          @switch-tab="activeTab = $event"
        />
      </keep-alive>
    </div>

    <!-- Bottom Tab Bar -->
    <div class="fg-v2-bottom-bar bg-white shadow-up row items-stretch justify-around">
      <!-- Now Tab -->
      <q-btn
        flat
        no-caps
        class="col fg-v2-tab"
        :color="activeTab === 0 ? 'primary' : 'grey-6'"
        @click="activeTab = 0"
      >
        <div class="column items-center q-py-xs">
          <q-icon :name="activeTab === 0 ? 'my_location' : 'my_location'" size="sm" />
          <span class="fg-v2-tab-label text-caption q-mt-xs">Now</span>
        </div>
      </q-btn>

      <!-- Center Capture Button -->
      <div class="fg-v2-fab-wrap col-auto column items-center justify-start">
        <q-btn
          unelevated
          round
          color="primary"
          icon="add"
          size="lg"
          style="width: 58px; height: 58px; margin-top: -18px; box-shadow: 0 4px 16px rgba(0,0,0,0.2)"
          @click="uiStore.openQuickCapture()"
        />
        <span class="fg-v2-tab-label text-caption text-grey-6 q-mt-xs">Capture</span>
      </div>

      <!-- Plan Tab -->
      <q-btn
        flat
        no-caps
        class="col fg-v2-tab"
        :color="activeTab === 1 ? 'primary' : 'grey-6'"
        @click="activeTab = 1"
      >
        <div class="column items-center q-py-xs">
          <q-icon name="event_note" size="sm" />
          <div class="row items-center q-gutter-xs">
            <span class="fg-v2-tab-label text-caption q-mt-xs">Plan</span>
            <q-badge
              v-if="inboxCount > 0"
              color="orange"
              rounded
              style="font-size: 10px; margin-top: 4px"
            >{{ inboxCount }}</q-badge>
          </div>
        </div>
      </q-btn>

      <!-- Review Tab -->
      <q-btn
        flat
        no-caps
        class="col fg-v2-tab"
        :color="activeTab === 2 ? 'secondary' : 'grey-6'"
        @click="activeTab = 2"
      >
        <div class="column items-center q-py-xs">
          <q-icon name="history" size="sm" />
          <span class="fg-v2-tab-label text-caption q-mt-xs">Review</span>
        </div>
      </q-btn>
    </div>

    <!-- Dialogs -->
    <FgQuickCaptureV2 />
    <FgAiReviewModal />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useFgUiStore } from './stores/fg-ui.store'
import { useFgTasksStore } from './stores/fg-tasks.store'
import { useFgDomainsStore } from './stores/fg-domains.store'
import { useFgSync } from './composables/fg-use-sync'

import FgLayoutSwitcher from './components/FgLayoutSwitcher.vue'
import FgNowViewV2 from './components/FgNowViewV2.vue'
import FgPlanningViewV2 from './components/FgPlanningViewV2.vue'
import FgReviewView from './FgReviewView.vue'
import FgQuickCaptureV2 from './components/FgQuickCaptureV2.vue'
import FgAiReviewModal from './components/FgAiReviewModal.vue'
import { onMounted, onUnmounted } from 'vue'

const uiStore = useFgUiStore()
const tasksStore = useFgTasksStore()
const domainsStore = useFgDomainsStore()
const { initSyncListeners } = useFgSync()

let stopSync = null

onMounted(() => {
  domainsStore.fetchDomains()
  tasksStore.fetchTasks()
  stopSync = initSyncListeners()
})

onUnmounted(() => {
  if (stopSync) stopSync()
})

// Tab navigation
const activeTab = computed({
  get: () => uiStore.activeTab,
  set: (val) => uiStore.setActiveTab(val)
})

const views = [FgNowViewV2, FgPlanningViewV2, FgReviewView]
const activeView = computed(() => views[activeTab.value])

// Inbox count badge on Plan tab
const inboxCount = computed(() => tasksStore.inboxTasks.length)
</script>

<style scoped>
.fg-v2-header {
  position: sticky;
  top: 0;
  z-index: 100;
  border-bottom: 1px solid #eee;
}
.fg-v2-bottom-bar {
  position: sticky;
  bottom: 0;
  z-index: 100;
  border-top: 1px solid #eee;
  min-height: 62px;
  padding-bottom: env(safe-area-inset-bottom, 0px);
}
.fg-v2-tab {
  min-height: 62px;
}
.fg-v2-tab-label {
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.3px;
}
.tracking-tight {
  letter-spacing: -0.025em;
}
</style>
