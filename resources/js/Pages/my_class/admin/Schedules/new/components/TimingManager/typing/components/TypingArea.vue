<template>
  <div class="typing-area">
    <div class="lesson-text" :class="{ 'blur': !isFocused }">
      <span 
        v-for="(char, index) in lessonText" 
        :key="index"
        :class="{
          'current': currentIndex === index,
          'correct': typedChars[index] === char,
          'incorrect': typedChars[index] && typedChars[index] !== char
        }"
      >{{ char }}</span>
    </div>
    
    <input
      ref="inputRef"
      type="text"
      class="typing-input"
      v-model="currentInput"
      @keydown="handleKeyDown"
      @focus="isFocused = true"
      @blur="isFocused = false"
      :disabled="isComplete"
    >

    <div class="typing-prompt" v-if="!isFocused">
      Click here to start typing
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
  lessonText: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['key-press', 'exercise-complete']);

const inputRef = ref(null);
const currentInput = ref('');
const typedChars = ref([]);
const currentIndex = ref(0);
const isFocused = ref(false);
const startTime = ref(null);

const isComplete = computed(() => currentIndex.value === props.lessonText.length);

const handleKeyDown = (event) => {
  if (!startTime.value) startTime.value = Date.now();
  
  if (event.key === 'Backspace') {
    if (currentIndex.value > 0) {
      currentIndex.value--;
      typedChars.value[currentIndex.value] = null;
    }
    return;
  }

  if (event.key.length === 1) {
    emit('key-press', event.key);
    typedChars.value[currentIndex.value] = event.key;
    currentIndex.value++;

    if (isComplete.value) {
      const endTime = Date.now();
      const timeInMinutes = (endTime - startTime.value) / 60000;
      const wordsTyped = props.lessonText.split(' ').length;
      const wpm = Math.round(wordsTyped / timeInMinutes);
      
      emit('exercise-complete', {
        wpm,
        accuracy: calculateAccuracy(),
        errors: calculateErrors()
      });
    }
  }
};

const calculateAccuracy = () => {
  const correctChars = typedChars.value.filter(
    (char, index) => char === props.lessonText[index]
  ).length;
  return Math.round((correctChars / props.lessonText.length) * 100);
};

const calculateErrors = () => {
  return typedChars.value.filter(
    (char, index) => char && char !== props.lessonText[index]
  ).length;
};

onMounted(() => {
  inputRef.value?.focus();
});

watch(isComplete, (newValue) => {
  if (newValue) {
    inputRef.value?.blur();
  }
});
</script>

<style scoped>
.typing-area {
  position: relative;
  max-width: 800px;
  margin: 30px auto;
  padding: 20px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.lesson-text {
  font-size: 24px;
  line-height: 1.5;
  margin-bottom: 20px;
  font-family: monospace;
  white-space: pre-wrap;
}

.lesson-text.blur {
  filter: blur(2px);
}

.lesson-text span {
  position: relative;
  transition: all 0.2s ease;
}

.lesson-text span.current {
  background: #e3f2fd;
  border-left: 2px solid #2196F3;
}

.lesson-text span.correct {
  color: #4CAF50;
}

.lesson-text span.incorrect {
  color: #f44336;
  text-decoration: underline wavy #f44336;
}

.typing-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.typing-prompt {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(0,0,0,0.8);
  color: white;
  padding: 10px 20px;
  border-radius: 20px;
  font-size: 14px;
}
</style>