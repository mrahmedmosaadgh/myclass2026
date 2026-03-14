<template>
  <div class="game-mode-selector">
    <div class="mode-header">
      <h2 class="title">🎮 Choose Game Mode</h2>
      <p class="subtitle">Quick select to start!</p>
    </div>

    <!-- Compact Mode Cards -->
    <div class="modes-grid-compact">
      <!-- Falling Questions Mode -->
      <div 
        class="mode-card-compact" 
        :class="{ selected: selectedMode === 'falling' }"
        @click="selectMode('falling')"
      >
        <div class="mode-icon">🍄</div>
        <h3 class="mode-name-compact">Falling</h3>
        <p class="mode-desc-compact">Catch & answer</p>
      </div>

      <!-- Orbiting Mode -->
      <div 
        class="mode-card-compact" 
        :class="{ selected: selectedMode === 'orbiting' }"
        @click="selectMode('orbiting')"
      >
        <div class="mode-icon">🌟</div>
        <h3 class="mode-name-compact">Solar</h3>
        <p class="mode-desc-compact">Orbiting answers</p>
      </div>

      <!-- Space Adventure Mode -->
      <div 
        class="mode-card-compact" 
        :class="{ selected: selectedMode === 'space' }"
        @click="selectMode('space')"
      >
        <div class="mode-icon">🚀</div>
        <h3 class="mode-name-compact">Space</h3>
        <p class="mode-desc-compact">Shoot answers</p>
      </div>
    </div>

    <!-- Quick Settings (Inline) -->
    <div class="quick-settings" v-if="selectedMode">
      <div class="setting-row">
        <span class="setting-label-sm">Difficulty:</span>
        <div class="difficulty-btns-sm">
          <button 
            class="diff-btn-sm" 
            :class="{ active: difficulty === 'easy' }"
            @click="difficulty = 'easy'"
          >
            😊
          </button>
          <button 
            class="diff-btn-sm" 
            :class="{ active: difficulty === 'medium' }"
            @click="difficulty = 'medium'"
          >
            🤔
          </button>
          <button 
            class="diff-btn-sm" 
            :class="{ active: difficulty === 'hard' }"
            @click="difficulty = 'hard'"
          >
            😈
          </button>
        </div>
      </div>
      
      <div class="setting-row">
        <span class="setting-label-sm">Lives:</span>
        <select v-model="lives" class="lives-select-sm">
          <option value="3">3</option>
          <option value="5">5</option>
          <option value="10">10</option>
        </select>
      </div>
      
      <div class="setting-row">
        <span class="setting-label-sm">Audio:</span>
        <div class="audio-toggles">
          <button 
            class="audio-btn" 
            :class="{ active: soundEnabled }"
            @click="soundEnabled = !soundEnabled"
            :title="'Sound: ' + (soundEnabled ? 'On' : 'Off')"
          >
            {{ soundEnabled ? '🔊' : '🔇' }}
          </button>
          <button 
            class="audio-btn" 
            :class="{ active: musicEnabled }"
            @click="musicEnabled = !musicEnabled"
            :title="'Music: ' + (musicEnabled ? 'On' : 'Off')"
          >
            {{ musicEnabled ? '🎵' : '' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Start Button -->
    <div class="action-buttons-compact">
      <button class="btn-cancel-sm" @click="$emit('cancel')">
        Cancel
      </button>
      <button 
        class="btn-start-sm" 
        :disabled="!selectedMode"
        @click="startGame"
      >
        {{ selectedMode ? '🎮 Start ' + getModeName() : 'Select a mode' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const emit = defineEmits(['start', 'cancel']);

const selectedMode = ref(null);
const difficulty = ref('medium');
const lives = ref(3);
const soundEnabled = ref(true);
const musicEnabled = ref(true);
const reduceMotion = ref(false);
const highContrast = ref(false);
const colorblindMode = ref(false);

onMounted(() => {
  // Load saved preferences
  const savedMode = localStorage.getItem('bm2_game_mode');
  const savedSettings = localStorage.getItem('bm2_game_settings');
  
  if (savedMode) {
    selectedMode.value = savedMode;
  }
  
  if (savedSettings) {
    const settings = JSON.parse(savedSettings);
    difficulty.value = settings.difficulty || 'medium';
    lives.value = settings.lives || 3;
    soundEnabled.value = settings.sound ?? true;
    musicEnabled.value = settings.music ?? true;
  }
});

const selectMode = (mode) => {
  selectedMode.value = mode;
  localStorage.setItem('bm2_game_mode', mode);
};

const getModeName = () => {
  const names = {
    falling: 'Falling Questions',
    orbiting: 'Solar System',
    space: 'Space Adventure'
  };
  return names[selectedMode.value] || '';
};

const startGame = () => {
  const settings = {
    mode: selectedMode.value,
    difficulty: difficulty.value,
    lives: lives.value,
    sound: soundEnabled.value,
    music: musicEnabled.value,
    reduceMotion: reduceMotion.value,
    highContrast: highContrast.value,
    colorblindMode: colorblindMode.value,
  };
  
  localStorage.setItem('bm2_game_settings', JSON.stringify(settings));
  
  emit('start', settings);
};
</script>

<style scoped>
.game-mode-selector {
  max-width: 800px;
  margin: 0 auto;
  padding: 1.5rem;
}

.mode-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.title {
  font-size: 1.8rem;
  font-weight: bold;
  color: #1a1a1a;
  margin-bottom: 0.3rem;
}

.subtitle {
  font-size: 0.95rem;
  color: #666;
}

/* Compact Grid */
.modes-grid-compact {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.mode-card-compact {
  background: white;
  border-radius: 0.75rem;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid transparent;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.mode-card-compact:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.mode-card-compact.selected {
  border-color: #4f46e5;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.mode-icon {
  font-size: 3rem;
  margin-bottom: 0.5rem;
}

.mode-name-compact {
  font-size: 1.1rem;
  font-weight: bold;
  color: #1a1a1a;
  margin-bottom: 0.25rem;
}

.mode-desc-compact {
  font-size: 0.85rem;
  color: #666;
}

/* Quick Settings */
.quick-settings {
  background: white;
  border-radius: 0.75rem;
  padding: 1rem;
  margin-bottom: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.setting-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.setting-row:last-child {
  margin-bottom: 0;
}

.setting-label-sm {
  font-weight: 600;
  color: #374151;
  min-width: 70px;
  font-size: 0.9rem;
}

.difficulty-btns-sm {
  display: flex;
  gap: 0.5rem;
}

.diff-btn-sm {
  width: 40px;
  height: 40px;
  border: 2px solid #e5e7eb;
  background: white;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 1.2rem;
}

.diff-btn-sm:hover {
  border-color: #4f46e5;
}

.diff-btn-sm.active {
  background: #4f46e5;
  color: white;
  border-color: #4f46e5;
}

.lives-select-sm {
  padding: 0.5rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  background: white;
  cursor: pointer;
}

.audio-toggles {
  display: flex;
  gap: 0.5rem;
}

.audio-btn {
  width: 40px;
  height: 40px;
  border: 2px solid #e5e7eb;
  background: white;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 1.2rem;
}

.audio-btn:hover {
  border-color: #10b981;
}

.audio-btn.active {
  background: #10b981;
  color: white;
  border-color: #10b981;
}

/* Compact Action Buttons */
.action-buttons-compact {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-cancel-sm {
  padding: 0.75rem 2rem;
  border: 2px solid #e5e7eb;
  background: white;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.95rem;
}

.btn-cancel-sm:hover {
  background: #f3f4f6;
}

.btn-start-sm {
  padding: 0.75rem 3rem;
  border: none;
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  color: white;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-start-sm:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(79, 70, 229, 0.4);
}

.btn-start-sm:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #9ca3af;
}
</style>
