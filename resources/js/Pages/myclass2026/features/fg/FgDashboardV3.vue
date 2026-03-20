<template>
  <div class="fg-v3-shell" :style="bgStyle">

    <!-- Subtle animated gradient orbs -->
    <div class="fg-v3-orb fg-v3-orb--1" />
    <div class="fg-v3-orb fg-v3-orb--2" />

    <!-- Top Header -->
    <div class="fg-v3-header row items-center justify-between q-px-lg q-py-sm">
      <div class="fg-v3-logo row items-center q-gutter-xs">
        <q-icon name="my_location" color="cyan-3" size="sm" />
        <span class="text-h6 text-weight-bolder text-white tracking-tight">Focus Grid</span>
      </div>
      <FgLayoutSwitcher />
    </div>

    <!-- Tab Content Area -->
    <div class="col overflow-auto fg-v3-content">
      <transition :name="transitionName" mode="out-in">
        <component
          :is="activeView"
          :key="activeTab"
          class="fg-v3-view"
          @switch-tab="switchTab"
        />
      </transition>
    </div>

    <!-- Glassmorphism Bottom Bar -->
    <div class="fg-v3-nav-wrap">
      <div class="fg-v3-nav row items-center justify-around">

        <!-- Animated indicator pill -->
        <div class="fg-v3-pill" :style="pillStyle" />

        <!-- Now Tab -->
        <button class="fg-v3-tab" @click="switchTab(0)" :class="{ 'fg-v3-tab--active': activeTab === 0 }">
          <div class="fg-v3-tab-icon-wrap">
            <q-icon name="my_location" :size="activeTab === 0 ? '28px' : '22px'" />
          </div>
          <span class="fg-v3-tab-label">Now</span>
        </button>

        <!-- Center Capture FAB -->
        <div class="fg-v3-fab-wrap" @click="uiStore.openQuickCapture()">
          <div class="fg-v3-fab-ring" />
          <button class="fg-v3-fab">
            <q-icon name="add" size="28px" color="white" />
          </button>
        </div>

        <!-- Plan Tab -->
        <button class="fg-v3-tab" @click="switchTab(1)" :class="{ 'fg-v3-tab--active': activeTab === 1 }">
          <div class="fg-v3-tab-icon-wrap">
            <q-icon name="event_note" :size="activeTab === 1 ? '28px' : '22px'" />
            <div v-if="inboxCount > 0" class="fg-v3-badge">{{ inboxCount }}</div>
          </div>
          <span class="fg-v3-tab-label">Plan</span>
        </button>

        <!-- Review Tab (far right) -->
        <button class="fg-v3-tab" @click="switchTab(2)" :class="{ 'fg-v3-tab--active': activeTab === 2 }">
          <div class="fg-v3-tab-icon-wrap">
            <q-icon name="history" :size="activeTab === 2 ? '28px' : '22px'" />
          </div>
          <span class="fg-v3-tab-label">Review</span>
        </button>

      </div>
    </div>

    <!-- Dialogs -->
    <FgQuickCaptureV2 />
    <FgAiReviewModal />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
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

onUnmounted(() => { if (stopSync) stopSync() })

const activeTab = computed({
  get: () => uiStore.activeTab,
  set: (val) => uiStore.setActiveTab(val)
})
const transitionName = ref('fg-v3-slide-left')

const views = [FgNowViewV2, FgPlanningViewV2, FgReviewView]
const activeView = computed(() => views[activeTab.value])

const switchTab = (idx) => {
  transitionName.value = idx > activeTab.value ? 'fg-v3-slide-left' : 'fg-v3-slide-right'
  activeTab.value = idx
}

const inboxCount = computed(() => tasksStore.inboxTasks.length)

// Dynamic pill position for the 4-slot nav (0=Now, skip fab, 1=Plan, 2=Review)
// Nav slots: [Now(0)] [FAB] [Plan(1)] [Review(2)]
// Map tab index to x-offset percent
const pillPositions = ['6%', '63%', '81%']
const pillStyle = computed(() => ({
  transform: `translateX(${pillPositions[activeTab.value]})`,
}))

// Background gradient cycles subtly
const bgStyle = {
  background: 'linear-gradient(145deg, #0f0c29, #1a1a2e 40%, #16213e 100%)',
}
</script>

<style scoped>
/* === Shell === */
.fg-v3-shell {
  min-height: 100svh;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
  color: #e8eaf6;
}

/* === Animated Background Orbs === */
.fg-v3-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.18;
  pointer-events: none;
  animation: fg-float 8s ease-in-out infinite alternate;
}
.fg-v3-orb--1 {
  width: 340px; height: 340px;
  background: radial-gradient(circle, #00e5ff, transparent 70%);
  top: -80px; left: -80px;
  animation-delay: 0s;
}
.fg-v3-orb--2 {
  width: 280px; height: 280px;
  background: radial-gradient(circle, #7c4dff, transparent 70%);
  bottom: 100px; right: -60px;
  animation-delay: -4s;
}
@keyframes fg-float {
  from { transform: translateY(0) scale(1); }
  to   { transform: translateY(30px) scale(1.1); }
}

/* === Header === */
.fg-v3-header {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(15, 12, 41, 0.6);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(255,255,255,0.07);
}
.tracking-tight { letter-spacing: -0.025em; }

/* === Content === */
.fg-v3-content {
  flex: 1;
}
.fg-v3-view {
  /* override child bg so dark theme shows */
}
:deep(.fg-v3-view .q-card),
:deep(.fg-v3-view .q-card--bordered) {
  background: rgba(255,255,255,0.05) !important;
  border-color: rgba(255,255,255,0.1) !important;
  color: #e8eaf6 !important;
}
:deep(.fg-v3-view .text-dark) { color: #e8eaf6 !important; }
:deep(.fg-v3-view .text-grey-7),
:deep(.fg-v3-view .text-grey-6),
:deep(.fg-v3-view .text-grey-5) { color: #9e9ec0 !important; }
:deep(.fg-v3-view .bg-grey-1) { background: rgba(255,255,255,0.04) !important; }

/* === Bottom Nav Wrapper === */
.fg-v3-nav-wrap {
  position: sticky;
  bottom: 0;
  z-index: 100;
  padding: 0 16px 16px;
  padding-bottom: max(16px, env(safe-area-inset-bottom));
}
.fg-v3-nav {
  position: relative;
  background: rgba(20, 18, 50, 0.75);
  backdrop-filter: blur(24px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 28px;
  padding: 10px 8px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.05) inset;
  overflow: hidden;
}

/* === Animated Pill Indicator === */
.fg-v3-pill {
  position: absolute;
  left: 0;
  top: 8px;
  width: 70px;
  height: calc(100% - 16px);
  background: linear-gradient(135deg, rgba(0,229,255,0.2), rgba(124,77,255,0.2));
  border: 1px solid rgba(0,229,255,0.3);
  border-radius: 20px;
  transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow: 0 0 16px rgba(0,229,255,0.15);
  pointer-events: none;
}

/* === Tab Buttons === */
.fg-v3-tab {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 70px;
  padding: 6px 4px;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: color 0.25s ease;
  color: rgba(200,200,220,0.5);
  z-index: 1;
}
.fg-v3-tab--active {
  color: #00e5ff;
  text-shadow: 0 0 12px rgba(0,229,255,0.6);
}
.fg-v3-tab-icon-wrap {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 28px;
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.fg-v3-tab--active .fg-v3-tab-icon-wrap {
  transform: translateY(-2px) scale(1.1);
  filter: drop-shadow(0 0 6px cyan);
}
.fg-v3-tab-label {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.5px;
  margin-top: 3px;
  text-transform: uppercase;
}

/* === Badge === */
.fg-v3-badge {
  position: absolute;
  top: -4px; right: -8px;
  background: #ff6b35;
  color: white;
  border-radius: 10px;
  font-size: 9px;
  font-weight: 700;
  padding: 1px 5px;
  min-width: 16px;
  text-align: center;
  box-shadow: 0 0 8px rgba(255,107,53,0.6);
}

/* === FAB === */
.fg-v3-fab-wrap {
  position: relative;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 70px;
}
.fg-v3-fab {
  width: 52px; height: 52px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #00e5ff, #7c4dff);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 20px rgba(0,229,255,0.4), 0 0 0 0 rgba(0,229,255,0.4);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  animation: fg-pulse 2.5s ease-in-out infinite;
  margin-top: -10px;
  position: relative;
  z-index: 1;
}
.fg-v3-fab:active {
  transform: scale(0.93);
}
.fg-v3-fab-ring {
  position: absolute;
  width: 60px; height: 60px;
  border-radius: 50%;
  border: 1px solid rgba(0,229,255,0.35);
  top: -14px; left: 5px;
  animation: fg-ring-pulse 2.5s ease-in-out infinite;
}
@keyframes fg-pulse {
  0%, 100% { box-shadow: 0 4px 20px rgba(0,229,255,0.4), 0 0 0 0 rgba(0,229,255,0.3); }
  50%       { box-shadow: 0 4px 20px rgba(0,229,255,0.6), 0 0 0 10px rgba(0,229,255,0); }
}
@keyframes fg-ring-pulse {
  0%, 100% { transform: scale(1); opacity: 0.35; }
  50%       { transform: scale(1.2); opacity: 0; }
}

/* === View Transitions === */
.fg-v3-slide-left-enter-active,
.fg-v3-slide-left-leave-active,
.fg-v3-slide-right-enter-active,
.fg-v3-slide-right-leave-active {
  transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
}
.fg-v3-slide-left-enter-from  { transform: translateX(30px); opacity: 0; }
.fg-v3-slide-left-leave-to    { transform: translateX(-30px); opacity: 0; }
.fg-v3-slide-right-enter-from { transform: translateX(-30px); opacity: 0; }
.fg-v3-slide-right-leave-to   { transform: translateX(30px); opacity: 0; }
</style>
