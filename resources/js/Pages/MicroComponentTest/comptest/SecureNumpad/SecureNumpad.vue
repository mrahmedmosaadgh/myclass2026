<template>
  <div class="inline-flex flex-col gap-2">
    <!-- Display/Input -->
    <div class="relative">
      <input
        type="text"
        :value="modelValue"
        :readonly="!allowKeyboard"
        class="w-full px-4 py-3 text-right text-2xl font-mono tracking-widest bg-zinc-50 dark:bg-zinc-800 border-2 border-zinc-200 dark:border-zinc-700 rounded-xl focus:outline-none focus:border-blue-500 transition-colors"
        :placeholder="placeholder"
        @click="showPad = !showPad; playClick()"
        @input="handleInput"
      />
      <!-- Active Indicator -->
      <div 
        class="absolute right-3 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full transition-colors"
        :class="showPad ? 'bg-blue-500 animate-pulse' : 'bg-zinc-300'"
      ></div>
    </div>

    <!-- Numpad Container -->
    <NumpadContainer 
      v-if="showPad"
      @press="press"
      @clear="clear"
      @backspace="backspace"
      @close="closePad"
    />

    <!-- Hidden Audio for Click Sound -->
    <AudioPlayer 
      ref="clickSound"
      src="/audio/click/mixkit-mouse-click-close-1113.wav" 
      :allow-replay-when-playing="true"
      class="hidden"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import AudioPlayer from '../AudioPlayer.vue';
import NumpadContainer from './NumpadContainer.vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  maxLength: {
    type: Number,
    default: 10
  },
  placeholder: {
    type: String,
    default: '0.00'
  },
  allowKeyboard: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);
const showPad = ref(false);
const clickSound = ref(null);

function playClick() {
  if (clickSound.value && clickSound.value.togglePlay) {
    clickSound.value.togglePlay();
  }
}

function handleInput(e) {
  if (!props.allowKeyboard) return;
  
  // Filter for numbers only
  let val = e.target.value.replace(/[^0-9]/g, '');
  
  if (val.length > props.maxLength) {
    val = val.slice(0, props.maxLength);
  }
  
  emit('update:modelValue', val);
  // Force update if invalid chars were typed
  e.target.value = val;
}

function press(num) {
  playClick();
  const current = String(props.modelValue);
  if (current.length >= props.maxLength) return;
  emit('update:modelValue', current + num);
}

function backspace() {
  playClick();
  const current = String(props.modelValue);
  if (current.length > 0) {
    emit('update:modelValue', current.slice(0, -1));
  }
}

function clear() {
  playClick();
  emit('update:modelValue', '');
}

function closePad() {
  showPad.value = false;
  playClick();
}
</script>

<style scoped>
.animate-enter {
  animation: slideUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(10px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
