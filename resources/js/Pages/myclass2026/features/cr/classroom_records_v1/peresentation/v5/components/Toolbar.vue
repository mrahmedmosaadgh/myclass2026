<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePaste } from '../composables/usePaste';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import { useGameStore } from '../stores/gameStore';
import { useLiveQuestionStore } from '../stores/liveQuestionStore';

const { createTextElement, createMathElement, createImageElement, createHTMLElement, createRectangleElement } = usePaste();
const presentation = usePresentationStore();
const ui = useUIStore();
const gameStore = useGameStore();
const liveQuestionStore = useLiveQuestionStore();

const fileInput = ref(null);
const isSaving = ref(false);
const showAddElementDropdown = ref(false);
const showInteractiveDropdown = ref(false);
const showAIUtilitiesDropdown = ref(false);

const saveStatusIcon = computed(() => {
  if (isSaving.value) return '⏳';
  if (presentation.saveStatus === 'saving') return '💾';
  if (presentation.saveStatus === 'error') return '❌';
  return '✅';
});

const saveStatusText = computed(() => {
  if (isSaving.value) return 'Saving...';
  if (presentation.saveStatus === 'saving') return 'Saving...';
  if (presentation.saveStatus === 'error') return 'Error';
  return 'Saved';
});

async function savePresentation() {
  isSaving.value = true;
  try {
    await presentation.saveCurrentPresentation();
  } catch (error) {
    console.error('Save failed:', error);
  } finally {
    isSaving.value = false;
  }
}

function openManagePage() {
  router.visit(route('classroom-records.presentation.manage'));
}

function addText() {
  createTextElement('New Text');
}

function addMath() {
  createMathElement();
}

function addRectangle() {
  createRectangleElement();
}

function addLeaderboard() {
  const lbBlock = {
    id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
    type: 'leaderboard',
    x: 50,
    y: 50,
    width: 900,
    height: 600,
    zIndex: presentation.slides[presentation.currentSlideIndex].elements.length + 1,
    visibilityOption: 'always-visible',
    isVisible: true,
  };
  presentation.addElement(lbBlock);
}

function addLiveQuestion() {
  const lqBlock = {
    id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
    type: 'live-question',
    x: 50,
    y: 50,
    width: 800,
    height: 400,
    zIndex: presentation.slides[presentation.currentSlideIndex].elements.length + 1,
    visibilityOption: 'always-visible',
    isVisible: true,
    data: {
      questionTitle: 'Enter your question here...',
      questionInstructions: 'Provide your answer below',
      timeLimit: null,
      sessionCode: null,
      isActive: false,
      responses: []
    }
  };
  presentation.addElement(lqBlock);
}

function triggerImageUpload() {
  if (fileInput.value) {
    fileInput.value.click();
  }
}

function handleFileUpload(e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = () => {
    createImageElement(reader.result);
  };
  reader.readAsDataURL(file);
  e.target.value = ''; // reset so the same file can be selected again
}

function addHtml() {
  createHTMLElement();
}

async function handlePasteBtn() {
  try {
    const clipboardItems = await navigator.clipboard.read();
    for (const clipboardItem of clipboardItems) {
      if (clipboardItem.types.some(type => type.startsWith('image/'))) {
        const imageType = clipboardItem.types.find(type => type.startsWith('image/'));
        const blob = await clipboardItem.getType(imageType);
        const reader = new FileReader();
        reader.onload = () => createImageElement(reader.result);
        reader.readAsDataURL(blob);
        return;
      }
      if (clipboardItem.types.includes('text/html')) {
        const blob = await clipboardItem.getType('text/html');
        const html = await blob.text();
        createHTMLElement(html);
        return;
      }
      if (clipboardItem.types.includes('text/plain')) {
        const blob = await clipboardItem.getType('text/plain');
        const text = await blob.text();
        createTextElement(text);
        return;
      }
    }
  } catch (err) {
    console.warn('Clipboard read failed, attempting fallback to text. Error:', err.message);
    try {
      const text = await navigator.clipboard.readText();
      if (text) {
        createTextElement(text);
      } else {
        alert('Clipboard is empty or inaccessible.');
      }
    } catch (e) {
      alert('Please press Ctrl+V / Cmd+V to paste. Safari/Firefox may restrict clipboard button access.');
    }
  }
}

function exportJson() {
  const payload = {
    title: presentation.title || "Untitled Presentation",
    description: presentation.description || "",
    usePhases: presentation.usePhases,
    slides: presentation.slides,
    groups: gameStore.groups,
    gameSettings: gameStore.gameSettings,
    questionHistory: gameStore.questionHistory
  };
  const jsonString = JSON.stringify(payload, null, 2);
  const blob = new Blob([jsonString], { type: "application/json" });
  const url = window.URL.createObjectURL(blob);
  
  const downloadAnchorNode = document.createElement('a');
  downloadAnchorNode.setAttribute("href", url);
  
  const safeFilename = payload.title.replace(/[^a-z0-9]/gi, '_').toLowerCase() || 'presentation';
  downloadAnchorNode.setAttribute("download", `${safeFilename}_v5.json`);
  document.body.appendChild(downloadAnchorNode);
  downloadAnchorNode.click();
  downloadAnchorNode.remove();
  
  // Clean up memory
  window.URL.revokeObjectURL(url);
}

function importJson() {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'application/json';
  input.onchange = e => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = fileEvent => {
      let parsedData;
      try {
        parsedData = JSON.parse(fileEvent.target.result);
      } catch(err) {
        console.error('Parse Error:', err);
        alert('Error reading file. The file is corrupted or severely broken.\\n(If this is an old exported file prior to the fix, it is permanently corrupted. Please generate a new export file.)');
        return;
      }

      try {
        if (Array.isArray(parsedData) || (parsedData && Array.isArray(parsedData.slides))) {
          presentation.loadPresentation(parsedData);
          
          if (parsedData.groups) {
            gameStore.groups = parsedData.groups;
          }
          if (parsedData.gameSettings) {
            gameStore.gameSettings = parsedData.gameSettings;
          }
          if (parsedData.questionHistory) {
            gameStore.questionHistory = parsedData.questionHistory;
          }
          
          ui.clearSelection();
        } else {
          alert('Invalid file format. Not a recognized presentation payload.');
        }
      } catch(err) {
        console.error('Memory Load Error:', err);
        alert('Error parsing JSON backup file. Details embedded in console: ' + err.message);
      }
    };
    reader.readAsText(file);
  };
  input.click();
}
function confirmReset() {
  if (confirm("Start a new presentation?\n\nTip: You may want to export your current presentation as JSON before clearing it.\n\nClick OK to clear all data and start fresh.")) {
    presentation.resetPresentation();
    ui.clearSelection();
  }
}

function handleClickOutside(event) {
  if (!event.target.closest('.add-element-dropdown')) {
    showAddElementDropdown.value = false;
  }
  if (!event.target.closest('.interactive-dropdown')) {
    showInteractiveDropdown.value = false;
  }
  if (!event.target.closest('.ai-utilities-dropdown')) {
    showAIUtilitiesDropdown.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div class="toolbar">
    <!-- Save and Manage Section -->
    <button @click="savePresentation" :disabled="isSaving" class="save-btn" title="Save Presentation (Ctrl+S)">
      <span class="save-icon">{{ saveStatusIcon }}</span>
      <span class="save-text">{{ saveStatusText }}</span>
    </button>

    <button @click="openManagePage" class="manage-btn" title="Manage All Presentations">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 3h7v7H3z"></path>
        <path d="M14 3h7v7h-7z"></path>
        <path d="M14 14h7v7h-7z"></path>
        <path d="M3 14h7v7H3z"></path>
      </svg>
      Manage
    </button>

    <!-- Divider -->
    <div class="divider"></div>

    <button @click="presentation.addSlide" title="Add New Slide">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="12" y1="8" x2="12" y2="16"></line>
        <line x1="8" y1="12" x2="16" y2="12"></line>
      </svg>
      Slide
    </button>
    
    <!-- Divider -->
    <div class="divider"></div>

    <!-- Add Element Dropdown -->
    <div class="add-element-dropdown" @click.stop>
      <button @click="showAddElementDropdown = !showAddElementDropdown" 
              class="add-element-btn" 
              title="Add Element">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="16"></line>
          <line x1="8" y1="12" x2="16" y2="12"></line>
        </svg>
        Add Element
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'rotate-180': showAddElementDropdown }">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>
      
      <div v-if="showAddElementDropdown" class="dropdown-menu">
        <button @click="addText; showAddElementDropdown = false" title="Add Text">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"></polyline><line x1="9" y1="20" x2="15" y2="20"></line><line x1="12" y1="4" x2="12" y2="20"></line></svg>
          Text
        </button>
        
        <button @click="addMath; showAddElementDropdown = false" title="Add Math Formula">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 5h-7l-3 14H4"></path><path d="M14 10h5"></path><path d="M14 14h5"></path></svg>
          Math
        </button>

        <button @click="triggerImageUpload; showAddElementDropdown = false" title="Upload Image from Device">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
          Image
        </button>
        
        <button @click="addRectangle; showAddElementDropdown = false" title="Add Shape (Rectangle)">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
          Shape
        </button>

        <button @click="addHtml; showAddElementDropdown = false" title="Add HTML Block">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
          HTML
        </button>
        
        <button @click="addLeaderboard; showAddElementDropdown = false" title="Add Live Leaderboard Slide">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6M6 20V10M18 20V4"></path></svg>
          Podium
        </button>
      </div>
    </div>
    
    <input type="file" ref="fileInput" accept="image/*" style="display: none" @change="handleFileUpload">

    <!-- Interactive Tools Dropdown -->
    <div class="interactive-dropdown" @click.stop>
      <button @click="showInteractiveDropdown = !showInteractiveDropdown" 
              class="interactive-btn" 
              title="Interactive Tools">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          <circle cx="9" cy="10" r="1"></circle>
          <circle cx="15" cy="10" r="1"></circle>
          <circle cx="12" cy="14" r="1"></circle>
        </svg>
        Interactive
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'rotate-180': showInteractiveDropdown }">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>
      
      <div v-if="showInteractiveDropdown" class="dropdown-menu">
        <button @click="addLiveQuestion; showInteractiveDropdown = false" title="Add Live Question to Slide">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="13" x2="15" y2="13"></line></svg>
          Live Q
        </button>
        
        <button @click="gameStore.isLeaderboardOpen = true; showInteractiveDropdown = false" title="Live Display Leaderboard">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg>
          Leaderboard
        </button>

        <button @click="gameStore.isGroupSetupOpen = true; showInteractiveDropdown = false" title="Configure Classroom Groups">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          Groups
        </button>

        <button @click="ui.isGroupQuizGeneratorOpen = true; showInteractiveDropdown = false" title="Generate Interactive Group Quiz (V3)">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
          Group Quiz
        </button>

        <button @click="liveQuestionStore.openPanel(); showInteractiveDropdown = false" title="Create Live Question Session">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h2.5a.5.5 0 0 0 .5-.5v-2A2.5 2.5 0 0 1 9.5 3h0A2.5 2.5 0 0 1 12 5.5v2a.5.5 0 0 0 .5.5H15a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2V8.4"></path><path d="M7 15h.01"></path><path d="M11 15h.01"></path><path d="M15 15h.01"></path><path d="M19 15h.01"></path></svg>
          Live Question
        </button>
      </div>
    </div>

    <!-- AI & Utilities Dropdown -->
    <div class="ai-utilities-dropdown" @click.stop>
      <button @click="showAIUtilitiesDropdown = !showAIUtilitiesDropdown" 
              class="ai-utilities-btn" 
              title="AI & Utilities">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="10" rx="2"></rect>
          <circle cx="12" cy="5" r="2"></circle>
          <path d="M12 7v4"></path>
          <line x1="8" y1="16" x2="8" y2="16"></line>
          <line x1="16" y1="16" x2="16" y2="16"></line>
        </svg>
        AI & Tools
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'rotate-180': showAIUtilitiesDropdown }">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>
      
      <div v-if="showAIUtilitiesDropdown" class="dropdown-menu">
        <button @click="handlePasteBtn; showAIUtilitiesDropdown = false" title="Paste Guide">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
          Paste
        </button>

        <button @click="ui.isAIPasteDialogOpen = true; showAIUtilitiesDropdown = false" title="Paste Standard AI Generation">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><circle cx="12" cy="5" r="2"></circle><path d="M12 7v4"></path><line x1="8" y1="16" x2="8" y2="16"></line><line x1="16" y1="16" x2="16" y2="16"></line></svg>
          AI Paste
        </button>
      </div>
    </div>
    
    <div class="divider"></div>

    <div class="save-status" :title="presentation.saveStatus === 'saved' ? 'All changes saved locally' : 'Saving...'">
      <div class="status-indicator" :class="presentation.saveStatus"></div>
      <span class="status-text">{{ presentation.saveStatus === 'saved' ? 'Saved' : 'Saving...' }}</span>
    </div>

    <div class="divider"></div>

    <button @click="exportJson" title="Export Presentation as JSON">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
      Export
    </button>
    
    <button @click="importJson" title="Import Presentation from JSON Backup">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
      Import
    </button>
    <button @click="confirmReset" title="Start a New Presentation">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
      Reset
    </button>
  </div>
</template>

<style scoped>
.toolbar {
  display: flex;
  gap: 10px;
  background: white;
  padding: 10px 15px;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  margin-bottom: 20px;
  justify-content: center;
  align-items: center;
}

.divider {
  width: 1px;
  height: 32px;
  background-color: #e5e7eb;
  margin: 0 5px;
}

.toolbar button {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  background: transparent;
  color: #4b5563;
  border: none;
  border-radius: 6px;
  padding: 8px 12px;
  cursor: pointer;
  font-size: 12px;
  transition: background 0.2s, color 0.2s;
}

.toolbar button:hover {
  background: #f3f4f6;
  color: #111827;
}

.toolbar button svg {
  color: #6366f1;
}

@media (max-width: 768px) {
  .toolbar {
    flex-wrap: wrap;
    gap: 8px;
    padding: 8px;
  }
  .toolbar button {
    padding: 6px;
    min-width: 50px;
  }
}

.save-status {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  min-width: 54px;
  padding: 0 4px;
}

.status-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-top: 4px;
  margin-bottom: 2px;
}

.status-indicator.saved {
  background-color: #10b981;
}

.status-indicator.saving {
  background-color: #f59e0b;
  animation: pulse 1s infinite;
}

.status-text {
  font-size: 11px;
  color: #6b7280;
  font-weight: 500;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

/* Save and Manage Buttons */
.save-btn {
  background: #10b981 !important;
  color: white !important;
  font-weight: 500;
  min-width: 80px;
}

.save-btn:hover {
  background: #059669 !important;
}

.save-btn:disabled {
  background: #9ca3af !important;
  cursor: not-allowed;
}

.save-icon {
  font-size: 16px;
}

.save-text {
  font-size: 11px;
}

.manage-btn {
  background: #3b82f6 !important;
  color: white !important;
  font-weight: 500;
}

.manage-btn:hover {
  background: #2563eb !important;
}

.manage-btn svg {
  color: white !important;
}

/* Add Element Dropdown Styles */
.add-element-dropdown {
  position: relative;
  display: inline-block;
}

.add-element-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #6366f1 !important;
  color: white !important;
  font-weight: 500;
  padding: 8px 12px !important;
  border-radius: 6px;
  transition: background 0.2s;
}

.add-element-btn:hover {
  background: #4f46e5 !important;
}

.add-element-btn svg {
  color: white !important;
}

.add-element-btn svg:last-child {
  transition: transform 0.2s;
}

.rotate-180 {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  margin-top: 4px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.1);
  z-index: 1000;
  min-width: 140px;
  overflow: hidden;
}

.dropdown-menu button {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 10px 12px;
  background: transparent;
  color: #374151;
  border: none;
  text-align: left;
  font-size: 13px;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
}

.dropdown-menu button:hover {
  background: #f3f4f6;
  color: #111827;
}

.dropdown-menu button svg {
  color: #6366f1;
  flex-shrink: 0;
}

/* Interactive Tools Dropdown Styles */
.interactive-dropdown {
  position: relative;
  display: inline-block;
}

.interactive-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #10b981 !important;
  color: white !important;
  font-weight: 500;
  padding: 8px 12px !important;
  border-radius: 6px;
  transition: background 0.2s;
}

.interactive-btn:hover {
  background: #059669 !important;
}

.interactive-btn svg {
  color: white !important;
}

.interactive-btn svg:last-child {
  transition: transform 0.2s;
}

/* AI & Utilities Dropdown Styles */
.ai-utilities-dropdown {
  position: relative;
  display: inline-block;
}

.ai-utilities-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #8b5cf6 !important;
  color: white !important;
  font-weight: 500;
  padding: 8px 12px !important;
  border-radius: 6px;
  transition: background 0.2s;
}

.ai-utilities-btn:hover {
  background: #7c3aed !important;
}

.ai-utilities-btn svg {
  color: white !important;
}

.ai-utilities-btn svg:last-child {
  transition: transform 0.2s;
}
</style>
