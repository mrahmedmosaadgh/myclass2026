import { computed, onBeforeUnmount, ref, watch } from 'vue';
import {
  clearFocusAppStorage,
  createDefaultState,
  createId,
  exportFocusAppState,
  formatDuration,
  importFocusAppStateFile,
  loadFocusAppState,
  saveFocusAppState,
} from '../lib/focusAppStorage';

const FOCUS_MINUTES = 10;
const BREAK_MINUTES = 5;

function nowIso() {
  return new Date().toISOString();
}

export function useFocusApp() {
  const state = ref(loadFocusAppState());
  const draftTitle = ref('');
  const draftNotes = ref('');
  const isImporting = ref(false);
  const lastAction = ref('Ready');
  const isTickerActive = ref(false);

  let tickerId = null;

  const activeTask = computed(() => {
    if (!state.value.activeTaskId) {
      return null;
    }

    return state.value.tasks.find((task) => task.id === state.value.activeTaskId) || null;
  });

  const runningTaskLabel = computed(() => activeTask.value?.title || 'NO ACTIVE TASK');

  const timerLabel = computed(() => formatDuration(state.value.timer.remainingSeconds));

  const timerProgress = computed(() => {
    const planned = Number(state.value.timer.plannedSeconds || 0);
    if (!planned) {
      return 0;
    }

    return Math.min(100, Math.max(0, ((planned - state.value.timer.remainingSeconds) / planned) * 100));
  });

  const timeline = computed(() => state.value.timeline);
  const taskCount = computed(() => state.value.tasks.length);
  const sessionCount = computed(() => state.value.sessions.length);
  const completedCount = computed(() => state.value.tasks.filter((task) => task.status === 'done').length);

  function pushTimeline(type, taskId, label, detail = '', tone = 'neutral') {
    const event = {
      id: createId('log'),
      type,
      taskId: taskId || null,
      label,
      detail,
      tone,
      timestamp: nowIso(),
    };

    state.value.timeline.unshift(event);
    return event;
  }

  function setActiveTask(task) {
    state.value.activeTaskId = task ? task.id : null;
    if (task) {
      task.status = 'active';
      task.updatedAt = nowIso();
    }
  }

  function createTask(title, notes = '') {
    const nextTask = {
      id: createId('task'),
      title: String(title || '').trim(),
      notes: String(notes || '').trim(),
      status: 'active',
      createdAt: nowIso(),
      updatedAt: nowIso(),
      completedAt: null,
    };

    state.value.tasks.unshift(nextTask);
    setActiveTask(nextTask);
    lastAction.value = `Task created: ${nextTask.title}`;
    pushTimeline('task_created', nextTask.id, nextTask.title, nextTask.notes || 'New focus task started', 'success');
    return nextTask;
  }

  function startCountdown(taskId, mode = 'focus', minutes = FOCUS_MINUTES, source = 'manual') {
    const startedAt = nowIso();
    const seconds = Math.max(1, Math.floor(minutes * 60));

    state.value.timer = {
      mode,
      status: 'running',
      plannedSeconds: seconds,
      remainingSeconds: seconds,
      startedAt,
      endedAt: null,
      lastTickAt: startedAt,
      taskId,
      source,
    };

    if (mode === 'focus') {
      lastAction.value = `Focus session started (${minutes}m)`;
      pushTimeline('timer_started', taskId, 'Focus timer started', `${minutes} minute focus block`, 'success');
    } else {
      lastAction.value = `Break started (${minutes}m)`;
      pushTimeline('break_started', taskId, 'Break timer started', `${minutes} minute recovery block`, 'warning');
    }

    startTicker();
  }

  function startTicker() {
    stopTicker();
    isTickerActive.value = true;

    tickerId = window.setInterval(() => {
      const timer = state.value.timer;
      if (timer.status !== 'running') {
        return;
      }

      timer.remainingSeconds = Math.max(0, timer.remainingSeconds - 1);
      timer.lastTickAt = nowIso();

      if (timer.remainingSeconds <= 0) {
        if (timer.mode === 'break') {
          finishBreakAndResumeFocus();
        } else {
          finishFocusTimer();
        }
      }
    }, 1000);
  }

  function stopTicker() {
    if (tickerId !== null) {
      window.clearInterval(tickerId);
      tickerId = null;
    }

    isTickerActive.value = false;
  }

  function pauseTimer() {
    if (state.value.timer.status !== 'running') {
      return;
    }

    state.value.timer.status = 'paused';
    state.value.timer.lastTickAt = nowIso();
    stopTicker();
    lastAction.value = 'Timer paused';
    pushTimeline('timer_paused', state.value.timer.taskId, 'Timer paused', 'Focus timer paused', 'warning');
  }

  function resumeTimer() {
    if (state.value.timer.status !== 'paused') {
      return;
    }

    state.value.timer.status = 'running';
    state.value.timer.lastTickAt = nowIso();
    lastAction.value = 'Timer resumed';
    pushTimeline('timer_resumed', state.value.timer.taskId, 'Timer resumed', 'Focus timer resumed', 'success');
    startTicker();
  }

  function resetTimer() {
    const timer = state.value.timer;
    timer.status = 'idle';
    timer.mode = 'idle';
    timer.plannedSeconds = state.value.settings.focusMinutes * 60;
    timer.remainingSeconds = timer.plannedSeconds;
    timer.startedAt = null;
    timer.endedAt = null;
    timer.lastTickAt = null;
    timer.source = 'manual';
    stopTicker();
    lastAction.value = 'Timer reset';
    pushTimeline('timer_reset', timer.taskId, 'Timer reset', 'Timer returned to the default 10 minute block', 'neutral');
  }

  function recordSession(taskId, plannedSeconds, actualSeconds, outcome, notes = '') {
    state.value.sessions.unshift({
      id: createId('session'),
      taskId,
      startedAt: state.value.timer.startedAt || nowIso(),
      endedAt: nowIso(),
      plannedSeconds,
      actualSeconds,
      outcome,
      notes,
    });
  }

  function finishFocusTimer() {
    const timer = state.value.timer;
    stopTicker();
    timer.status = 'completed';
    timer.remainingSeconds = 0;
    timer.endedAt = nowIso();

    const task = activeTask.value;
    if (task) {
      task.updatedAt = nowIso();
    }

    recordSession(timer.taskId, timer.plannedSeconds, timer.plannedSeconds, 'focus_complete');
    pushTimeline('timer_finished', timer.taskId, 'Focus timer ended', 'Choose the next action', 'danger');
    lastAction.value = 'Timer completed';
  }

  function finishBreakAndResumeFocus() {
    const taskId = state.value.timer.taskId;
    stopTicker();
    recordSession(taskId, state.value.timer.plannedSeconds, state.value.timer.plannedSeconds, 'break_complete');
    pushTimeline('break_finished', taskId, 'Break finished', 'Automatically resuming focus block', 'success');
    startCountdown(taskId, 'focus', FOCUS_MINUTES, 'after_break');
  }

  function startTask(title, notes = '') {
    const task = createTask(title, notes);
    startCountdown(task.id, 'focus', FOCUS_MINUTES, 'new_task');
    return task;
  }

  function continueSameTask(taskId = state.value.timer.taskId || state.value.activeTaskId) {
    const task = state.value.tasks.find((entry) => entry.id === taskId);
    if (!task) {
      return;
    }

    setActiveTask(task);
    startCountdown(task.id, 'focus', FOCUS_MINUTES, 'continue_same_task');
    pushTimeline('task_continued', task.id, 'Continuing same task', 'New 10 minute block started', 'success');
  }

  function startBreak(taskId = state.value.timer.taskId || state.value.activeTaskId) {
    if (!taskId) {
      return;
    }

    startCountdown(taskId, 'break', BREAK_MINUTES, 'break');
  }

  function markCurrentTaskDone() {
    const task = activeTask.value;
    if (!task) {
      return;
    }

    task.status = 'done';
    task.completedAt = nowIso();
    task.updatedAt = nowIso();
    state.value.activeTaskId = null;
    lastAction.value = `Task done: ${task.title}`;
    pushTimeline('task_done', task.id, 'Task marked done', 'Old task completed and archived', 'success');
  }

  function createNewTaskAfterDone(nextTitle = '', nextNotes = '') {
    markCurrentTaskDone();
    state.value.timer.status = 'idle';
    state.value.timer.mode = 'idle';
    state.value.timer.remainingSeconds = state.value.settings.focusMinutes * 60;
    state.value.timer.plannedSeconds = state.value.settings.focusMinutes * 60;
    state.value.timer.startedAt = null;
    state.value.timer.endedAt = null;
    state.value.timer.lastTickAt = null;
    stopTicker();

    if (String(nextTitle || '').trim()) {
      return startTask(nextTitle, nextNotes);
    }

    lastAction.value = 'Ready for a new task';
    pushTimeline('task_ready', null, 'Ready for next task', 'Previous task was closed', 'neutral');
    return null;
  }

  function markNeedsContinue(notes = '') {
    const task = activeTask.value;
    if (!task) {
      return;
    }

    task.status = 'needs_continue';
    task.notes = String(notes || '').trim();
    task.updatedAt = nowIso();
    state.value.activeTaskId = task.id;
    state.value.timer.status = 'idle';
    state.value.timer.mode = 'idle';
    state.value.timer.remainingSeconds = state.value.settings.focusMinutes * 60;
    state.value.timer.plannedSeconds = state.value.settings.focusMinutes * 60;
    state.value.timer.startedAt = null;
    state.value.timer.endedAt = null;
    state.value.timer.lastTickAt = null;
    stopTicker();

    lastAction.value = 'Task saved for later';
    pushTimeline('task_needs_continue', task.id, 'Need to continue later', task.notes || 'User added a continuation note', 'warning');
  }

  function resumeTaskFromTimeline(taskId) {
    const task = state.value.tasks.find((entry) => entry.id === taskId);
    if (!task) {
      return;
    }

    setActiveTask(task);
    startCountdown(task.id, 'focus', FOCUS_MINUTES, 'timeline_resume');
    pushTimeline('task_resumed', task.id, 'Task resumed from timeline', task.title, 'success');
  }

  async function importStateFromFile(file) {
    isImporting.value = true;

    try {
      const imported = await importFocusAppStateFile(file);
      state.value = imported;
      lastAction.value = 'State imported';
      pushTimeline('state_imported', null, 'Data imported', 'Local app state replaced from JSON import', 'success');
      return imported;
    } finally {
      isImporting.value = false;
    }
  }

  function exportState() {
    exportFocusAppState(state.value);
    lastAction.value = 'State exported';
    pushTimeline('state_exported', null, 'Data exported', 'JSON file downloaded', 'success');
  }

  function clearAllData() {
    stopTicker();
    clearFocusAppStorage();
    state.value = createDefaultState();
    draftTitle.value = '';
    draftNotes.value = '';
    lastAction.value = 'All data cleared';
    pushTimeline('data_cleared', null, 'Data cleared', 'Local storage was reset', 'danger');
  }

  function hydrateFromStorage() {
    state.value = loadFocusAppState();
    if (state.value.timer.status === 'running') {
      startTicker();
    }
  }

  function updateDraftTitle(value) {
    draftTitle.value = value;
  }

  function updateDraftNotes(value) {
    draftNotes.value = value;
  }

  watch(
    state,
    (value) => {
      saveFocusAppState(value);
    },
    { deep: true }
  );

  onBeforeUnmount(() => {
    stopTicker();
  });

  return {
    state,
    draftTitle,
    draftNotes,
    isImporting,
    lastAction,
    isTickerActive,
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
    markCurrentTaskDone,
    createNewTaskAfterDone,
    markNeedsContinue,
    resumeTaskFromTimeline,
    exportState,
    importStateFromFile,
    clearAllData,
    hydrateFromStorage,
    formatDuration,
  };
}
