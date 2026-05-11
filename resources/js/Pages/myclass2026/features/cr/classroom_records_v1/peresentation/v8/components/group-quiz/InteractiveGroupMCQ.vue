<script setup>
import { ref, computed } from 'vue'
import { useGameStore } from '../../stores/gameStore.js'
import { useMathRenderer } from '../../composables/useMathRenderer.js'
import QrScanInput from './QrScanInput.vue'

const props = defineProps({
  element: { type: Object, required: true },
  isPresentMode: { type: Boolean, default: false }
})

const gameStore = useGameStore()
const { renderMath } = useMathRenderer()

// ── State ──────────────────────────────────────────────
const isLocked = ref(true)
const activeGroupId = ref(null)
const isPracticeMode = ref(false)
const showScanner = ref(false)

// ── Sound Effects (simple Web Audio) ───────────────────
function playSound(type) {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)()
    const osc = ctx.createOscillator()
    const gain = ctx.createGain()
    osc.connect(gain)
    gain.connect(ctx.destination)

    if (type === 'correct') {
      osc.frequency.setValueAtTime(523, ctx.currentTime)
      osc.frequency.setValueAtTime(659, ctx.currentTime + 0.1)
      gain.gain.setValueAtTime(0.15, ctx.currentTime)
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4)
      osc.start(ctx.currentTime)
      osc.stop(ctx.currentTime + 0.4)
    } else if (type === 'incorrect') {
      osc.frequency.setValueAtTime(300, ctx.currentTime)
      osc.frequency.setValueAtTime(250, ctx.currentTime + 0.15)
      gain.gain.setValueAtTime(0.15, ctx.currentTime)
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4)
      osc.start(ctx.currentTime)
      osc.stop(ctx.currentTime + 0.4)
    } else {
      // hover / click
      osc.frequency.setValueAtTime(880, ctx.currentTime)
      gain.gain.setValueAtTime(0.06, ctx.currentTime)
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1)
      osc.start(ctx.currentTime)
      osc.stop(ctx.currentTime + 0.1)
    }
  } catch {
    // ignore audio errors
  }
}

// ── Computed ───────────────────────────────────────────
const qData = computed(() => props.element.questionData || {})
const options = computed(() => qData.value.options || [])
const correctId = computed(() => qData.value.correctId)

const elementId = computed(() => props.element.id)
const isGraded = computed(() => gameStore.isGraded(elementId.value))
const qHistory = computed(() => gameStore.questionHistory[elementId.value])

// Which groups have answered for this element
const groupAnswers = computed(() => qHistory.value?.groupAnswers || {})

const canGrade = computed(() => {
  if (!isPresentMode.value || isLocked.value || isGraded.value) return false
  const answeredCount = Object.keys(groupAnswers.value).length
  return answeredCount > 0
})

const isInteractive = computed(() =>
  props.isPresentMode && !isLocked.value && !isGraded.value
)

// ── Actions ────────────────────────────────────────────
function toggleLock() {
  if (isGraded.value) return
  isLocked.value = !isLocked.value
  if (!isLocked.value) {
    activeGroupId.value = null
    playSound('hover')
  }
}

function selectGroup(gId) {
  if (!isInteractive.value) return
  activeGroupId.value = activeGroupId.value === gId ? null : gId
  playSound('hover')
}

function handleOptionClick(optId) {
  if (!isInteractive.value) return

  if (isPracticeMode.value) {
    gameStore.logGroupAnswer(elementId.value, 'practice-' + Date.now(), optId)
    playSound('hover')
    return
  }

  if (!activeGroupId.value) return
  gameStore.logGroupAnswer(elementId.value, activeGroupId.value, optId)
  playSound('hover')
  activeGroupId.value = null
}

function gradeGroups() {
  if (!canGrade.value) return

  let anyRight = false
  let anyWrong = false
  const answers = groupAnswers.value
  const correct = correctId.value

  Object.entries(answers).forEach(([gId, optId]) => {
    if (gId.startsWith('practice-')) return
    if (optId === correct) {
      gameStore.updateGroupScore(gId, gameStore.gameSettings.correctPoints)
      anyRight = true
    } else if (gameStore.gameSettings.allowNegativeScore) {
      gameStore.updateGroupScore(gId, gameStore.gameSettings.wrongPoints)
      anyWrong = true
    } else {
      anyWrong = true
    }
  })

  gameStore.markGraded(elementId.value)
  playSound(anyRight ? 'correct' : 'incorrect')
}

function undoGrade() {
  if (!isGraded.value) return

  // Revert scores
  const answers = groupAnswers.value
  const correct = correctId.value

  Object.entries(answers).forEach(([gId, optId]) => {
    if (gId.startsWith('practice-')) return
    if (optId === correct) {
      gameStore.updateGroupScore(gId, -gameStore.gameSettings.correctPoints)
    } else if (gameStore.gameSettings.allowNegativeScore) {
      gameStore.updateGroupScore(gId, -gameStore.gameSettings.wrongPoints)
    }
  })

  gameStore.markUngraded(elementId.value)
}

function resetAnswers() {
  gameStore.clearElementHistory(elementId.value)
  isLocked.value = true
  activeGroupId.value = null
}

function onScanned(decoded) {
  if (!isInteractive.value) return
  if (decoded.optionId) {
    // Full payload: assign answer
    gameStore.logGroupAnswer(elementId.value, decoded.groupId, decoded.optionId)
    playSound('hover')
  } else {
    // Group only: select that group
    selectGroup(decoded.groupId)
  }
}

function onSelectGroup(gId) {
  selectGroup(gId)
}

function getOptionBadgeClass(optId) {
  if (isGraded.value) {
    if (optId === correctId.value) return 'badge-correct'
    return 'badge-wrong'
  }
  return 'badge-pending'
}
</script>

<template>
  <div class="igmcq-container" :class="{ 'is-graded': isGraded, 'is-locked': isLocked }">
    <!-- GROUP SIDEBAR -->
    <div class="igmcq-sidebar">
      <div class="igmcq-sidebar-header">
        <span class="igmcq-sidebar-title">Groups</span>
        <button
          v-if="isPresentMode"
          class="igmcq-scan-toggle"
          :class="{ active: showScanner }"
          @click="showScanner = !showScanner"
          title="Toggle QR Scanner"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><rect x="7" y="7" width="10" height="10" rx="1"/>
          </svg>
        </button>
      </div>

      <!-- Scanner (collapsible) -->
      <div v-if="showScanner && isPresentMode" class="igmcq-scanner-box">
        <QrScanInput
          :disabled="!isInteractive"
          @scanned="onScanned"
          @select-group="onSelectGroup"
        />
      </div>

      <!-- Group List -->
      <div class="igmcq-groups">
        <button
          v-for="group in gameStore.groups"
          :key="group.id"
          class="igmcq-group-btn"
          :class="{
            active: activeGroupId === group.id,
            answered: groupAnswers[group.id] && !isGraded,
            graded: isGraded
          }"
          :style="{ '--group-color': group.color }"
          @click="selectGroup(group.id)"
        >
          <div class="igmcq-group-color" :style="{ backgroundColor: group.color }" />
          <span class="igmcq-group-name">{{ group.name }}</span>
          <span class="igmcq-group-score">{{ group.score }}</span>
          <span v-if="groupAnswers[group.id] && !isGraded" class="igmcq-group-ans">
            {{ groupAnswers[group.id] }}
          </span>
        </button>
      </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="igmcq-main">
      <!-- Question -->
      <div class="igmcq-question" v-html="renderMath(qData.question) || 'Question text...'" />

      <!-- Options Grid -->
      <div class="igmcq-options">
        <button
          v-for="opt in options"
          :key="opt.id"
          class="igmcq-option"
          :class="{
            'opt-correct': isGraded && opt.id === correctId,
            'opt-wrong': isGraded && opt.id !== correctId,
            'opt-answered': !isGraded && Object.values(groupAnswers).includes(opt.id),
            'opt-disabled': !isInteractive
          }"
          @click="handleOptionClick(opt.id)"
        >
          <span class="igmcq-opt-label">{{ opt.id }}</span>
          <span class="igmcq-opt-text" v-html="renderMath(opt.text)" />

          <!-- Answer badges -->
          <div v-if="!isGraded" class="igmcq-opt-badges">
            <span
              v-for="[gId, ans] in Object.entries(groupAnswers).filter(([,a]) => a === opt.id)"
              :key="gId"
              class="igmcq-opt-badge"
              :class="getOptionBadgeClass(opt.id)"
              :style="{ backgroundColor: gameStore.groups.find(g => g.id === gId)?.color }"
            >
              {{ gameStore.groups.find(g => g.id === gId)?.name?.charAt(0) || '?' }}
            </span>
          </div>

          <!-- Correct indicator -->
          <span v-if="isGraded && opt.id === correctId" class="igmcq-correct-mark">✓</span>
        </button>
      </div>

      <!-- Teacher Controls (present mode only) -->
      <div v-if="isPresentMode" class="igmcq-controls">
        <!-- Lock/Unlock -->
        <button
          class="igmcq-control-btn"
          :class="isLocked ? 'btn-locked' : 'btn-unlocked'"
          :disabled="isGraded"
          @click="toggleLock"
        >
          <svg v-if="isLocked" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>
          </svg>
          {{ isLocked ? 'Unlock' : 'Lock' }}
        </button>

        <!-- Practice Mode Toggle -->
        <button
          v-if="!isGraded"
          class="igmcq-control-btn"
          :class="{ active: isPracticeMode }"
          @click="isPracticeMode = !isPracticeMode"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="5 3 19 12 5 21 5 3"/>
          </svg>
          {{ isPracticeMode ? 'Practice On' : 'Practice' }}
        </button>

        <!-- Grade Button -->
        <button
          v-if="!isGraded"
          class="igmcq-control-btn btn-grade"
          :disabled="!canGrade"
          @click="gradeGroups"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          Grade
        </button>

        <!-- Undo Grade -->
        <button
          v-if="isGraded"
          class="igmcq-control-btn"
          @click="undoGrade"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
          </svg>
          Undo
        </button>

        <!-- Reset -->
        <button
          class="igmcq-control-btn btn-danger"
          @click="resetAnswers"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
          </svg>
          Reset
        </button>
      </div>

      <!-- Status Bar -->
      <div class="igmcq-status">
        <span v-if="isGraded" class="status-graded">✓ Graded</span>
        <span v-else-if="isLocked" class="status-locked">🔒 Locked</span>
        <span v-else-if="activeGroupId" class="status-active">
          Assigning to: {{ gameStore.groups.find(g => g.id === activeGroupId)?.name }}
        </span>
        <span v-else class="status-waiting">Select a group, then click an option</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.igmcq-container {
  display: flex;
  width: 100%;
  height: 100%;
  background: #ffffff;
  border-radius: 8px;
  overflow: hidden;
  font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
  color: #111827;
}

/* SIDEBAR */
.igmcq-sidebar {
  width: 160px;
  min-width: 160px;
  background: #f9fafb;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
}

.igmcq-sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  border-bottom: 1px solid #e5e7eb;
}

.igmcq-sidebar-title {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6b7280;
}

.igmcq-scan-toggle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
}

.igmcq-scan-toggle.active {
  background: #6366f1;
  color: white;
}

.igmcq-scan-toggle:hover:not(.active) {
  background: #e5e7eb;
}

.igmcq-scanner-box {
  padding: 8px;
  border-bottom: 1px solid #e5e7eb;
}

.igmcq-groups {
  flex: 1;
  overflow-y: auto;
  padding: 4px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.igmcq-group-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border: none;
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
  transition: background 0.1s;
  text-align: left;
  font-size: 13px;
  color: #374151;
  position: relative;
}

.igmcq-group-btn:hover {
  background: #f3f4f6;
}

.igmcq-group-btn.active {
  background: #eff6ff;
  box-shadow: inset 3px 0 0 var(--group-color, #6366f1);
}

.igmcq-group-btn.answered {
  background: #ecfdf5;
}

.igmcq-group-color {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.igmcq-group-name {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.igmcq-group-score {
  font-weight: 700;
  color: #111827;
  font-variant-numeric: tabular-nums;
}

.igmcq-group-ans {
  font-size: 11px;
  font-weight: 700;
  color: #059669;
  background: #d1fae5;
  padding: 2px 6px;
  border-radius: 4px;
  margin-left: 2px;
}

/* MAIN */
.igmcq-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 20px;
  gap: 16px;
  overflow-y: auto;
}

.igmcq-question {
  font-size: 20px;
  font-weight: 600;
  line-height: 1.4;
  color: #111827;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
  border-left: 4px solid #6366f1;
}

/* OPTIONS */
.igmcq-options {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.igmcq-option {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 8px;
  padding: 16px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: #ffffff;
  cursor: pointer;
  transition: all 0.15s;
  text-align: left;
  position: relative;
  min-height: 80px;
}

.igmcq-option:hover:not(.opt-disabled) {
  border-color: #6366f1;
  background: #f5f3ff;
  transform: translateY(-1px);
}

.igmcq-option.opt-disabled {
  cursor: default;
  opacity: 0.7;
}

.igmcq-option.opt-correct {
  border-color: #10b981;
  background: #ecfdf5;
}

.igmcq-option.opt-wrong {
  border-color: #e5e7eb;
  opacity: 0.6;
}

.igmcq-option.opt-answered {
  border-color: #6366f1;
}

.igmcq-opt-label {
  font-size: 18px;
  font-weight: 700;
  color: #6366f1;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f3ff;
  border-radius: 50%;
}

.igmcq-opt-text {
  font-size: 15px;
  color: #374151;
  line-height: 1.4;
}

.igmcq-opt-badges {
  display: flex;
  gap: 4px;
  margin-top: auto;
}

.igmcq-opt-badge {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
  color: white;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.igmcq-correct-mark {
  position: absolute;
  top: 8px;
  right: 12px;
  font-size: 20px;
  color: #10b981;
  font-weight: 700;
}

/* CONTROLS */
.igmcq-controls {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  padding-top: 8px;
  border-top: 1px solid #e5e7eb;
}

.igmcq-control-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
  background: #f9fafb;
  color: #374151;
  transition: all 0.15s;
}

.igmcq-control-btn:hover:not(:disabled) {
  background: #f3f4f6;
}

.igmcq-control-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.igmcq-control-btn.active {
  background: #6366f1;
  color: white;
  border-color: #6366f1;
}

.btn-locked { background: #fef3c7; color: #92400e; }
.btn-unlocked { background: #d1fae5; color: #065f46; }
.btn-grade { background: #6366f1; color: white; border-color: #6366f1; }
.btn-grade:hover:not(:disabled) { background: #4f46e5; }
.btn-danger { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
.btn-danger:hover { background: #fee2e2; }

/* STATUS */
.igmcq-status {
  font-size: 13px;
  font-weight: 500;
  padding: 8px 12px;
  border-radius: 6px;
  text-align: center;
}

.status-graded { color: #047857; background: #ecfdf5; }
.status-locked { color: #92400e; background: #fef3c7; }
.status-active { color: #1e40af; background: #dbeafe; }
.status-waiting { color: #6b7280; background: #f3f4f6; }

/* Mobile */
@media (max-width: 640px) {
  .igmcq-container {
    flex-direction: column;
  }
  .igmcq-sidebar {
    width: 100%;
    min-width: auto;
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
    max-height: 160px;
  }
  .igmcq-options {
    grid-template-columns: 1fr;
  }
}

/* Math rendering styles */
.igmcq-question :deep(sup),
.igmcq-opt-text :deep(sup) {
  font-size: 0.7em;
  vertical-align: super;
  line-height: 0;
}

.igmcq-question :deep(sub),
.igmcq-opt-text :deep(sub) {
  font-size: 0.7em;
  vertical-align: sub;
  line-height: 0;
}

.igmcq-question :deep(.math-inline),
.igmcq-opt-text :deep(.math-inline) {
  display: inline;
}
</style>
