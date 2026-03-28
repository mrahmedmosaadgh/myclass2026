<script setup>
import { ref, computed } from 'vue';
import { useQuestionSession } from '../../../../../remot_control/v1/examples/question_responses/composables/useQuestionSession';
import { useLiveQuestionStore } from '../stores/liveQuestionStore';

const props = defineProps({
  element: Object,
  isEditMode: Boolean
});

const emit = defineEmits(['update', 'select']);
const liveQuestionStore = useLiveQuestionStore();

const isInteractive = ref(false);
const activeSession = ref(null);
const sessionCode = ref('');

// Initialize element data if not exists
if (!props.element.data) {
  emit('update', {
    data: {
      questionTitle: 'Enter your question...',
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

  emit('update', {
    data: {
      ...props.element.data,
      sessionCode: sessionCode.value,
      status: 'active'
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
      status: 'idle'
    }
  });
}

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
    <div v-if="isEditMode" class="edit-container">
      <div class="edit-header">
        <h4>📝 Live Question</h4>
      </div>
      
      <div class="edit-form">
        <div class="form-group">
          <label>Question:</label>
          <input 
            v-model="element.data.questionTitle"
            @input="emit('update', { data: { ...element.data, questionTitle: $event.target.value } })"
            type="text"
            class="question-input"
            placeholder="What is your question?"
          />
        </div>
        
        <div class="form-group">
          <label>Instructions (optional):</label>
          <textarea 
            v-model="element.data.questionInstructions"
            @input="emit('update', { data: { ...element.data, questionInstructions: $event.target.value } })"
            class="instructions-input"
            rows="2"
            placeholder="Additional instructions for students..."
          ></textarea>
        </div>

        <div class="stats">
          <span>📊 {{ hasResponses ? element.data.responses.length : 0 }} responses</span>
          <span v-if="element.data.status === 'graded'" class="graded-badge">✓ Graded</span>
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

        <div v-if="isInteractive" class="session-code">
          Code: <strong>{{ sessionCode }}</strong>
        </div>
      </div>

      <!-- Question Display -->
      <div class="question-display">
        <h2 class="question-title">{{ element.data?.questionTitle || 'Live Question' }}</h2>
        <p v-if="element.data?.questionInstructions" class="question-instructions">
          {{ element.data.questionInstructions }}
        </p>
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
  padding: 20px;
  height: 100%;
  overflow-y: auto;
}

.edit-header {
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 2px solid #e5e7eb;
}

.edit-header h4 {
  margin: 0;
  font-size: 16px;
  color: #374151;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
}

.question-input,
.instructions-input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
}

.question-input:focus,
.instructions-input:focus {
  outline: none;
  border-color: #10b981;
}

.stats {
  display: flex;
  gap: 12px;
  align-items: center;
  font-size: 12px;
  color: #6b7280;
  padding: 8px;
  background: #f9fafb;
  border-radius: 6px;
}

.graded-badge {
  background: #10b981;
  color: white;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 500;
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

.session-code {
  margin-left: auto;
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

.question-display {
  padding: 20px;
  background: #f9fafb;
  border-radius: 8px;
  margin-bottom: 16px;
  text-align: center;
}

.question-title {
  font-size: 20px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 8px;
}

.question-instructions {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
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
