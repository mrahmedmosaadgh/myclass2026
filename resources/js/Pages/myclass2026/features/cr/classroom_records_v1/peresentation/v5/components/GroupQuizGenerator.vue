<script setup>
import { ref } from 'vue';
import { useQuasar } from 'quasar';
import { useUIStore } from '../stores/uiStore';
import { useAIPaste } from '../composables/useAIPaste';
import { usePresentationStore } from '../stores/presentationStore';
import EditableMath from './EditableMath.vue';

const ui = useUIStore();
const $q = useQuasar();
const presentation = usePresentationStore();
const { generateQuestionElements } = useAIPaste();

const topic = ref('');
const qCount = ref(3);
const difficulty = ref('Medium');
const extraInfo = ref('');
const jsonInput = ref('');
const parsedQuestions = ref([]);
const errorMessage = ref('');

// Closes the modal and resets
function close() {
  ui.isGroupQuizGeneratorOpen = false;
  parsedQuestions.value = [];
  jsonInput.value = '';
  errorMessage.value = '';
}

function copyPrompt() {
  const prompt = `You are an expert teacher. Generate a JSON Array containing ${qCount.value} Multiple Choice questions about ${topic.value || 'General Knowledge'}.
Difficulty Level: ${difficulty.value}.
${extraInfo.value ? 'Additional Requirements: ' + extraInfo.value : ''}

Use Markdown bolding "**" or "###" if needed. If you write math formulas, strictly wrap them in "\\\\(" and "\\\\)".
IMPORTANT: Because this is JSON, YOU MUST DOUBLE-ESCAPE ALL LATEX BACKSLASHES! For example, write "\\\\frac{1}{2}" instead of "\\frac{1}{2}".
Return ONLY valid JSON format exactly like this:
[
  {
    "question": "What is \\\\( 5 + 5 \\\\)?",
    "options": ["\\\\( 7 \\\\)", "\\\\( 10 \\\\)", "\\\\( 12 \\\\)", "\\\\( 15 \\\\)"],
    "answer": "B) \\\\( 10 \\\\)"
  }
]`;
  
  navigator.clipboard.writeText(prompt);
  $q.notify({
    type: 'positive',
    message: 'Prompt copied successfully!',
    caption: 'Paste this into your AI assistant.',
    position: 'top'
  });
}

function parsePreview() {
  errorMessage.value = '';
  parsedQuestions.value = [];
  
  if (!jsonInput.value.trim()) {
      errorMessage.value = "Please paste JSON output to preview.";
      return;
  }

  try {
    let raw = jsonInput.value.trim();
    if (raw.startsWith('```')) {
      raw = raw.replace(/^```[a-z]*\n/i, '').replace(/\n```$/i, '');
    }

    let fixedRaw = "";
    for (let i = 0; i < raw.length; i++) {
        if (raw[i] === '\\') {
          if (i + 1 < raw.length) {
            const next = raw[i+1];
            if (['"', '\\', 'n', 't', 'r'].includes(next)) {
               fixedRaw += '\\' + next;
               i++;
            } else {
               fixedRaw += '\\\\';
            }
          } else {
            fixedRaw += '\\\\';
          }
        } else {
          fixedRaw += raw[i];
        }
    }

    const data = JSON.parse(fixedRaw);
    if (!Array.isArray(data)) {
       throw new Error("Invalid structure. Must be a JSON Array.");
    }
    parsedQuestions.value = data;
  } catch (err) {
    errorMessage.value = "Invalid JSON: " + err.message;
  }
}

function submitToPresentation() {
  if (parsedQuestions.value.length === 0) return;
  
  // Format v3 signals the generator to build GroupMCQ.vue blocks
  generateQuestionElements(parsedQuestions.value, 'new', 'v3');
  
  // Automatically append a Final Leaderboard Slide
  presentation.addSlide();
  const lbBlock = {
    id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
    type: 'leaderboard',
    x: 60,
    y: 50,
    width: 900,
    height: 650,
    zIndex: 1,
    visibilityOption: 'always-visible',
    isVisible: true,
  };
  presentation.addElement(lbBlock);

  close();
}
</script>

<template>
  <div v-if="ui.isGroupQuizGeneratorOpen" class="modal-backdrop" @click.self="close">
    <div class="modal-content">
      <div class="modal-header">
        <h2>🏆 Group Quiz Generator (V3)</h2>
        <button class="close-btn" @click="close">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>

      <div class="modal-body">
        <!-- Split View: Prompt Builder (Left) VS JSON Input & Preview (Right) -->
        <div class="generator-split">
          
          <!-- LEFT: Prompt Builder -->
          <div class="generator-col prompt-col">
            <h3>1. Setup AI Prompt</h3>
            
            <label class="input-block">
              <span>Lesson Topic</span>
              <input type="text" v-model="topic" placeholder="e.g. History, Advanced Calculus..." />
            </label>
            
            <div class="form-row">
              <label class="input-block">
                <span>Number of Questions</span>
                <input type="number" v-model="qCount" min="1" max="10" />
              </label>
              
              <label class="input-block">
                <span>Difficulty</span>
                <select v-model="difficulty">
                  <option>Easy</option>
                  <option>Medium</option>
                  <option>Hard</option>
                  <option>Expert</option>
                </select>
              </label>
            </div>

            <label class="input-block">
              <span>Additional Instructions (Optional)</span>
              <textarea v-model="extraInfo" placeholder="e.g. Focus purely on algebraic fractions." rows="2"></textarea>
            </label>

            <button class="btn-copy" @click="copyPrompt">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
              Generate & Copy Prompt
            </button>
            <p class="helper-text">Copy the prompt above and paste it into ChatGPT, Claude, etc.</p>
          </div>

          <!-- RIGHT: JSON Input & Preview -->
          <div class="generator-col action-col">
            <h3>2. Paste AI Output</h3>
            <textarea
              v-model="jsonInput" 
              placeholder="Paste the raw JSON Array back from the AI here..."
              class="json-entry-box"
            ></textarea>

            <button class="btn-preview" @click="parsePreview" :disabled="!jsonInput">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              Preview Questions
            </button>
            <div v-if="errorMessage" class="error-msg">{{ errorMessage }}</div>

            <!-- Previews -->
            <div v-if="parsedQuestions.length > 0" class="preview-area">
              <h4>Visual Preview ({{ parsedQuestions.length }} Questions)</h4>
              <div class="preview-cards">
                <div v-for="(q, index) in parsedQuestions" :key="index" class="q-card">
                  <div class="q-title">
                     <EditableMath :content="q.question" :isEditMode="false" />
                  </div>
                  <div v-if="q.options" class="q-options">
                    <div v-for="(opt, oIndex) in q.options" :key="oIndex" class="q-opt" :class="{'opt-correct': q.answer && (q.answer.includes(opt) || opt.includes(q.answer))}">
                      <EditableMath :content="opt" :isEditMode="false" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Final Submission -->
              <button class="btn-submit" @click="submitToPresentation">
                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                 Confirm & Add to Presentation
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  width: 900px;
  max-width: 95vw;
  height: 85vh;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 16px 20px;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fdfdfd;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #111827;
}

.close-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #6b7280;
  padding: 4px;
  border-radius: 6px;
}
.close-btn:hover { background: #f3f4f6; color: #111827; }

.modal-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex: 1;
  overflow: hidden; /* Child elements handle scrolling */
}

.generator-split {
  display: flex;
  gap: 20px;
  height: 100%;
}

.generator-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #f8fafc;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  overflow-y: auto;
}

.generator-col h3 {
  margin: 0 0 16px 0;
  font-size: 1.1rem;
  color: #334155;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 8px;
}

/* Form Styles */
.input-block {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 12px;
}
.input-block span {
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
}
.input-block input, .input-block select, .input-block textarea {
  padding: 8px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.95rem;
}
.input-block textarea {
  resize: vertical;
}

.form-row {
  display: flex;
  gap: 12px;
}
.form-row .input-block {
  flex: 1;
}

.btn-copy {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  background: #f59e0b;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-copy:hover { background: #d97706; }

.helper-text {
  font-size: 0.8rem;
  color: #64748b;
  text-align: center;
  margin-top: 8px;
}

/* Action Col (Paste & Preview) */
.json-entry-box {
  flex-shrink: 0;
  height: 120px;
  padding: 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-family: monospace;
  font-size: 0.85rem;
  resize: none;
  background: #ffffff;
  margin-bottom: 12px;
}

.btn-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px;
  background: #6366f1;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-preview:hover:not(:disabled) { background: #4f46e5; }
.btn-preview:disabled { background: #94a3b8; cursor: not-allowed; }

.error-msg {
  color: #dc2626;
  font-size: 0.85rem;
  margin-top: 8px;
  font-weight: 600;
  background: #fef2f2;
  padding: 8px;
  border-radius: 4px;
}

/* Preview Area */
.preview-area {
  margin-top: 20px;
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 200px;
}

.preview-area h4 {
  margin: 0 0 10px 0;
  color: #1e293b;
  font-size: 0.95rem;
}

.preview-cards {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding-right: 8px;
  margin-bottom: 12px;
}

.q-card {
  background: white;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 12px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.q-title {
  font-weight: bold;
  font-size: 1rem;
  margin-bottom: 8px;
  color: #0f172a;
}

.q-options {
  display: grid;
  grid-template-columns: 1fr;
  gap: 4px;
}

.q-opt {
  padding: 6px 10px;
  background: #f1f5f9;
  border: 1px dashed #cbd5e1;
  border-radius: 4px;
  font-size: 0.9rem;
}

.opt-correct {
  background: #dcfce3;
  border-color: #22c55e;
  border-style: solid;
}

.btn-submit {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  font-size: 1.05rem;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 6px -1px rgba(16,185,129,0.3);
}
.btn-submit:hover { background: #059669; transform: translateY(-1px); }
</style>
