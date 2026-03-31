<script setup>
import { ref } from 'vue';
import { useGameStore } from '../stores/gameStore';

const gameStore = useGameStore();
const newGroupName = ref('');

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

      <div class="modal-body">
        
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
            <button class="btn-text" @click="gameStore.resetScores">Reset All Scores</button>
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
</style>
