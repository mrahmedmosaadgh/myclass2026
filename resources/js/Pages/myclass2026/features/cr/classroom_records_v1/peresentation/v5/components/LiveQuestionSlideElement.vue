<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useQuestionSession } from '../../../../../remot_control/v1/examples/question_responses/composables/useQuestionSession';
import { useLiveQuestionStore } from '../stores/liveQuestionStore';
import QRCodeVue from 'qrcode.vue';

const props = defineProps({
  element: Object,
  isEditMode: Boolean
});

const emit = defineEmits(['update', 'select']);
const liveQuestionStore = useLiveQuestionStore();

const isInteractive = ref(false);
const activeSession = ref(null);
const sessionCode = ref('');
const showQR = ref(false);
const copySuccess = ref(false);

// Computed property for QR code URL to avoid window.location issues
const qrCodeUrl = computed(() => {
  if (!sessionCode.value && !props.element.data?.sessionCode) return '';
  const baseUrl = typeof window !== 'undefined' ? window.location.origin : '';
  const code = sessionCode.value || props.element.data?.sessionCode;
  return `${baseUrl}/remote-control/question-responses/student?code=${code}`;
});

// Initialize element data if not exists
if (!props.element.data) {
  emit('update', {
    data: {
      questionTitle: '',
      questionInstructions: '',
      responses: [],
      status: 'idle' // idle, active, graded
    }
  });
}

const isGraded = computed(() => props.element.data?.status === 'graded');
const hasResponses = computed(() => (props.element.data?.responses || []).length > 0);

// Generate session code
function generateCode() {
  const chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
  let code = '';
  for (let i = 0; i < 6; i++) {
    code += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return code;
}


// Copy session code to clipboard
async function copyCode() {
  try {
    await navigator.clipboard.writeText(sessionCode.value);
    copySuccess.value = true;
    setTimeout(() => {
      copySuccess.value = false;
    }, 2000);
  } catch (error) {
    console.error('Failed to copy code:', error);
  }
}

// Toggle QR code display
function toggleQR() {
  showQR.value = !showQR.value;
}

// Start live question session
function startSession() {
  if (!props.element.data?.questionTitle || props.element.data.questionTitle === 'Enter your question...') {
    alert('Please enter a question first');
    return;
  }

  sessionCode.value = generateCode();
  isInteractive.value = true;

  // Initialize remote control session
  activeSession.value = useQuestionSession(sessionCode.value, 'teacher');

  // Publish question
  const questionData = {
    id: props.element.id,
    title: props.element.data.questionTitle,
    instructions: props.element.data.questionInstructions || '',
    minLength: 1,
    maxLength: 1000
  };

  activeSession.value.publishQuestion(questionData);

  // Listen for student responses
  activeSession.value.channel.onCommand((command) => {
    if (command.type === 'submit_answer') {
      addResponse(command.payload);
    }
  });

  // Persist session state
  emit('update', {
    data: {
      ...props.element.data,
      sessionCode: sessionCode.value,
      status: 'active',
      isInteractive: true
    }
  });
}

// Stop session
function lockSession() {
  isInteractive.value = false;
  
  if (activeSession.value) {
    activeSession.value.closeSession();
    activeSession.value = null;
  }

  emit('update', {
    data: {
      ...props.element.data,
      status: 'idle',
      isInteractive: false
    }
  });
}

// Restore session on mount if it was active
onMounted(() => {
  if (props.element.data?.status === 'active' && props.element.data?.sessionCode) {
    sessionCode.value = props.element.data.sessionCode;
    isInteractive.value = props.element.data.isInteractive || false;
    
    // Restore remote control session
    if (isInteractive.value) {
      activeSession.value = useQuestionSession(sessionCode.value, 'teacher');
      
      // Re-publish question
      const questionData = {
        id: props.element.id,
        title: props.element.data.questionTitle,
        instructions: props.element.data.questionInstructions || '',
        minLength: 1,
        maxLength: 1000
      };
      
      activeSession.value.publishQuestion(questionData);
      
      // Listen for student responses
      activeSession.value.channel.onCommand((command) => {
        if (command.type === 'submit_answer') {
          addResponse(command.payload);
        }
      });
    }
  }
});

onUnmounted(() => {
  // Don't close session on unmount - let it persist
  // Session will be restored on remount
});

// Add student response
function addResponse(response) {
  const responses = props.element.data?.responses || [];
  const existingIndex = responses.findIndex(r => r.studentId === response.studentId);
  
  if (existingIndex === -1) {
    responses.push({
      studentId: response.studentId,
      studentName: response.studentName,
      answer: response.answer?.text || response.answer,
      timestamp: response.timestamp || new Date().toISOString(),
      score: 0,
      rank: null
    });

    emit('update', {
      data: {
        ...props.element.data,
        responses
      }
    });
  }
}

// Grade responses
function gradeResponses() {
  if (isGraded.value) return;

  const responses = props.element.data?.responses || [];
  if (responses.length === 0) {
    alert('No responses to grade yet!');
    return;
  }

  // Calculate rankings based on scores
  const sorted = [...responses].sort((a, b) => b.score - a.score);
  let currentRank = 1;
  sorted.forEach((response, index) => {
    if (index > 0 && sorted[index - 1].score > response.score) {
      currentRank = index + 1;
    }
    response.rank = currentRank;
  });

  emit('update', {
    data: {
      ...props.element.data,
      responses: sorted,
      status: 'graded'
    }
  });
}

// Update student score
function updateScore(studentId, score) {
  const responses = props.element.data?.responses || [];
  const response = responses.find(r => r.studentId === studentId);
  if (response) {
    response.score = parseInt(score) || 0;
    emit('update', {
      data: {
        ...props.element.data,
        responses
      }
    });
  }
}

// Reset/replay
function replayQuestion() {
  emit('update', {
    data: {
      ...props.element.data,
      responses: [],
      status: 'idle'
    }
  });
  isInteractive.value = false;
  sessionCode.value = '';
}
</script>

<template>
  <div class="live-question-wrapper" @click="emit('select')">
    <!-- Edit Mode -->
    <div v-if="isEditMode" class="edit-container" @click.stop>
      <div class="edit-header">
        <div class="header-content">
          <div class="icon-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <h4>Live Question</h4>
          </div>
          <div class="status-badges">
            <span v-if="element.data?.status === 'active'" class="badge active-badge">🟢 Active</span>
            <span v-if="element.data?.status === 'graded'" class="badge graded-badge">✓ Graded</span>
            <span v-if="hasResponses" class="badge response-badge">{{ element.data.responses.length }} responses</span>
          </div>
        </div>
      </div>
      
      <div class="edit-form" @click.stop>
        <div class="form-group">
          <label class="form-label">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            Question
          </label>
          <input 
            :value="element.data?.questionTitle || ''"
            @input="emit('update', { data: { ...element.data, questionTitle: $event.target.value } })"
            type="text"
            class="question-input"
            placeholder="What is your question? (e.g., What is the capital of France?)"
            @click.stop
            @mousedown.stop
          />
        </div>
        
        <div class="form-group">
          <label class="form-label">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="8" y1="6" x2="21" y2="6"></line>
              <line x1="8" y1="12" x2="21" y2="12"></line>
              <line x1="8" y1="18" x2="21" y2="18"></line>
              <line x1="3" y1="6" x2="3.01" y2="6"></line>
              <line x1="3" y1="12" x2="3.01" y2="12"></line>
              <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
            Instructions (optional)
          </label>
          <textarea 
            :value="element.data?.questionInstructions || ''"
            @input="emit('update', { data: { ...element.data, questionInstructions: $event.target.value } })"
            class="instructions-input"
            rows="3"
            placeholder="Add any additional instructions for students..."
            @click.stop
            @mousedown.stop
          ></textarea>
        </div>

        <div v-if="element.data?.sessionCode" class="session-info-edit">
          <div class="info-item">
            <span class="info-label">Session Code:</span>
            <span class="info-value">{{ element.data.sessionCode }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Presentation Mode -->
    <div v-else class="presentation-container">
      <!-- Control Bar -->
      <div class="control-bar">
        <button 
          @click="startSession" 
          :disabled="isInteractive || isGraded"
          class="ctrl-btn start"
        >
          ▶ Start
        </button>
        
        <button 
          @click="lockSession" 
          :disabled="!isInteractive"
          class="ctrl-btn lock"
        >
          ⏸ Lock
        </button>

        <button 
          @click="gradeResponses" 
          :disabled="!hasResponses || isGraded"
          class="ctrl-btn grade"
        >
          ✓ Grade
        </button>

        <button 
          @click="replayQuestion"
          class="ctrl-btn replay"
        >
          ↻ Reset
        </button>

        <div v-if="isInteractive || (element.data?.status === 'active' && element.data?.sessionCode)" class="session-code-container">
          <div class="session-code">
            Code: <strong>{{ sessionCode || element.data?.sessionCode }}</strong>
          </div>
          
          <button @click="copyCode" class="copy-btn" :class="{ copied: copySuccess }">
            <svg v-if="!copySuccess" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
              <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            {{ copySuccess ? 'Copied!' : 'Copy' }}
          </button>
          
          <button @click="toggleQR" class="qr-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="14" y="14" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            QR
          </button>
        </div>
        
        <!-- QR Code Modal -->
        <div v-if="showQR" class="qr-modal" @click="showQR = false">
          <div class="qr-content" @click.stop>
            <h3>Scan to Join</h3>
            <div class="qr-code-wrapper">
              <QRCodeVue
                :value="qrCodeUrl"
                :size="200"
                level="M"
                render-as="svg"
                class="qr-code"
              />
            </div>
            <p class="qr-code-text">Code: <strong>{{ sessionCode || element.data?.sessionCode }}</strong></p>
            <button @click="showQR = false" class="close-qr">Close</button>
          </div>
        </div>
      </div>

      <!-- Question Display -->
      <div class="question-display">
        <div class="question-header">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          <span class="question-label">Question</span>
        </div>
        <h2 class="question-title">
  {{ element.data?.questionTitle || 'Enter your question in edit mode...' }}
</h2>
        <div v-if="element.data?.questionInstructions" class="instructions-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
          <p class="question-instructions">{{ element.data.questionInstructions }}</p>
        </div>
      </div>

      <!-- Responses List -->
      <div v-if="hasResponses" class="responses-section">
        <h3>Student Responses ({{ element.data.responses.length }})</h3>
        
        <div class="responses-list">
          <div 
            v-for="response in element.data.responses" 
            :key="response.studentId"
            class="response-card"
            :class="{ 'graded': isGraded }"
          >
            <div class="response-header">
              <div class="student-info">
                <span v-if="response.rank" class="rank-badge" :class="`rank-${response.rank}`">
                  #{{ response.rank }}
                </span>
                <span class="student-name">{{ response.studentName }}</span>
              </div>
              
              <div class="score-input" v-if="!isGraded">
                <input 
                  type="number"
                  :value="response.score"
                  @input="updateScore(response.studentId, $event.target.value)"
                  min="0"
                  max="100"
                  class="score-field"
                  placeholder="0"
                />
                <span class="score-label">/100</span>
              </div>
              
              <div v-else class="score-display">
                <span class="score-value">{{ response.score }}</span>
                <span class="score-label">pts</span>
              </div>
            </div>
            
            <div class="response-content">
              {{ response.answer }}
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!isInteractive" class="empty-state">
        <p>👆 Click "Start" to begin collecting responses</p>
      </div>

      <div v-else class="waiting-state">
        <p>⏳ Waiting for student responses...</p>
        <p class="session-info">Students can join with code: <strong>{{ sessionCode }}</strong></p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.live-question-wrapper {
  width: 100%;
  height: 100%;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Edit Mode */
.edit-container {
  padding: 24px;
  height: 100%;
  overflow-y: auto;
  background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
}

.edit-header {
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 2px solid #d1fae5;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.icon-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.icon-title h4 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #065f46;
}

.status-badges {
  display: flex;
  gap: 8px;
}

.badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.3px;
}

.active-badge {
  background: #d1fae5;
  color: #065f46;
}

.graded-badge {
  background: #10b981;
  color: white;
}

.response-badge {
  background: #dbeafe;
  color: #1e40af;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 4px;
}

.question-input,
.instructions-input {
  padding: 12px 16px;
  border: 2px solid #d1d5db;
  border-radius: 8px;
  font-size: 15px;
  font-family: inherit;
  background: white;
  transition: all 0.2s ease;
  color: #111827;
}

.question-input:hover,
.instructions-input:hover {
  border-color: #10b981;
}

.question-input:focus,
.instructions-input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.question-input::placeholder,
.instructions-input::placeholder {
  color: #9ca3af;
}

.session-info-edit {
  padding: 12px 16px;
  background: white;
  border: 1px solid #d1fae5;
  border-radius: 8px;
  margin-top: 8px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-label {
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
}

.info-value {
  font-size: 14px;
  font-weight: 700;
  color: #10b981;
  letter-spacing: 1px;
}

/* Presentation Mode */
.presentation-container {
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 16px;
}

.control-bar {
  display: flex;
  gap: 8px;
  align-items: center;
  padding: 12px;
  background: #f3f4f6;
  border-radius: 8px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.ctrl-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.ctrl-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.ctrl-btn.start {
  background: #10b981;
  color: white;
}

.ctrl-btn.start:hover:not(:disabled) {
  background: #059669;
}

.ctrl-btn.lock {
  background: #f59e0b;
  color: white;
}

.ctrl-btn.grade {
  background: #3b82f6;
  color: white;
}

.ctrl-btn.replay {
  background: #6b7280;
  color: white;
}

.session-code-container {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 8px;
}

.session-code {
  padding: 6px 12px;
  background: white;
  border-radius: 6px;
  font-size: 13px;
  color: #374151;
}

.session-code strong {
  color: #10b981;
  font-size: 16px;
  margin-left: 4px;
}

.copy-btn,
.qr-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 10px;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s ease;
}

.copy-btn:hover,
.qr-btn:hover {
  background: #f9fafb;
  border-color: #10b981;
  color: #10b981;
}

.copy-btn.copied {
  background: #10b981;
  color: white;
  border-color: #10b981;
}

.qr-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.qr-content {
  background: white;
  padding: 32px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
  max-width: 90%;
}

.qr-content h3 {
  margin: 0 0 20px;
  font-size: 20px;
  color: #111827;
}

.qr-code-wrapper {
  display: flex;
  justify-content: center;
  margin: 0 auto 16px;
  padding: 16px;
  background: white;
  border-radius: 8px;
  border: 2px solid #e5e7eb;
  width: fit-content;
}

.qr-code {
  display: block;
}

.qr-code-text {
  font-size: 14px;
  color: #6b7280;
  margin: 16px 0;
}

.qr-code-text strong {
  color: #10b981;
  font-size: 18px;
  font-weight: 700;
}

.close-qr {
  padding: 10px 24px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.close-qr:hover {
  background: #059669;
}

.question-display {
  padding: 28px;
  background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
  border-radius: 12px;
  margin-bottom: 20px;
  border: 2px solid #d1fae5;
}

.question-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}

.question-label {
  font-size: 14px;
  font-weight: 600;
  color: #059669;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.question-title {
  font-size: 24px;
  font-weight: 700;
  color: #065f46;
  margin: 0 0 16px;
  line-height: 1.4;
  text-align: left;
}

.instructions-box {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 16px;
  background: white;
  border-radius: 8px;
  border-left: 3px solid #10b981;
  margin-top: 12px;
}

.question-instructions {
  font-size: 15px;
  color: #374151;
  margin: 0;
  line-height: 1.6;
  text-align: left;
}

.responses-section {
  flex: 1;
  overflow-y: auto;
}

.responses-section h3 {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 12px;
}

.responses-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.response-card {
  padding: 12px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  transition: all 0.2s;
}

.response-card.graded {
  background: #f0fdf4;
  border-color: #10b981;
}

.response-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.student-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.rank-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  font-size: 11px;
  font-weight: 700;
  color: white;
}

.rank-1 {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
}

.rank-2 {
  background: linear-gradient(135deg, #d1d5db, #9ca3af);
}

.rank-3 {
  background: linear-gradient(135deg, #f87171, #dc2626);
}

.student-name {
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.score-input {
  display: flex;
  align-items: center;
  gap: 4px;
}

.score-field {
  width: 60px;
  padding: 4px 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  text-align: center;
  font-size: 13px;
  font-weight: 600;
}

.score-field:focus {
  outline: none;
  border-color: #10b981;
}

.score-display {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  background: #10b981;
  border-radius: 4px;
}

.score-value {
  font-weight: 700;
  color: white;
  font-size: 14px;
}

.score-label {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.8);
}

.response-content {
  font-size: 13px;
  color: #374151;
  line-height: 1.5;
  padding: 8px;
  background: white;
  border-radius: 4px;
}

.empty-state,
.waiting-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #6b7280;
  padding: 40px;
}

.session-info {
  margin-top: 8px;
  font-size: 14px;
}

.session-info strong {
  color: #10b981;
  font-size: 18px;
}
</style>
