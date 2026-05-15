<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import { usePresentationStore } from '../../stores/presentationStore.js'
import { useMathRenderer } from '../../composables/useMathRenderer.js'
import { fromAI } from '../../domains/questions/index.js'

const emit = defineEmits(['close'])

const $q = useQuasar()
const presentation = usePresentationStore()
const { renderMath } = useMathRenderer()

// ── Form state ──────────────────────────────────────────
const topic = ref('')
const qCount = ref(5)
const difficulty = ref('Medium')
const extraInfo = ref('')
const jsonInput = ref('')
const parsedQuestions = ref([])
const errorMessage = ref('')
const showPreview = ref(false)
const editingIndex = ref(-1)

// Timer settings
const timerEnabled = ref(false)
const timerMode = ref('per-question')
const timerSeconds = ref(30)

// Auto-submit setting
const autoSubmitOnAnswer = ref(true)

const difficultyOptions = ['Easy', 'Medium', 'Hard', 'Mixed']
const timerModeOptions = ['per-question', 'total-quiz']

// ── Computed ────────────────────────────────────────────
const canInject = computed(() => parsedQuestions.value.length > 0)
const hasErrors = computed(() => !!errorMessage.value)

// ── Prompt ──────────────────────────────────────────────
function buildPrompt() {
  const diffMap = { Easy: 1, Medium: 2, Hard: 3, Mixed: 2 }
  const diffLevel = diffMap[difficulty.value] || 2

  return `You are an expert teacher. Generate a JSON Array containing ${qCount.value} multiple_choice questions about ${topic.value || 'General Knowledge'}.
Difficulty Level: ${difficulty.value} (1=Easy, 3=Hard).
${extraInfo.value ? 'Additional Requirements: ' + extraInfo.value : ''}

Use Markdown bolding "**" if needed. If you write math formulas, strictly wrap them in "\\(" and "\\)".
IMPORTANT: Because this is JSON, YOU MUST DOUBLE-ESCAPE ALL LATEX BACKSLASHES! For example, write "\\\\frac{1}{2}" instead of "\\frac{1}{2}".

Return ONLY valid JSON format exactly like this MINIMAL schema (the app will enrich it):
[
  {
    "type": "multiple_choice",
    "prompt": "What is \\\( 5 + 5 \\\) ?",
    "options": [
      { "text": "\\( 7 \\)", "correct": false, "rationale": "Too low" },
      { "text": "\\( 10 \\)", "correct": true, "rationale": "Correct! 5 + 5 = 10" },
      { "text": "\\( 12 \\)", "correct": false, "rationale": "Too high" },
      { "text": "\\( 15 \\)", "correct": false, "rationale": "Way too high" }
    ],
    "explanation": "Basic addition: five plus five equals ten.",
    "hints": ["Count on your fingers"]
  }
]`
}

function copyPrompt() {
  navigator.clipboard.writeText(buildPrompt())
    .then(() => $q.notify({ type: 'positive', message: 'Prompt copied to clipboard! Paste into ChatGPT/Claude.', position: 'top', timeout: 3000 }))
    .catch(() => {
      // Fallback
      const ta = document.createElement('textarea')
      ta.value = buildPrompt()
      document.body.appendChild(ta)
      ta.select()
      document.execCommand('copy')
      document.body.removeChild(ta)
      $q.notify({ type: 'positive', message: 'Prompt copied!', position: 'top', timeout: 3000 })
    })
}

async function pasteFromClipboard() {
  try {
    const text = await navigator.clipboard.readText()
    if (text) {
      jsonInput.value = text
      parsePreview()
    }
  } catch {
    $q.notify({ type: 'warning', message: 'Could not access clipboard. Please paste manually.', position: 'top', timeout: 3000 })
  }
}

// ── JSON Parsing ────────────────────────────────────────
function parsePreview() {
  errorMessage.value = ''
  parsedQuestions.value = []
  showPreview.value = false

  if (!jsonInput.value.trim()) {
    errorMessage.value = 'Please paste JSON output to preview.'
    return
  }

  try {
    let raw = jsonInput.value.trim()
    // Strip markdown code fences
    if (raw.startsWith('```')) {
      raw = raw.replace(/^```[a-z]*\n/i, '').replace(/\n```$/i, '')
    }

    // Fix single backslashes in JSON (common AI mistake)
    let fixedRaw = ''
    for (let i = 0; i < raw.length; i++) {
      if (raw[i] === '\\') {
        if (i + 1 < raw.length && ['"', '\\', 'n', 't', 'r'].includes(raw[i + 1])) {
          fixedRaw += '\\' + raw[i + 1]
          i++
        } else {
          fixedRaw += '\\\\'
        }
      } else {
        fixedRaw += raw[i]
      }
    }

    const data = JSON.parse(fixedRaw)
    if (!Array.isArray(data)) {
      throw new Error('JSON must be an array of questions')
    }

    parsedQuestions.value = data.map((q, idx) => normalizeQuestion(q, idx))
    showPreview.value = true
  } catch (err) {
    errorMessage.value = 'Invalid JSON: ' + err.message
  }
}

function normalizeQuestion(q, idx) {
  // Try to normalize using the domain layer (handles both v8 and AI minimal formats)
  try {
    const normalized = fromAI(q)
    if (normalized) {
      normalized.id = 'q_' + idx + '_' + Date.now()
      return normalized
    }
  } catch (e) {
    console.warn('Domain normalizer failed, falling back to manual:', e)
  }

  // Fallback manual normalization for legacy formats
  const options = []
  const optLabels = ['a', 'b', 'c', 'd', 'e', 'f']

  if (Array.isArray(q.options)) {
    q.options.forEach((opt, i) => {
      if (typeof opt === 'string') {
        options.push({ id: optLabels[i] || 'x', text: opt, is_correct: false })
      } else if (opt && typeof opt === 'object') {
        options.push({
          id: opt.id || optLabels[i] || 'x',
          text: opt.text || opt.label || String(opt),
          is_correct: Boolean(opt.correct || opt.isCorrect || opt.is_correct),
          ...(opt.rationale && { rationale: opt.rationale }),
        })
      }
    })
  }

  // Detect correct answer from legacy "answer" field
  if (q.answer && options.length > 0) {
    const ans = String(q.answer).trim().toUpperCase()
    const match = ans.match(/^([A-F])\)/)
    if (match) {
      const letter = match[1].toLowerCase()
      const found = options.find(o => o.id === letter)
      if (found) found.is_correct = true
    } else {
      const ansText = String(q.answer).replace(/^[A-F]\)\s*/, '').trim()
      const found = options.find(o => o.text === ansText || o.text.includes(ansText))
      if (found) found.is_correct = true
    }
  }

  // Ensure at least one correct answer
  if (!options.some(o => o.is_correct) && options.length > 0) {
    options[0].is_correct = true
  }

  return {
    schema_version: 1,
    id: 'q_' + idx + '_' + Date.now(),
    type: 'multiple_choice',
    marks: 1,
    content: {
      prompt: q.question || q.prompt || 'Question ' + (idx + 1),
      options: options.length > 0 ? options : [
        { id: 'a', text: 'Option A', is_correct: true },
        { id: 'b', text: 'Option B', is_correct: false },
        { id: 'c', text: 'Option C', is_correct: false },
        { id: 'd', text: 'Option D', is_correct: false },
      ],
      explanation: q.explanation || '',
      hints: q.hints || [],
    },
    meta: {
      difficulty: 2,
      bloom_level: 1,
      estimated_time_sec: 60,
      source: 'ai',
      tags: [],
      cognitive_demand: 'recall',
      assessment_mode: 'traditional',
    },
    evaluation: { mode: 'auto' },
  }
}

// ── Preview editing ─────────────────────────────────────
function startEdit(idx) {
  editingIndex.value = idx
}

function saveEdit(idx, field, value) {
  parsedQuestions.value[idx][field] = value
  editingIndex.value = -1
}

function deleteQuestion(idx) {
  parsedQuestions.value.splice(idx, 1)
  if (parsedQuestions.value.length === 0) {
    showPreview.value = false
  }
}

function addOption(qIdx) {
  const labels = ['a', 'b', 'c', 'd', 'e', 'f']
  const q = parsedQuestions.value[qIdx]
  if (q.content.options.length < 6) {
    q.content.options.push({
      id: labels[q.content.options.length] || 'x',
      text: 'New Option',
      is_correct: false,
    })
  }
}

function removeOption(qIdx, optIdx) {
  const q = parsedQuestions.value[qIdx]
  if (q.content.options.length > 2) {
    const removed = q.content.options.splice(optIdx, 1)[0]
    // If we removed the correct answer, set first as correct
    if (removed?.is_correct && !q.content.options.some(o => o.is_correct)) {
      q.content.options[0].is_correct = true
    }
  }
}

function setCorrect(qIdx, optId) {
  const q = parsedQuestions.value[qIdx]
  q.content.options.forEach(opt => {
    opt.is_correct = (opt.id === optId)
  })
}

// v8 compatibility helpers for template
function getQuestionPrompt(q) {
  return q.content?.prompt || q.question || ''
}

function getQuestionOptions(q) {
  return q.content?.options || q.options || []
}

function getCorrectOptionId(q) {
  return getQuestionOptions(q).find(opt => opt.is_correct)?.id || null
}

// ── Inject ──────────────────────────────────────────────
function injectQuiz() {
  if (!canInject.value) return

  // Normalize all questions to v8 before injecting
  const v8Questions = parsedQuestions.value.map((q, idx) => {
    if (q.schema_version === 1 && q.content) {
      return q // Already v8
    }
    return normalizeQuestion(q, idx)
  })

  const quizElement = {
    id: 'quiz-v2-' + Date.now(),
    type: 'quiz-v2',
    title: topic.value || 'Quiz',
    questions: v8Questions,
    settings: {
      pointsPerCorrect: 10,
      penaltyPerWrong: 0,
      showExplanation: true,
      timerEnabled: timerEnabled.value,
      timerSeconds: timerSeconds.value,
      timerMode: timerMode.value,
      autoAdvance: true,
      autoAdvanceDelay: 1200,
      autoSubmitOnAnswer: autoSubmitOnAnswer.value
    },
    currentQuestionIndex: 0,
    userAnswers: {},
    showResults: false,
    x: 60,
    y: 40,
    width: 680,
    height: 520,
    zIndex: 1,
    visibilityOption: 'always-visible',
    isVisible: true
  }

  presentation.addElement(quizElement)
  emit('close')
}

function createEmptyQuestions() {
  const empty = []
  const labels = ['a', 'b', 'c', 'd']
  for (let i = 0; i < qCount.value; i++) {
    empty.push({
      schema_version: 1,
      id: 'q_' + i + '_' + Date.now(),
      type: 'multiple_choice',
      marks: 1,
      content: {
        prompt: `Question ${i + 1}`,
        options: labels.map((l, j) => ({
          id: l,
          text: `Option ${l.toUpperCase()}`,
          is_correct: j === 0,
        })),
        explanation: '',
        hints: [],
      },
      meta: {
        difficulty: 2,
        bloom_level: 1,
        estimated_time_sec: 60,
        source: 'teacher',
        tags: [],
        cognitive_demand: 'recall',
        assessment_mode: 'traditional',
      },
      evaluation: { mode: 'auto' },
    })
  }
  parsedQuestions.value = empty
  showPreview.value = true
}

function cancel() {
  emit('close')
}
</script>

<template>
  <div class="modal-overlay" @click.self="cancel">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header">
        <h2 class="modal-title">🤖 AI Quiz Generator</h2>
        <button class="modal-close" @click="cancel" title="Close">✕</button>
      </div>

      <!-- Form -->
      <div class="modal-body">
        <div class="form-row">
          <label class="form-label">Topic</label>
          <input
            v-model="topic"
            class="form-input"
            placeholder="e.g., Science, Math, History..."
          />
        </div>

        <div class="form-row grid-2">
          <div>
            <label class="form-label">Questions</label>
            <input v-model.number="qCount" type="number" min="1" max="50" class="form-input" />
          </div>
          <div>
            <label class="form-label">Difficulty</label>
            <select v-model="difficulty" class="form-select">
              <option v-for="opt in difficultyOptions" :key="opt">{{ opt }}</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <label class="form-label">Extra Instructions (optional)</label>
          <textarea
            v-model="extraInfo"
            class="form-textarea"
            rows="2"
            placeholder="e.g., Focus on fractions, avoid negative numbers..."
          />
        </div>

        <!-- Timer Settings -->
        <div class="form-row">
          <label class="form-label">⏱️ Timer Settings</label>
          <div class="timer-settings">
            <label class="checkbox-label">
              <input type="checkbox" v-model="timerEnabled" />
              <span>Enable Timer</span>
            </label>
            <div v-if="timerEnabled" class="timer-options">
              <div class="timer-option">
                <label class="timer-option-label">Mode:</label>
                <select v-model="timerMode" class="form-select">
                  <option v-for="mode in timerModeOptions" :key="mode" :value="mode">
                    {{ mode === 'per-question' ? 'Per Question' : 'Total Quiz' }}
                  </option>
                </select>
              </div>
              <div class="timer-option">
                <label class="timer-option-label">Duration (seconds):</label>
                <input v-model.number="timerSeconds" type="number" min="5" max="600" class="form-input" />
              </div>
            </div>
          </div>
        </div>

        <!-- Auto-Submit Settings -->
        <div class="form-row">
          <label class="form-label">⚡ Quiz Behavior</label>
          <div class="timer-settings">
            <label class="checkbox-label">
              <input type="checkbox" v-model="autoSubmitOnAnswer" />
              <span>Auto-advance to next question after answering</span>
            </label>
            <p class="setting-hint">
              When disabled, students see feedback and must click "Next →" to advance manually.
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-actions">
          <button class="btn btn-secondary" @click="copyPrompt">
            📋 Copy Prompt
          </button>
          <button class="btn btn-secondary" @click="pasteFromClipboard">
            📋 Paste
          </button>
        </div>

        <!-- JSON Input -->
        <div class="form-row">
          <label class="form-label">JSON Output</label>
          <textarea
            v-model="jsonInput"
            class="form-textarea json-area"
            rows="6"
            placeholder="Paste ChatGPT/Claude JSON output here..."
          />
        </div>

        <div class="form-actions">
          <button class="btn btn-primary" @click="parsePreview">
            🔍 Preview
          </button>
          <button class="btn btn-secondary" @click="createEmptyQuestions">
            📝 Empty Questions
          </button>
        </div>

        <!-- Error -->
        <div v-if="errorMessage" class="form-error">
          {{ errorMessage }}
        </div>

        <!-- Preview -->
        <div v-if="showPreview && parsedQuestions.length > 0" class="preview-section">
          <div class="preview-header">
            <h3 class="preview-title">✓ Preview ({{ parsedQuestions.length }} questions)</h3>
          </div>

          <div class="preview-list">
            <div
              v-for="(q, qIdx) in parsedQuestions"
              :key="q.id"
              class="preview-card"
            >
              <!-- Question -->
              <div class="preview-question-row">
                <span class="preview-num">{{ qIdx + 1 }}.</span>
                <div class="preview-question-content">
                  <input
                    v-if="editingIndex === qIdx && editingField === 'question'"
                    v-model="q.content.prompt"
                    class="preview-edit-input"
                    @blur="editingIndex = -1"
                    @keyup.enter="editingIndex = -1"
                  />
                  <span
                    v-else
                    class="preview-question-text"
                    v-html="renderMath(getQuestionPrompt(q))"
                    @click="editingIndex = qIdx; editingField = 'question'"
                    title="Click to edit"
                  />
                </div>
                <button class="preview-delete" @click="deleteQuestion(qIdx)" title="Delete">🗑</button>
              </div>

              <!-- Options -->
              <div class="preview-options">
                <div
                  v-for="(opt, optIdx) in getQuestionOptions(q)"
                  :key="opt.id"
                  class="preview-option"
                  :class="{ 'is-correct': opt.is_correct }"
                  @click="setCorrect(qIdx, opt.id)"
                >
                  <span class="preview-opt-label">{{ String(opt.id).toUpperCase() }}</span>
                  <span class="preview-opt-text" v-html="renderMath(opt.text)" />
                  <span v-if="opt.is_correct" class="preview-opt-check">✓</span>
                </div>
              </div>

              <div class="preview-meta">
                <span class="preview-correct-label">Correct: {{ String(getCorrectOptionId(q) || '-').toUpperCase() }}</span>
                <span class="preview-hint">Click option to set correct answer</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="cancel">Cancel</button>
        <button
          class="btn btn-primary"
          :disabled="!canInject"
          @click="injectQuiz"
        >
          🚀 Inject Quiz ({{ parsedQuestions.length }} Qs)
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.modal-content {
  background: #1e1e1e;
  border-radius: 12px;
  width: 100%;
  max-width: 700px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid #333;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #333;
  flex-shrink: 0;
}

.modal-title {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #f0f0f0;
}

.modal-close {
  background: transparent;
  border: none;
  color: #888;
  font-size: 18px;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.modal-close:hover {
  background: #333;
  color: #f0f0f0;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 14px 20px;
  border-top: 1px solid #333;
  flex-shrink: 0;
}

/* Form elements */
.form-row {
  margin-bottom: 14px;
}

.form-row.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #aaa;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 10px 12px;
  background: #2a2a2a;
  border: 1px solid #444;
  border-radius: 8px;
  color: #f0f0f0;
  font-size: 14px;
  box-sizing: border-box;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #63b3ed;
}

.form-textarea {
  resize: vertical;
  font-family: ui-monospace, monospace;
}

.form-textarea.json-area {
  font-size: 12px;
  line-height: 1.5;
}

.form-actions {
  display: flex;
  gap: 8px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.form-error {
  padding: 10px 14px;
  background: rgba(245, 101, 101, 0.1);
  border: 1px solid rgba(245, 101, 101, 0.3);
  border-radius: 8px;
  color: #f56565;
  font-size: 13px;
  margin-bottom: 14px;
}

/* Timer Settings */
.timer-settings {
  background: #252525;
  border: 1px solid #383838;
  border-radius: 8px;
  padding: 12px 14px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #e0e0e0;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.timer-options {
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.timer-option {
  display: flex;
  align-items: center;
  gap: 10px;
}

.timer-option-label {
  font-size: 13px;
  color: #aaa;
  white-space: nowrap;
  min-width: 120px;
}

.timer-option .form-select,
.timer-option .form-input {
  flex: 1;
}

/* Buttons */
.btn {
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-primary {
  background: #63b3ed;
  color: #1a1a1a;
}

.btn-primary:hover:not(:disabled) {
  background: #4fa3e0;
}

.btn-primary:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn-secondary {
  background: #2a2a2a;
  color: #ccc;
  border: 1px solid #444;
}

.btn-secondary:hover {
  background: #333;
  border-color: #555;
}

/* Preview section */
.preview-section {
  margin-top: 16px;
  border-top: 1px solid #333;
  padding-top: 16px;
}

.preview-header {
  margin-bottom: 12px;
}

.preview-title {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #48bb78;
}

.preview-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 400px;
  overflow-y: auto;
}

.preview-card {
  background: #252525;
  border: 1px solid #383838;
  border-radius: 8px;
  padding: 12px 14px;
}

.preview-question-row {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 8px;
}

.preview-num {
  font-size: 14px;
  font-weight: 700;
  color: #63b3ed;
  flex-shrink: 0;
}

.preview-question-content {
  flex: 1;
  min-width: 0;
}

.preview-question-text {
  font-size: 14px;
  color: #e0e0e0;
  line-height: 1.4;
  cursor: text;
  word-break: break-word;
}

.preview-question-text:hover {
  color: #fff;
}

.preview-edit-input {
  width: 100%;
  padding: 6px 8px;
  background: #2a2a2a;
  border: 1px solid #63b3ed;
  border-radius: 6px;
  color: #f0f0f0;
  font-size: 14px;
}

.preview-delete {
  background: transparent;
  border: none;
  color: #888;
  cursor: pointer;
  font-size: 14px;
  padding: 2px 6px;
  border-radius: 4px;
  flex-shrink: 0;
}

.preview-delete:hover {
  background: rgba(245, 101, 101, 0.15);
  color: #f56565;
}

.preview-options {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 8px;
}

.preview-option {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  background: #2a2a2a;
  border: 1px solid #444;
  border-radius: 6px;
  font-size: 13px;
  color: #ccc;
  cursor: pointer;
  transition: all 0.15s;
}

.preview-option:hover {
  border-color: #63b3ed;
}

.preview-option.is-correct {
  border-color: #48bb78;
  background: rgba(72, 187, 120, 0.1);
  color: #48bb78;
}

.preview-opt-label {
  font-weight: 700;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #383838;
  border-radius: 4px;
  font-size: 11px;
}

.preview-opt-check {
  font-size: 12px;
}

.preview-meta {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #666;
}

.preview-hint {
  font-style: italic;
}

/* Math rendering in preview */
.preview-question-text :deep(sup),
.preview-opt-text :deep(sup) {
  font-size: 0.75em;
  vertical-align: super;
}

.preview-question-text :deep(sub),
.preview-opt-text :deep(sub) {
  font-size: 0.75em;
  vertical-align: sub;
}

@media (max-width: 640px) {
  .form-row.grid-2 {
    grid-template-columns: 1fr;
  }
  .modal-content {
    max-height: 95vh;
    border-radius: 8px;
  }
}
</style>
