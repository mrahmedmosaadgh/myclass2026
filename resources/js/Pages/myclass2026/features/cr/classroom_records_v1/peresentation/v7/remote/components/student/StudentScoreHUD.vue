<script setup>
import { computed } from 'vue';
import { useGameStore } from '../../../stores/gameStore';

const gameStore = useGameStore();

// Mock rank (will be calculated from participants list)
const rank = computed(() => {
  return '#1'; 
});

const currentScore = computed(() => {
  // In a real scenario, we'd map the student's ID to the participants score in gameStore
  return 0;
});

const groupName = computed(() => {
  return 'Group A';
});
</script>

<template>
  <footer class="student-hud shadow-lg">
    <div class="hud-item group-info">
      <span class="label">Group</span>
      <span class="value">{{ groupName }}</span>
    </div>
    
    <div class="hud-divider"></div>
    
    <div class="hud-item score-info">
      <span class="label">My Score</span>
      <span class="value highlight">{{ currentScore }}</span>
    </div>
    
    <div class="hud-divider"></div>
    
    <div class="hud-item rank-info">
      <span class="label">Rank</span>
      <span class="value">{{ rank }}</span>
    </div>
  </footer>
</template>

<style scoped>
.student-hud {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 80px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-top: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-around;
  padding: 0 1rem;
  z-index: 1000;
  padding-bottom: env(safe-area-inset-bottom);
}

.hud-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.hud-item .label {
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.05em;
}

.hud-item .value {
  font-size: 1.1rem;
  font-weight: 800;
  color: #1e293b;
}

.value.highlight {
  color: #6366f1;
}

.hud-divider {
  width: 1px;
  height: 32px;
  background: #e2e8f0;
}

.shadow-lg {
  box-shadow: 0 -10px 15px -3px rgb(0 0 0 / 0.1);
}
</style>
