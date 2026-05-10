# Group Quiz Feature - Improvement Tasks

## Executive Summary

**Current State:** ~3,500 lines across 7 files implementing teacher-driven group quiz with QR scanning, AI generation, and leaderboard visualization.

**Core Gap:** No student-facing digital submission interface. All interactions are teacher-controlled (teacher selects group + clicks answer on their screen). QR codes are physical workarounds, not digital solutions.

**Files Analyzed:**
- `stores/gameStore.js` (157 lines) - State management
- `GroupSetupModal.vue` (876 lines) - Group CRUD & QR printing
- `InteractiveGroupMCQ.vue` (1,191 lines) - Teacher MCQ interaction
- `GroupQuizGenerator.vue` (565 lines) - AI prompt builder
- `PrintGroupQrCodes.vue` (309 lines) - Standalone QR print page
- `composables/useAIPaste.js` (132 lines) - Element generator
- `LeaderboardSlide.vue` (290 lines) - ECharts visualization

---

## Priority Matrix

| Priority | Issue | Impact | Effort | Files Affected |
|----------|-------|--------|--------|----------------|
| **P0** | No student submission interface | Critical UX gap | High | 5+ files |
| **P0** | Direct state mutations break reactivity | Data integrity | Low | 2 files |
| **P1** | Practice mode answers leak into grading | Scoring accuracy | Low | 2 files |
| **P1** | Scoring not configurable in UI | Feature completeness | Medium | 3 files |
| **P2** | QR code logic duplicated 3x | Maintainability | Medium | 3 files |
| **P2** | No persistent storage | Data loss risk | Medium | 2 files |
| **P3** | No per-group undo | Teacher workflow | Medium | 2 files |
| **P3** | Scanner has no visual buffer | Usability | Low | 1 file |
| **P3** | No mobile layout | Accessibility | Medium | 2 files |
| **P4** | Event listener cleanup risk | Edge case bug | Low | 1 file |
| **P4** | QR payload format fragile | Error handling | Low | 2 files |
| **P4** | No inline question editing | Workflow friction | Medium | 1 file |
| **P4** | Leaderboard slide has no title | Polish | Low | 1 file |

---

## Detailed Task Breakdown

### **P0-1: Student Digital Submission Interface** ⚡ CRITICAL
**Problem:** Entire flow is teacher-driven. No way for students to submit answers from their devices.

**Current Workflow:**
1. Teacher unlocks quiz
2. Teacher selects group manually
3. Teacher clicks answer on their screen
4. OR teacher scans physical QR code from student

**Desired Workflow:**
1. Teacher unlocks quiz
2. Students see question on their devices (via remote session)
3. Students select answer on their device
4. Answer auto-submits to teacher's screen
5. Teacher grades when ready

**Implementation Plan:**

**Files to Create:**
- `remote/components/student/GroupQuizCard.vue` - Student-facing MCQ interface

**Files to Modify:**
- `stores/gameStore.js` - Add student submission handlers
- `remote/StudentInteract.vue` - Route group-mcq elements to GroupQuizCard
- `InteractiveGroupMCQ.vue` - Listen for remote submissions
- `composables/useRemoteSession.js` (if exists) - Add quiz submission events

**Technical Details:**
```javascript
// gameStore.js - Add to store
function handleStudentSubmission(signal) {
  if (signal.event === 'GROUP_ANSWER_SUBMIT') {
    const { elementId, groupId, optionId, studentId } = signal.context;
    logGroupAnswer(elementId, groupId, optionId);
    // Emit to teacher UI for visual feedback
  }
}

// StudentInteract.vue - Add element type handler
if (element.type === 'group-mcq' && isInteractive) {
  return h(GroupQuizCard, {
    element,
    groupId: currentStudent.groupId,
    onSubmit: (optionId) => {
      remoteSession.sendSignal('GROUP_ANSWER_SUBMIT', {
        elementId: element.id,
        groupId: currentStudent.groupId,
        optionId,
        studentId: currentStudent.id
      });
    }
  });
}
```

**Acceptance Criteria:**
- [ ] Students can see group-mcq questions on their devices
- [ ] Students can select and submit answers
- [ ] Teacher sees real-time submission badges on options
- [ ] Submissions work alongside existing QR scanner
- [ ] Practice mode disables student submissions
- [ ] Graded questions disable student submissions

**Estimated Effort:** 6-8 hours

---

### **P0-2: Fix Direct State Mutations** 🐛 BUG
**Problem:** `GroupSetupModal.vue:33` directly mutates `group.score = Number(val)` breaking Vue 3 reactivity.

**Current Code:**
```vue
<!-- GroupSetupModal.vue:30-35 -->
function handleScoreChange(groupId, val) {
  const group = gameStore.groups.find(g => g.id === groupId);
  if (group) {
    group.score = Number(val) || 0; // ❌ Direct mutation
  }
}
```

**Fix:**
```javascript
// gameStore.js - Add action
function setGroupScore(id, newScore) {
  const group = groups.value.find(g => g.id === id);
  if (group) group.score = Number(newScore) || 0;
}

// GroupSetupModal.vue - Use action
function handleScoreChange(groupId, val) {
  gameStore.setGroupScore(groupId, val);
}
```

**Files to Modify:**
- `stores/gameStore.js` - Add `setGroupScore` action
- `GroupSetupModal.vue` - Replace direct mutation with action call

**Acceptance Criteria:**
- [ ] Score changes trigger reactivity in leaderboard
- [ ] No direct mutations to `groups.value` array items
- [ ] All computed properties update correctly

**Estimated Effort:** 30 minutes

---

### **P1-1: Practice Mode Answer Leak** 🐛 BUG
**Problem:** Practice answers stored in `questionHistory` get graded if teacher locks without clearing.

**Current Flow:**
```javascript
// InteractiveGroupMCQ.vue:133-137
function startPractice() {
  isInteractive.value = true;
  isPracticeMode.value = true;
  activeGroupId.value = null;
  // ❌ Doesn't clear questionHistory
}

// Line 127-131
function lockQuiz() {
  isInteractive.value = false;
  isPracticeMode.value = false; // ❌ Clears flag but not data
  activeGroupId.value = null;
}
```

**Fix Options:**

**Option A: Clear on Lock (Recommended)**
```javascript
function lockQuiz() {
  isInteractive.value = false;
  isPracticeMode.value = false;
  activeGroupId.value = null;
  // Clear practice answers
  if (qHistory.value.groupAnswers['practice-mode']) {
    delete qHistory.value.groupAnswers['practice-mode'];
  }
}
```

**Option B: Separate Practice History**
```javascript
// gameStore.js
const practiceHistory = ref({});

function logPracticeAnswer(elementId, optionId) {
  if (!practiceHistory.value[elementId]) {
    practiceHistory.value[elementId] = [];
  }
  practiceHistory.value[elementId].push(optionId);
}
```

**Files to Modify:**
- `InteractiveGroupMCQ.vue` - Clear practice data on lock
- `stores/gameStore.js` - (Optional) Add practice history separation

**Acceptance Criteria:**
- [ ] Practice answers don't affect real grading
- [ ] Locking quiz clears practice state
- [ ] Practice mode shows visual indicator
- [ ] Can switch practice → lock → grade without data leak

**Estimated Effort:** 1 hour

---

### **P1-2: Configurable Scoring UI** ✨ FEATURE
**Problem:** 
- `correctPoints = 10` hardcoded, never exposed in UI
- `wrongPoints = -5` hardcoded in grading logic (line 186)
- `allowNegativeScore` toggle exists but wrong points value not configurable

**Current State:**
```javascript
// gameStore.js:5-9
const gameSettings = ref({
  correctPoints: 10,        // ❌ Never shown in UI
  wrongPoints: 0,           // ❌ Fallback, not used
  allowNegativeScore: false // ✅ Has toggle
});

// InteractiveGroupMCQ.vue:185-187
if (gameStore.gameSettings.allowNegativeScore) {
  gameStore.updateGroupScore(gId, gameStore.gameSettings.wrongPoints || -5);
  // ❌ Hardcoded -5
}
```

**Implementation:**

**1. Add Settings UI in GroupSetupModal**
```vue
<!-- GroupSetupModal.vue - Add new tab -->
<q-tab name="scoring" label="Scoring" icon="calculate" />

<q-tab-panel name="scoring">
  <div class="scoring-settings">
    <h3>Point Values</h3>
    
    <label class="input-block">
      <span>Points for Correct Answer</span>
      <input 
        type="number" 
        :value="gameStore.gameSettings.correctPoints"
        @input="gameStore.gameSettings.correctPoints = Number($event.target.value)"
        min="1"
        max="100"
      />
    </label>
    
    <q-toggle
      v-model="gameStore.gameSettings.allowNegativeScore"
      label="Enable Negative Scoring"
    />
    
    <label 
      v-if="gameStore.gameSettings.allowNegativeScore"
      class="input-block"
    >
      <span>Points for Wrong Answer (negative)</span>
      <input 
        type="number" 
        :value="gameStore.gameSettings.wrongPoints"
        @input="gameStore.gameSettings.wrongPoints = Number($event.target.value)"
        max="0"
        step="1"
      />
    </label>
  </div>
</q-tab-panel>
```

**2. Fix Grading Logic**
```javascript
// InteractiveGroupMCQ.vue:185-187
if (gameStore.gameSettings.allowNegativeScore) {
  const penalty = gameStore.gameSettings.wrongPoints || -5;
  gameStore.updateGroupScore(gId, penalty);
}
```

**3. Future: Per-Question Points**
```javascript
// Element schema enhancement
const groupMcqBlock = {
  type: 'group-mcq',
  questionData: {
    question: '...',
    options: [...],
    correctId: 'A',
    customPoints: {        // ✨ New
      correct: 15,         // Override global
      wrong: -3            // Override global
    }
  }
};
```

**Files to Modify:**
- `GroupSetupModal.vue` - Add scoring tab with inputs
- `InteractiveGroupMCQ.vue` - Use `gameSettings.wrongPoints` instead of hardcoded -5
- `stores/gameStore.js` - Update `wrongPoints` default to -5

**Acceptance Criteria:**
- [ ] Correct points configurable (1-100)
- [ ] Wrong points configurable when negative scoring enabled
- [ ] Settings persist during session
- [ ] Grading uses configured values
- [ ] UI shows current settings clearly

**Estimated Effort:** 2-3 hours

---

### **P2-1: Extract QR Code Print Logic** ♻️ REFACTOR
**Problem:** Same A4 QR layout (~250 lines) duplicated in 3 files:
- `GroupSetupModal.vue` (QR tab)
- `InteractiveGroupMCQ.vue` (inline modal)
- `PrintGroupQrCodes.vue` (standalone page)

**Solution: Create Composable**

**New File: `composables/useQrPrint.js`**
```javascript
import { ref, computed } from 'vue';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';

export function useQrPrint(groups) {
  const printAreaRef = ref(null);
  const a4PreviewScale = ref(1);
  const isPrintQrOpen = ref(false);
  
  const printOptionIds = computed(() => ['A', 'B', 'C', 'D']);
  
  function getQrPayload(groupId, optId) {
    const g = String(groupId);
    const groupToken = g.toLowerCase().startsWith('g') 
      ? g 
      : `g${g}`;
    return `${groupToken}_${String(optId).toLowerCase()}`;
  }
  
  function updateA4PreviewScale() {
    if (!printAreaRef.value) return;
    const containerWidth = printAreaRef.value.parentElement?.offsetWidth || 800;
    const a4Width = 794; // A4 width in pixels at 96 DPI
    a4PreviewScale.value = Math.min(containerWidth / a4Width, 1);
  }
  
  async function downloadPdf() {
    if (!printAreaRef.value) return;
    
    const canvas = await html2canvas(printAreaRef.value, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff'
    });
    
    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('p', 'mm', 'a4');
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = pdf.internal.pageSize.getHeight();
    
    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
    pdf.save(`group-qr-codes-${Date.now()}.pdf`);
  }
  
  function openPrintDialog() {
    window.print();
  }
  
  return {
    printAreaRef,
    a4PreviewScale,
    isPrintQrOpen,
    printOptionIds,
    getQrPayload,
    updateA4PreviewScale,
    downloadPdf,
    openPrintDialog
  };
}
```

**New Component: `components/QrPrintSheet.vue`**
```vue
<script setup>
import { onMounted, onUnmounted } from 'vue';
import QrCode from '@/Components/Common/QrCode.vue';

const props = defineProps({
  groups: Array,
  printAreaRef: Object,
  a4PreviewScale: Number,
  printOptionIds: Array,
  getQrPayload: Function
});

const emit = defineEmits(['update-scale']);

onMounted(() => {
  emit('update-scale');
  window.addEventListener('resize', () => emit('update-scale'));
});

onUnmounted(() => {
  window.removeEventListener('resize', () => emit('update-scale'));
});
</script>

<template>
  <div 
    ref="printAreaRef"
    class="qr-print-sheet"
    :style="{ transform: `scale(${a4PreviewScale})` }"
  >
    <div class="qr-header">
      <h1>Group QR Codes</h1>
      <p>Scan to submit answers</p>
    </div>
    
    <div class="qr-grid">
      <div 
        v-for="group in groups" 
        :key="group.id"
        class="qr-group-block"
      >
        <div class="qr-group-header" :style="{ backgroundColor: group.color }">
          {{ group.name }}
        </div>
        
        <div class="qr-codes-row">
          <div 
            v-for="optId in printOptionIds" 
            :key="optId"
            class="qr-code-cell"
          >
            <QrCode 
              :value="getQrPayload(group.id, optId)"
              :size="120"
            />
            <div class="qr-label">{{ optId }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
```

**Files to Modify:**
- Create `composables/useQrPrint.js`
- Create `components/QrPrintSheet.vue`
- Refactor `GroupSetupModal.vue` - Use composable
- Refactor `InteractiveGroupMCQ.vue` - Use composable
- Refactor `PrintGroupQrCodes.vue` - Use composable

**Acceptance Criteria:**
- [ ] All 3 locations use shared composable
- [ ] PDF download works identically
- [ ] Print dialog works identically
- [ ] A4 scaling works correctly
- [ ] No visual regressions

**Estimated Effort:** 3-4 hours

---

### **P2-2: Add Persistent Storage** 💾 FEATURE
**Problem:** All state in-memory only. Page refresh = data loss.

**Implementation: LocalStorage + Auto-Save**

**1. Add Storage Composable**
```javascript
// composables/useGamePersistence.js
import { watch } from 'vue';

export function useGamePersistence(gameStore) {
  const STORAGE_KEY = 'presentation-v7-game-state';
  
  function saveState() {
    try {
      const state = {
        groups: gameStore.groups,
        gameSettings: gameStore.gameSettings,
        questionHistory: gameStore.questionHistory,
        timestamp: Date.now()
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (err) {
      console.warn('Failed to save game state:', err);
    }
  }
  
  function loadState() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      
      const state = JSON.parse(raw);
      const age = Date.now() - (state.timestamp || 0);
      
      // Auto-expire after 24 hours
      if (age > 24 * 60 * 60 * 1000) {
        clearState();
        return null;
      }
      
      return state;
    } catch (err) {
      console.warn('Failed to load game state:', err);
      return null;
    }
  }
  
  function clearState() {
    localStorage.removeItem(STORAGE_KEY);
  }
  
  // Auto-save on changes (debounced)
  let saveTimeout;
  watch(
    () => [gameStore.groups, gameStore.questionHistory],
    () => {
      clearTimeout(saveTimeout);
      saveTimeout = setTimeout(saveState, 1000);
    },
    { deep: true }
  );
  
  return { saveState, loadState, clearState };
}
```

**2. Integrate in Store**
```javascript
// gameStore.js
import { useGamePersistence } from '../composables/useGamePersistence';

export const useGameStore = defineStore('presentation-game', () => {
  // ... existing state ...
  
  const persistence = useGamePersistence({
    groups,
    gameSettings,
    questionHistory
  });
  
  function restoreFromStorage() {
    const saved = persistence.loadState();
    if (saved) {
      groups.value = saved.groups || groups.value;
      gameSettings.value = saved.gameSettings || gameSettings.value;
      questionHistory.value = saved.questionHistory || {};
    }
  }
  
  function clearAllData() {
    resetScores();
    persistence.clearState();
  }
  
  return {
    // ... existing exports ...
    restoreFromStorage,
    clearAllData
  };
});
```

**3. Add UI Controls**
```vue
<!-- GroupSetupModal.vue - Add to setup tab -->
<div class="storage-controls">
  <q-btn 
    outline 
    color="primary"
    icon="save"
    label="Save Progress"
    @click="gameStore.saveState()"
  />
  
  <q-btn 
    outline 
    color="negative"
    icon="delete_forever"
    label="Clear All Data"
    @click="confirmClearData"
  />
</div>
```

**Files to Modify:**
- Create `composables/useGamePersistence.js`
- `stores/gameStore.js` - Add persistence integration
- `GroupSetupModal.vue` - Add save/clear buttons
- `Index.vue` or main entry - Call `restoreFromStorage()` on mount

**Acceptance Criteria:**
- [ ] State auto-saves on changes (debounced)
- [ ] State restores on page load
- [ ] Data expires after 24 hours
- [ ] Manual save button works
- [ ] Clear all data button works with confirmation
- [ ] No errors if localStorage unavailable

**Estimated Effort:** 2-3 hours

---

### **P3-1: Per-Group Undo** ✨ FEATURE
**Problem:** After grading, only option is "Reset Quiz" (wipes ALL). No per-group correction.

**Implementation:**

**1. Add Undo Action to Store**
```javascript
// gameStore.js
function undoGroupGrade(elementId, groupId) {
  const qData = questionHistory.value[elementId];
  if (!qData || qData.status !== 'graded') return;
  
  // Find what this group answered
  const answeredOption = qData.groupAnswers[groupId];
  if (!answeredOption) return;
  
  // Reverse the score change
  const element = /* get element from presentation store */;
  const wasCorrect = answeredOption === element.questionData.correctId;
  
  if (wasCorrect) {
    updateGroupScore(groupId, -gameSettings.value.correctPoints);
  } else if (gameSettings.value.allowNegativeScore) {
    const penalty = gameSettings.value.wrongPoints || -5;
    updateGroupScore(groupId, -penalty); // Reverse negative
  }
  
  // Remove answer
  delete qData.groupAnswers[groupId];
  
  // If no answers left, ungrade the question
  if (Object.keys(qData.groupAnswers).length === 0) {
    qData.status = 'locked_in';
  }
}
```

**2. Add UI in InteractiveGroupMCQ**
```vue
<!-- InteractiveGroupMCQ.vue - In group sidebar -->
<div 
  v-for="group in gameStore.groups"
  :key="group.id"
  class="group-item"
>
  <div class="group-info">
    <div class="group-name" :style="{ color: group.color }">
      {{ group.name }}
    </div>
    <div class="group-score">{{ group.score }} pts</div>
  </div>
  
  <!-- Show undo if graded and this group answered -->
  <q-btn
    v-if="isGraded && qHistory.groupAnswers[group.id]"
    flat
    dense
    round
    size="sm"
    icon="undo"
    color="warning"
    @click="undoGroup(group.id)"
  >
    <q-tooltip>Undo {{ group.name }}'s answer</q-tooltip>
  </q-btn>
</div>
```

**Files to Modify:**
- `stores/gameStore.js` - Add `undoGroupGrade` action
- `InteractiveGroupMCQ.vue` - Add undo buttons in group sidebar
- `InteractiveGroupMCQ.vue` - Add `undoGroup` method

**Acceptance Criteria:**
- [ ] Undo button appears for graded groups
- [ ] Clicking undo reverses score change
- [ ] Clicking undo removes answer badge
- [ ] If all answers undone, question becomes ungraded
- [ ] Undo works correctly for both positive and negative scoring

**Estimated Effort:** 2 hours

---

### **P3-2: Scanner Visual Buffer** 🎨 UX
**Problem:** Teachers type `g1_a` blindly with no visible feedback until Enter.

**Implementation:**

**1. Add Visible Input Field**
```vue
<!-- InteractiveGroupMCQ.vue - Add to control bar -->
<div v-if="isInteractive && !isGraded" class="scanner-input-group">
  <q-input
    ref="scannerInputRef"
    v-model="scannerBuffer"
    outlined
    dense
    label="QR Scanner Input"
    placeholder="Type or scan: g1_a"
    @keydown.enter="processScannerBuffer"
    @keydown.esc="scannerBuffer = ''"
  >
    <template v-slot:prepend>
      <q-icon name="qr_code_scanner" />
    </template>
    
    <template v-slot:append>
      <q-btn
        v-if="scannerBuffer"
        flat
        dense
        round
        icon="close"
        @click="scannerBuffer = ''"
      />
    </template>
  </q-input>
  
  <div v-if="scannerLastResult" class="scanner-result">
    {{ scannerLastResult }}
  </div>
</div>
```

**2. Update Scanner Logic**
```javascript
// InteractiveGroupMCQ.vue
const scannerBuffer = ref(''); // Make reactive
const scannerInputRef = ref(null);

function processScannerBuffer() {
  if (!scannerBuffer.value.trim()) return;
  handleScannedQr(scannerBuffer.value);
  scannerBuffer.value = ''; // Clear after processing
}

// Keep keyboard wedge support for hardware scanners
function handleGlobalScannerKeydown(e) {
  // ... existing checks ...
  
  // If scanner input is focused, let it handle naturally
  if (scannerInputRef.value?.$el?.contains(document.activeElement)) {
    return;
  }
  
  // Otherwise, accumulate in buffer for hardware scanners
  if (e.key === 'Enter') {
    processScannerBuffer();
  } else if (e.key.length === 1) {
    scannerBuffer.value += e.key;
  }
}
```

**Files to Modify:**
- `InteractiveGroupMCQ.vue` - Add visible input field
- `InteractiveGroupMCQ.vue` - Update scanner buffer logic

**Acceptance Criteria:**
- [ ] Visible input field shows typed characters
- [ ] Enter key processes input
- [ ] Escape key clears input
- [ ] Hardware scanners still work (keyboard wedge)
- [ ] Result feedback shows below input
- [ ] Input auto-focuses when quiz unlocked

**Estimated Effort:** 1-2 hours

---

### **P3-3: Mobile Responsive Layout** 📱 UX
**Problem:** Group sidebar (220px) + 2-column grid unusable on screens < 768px.

**Implementation:**

**1. Add Responsive Breakpoints**
```vue
<!-- InteractiveGroupMCQ.vue -->
<script setup>
import { useQuasar } from 'quasar';

const $q = useQuasar();
const isMobile = computed(() => $q.screen.lt.md); // < 1024px
const isTablet = computed(() => $q.screen.lt.lg && $q.screen.gt.sm);
</script>

<template>
  <div 
    class="group-mcq-container"
    :class="{
      'mobile-layout': isMobile,
      'tablet-layout': isTablet
    }"
  >
    <!-- Collapsible sidebar on mobile -->
    <div 
      v-if="!isMobile || showGroupSidebar"
      class="group-sidebar"
    >
      <!-- ... groups ... -->
    </div>
    
    <!-- Toggle button for mobile -->
    <q-btn
      v-if="isMobile"
      class="mobile-sidebar-toggle"
      fab
      color="primary"
      icon="groups"
      @click="showGroupSidebar = !showGroupSidebar"
    >
      <q-badge v-if="activeGroupId" color="orange" floating>1</q-badge>
    </q-btn>
    
    <!-- Single column on mobile -->
    <div 
      class="options-grid"
      :class="{
        'single-column': isMobile,
        'two-column': !isMobile
      }"
    >
      <!-- ... options ... -->
    </div>
  </div>
</template>

<style scoped>
.group-mcq-container {
  display: flex;
  gap: 20px;
}

.mobile-layout {
  flex-direction: column;
}

.mobile-layout .group-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 50vh;
  z-index: 1000;
  background: white;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.options-grid.single-column {
  grid-template-columns: 1fr;
}

.options-grid.two-column {
  grid-template-columns: repeat(2, 1fr);
}

.mobile-sidebar-toggle {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 999;
}
</style>
```

**Files to Modify:**
- `InteractiveGroupMCQ.vue` - Add responsive classes and mobile sidebar toggle
- `LeaderboardSlide.vue` - Adjust chart sizing for mobile

**Acceptance Criteria:**
- [ ] Single column layout on mobile (< 768px)
- [ ] Collapsible sidebar on mobile
- [ ] FAB button to toggle sidebar
- [ ] Touch-friendly button sizes (min 44px)
- [ ] Leaderboard chart scales correctly
- [ ] No horizontal scroll on any screen size

**Estimated Effort:** 3-4 hours

---

### **P4-1: Event Listener Cleanup** 🐛 BUG
**Problem:** Global `keydown` listener in `InteractiveGroupMCQ.vue:292` may fire during component destruction.

**Current Code:**
```javascript
onMounted(() => {
  window.addEventListener('keydown', handleGlobalScannerKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalScannerKeydown);
});
```

**Fix: Add Cleanup Guard**
```javascript
const isComponentMounted = ref(true);

function handleGlobalScannerKeydown(e) {
  if (!isComponentMounted.value) return; // ✅ Guard
  if (props.isEditMode) return;
  // ... rest of logic
}

onMounted(() => {
  isComponentMounted.value = true;
  window.addEventListener('keydown', handleGlobalScannerKeydown);
});

onUnmounted(() => {
  isComponentMounted.value = false;
  window.removeEventListener('keydown', handleGlobalScannerKeydown);
});
```

**Files to Modify:**
- `InteractiveGroupMCQ.vue` - Add mounted guard

**Acceptance Criteria:**
- [ ] No errors in console during rapid navigation
- [ ] Listener properly removed on unmount
- [ ] No memory leaks

**Estimated Effort:** 15 minutes

---

### **P4-2: Robust QR Payload Format** 🔒 ENHANCEMENT
**Problem:** Regex `g[a-z]?(\d+)[-_]([a-z])` breaks if group IDs contain letters. No checksum.

**Current Format:** `g1_a`, `g2_b`

**Enhanced Format:** `g1_a_c4` (checksum)

**Implementation:**
```javascript
// composables/useQrCodec.js
export function useQrCodec() {
  function generateChecksum(groupId, optionId) {
    const str = `${groupId}${optionId}`;
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      hash = ((hash << 5) - hash) + str.charCodeAt(i);
      hash = hash & hash; // Convert to 32bit integer
    }
    return Math.abs(hash).toString(36).slice(0, 2);
  }
  
  function encodePayload(groupId, optionId) {
    const checksum = generateChecksum(groupId, optionId);
    return `${groupId}_${optionId}_${checksum}`;
  }
  
  function decodePayload(raw) {
    const parts = raw.split('_');
    if (parts.length < 2) return null;
    
    const groupId = parts[0];
    const optionId = parts[1];
    const providedChecksum = parts[2];
    
    // Validate checksum if present
    if (providedChecksum) {
      const expectedChecksum = generateChecksum(groupId, optionId);
      if (providedChecksum !== expectedChecksum) {
        return { error: 'Invalid checksum', groupId, optionId };
      }
    }
    
    return { groupId, optionId };
  }
  
  return { encodePayload, decodePayload };
}
```

**Files to Modify:**
- Create `composables/useQrCodec.js`
- `GroupSetupModal.vue` - Use encoder
- `InteractiveGroupMCQ.vue` - Use decoder
- `PrintGroupQrCodes.vue` - Use encoder

**Acceptance Criteria:**
- [ ] QR codes include checksum
- [ ] Scanner validates checksum
- [ ] Invalid checksums show warning
- [ ] Backward compatible with old format (no checksum)
- [ ] Supports alphanumeric group IDs

**Estimated Effort:** 2 hours

---

### **P4-3: Inline Question Editing** ✨ FEATURE
**Problem:** AI-generated questions show in read-only preview. Must go back to AI if formatting wrong.

**Implementation:**

**1. Add Edit Mode to Preview**
```vue
<!-- GroupQuizGenerator.vue -->
<script setup>
const editingQuestionIndex = ref(null);

function startEditQuestion(index) {
  editingQuestionIndex.value = index;
}

function saveQuestionEdit(index) {
  editingQuestionIndex.value = null;
}

function deleteQuestion(index) {
  parsedQuestions.value.splice(index, 1);
}
</script>

<template>
  <div class="preview-section">
    <div 
      v-for="(q, idx) in parsedQuestions" 
      :key="idx"
      class="question-preview-card"
    >
      <!-- View Mode -->
      <div v-if="editingQuestionIndex !== idx">
        <div class="question-text">
          <EditableMath :content="q.question" :is-edit-mode="false" />
        </div>
        
        <div class="options-list">
          <div v-for="(opt, i) in q.options" :key="i">
            {{ String.fromCharCode(65 + i) }}) {{ opt }}
          </div>
        </div>
        
        <div class="correct-answer">
          ✓ {{ q.answer }}
        </div>
        
        <div class="card-actions">
          <q-btn 
            flat 
            dense 
            icon="edit" 
            @click="startEditQuestion(idx)"
          />
          <q-btn 
            flat 
            dense 
            icon="delete" 
            color="negative"
            @click="deleteQuestion(idx)"
          />
        </div>
      </div>
      
      <!-- Edit Mode -->
      <div v-else class="edit-mode">
        <q-input
          v-model="q.question"
          label="Question"
          outlined
          autogrow
        />
        
        <div 
          v-for="(opt, i) in q.options" 
          :key="i"
          class="option-edit"
        >
          <q-input
            v-model="q.options[i]"
            :label="`Option ${String.fromCharCode(65 + i)}`"
            outlined
          />
        </div>
        
        <q-select
          v-model="q.answer"
          :options="q.options"
          label="Correct Answer"
          outlined
        />
        
        <div class="edit-actions">
          <q-btn 
            flat 
            label="Save" 
            color="primary"
            @click="saveQuestionEdit(idx)"
          />
          <q-btn 
            flat 
            label="Cancel"
            @click="editingQuestionIndex = null"
          />
        </div>
      </div>
    </div>
  </div>
</template>
```

**Files to Modify:**
- `GroupQuizGenerator.vue` - Add edit mode to preview cards

**Acceptance Criteria:**
- [ ] Each question has edit button
- [ ] Edit mode shows editable inputs
- [ ] Can modify question text
- [ ] Can modify options
- [ ] Can change correct answer
- [ ] Can delete questions
- [ ] Changes persist when submitting to presentation

**Estimated Effort:** 2-3 hours

---

### **P4-4: Leaderboard Slide Title** 🎨 POLISH
**Problem:** `GroupQuizGenerator.vue:140` creates blank slide with just chart. No title.

**Current Code:**
```javascript
function appendLeaderboard() {
  presentation.addSlide(); // ❌ No title
  const lbBlock = { /* ... */ };
  presentation.addElement(lbBlock);
}
```

**Fix:**
```javascript
function appendLeaderboard() {
  const slideIndex = presentation.addSlide();
  const newSlide = presentation.slides[slideIndex];
  
  // Set slide title
  newSlide.title = '🏆 Final Standings';
  
  // Add decorative title element
  const titleBlock = {
    id: 'el-' + Date.now() + 'title',
    type: 'text',
    content: '# 🏆 Final Standings\n\nClassroom Group Results',
    x: 60,
    y: 20,
    width: 900,
    height: 80,
    zIndex: 0,
    visibilityOption: 'always-visible',
    isVisible: true,
    color: '#1f2937'
  };
  
  presentation.addElement(titleBlock);
  
  // Add leaderboard chart
  const lbBlock = {
    id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
    type: 'leaderboard',
    x: 60,
    y: 120, // Adjusted for title
    width: 900,
    height: 580, // Adjusted height
    zIndex: 1,
    visibilityOption: 'always-visible',
    isVisible: true,
  };
  
  presentation.addElement(lbBlock);
}
```

**Files to Modify:**
- `GroupQuizGenerator.vue` - Add title element when creating leaderboard slide

**Acceptance Criteria:**
- [ ] Leaderboard slide has title in slide list
- [ ] Title element appears above chart
- [ ] Chart repositioned to accommodate title
- [ ] No layout overlap

**Estimated Effort:** 30 minutes

---

## Implementation Roadmap

### **Phase 1: Critical Fixes (Week 1)**
- [ ] P0-1: Student Digital Submission Interface (6-8h)
- [ ] P0-2: Fix Direct State Mutations (30m)
- [ ] P1-1: Practice Mode Answer Leak (1h)

**Total: ~8-10 hours**

### **Phase 2: Core Features (Week 2)**
- [ ] P1-2: Configurable Scoring UI (2-3h)
- [ ] P2-2: Add Persistent Storage (2-3h)
- [ ] P3-1: Per-Group Undo (2h)

**Total: ~6-8 hours**

### **Phase 3: Refactoring & UX (Week 3)**
- [ ] P2-1: Extract QR Code Print Logic (3-4h)
- [ ] P3-2: Scanner Visual Buffer (1-2h)
- [ ] P3-3: Mobile Responsive Layout (3-4h)

**Total: ~7-10 hours**

### **Phase 4: Polish & Enhancements (Week 4)**
- [ ] P4-1: Event Listener Cleanup (15m)
- [ ] P4-2: Robust QR Payload Format (2h)
- [ ] P4-3: Inline Question Editing (2-3h)
- [ ] P4-4: Leaderboard Slide Title (30m)

**Total: ~5-6 hours**

---

## Testing Checklist

### **Functional Tests**
- [ ] Students can submit answers from devices
- [ ] QR scanner works with physical codes
- [ ] Practice mode doesn't affect real scores
- [ ] Grading calculates points correctly
- [ ] Undo reverses individual group scores
- [ ] Leaderboard updates in real-time
- [ ] Persistent storage survives refresh
- [ ] Mobile layout works on phones/tablets

### **Edge Cases**
- [ ] Grading with missing groups shows warning
- [ ] Scanning invalid QR shows error
- [ ] Multiple students in same group handled
- [ ] Negative scores work correctly
- [ ] Empty groups list handled gracefully
- [ ] Offline mode works without remote session

### **Performance**
- [ ] 50 groups render without lag
- [ ] Leaderboard chart animates smoothly
- [ ] QR code generation < 2s for 50 groups
- [ ] LocalStorage doesn't exceed quota

---

## Success Metrics

**Before:**
- ❌ No student submission interface
- ❌ Teacher must manually click for each group
- ❌ Data lost on refresh
- ❌ Scoring hardcoded
- ❌ No mobile support

**After:**
- ✅ Students submit from their devices
- ✅ Teacher grades when ready
- ✅ Data persists across sessions
- ✅ Configurable point values
- ✅ Responsive mobile layout
- ✅ Per-group undo capability
- ✅ Visual scanner feedback
- ✅ Maintainable QR code logic

---

## Notes

- **Remote Session Infrastructure:** Existing `remote/StudentInteract.vue` and `remote/components/student/` can be leveraged for P0-1
- **Quasar Components:** Check `app.js` for globally imported components before adding local imports
- **Vue 3 Reactivity:** Always use store actions for mutations, never direct `.value` array item changes
- **Backward Compatibility:** Maintain QR code format compatibility during P4-2 migration
- **Mobile First:** Test P3-3 on real devices, not just browser DevTools

---

**Total Estimated Effort:** 26-34 hours across 4 weeks
**Priority Focus:** P0 and P1 tasks unlock core functionality
**Quick Wins:** P0-2 (30m), P4-1 (15m), P4-4 (30m)
