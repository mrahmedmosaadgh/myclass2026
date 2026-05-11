<script setup>
import { ref, computed, nextTick } from 'vue'
import { useQuasar } from 'quasar'
import { useGameStore } from '../../stores/gameStore.js'
import QrPrintSheet from './QrPrintSheet.vue'

const $q = useQuasar()
const gameStore = useGameStore()

const emit = defineEmits(['close'])

// ── Tabs ───────────────────────────────────────────────
const activeTab = ref('setup') // 'setup' | 'scoring' | 'qrcodes'

// ── Setup tab state ──────────────────────────────────
const newGroupName = ref('')
const newGroupColor = ref('#6366f1')
const editingGroupId = ref(null)
const editingName = ref('')

// ── JSON import/export ────────────────────────────────
const jsonText = ref('')
const jsonError = ref('')
const jsonSuccess = ref('')
const showJsonDialog = ref(false)

// ── Computed ───────────────────────────────────────────
const canAddGroup = computed(() =>
  newGroupName.value.trim().length > 0 && gameStore.groups.length < 50
)

const totalScore = computed(() =>
  gameStore.groups.reduce((sum, g) => sum + (g.score || 0), 0)
)

// ── Setup Actions ──────────────────────────────────────
function handleAddGroup() {
  if (!canAddGroup.value) return
  gameStore.addGroup(newGroupName.value.trim(), newGroupColor.value)
  newGroupName.value = ''
}

function startEditName(group) {
  editingGroupId.value = group.id
  editingName.value = group.name
  nextTick(() => {
    const input = document.querySelector(`[data-edit-input="${group.id}"]`)
    if (input) input.focus()
  })
}

function saveEditName(groupId) {
  if (editingName.value.trim()) {
    gameStore.updateGroupName(groupId, editingName.value.trim())
  }
  editingGroupId.value = null
}

function cancelEditName() {
  editingGroupId.value = null
  editingName.value = ''
}

function handleKeydownEdit(e, groupId) {
  if (e.key === 'Enter') saveEditName(groupId)
  if (e.key === 'Escape') cancelEditName()
}

function handleScoreInput(groupId, val) {
  gameStore.setGroupScore(groupId, val)
}

function handleRemoveGroup(id) {
  $q.dialog({
    title: 'Remove Group',
    message: 'Remove this group?',
    ok: { label: 'Remove', color: 'negative' },
    cancel: { label: 'Cancel', color: 'grey-7' },
    style: 'border-radius: 12px'
  }).onOk(() => {
    gameStore.removeGroup(id)
  })
}

function handleResetAll() {
  $q.dialog({
    title: 'Reset All',
    message: 'Reset all scores and answer history?',
    ok: { label: 'Reset', color: 'negative' },
    cancel: { label: 'Cancel', color: 'grey-7' },
    style: 'border-radius: 12px'
  }).onOk(() => {
    gameStore.resetScores()
  })
}

function handleResetGroups() {
  $q.dialog({
    title: 'Reset Groups',
    message: 'Reset all groups to defaults?',
    ok: { label: 'Reset', color: 'negative' },
    cancel: { label: 'Cancel', color: 'grey-7' },
    style: 'border-radius: 12px'
  }).onOk(() => {
    gameStore.resetGroups()
  })
}

// ── JSON Actions ───────────────────────────────────────
function openExportJson() {
  jsonText.value = JSON.stringify(gameStore.groups, null, 2)
  jsonError.value = ''
  jsonSuccess.value = ''
  showJsonDialog.value = true
}

function openImportJson() {
  jsonText.value = ''
  jsonError.value = ''
  jsonSuccess.value = ''
  showJsonDialog.value = true
}

async function copyJson() {
  try {
    await navigator.clipboard.writeText(jsonText.value)
    jsonSuccess.value = 'Copied to clipboard'
    setTimeout(() => { jsonSuccess.value = '' }, 1500)
  } catch {
    jsonError.value = 'Failed to copy'
  }
}

function applyImportJson() {
  jsonError.value = ''
  jsonSuccess.value = ''

  try {
    let parsed = JSON.parse(jsonText.value.trim())
    if (!Array.isArray(parsed)) {
      parsed = parsed?.groups
    }
    if (!Array.isArray(parsed)) {
      throw new Error('Must be an array of groups')
    }

    const cleaned = parsed
      .filter(g => g && typeof g === 'object')
      .slice(0, 50)
      .map((g, i) => ({
        id: String(g.id || `g${i + 1}`),
        name: String(g.name || `Group ${i + 1}`).trim(),
        score: Number(g.score) || 0,
        color: /^#[0-9a-fA-F]{6}$/.test(g.color) ? g.color : '#8b5cf6'
      }))

    if (cleaned.length === 0) {
      throw new Error('No valid groups found')
    }

    gameStore.groups = cleaned
    jsonSuccess.value = `${cleaned.length} groups imported`
    setTimeout(() => {
      showJsonDialog.value = false
      jsonSuccess.value = ''
    }, 1200)
  } catch (err) {
    jsonError.value = err.message
  }
}

function closeJsonDialog() {
  showJsonDialog.value = false
  jsonText.value = ''
  jsonError.value = ''
  jsonSuccess.value = ''
}

// ── Scoring Actions ────────────────────────────────────
function updateCorrectPoints(val) {
  const num = Number(val)
  if (num >= 1 && num <= 100) {
    gameStore.gameSettings.correctPoints = num
  }
}

function updateWrongPoints(val) {
  const num = Number(val)
  if (num <= 0 && num >= -100) {
    gameStore.gameSettings.wrongPoints = num
  }
}
</script>

<template>
  <!-- Modal Backdrop -->
  <div class="gsd-backdrop" @click.self="$emit('close')">
    <div class="gsd-modal">
      <!-- Header -->
      <div class="gsd-header">
        <h2 class="gsd-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          Group Setup
        </h2>
        <button class="gsd-close-btn" @click="$emit('close')">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Tabs -->
      <div class="gsd-tabs">
        <button
          class="gsd-tab"
          :class="{ active: activeTab === 'setup' }"
          @click="activeTab = 'setup'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
          </svg>
          Setup
        </button>
        <button
          class="gsd-tab"
          :class="{ active: activeTab === 'scoring' }"
          @click="activeTab = 'scoring'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          Scoring
        </button>
        <button
          class="gsd-tab"
          :class="{ active: activeTab === 'qrcodes' }"
          @click="activeTab = 'qrcodes'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
          </svg>
          QR Codes
        </button>
      </div>

      <!-- Body -->
      <div class="gsd-body">
        <!-- SETUP TAB -->
        <div v-if="activeTab === 'setup'" class="gsd-tab-panel">
          <!-- Add Group -->
          <div class="gsd-add-row">
            <input
              v-model="newGroupName"
              class="gsd-input"
              placeholder="New group name..."
              @keydown.enter="handleAddGroup"
            />
            <input
              v-model="newGroupColor"
              type="color"
              class="gsd-color-picker"
              title="Group color"
            />
            <button
              class="gsd-btn primary"
              :disabled="!canAddGroup"
              @click="handleAddGroup"
            >
              Add
            </button>
          </div>

          <!-- Groups List -->
          <div class="gsd-groups-list">
            <div
              v-for="group in gameStore.groups"
              :key="group.id"
              class="gsd-group-row"
            >
              <!-- Color Dot -->
              <div
                class="gsd-group-dot"
                :style="{ backgroundColor: group.color }"
              />

              <!-- Name (edit or display) -->
              <div class="gsd-group-name-col">
                <input
                  v-if="editingGroupId === group.id"
                  :data-edit-input="group.id"
                  v-model="editingName"
                  class="gsd-input gsd-edit-input"
                  @keydown="handleKeydownEdit($event, group.id)"
                  @blur="saveEditName(group.id)"
                />
                <span
                  v-else
                  class="gsd-group-name"
                  @click="startEditName(group)"
                  title="Click to edit"
                >
                  {{ group.name }}
                </span>
              </div>

              <!-- Score -->
              <div class="gsd-group-score-col">
                <input
                  type="number"
                  class="gsd-input gsd-score-input"
                  :value="group.score"
                  @input="handleScoreInput(group.id, $event.target.value)"
                />
                <span class="gsd-score-label">pts</span>
              </div>

              <!-- Actions -->
              <div class="gsd-group-actions">
                <button
                  class="gsd-icon-btn"
                  title="Edit name"
                  @click="startEditName(group)"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button
                  class="gsd-icon-btn danger"
                  title="Remove"
                  @click="handleRemoveGroup(group.id)"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Footer Stats -->
          <div class="gsd-setup-footer">
            <span class="gsd-stat">{{ gameStore.groups.length }} groups</span>
            <span class="gsd-stat">Total: {{ totalScore }} pts</span>
            <div class="gsd-setup-actions">
              <button class="gsd-btn secondary small" @click="openExportJson">
                Export JSON
              </button>
              <button class="gsd-btn secondary small" @click="openImportJson">
                Import JSON
              </button>
              <button class="gsd-btn danger small" @click="handleResetScores">
                Reset Scores
              </button>
              <button class="gsd-btn danger small" @click="handleResetGroups">
                Reset All
              </button>
            </div>
          </div>
        </div>

        <!-- SCORING TAB -->
        <div v-if="activeTab === 'scoring'" class="gsd-tab-panel">
          <div class="gsd-scoring-grid">
            <!-- Correct Points -->
            <div class="gsd-scoring-item">
              <label class="gsd-label">Points for Correct Answer</label>
              <div class="gsd-scoring-input-row">
                <input
                  type="range"
                  min="1"
                  max="100"
                  :value="gameStore.gameSettings.correctPoints"
                  class="gsd-range"
                  @input="updateCorrectPoints($event.target.value)"
                />
                <input
                  type="number"
                  min="1"
                  max="100"
                  class="gsd-input gsd-number-input"
                  :value="gameStore.gameSettings.correctPoints"
                  @input="updateCorrectPoints($event.target.value)"
                />
              </div>
            </div>

            <!-- Negative Scoring Toggle -->
            <div class="gsd-scoring-item">
              <label class="gsd-toggle-label">
                <input
                  type="checkbox"
                  :checked="gameStore.gameSettings.allowNegativeScore"
                  @change="gameStore.gameSettings.allowNegativeScore = $event.target.checked"
                />
                <span class="gsd-toggle-text">Enable Negative Scoring</span>
              </label>
            </div>

            <!-- Wrong Points (conditional) -->
            <div
              v-if="gameStore.gameSettings.allowNegativeScore"
              class="gsd-scoring-item"
            >
              <label class="gsd-label">Points for Wrong Answer</label>
              <div class="gsd-scoring-input-row">
                <input
                  type="range"
                  min="-100"
                  max="0"
                  :value="gameStore.gameSettings.wrongPoints"
                  class="gsd-range"
                  @input="updateWrongPoints($event.target.value)"
                />
                <input
                  type="number"
                  min="-100"
                  max="0"
                  class="gsd-input gsd-number-input"
                  :value="gameStore.gameSettings.wrongPoints"
                  @input="updateWrongPoints($event.target.value)"
                />
              </div>
              <p class="gsd-hint">
                Negative value subtracted from group score for wrong answers.
              </p>
            </div>

            <!-- Preview -->
            <div class="gsd-scoring-preview">
              <h4>Current Settings</h4>
              <div class="gsd-preview-row">
                <span>Correct:</span>
                <span class="gsd-preview-value correct">+{{ gameStore.gameSettings.correctPoints }} pts</span>
              </div>
              <div class="gsd-preview-row">
                <span>Wrong:</span>
                <span
                  class="gsd-preview-value"
                  :class="gameStore.gameSettings.allowNegativeScore ? 'wrong' : 'neutral'"
                >
                  {{ gameStore.gameSettings.allowNegativeScore ? gameStore.gameSettings.wrongPoints : '0' }} pts
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- QR CODES TAB -->
        <div v-if="activeTab === 'qrcodes'" class="gsd-tab-panel">
          <QrPrintSheet
            :groups="gameStore.groups"
            :title="'Group QR Codes'"
          />
        </div>
      </div>
    </div>
  </div>

  <!-- JSON Dialog (overlay within dialog) -->
  <div v-if="showJsonDialog" class="gsd-json-overlay" @click.self="closeJsonDialog">
    <div class="gsd-json-modal">
      <h3>{{ jsonText ? 'Export / Import Groups' : 'Import Groups' }}</h3>
      <textarea
        v-model="jsonText"
        class="gsd-json-area"
        rows="12"
        placeholder="Paste group JSON here..."
      />
      <div v-if="jsonError" class="gsd-json-error">{{ jsonError }}</div>
      <div v-if="jsonSuccess" class="gsd-json-success">{{ jsonSuccess }}</div>
      <div class="gsd-json-actions">
        <button v-if="jsonText" class="gsd-btn secondary small" @click="copyJson">Copy</button>
        <button class="gsd-btn primary small" @click="applyImportJson">Import</button>
        <button class="gsd-btn ghost small" @click="closeJsonDialog">Close</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Backdrop */
.gsd-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

/* Modal */
.gsd-modal {
  background: #ffffff;
  border-radius: 12px;
  width: 100%;
  max-width: 640px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

/* Header */
.gsd-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
}

.gsd-title {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  display: flex;
  align-items: center;
  gap: 10px;
}

.gsd-close-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.15s;
}

.gsd-close-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

/* Tabs */
.gsd-tabs {
  display: flex;
  gap: 0;
  padding: 0 20px;
  border-bottom: 1px solid #e5e7eb;
}

.gsd-tab {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 12px 16px;
  border: none;
  border-bottom: 2px solid transparent;
  background: transparent;
  color: #6b7280;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  margin-bottom: -1px;
}

.gsd-tab:hover {
  color: #374151;
  background: #f9fafb;
}

.gsd-tab.active {
  color: #6366f1;
  border-bottom-color: #6366f1;
  background: #f5f3ff;
}

/* Body */
.gsd-body {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.gsd-tab-panel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Inputs */
.gsd-input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  color: #111827;
  background: #ffffff;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  min-width: 0;
}

.gsd-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.gsd-color-picker {
  width: 40px;
  height: 36px;
  padding: 2px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  background: #ffffff;
}

.gsd-range {
  flex: 1;
  accent-color: #6366f1;
}

/* Buttons */
.gsd-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s;
  white-space: nowrap;
}

.gsd-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.gsd-btn.primary {
  background: #6366f1;
  color: white;
  border-color: #6366f1;
}

.gsd-btn.primary:hover:not(:disabled) {
  background: #4f46e5;
}

.gsd-btn.secondary {
  background: #f9fafb;
  color: #374151;
  border-color: #d1d5db;
}

.gsd-btn.secondary:hover:not(:disabled) {
  background: #f3f4f6;
}

.gsd-btn.danger {
  background: #fef2f2;
  color: #991b1b;
  border-color: #fecaca;
}

.gsd-btn.danger:hover:not(:disabled) {
  background: #fee2e2;
}

.gsd-btn.ghost {
  background: transparent;
  color: #6b7280;
  border-color: #e5e7eb;
}

.gsd-btn.ghost:hover {
  background: #f9fafb;
}

.gsd-btn.small {
  padding: 6px 12px;
  font-size: 13px;
}

.gsd-icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.15s;
}

.gsd-icon-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.gsd-icon-btn.danger:hover {
  background: #fef2f2;
  color: #ef4444;
}

/* Add Row */
.gsd-add-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.gsd-add-row .gsd-input {
  flex: 1;
}

/* Groups List */
.gsd-groups-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  max-height: 320px;
  overflow-y: auto;
  padding-right: 4px;
}

.gsd-group-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  background: #f9fafb;
  border-radius: 8px;
  transition: background 0.15s;
}

.gsd-group-row:hover {
  background: #f3f4f6;
}

.gsd-group-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
}

.gsd-group-name-col {
  flex: 1;
  min-width: 0;
}

.gsd-group-name {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background 0.15s;
}

.gsd-group-name:hover {
  background: #e5e7eb;
}

.gsd-edit-input {
  width: 100%;
}

.gsd-group-score-col {
  display: flex;
  align-items: center;
  gap: 4px;
}

.gsd-score-input {
  width: 64px;
  text-align: right;
}

.gsd-score-label {
  font-size: 12px;
  color: #9ca3af;
}

.gsd-group-actions {
  display: flex;
  gap: 2px;
}

/* Setup Footer */
.gsd-setup-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
  flex-wrap: wrap;
  gap: 8px;
}

.gsd-stat {
  font-size: 13px;
  color: #6b7280;
  font-weight: 500;
}

.gsd-setup-actions {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

/* Scoring */
.gsd-scoring-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.gsd-scoring-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.gsd-label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.gsd-scoring-input-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.gsd-number-input {
  width: 80px;
  text-align: center;
  font-weight: 600;
}

.gsd-toggle-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  background: #f9fafb;
  transition: background 0.15s;
}

.gsd-toggle-label:hover {
  background: #f3f4f6;
}

.gsd-toggle-text {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.gsd-hint {
  font-size: 13px;
  color: #9ca3af;
  margin: 0;
}

/* Scoring Preview */
.gsd-scoring-preview {
  background: #f9fafb;
  border-radius: 8px;
  padding: 14px 16px;
  margin-top: 4px;
}

.gsd-scoring-preview h4 {
  margin: 0 0 10px 0;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.gsd-preview-row {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  font-size: 14px;
}

.gsd-preview-row + .gsd-preview-row {
  border-top: 1px solid #e5e7eb;
}

.gsd-preview-value {
  font-weight: 700;
}

.gsd-preview-value.correct {
  color: #059669;
}

.gsd-preview-value.wrong {
  color: #dc2626;
}

.gsd-preview-value.neutral {
  color: #6b7280;
}

/* JSON Overlay */
.gsd-json-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  padding: 20px;
}

.gsd-json-modal {
  background: #ffffff;
  border-radius: 12px;
  width: 100%;
  max-width: 520px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.gsd-json-modal h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.gsd-json-area {
  width: 100%;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-family: ui-monospace, SFMono-Regular, monospace;
  font-size: 13px;
  line-height: 1.5;
  resize: vertical;
  min-height: 180px;
  outline: none;
}

.gsd-json-area:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.gsd-json-error {
  padding: 8px 12px;
  background: #fef2f2;
  color: #b91c1c;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
}

.gsd-json-success {
  padding: 8px 12px;
  background: #ecfdf5;
  color: #047857;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
}

.gsd-json-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

/* Scrollbar */
.gsd-groups-list::-webkit-scrollbar {
  width: 6px;
}

.gsd-groups-list::-webkit-scrollbar-track {
  background: transparent;
}

.gsd-groups-list::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

/* Mobile */
@media (max-width: 480px) {
  .gsd-modal {
    max-height: 95vh;
    border-radius: 0;
  }

  .gsd-add-row {
    flex-wrap: wrap;
  }

  .gsd-add-row .gsd-input {
    width: 100%;
  }

  .gsd-group-row {
    flex-wrap: wrap;
  }

  .gsd-setup-footer {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
