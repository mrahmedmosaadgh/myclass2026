<script setup>
import { ref } from 'vue';
import { useUIStore } from '../stores/uiStore';
import { useAIPaste } from '../composables/useAIPaste';

const ui = useUIStore();
const { generateQuestionElements } = useAIPaste();

const jsonInput = ref('');
const targetSlide = ref('new');
const targetFormat = ref('v2');
const errorMessage = ref('');

const promptMultipleChoice = `You are an expert teacher. Generate a JSON Array containing 3 Multiple Choice questions about [TOPIC]. Use Markdown bolding "**" or "###" if needed. If you write math formulas, strictly wrap them in "\\\\(" and "\\\\)".
IMPORTANT: Because this is JSON, YOU MUST DOUBLE-ESCAPE ALL LATEX BACKSLASHES! For example, write "\\\\frac{1}{2}" instead of "\\frac{1}{2}".
Return ONLY valid JSON format exactly like this:
[
  {
    "question": "What is \\\\( 5 + 5 \\\\)?",
    "options": ["\\\\( 7 \\\\)", "\\\\( 10 \\\\)", "\\\\( 12 \\\\)", "\\\\( 15 \\\\)"],
    "answer": "B) \\\\( 10 \\\\)"
  }
]`;

const promptShortAnswer = `You are an expert teacher. Generate a JSON Array containing 3 Short Answer questions about [TOPIC]. Use Markdown bolding "**" or "###" if needed. If you write math formulas, strictly wrap them in "\\\\(" and "\\\\)".
IMPORTANT: Because this is JSON, YOU MUST DOUBLE-ESCAPE ALL LATEX BACKSLASHES! For example, write "\\\\frac{1}{2}" instead of "\\frac{1}{2}".
Return ONLY valid JSON format exactly like this:
[
  {
    "question": "Explain the concept of...",
    "answer": "The core concept is..."
  }
]`;

function copyToClipboard(text) {
  navigator.clipboard.writeText(text);
  alert('Prompt copied! Paste it into your preferred AI (ChatGPT/Claude).');
}

function processJSON() {
  errorMessage.value = '';
  if (!jsonInput.value.trim()) {
    errorMessage.value = 'Error: Please paste the generated JSON code first.';
    return;
  }

  try {
    let raw = jsonInput.value.trim();
    if (raw.startsWith('```')) {
      raw = raw.replace(/^```[a-z]*\n/i, '').replace(/\n```$/i, '');
    }

    // Auto-fix unescaped LaTeX backslashes common in AI output
    let fixedRaw = "";
    for (let i = 0; i < raw.length; i++) {
      if (raw[i] === '\\') {
        if (i + 1 < raw.length) {
          const next = raw[i+1];
          // If it's already a valid JSON escape sequence the AI intended
          if (next === '"' || next === '\\' || next === 'n' || next === 't' || next === 'r') {
             fixedRaw += '\\' + next;
             i++;
          } else {
             // It's a raw LaTeX backslash (e.g. \frac or \(). Double escape it so JSON builds natively
             fixedRaw += '\\\\';
          }
        } else {
          fixedRaw += '\\\\';
        }
      } else {
        fixedRaw += raw[i];
      }
    }

    const parsedData = JSON.parse(fixedRaw);
    
    if (!Array.isArray(parsedData) && typeof parsedData !== 'object') {
       throw new Error("Invalid structure. Must be a JSON Object or Array of objects.");
    }

    generateQuestionElements(parsedData, targetSlide.value, targetFormat.value);
    
    ui.isAIPasteDialogOpen = false;
    jsonInput.value = '';

  } catch (err) {
    errorMessage.value = 'Invalid JSON: ' + err.message + '\nMake sure you only copied the JSON output.';
  }
}

function close() {
  ui.isAIPasteDialogOpen = false;
}
</script>

<template>
  <div v-if="ui.isAIPasteDialogOpen" class="modal-overlay" @mousedown.self="close">
    <div class="modal">
      <div class="modal-header">
        <h3 style="display: flex; align-items: center; gap: 8px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><circle cx="12" cy="5" r="2"></circle><path d="M12 7v4"></path><line x1="8" y1="16" x2="8" y2="16"></line><line x1="16" y1="16" x2="16" y2="16"></line></svg>
          Paste from AI
        </h3>
        <button @click="close" class="close-btn">&times;</button>
      </div>
      
      <div class="modal-body">
        <p class="desc">Instantly convert AI-generated JSON into fully structured presentation slides.</p>
        
        <div class="prompt-templates">
          <h4>1. Copy an AI Prompt Template:</h4>
          <div class="template-btns">
            <button class="btn-secondary" @click="copyToClipboard(promptMultipleChoice)">
               📋 Multiple Choice
            </button>
            <button class="btn-secondary" @click="copyToClipboard(promptShortAnswer)">
               📋 Short Answer
            </button>
          </div>
        </div>

        <div class="input-area">
          <h4>2. Paste the resulting JSON here:</h4>
          <textarea v-model="jsonInput" placeholder='[\n  {\n    "question": "What is the capital of France?",\n    "options": ["A) London", "B) Paris", "C) Berlin", "D) Madrid"],\n    "answer": "B) Paris"\n  }\n]'></textarea>
        </div>

        <div v-if="errorMessage" class="error-box">
          {{ errorMessage }}
        </div>

        <div class="actions-area">
          <div class="target-select">
            <label>
              <input type="radio" v-model="targetSlide" value="new"> 
              Spawn entirely new Slides
            </label>
            <label>
              <input type="radio" v-model="targetSlide" value="current"> 
              Drop onto Current Slide
            </label>
          </div>
          <div class="target-select">
            <label>
              <input type="radio" v-model="targetFormat" value="v1"> 
              V1: Generic Concealed Blocks
            </label>
            <label style="color: #6d28d9; font-weight: bold;">
              <input type="radio" v-model="targetFormat" value="v2"> 
              V2: Interactive Quiz Component
            </label>
          </div>
          <button class="btn-primary" @click="processJSON">Generate Slides</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal {
  background: white;
  width: 550px;
  max-width: 95vw;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  color: #111827;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #6b7280;
}

.close-btn:hover { color: #111827; }

.modal-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.desc {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.prompt-templates h4, .input-area h4 {
  margin: 0 0 10px 0;
  font-size: 15px;
  font-weight: 600;
  color: #374151;
}

.template-btns {
  display: flex;
  gap: 10px;
}

.btn-secondary {
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: 0.2s;
}
.btn-secondary:hover { background: #e5e7eb; }

.input-area textarea {
  width: 100%;
  height: 180px;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-family: monospace;
  font-size: 13px;
  resize: vertical;
}

.input-area textarea:focus {
  outline: none;
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
}

.error-box {
  background: #fee2e2;
  color: #b91c1c;
  padding: 10px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
}

.actions-area {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid #e5e7eb;
  padding-top: 15px;
}

.target-select {
  display: flex;
  flex-direction: column;
  gap: 5px;
  font-size: 13px;
  color: #374151;
}

.target-select label {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
}

.btn-primary {
  background: #8b5cf6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.2s;
}

.btn-primary:hover {
  background: #7c3aed;
}

@media (max-width: 600px) {
  .actions-area {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
  }
}
</style>
