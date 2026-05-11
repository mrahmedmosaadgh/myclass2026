<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import { usePresentationStore } from '../../stores/presentationStore.js'
import { fromAI } from '../../domains/questions/index.js'

const presentation = usePresentationStore()
const emit = defineEmits(['close'])

// ── Prompt Builder ─────────────────────────────────────
const topic = ref('')
const qCount = ref(3)
const difficulty = ref('Medium')
const extraInfo = ref('')

// ── JSON / Preview ─────────────────────────────────────
const jsonInput = ref('')
const parsedQuestions = ref([])
const parseError = ref('')
const editingIndex = ref(null)
const editingQuestion = ref(null)

const hasQuestions = computed(() => parsedQuestions.value.length > 0)

const $q = useQuasar()

const promptText = computed(() =>
  `Generate ${qCount.value} multiple_choice questions about: ${topic.value || 'General Knowledge'}
Difficulty: ${difficulty.value}
${extraInfo.value ? 'Notes: ' + extraInfo.value : ''}

Return ONLY valid JSON like this MINIMAL schema:
[
  {
    "type": "multiple_choice",
    "prompt": "Question text here",
    "options": [
      { "text": "Option A", "correct": false },
      { "text": "Option B", "correct": true, "rationale": "Why this is correct" }
    ],
    "explanation": "Why the answer is correct"
  }
]`
)

// ── Actions ────────────────────────────────────────────
async function copyPrompt() {
  try {
    await navigator.clipboard.writeText(promptText.value)
    $q.notify({ type: 'positive', message: 'Prompt copied to clipboard!', position: 'top', timeout: 3000 })
  } catch {
    $q.notify({ type: 'warning', message: 'Could not copy to clipboard', position: 'top', timeout: 3000 })
  }
}

async function pasteFromClipboard() {
  try {
    const text = await navigator.clipboard.readText()
    if (text) {
      jsonInput.value = text
      parseJson()
    }
  } catch {
    $q.notify({ type: 'warning', message: 'Could not access clipboard. Please paste manually.', position: 'top', timeout: 3000 })
  }
}

function parseJson() {
  parseError.value = ''
  parsedQuestions.value = []

  if (!jsonInput.value.trim()) {
    parseError.value = 'Paste JSON output from the AI'
    return
  }

  try {
    let raw = jsonInput.value.trim()
    // Strip markdown code fences
    if (raw.startsWith('```')) {
      raw = raw.replace(/^```[a-z]*\n/i, '').replace(/\n```$/i, '')
    }

    const data = JSON.parse(raw)
    if (!Array.isArray(data)) {
      throw new Error('Must be a JSON array')
    }

    parsedQuestions.value = data.map((q, i) => normalizeQuestion(q, i))
  } catch (err) {
    parseError.value = 'Invalid JSON: ' + err.message
  }
}

function normalizeQuestion(q, index) {
  // Try domain normalizer first (handles AI minimal and v8)
  try {
    const normalized = fromAI(q)
    if (normalized) {
      normalized.id = 'q_' + index + '_' + Date.now()
      return normalized
    }
  } catch (e) {
    console.warn('Domain normalizer failed, using manual fallback:', e)
  }

  // Manual fallback for legacy formats
  const opts = Array.isArray(q.options) ? q.options : []
  const labels = ['a', 'b', 'c', 'd', 'e', 'f']
  const mappedOptions = opts.slice(0, 6).map((item, i) => {
    if (typeof item === 'string') {
      return { id: labels[i], text: String(item), is_correct: false }
    }
    return {
      id: item.id || labels[i],
      text: String(item.text || item.label || ''),
      is_correct: Boolean(item.correct || item.isCorrect || item.is_correct),
      ...(item.rationale && { rationale: item.rationale }),
    }
  })

  // Detect correct answer from legacy "answer" field
  if (q.answer && mappedOptions.length > 0) {
    const ans = String(q.answer).trim().toUpperCase()
    const match = ans.match(/^([A-F])\)/)
    if (match) {
      const letter = match[1].toLowerCase()
      const found = mappedOptions.find(o => o.id === letter)
      if (found) found.is_correct = true
    } else {
      const ansText = String(q.answer).replace(/^[A-F]\)\s*/, '').trim()
      const found = mappedOptions.find(o => o.text === ansText || o.text.includes(ansText))
      if (found) found.is_correct = true
    }
  }

  // Ensure at least one correct
  if (!mappedOptions.some(o => o.is_correct) && mappedOptions.length > 0) {
    mappedOptions[0].is_correct = true
  }

  return {
    schema_version: 1,
    id: 'q_' + index + '_' + Date.now(),
    type: 'multiple_choice',
    marks: 1,
    content: {
      prompt: String(q.question || q.prompt || 'Untitled Question'),
      options: mappedOptions,
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

// ── Inline Editing ─────────────────────────────────────
function startEdit(index) {
  editingIndex.value = index
  editingQuestion.value = JSON.parse(JSON.stringify(parsedQuestions.value[index]))
}

function saveEdit() {
  if (editingIndex.value !== null && editingQuestion.value) {
    parsedQuestions.value[editingIndex.value] = editingQuestion.value
  }
  editingIndex.value = null
  editingQuestion.value = null
}

function cancelEdit() {
  editingIndex.value = null
  editingQuestion.value = null
}

function deleteQuestion(index) {
  parsedQuestions.value.splice(index, 1)
}

// ── Injection ────────────────────────────────────────
function injectQuestions() {
  if (!parsedQuestions.value.length) return

  // Add each question as a group-mcq element on a new slide
  parsedQuestions.value.forEach((q, i) => {
    if (i > 0) presentation.addSlide()

    const slide = presentation.currentSlide
    const startZ = (slide.elements?.length || 0) + 1

    const mcqElement = {
      id: 'el-gq-' + Date.now() + '-' + i,
      type: 'group-mcq',
      questionData: q, // v8 canonical question object
      x: 80,
      y: 40,
      width: 640,
      height: 520,
      zIndex: startZ,
      visibilityOption: 'always-visible',
      isVisible: true,
      backgroundColor: '#ffffff'
    }

    presentation.addElement(mcqElement)
  })

  // Append leaderboard slide
  appendLeaderboardSlide()

  emit('close')
}

function appendLeaderboardSlide() {
  presentation.addSlide()
  const slide = presentation.currentSlide

  // Title element
  presentation.addElement({
    id: 'el-lb-title-' + Date.now(),
    type: 'text',
    content: '<h2 style="margin:0;text-align:center;">🏆 Final Standings</h2>',
    x: 200,
    y: 20,
    width: 400,
    height: 50,
    zIndex: 1,
    visibilityOption: 'always-visible',
    isVisible: true,
    fontSize: 24
  })

  // Leaderboard chart element
  presentation.addElement({
    id: 'el-lb-chart-' + Date.now(),
    type: 'group-leaderboard',
    x: 60,
    y: 90,
    width: 680,
    height: 500,
    zIndex: 2,
    visibilityOption: 'always-visible',
    isVisible: true
  })
}

function injectEmptyQuestions() {
  for (let i = 0; i < qCount.value; i++) {
    if (i > 0) presentation.addSlide()

    const labels = ['a', 'b', 'c', 'd']
    const mcqElement = {
      id: 'el-empty-' + Date.now() + '-' + i,
      type: 'group-mcq',
      questionData: {
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
      },
      x: 80,
      y: 40,
      width: 640,
      height: 520,
      zIndex: 1,
      visibilityOption: 'always-visible',
      isVisible: true,
      backgroundColor: '#ffffff'
    }
    presentation.addElement(mcqElement)
  }

  appendLeaderboardSlide()
  emit('close')
}
</script>

<template>
  <div class="gqg-backdrop" @click.self="$emit('close')">
    <div class="gqg-modal">
      <!-- Header -->
      <div class="gqg-header">
        <h2>🎯 Group Quiz Generator</h2>
        <button class="gqg-close" @click="$emit('close')">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <div class="gqg-body">
        <!-- LEFT: Prompt Builder -->
        <div class="gqg-col prompt-col">
          <h3>1. Build AI Prompt</h3>

          <label class="gqg-field">
            <span>Topic</span>
            <input v-model="topic" placeholder="e.g. Photosynthesis, World War II..." />
          </label>

          <div class="gqg-row">
            <label class="gqg-field">
              <span>Questions</span>
              <input type="number" v-model.number="qCount" min="1" max="10" />
            </label>
            <label class="gqg-field">
              <span>Difficulty</span>
              <select v-model="difficulty">
                <option>Easy</option>
                <option>Medium</option>
                <option>Hard</option>
              </select>
            </label>
          </div>

          <label class="gqg-field">
            <span>Extra Requirements (optional)</span>
            <textarea v-model="extraInfo" rows="2" placeholder="e.g. Include diagrams, focus on formulas..." />
          </label>

          <div class="gqg-actions">
            <button class="gqg-btn primary" @click="copyPrompt">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
              </svg>
              Copy Prompt
            </button>
            <button class="gqg-btn secondary" @click="injectEmptyQuestions">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
              </svg>
              Empty Questions
            </button>
          </div>

          <div class="gqg-hint">
            Copy the prompt, paste it into ChatGPT/Claude, then paste the JSON output on the right.
          </div>
        </div>

        <!-- RIGHT: JSON Input + Preview -->
        <div class="gqg-col preview-col">
          <h3>2. Paste JSON & Preview</h3>

          <div class="gqg-json-bar">
            <textarea
              v-model="jsonInput"
              class="gqg-json-input"
              rows="4"
              placeholder="Paste AI JSON output here..."
              @input="parseError = ''"
            />
            <div class="gqg-json-actions">
              <button class="gqg-btn small" @click="pasteFromClipboard">📋 Paste</button>
              <button class="gqg-btn small primary" @click="parseJson">Preview</button>
            </div>
          </div>

          <div v-if="parseError" class="gqg-error">{{ parseError }}</div>

          <!-- Preview List -->
          <div v-if="hasQuestions" class="gqg-preview-list">
            <div
              v-for="(q, idx) in parsedQuestions"
              :key="q.id"
              class="gqg-preview-card"
            >
              <!-- View Mode -->
              <div v-if="editingIndex !== idx" class="gqg-preview-view">
                <div class="gqg-preview-question">{{ idx + 1 }}. {{ q.content?.prompt || q.question }}</div>
                <div class="gqg-preview-options">
                  <span
                    v-for="opt in (q.content?.options || q.options || [])"
                    :key="opt.id"
                    class="gqg-preview-opt"
                    :class="{ correct: opt.is_correct || opt.id === q.correctId }"
                  >
                    {{ String(opt.id).toUpperCase() }}) {{ opt.text }}
                  </span>
                </div>
                <div class="gqg-preview-actions">
                  <button class="gqg-icon-btn" @click="startEdit(idx)" title="Edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                  </button>
                  <button class="gqg-icon-btn danger" @click="deleteQuestion(idx)" title="Delete">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Edit Mode -->
              <div v-else class="gqg-preview-edit">
                <label class="gqg-field">
                  <span>Question</span>
                  <textarea v-model="editingQuestion.content.prompt" rows="2" />
                </label>
                <div class="gqg-edit-options">
                  <label
                    v-for="(opt, oi) in editingQuestion.content.options"
                    :key="opt.id"
                    class="gqg-field"
                  >
                    <span>Option {{ String(opt.id).toUpperCase() }}</span>
                    <input v-model="editingQuestion.content.options[oi].text" />
                    <label class="gqg-checkbox">
                      <input
                        type="checkbox"
                        v-model="editingQuestion.content.options[oi].is_correct"
                      />
                      Correct
                    </label>
                  </label>
                </div>
                <label class="gqg-field">
                  <span>Explanation</span>
                  <textarea v-model="editingQuestion.content.explanation" rows="2" />
                </label>
                <div class="gqg-edit-actions">
                  <button class="gqg-btn primary small" @click="saveEdit">Save</button>
                  <button class="gqg-btn ghost small" @click="cancelEdit">Cancel</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Inject Button -->
          <div v-if="hasQuestions" class="gqg-inject-bar">
            <span class="gqg-inject-count">{{ parsedQuestions.length }} questions ready</span>
            <button class="gqg-btn primary" @click="injectQuestions">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
              </svg>
              Inject to Presentation
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.gqg-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.gqg-modal {
  background: #ffffff;
  border-radius: 12px;
  width: 100%;
  max-width: 960px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.gqg-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
}

.gqg-header h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #111827;
}

.gqg-close {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
}

.gqg-close:hover {
  background: #f3f4f6;
  color: #374151;
}

.gqg-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
  overflow: hidden;
  flex: 1;
}

.gqg-col {
  padding: 20px;
  overflow-y: auto;
}

.gqg-col + .gqg-col {
  border-left: 1px solid #e5e7eb;
}

.gqg-col h3 {
  margin: 0 0 16px 0;
  font-size: 15px;
  font-weight: 600;
  color: #374151;
}

/* Field */
.gqg-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 14px;
}

.gqg-field span {
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
}

.gqg-field input,
.gqg-field select,
.gqg-field textarea {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  color: #111827;
  background: #ffffff;
  outline: none;
  font-family: inherit;
}

.gqg-field input:focus,
.gqg-field select:focus,
.gqg-field textarea:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.gqg-field textarea {
  resize: vertical;
  min-height: 60px;
}

.gqg-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.gqg-row .gqg-field {
  margin-bottom: 0;
}

/* Buttons */
.gqg-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s;
  background: transparent;
}

.gqg-btn.primary {
  background: #6366f1;
  color: white;
  border-color: #6366f1;
}

.gqg-btn.primary:hover {
  background: #4f46e5;
}

.gqg-btn.secondary {
  background: #f9fafb;
  color: #374151;
  border-color: #d1d5db;
}

.gqg-btn.secondary:hover {
  background: #f3f4f6;
}

.gqg-btn.ghost {
  background: transparent;
  color: #6b7280;
  border-color: #e5e7eb;
}

.gqg-btn.ghost:hover {
  background: #f9fafb;
}

.gqg-btn.small {
  padding: 6px 12px;
  font-size: 13px;
}

.gqg-actions {
  display: flex;
  gap: 10px;
  margin-top: 4px;
}

.gqg-hint {
  margin-top: 12px;
  font-size: 12px;
  color: #9ca3af;
  line-height: 1.5;
}

/* JSON Input */
.gqg-json-bar {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 12px;
}

.gqg-json-input {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 13px;
  font-family: ui-monospace, monospace;
  line-height: 1.5;
  resize: vertical;
  min-height: 80px;
  outline: none;
}

.gqg-json-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.gqg-json-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.gqg-error {
  padding: 8px 12px;
  background: #fef2f2;
  color: #b91c1c;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 12px;
}

/* Preview Cards */
.gqg-preview-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 340px;
  overflow-y: auto;
  padding-right: 4px;
}

.gqg-preview-card {
  background: #f9fafb;
  border-radius: 8px;
  padding: 12px;
  border: 1px solid #e5e7eb;
}

.gqg-preview-question {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 8px;
  line-height: 1.4;
}

.gqg-preview-options {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 10px;
}

.gqg-preview-opt {
  font-size: 13px;
  padding: 4px 10px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  color: #374151;
}

.gqg-preview-opt.correct {
  background: #ecfdf5;
  border-color: #a7f3d0;
  color: #047857;
  font-weight: 600;
}

.gqg-preview-actions {
  display: flex;
  gap: 4px;
  justify-content: flex-end;
}

.gqg-icon-btn {
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

.gqg-icon-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.gqg-icon-btn.danger:hover {
  background: #fef2f2;
  color: #ef4444;
}

/* Edit Mode */
.gqg-preview-edit {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.gqg-edit-options {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.gqg-edit-options .gqg-field {
  margin-bottom: 0;
}

.gqg-edit-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  margin-top: 4px;
}

/* Inject Bar */
.gqg-inject-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 14px;
  margin-top: 14px;
  border-top: 1px solid #e5e7eb;
}

.gqg-inject-count {
  font-size: 13px;
  color: #6b7280;
  font-weight: 500;
}

/* Scrollbar */
.gqg-preview-list::-webkit-scrollbar,
.gqg-col::-webkit-scrollbar {
  width: 6px;
}

.gqg-preview-list::-webkit-scrollbar-thumb,
.gqg-col::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

/* Mobile */
@media (max-width: 768px) {
  .gqg-body {
    grid-template-columns: 1fr;
  }
  .gqg-col + .gqg-col {
    border-left: none;
    border-top: 1px solid #e5e7eb;
  }
}
</style>
