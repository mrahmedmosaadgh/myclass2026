<script setup>
import { ref } from 'vue';
import { useQuasar } from 'quasar';
import { useUIStore } from '../stores/uiStore';
import { useGameStore } from '../stores/gameStore';
import { useAIPaste } from '../composables/useAIPaste';
import { usePresentationStore } from '../stores/presentationStore';
import { saveGroupQuizSession } from '../quizgroupv1/ver1/composables/useGroupQuizPlayerSession';
import EditableMath from './EditableMath.vue';

const ui = useUIStore();
const gameStore = useGameStore();
const $q = useQuasar();
const presentation = usePresentationStore();
const { generateQuestionElements } = useAIPaste();

const topic = ref(presentation.title || '');
const qCount = ref(3);
const difficulty = ref('Medium');
const extraInfo = ref('');
const jsonInput = ref('');
const parsedQuestions = ref([]);
const errorMessage = ref('');
const selectedTab = ref('setup');

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

function generateEmptyQuestions() {
  const empty = [];
  for (let i = 0; i < qCount.value; i++) {
    empty.push({
      question: `New Question ${i+1}`,
      options: [
        { id: 'opt-a-' + i, text: 'Option A' },
        { id: 'opt-b-' + i, text: 'Option B' },
        { id: 'opt-c-' + i, text: 'Option C' },
        { id: 'opt-d-' + i, text: 'Option D' }
      ],
      correctId: 'opt-a-' + i
    });
  }
  
  // Format v3 signals the generator to build GroupMCQ.vue blocks
  // Since useAIPaste expects a specific format, we adjust
  const legacyFormat = empty.map(q => ({
    question: q.question,
    options: q.options.map(o => o.text),
    answer: q.options[0].text // Default first as correct
  }));
  
  generateQuestionElements(legacyFormat, 'new', 'v3');
  appendLeaderboard();
  close();
}

async function pasteFromClipboard() {
  try {
    const text = await navigator.clipboard.readText();
    if (text) {
      jsonInput.value = text;
      parsePreview();
    }
  } catch (err) {
    $q.notify({ type: 'negative', message: 'Could not access clipboard' });
  }
}

function appendLeaderboard() {
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
}

function submitToPresentation(layoutMode = 'individual') {
  if (parsedQuestions.value.length === 0) return;

  // Format v3 signals the generator to build GroupMCQ.vue blocks
  generateQuestionElements(parsedQuestions.value, 'new', 'v3', layoutMode);

  appendLeaderboard();
  close();
}

function exportToReusablePlayer() {
  if (parsedQuestions.value.length === 0) return;

  const saved = saveGroupQuizSession(gameStore.groups, parsedQuestions.value);

  if (saved) {
    $q.notify({
      type: 'positive',
      message: 'Quiz exported to Reusable Player',
      caption: 'Open the Group Quiz Player test page to play.',
      position: 'top',
      actions: [
        {
          label: 'Open Player',
          color: 'white',
          handler: () => {
            window.open('/classroom-records/presentation/builder-v7/group-quiz-player', '_blank');
          }
        }
      ]
    });
  } else {
    $q.notify({
      type: 'negative',
      message: 'Failed to export quiz to player',
      position: 'top'
    });
  }
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
        <!-- Tab Navigation -->
        <div class="tab-nav">
          <button 
            class="tab-btn" 
            :class="{ active: selectedTab === 'setup' }"
            @click="selectedTab = 'setup'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            Setup AI Prompt
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: selectedTab === 'preview' }"
            @click="selectedTab = 'preview'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            Paste & Preview
          </button>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
          
          <!-- TAB 1: Setup AI Prompt -->
          <div v-if="selectedTab === 'setup'" class="tab-panel setup-panel">
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

            <div style="margin-top: 20px; border-top: 1px dashed #cbd5e1; padding-top: 20px;">
              <button class="btn-secondary-full" @click="generateEmptyQuestions">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Generate Empty Questions
              </button>
              <p class="helper-text">Create placeholders for manual editing.</p>
            </div>
          </div>

          <!-- TAB 2: Paste AI Output & Preview -->
          <div v-if="selectedTab === 'preview'" class="tab-panel preview-panel">
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
            
            <button class="btn-paste" @click="pasteFromClipboard">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
              Paste AI Output
            </button>
            <div v-if="errorMessage" class="error-msg">{{ errorMessage }}</div>

            <!-- Previews -->
            <div v-if="parsedQuestions.length > 0" class="preview-area">
              <h4>Visual Preview ({{ parsedQuestions.length }} Questions)</h4>
              <div class="preview-cards">
                <div v-for="(q, index) in parsedQuestions" :key="index" class="q-card">
                  <div class="q-header">
                    <span class="q-number">{{ index + 1 }}</span>
                    <div class="q-title">
                       <EditableMath :content="q.question" :isEditMode="false" />
                    </div>
                  </div>
                  <div v-if="q.options" class="q-options">
                    <div v-for="(opt, oIndex) in q.options" :key="oIndex" class="q-opt" :class="{'opt-correct': q.answer && (q.answer.includes(opt) || opt.includes(q.answer))}">
                      <span class="opt-label">{{ String.fromCharCode(65 + oIndex) }}</span>
                      <EditableMath :content="opt" :isEditMode="false" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Final Submission -->
              <button class="btn-submit" @click="submitToPresentation('extended')">
                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                 Add All in Extended Slide
              </button>

              <button class="btn-submit" style="margin-top:8px; background:#3b82f6;" @click="submitToPresentation('individual')">
                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                 Add as Individual Slides
              </button>

              <button class="btn-submit" style="margin-top:8px; background:#2563eb;" @click="exportToReusablePlayer">
                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                 Export to Reusable Player
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
  overflow: hidden;
}

/* Tab Navigation */
.tab-nav {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid #e2e8f0;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: transparent;
  border: none;
  border-radius: 8px;
  color: #64748b;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn:hover {
  background: #f1f5f9;
  color: #334155;
}

.tab-btn.active {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: white;
  box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3);
}

.tab-btn svg {
  flex-shrink: 0;
}

/* Tab Content */
.tab-content {
  flex: 1;
  overflow-y: auto;
  padding-right: 8px;
}

.tab-panel {
  max-width: 700px;
  margin: 0 auto;
}

.setup-panel {
  background: #f8fafc;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.preview-panel {
  background: #f8fafc;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
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
  min-height: 300px;
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
  gap: 16px;
  padding-right: 8px;
  margin-bottom: 12px;
}

/* Custom scrollbar */
.preview-cards::-webkit-scrollbar {
  width: 6px;
}
.preview-cards::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}
.preview-cards::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}
.preview-cards::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.q-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
  transition: all 0.2s ease;
}

.q-card:hover {
  box-shadow: 0 4px 6px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.06);
  border-color: #cbd5e1;
}

.q-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 14px;
}

.q-number {
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: white;
  font-size: 14px;
  font-weight: 800;
  box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3);
}

.q-title {
  flex: 1;
  font-weight: 700;
  font-size: 1.15rem;
  line-height: 1.5;
  color: #0f172a;
}

.q-options {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.q-opt {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 10px 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
  line-height: 1.4;
  color: #334155;
  transition: all 0.2s ease;
}

.q-opt:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.opt-label {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  background: #e2e8f0;
  color: #475569;
  font-size: 12px;
  font-weight: 700;
}

.opt-correct {
  background: #f0fdf4;
  border-color: #22c55e;
  border-width: 1.5px;
}

.opt-correct .opt-label {
  background: #22c55e;
  color: white;
}

.opt-correct {
  color: #166534;
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

.btn-secondary-full {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px;
  background: white;
  color: #4b5563;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-secondary-full:hover { background: #f9fafb; border-color: #9ca3af; }

.btn-paste {
  margin-top: 10px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px;
  background: #f3f4f6;
  color: #111827;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-paste:hover { background: #e5e7eb; }
</style>
