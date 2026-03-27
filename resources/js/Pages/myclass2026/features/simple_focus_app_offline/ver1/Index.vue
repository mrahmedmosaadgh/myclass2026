<script setup>
import { onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import StandaloneLayout from './layouts/StandaloneLayout.vue';
import { useFocusApp } from './composables/useFocusApp';
import { useStandaloneApp } from './composables/useStandaloneApp';
import ConfirmDialog from './components/ConfirmDialog.vue';
import TaskComposer from './components/TaskComposer.vue';
import TimerPanel from './components/TimerPanel.vue';
import ActionChooser from './components/ActionChooser.vue';
import TimelineLog from './components/TimelineLog.vue';
import DataToolsPanel from './components/DataToolsPanel.vue';

defineOptions({ layout: StandaloneLayout });

const {
  state,
  draftTitle,
  draftNotes,
  isImporting,
  lastAction,
  activeTask,
  runningTaskLabel,
  timerLabel,
  timerProgress,
  timeline,
  taskCount,
  sessionCount,
  completedCount,
  updateDraftTitle,
  updateDraftNotes,
  startTask,
  continueSameTask,
  startBreak,
  pauseTimer,
  resumeTimer,
  resetTimer,
  createNewTaskAfterDone,
  markNeedsContinue,
  resumeTaskFromTimeline,
  exportState,
  importStateFromFile,
  clearAllData,
  hydrateFromStorage,
} = useFocusApp();

const {
  manifestHref,
  canInstall,
  isInstalled,
  serviceWorkerStatus,
  promptInstall,
} = useStandaloneApp();

const confirmState = ref({
  open: false,
  title: 'CONFIRM ACTION',
  message: '',
  confirmLabel: 'CONFIRM',
  cancelLabel: 'CANCEL',
  tone: 'warning',
  onConfirm: null,
});

const pendingImportFile = ref(null);

function askConfirm({ title, message, confirmLabel = 'CONFIRM', cancelLabel = 'CANCEL', tone = 'warning', onConfirm }) {
  confirmState.value = {
    open: true,
    title,
    message,
    confirmLabel,
    cancelLabel,
    tone,
    onConfirm,
  };
}

function closeConfirm() {
  confirmState.value.open = false;
  confirmState.value.onConfirm = null;
}

function runConfirm() {
  const callback = confirmState.value.onConfirm;
  closeConfirm();
  if (typeof callback === 'function') {
    callback();
  }
}

function handleCreateTask() {
  const title = draftTitle.value.trim();
  if (!title) {
    askConfirm({
      title: 'MISSING TASK TITLE',
      message: 'Type a task title before starting the 10 minute focus block.',
      confirmLabel: 'OK',
      cancelLabel: 'BACK',
      tone: 'danger',
      onConfirm: () => {},
    });
    return;
  }

  askConfirm({
    title: 'START NEW TASK',
    message: `Start this task and begin the default 10 minute timer?\n\nTask: ${title}`,
    confirmLabel: 'START',
    cancelLabel: 'EDIT',
    tone: 'success',
    onConfirm: () => {
      startTask(title, draftNotes.value);
      updateDraftTitle('');
      updateDraftNotes('');
    },
  });
}

function handlePause() {
  askConfirm({
    title: 'PAUSE TIMER',
    message: 'Pause the current focus timer?',
    confirmLabel: 'PAUSE',
    tone: 'warning',
    onConfirm: () => pauseTimer(),
  });
}

function handleResume() {
  askConfirm({
    title: 'RESUME TIMER',
    message: 'Resume the current focus timer?',
    confirmLabel: 'RESUME',
    tone: 'success',
    onConfirm: () => resumeTimer(),
  });
}

function handleReset() {
  askConfirm({
    title: 'RESET TIMER',
    message: 'Reset the timer and keep the current task state?',
    confirmLabel: 'RESET',
    tone: 'danger',
    onConfirm: () => resetTimer(),
  });
}

function handleBreak() {
  askConfirm({
    title: 'START 5 MIN BREAK',
    message: 'Start a 5 minute break and automatically continue the same task afterward?',
    confirmLabel: 'BREAK',
    tone: 'warning',
    onConfirm: () => startBreak(),
  });
}

function handleContinueSameTask() {
  askConfirm({
    title: 'CONTINUE SAME TASK',
    message: 'Start another 10 minute block on the same task?',
    confirmLabel: 'CONTINUE',
    tone: 'success',
    onConfirm: () => continueSameTask(),
  });
}

function handleDoneAndNewTask() {
  askConfirm({
    title: 'MARK OLD TASK DONE',
    message: 'Mark the current task done and open a new 10 minute task?',
    confirmLabel: 'DONE',
    tone: 'danger',
    onConfirm: () => createNewTaskAfterDone(draftTitle.value.trim(), draftNotes.value),
  });
}

function handleNeedsContinue() {
  askConfirm({
    title: 'SAVE FOR LATER',
    message: 'Mark the task as needing follow-up and store your notes?',
    confirmLabel: 'SAVE NOTE',
    tone: 'warning',
    onConfirm: () => markNeedsContinue(draftNotes.value),
  });
}

function handleTimelineResume(taskId) {
  const task = state.value.tasks.find((item) => item.id === taskId);
  if (!task) {
    return;
  }

  askConfirm({
    title: 'RESUME FROM TIMELINE',
    message: `Resume this task and start a new 10 minute block?\n\nTask: ${task.title}`,
    confirmLabel: 'RESUME',
    tone: 'success',
    onConfirm: () => resumeTaskFromTimeline(taskId),
  });
}

function handleExport() {
  askConfirm({
    title: 'EXPORT JSON',
    message: 'Download a JSON copy of your local focus data?',
    confirmLabel: 'EXPORT',
    tone: 'success',
    onConfirm: () => exportState(),
  });
}

function handleClear() {
  askConfirm({
    title: 'CLEAR LOCAL DATA',
    message: 'This will delete tasks, sessions, and timeline history from this browser. Continue?',
    confirmLabel: 'CLEAR',
    tone: 'danger',
    onConfirm: () => clearAllData(),
  });
}

function handleInstall() {
  askConfirm({
    title: 'INSTALL APP',
    message: 'Open the browser install flow for the standalone focus app?',
    confirmLabel: 'INSTALL',
    tone: 'success',
    onConfirm: async () => {
      await promptInstall();
    },
  });
}

async function handleImportFile(file) {
  pendingImportFile.value = file;

  askConfirm({
    title: 'IMPORT JSON',
    message: 'Importing will replace the current local data. Continue?',
    confirmLabel: 'IMPORT',
    tone: 'warning',
    onConfirm: async () => {
      if (!pendingImportFile.value) {
        return;
      }

      await importStateFromFile(pendingImportFile.value);
      pendingImportFile.value = null;
    },
  });
}

onMounted(() => {
  hydrateFromStorage();
});
</script>

<template>
  <Head>
    <title>Simple Focus App Offline v1</title>
    <meta name="description" content="Standalone offline focus app with DOS-style workflow and local timeline logs.">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" :href="manifestHref">
    <link rel="icon" href="/simple-focus-app-offline/v1/icon.svg" type="image/svg+xml">
  </Head>

  <main class="focus-app">
    <header class="topbar">
      <div>
        <div class="brand-line">SIMPLE FOCUS APP OFFLINE / VER1</div>
        <div class="sub-line">NO AUTH • LOCAL FIRST • INSTALLABLE</div>
      </div>

      <div class="status-strip">
        <span>tasks: {{ taskCount }}</span>
        <span>sessions: {{ sessionCount }}</span>
        <span>done: {{ completedCount }}</span>
        <span>sw: {{ serviceWorkerStatus }}</span>
      </div>
    </header>

    <section class="hero-grid">
      <TaskComposer
        :title="draftTitle"
        :notes="draftNotes"
        :active-task-title="runningTaskLabel"
        :disabled="isImporting"
        @update:title="updateDraftTitle"
        @update:notes="updateDraftNotes"
        @create="handleCreateTask"
      />

      <TimerPanel
        :task-title="runningTaskLabel"
        :timer-label="timerLabel"
        :timer-status="state.timer.status"
        :progress="timerProgress"
        :last-action="lastAction"
        @pause="handlePause"
        @resume="handleResume"
        @reset="handleReset"
      />
    </section>

    <ActionChooser
      :visible="state.timer.status === 'completed'"
      :task-title="runningTaskLabel"
      :next-task-title="draftTitle"
      :next-task-notes="draftNotes"
      :continue-notes="draftNotes"
      @update:nextTaskTitle="updateDraftTitle"
      @update:nextTaskNotes="updateDraftNotes"
      @update:continueNotes="updateDraftNotes"
      @break="handleBreak"
      @continue="handleContinueSameTask"
      @done="handleDoneAndNewTask"
      @needs-continue="handleNeedsContinue"
    />

    <section class="timeline-grid">
      <TimelineLog
        :entries="timeline"
        :active-task-id="activeTask?.id || null"
        @resume="handleTimelineResume"
      />

      <DataToolsPanel
        :can-install="canInstall"
        :is-installed="isInstalled"
        :is-importing="isImporting"
        :service-worker-status="serviceWorkerStatus"
        @export="handleExport"
        @clear="handleClear"
        @install="handleInstall"
        @import-file="handleImportFile"
      />
    </section>

    <section class="footer-strip">
      <div class="footer-box">
        <span>layout</span>
        <strong>DOS / TERMINAL STYLE</strong>
      </div>
      <div class="footer-box">
        <span>default block</span>
        <strong>10 MIN FOCUS</strong>
      </div>
      <div class="footer-box">
        <span>offline storage</span>
        <strong>LOCALSTORAGE JSON</strong>
      </div>
    </section>
  </main>

  <ConfirmDialog
    :open="confirmState.open"
    :title="confirmState.title"
    :message="confirmState.message"
    :confirm-label="confirmState.confirmLabel"
    :cancel-label="confirmState.cancelLabel"
    :tone="confirmState.tone"
    @cancel="closeConfirm"
    @confirm="runConfirm"
  />
</template>

<style scoped>
.focus-app {
  min-height: 100vh;
  padding: 1.2rem;
  background: #000;
  color: #f0fdf4;
  font-family: 'Courier New', Courier, monospace;
  text-shadow: 0 0 6px rgba(74, 222, 128, 0.1);
}

.topbar {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.brand-line {
  color: #4ade80;
  letter-spacing: 0.22em;
  font-size: 0.9rem;
  font-weight: 600;
  text-shadow: 0 0 12px rgba(74, 222, 128, 0.4);
}

.sub-line {
  margin-top: 0.35rem;
  color: #86efac;
  letter-spacing: 0.12em;
  font-size: 0.76rem;
  opacity: 0.9;
}

.status-strip {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  color: #86efac;
  font-size: 0.76rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  background: rgba(34, 197, 94, 0.08);
  padding: 0.5rem 0.8rem;
  border: 1px solid rgba(34, 197, 94, 0.2);
  border-radius: 2px;
}

.hero-grid,
.timeline-grid,
.footer-strip {
  display: grid;
  gap: 0.9rem;
}

.hero-grid {
  grid-template-columns: 1fr;
}

.timeline-grid {
  margin-top: 0.9rem;
  grid-template-columns: 1.4fr 0.8fr;
}

.footer-strip {
  margin-top: 0.9rem;
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.footer-box {
  border: 1px solid rgba(74, 222, 128, 0.4);
  padding: 0.8rem;
  background: rgba(10, 10, 10, 0.8);
  border-radius: 2px;
  box-shadow: inset 0 0 0 1px rgba(74, 222, 128, 0.1);
}

.footer-box span {
  display: block;
  color: #4ade80;
  font-size: 0.75rem;
  letter-spacing: 0.18em;
  margin-bottom: 0.35rem;
  font-weight: 500;
}

.footer-box strong {
  color: #f0fdf4;
  letter-spacing: 0.12em;
  text-shadow: 0 0 8px rgba(74, 222, 128, 0.2);
}

@media (max-width: 900px) {
  .timeline-grid,
  .footer-strip {
    grid-template-columns: 1fr;
  }

  .focus-app {
    padding: 0.75rem;
  }
}
</style>
