<script setup>
import { ref, computed, watch } from 'vue';
import { useGameStore } from '../../../stores/gameStore';
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';

const gameStore = useGameStore();
const participants = computed(() => gameStore.participants);

// Real-time listener for student joins
const teacherChannel = computed(() => 
  gameStore.accessCode ? `quiz_${gameStore.accessCode}_teacher` : null
);

useRealtimeChannel(teacherChannel, (signal) => {
  gameStore.handleStudentSignal(signal);
});

const onlineCount = computed(() => gameStore.onlineCount);
</script>

<template>
  <div class="participant-roster">
    <div class="roster-header">
      <h3 class="panel-subtitle">Joined Students</h3>
      <span class="count-badge">{{ onlineCount }}/{{ participants.length }}</span>
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Search students...">
    </div>

    <div class="student-list border-top">
      <div v-for="student in participants" :key="student.id" class="student-item">
        <div class="student-info">
          <div class="status-dot" :class="student.status"></div>
          <div class="name-details">
            <span class="name">{{ student.name }}</span>
            <span class="group-tag" :style="{ backgroundColor: student.group === 'Group A' ? '#fef2f2' : '#eff6ff', color: student.group === 'Group A' ? '#ef4444' : '#3b82f6' }">
              {{ student.group }}
            </span>
          </div>
        </div>
        <div class="actions">
          <span class="score-pill">{{ gameStore.gameSettings.correctPoints }} pts</span>
        </div>
      </div>
      
      <div v-if="participants.length === 0" class="empty-roster">
        <p>No students have joined yet.</p>
        <p class="hint">Share the session code to get started!</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.participant-roster {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.roster-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.panel-subtitle {
  font-size: 0.95rem;
  font-weight: 700;
  margin: 0;
  color: #475569;
}

.count-badge {
  background: #f1f5f9;
  padding: 2px 10px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
}

.search-bar input {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.85rem;
}

.student-list {
  display: flex;
  flex-direction: column;
  margin: 0 -1.5rem;
}

.border-top { border-top: 1px solid #e2e8f0; }

.student-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.2s;
}

.student-item:hover {
  background: #f8fafc;
}

.student-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.status-dot.online { background: #10b981; box-shadow: 0 0 5px rgba(16, 185, 129, 0.5); }
.status-dot.offline { background: #cbd5e1; }

.name-details {
  display: flex;
  flex-direction: column;
}

.name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
}

.group-tag {
  font-size: 0.65rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 4px;
  width: fit-content;
  margin-top: 2px;
}

.score-pill {
  background: #f0fdf4;
  color: #166534;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 10px;
  border: 1px solid #dcfce7;
}

.empty-roster {
  text-align: center;
  padding: 3rem 1.5rem;
  color: #94a3b8;
}

.empty-roster p { margin: 0; font-size: 0.9rem; }
.empty-roster .hint { font-size: 0.8rem; margin-top: 0.25rem; }
</style>
