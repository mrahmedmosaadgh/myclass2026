<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePaste } from '../composables/usePaste';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import { useGameStore } from '../stores/gameStore';
import { useLiveQuestionStore } from '../stores/liveQuestionStore';

const { createTextElement, createMathElement, createMarkdownElement, createImageElement, createImageElementOriginal, createHTMLElement, createRectangleElement } = usePaste();
const presentation = usePresentationStore();
const ui = useUIStore();
const gameStore = useGameStore();
const liveQuestionStore = useLiveQuestionStore();

const fileInput = ref(null);
const isSaving = ref(false);
const showShareLinkModal = ref(false);
const isCopyingLink = ref(false);
const showSlidesJsonModal = ref(false);
const slidesJsonText = ref('');
const slidesJsonError = ref('');
const slidesJsonSuccess = ref('');

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

const studentJoinUrl = computed(() => {
  if (!gameStore.accessCode) return '';
  const baseUrl = window.location.origin;
  return `${baseUrl}/classroom-records/presentation/remote/student?code=${gameStore.accessCode}`;
});

function generateSessionCode() {
  const chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // No I, O, 0, 1 to avoid confusion
  let code = '';
  for (let i = 0; i < 6; i++) {
    code += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return code;
}

function ensureAccessCode() {
  if (!gameStore.accessCode) {
    gameStore.accessCode = generateSessionCode();
    gameStore.sessionStatus = 'waiting';
  }
}

async function pasteImageOriginalSize({ newSlide = false } = {}) {
  try {
    const clipboardItems = await navigator.clipboard.read();
    for (const clipboardItem of clipboardItems) {
      if (clipboardItem.types.some(type => type.startsWith('image/'))) {
        const imageType = clipboardItem.types.find(type => type.startsWith('image/'));
        const blob = await clipboardItem.getType(imageType);
        const reader = new FileReader();
        reader.onload = () => createImageElementOriginal(reader.result, { newSlide });
        reader.readAsDataURL(blob);
        return;
      }
    }
    alert('No image found in clipboard.');
  } catch (err) {
    console.warn('Clipboard image read failed:', err?.message || err);
    alert('Clipboard access blocked. Try using Ctrl+V / Cmd+V inside the slide, or use browser permissions.');
  }
}

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

function addMarkdown() {
  createMarkdownElement('## Title\n\nType **Markdown** tables and LaTeX like: $\\frac{2}{3}$ or $$\\frac{2}{3}$$');
}

function addRectangle() {
  createRectangleElement();
}

function startDrawRectangle() {
  ui.toggleDrawRectangleMode();
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

function openShareLinkModal() {
  ensureAccessCode();
  showShareLinkModal.value = true;
}

async function copyCurrentSlideJson() {
  slidesJsonError.value = '';
  slidesJsonSuccess.value = '';
  try {
    const text = presentation.getCurrentSlideJson(true);
    if (!text) {
      slidesJsonError.value = 'No current slide to copy.';
      return;
    }
    await navigator.clipboard.writeText(text);
    slidesJsonSuccess.value = 'Copied current slide JSON.';
  } catch (e) {
    slidesJsonError.value = 'Copy failed. Your browser may block clipboard access.';
  }
}

function openSlidesJsonModal() {
  slidesJsonText.value = '';
  slidesJsonError.value = '';
  slidesJsonSuccess.value = '';
  showSlidesJsonModal.value = true;
}

function pasteSlidesJsonApply() {
  slidesJsonError.value = '';
  slidesJsonSuccess.value = '';
  const result = presentation.appendSlidesFromJson(slidesJsonText.value, { insertAfterCurrent: true });
  if (!result.ok) {
    slidesJsonError.value = result.error || 'Invalid slide JSON.';
    return;
  }
  slidesJsonSuccess.value = `Added ${result.added} slide(s).`;
  slidesJsonText.value = '';
}

async function copyShareLink() {
  isCopyingLink.value = true;
  try {
    await navigator.clipboard.writeText(studentJoinUrl.value);
  } catch (err) {
    console.error('Failed to copy link:', err);
    // Fallback for older browsers
    const textArea = document.createElement('textarea');
    textArea.value = studentJoinUrl.value;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
  } finally {
    isCopyingLink.value = false;
  }
}
</script>

<template>
  <q-toolbar class="toolbar-fixed">
    <q-btn color="positive" :label="saveStatusText" :disable="isSaving" @click="savePresentation" no-caps dense class="q-mr-sm" />

    <q-btn color="primary" label="Manage" @click="openManagePage" no-caps dense class="q-mr-sm" />

    <q-separator vertical class="q-mx-sm" />

    <q-btn flat label="Slide" @click="presentation.addSlide" no-caps dense />
    <q-btn flat :label="ui.isPagesView ? 'Pages (On)' : 'Pages'" @click="ui.togglePagesView()" :color="ui.isPagesView ? 'primary' : undefined" no-caps dense />

    <q-separator vertical class="q-mx-sm" />

    <q-btn-dropdown color="primary" label="Add" no-caps dense>
      <q-list dense style="min-width: 220px">
        <q-item clickable v-close-popup @click="addText"><q-item-section>Text</q-item-section></q-item>
        <q-item clickable v-close-popup @click="addMath"><q-item-section>Math</q-item-section></q-item>
        <q-item clickable v-close-popup @click="addMarkdown"><q-item-section>Markdown</q-item-section></q-item>
        <q-item clickable v-close-popup @click="triggerImageUpload"><q-item-section>Image (Upload)</q-item-section></q-item>
        <q-item clickable v-close-popup @click="addRectangle"><q-item-section>Shape (Rect)</q-item-section></q-item>
        <q-item clickable v-close-popup @click="startDrawRectangle"><q-item-section>Draw Rect</q-item-section></q-item>
        <q-item clickable v-close-popup @click="addLeaderboard"><q-item-section>Podium</q-item-section></q-item>
      </q-list>
    </q-btn-dropdown>

    <input type="file" ref="fileInput" accept="image/*" style="display: none" @change="handleFileUpload">

    <q-btn-dropdown color="secondary" label="Interactive" no-caps dense class="q-ml-sm">
      <q-list dense style="min-width: 240px">
        <q-item clickable v-close-popup @click="addLiveQuestion"><q-item-section>Live Q (Element)</q-item-section></q-item>
        <q-item clickable v-close-popup @click="() => { gameStore.isLeaderboardOpen = true; }"><q-item-section>Leaderboard</q-item-section></q-item>
        <q-item clickable v-close-popup @click="() => { gameStore.isGroupSetupOpen = true; }"><q-item-section>Groups</q-item-section></q-item>
        <q-item clickable v-close-popup @click="() => { ui.isGroupQuizGeneratorOpen = true; }"><q-item-section>Group Quiz</q-item-section></q-item>
        <q-item clickable v-close-popup @click="() => { liveQuestionStore.openPanel(); }"><q-item-section>Live Question</q-item-section></q-item>
        <q-separator />
        <q-item>
          <q-item-section>
            <q-toggle
              :model-value="ui.useGroupQuizV2"
              @update:model-value="ui.toggleGroupQuizV2()"
              label="Use V2 Modern UI"
              color="primary"
              dense
            />
          </q-item-section>
        </q-item>
      </q-list>
    </q-btn-dropdown>

    <q-btn-dropdown color="accent" label="Tools" no-caps dense class="q-ml-sm">
      <q-list dense style="min-width: 260px">
        <q-item clickable v-close-popup @click="handlePasteBtn"><q-item-section>Paste</q-item-section></q-item>
        <q-item clickable v-close-popup @click="() => pasteImageOriginalSize({ newSlide: false })"><q-item-section>Paste Img (Original)</q-item-section></q-item>
        <q-item clickable v-close-popup @click="() => pasteImageOriginalSize({ newSlide: true })"><q-item-section>Paste Img (New Slide)</q-item-section></q-item>
        <q-item clickable v-close-popup @click="() => { ui.isAIPasteDialogOpen = true; }"><q-item-section>AI Paste</q-item-section></q-item>
        <q-separator />
        <q-item clickable v-close-popup @click="openSlidesJsonModal"><q-item-section>Slides JSON</q-item-section></q-item>
      </q-list>
    </q-btn-dropdown>

    <q-space />

    <q-btn color="positive" label="Share" @click="openShareLinkModal" no-caps dense class="q-mr-sm" />

    <div class="save-status" :title="presentation.saveStatus === 'saved' ? 'All changes saved locally' : 'Saving...'">
      <div class="status-indicator" :class="presentation.saveStatus"></div>
      <span class="status-text">{{ presentation.saveStatus === 'saved' ? 'Saved' : 'Saving...' }}</span>
    </div>

    <q-separator vertical class="q-mx-sm" />

    <q-btn-dropdown color="grey-8" label="File" no-caps dense>
      <q-list dense style="min-width: 220px">
        <q-item clickable v-close-popup @click="exportJson"><q-item-section>Export</q-item-section></q-item>
        <q-item clickable v-close-popup @click="importJson"><q-item-section>Import</q-item-section></q-item>
        <q-separator />
        <q-item clickable v-close-popup @click="confirmReset"><q-item-section class="text-negative">Reset</q-item-section></q-item>
      </q-list>
    </q-btn-dropdown>
  </q-toolbar>

  <q-dialog v-model="showShareLinkModal">
    <q-card style="width: 560px; max-width: 95vw;">
      <q-card-section class="row items-center">
        <div class="text-h6">Share Presentation</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <div class="q-mb-sm">Students can use this link to join your presentation</div>

        <div class="q-mb-md" style="display:flex; gap:12px; align-items:center;">
          <div style="min-width: 120px; color:#6b7280; font-weight:600;">Session Code</div>
          <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-weight:800; letter-spacing: 0.12em; font-size: 18px;">{{ gameStore.accessCode }}</div>
        </div>

        <q-input
          :model-value="studentJoinUrl"
          readonly
          outlined
          dense
          label="Student Join URL"
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn color="primary" :loading="isCopyingLink" label="Copy Link" @click="copyShareLink" no-caps />
        <q-btn flat label="Close" v-close-popup no-caps />
      </q-card-actions>
    </q-card>
  </q-dialog>

  <q-dialog v-model="showSlidesJsonModal">
    <q-card style="width: 720px; max-width: 95vw;">
      <q-card-section class="row items-center">
        <div class="text-h6">Slides JSON</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <div class="q-mb-sm">Copy the current slide as JSON, or paste one slide object / an array of slides to add.</div>

        <div class="row q-col-gutter-sm q-mb-md">
          <div class="col-auto">
            <q-btn color="primary" label="Copy Current Slide" @click="copyCurrentSlideJson" no-caps />
          </div>
        </div>

        <q-input
          v-model="slidesJsonText"
          type="textarea"
          autogrow
          outlined
          label="Paste slide JSON"
          :hint="'One slide object {..} OR array [{..},{..}]'"
          input-style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;"
        />

        <div v-if="slidesJsonError" class="q-mt-sm" style="color:#ef4444; font-weight:600;">{{ slidesJsonError }}</div>
        <div v-if="slidesJsonSuccess" class="q-mt-sm" style="color:#10b981; font-weight:600;">{{ slidesJsonSuccess }}</div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn color="primary" label="Add Slides" @click="pasteSlidesJsonApply" :disable="!slidesJsonText.trim()" no-caps />
        <q-btn flat label="Close" v-close-popup no-caps />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<style scoped>
.toolbar-fixed {
  position: sticky;
  top: 80px;
  left: 0;
  right: 0;
  z-index: 1100;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(229, 231, 235, 0.7);
  padding: 8px 16px;
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
</style>
