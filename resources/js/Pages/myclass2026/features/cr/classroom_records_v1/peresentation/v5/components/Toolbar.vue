<script setup>
import { ref } from 'vue';
import { usePaste } from '../composables/usePaste';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import { useGameStore } from '../stores/gameStore';

const { createTextElement, createMathElement, createImageElement, createHTMLElement, createRectangleElement } = usePaste();
const presentation = usePresentationStore();
const ui = useUIStore();
const gameStore = useGameStore();

const fileInput = ref(null);

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
  downloadAnchorNode.setAttribute("download", `${safeFilename}_v4.json`);
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
</script>

<template>
  <div class="toolbar">
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

    <button @click="addText" title="Add Text">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"></polyline><line x1="9" y1="20" x2="15" y2="20"></line><line x1="12" y1="4" x2="12" y2="20"></line></svg>
      Text
    </button>
    
    <button @click="addMath" title="Add Math Formula">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 5h-7l-3 14H4"></path><path d="M14 10h5"></path><path d="M14 14h5"></path></svg>
      Math
    </button>

    <button @click="triggerImageUpload" title="Upload Image from Device">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
      Image
    </button>
    
    <input type="file" ref="fileInput" accept="image/*" style="display: none" @change="handleFileUpload">

    <button @click="addRectangle" title="Add Shape (Rectangle)">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
      Shape
    </button>

    <button @click="addHtml" title="Add HTML Block">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
      HTML
    </button>
    
    <button @click="addLeaderboard" title="Add Live Leaderboard Slide">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6M6 20V10M18 20V4"></path></svg>
      Podium
    </button>
    
    <button @click="handlePasteBtn" title="Paste Guide">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
      Paste
    </button>
    
    <div class="divider"></div>

    <button @click="gameStore.isLeaderboardOpen = true" title="Live Display Leaderboard">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg>
      Leaderboard
    </button>

    <button @click="gameStore.isGroupSetupOpen = true" title="Configure Classroom Groups">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
      Groups
    </button>

    <button @click="ui.isGroupQuizGeneratorOpen = true" title="Generate Interactive Group Quiz (V3)">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
      Group Quiz
    </button>

    <button @click="ui.isAIPasteDialogOpen = true" title="Paste Standard AI Generation">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><circle cx="12" cy="5" r="2"></circle><path d="M12 7v4"></path><line x1="8" y1="16" x2="8" y2="16"></line><line x1="16" y1="16" x2="16" y2="16"></line></svg>
      AI Paste
    </button>
    
    <div class="divider"></div>

    <button @click="exportJson" title="Export Presentation as JSON">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
      Export
    </button>
    
    <button @click="importJson" title="Import Presentation from JSON Backup">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
      Import
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
</style>
