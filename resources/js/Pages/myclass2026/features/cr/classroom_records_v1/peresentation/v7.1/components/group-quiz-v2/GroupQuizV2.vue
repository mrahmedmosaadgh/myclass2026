<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useGameStore } from '../../stores/gameStore';
import { useQuizSound } from './composables/useQuizSound';
import { useQuizGrading } from './composables/useQuizGrading';
import { useQrCodec } from './composables/useQrCodec';

import QuestionHeader from './components/QuestionHeader.vue';
import OptionGrid from './components/OptionGrid.vue';
import GroupSelector from './components/GroupSelector.vue';
import QuizControlBar from './components/QuizControlBar.vue';
import GradingResultBanner from './components/GradingResultBanner.vue';
import QrScannerPanel from './components/QrScannerPanel.vue';
import QrPrintSheet from './components/QrPrintSheet.vue';
import QrScanDialog from '@/Components/Common/QrScanDialog.vue';

const props = defineProps({
  element: { type: Object, required: true },
  isEditMode: { type: Boolean, default: false }
});

const emit = defineEmits(['update', 'select']);

const gameStore = useGameStore();
const { isMuted, toggleMute, playSound } = useQuizSound();
const { qHistory, isGraded, gradeGroups } = useQuizGrading(props);
const { parseQrPayload, validateAnswerPayload, isTypingTarget } = useQrCodec();

const isInteractive = ref(false);
const isPracticeMode = ref(false);
const activeGroupId = ref(null);
const isQrDialogOpen = ref(false);
const isPrintQrOpen = ref(false);

const scannerState = ref('idle');
const scannerLastRaw = ref('');
const scannerLastResult = ref('');
let scannerBuffer = '';
let scannerTimer = null;

// --- Interaction Handlers ---
function handleOptionClick(optId) {
  if (props.isEditMode || !isInteractive.value || isGraded.value) return;

  if (isPracticeMode.value) {
    gameStore.logGroupAnswer(props.element.id, 'practice-mode', optId);
    playSound('hover');
    return;
  }
  if (!activeGroupId.value) return;

  gameStore.logGroupAnswer(props.element.id, activeGroupId.value, optId);
  playSound('hover');
  activeGroupId.value = null;
}

function handleOptionHover() {
  if (!props.isEditMode && isInteractive.value && activeGroupId.value && !isGraded.value) {
    playSound('hover');
  }
}

function selectGroup(groupId) {
  if (isGraded.value) return;
  activeGroupId.value = activeGroupId.value === groupId ? null : groupId;
}

function clearGroupAnswer(groupId) {
  if (isGraded.value) return;
  gameStore.clearGroupAnswer(props.element.id, groupId);
}

// --- Control Bar Handlers ---
function startQuiz() { isInteractive.value = true; isPracticeMode.value = false; }
function lockQuiz() { isInteractive.value = false; isPracticeMode.value = false; activeGroupId.value = null; }
function startPractice() { isInteractive.value = true; isPracticeMode.value = true; activeGroupId.value = null; }
function handleGrade() { gradeGroups(playSound); }

function replayQuiz() {
  gameStore.questionHistory[props.element.id] = { groupAnswers: {}, status: 'locked_in' };
  isInteractive.value = false;
  isPracticeMode.value = false;
  activeGroupId.value = null;
}

function shuffleOptions() {
  const newOptions = [...props.element.questionData.options];
  for (let i = newOptions.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [newOptions[i], newOptions[j]] = [newOptions[j], newOptions[i]];
  }
  emit('update', { questionData: { ...props.element.questionData, options: newOptions } });
}

// --- QR ---
function openQrScanner() {
  if (isGraded.value) return;
  isQrDialogOpen.value = true;
}

function handleScannedQr(rawText) {
  if (isGraded.value) return;
  const raw = String(rawText || '').trim();
  if (!raw) return;

  scannerLastRaw.value = raw;
  scannerState.value = 'received';
  scannerLastResult.value = '';

  const parsed = parseQrPayload(raw);

  if (parsed.type === 'answer') {
    const validation = validateAnswerPayload(parsed, props.element);
    if (!validation.ok) {
      scannerLastResult.value = validation.message;
      return;
    }
    if (!isInteractive.value) {
      scannerLastResult.value = 'Locked (unlock interaction first)';
      return;
    }
    gameStore.logGroupAnswer(props.element.id, validation.group.id, validation.choiceId);
    playSound('hover');
    activeGroupId.value = null;
    scannerLastResult.value = `${validation.group.name} -> ${validation.choiceId}`;
    isQrDialogOpen.value = false;
  } else if (parsed.type === 'select-group') {
    playSound('hover');
    activeGroupId.value = parsed.group.id;
    scannerLastResult.value = `Selected: ${parsed.group.name}`;
    isQrDialogOpen.value = false;
  } else {
    scannerLastResult.value = 'Unrecognized payload';
  }
}

// --- Scanner Keydown ---
function handleGlobalScannerKeydown(e) {
  if (props.isEditMode) return;
  if (!isInteractive.value) { scannerState.value = 'idle'; return; }
  if (isGraded.value) return;
  if (isPrintQrOpen.value) return;
  if (isTypingTarget(document.activeElement)) return;

  if (e.key === 'Enter' || e.key === 'Tab') {
    if (e.key === 'Tab') e.preventDefault();
    const code = String(scannerBuffer || '').trim();
    scannerBuffer = '';
    if (scannerTimer) { clearTimeout(scannerTimer); scannerTimer = null; }
    if (code) handleScannedQr(code);
    return;
  }

  if (e.key && e.key.length === 1) {
    if (scannerState.value !== 'waiting') scannerState.value = 'waiting';
    scannerBuffer += e.key;
    if (scannerTimer) clearTimeout(scannerTimer);
    scannerTimer = setTimeout(() => {
      scannerBuffer = '';
      scannerTimer = null;
      scannerState.value = (isInteractive.value && !props.isEditMode && !isGraded.value) ? 'waiting' : 'idle';
    }, 800);
  }
}

// --- Edit Handlers ---
function updateQuestion(newQ) {
  emit('update', { questionData: { ...props.element.questionData, question: newQ } });
}
function updateOption({ index, text }) {
  const newOptions = [...props.element.questionData.options];
  newOptions[index].text = text;
  emit('update', { questionData: { ...props.element.questionData, options: newOptions } });
}
function setCorrectId(optId) {
  emit('update', { questionData: { ...props.element.questionData, correctId: optId } });
}

// --- Lifecycle ---
onMounted(() => {
  window.addEventListener('keydown', handleGlobalScannerKeydown);
  if (!props.isEditMode && isInteractive.value && !isGraded.value) {
    scannerState.value = 'waiting';
  }
});

onUnmounted(() => {
  isQrDialogOpen.value = false;
  window.removeEventListener('keydown', handleGlobalScannerKeydown);
  if (scannerTimer) { clearTimeout(scannerTimer); scannerTimer = null; }
});

// --- Computed helpers ---
const hasAnswers = computed(() => Object.keys(qHistory.value.groupAnswers).length > 0);
const hasActiveGroupOrPractice = computed(() => !!activeGroupId.value || isPracticeMode.value);

function handleWrapperClick(e) {
  e.stopPropagation();
  emit('select');
}
</script>

<template>
  <q-card
    flat
    bordered
    class="quiz-v2-wrapper"
    @mousedown="handleWrapperClick"
    @touchstart="handleWrapperClick"
  >
    <!-- Mute toggle -->
    <q-btn
      v-if="!isEditMode"
      flat
      round
      dense
      :icon="isMuted ? 'volume_off' : 'volume_up'"
      :color="isMuted ? 'grey-5' : 'grey-6'"
      class="mute-btn"
      @click.stop="toggleMute"
    >
      <q-tooltip>{{ isMuted ? 'Unmute' : 'Mute' }}</q-tooltip>
    </q-btn>

    <q-card-section class="q-pa-md">
      <!-- Question -->
      <QuestionHeader
        :question="element.questionData.question"
        :isEditMode="isEditMode"
        @update="updateQuestion"
        @select="$emit('select')"
      />

      <!-- Grading Result Banner -->
      <GradingResultBanner
        v-if="isGraded"
        :correctCount="Object.values(qHistory.groupAnswers).filter(a => a === element.questionData.correctId).length"
        :wrongCount="Object.values(qHistory.groupAnswers).filter(a => a !== element.questionData.correctId).length"
        :totalGroups="gameStore.groups.length"
      />

      <!-- Options Grid -->
      <OptionGrid
        :options="element.questionData.options"
        :groupAnswers="qHistory.groupAnswers"
        :correctId="element.questionData.correctId"
        :isGraded="isGraded"
        :isEditMode="isEditMode"
        :isInteractive="isInteractive"
        :hasActiveGroup="hasActiveGroupOrPractice"
        :groups="gameStore.groups"
        @optionClick="handleOptionClick"
        @optionHover="handleOptionHover"
        @setCorrect="setCorrectId"
        @updateOption="updateOption"
        @select="$emit('select')"
      />

      <!-- Instructor Panel (not in edit mode) -->
      <template v-if="!isEditMode">
        <q-separator class="q-my-md" />

        <!-- Group Selector -->
        <GroupSelector
          :groups="gameStore.groups"
          :activeGroupId="activeGroupId"
          :groupAnswers="qHistory.groupAnswers"
          :isGraded="isGraded"
          :isInteractive="isInteractive"
          :correctId="element.questionData.correctId"
          @selectGroup="selectGroup"
          @clearGroupAnswer="clearGroupAnswer"
          @printQr="isPrintQrOpen = true"
          @scanQr="openQrScanner"
        />

        <!-- Scanner Panel -->
        <QrScannerPanel
          v-if="isInteractive && !isGraded"
          :scannerState="scannerState"
          :lastRaw="scannerLastRaw"
          :lastResult="scannerLastResult"
          class="q-mt-sm"
        />

        <!-- Control Bar -->
        <QuizControlBar
          class="q-mt-md"
          :isInteractive="isInteractive"
          :isGraded="isGraded"
          :isPracticeMode="isPracticeMode"
          :hasAnswers="hasAnswers"
          :isMuted="isMuted"
          @start="startQuiz"
          @lock="lockQuiz"
          @grade="handleGrade"
          @practice="startPractice"
          @replay="replayQuiz"
          @shuffle="shuffleOptions"
          @toggleMute="toggleMute"
        />
      </template>
    </q-card-section>

    <!-- QR Scan Dialog -->
    <QrScanDialog
      v-model="isQrDialogOpen"
      title="Scan QR"
      subtitle="Scan g1_a to auto-answer, or scan group id/name to select the group."
      @scanned="handleScannedQr"
    />

    <!-- QR Print Dialog -->
    <q-dialog v-model="isPrintQrOpen" maximized persistent>
      <q-card class="qr-print-dialog">
        <q-bar class="bg-primary text-white">
          <q-icon name="qr_code" />
          <div>Print Group QR Codes</div>
          <q-space />
          <q-btn dense flat icon="print" @click="$refs.printSheet?.print()" label="Print" />
          <q-btn dense flat icon="close" @click="isPrintQrOpen = false" />
        </q-bar>
        <q-card-section class="q-pa-none">
          <QrPrintSheet
            ref="printSheet"
            :groups="gameStore.groups"
            :options="element.questionData.options"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-card>
</template>

<style scoped>
.quiz-v2-wrapper {
  width: 100%;
  height: 100%;
  background: #fff;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.mute-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  z-index: 10;
}
.qr-print-dialog {
  width: 100%;
  max-width: 100%;
  height: 100%;
  max-height: 100%;
  display: flex;
  flex-direction: column;
}
.qr-print-dialog :deep(.q-card__section) {
  flex: 1;
  overflow: auto;
}
</style>
