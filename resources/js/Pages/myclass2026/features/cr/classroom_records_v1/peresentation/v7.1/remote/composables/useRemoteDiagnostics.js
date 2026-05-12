import { computed, onMounted, onUnmounted, ref } from 'vue';

function createSerializer(maxDepth = 4, maxEntries = 50) {
  const seen = new WeakSet();

  const serialize = (value, depth = 0) => {
    if (value === null || value === undefined) {
      return value;
    }

    const valueType = typeof value;

    if (valueType === 'string' || valueType === 'number' || valueType === 'boolean') {
      return value;
    }

    if (valueType === 'bigint') {
      return value.toString();
    }

    if (valueType === 'function') {
      return `[Function ${value.name || 'anonymous'}]`;
    }

    if (value instanceof Error) {
      return {
        name: value.name,
        message: value.message,
        stack: value.stack,
      };
    }

    if (value instanceof Date) {
      return value.toISOString();
    }

    if (valueType !== 'object') {
      return String(value);
    }

    if (seen.has(value)) {
      return '[Circular]';
    }

    if (depth >= maxDepth) {
      return '[MaxDepth]';
    }

    seen.add(value);

    if (Array.isArray(value)) {
      return value.slice(0, maxEntries).map((item) => serialize(item, depth + 1));
    }

    if (value instanceof Set) {
      return Array.from(value).slice(0, maxEntries).map((item) => serialize(item, depth + 1));
    }

    if (value instanceof Map) {
      return Array.from(value.entries())
        .slice(0, maxEntries)
        .map(([key, entryValue]) => [serialize(key, depth + 1), serialize(entryValue, depth + 1)]);
    }

    const output = {};
    for (const [key, entryValue] of Object.entries(value).slice(0, maxEntries)) {
      output[key] = serialize(entryValue, depth + 1);
    }

    return output;
  };

  return serialize;
}

function normalizeStatus(outcome) {
  if (outcome && typeof outcome === 'object' && typeof outcome.status === 'string') {
    return outcome.status;
  }

  if (outcome && typeof outcome === 'object' && typeof outcome.ok === 'boolean') {
    return outcome.ok ? 'passed' : 'failed';
  }

  if (outcome === false) {
    return 'failed';
  }

  return 'passed';
}

export function useRemoteDiagnostics(options = {}) {
  const tests = options.tests || [];
  const autoRun = options.autoRun ?? true;
  const maxLogEntries = options.maxLogEntries ?? 100;

  const results = ref([]);
  const logs = ref([]);
  const running = ref(false);
  const lastRunAt = ref(null);
  const serialize = createSerializer();
  const hasWindow = typeof window !== 'undefined';
  const hasNavigator = typeof navigator !== 'undefined';

  const systemInfo = computed(() => {
    if (!hasWindow) {
      return {};
    }

    return {
      url: window.location.href,
      path: window.location.pathname,
      origin: window.location.origin,
      userAgent: hasNavigator ? navigator.userAgent : 'unknown',
      language: hasNavigator ? navigator.language : 'unknown',
      platform: hasNavigator && navigator.platform ? navigator.platform : 'unknown',
      online: hasNavigator ? navigator.onLine : false,
      viewport: `${window.innerWidth} × ${window.innerHeight}`,
      timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
      clipboard: !!(hasNavigator && navigator.clipboard),
      fetch: typeof window.fetch === 'function',
      localStorage: typeof window.localStorage !== 'undefined',
      sessionStorage: typeof window.sessionStorage !== 'undefined',
      performance: typeof window.performance !== 'undefined',
    };
  });

  const summary = computed(() => {
    const tally = results.value.reduce(
      (acc, result) => {
        acc.total += 1;
        acc[result.status] += 1;
        return acc;
      },
      {
        total: 0,
        passed: 0,
        failed: 0,
        skipped: 0,
      },
    );

    tally.completed = tally.passed + tally.failed + tally.skipped;
    tally.successRate = tally.total ? Math.round((tally.passed / tally.total) * 100) : 0;

    return tally;
  });

  const report = computed(() => ({
    generatedAt: lastRunAt.value,
    summary: summary.value,
    systemInfo: systemInfo.value,
    results: results.value,
    logs: logs.value,
  }));

  function pushLog(entry) {
    const nextEntry = {
      id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
      timestamp: new Date().toISOString(),
      ...entry,
    };

    logs.value.unshift(nextEntry);
    if (logs.value.length > maxLogEntries) {
      logs.value.length = maxLogEntries;
    }

    return nextEntry;
  }

  function pushError(source, message, details = null) {
    return pushLog({
      level: 'error',
      source,
      message,
      details: serialize(details),
    });
  }

  function upsertResult(result) {
    const index = results.value.findIndex((entry) => entry.id === result.id);

    if (index === -1) {
      results.value.push(result);
      return;
    }

    results.value.splice(index, 1, result);
  }

  function normalizeResult(test, outcome, durationMs) {
    const status = normalizeStatus(outcome);
    const baseDetails = outcome && typeof outcome === 'object' && !(outcome instanceof Error)
      ? outcome.details ?? outcome.data ?? outcome.value ?? outcome
      : outcome;

    return {
      id: test.id,
      label: test.label,
      category: test.category || 'general',
      description: test.description || '',
      status,
      message: outcome && typeof outcome === 'object' && typeof outcome.message === 'string'
        ? outcome.message
        : status === 'passed'
          ? 'Passed'
          : status === 'skipped'
            ? 'Skipped'
            : 'Failed',
      durationMs,
      details: serialize(baseDetails),
      ranAt: new Date().toISOString(),
    };
  }

  async function runTest(test) {
    if (!test || typeof test.run !== 'function') {
      return null;
    }

    const startedAt = performance.now();

    try {
      const outcome = await test.run({
        systemInfo: systemInfo.value,
        serialize,
        log: pushLog,
        error: pushError,
      });
      const result = normalizeResult(test, outcome, Math.round(performance.now() - startedAt));
      upsertResult(result);
      return result;
    } catch (error) {
      const result = {
        id: test.id,
        label: test.label,
        category: test.category || 'general',
        description: test.description || '',
        status: 'failed',
        message: error?.message || 'Unexpected test failure',
        durationMs: Math.round(performance.now() - startedAt),
        details: serialize(error),
        ranAt: new Date().toISOString(),
      };

      upsertResult(result);
      pushError(`test:${test.id}`, result.message, result.details);
      return result;
    }
  }

  async function runAllTests() {
    running.value = true;
    lastRunAt.value = new Date().toISOString();

    try {
      for (const test of tests) {
        await runTest(test);
      }
    } finally {
      running.value = false;
    }

    return results.value;
  }

  function clearResults() {
    results.value = [];
  }

  function clearLogs() {
    logs.value = [];
  }

  async function copyReport() {
    if (!hasNavigator || !navigator.clipboard?.writeText) {
      throw new Error('Clipboard is not available in this browser context.');
    }

    await navigator.clipboard.writeText(JSON.stringify(report.value, null, 2));
  }

  function handleWindowError(event) {
    pushError('window.error', event?.message || 'Unhandled window error', {
      filename: event?.filename,
      lineno: event?.lineno,
      colno: event?.colno,
      error: event?.error,
    });
  }

  function handleUnhandledRejection(event) {
    pushError('window.unhandledrejection', 'Unhandled promise rejection', event?.reason);
  }

  onMounted(() => {
    if (hasWindow) {
      window.addEventListener('error', handleWindowError);
      window.addEventListener('unhandledrejection', handleUnhandledRejection);
    }

    if (autoRun) {
      void runAllTests();
    }
  });

  onUnmounted(() => {
    if (hasWindow) {
      window.removeEventListener('error', handleWindowError);
      window.removeEventListener('unhandledrejection', handleUnhandledRejection);
    }
  });

  return {
    results,
    logs,
    running,
    lastRunAt,
    systemInfo,
    summary,
    report,
    runTest,
    runAllTests,
    clearResults,
    clearLogs,
    copyReport,
    pushLog,
    pushError,
  };
}
