<template>
  <div class="bm2-game-wrapper">
    <!-- Game Mode Selector Modal -->
    <div v-if="showModeSelector" class="mode-selector-overlay">
      <GameModeSelector
        @start="handleModeSelected"
        @cancel="handleCancelModeSelection"
      />
    </div>
    
    <!-- Game Components -->
    <FallingQuestionsGame
      v-else-if="isGameMode && gameSettings.mode === 'falling'"
      :questions="questions"
      :settings="gameSettings"
      @answer="handleAnswer"
      @complete="handleGameComplete"
      ref="gameComponent"
    />
    
    <OrbitingGame
      v-else-if="isGameMode && gameSettings.mode === 'orbiting'"
      :questions="questions"
      :settings="gameSettings"
      @answer="handleAnswer"
      @complete="handleGameComplete"
      ref="gameComponent"
    />
    
    <SpaceAdventureGame
      v-else-if="isGameMode && gameSettings.mode === 'space'"
      :questions="questions"
      :settings="gameSettings"
      @answer="handleAnswer"
      @complete="handleGameComplete"
      ref="gameComponent"
    />
    
    <!-- Normal Mode (Slot Content) -->
    <div v-else class="normal-mode">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import GameModeSelector from './GameModeSelector.vue';
import FallingQuestionsGame from './GameModes/FallingQuestionsGame.vue';
import OrbitingGame from './GameModes/OrbitingGame.vue';
import SpaceAdventureGame from './GameModes/SpaceAdventureGame.vue';

const props = defineProps({
  assessmentId: {
    type: Number,
    required: true,
  },
  questions: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['answer', 'game-complete']);

const showModeSelector = ref(false);
const isGameMode = ref(false);
const gameSettings = ref({
  mode: 'normal',
  difficulty: 'medium',
  lives: 3,
  sound: true,
  music: true,
});

const gameComponent = ref(null);

onMounted(() => {
  // Always show mode selector - let user choose each time
  showModeSelector.value = true;
});

const handleModeSelected = (settings) => {
  gameSettings.value = settings;
  isGameMode.value = true;
  showModeSelector.value = false;
};

const handleCancelModeSelection = () => {
  showModeSelector.value = false;
  isGameMode.value = false;
  gameSettings.value.mode = 'normal';
};

const handleAnswer = (answerData) => {
  emit('answer', answerData);
};

const handleGameComplete = (gameStats) => {
  emit('game-complete', gameStats);
};

// Expose method to forward clicks to game component
const handleClick = (event) => {
  if (gameComponent.value && gameComponent.value.handleClick) {
    gameComponent.value.handleClick(event);
  }
};

defineExpose({ handleClick });
</script>

<style scoped>
.bm2-game-wrapper {
  width: 100%;
  height: 100%;
}

.mode-selector-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.normal-mode {
  width: 100%;
  height: 100%;
}
</style>
