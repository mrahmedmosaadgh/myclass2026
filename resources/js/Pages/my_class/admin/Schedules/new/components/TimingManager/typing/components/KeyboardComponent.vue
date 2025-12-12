<template>
  <div class="keyboard-container">
    <div class="keyboard">
      <div v-for="(row, rowIndex) in keyboardLayout" :key="rowIndex" class="keyboard-row">
        <div 
          v-for="key in row" 
          :key="key"
          class="key"
          :class="{
            'active': key.toLowerCase() === activeKey?.toLowerCase(),
            'highlight': highlightKeys.includes(key.toUpperCase()),
            'home-row': homeRowKeys.includes(key)
          }"
        >
          {{ key }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  activeKey: {
    type: String,
    default: null
  },
  highlightKeys: {
    type: Array,
    default: () => []
  }
});

const homeRowKeys = ['A', 'S', 'D', 'F', 'J', 'K', 'L', ';'];
const keyboardLayout = [
  ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
  ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', ';'],
  ['Z', 'X', 'C', 'V', 'B', 'N', 'M', ',', '.', '/']
];
</script>

<style scoped>
.keyboard-container {
  margin: 20px auto;
  max-width: 800px;
  padding: 20px;
  background: #f5f5f5;
  border-radius: 10px;
}

.keyboard {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.keyboard-row {
  display: flex;
  justify-content: center;
  gap: 6px;
}

.key {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 18px;
  font-weight: 500;
  user-select: none;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.key.active {
  background: #4CAF50;
  color: white;
  transform: translateY(2px);
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.key.highlight {
  background: #2196F3;
  color: white;
}

.key.home-row {
  border-bottom: 3px solid #FFB74D;
}
</style>