const STORAGE_KEY = 'simple_focus_app_offline_v1_state';
const APP_VERSION = 'ver1';

const DEFAULT_SETTINGS = {
  focusMinutes: 10,
  breakMinutes: 5,
  theme: 'terminal',
};

const DEFAULT_TIMER = {
  mode: 'idle',
  status: 'idle',
  plannedSeconds: DEFAULT_SETTINGS.focusMinutes * 60,
  remainingSeconds: DEFAULT_SETTINGS.focusMinutes * 60,
  startedAt: null,
  endedAt: null,
  lastTickAt: null,
  taskId: null,
};

function nowIso() {
  return new Date().toISOString();
}

function safeParse(value) {
  try {
    return JSON.parse(value);
  } catch (error) {
    return null;
  }
}

function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

function createId(prefix = 'item') {
  if (typeof crypto !== 'undefined' && crypto.randomUUID) {
    return `${prefix}-${crypto.randomUUID()}`;
  }

  return `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
}

function normalizeTask(task, index = 0) {
  const createdAt = task?.createdAt || nowIso();
  const status = ['active', 'done', 'paused', 'needs_continue'].includes(task?.status)
    ? task.status
    : index === 0
      ? 'active'
      : 'paused';

  return {
    id: task?.id || createId('task'),
    title: String(task?.title || '').trim() || 'Untitled task',
    notes: String(task?.notes || '').trim(),
    status,
    createdAt,
    updatedAt: task?.updatedAt || createdAt,
    completedAt: task?.completedAt || null,
  };
}

function normalizeTimelineItem(item) {
  return {
    id: item?.id || createId('log'),
    type: item?.type || 'info',
    taskId: item?.taskId || null,
    label: String(item?.label || 'Log entry'),
    detail: String(item?.detail || ''),
    tone: item?.tone || 'neutral',
    timestamp: item?.timestamp || nowIso(),
  };
}

function normalizeSession(session) {
  return {
    id: session?.id || createId('session'),
    taskId: session?.taskId || null,
    startedAt: session?.startedAt || nowIso(),
    endedAt: session?.endedAt || null,
    plannedSeconds: Number(session?.plannedSeconds || DEFAULT_SETTINGS.focusMinutes * 60),
    actualSeconds: Number(session?.actualSeconds || 0),
    outcome: session?.outcome || 'unknown',
    notes: String(session?.notes || ''),
  };
}

function restoreTimer(timer, settings) {
  const normalized = {
    ...clone(DEFAULT_TIMER),
    ...timer,
  };

  normalized.plannedSeconds = Number(normalized.plannedSeconds || settings.focusMinutes * 60);
  normalized.remainingSeconds = Number(normalized.remainingSeconds || normalized.plannedSeconds);

  if (normalized.status === 'running' && normalized.lastTickAt) {
    const lastTickAt = new Date(normalized.lastTickAt).getTime();
    const elapsed = Math.max(0, Math.floor((Date.now() - lastTickAt) / 1000));

    if (elapsed > 0) {
      normalized.remainingSeconds = Math.max(0, normalized.remainingSeconds - elapsed);
    }

    normalized.lastTickAt = nowIso();

    if (normalized.remainingSeconds <= 0) {
      normalized.status = 'completed';
      normalized.endedAt = nowIso();
    }
  }

  return normalized;
}

export function createDefaultState() {
  const stamp = nowIso();

  return {
    version: APP_VERSION,
    meta: {
      createdAt: stamp,
      updatedAt: stamp,
    },
    settings: {
      ...DEFAULT_SETTINGS,
    },
    activeTaskId: null,
    tasks: [],
    sessions: [],
    timeline: [],
    timer: {
      ...clone(DEFAULT_TIMER),
    },
  };
}

export function normalizeState(rawState) {
  const base = createDefaultState();
  const source = rawState && typeof rawState === 'object' ? rawState : {};
  const settings = {
    ...base.settings,
    ...(source.settings && typeof source.settings === 'object' ? source.settings : {}),
  };

  const tasks = Array.isArray(source.tasks)
    ? source.tasks.map((task, index) => normalizeTask(task, index))
    : [];

  const timeline = Array.isArray(source.timeline)
    ? source.timeline.map((item) => normalizeTimelineItem(item))
    : [];

  const sessions = Array.isArray(source.sessions)
    ? source.sessions.map((item) => normalizeSession(item))
    : [];

  const activeTaskFromList = tasks.find((task) => task.status === 'active') || null;
  const activeTaskId = source.activeTaskId || activeTaskFromList?.id || null;

  return {
    version: source.version || APP_VERSION,
    meta: {
      createdAt: source?.meta?.createdAt || base.meta.createdAt,
      updatedAt: source?.meta?.updatedAt || base.meta.updatedAt,
    },
    settings,
    activeTaskId,
    tasks,
    sessions,
    timeline,
    timer: restoreTimer(source.timer, settings),
  };
}

export function loadFocusAppState() {
  if (typeof window === 'undefined') {
    return createDefaultState();
  }

  const raw = window.localStorage.getItem(STORAGE_KEY);
  if (!raw) {
    return createDefaultState();
  }

  const parsed = safeParse(raw);
  return normalizeState(parsed);
}

export function saveFocusAppState(state) {
  if (typeof window === 'undefined') {
    return normalizeState(state);
  }

  const nextState = normalizeState(state);
  nextState.meta.updatedAt = nowIso();
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(nextState, null, 2));
  return nextState;
}

export function clearFocusAppStorage() {
  if (typeof window === 'undefined') {
    return;
  }

  window.localStorage.removeItem(STORAGE_KEY);
}

export function exportFocusAppState(state) {
  if (typeof window === 'undefined') {
    return;
  }

  const payload = JSON.stringify(normalizeState(state), null, 2);
  const fileName = `simple-focus-app-offline-${new Date().toISOString().slice(0, 10)}.json`;
  const blob = new Blob([payload], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = fileName;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}

export async function importFocusAppStateFile(file) {
  const text = await file.text();
  const parsed = safeParse(text);

  if (!parsed) {
    throw new Error('The selected file is not valid JSON.');
  }

  return normalizeState(parsed);
}

export function formatDuration(totalSeconds) {
  const safeSeconds = Math.max(0, Math.floor(Number(totalSeconds) || 0));
  const minutes = Math.floor(safeSeconds / 60);
  const seconds = safeSeconds % 60;

  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

export function formatTimestamp(value) {
  if (!value) {
    return '--:--';
  }

  const date = new Date(value);
  return new Intl.DateTimeFormat('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  }).format(date);
}

export function formatTimelineDate(value) {
  if (!value) {
    return 'unknown';
  }

  return new Intl.DateTimeFormat('en-US', {
    weekday: 'short',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value));
}

export { APP_VERSION, STORAGE_KEY, createId };
