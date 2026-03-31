<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { useQuasar } from 'quasar';
import EditableMath from './EditableMath.vue';
import { useGameStore } from '../stores/gameStore';
import { Html5QrcodeScanner, Html5Qrcode } from 'html5-qrcode';

const props = defineProps({
  element: Object,
  isEditMode: Boolean
});

const emit = defineEmits(['update', 'select']);
const gameStore = useGameStore();
const $q = useQuasar();

const isMuted = ref(false);
const isInteractive = ref(false); // Controls if the buttons accept clicks
const activeGroupId = ref(null); // The group currently selected by teacher to answer
const isQrScanning = ref(false); // State for QR overlay
const isPracticeMode = ref(false); // Practice mode bypasses group selection
let html5QrCode = null;

// Simple Web Audio API helper
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
    osc.type = 'sine';
    osc.frequency.setValueAtTime(400, now);
    osc.frequency.exponentialRampToValueAtTime(800, now + 0.1);
    gainNode.gain.setValueAtTime(0.1, now);
    gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.3);
    osc.start(now);
    osc.stop(now + 0.3);
  } else if (type === 'incorrect') {
    osc.type = 'sawtooth';
    osc.frequency.setValueAtTime(250, now);
    osc.frequency.exponentialRampToValueAtTime(150, now + 0.2);
    gainNode.gain.setValueAtTime(0.1, now);
    gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.3);
    osc.start(now);
    osc.stop(now + 0.3);
  }
}

// State Accessors
const qHistory = computed(() => {
  return gameStore.questionHistory[props.element.id] || { groupAnswers: {}, status: 'locked_in' };
});

const isGraded = computed(() => qHistory.value.status === 'graded');

// Interaction Handlers
function toggleMute() { isMuted.value = !isMuted.value; }

function handleOptionHover() {
  if (!props.isEditMode && isInteractive.value && activeGroupId.value && !isGraded.value) {
    playSound('hover');
  }
}

function handleOptionClick(optId) {
  if (props.isEditMode || !isInteractive.value || isGraded.value) return;
  
  if (isPracticeMode.value) {
    // Just toggle the badge for practice (using a fake 'practice' ID or similar)
    gameStore.logGroupAnswer(props.element.id, 'practice-mode', optId);
    playSound('hover');
    return;
  }

  if (!activeGroupId.value) return; // Must select a group to assign to
  
  gameStore.logGroupAnswer(props.element.id, activeGroupId.value, optId);
  playSound('hover'); // Confirm assignment
  activeGroupId.value = null; // Clear so next group can be selected
}

function getGroupsOnOption(optId) {
  const ans = qHistory.value.groupAnswers;
  const groupsHere = Object.keys(ans).filter(gId => ans[gId] === optId);
  return groupsHere.map(gId => gameStore.groups.find(g => g.id === gId)).filter(Boolean);
}

function selectGroup(groupId) {
  if (isGraded.value) return;
  activeGroupId.value = activeGroupId.value === groupId ? null : groupId; // Toggle
}

function removeGroupAnswer(groupId) {
  if (isGraded.value) return;
  gameStore.clearGroupAnswer(props.element.id, groupId);
}

// Control Bar Handlers
function startQuiz() {
  isInteractive.value = true;
}

function lockQuiz() {
  isInteractive.value = false;
  isPracticeMode.value = false;
  activeGroupId.value = null;
}

function startPractice() {
  isInteractive.value = true;
  isPracticeMode.value = true;
  activeGroupId.value = null;
}

function gradeGroups() {
  if (isGraded.value) return;
  
  const ansObj = qHistory.value.groupAnswers;
  const answeredGroupIds = Object.keys(ansObj);
  
  if (answeredGroupIds.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'No groups have answered yet!',
      position: 'top'
    });
    return;
  }

  // Check for groups that haven't answered
  const missingGroups = gameStore.groups.filter(g => !answeredGroupIds.includes(g.id.toString()));
  if (missingGroups.length > 0) {
    const missingNames = missingGroups.map(g => g.name).join(', ');
    
    $q.dialog({
      title: 'Missing Answers',
      message: `Wait! The following groups haven't answered yet: <strong>${missingNames}</strong>.<br><br>Do you want to proceed and grade anyway?`,
      html: true,
      cancel: true,
      persistent: true,
      ok: { label: 'Grade Anyway', color: 'negative', flat: true },
      cancel: { label: 'Wait for them', color: 'primary' }
    }).onOk(() => {
      executeGrading(answeredGroupIds, ansObj);
    });
  } else {
    executeGrading(answeredGroupIds, ansObj);
  }
}

function executeGrading(answeredGroupIds, ansObj) {
  let anyWrong = false;
  let anyRight = false;

  answeredGroupIds.forEach(gId => {
     const optId = ansObj[gId];
     if (optId === props.element.questionData.correctId) {
         gameStore.updateGroupScore(gId, gameStore.gameSettings.correctPoints);
         anyRight = true;
     } else {
         if (gameStore.gameSettings.allowNegativeScore) {
             gameStore.updateGroupScore(gId, gameStore.gameSettings.wrongPoints || -5);
         }
         anyWrong = true;
     }
  });

  gameStore.questionHistory[props.element.id] = { ...qHistory.value, status: 'graded' };
  
  if (anyRight) playSound('correct');
  else playSound('incorrect');
}

function shuffleOptions() {
  const newOptions = [...props.element.questionData.options];
  for (let i = newOptions.length - 1; i > 0; i--) {
     const j = Math.floor(Math.random() * (i + 1));
     [newOptions[i], newOptions[j]] = [newOptions[j], newOptions[i]];
  }
  emit('update', { questionData: { ...props.element.questionData, options: newOptions } });
}

function replayQuiz() {
  gameStore.questionHistory[props.element.id] = { groupAnswers: {}, status: 'locked_in' };
  isInteractive.value = false;
  isPracticeMode.value = false;
  activeGroupId.value = null;
}

// QR Scanner Logic
async function toggleQrScanner() {
  if (isQrScanning.value) {
     stopQrScanner();
     return;
  }
  isQrScanning.value = true;
  
  // Wait for the modal DOM element to render
  setTimeout(() => {
    html5QrCode = new Html5Qrcode("qr-reader-" + props.element.id);
    
    const qrConfig = {
      fps: 10,
      qrbox: { width: 250, height: 250 }
    };

    const onScanSuccess = (decodedText) => {
      const targetGroup = gameStore.groups.find(g => g.id == decodedText || g.name.toLowerCase() === decodedText.toLowerCase());
      if (targetGroup && !isGraded.value) {
         playSound('hover');
         activeGroupId.value = targetGroup.id;
      }
    };

    // Attempt to start with environment camera first, fallback if not found (for desktops)
    html5QrCode.start({ facingMode: "environment" }, qrConfig, onScanSuccess)
      .catch(err => {
          console.log("Environment scanner failed, trying default camera...", err);
          return html5QrCode.start({ facingMode: "user" }, qrConfig, onScanSuccess);
      })
      .catch(err => {
          console.warn("QR Scanner Start Error:", err);
          $q.notify({
            type: 'negative',
            message: 'Camera not found or blocked. Check permissions.',
            position: 'top'
          });
          isQrScanning.value = false;
      });
  }, 100);
}

function stopQrScanner() {
  if (html5QrCode) {
     html5QrCode.stop().then(() => {
        html5QrCode.clear();
        html5QrCode = null;
     }).catch(err => console.warn(err));
  }
  isQrScanning.value = false;
}

onUnmounted(() => {
  stopQrScanner();
});

// Edit Handlers
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
function handleWrapperClick(e) {
  e.stopPropagation();
  emit('select');
}

// Visual Helpers
function getOptionClass(optId) {
  if (props.isEditMode) return 'edit-mode-btn';
  
  if (isGraded.value) {
    if (optId === props.element.questionData.correctId) return 'correct-btn';
    // If a group picked it and it's wrong, highlight it as a wrong choice
    const groupsHere = getGroupsOnOption(optId);
    if (groupsHere.length > 0) return 'incorrect-btn';
    return 'disabled-btn';
  }

  // Not graded
  if (!isInteractive.value) return 'locked-mode-btn';
  
  // If active group selected, make it glow if they can click it
  if (activeGroupId.value || isPracticeMode.value) return 'assignable-btn';
  
  return 'interactive-btn';
}
</script>

<template>
  <div class="mcq-wrapper" @mousedown="handleWrapperClick" @touchstart="handleWrapperClick">
    
    <button v-if="!isEditMode" class="mute-toggle" @click.stop="toggleMute" :title="isMuted ? 'Unmute Sounds' : 'Mute Sounds'">
      <svg v-if="isMuted" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5L6 9H2v6h4l5 4V5z"></path><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path></svg>
    </button>

    <div class="v3-layout">
      <!-- Main Quiz Area -->
      <div class="quiz-primary">
        <div class="question-header">
          <EditableMath :content="element.questionData.question" :isEditMode="isEditMode" @update="updateQuestion" @select="emit('select')" />
        </div>

        <div class="options-grid">
          <div v-for="(opt, index) in element.questionData.options" :key="opt.id" class="option-container">
            <div v-if="isEditMode" class="edit-radio-wrapper">
              <input type="radio" :name="'g-correct-' + element.id" :checked="element.questionData.correctId === opt.id" @change="setCorrectId(opt.id)" title="Mark as correct answer">
            </div>

            <div class="option-btn" :class="getOptionClass(opt.id)" @mouseenter="handleOptionHover" @click.stop="handleOptionClick(opt.id)">
              <div style="flex: 1; max-width: 100%; min-width: 0; overflow-x: auto; overflow-y: hidden;">
                <EditableMath :content="opt.text" :isEditMode="isEditMode" @update="(v) => updateOption(index, v)" @select="emit('select')" />
              </div>
              
              <!-- Group Badges on Options -->
              <div v-if="!isEditMode" class="group-badges">
                 <span v-for="g in getGroupsOnOption(opt.id)" :key="g.id" class="g-badge" :style="{ backgroundColor: g.color }">
                    {{ g.name }}
                 </span>
                 
                 <!-- Feedback icon if graded -->
                 <div v-if="isGraded && opt.id === element.questionData.correctId" class="feedback-icon" style="color: #059669; margin-left: 8px;">
                   <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                 </div>
                 <div v-else-if="isGraded && getGroupsOnOption(opt.id).length > 0" class="feedback-icon" style="color: #dc2626; margin-left: 8px;">
                   <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                 </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Instructor Sidebar (Groups) -->
      <div v-if="!isEditMode && isInteractive" class="group-sidebar">
         <div class="sidebar-header">
           <h3>👥 Select Group</h3>
           <button @click.stop="toggleQrScanner" class="qr-btn" :class="{ 'qr-active': isQrScanning }" title="Scan Group QR Card">
             <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
           </button>
         </div>
         <div class="sidebar-groups">
           <div 
              v-for="g in gameStore.groups" 
              :key="g.id" 
              class="sidebar-group" 
              :class="{ 'g-active': activeGroupId === g.id, 'g-answered': !!qHistory.groupAnswers[g.id] }"
              @click="selectGroup(g.id)"
           >
              <div class="g-dot" :style="{ backgroundColor: g.color }"></div>
              <span class="g-name">{{ g.name }}</span>
              
              <button v-if="qHistory.groupAnswers[g.id] && !isGraded" class="clear-g-btn" @click.stop="removeGroupAnswer(g.id)">✕</button>
              
              <template v-else-if="qHistory.groupAnswers[g.id] && isGraded">
                <div class="g-result-info">
                   <span v-if="qHistory.groupAnswers[g.id] === element.questionData.correctId" class="g-points pos-pts">+{{ gameStore.gameSettings.correctPoints }}</span>
                   <span v-else class="g-points neg-pts">{{ gameStore.gameSettings.allowNegativeScore ? (gameStore.gameSettings.wrongPoints || -5) : 0 }}</span>
                   
                   <span v-if="qHistory.groupAnswers[g.id] === element.questionData.correctId" class="graded-mark correct-mark">✓</span>
                   <span v-else class="graded-mark wrong-mark">✕</span>
                </div>
              </template>

               <div v-else-if="!isGraded && isInteractive" class="status-icon-wrapper">
                  <img v-if="activeGroupId === g.id" src="/icon/solve/download (3).png" class="status-img pulse" title="Group is solving..." width="20" height="20" />
                  <img v-else-if="!qHistory.groupAnswers[g.id]" src="/icon/solve/download.png" class="status-img grayscale" title="Not solving yet" width="20" height="20" />
                  <img v-else src="/icon/solve/download (1).png" class="status-img" title="Answered!" width="20" height="20" />
               </div>
            </div>
          </div>
        </div>
      </div>

    <!-- Instructor Control Bar -->
    <div v-if="!isEditMode" class="quiz-controls">
      <template v-if="!isInteractive && !isGraded">
        <button class="ctrl-btn btn-start" @click.stop="startQuiz">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
          Unlock Group Interaction
        </button>
        
        <button class="ctrl-btn btn-secondary" @click.stop="shuffleOptions">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 3 21 3 21 8"></polyline><line x1="4" y1="20" x2="21" y2="3"></line><polyline points="21 16 21 21 16 21"></polyline><line x1="15" y1="15" x2="21" y2="21"></line><line x1="4" y1="4" x2="9" y2="9"></line></svg>
          Shuffle Options
        </button>

        <button class="ctrl-btn btn-practice" @click.stop="startPractice">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
          Just Practice
        </button>
      </template>

      <template v-else-if="isInteractive && !isGraded">
        <span class="instructor-hint" v-if="isPracticeMode">Practice Mode: Interaction is for demonstration only (scores not saved).</span>
        <span class="instructor-hint" v-else-if="activeGroupId">Click an Option to assign it to the selected group...</span>
        <span class="instructor-hint" v-else>Click a Group on the right, then click their answer option.</span>
        
        <div style="flex:1"></div>
        
        <button class="ctrl-btn btn-grade" @click.stop="gradeGroups" :disabled="Object.keys(qHistory.groupAnswers).length === 0">
           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
           Grade Answers
        </button>

        <button class="ctrl-btn btn-lock" @click.stop="lockQuiz">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          Lock
        </button>
      </template>

      <template v-else-if="isGraded">
        <div class="graded-notice">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4l3 3"></path></svg>
          Archived to History
        </div>
        
        <div style="flex:1"></div>
        
        <button class="ctrl-btn btn-secondary" @click.stop="replayQuiz">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><polyline points="23 20 23 14 17 14"></polyline><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 0 1 3.51 15"></path></svg>
          Reset Quiz
        </button>
      </template>
    </div>

    <!-- QR Reader Floating Modal -->
    <div v-if="isQrScanning" class="qr-modal-backdrop" @click.stop="stopQrScanner">
       <div class="qr-modal" @click.stop>
          <div class="qr-modal-header">
             <h4>Scan Group Card</h4>
             <button @click="stopQrScanner">✕</button>
          </div>
          <div :id="'qr-reader-' + element.id" class="qr-reader-viewport"></div>
          <div class="qr-status" v-if="activeGroupId">
             Target Selected: <strong>{{ gameStore.groups.find(g => g.id === activeGroupId)?.name }}</strong>
          </div>
       </div>
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
  overflow: hidden;
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
  z-index: 10;
}
.mute-toggle:hover { color: #4b5563; }

.v3-layout {
  display: flex;
  flex: 1;
  gap: 20px;
  min-height: 0;
}

.quiz-primary {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

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

.options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  flex: 1;
  margin-top: 10px;
  overflow-y: auto;
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
.edit-radio-wrapper input { transform: scale(1.5); cursor: pointer; }

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

.group-badges {
  margin-left: auto;
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  align-items: center;
  justify-content: flex-end;
}
.g-badge {
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: bold;
  white-space: nowrap;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

/* Sidebar Styles */
.group-sidebar {
  width: 220px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px;
  display: flex;
  flex-direction: column;
}
.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 6px;
  margin-bottom: 10px;
}
.sidebar-header h3 {
  margin: 0;
  font-size: 0.95rem;
  color: #334155;
}
.qr-btn {
  background: #e2e8f0;
  border: none;
  border-radius: 6px;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #475569;
  transition: all 0.2s;
}
.qr-btn:hover { background: #cbd5e1; color: #0f172a; }
.qr-active { background: #3b82f6; color: white; }

.sidebar-groups {
  display: flex;
  flex-direction: column;
  gap: 6px;
  overflow-y: auto;
}
.sidebar-group {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px;
  background: #ffffff;
  border: 2px solid transparent;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  color: #475569;
  transition: all 0.2s;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.sidebar-group:hover { border-color: #cbd5e1; }
.g-active { border-color: #3b82f6; background: #eff6ff; color: #1d4ed8; }
.g-answered { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
.g-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
.g-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.clear-g-btn {
  background: #fee2e2;
  color: #ef4444;
  border: none;
  border-radius: 4px;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  cursor: pointer;
}
.g-result-info {
  display: flex;
  align-items: center;
  gap: 6px;
}
.g-points {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 2px 4px;
  border-radius: 4px;
}
.pos-pts { background: #d1fae5; color: #065f46; }
.neg-pts { background: #fee2e2; color: #991b1b; }
.graded-mark { font-weight: bold; font-size: 1rem; }
.correct-mark { color: #10b981; }
.wrong-mark { color: #ef4444; }

/* Status Icons */
.status-icon-wrapper {
  margin-left: auto;
  display: flex;
  align-items: center;
}
.status-img {
  object-fit: contain;
}
.pulse {
  animation: pulse-animation 2s infinite;
}
.grayscale {
  filter: grayscale(100%);
  opacity: 0.3;
}
@keyframes pulse-animation {
  0% { transform: scale(0.95); opacity: 0.8; }
  50% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(0.95); opacity: 0.8; }
}

/* Button Modes */
.assignable-btn { cursor: copy; border-color: #93c5fd; }
.assignable-btn:hover { background: #eff6ff; box-shadow: 0 0 0 2px #bfdbfe; }
.locked-mode-btn { opacity: 0.8; }
.edit-mode-btn { cursor: default; }

.correct-btn {
  background-color: #d1fae5;
  border-color: #10b981;
  color: #065f46;
  z-index: 5;
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
}
.incorrect-btn {
  background-color: #fee2e2;
  border-color: #ef4444;
  color: #991b1b;
}
.disabled-btn { opacity: 0.5; filter: grayscale(100%); }

/* Controls */
.quiz-controls {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 15px;
  margin-top: 10px;
  padding-top: 15px;
  border-top: 1px dashed #e5e7eb;
}
.instructor-hint {
  font-size: 0.85rem;
  color: #64748b;
  font-style: italic;
}
.graded-notice {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.9rem;
  color: #059669;
  font-weight: 600;
  background: #d1fae5;
  padding: 6px 12px;
  border-radius: 6px;
  margin: 0 auto;
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
.btn-start { background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe; }
.btn-start:hover { background-color: #e9d5ff; }
.btn-grade { background-color: #dcfce3; color: #15803d; border-color: #86efac; }
.btn-grade:hover:not(:disabled) { background: #bbf7d0; }
.btn-grade:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-lock { background-color: #f3f4f6; color: #4b5563; border-color: #e5e7eb; }
.btn-lock:hover { background-color: #e5e7eb; }
.btn-secondary { background-color: #f1f5f9; color: #334155; border-color: #cbd5e1; }
.btn-secondary:hover { background-color: #e2e8f0; }

.btn-practice { background-color: #ecfdf5; color: #065f46; border-color: #6ee7b7; }
.btn-practice:hover { background-color: #d1fae5; }

/* QR Modal */
.qr-modal-backdrop {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(15,23,42,0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  backdrop-filter: blur(4px);
}
.qr-modal {
  background: white;
  width: 320px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
  display: flex;
  flex-direction: column;
}
.qr-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}
.qr-modal-header h4 { margin: 0; color: #0f172a; font-size: 1rem; }
.qr-modal-header button { background: transparent; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b; }
.qr-reader-viewport {
  width: 100%;
  aspect-ratio: 1; /* Square camera feed */
  background: black;
}
.qr-status {
  padding: 12px;
  background: #dcfce3;
  color: #15803d;
  text-align: center;
  font-size: 0.9rem;
}
</style>
