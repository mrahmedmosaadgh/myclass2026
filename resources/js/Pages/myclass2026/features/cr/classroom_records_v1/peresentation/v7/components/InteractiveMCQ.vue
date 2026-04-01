<script setup>
import { ref, computed } from 'vue';
import EditableMath from './EditableMath.vue';

const props = defineProps({
  element: Object,
  isEditMode: Boolean
});

const emit = defineEmits(['update', 'select']);

const selectedAnswer = ref(null);
const isMuted = ref(false);
const isInteractive = ref(false); // Controls if the buttons accept clicks
const hasSubmitted = ref(false);
const feedbackMessage = ref('');

// Simple Web Audio API helper for zero-dependency sound effects
const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

function playSound(type) {
  if (isMuted.value) return;
  if (audioCtx.state === 'suspended') audioCtx.resume();

  const osc = audioCtx.createOscillator();
  const gainNode = audioCtx.createGain();
  osc.connect(gainNode);
  gainNode.connect(audioCtx.destination);

  const now = audioCtx.currentTime;

  if (type === 'hover') {
    osc.type = 'sine';
    osc.frequency.setValueAtTime(600, now);
    gainNode.gain.setValueAtTime(0.02, now);
    gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.05);
    osc.start(now);
    osc.stop(now + 0.05);
  } else if (type === 'correct') {
    // Pleasant ascending chime
    osc.type = 'sine';
    osc.frequency.setValueAtTime(400, now);
    osc.frequency.exponentialRampToValueAtTime(800, now + 0.1);
    gainNode.gain.setValueAtTime(0.1, now);
    gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.3);
    osc.start(now);
    osc.stop(now + 0.3);
  } else if (type === 'incorrect') {
    // Low negative buzz
    osc.type = 'sawtooth';
    osc.frequency.setValueAtTime(250, now);
    osc.frequency.exponentialRampToValueAtTime(150, now + 0.2);
    gainNode.gain.setValueAtTime(0.1, now);
    gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.3);
    osc.start(now);
    osc.stop(now + 0.3);
  }
}

function handleOptionHover() {
  if (!props.isEditMode && isInteractive.value) {
    const isCorrectLocked = hasSubmitted.value && selectedAnswer.value === props.element.questionData.correctId;
    if (!isCorrectLocked) playSound('hover');
  }
}

function handleOptionClick(optId) {
  if (props.isEditMode || !isInteractive.value) return;
  
  const mode = props.element.questionData.feedbackMode || 'instant';

  if (mode === 'instant') {
    if (selectedAnswer.value) return; 
    selectedAnswer.value = optId;
    hasSubmitted.value = true;
    if (optId === props.element.questionData.correctId) {
      playSound('correct');
    } else {
      playSound('incorrect');
    }
  } else {
    // Submit Mode
    const isCorrectLocked = hasSubmitted.value && selectedAnswer.value === props.element.questionData.correctId;
    if (isCorrectLocked) return; 

    selectedAnswer.value = optId; 
    hasSubmitted.value = false;
    feedbackMessage.value = '';
    playSound('hover'); 
  }
}

function getButtonClass(optId) {
  if (props.isEditMode) return 'edit-mode-btn';
  if (!isInteractive.value) return 'locked-mode-btn';

  const mode = props.element.questionData.feedbackMode || 'instant';

  if (!selectedAnswer.value) return 'interactive-btn'; 

  if (mode === 'instant') {
    if (optId === props.element.questionData.correctId) return 'correct-btn';
    if (selectedAnswer.value === optId) return 'incorrect-btn';
    return 'disabled-btn';
  }

  // Submit Mode
  if (!hasSubmitted.value) {
     if (selectedAnswer.value === optId) return 'selected-btn';
     return 'interactive-btn';
  }

  if (selectedAnswer.value === optId) {
     if (optId === props.element.questionData.correctId) return 'correct-btn';
     return 'incorrect-btn';
  }

  const isCorrectLocked = hasSubmitted.value && selectedAnswer.value === props.element.questionData.correctId;
  if (!isCorrectLocked) {
     return 'interactive-btn'; // Still clickable to try again!
  }

  return 'disabled-btn';
}

function updateQuestion(newQuestion) {
  const newQData = { ...props.element.questionData, question: newQuestion };
  emit('update', { questionData: newQData });
}

function updateOption(optIndex, newText) {
  const newOptions = [...props.element.questionData.options];
  newOptions[optIndex].text = newText;
  const newQData = { ...props.element.questionData, options: newOptions };
  emit('update', { questionData: newQData });
}

function setCorrectId(optId) {
  const newQData = { ...props.element.questionData, correctId: optId };
  emit('update', { questionData: newQData });
}

function setFeedbackMode(mode) {
  const newQData = { ...props.element.questionData, feedbackMode: mode };
  emit('update', { questionData: newQData });
}

function handleWrapperClick(e) {
  e.stopPropagation();
  emit('select');
}

function toggleMute() {
  isMuted.value = !isMuted.value;
}

function startQuiz() {
  isInteractive.value = true;
  selectedAnswer.value = null;
  hasSubmitted.value = false;
  feedbackMessage.value = '';
}

function resetQuiz() {
  selectedAnswer.value = null;
  hasSubmitted.value = false;
  feedbackMessage.value = '';
}

function lockQuiz() {
  isInteractive.value = false;
  selectedAnswer.value = null;
  hasSubmitted.value = false;
  feedbackMessage.value = '';
}

function submitAnswer() {
  if (!selectedAnswer.value) return;
  hasSubmitted.value = true;
  if (selectedAnswer.value === props.element.questionData.correctId) {
    playSound('correct');
    feedbackMessage.value = 'Correct! well done.';
  } else {
    playSound('incorrect');
    feedbackMessage.value = 'Incorrect, try again...';
  }
}
</script>

<template>
  <div class="mcq-wrapper" @mousedown="handleWrapperClick" @touchstart="handleWrapperClick">
    
    <!-- Sound control toggle -->
    <button v-if="!isEditMode" class="mute-toggle" @click.stop="toggleMute" :title="isMuted ? 'Unmute Sounds' : 'Mute Sounds'">
      <svg v-if="isMuted" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5L6 9H2v6h4l5 4V5z"></path><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path></svg>
    </button>

    <!-- Main Question area -->
    <div class="question-header">
      <!-- Edit Mode Toolbar -->
      <div v-if="isEditMode" class="mcq-edit-toolbar">
         <label>
           <input type="radio" :name="'fbm-' + element.id" value="instant" :checked="element.questionData.feedbackMode !== 'submit'" @change="setFeedbackMode('instant')"> Instant Reveal
         </label>
         <label style="margin-left: 10px;">
           <input type="radio" :name="'fbm-' + element.id" value="submit" :checked="element.questionData.feedbackMode === 'submit'" @change="setFeedbackMode('submit')"> Require Submit
         </label>
      </div>

      <EditableMath 
        :content="element.questionData.question" 
        :isEditMode="isEditMode" 
        @update="updateQuestion" 
        @select="emit('select')"
      />
    </div>

    <!-- Options Grid -->
    <div class="options-grid">
      <div 
        v-for="(opt, index) in element.questionData.options" 
        :key="opt.id"
        class="option-container"
      >
        <!-- Selection Radio (Edit Mode ONLY) -->
        <div v-if="isEditMode" class="edit-radio-wrapper">
          <input 
            type="radio" 
            :name="'correct-opt-' + element.id" 
            :checked="element.questionData.correctId === opt.id"
            @change="setCorrectId(opt.id)"
            title="Mark as correct answer"
          >
        </div>

        <!-- The Button Core -->
        <div 
          class="option-btn" 
          :class="getButtonClass(opt.id)"
          @mouseenter="handleOptionHover"
          @click.stop="handleOptionClick(opt.id)"
        >
          <div style="flex: 1; max-width: 100%; min-width: 0; overflow-x: auto; overflow-y: hidden;">
            <EditableMath 
              :content="opt.text" 
              :isEditMode="isEditMode" 
              @update="(v) => updateOption(index, v)" 
              @select="emit('select')"
            />
          </div>
          
          <!-- Presentation visual feedback indicators -->
          <div v-if="!isEditMode && getButtonClass(opt.id) === 'correct-btn'" class="feedback-icon" style="color: #059669;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
          <div v-if="!isEditMode && getButtonClass(opt.id) === 'incorrect-btn'" class="feedback-icon" style="color: #dc2626;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Instructor Control Bar (Present Mode Only) -->
    <div v-if="!isEditMode" class="quiz-controls">
      <button v-if="!isInteractive" class="ctrl-btn btn-start" @click.stop="startQuiz">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
        Unlock Quiz
      </button>

      <template v-else>
        <button v-if="selectedAnswer && !hasSubmitted && element.questionData.feedbackMode === 'submit'" class="ctrl-btn btn-submit" @click.stop="submitAnswer">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          Submit Answer
        </button>

        <button v-if="hasSubmitted || element.questionData.feedbackMode !== 'submit'" class="ctrl-btn btn-reset" @click.stop="resetQuiz">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
          Reset
        </button>

        <span v-if="feedbackMessage" class="feedback-msg" :class="feedbackMessage.includes('Correct') ? 'msg-correct' : 'msg-incorrect'">
          {{ feedbackMessage }}
        </span>

        <div style="flex:1"></div>

        <button class="ctrl-btn btn-lock" @click.stop="lockQuiz">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          Lock
        </button>
      </template>
    </div>

  </div>
</template>

<style scoped>
.mcq-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 15px;
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
  position: relative;
}

.mute-toggle {
  position: absolute;
  top: 10px;
  right: 10px;
  background: transparent;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  transition: 0.2s;
  z-index: 10;
}
.mute-toggle:hover { color: #4b5563; }

.question-header {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  padding-bottom: 10px;
  border-bottom: 2px solid #f3f4f6;
  min-height: 50px;
  max-width: 100%;
  overflow-x: auto;
}

.mcq-edit-toolbar {
  font-size: 0.8rem;
  padding: 4px 8px;
  background: #fdf2f8;
  border: 1px dashed #fbcfe8;
  margin-bottom: 8px;
  border-radius: 4px;
}

.options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  flex: 1;
}

.option-container {
  display: flex;
  align-items: stretch;
  gap: 8px;
}

.edit-radio-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
}
.edit-radio-wrapper input {
  transform: scale(1.5);
  cursor: pointer;
}

.option-btn {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 15px;
  border-radius: 8px;
  border: 2px solid #e5e7eb;
  background: #f9fafb;
  min-height: 80px;
  max-width: 100%;
}

.feedback-icon {
  margin-left: auto;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* Present Mode Interactive Styles */
.interactive-btn {
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.interactive-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border-color: #8b5cf6;
  background: white;
}

/* Post-Click Feedback States */
.selected-btn {
  background-color: #eff6ff;
  border-color: #3b82f6;
  transform: scale(1.02);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.correct-btn {
  background-color: #d1fae5;
  border-color: #10b981;
  color: #065f46;
  transform: scale(1.02);
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15);
  z-index: 5;
}

.incorrect-btn {
  background-color: #fee2e2;
  border-color: #ef4444;
  color: #991b1b;
  opacity: 0.9;
}

.disabled-btn {
  opacity: 0.5;
  filter: grayscale(100%);
  pointer-events: none;
}

.edit-mode-btn {
  cursor: default;
}

.locked-mode-btn {
  cursor: not-allowed;
  opacity: 0.8;
}

/* Control Bar */
.quiz-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
  margin-top: 10px;
  padding-top: 15px;
  border-top: 1px dashed #e5e7eb;
}

.ctrl-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid transparent;
}

.btn-start {
  background-color: #f3e8ff;
  color: #7e22ce;
  border-color: #d8b4fe;
}
.btn-start:hover { background-color: #e9d5ff; }

.btn-submit {
  background-color: #dbeafe;
  color: #1d4ed8;
  border-color: #93c5fd;
}
.btn-submit:hover { background-color: #bfdbfe; }

.btn-reset {
  background-color: #e0f2fe;
  color: #0369a1;
  border-color: #bae6fd;
}
.btn-reset:hover { background-color: #bae6fd; }

.btn-lock {
  background-color: #f3f4f6;
  color: #4b5563;
  border-color: #e5e7eb;
}
.btn-lock:hover { background-color: #e5e7eb; }

.feedback-msg {
  font-weight: 600;
  font-size: 14px;
}
.msg-correct { color: #059669; }
.msg-incorrect { color: #dc2626; }
</style>
