<script setup>
import { computed } from 'vue';
import { useGameStore } from '../../../stores/gameStore';

const gameStore = useGameStore();

const studentJoinUrl = computed(() => {
  const baseUrl = window.location.origin;
  return `${baseUrl}/classroom-records/presentation/remote/student?code=${gameStore.accessCode}`;
});

const statusColor = computed(() => {
  switch (gameStore.sessionStatus) {
    case 'active': return '#10b981'; // Green
    case 'waiting': return '#f59e0b'; // Amber
    case 'completed': return '#ef4444'; // Red
    default: return '#64748b'; // Gray
  }
});

const refreshPage = () => {
  window.location.reload();
};
</script>

<template>
  <header class="session-header shadow-sm">
    <div class="left-section">
      <div class="logo-area">
        <span class="v5-badge">V5 REMOTE</span>
      </div>
      <div class="divider"></div>
      <div class="session-info">
        <span class="label">Session Code</span>
        <span class="code" v-if="gameStore.accessCode">{{ gameStore.accessCode }}</span>
        <span class="code placeholder" v-else>------</span>
      </div>
    </div>

    <div class="middle-section">
      <div class="status-indicator">
        <span class="dot" :style="{ backgroundColor: statusColor }"></span>
        <span class="status-text">{{ gameStore.sessionStatus.toUpperCase() }}</span>
      </div>
      
      <!-- Session Controls -->
      <div class="session-controls">
        <button 
          v-if="gameStore.sessionStatus === 'active' || gameStore.sessionStatus === 'waiting'"
          class="btn-end"
          @click="gameStore.endSession()"
        >
          Stop Session
        </button>
        <button 
          v-if="gameStore.sessionStatus === 'completed' || gameStore.sessionStatus === 'offline'"
          class="btn-new"
          @click="refreshPage"
        >
          Start New
        </button>
      </div>
    </div>

    <div class="right-section">
      <div class="stat-item">
        <span class="stat-value">{{ Object.keys(gameStore.questionHistory).length }}</span>
        <span class="stat-label">Questions</span>
      </div>
      <div class="stat-item join-link" @click="$emit('show-qr')">
        <span class="stat-value">🔗</span>
        <span class="stat-label">Join Link</span>
      </div>
      <div class="stat-item">
        <span class="stat-value">{{ gameStore.onlineCount }}</span>
        <span class="stat-label">Students Online</span>
      </div>
    </div>
  </header>
</template>

<style scoped>
.session-header {
  height: 72px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2rem;
  z-index: 100;
}

.left-section, .right-section, .middle-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.v5-badge {
  background: #6366f1;
  color: white;
  font-size: 0.75rem;
  font-weight: 800;
  padding: 4px 8px;
  border-radius: 4px;
  letter-spacing: 0.05em;
}

.divider {
  width: 1px;
  height: 32px;
  background: #e2e8f0;
}

.session-info {
  display: flex;
  flex-direction: column;
}

.session-info .label {
  font-size: 0.7rem;
  text-transform: uppercase;
  color: #64748b;
  font-weight: 600;
  margin-bottom: -2px;
}

.session-info .code {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  font-family: monospace;
  letter-spacing: 1px;
}

.code.placeholder {
  color: #cbd5e1;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f8fafc;
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  box-shadow: 0 0 8px v-bind(statusColor);
}

.status-text {
  font-size: 0.8rem;
  font-weight: 700;
  color: #475569;
}

.session-controls {
  display: flex;
  gap: 8px;
}

.btn-end, .btn-new {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-end {
  background: #fef2f2;
  color: #ef4444;
  border: 1px solid #fee2e2;
}
.btn-end:hover { background: #fee2e2; }

.btn-new {
  background: #f0fdf4;
  color: #10b981;
  border: 1px solid #dcfce7;
}
.btn-new:hover { background: #dcfce7; }

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  font-size: 0.65rem;
  text-transform: uppercase;
  color: #64748b;
  font-weight: 600;
}

.join-link {
  cursor: pointer;
  transition: opacity 0.2s;
}

.join-link:hover {
  opacity: 0.7;
}

@media (max-width: 768px) {
  .session-header {
    padding: 0 1rem;
    height: 64px;
  }
  .stat-item:nth-child(2), .divider, .v5-badge {
    display: none;
  }
  .session-info .code {
    font-size: 1.1rem;
  }
}
</style>
