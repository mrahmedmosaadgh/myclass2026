<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import { toExport, toExportArray, toReadyToPrint, toQuizEngine, normalize, validateAll } from '../domains/questions/index.js'

const props = defineProps({
  questions: { type: Array, required: true }, // Array of v8 or legacy question objects
})

const emit = defineEmits(['close', 'imported'])

const $q = useQuasar()

const tab = ref('export') // 'export' | 'import'
const exportFormat = ref('v8') // 'v8' | 'readytoprint' | 'quizengine'
const importText = ref('')
const importError = ref('')

const exportFormats = [
  { label: 'V8 Canonical (Full)', value: 'v8' },
  { label: 'V8 Minimal (Clean)', value: 'v8_minimal' },
  { label: 'ReadyToPrint (Format C)', value: 'readytoprint' },
  { label: 'QuizEngine (Format A)', value: 'quizengine' },
]

const normalizedQuestions = computed(() => {
  return props.questions.map(q => {
    if (q.schema_version === 1 && q.content) return q
    try { return normalize(q) } catch { return null }
  }).filter(Boolean)
})

const exportJson = computed(() => {
  const qs = normalizedQuestions.value
  if (qs.length === 0) return '[]'

  let output
  switch (exportFormat.value) {
    case 'v8':
      output = toExportArray(qs, { pretty: true })
      break
    case 'v8_minimal':
      output = toExportArray(qs, { pretty: true, stripRuntime: true })
      break
    case 'readytoprint':
      output = JSON.stringify(qs.map(q => toReadyToPrint(q)).filter(Boolean), null, 2)
      break
    case 'quizengine':
      output = JSON.stringify(qs.map(q => toQuizEngine(q)).filter(Boolean), null, 2)
      break
    default:
      output = toExportArray(qs, { pretty: true })
  }
  return output
})

const validCount = computed(() => {
  if (normalizedQuestions.value.length === 0) return 0
  const result = validateAll(normalizedQuestions.value)
  return result.results.filter(r => r.valid).length
})

function copyExport() {
  navigator.clipboard.writeText(exportJson.value)
    .then(() => $q.notify({ type: 'positive', message: 'Questions copied to clipboard!', position: 'top', timeout: 3000 }))
    .catch(() => $q.notify({ type: 'negative', message: 'Failed to copy to clipboard', position: 'top', timeout: 3000 }))
}

function downloadExport() {
  const blob = new Blob([exportJson.value], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `questions-${exportFormat.value}-${new Date().toISOString().split('T')[0]}.json`
  a.click()
  URL.revokeObjectURL(url)
}

function parseImport() {
  importError.value = ''
  if (!importText.value.trim()) {
    importError.value = 'Paste JSON to import'
    return
  }

  try {
    let raw = importText.value.trim()
    if (raw.startsWith('```')) {
      raw = raw.replace(/^```[a-z]*\n/i, '').replace(/\n```$/i, '')
    }

    const data = JSON.parse(raw)
    const rawQuestions = Array.isArray(data) ? data : [data]

    const imported = rawQuestions.map((q, i) => {
      try {
        const normalized = normalize(q)
        if (normalized) return normalized
      } catch (e) {
        console.warn(`Failed to normalize question ${i}:`, e)
      }
      return null
    }).filter(Boolean)

    if (imported.length === 0) {
      importError.value = 'No valid questions found in the JSON'
      return
    }

    emit('imported', imported)
    $q.notify({ type: 'positive', message: `${imported.length} question(s) imported successfully!`, position: 'top', timeout: 3000 })
    importText.value = ''
  } catch (err) {
    importError.value = 'Invalid JSON: ' + err.message
  }
}

async function pasteImport() {
  try {
    const text = await navigator.clipboard.readText()
    if (text) {
      importText.value = text
    }
  } catch {
    $q.notify({ type: 'warning', message: 'Could not access clipboard. Please paste manually.', position: 'top', timeout: 3000 })
  }
}
</script>

<template>
  <div class="qeid-backdrop" @click.self="$emit('close')">
    <div class="qeid-modal">
      <!-- Header -->
      <div class="qeid-header">
        <h3 class="qeid-title">📋 Questions Export / Import</h3>
        <button class="qeid-close" @click="$emit('close')">✕</button>
      </div>

      <!-- Tabs -->
      <div class="qeid-tabs">
        <button
          class="qeid-tab"
          :class="{ active: tab === 'export' }"
          @click="tab = 'export'"
        >
          Export ({{ normalizedQuestions.length }})
        </button>
        <button
          class="qeid-tab"
          :class="{ active: tab === 'import' }"
          @click="tab = 'import'"
        >
          Import
        </button>
      </div>

      <!-- Export Tab -->
      <div v-if="tab === 'export'" class="qeid-body">
        <div v-if="normalizedQuestions.length === 0" class="qeid-empty">
          No questions available to export.
        </div>

        <template v-else>
          <div class="qeid-row">
            <label class="qeid-label">Format</label>
            <select v-model="exportFormat" class="qeid-select">
              <option v-for="fmt in exportFormats" :key="fmt.value" :value="fmt.value">
                {{ fmt.label }}
              </option>
            </select>
          </div>

          <div class="qeid-meta">
            <span class="qeid-badge">{{ normalizedQuestions.length }} questions</span>
            <span class="qeid-badge valid">{{ validCount }} valid</span>
            <span v-if="validCount < normalizedQuestions.length" class="qeid-badge warn">
              {{ normalizedQuestions.length - validCount }} issues
            </span>
          </div>

          <textarea
            class="qeid-json-display"
            readonly
            :value="exportJson"
            rows="12"
          />

          <div class="qeid-actions">
            <button class="qeid-btn secondary" @click="copyExport">
              📋 Copy
            </button>
            <button class="qeid-btn primary" @click="downloadExport">
              💾 Download
            </button>
          </div>
        </template>
      </div>

      <!-- Import Tab -->
      <div v-else class="qeid-body">
        <div class="qeid-hint">
          Paste JSON questions in any supported format (V8, AI minimal, legacy formats).
        </div>

        <textarea
          v-model="importText"
          class="qeid-json-input"
          rows="10"
          placeholder="Paste JSON array of questions here..."
        />

        <div v-if="importError" class="qeid-error">{{ importError }}</div>

        <div class="qeid-actions">
          <button class="qeid-btn secondary" @click="pasteImport">
            📋 Paste
          </button>
          <button class="qeid-btn primary" @click="parseImport">
            🔍 Import
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.qeid-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.qeid-modal {
  background: #1e1e1e;
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid #333;
}

.qeid-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  border-bottom: 1px solid #333;
}

.qeid-title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #f0f0f0;
}

.qeid-close {
  background: transparent;
  border: none;
  color: #888;
  font-size: 16px;
  cursor: pointer;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
}

.qeid-close:hover {
  background: #333;
  color: #f0f0f0;
}

.qeid-tabs {
  display: flex;
  border-bottom: 1px solid #333;
}

.qeid-tab {
  flex: 1;
  padding: 10px;
  background: transparent;
  border: none;
  color: #888;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.qeid-tab.active {
  color: #63b3ed;
  border-bottom: 2px solid #63b3ed;
  background: rgba(99, 179, 237, 0.05);
}

.qeid-tab:hover:not(.active) {
  color: #ccc;
  background: rgba(255, 255, 255, 0.03);
}

.qeid-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.qeid-empty {
  text-align: center;
  color: #666;
  padding: 32px;
  font-size: 14px;
}

.qeid-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.qeid-label {
  font-size: 13px;
  font-weight: 600;
  color: #aaa;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  white-space: nowrap;
}

.qeid-select {
  flex: 1;
  padding: 8px 10px;
  background: #2a2a2a;
  border: 1px solid #444;
  border-radius: 6px;
  color: #f0f0f0;
  font-size: 13px;
}

.qeid-meta {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.qeid-badge {
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  background: #2a2a2a;
  color: #888;
  border: 1px solid #383838;
}

.qeid-badge.valid {
  background: rgba(72, 187, 120, 0.1);
  color: #48bb78;
  border-color: rgba(72, 187, 120, 0.3);
}

.qeid-badge.warn {
  background: rgba(245, 101, 101, 0.1);
  color: #f56565;
  border-color: rgba(245, 101, 101, 0.3);
}

.qeid-json-display,
.qeid-json-input {
  width: 100%;
  flex: 1;
  min-height: 200px;
  padding: 10px 12px;
  background: #252525;
  border: 1px solid #383838;
  border-radius: 8px;
  color: #e0e0e0;
  font-size: 12px;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  line-height: 1.5;
  resize: vertical;
  box-sizing: border-box;
}

.qeid-json-display {
  resize: none;
}

.qeid-hint {
  font-size: 13px;
  color: #888;
  line-height: 1.4;
}

.qeid-error {
  padding: 8px 12px;
  background: rgba(245, 101, 101, 0.1);
  border: 1px solid rgba(245, 101, 101, 0.3);
  border-radius: 6px;
  color: #f56565;
  font-size: 13px;
}

.qeid-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 4px;
}

.qeid-btn {
  padding: 8px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s;
}

.qeid-btn.primary {
  background: #63b3ed;
  color: #1a1a1a;
}

.qeid-btn.primary:hover {
  background: #4fa3e0;
}

.qeid-btn.secondary {
  background: #2a2a2a;
  color: #ccc;
  border: 1px solid #444;
}

.qeid-btn.secondary:hover {
  background: #333;
  border-color: #555;
}
</style>
