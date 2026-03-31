<script setup>
import { useSectionStore } from '../stores/sectionStore';
import { useUIStore } from '../stores/uiStore';

const sectionStore = useSectionStore();
const ui = useUIStore();

function close() {
  ui.isSectionManagerOpen = false;
}

const colorPresets = ['#fcd34d', '#f9a8d4', '#93c5fd', '#a7f3d0', '#c4b5fd', '#fca5a5', '#5eead4', '#e5e7eb', '#9ca3af', '#f87171', '#fbbf24', '#34d399', '#60a5fa', '#818cf8', '#f472b6'];

const phaseSuggestions = {
  0: ['Introduction', 'Warm-up', 'مقدمة', 'تهيئة', 'التهيئة'],
  1: ['Core Lesson', 'Presentation', 'الشرح', 'العرض'],
  2: ['Practice', 'Activity', 'تدريب', 'نشاط'],
  3: ['Assessment', 'Evaluation', 'تقييم', 'تقويم'],
  4: ['Conclusion', 'Closure', 'خاتمة', 'غلق']
};
</script>

<template>
  <div class="modal-overlay" @mousedown.self="close">
    <div class="modal">
      <div class="modal-header">
        <h3>Manage Lesson Phases</h3>
        <button @click="close" class="close-btn">&times;</button>
      </div>
      <div class="modal-body">
        <p class="desc">Customize the lesson phases. These changes are saved directly to your browser.</p>
        
        <div class="section-list">
          <div v-for="(sec, index) in sectionStore.sections" :key="sec.id" class="section-wrapper">
            <div v-if="phaseSuggestions[index]" class="suggestion-tags">
              <span class="suggestion-label">Suggestions:</span>
              <span 
                v-for="sug in phaseSuggestions[index]" 
                :key="sug" 
                @click="sec.name = sug" 
                class="tag-btn"
              >
                {{ sug }}
              </span>
            </div>
            
            <div class="section-item">
              <input type="color" v-model="sec.color" class="color-picker" :list="'presetColors' + index">
            <datalist :id="'presetColors' + index">
              <option v-for="c in colorPresets" :key="c" :value="c"></option>
            </datalist>
            
            <input type="text" v-model="sec.name" class="name-input" placeholder="Phase Name">
            
            <button class="icon-btn text-red" @click="sectionStore.deleteSection(index)" title="Remove Phase">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
            </div>
          </div>
        </div>

        <div class="modal-actions">
          <button class="btn-secondary" @click="sectionStore.addSection()">+ Add Phase</button>
          <button class="btn-danger-outline" @click="sectionStore.resetToDefault()">Reset to Defaults</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999; /* Highest priority */
}

.modal {
  background: white;
  width: 500px;
  max-width: 90vw;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  color: #111827;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #6b7280;
}
.close-btn:hover { color: #111827; }

.modal-body {
  padding: 20px;
  max-height: 70vh;
  overflow-y: auto;
}

.desc {
  font-size: 14px;
  color: #6b7280;
  margin-top: 0;
  margin-bottom: 20px;
}

.section-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.section-wrapper {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 1px dashed #e5e7eb;
}

.section-wrapper:last-child {
  border-bottom: none;
}

.suggestion-tags {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
}

.suggestion-label {
  font-size: 10px;
  color: #9ca3af;
  margin-right: 4px;
}

.tag-btn {
  font-size: 11px;
  background: #f3f4f6;
  color: #4b5563;
  padding: 2px 8px;
  border-radius: 12px;
  cursor: pointer;
  border: 1px solid #e5e7eb;
  transition: all 0.2s;
}

.tag-btn:hover {
  background: #e0e7ff;
  color: #4f46e5;
  border-color: #c7d2fe;
}

.section-item {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f9fafb;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.color-picker {
  width: 30px;
  height: 30px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  padding: 0;
  cursor: pointer;
}

.name-input {
  flex: 1;
  padding: 6px 10px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
}

.icon-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
}
.icon-btn:hover { background: #fee2e2; }
.text-red { color: #ef4444; }

.modal-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
}

.btn-secondary {
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: 0.2s;
}
.btn-secondary:hover { background: #e5e7eb; }

.btn-danger-outline {
  background: transparent;
  color: #ef4444;
  border: 1px solid #fca5a5;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: 0.2s;
}
.btn-danger-outline:hover {
  background: #fef2f2;
}
</style>
