<script>
export default { layout: false };
</script>

<script setup>
import { computed, ref } from 'vue';
import { useGameStore } from '../stores/gameStore';
import { usePresentationStore } from '../stores/presentationStore';
import { useRemoteDiagnostics } from './composables/useRemoteDiagnostics';

const gameStore = useGameStore();
const presentation = usePresentationStore();
const isCopying = ref(false);
const baseUrl = typeof window !== 'undefined' ? window.location.origin : '';

const teacherUrl = computed(() => `${baseUrl}/classroom-records/presentation/remote/teacher`);
const studentUrl = computed(() => `${baseUrl}/classroom-records/presentation/remote/student`);
const builderUrl = computed(() => `${baseUrl}/classroom-records/presentation/builder-v5`);
const sessionLabel = computed(() => gameStore.accessCode ? `Session ${gameStore.accessCode}` : 'No active remote session');
const slideLabel = computed(() => `${presentation.currentSlideIndex + 1} / ${presentation.slides.length}`);
const hasSession = computed(() => Boolean(gameStore.sessionId && gameStore.accessCode));

const featureTests = [
  {
    id: 'browser-runtime',
    label: 'Browser runtime',
    category: 'environment',
    description: 'Confirms the page can access the browser APIs needed by the remote system.',
    run: ({ systemInfo }) => ({
      message: systemInfo.online ? 'Browser runtime is ready and online' : 'Browser runtime is ready but offline',
      details: {
        online: systemInfo.online,
        viewport: systemInfo.viewport,
        userAgent: systemInfo.userAgent,
        localStorage: systemInfo.localStorage,
        sessionStorage: systemInfo.sessionStorage,
        fetch: systemInfo.fetch,
        clipboard: systemInfo.clipboard,
      },
    }),
  },
  {
    id: 'presentation-store',
    label: 'Presentation store',
    category: 'state',
    description: 'Checks the slide state that remote teacher/student pages depend on.',
    run: () => {
      const slideCount = presentation.slides.length;
      const currentSlide = presentation.currentSlide;
      const inRange = presentation.currentSlideIndex >= 0 && presentation.currentSlideIndex < slideCount;

      if (!slideCount) {
        return {
          status: 'failed',
          message: 'Presentation store has no slides',
          details: {
            slideCount,
            currentSlideIndex: presentation.currentSlideIndex,
          },
        };
      }

      if (!inRange || !currentSlide) {
        return {
          status: 'failed',
          message: 'Current slide index is out of range',
          details: {
            slideCount,
            currentSlideIndex: presentation.currentSlideIndex,
            currentSlide,
          },
        };
      }

      return {
        message: 'Presentation store is ready',
        details: {
          slideCount,
          currentSlideIndex: presentation.currentSlideIndex,
          currentSlideId: currentSlide.id,
          elementCount: currentSlide.elements?.length || 0,
          title: presentation.title,
        },
      };
    },
  },
  {
    id: 'game-store',
    label: 'Remote game store',
    category: 'state',
    description: 'Validates the shared remote session state used by teacher and student views.',
    run: () => ({
      message: 'Game store loaded',
      details: {
        sessionId: gameStore.sessionId,
        accessCode: gameStore.accessCode,
        sessionStatus: gameStore.sessionStatus,
        onlineCount: gameStore.onlineCount,
        participantCount: gameStore.participants.length,
      },
    }),
  },
  {
    id: 'teacher-link',
    label: 'Teacher route link',
    category: 'routes',
    description: 'Confirms the teacher remote URL is assembled correctly for later navigation.',
    run: () => ({
      message: 'Teacher remote link ready',
      details: {
        url: teacherUrl.value,
        routeHint: '/classroom-records/presentation/remote/teacher',
      },
    }),
  },
  {
    id: 'student-link',
    label: 'Student route link',
    category: 'routes',
    description: 'Confirms the student join URL is assembled correctly for later navigation.',
    run: () => ({
      message: 'Student remote link ready',
      details: {
        url: studentUrl.value,
        routeHint: '/classroom-records/presentation/remote/student',
      },
    }),
  },
  {
    id: 'session-channel-names',
    label: 'Realtime channel names',
    category: 'realtime',
    description: 'Verifies the teacher/student channel naming convention used by the remote system.',
    run: () => {
      if (!gameStore.accessCode) {
        return {
          status: 'skipped',
          message: 'No access code yet, so realtime channels cannot be derived',
          details: {
            sessionId: gameStore.sessionId,
            accessCode: gameStore.accessCode,
          },
        };
      }

      return {
        message: 'Realtime channel names derived successfully',
        details: {
          studentChannel: `quiz_${gameStore.accessCode}`,
          teacherChannel: `quiz_${gameStore.accessCode}_teacher`,
        },
      };
    },
  },
  {
    id: 'live-stats-endpoint',
    label: 'Live stats endpoint',
    category: 'api',
    description: 'Checks that the teacher stats endpoint responds for the active session when one exists.',
    run: async () => {
      if (!hasSession.value) {
        return {
          status: 'skipped',
          message: 'No active session to verify stats against',
          details: {
            sessionId: gameStore.sessionId,
            accessCode: gameStore.accessCode,
          },
        };
      }

      const response = await fetch(`/api/cr/sessions/${gameStore.sessionId}/stats`, {
        headers: {
          Accept: 'application/json',
        },
      });

      let payload = null;
      try {
        payload = await response.json();
      } catch (error) {
        payload = {
          parseError: error?.message || 'Unable to parse JSON response',
        };
      }

      if (!response.ok) {
        return {
          status: 'failed',
          message: `Stats endpoint returned HTTP ${response.status}`,
          details: payload,
        };
      }

      return {
        message: 'Stats endpoint responded successfully',
        details: payload,
      };
    },
  },
  {
    id: 'clipboard-api',
    label: 'Clipboard API',
    category: 'environment',
    description: 'Verifies clipboard support for diagnostics export and future remote tools.',
    run: () => {
      if (typeof navigator === 'undefined' || !navigator.clipboard) {
        return {
          status: 'skipped',
          message: 'Clipboard API is unavailable in this browser context',
          details: {
            available: false,
          },
        };
      }

      return {
        message: 'Clipboard API is available',
        details: {
          available: true,
          writeText: typeof navigator.clipboard.writeText === 'function',
        },
      };
    },
  },
];

const diagnostics = useRemoteDiagnostics({
  tests: featureTests,
  autoRun: true,
  maxLogEntries: 80,
});

const statusLabels = {
  passed: 'PASS',
  failed: 'FAIL',
  skipped: 'SKIP',
};

const statusOrder = {
  failed: 0,
  skipped: 1,
  passed: 2,
};

const groupedResults = computed(() => {
  return [...diagnostics.results.value].sort((a, b) => {
    const orderA = statusOrder[a.status] ?? 99;
    const orderB = statusOrder[b.status] ?? 99;

    if (orderA !== orderB) {
      return orderA - orderB;
    }

    return (a.label || '').localeCompare(b.label || '');
  });
});

const recentLogs = computed(() => diagnostics.logs.value.slice(0, 20));

const summaryCards = computed(() => [
  {
    label: 'Total checks',
    value: diagnostics.summary.value.total,
    tone: 'indigo',
  },
  {
    label: 'Passed',
    value: diagnostics.summary.value.passed,
    tone: 'emerald',
  },
  {
    label: 'Failed',
    value: diagnostics.summary.value.failed,
    tone: 'rose',
  },
  {
    label: 'Skipped',
    value: diagnostics.summary.value.skipped,
    tone: 'amber',
  },
  {
    label: 'Success rate',
    value: `${diagnostics.summary.value.successRate}%`,
    tone: 'slate',
  },
]);

const systemFields = computed(() => [
  ['URL', diagnostics.systemInfo.value.url],
  ['Path', diagnostics.systemInfo.value.path],
  ['Origin', diagnostics.systemInfo.value.origin],
  ['Viewport', diagnostics.systemInfo.value.viewport],
  ['Language', diagnostics.systemInfo.value.language],
  ['Platform', diagnostics.systemInfo.value.platform],
  ['Time zone', diagnostics.systemInfo.value.timeZone],
  ['Online', diagnostics.systemInfo.value.online ? 'Yes' : 'No'],
]);

function formatDuration(durationMs) {
  if (durationMs === undefined || durationMs === null) {
    return '—';
  }

  return `${durationMs}ms`;
}

function statusClass(status) {
  return `status-${status}`;
}

function cardClass(tone) {
  return `tone-${tone}`;
}

async function handleCopyReport() {
  isCopying.value = true;

  try {
    await diagnostics.copyReport();
    diagnostics.pushLog({
      level: 'info',
      source: 'ui.copy-report',
      message: 'Diagnostic report copied to clipboard',
    });
  } catch (error) {
    diagnostics.pushError('ui.copy-report', error?.message || 'Unable to copy diagnostics report', error);
  } finally {
    isCopying.value = false;
  }
}
</script>

<template>
  <div class="remote-diagnostics-page">
    <section class="hero card">
      <div class="hero-copy">
        <span class="badge">V5 REMOTE TEST HUB</span>
        <h1>Remote system diagnostics</h1>
        <p>
          Use this page to validate remote teacher, student, state, route, and API behavior in one place.
          The test registry is intentionally data-driven so future features can be added without changing the page layout.
        </p>
      </div>

      <div class="hero-actions">
        <button class="btn primary" :disabled="diagnostics.running" @click="diagnostics.runAllTests()">
          {{ diagnostics.running ? 'Running checks...' : 'Run all checks' }}
        </button>
        <button class="btn secondary" :disabled="isCopying" @click="handleCopyReport">
          {{ isCopying ? 'Copying...' : 'Copy report' }}
        </button>
        <button class="btn ghost" :disabled="diagnostics.running" @click="diagnostics.clearLogs(); diagnostics.clearResults(); diagnostics.runAllTests()">
          Reset and rerun
        </button>
      </div>

      <div class="quick-links">
        <a :href="teacherUrl" target="_blank" rel="noreferrer">Open teacher remote</a>
        <a :href="studentUrl" target="_blank" rel="noreferrer">Open student remote</a>
        <a :href="builderUrl" target="_blank" rel="noreferrer">Open builder</a>
      </div>
    </section>

    <section class="stats-grid">
      <article
        v-for="card in summaryCards"
        :key="card.label"
        class="stat-card"
        :class="cardClass(card.tone)"
      >
        <span class="stat-label">{{ card.label }}</span>
        <span class="stat-value">{{ card.value }}</span>
      </article>
      <article class="stat-card tone-slate session-card">
        <span class="stat-label">Current session</span>
        <span class="stat-value session-label">{{ sessionLabel }}</span>
        <small>{{ slideLabel }}</small>
      </article>
    </section>

    <section class="content-grid">
      <div class="left-column">
        <article class="panel card">
          <div class="panel-heading">
            <div>
              <h2>Feature checks</h2>
              <p>Each check is a reusable probe that can be extended later when new remote capabilities are added.</p>
            </div>
            <span class="panel-meta">{{ diagnostics.lastRunAt.value ? `Last run ${new Date(diagnostics.lastRunAt.value).toLocaleString()}` : 'Not run yet' }}</span>
          </div>

          <div class="feature-list">
            <article
              v-for="result in groupedResults"
              :key="result.id"
              class="feature-card"
              :class="statusClass(result.status)"
            >
              <div class="feature-card-header">
                <div>
                  <div class="feature-name-row">
                    <h3>{{ result.label }}</h3>
                    <span class="pill">{{ statusLabels[result.status] || result.status.toUpperCase() }}</span>
                  </div>
                  <p>{{ result.description }}</p>
                </div>
                <div class="feature-meta">
                  <span>{{ result.category }}</span>
                  <span>{{ formatDuration(result.durationMs) }}</span>
                </div>
              </div>

              <div class="feature-message">{{ result.message }}</div>

              <details v-if="result.details" class="feature-details">
                <summary>View details</summary>
                <pre>{{ JSON.stringify(result.details, null, 2) }}</pre>
              </details>
            </article>
          </div>
        </article>
      </div>

      <div class="right-column">
        <article class="panel card">
          <div class="panel-heading">
            <div>
              <h2>System snapshot</h2>
              <p>Useful context to copy into bug reports.</p>
            </div>
          </div>

          <dl class="system-list">
            <div v-for="[label, value] in systemFields" :key="label" class="system-row">
              <dt>{{ label }}</dt>
              <dd>{{ value || '—' }}</dd>
            </div>
          </dl>

          <div class="session-flags">
            <span class="flag" :class="hasSession ? 'flag-ready' : 'flag-muted'">{{ hasSession ? 'Session ready' : 'No live session' }}</span>
            <span class="flag" :class="diagnostics.running ? 'flag-running' : 'flag-muted'">{{ diagnostics.running ? 'Running checks' : 'Idle' }}</span>
          </div>
        </article>

        <article class="panel card">
          <div class="panel-heading">
            <div>
              <h2>Runtime errors</h2>
              <p>Window errors and rejected promises are collected here automatically.</p>
            </div>
          </div>

          <div v-if="recentLogs.length === 0" class="empty-state">
            No runtime errors captured yet.
          </div>

          <div v-else class="log-list">
            <article v-for="log in recentLogs" :key="log.id" class="log-entry" :class="`level-${log.level}`">
              <div class="log-topline">
                <strong>{{ log.source }}</strong>
                <span>{{ new Date(log.timestamp).toLocaleTimeString() }}</span>
              </div>
              <p>{{ log.message }}</p>
              <pre v-if="log.details">{{ JSON.stringify(log.details, null, 2) }}</pre>
            </article>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

<style scoped>
.remote-diagnostics-page {
  min-height: 100vh;
  padding: 24px;
  background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
  color: #0f172a;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.card {
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(148, 163, 184, 0.18);
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
}

.hero {
  border-radius: 24px;
  padding: 28px;
  display: flex;
  gap: 24px;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.hero-copy {
  max-width: 760px;
}

.badge {
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  background: #e0e7ff;
  color: #4338ca;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
}

.hero h1 {
  margin: 14px 0 10px;
  font-size: clamp(2rem, 4vw, 3.25rem);
  line-height: 1.05;
}

.hero p {
  margin: 0;
  color: #475569;
  line-height: 1.7;
}

.hero-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 180px;
}

.btn {
  border: 0;
  border-radius: 14px;
  padding: 12px 16px;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.btn:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn.primary {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  box-shadow: 0 10px 24px rgba(99, 102, 241, 0.28);
}

.btn.secondary {
  background: #0f172a;
  color: white;
}

.btn.ghost {
  background: #f8fafc;
  color: #334155;
  border: 1px solid #e2e8f0;
}

.quick-links {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 18px;
}

.quick-links a {
  color: #4338ca;
  text-decoration: none;
  font-weight: 700;
  background: #eef2ff;
  padding: 8px 12px;
  border-radius: 999px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 14px;
  margin-bottom: 20px;
}

.stat-card {
  border-radius: 20px;
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stat-label {
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #64748b;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 900;
  color: #0f172a;
}

.session-card .session-label {
  font-size: 1rem;
  line-height: 1.3;
}

.tone-indigo { background: #eef2ff; }
.tone-emerald { background: #ecfdf5; }
.tone-rose { background: #fff1f2; }
.tone-amber { background: #fffbeb; }
.tone-slate { background: #f8fafc; }

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.8fr) minmax(320px, 0.9fr);
  gap: 20px;
}

.panel {
  border-radius: 24px;
  padding: 22px;
}

.panel + .panel {
  margin-top: 20px;
}

.panel-heading {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
  margin-bottom: 18px;
}

.panel-heading h2 {
  margin: 0 0 6px;
  font-size: 1.15rem;
}

.panel-heading p {
  margin: 0;
  color: #64748b;
}

.panel-meta {
  font-size: 12px;
  color: #64748b;
  white-space: nowrap;
}

.feature-list {
  display: grid;
  gap: 14px;
}

.feature-card {
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 16px;
  background: #fff;
}

.feature-card-header {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
}

.feature-name-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.feature-name-row h3 {
  margin: 0;
  font-size: 1rem;
}

.feature-card p {
  margin: 6px 0 0;
  color: #64748b;
}

.feature-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  font-size: 12px;
  color: #64748b;
}

.pill {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.08em;
  background: #f8fafc;
  color: #334155;
}

.feature-message {
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 10px;
}

.feature-details summary {
  cursor: pointer;
  font-size: 13px;
  font-weight: 700;
  color: #4338ca;
}

.feature-details pre,
.log-entry pre {
  margin: 10px 0 0;
  overflow: auto;
  font-size: 12px;
  line-height: 1.5;
  background: #0f172a;
  color: #e2e8f0;
  border-radius: 14px;
  padding: 14px;
}

.status-passed {
  border-color: rgba(16, 185, 129, 0.35);
  box-shadow: inset 0 0 0 1px rgba(16, 185, 129, 0.08);
}

.status-failed {
  border-color: rgba(239, 68, 68, 0.35);
  box-shadow: inset 0 0 0 1px rgba(239, 68, 68, 0.08);
}

.status-skipped {
  border-color: rgba(245, 158, 11, 0.35);
  box-shadow: inset 0 0 0 1px rgba(245, 158, 11, 0.08);
}

.status-passed .pill {
  background: #dcfce7;
  color: #166534;
}

.status-failed .pill {
  background: #fee2e2;
  color: #991b1b;
}

.status-skipped .pill {
  background: #fef3c7;
  color: #92400e;
}

.left-column,
.right-column {
  display: flex;
  flex-direction: column;
}

.system-list {
  display: grid;
  gap: 10px;
  margin: 0;
}

.system-row {
  display: grid;
  grid-template-columns: 110px minmax(0, 1fr);
  gap: 12px;
  align-items: start;
}

.system-row dt {
  font-weight: 800;
  color: #475569;
}

.system-row dd {
  margin: 0;
  color: #0f172a;
  word-break: break-word;
}

.session-flags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 18px;
}

.flag {
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
}

.flag-ready {
  background: #dcfce7;
  color: #166534;
}

.flag-running {
  background: #e0e7ff;
  color: #4338ca;
}

.flag-muted {
  background: #f1f5f9;
  color: #475569;
}

.empty-state {
  border: 1px dashed #cbd5e1;
  border-radius: 18px;
  padding: 18px;
  color: #64748b;
  text-align: center;
}

.log-list {
  display: grid;
  gap: 12px;
}

.log-entry {
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  padding: 14px;
}

.log-entry.level-error {
  border-color: #fecaca;
  background: #fff1f2;
}

.log-entry.level-warn {
  border-color: #fde68a;
  background: #fffbeb;
}

.log-entry.level-info {
  border-color: #bfdbfe;
  background: #eff6ff;
}

.log-topline {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  font-size: 12px;
  color: #64748b;
}

.log-entry p {
  margin: 8px 0 0;
  font-weight: 700;
  color: #0f172a;
}

.status-passed,
.status-failed,
.status-skipped {
  background: #fff;
}

@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .remote-diagnostics-page {
    padding: 14px;
  }

  .hero {
    flex-direction: column;
  }

  .hero-actions {
    width: 100%;
    min-width: 0;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .feature-card-header,
  .panel-heading {
    flex-direction: column;
  }

  .feature-meta {
    align-items: flex-start;
  }

  .system-row {
    grid-template-columns: 1fr;
    gap: 4px;
  }
}
</style>
