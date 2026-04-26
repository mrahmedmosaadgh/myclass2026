<script setup>
import { computed, nextTick, ref } from 'vue';
import { useGameStore } from '../stores/gameStore';
import QrCode from '@/Components/Common/QrCode.vue';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';

const gameStore = useGameStore();
const newGroupName = ref('');
const activeTab = ref('setup'); // 'setup' | 'qrcodes'
const printAreaRef = ref(null);
const a4PreviewScale = ref(1);

const showGroupsJsonDialog = ref(false);
const groupsJsonText = ref('');
const groupsJsonErrors = ref([]);
const groupsJsonSuccess = ref('');

function close() {
  gameStore.isGroupSetupOpen = false;
}

function handleAddGroup() {
  if (newGroupName.value.trim()) {
    gameStore.addGroup(newGroupName.value.trim());
    newGroupName.value = '';
  }
}

function handleScoreChange(groupId, val) {
  const group = gameStore.groups.find(g => g.id === groupId);
  if (group) {
    group.score = Number(val) || 0;
  }
}

function normalizeGroupId(id, takenIds) {
  const raw = String(id ?? '').trim();
  const base = raw ? (raw.toLowerCase().startsWith('g') ? raw : `g${raw}`) : `g${Date.now()}`;
  let next = base;
  let i = 1;
  while (takenIds.has(next)) {
    next = `${base}_${i}`;
    i += 1;
  }
  takenIds.add(next);
  return next;
}

function isValidHexColor(c) {
  if (typeof c !== 'string') return false;
  return /^#[0-9a-fA-F]{6}$/.test(c.trim());
}

function parseAndValidateGroupsJson(text) {
  const errors = [];
  let parsed;
  try {
    parsed = JSON.parse(String(text ?? ''));
  } catch {
    return { ok: false, errors: ['Invalid JSON. Please paste valid JSON.'], groups: [] };
  }

  const rawGroups = Array.isArray(parsed) ? parsed : parsed?.groups;
  if (!Array.isArray(rawGroups)) {
    return {
      ok: false,
      errors: ['JSON must be an array of groups, or an object like { "groups": [...] }.'],
      groups: []
    };
  }

  if (rawGroups.length === 0) {
    return { ok: false, errors: ['Groups list is empty.'], groups: [] };
  }

  if (rawGroups.length > 50) {
    errors.push('Too many groups (max 50).');
  }

  const takenIds = new Set();
  const cleaned = [];

  rawGroups.forEach((g, idx) => {
    if (!g || typeof g !== 'object') {
      errors.push(`Group #${idx + 1} must be an object.`);
      return;
    }

    const name = String(g.name ?? '').trim();
    if (!name) {
      errors.push(`Group #${idx + 1}: name is required.`);
    }

    const color = g.color ?? '';
    if (!isValidHexColor(color)) {
      errors.push(`Group #${idx + 1}: color must be a hex value like #3b82f6.`);
    }

    const scoreNum = Number(g.score ?? 0);
    if (!Number.isFinite(scoreNum)) {
      errors.push(`Group #${idx + 1}: score must be a number.`);
    }

    const id = normalizeGroupId(g.id ?? `g${idx + 1}`, takenIds);

    cleaned.push({
      id,
      name: name || `Group ${idx + 1}`,
      score: Number.isFinite(scoreNum) ? scoreNum : 0,
      color: isValidHexColor(color) ? String(color).trim() : '#8b5cf6'
    });
  });

  if (errors.length > 0) {
    return { ok: false, errors, groups: [] };
  }

  return { ok: true, errors: [], groups: cleaned };
}

async function copyGroupsJson() {
  groupsJsonSuccess.value = '';
  groupsJsonErrors.value = [];
  const payload = JSON.stringify(gameStore.groups, null, 2);

  try {
    if (navigator?.clipboard?.writeText) {
      await navigator.clipboard.writeText(payload);
    } else {
      const textarea = document.createElement('textarea');
      textarea.value = payload;
      textarea.style.position = 'fixed';
      textarea.style.left = '-9999px';
      textarea.style.top = '-9999px';
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
    }
    groupsJsonSuccess.value = 'Copied groups JSON to clipboard.';
    window.setTimeout(() => {
      groupsJsonSuccess.value = '';
    }, 1600);
  } catch {
    groupsJsonErrors.value = ['Failed to copy. Your browser may block clipboard access.'];
  }
}

function openPasteGroupsJson() {
  groupsJsonErrors.value = [];
  groupsJsonSuccess.value = '';
  groupsJsonText.value = '';
  showGroupsJsonDialog.value = true;
}

function closePasteGroupsJson() {
  showGroupsJsonDialog.value = false;
}

function applyPastedGroupsJson() {
  groupsJsonErrors.value = [];
  groupsJsonSuccess.value = '';

  const result = parseAndValidateGroupsJson(groupsJsonText.value);
  if (!result.ok) {
    groupsJsonErrors.value = result.errors;
    return;
  }

  gameStore.groups = result.groups;
  closePasteGroupsJson();
  groupsJsonSuccess.value = 'Groups imported successfully.';
  window.setTimeout(() => {
    groupsJsonSuccess.value = '';
  }, 1600);
}

const printOptionIds = computed(() => ['A', 'B', 'C', 'D']);

function getQrPayload(groupId, optId) {
  const g = String(groupId);
  const groupToken = g.toLowerCase().startsWith('g') ? g : `g${g}`;
  return `${groupToken}_${String(optId).toLowerCase()}`;
}

function openQrPrintPage() {
  try {
    localStorage.setItem(
      'builder-v7-group-qr-print-data',
      JSON.stringify({ groups: gameStore.groups })
    );
  } catch {
    // ignore
  }

  window.open('/classroom-records/presentation/builder-v7/group-qr-print', '_blank', 'noopener,noreferrer');
}

function updateA4PreviewScale() {
  const pageW = 210 * 3.7795275591;
  const pageH = 297 * 3.7795275591;
  const pad = 32;
  const availableW = Math.max(320, window.innerWidth - pad * 2);
  const availableH = Math.max(320, window.innerHeight - 140);
  const s = Math.min(1, availableW / pageW, availableH / pageH);
  a4PreviewScale.value = Number.isFinite(s) && s > 0 ? s : 1;
}

async function openQrTab() {
  activeTab.value = 'qrcodes';
  await nextTick();
  updateA4PreviewScale();
}

async function printQrCodes() {
  await nextTick();
  if (!printAreaRef.value) return;

  document.body.classList.add('print-group-qr-mode');
  const cleanup = () => {
    document.body.classList.remove('print-group-qr-mode');
    window.removeEventListener('afterprint', cleanup);
  };
  window.addEventListener('afterprint', cleanup);
  window.print();
}

async function downloadQrCodesPdf() {
  await nextTick();
  const el = printAreaRef.value;
  if (!el) return;

  const canvas = await html2canvas(el, { backgroundColor: '#ffffff', scale: 2 });
  const imgData = canvas.toDataURL('image/png');

  const pdf = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
  const pageW = 210;
  const pageH = 297;
  pdf.addImage(imgData, 'PNG', 0, 0, pageW, pageH);
  pdf.save('group-qr-codes-a4.pdf');
}
</script>

<template>
  <div v-if="gameStore.isGroupSetupOpen" class="modal-backdrop" @click.self="close">
    <div class="modal-content">
      <div class="modal-header">
        <h2>👥 Classroom Group Setup</h2>
        <button class="close-btn" @click="close">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>

      <div class="modal-tabs">
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'setup' }"
          @click="activeTab = 'setup'"
        >
          Setup
        </button>
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'qrcodes' }"
          @click="openQrTab"
        >
          QR Codes
        </button>
      </div>

      <div class="modal-body" v-if="activeTab === 'setup'">
        
        <!-- Game Settings -->
        <div class="settings-card">
          <h3>🎮 Scoring Rules</h3>
          <label class="setting-row">
            <div>
              <strong>Enable Negative Scoring Penalty</strong>
              <p class="setting-desc">If active, incorrect guesses deduct points instead of just giving 0.</p>
            </div>
            <div class="slider-toggle">
              <input type="checkbox" v-model="gameStore.gameSettings.allowNegativeScore">
            </div>
          </label>
        </div>

        <!-- Groups List -->
        <div class="groups-section">
          <div class="groups-header">
            <h3>Active Groups ({{ gameStore.groups.length }})</h3>
            <div class="groups-header-actions">
              <button class="btn-text" @click="copyGroupsJson">Copy Groups JSON</button>
              <button class="btn-text" @click="openPasteGroupsJson">Paste Groups JSON</button>
              <button class="btn-text" @click="gameStore.resetScores">Reset All Scores</button>
            </div>
          </div>

          <div v-if="groupsJsonSuccess" class="json-success">{{ groupsJsonSuccess }}</div>
          <div v-if="groupsJsonErrors.length" class="json-errors">
            <div v-for="(e, i) in groupsJsonErrors" :key="'json-err-' + i">{{ e }}</div>
          </div>
          
          <div class="groups-list">
            <div v-for="group in gameStore.groups" :key="group.id" class="group-row">
               <input type="color" :value="group.color" @input="e => gameStore.updateGroupColor(group.id, e.target.value)" class="color-picker" title="Group Color" />
               <input type="text" :value="group.name" @change="e => gameStore.updateGroupName(group.id, e.target.value)" class="name-input" placeholder="Group Name" />
               
               <div class="score-controls">
                 <span>Score:</span>
                 <input type="number" :value="group.score" @change="e => handleScoreChange(group.id, e.target.value)" class="score-input" />
               </div>

               <button class="btn-icon delete-btn" @click="gameStore.removeGroup(group.id)" title="Remove Group">
                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
               </button>
            </div>
          </div>
        </div>

        <!-- Add New Group -->
        <div class="add-group-row">
          <input type="text" v-model="newGroupName" placeholder="New Group Name..." @keyup.enter="handleAddGroup" />
          <button class="btn-primary" @click="handleAddGroup">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Group
          </button>
        </div>

        <div v-if="showGroupsJsonDialog" class="json-dialog-backdrop" @click.self="closePasteGroupsJson">
          <div class="json-dialog">
            <div class="json-dialog-header">
              <strong>Paste Groups JSON</strong>
              <button class="close-btn" @click="closePasteGroupsJson" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
              </button>
            </div>
            <div class="json-dialog-body">
              <textarea v-model="groupsJsonText" class="json-textarea" rows="10" placeholder='Paste JSON array like: [{"id":"g1","name":"Group A","score":0,"color":"#ef4444"}]'></textarea>
              <div class="json-dialog-actions">
                <button class="btn-secondary" @click="closePasteGroupsJson">Cancel</button>
                <button class="btn-primary" @click="applyPastedGroupsJson">Import</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-body qr-tab" v-else>
        <div class="qr-actions">
          <button class="btn-primary" @click="printQrCodes">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
            Print
          </button>

          <button class="btn-secondary" @click="downloadQrCodesPdf">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download
          </button>

          <button class="btn-secondary" @click="openQrPrintPage">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M21 14v7h-7"/><path d="M3 10V3h7"/><path d="M3 21h7"/></svg>
            Open in New Page
          </button>

          <div class="qr-hint">A4 preview (print-ready)</div>
        </div>

        <div class="a4-stage">
          <div
            class="a4-page"
            ref="printAreaRef"
            :style="{ transform: `scale(${a4PreviewScale})`, transformOrigin: 'top center' }"
          >
            <div class="a4-header">
              <div class="a4-title">Group QR Codes</div>
              <div class="a4-sub">Scan format: g1_a</div>
            </div>

            <div class="a4-content">
              <div v-for="g in gameStore.groups" :key="'g-qr-' + g.id" class="qr-print-group">
                <div class="qr-print-group-title" :style="{ borderColor: g.color }">
                  <span class="qr-dot" :style="{ backgroundColor: g.color }"></span>
                  <strong>{{ g.name }}</strong>
                </div>

                <div class="qr-print-grid">
                  <div v-for="optId in printOptionIds" :key="'qr-' + g.id + '-' + optId" class="qr-print-item">
                    <QrCode :value="getQrPayload(g.id, optId)" :size="110" level="H" />
                    <div class="qr-print-meta">
                      <div class="qr-choice">{{ optId }}</div>
                      <div class="qr-payload">{{ getQrPayload(g.id, optId) }}</div>
                    </div>
                  </div>
                </div>
              </div>
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
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  width: 600px;
  max-width: 90vw;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
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

.modal-tabs {
  display: flex;
  gap: 8px;
  padding: 10px 14px;
  border-bottom: 1px solid #f3f4f6;
  background: #ffffff;
}

.tab-btn {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  padding: 8px 12px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 800;
  font-size: 0.85rem;
  color: #334155;
}

.tab-btn.active {
  background: #3b82f6;
  border-color: #2563eb;
  color: white;
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
  display: flex;
  align-items: center;
  justify-content: center;
}
.close-btn:hover { background: #f3f4f6; color: #111827; }

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  max-height: 80vh;
  overflow-y: auto;
}

.qr-tab {
  padding: 16px;
  gap: 12px;
  max-height: 80vh;
}

.qr-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.btn-secondary {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #0f172a;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: 700;
  cursor: pointer;
}

.btn-secondary:hover { background: #111827; }

.qr-hint {
  margin-left: auto;
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 700;
}

.a4-stage {
  width: 100%;
  display: flex;
  justify-content: center;
  padding: 12px;
}

.a4-page {
  width: 210mm;
  min-height: 297mm;
  background: white;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 25px rgba(0,0,0,0.12);
  padding: 12mm;
}

.a4-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  padding-bottom: 8mm;
  border-bottom: 1px dashed #cbd5e1;
  margin-bottom: 8mm;
}

.a4-title {
  font-weight: 900;
  font-size: 18px;
  color: #0f172a;
}

.a4-sub {
  font-size: 12px;
  color: #64748b;
  font-weight: 700;
}

.a4-content {
  display: flex;
  flex-direction: column;
  gap: 10mm;
}

.qr-print-group {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 10px;
}

.qr-print-group-title {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-bottom: 8px;
  border-bottom: 2px solid;
  margin-bottom: 10px;
  color: #0f172a;
}

.qr-dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
}

.qr-print-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 6mm;
  justify-items: center;
}

.qr-print-item {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 3mm;
  background: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.qr-print-meta {
  width: 100%;
  text-align: center;
}

.qr-choice {
  font-weight: 900;
  color: #0f172a;
}

.qr-payload {
  font-size: 11px;
  color: #64748b;
  word-break: break-all;
}

@media print {
  @page {
    size: A4;
    margin: 10mm;
  }

  :global(body.print-group-qr-mode) * {
    visibility: hidden !important;
  }

  :global(body.print-group-qr-mode) .a4-page,
  :global(body.print-group-qr-mode) .a4-page * {
    visibility: visible !important;
  }

  :global(body.print-group-qr-mode) .a4-page {
    position: fixed;
    left: 0;
    top: 0;
    width: 210mm;
    min-height: 297mm;
    box-shadow: none;
    border: none;
    padding: 0;
    transform: none !important;
  }
}

/* Settings Card */
.settings-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
}
.settings-card h3 {
  margin: 0 0 12px 0;
  font-size: 1rem;
  color: #334155;
}
.setting-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.setting-row strong {
  display: block;
  font-size: 0.95rem;
  color: #1e293b;
}
.setting-desc {
  margin: 4px 0 0 0;
  font-size: 0.85rem;
  color: #64748b;
}

/* Groups List */
.groups-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.groups-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.groups-header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}
.groups-header h3 {
  margin: 0;
  font-size: 1rem;
  color: #111827;
}
.btn-text {
  background: transparent;
  color: #6366f1;
  border: none;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}
.btn-text:hover { color: #4338ca; text-decoration: underline; }

.groups-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.group-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}
.color-picker {
  width: 32px;
  height: 32px;
  padding: 0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.color-picker::-webkit-color-swatch-wrapper { padding: 0; }
.color-picker::-webkit-color-swatch { border: 1px solid #d1d5db; border-radius: 4px; }

.name-input {
  flex: 1;
  padding: 6px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.95rem;
}

.score-controls {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.9rem;
  color: #374151;
  font-weight: bold;
}
.score-input {
  width: 60px;
  padding: 4px;
  text-align: right;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  font-weight: bold;
}

.delete-btn {
  background: #fef2f2;
  color: #ef4444;
  border: 1px solid #fecaca;
  padding: 6px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
}
.delete-btn:hover { background: #fee2e2; }

/* Add Group */
.add-group-row {
  display: flex;
  gap: 12px;
  margin-top: 8px;
}
.add-group-row input {
  flex: 1;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.95rem;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #4f46e5;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}
.btn-primary:hover { background: #4338ca; }

.json-success {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  color: #065f46;
  padding: 10px 12px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.9rem;
}

.json-errors {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
  padding: 10px 12px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.9rem;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.json-dialog-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 11000;
  backdrop-filter: blur(4px);
}

.json-dialog {
  width: 720px;
  max-width: 92vw;
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.json-dialog-header {
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f8fafc;
}

.json-dialog-body {
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.json-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size: 12px;
  line-height: 1.4;
  resize: vertical;
}

.json-dialog-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
