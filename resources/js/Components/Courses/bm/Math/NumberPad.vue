<template>
  <div class="bm-number-pad q-gutter-sm row justify-center" style="max-width: 300px; margin: 0 auto;">
    <q-btn v-for="n in keys" :key="n"
      @click="handleInput(n)"
      :class="['text-h5 text-weight-bold', (n === 0 && numbersOnly) ? 'col-grow' : 'col-3']"
      color="grey-2"
      text-color="dark"
      padding="md"
      unelevated
      :label="n"
    />
    <q-btn
      @click="$emit('backspace')"
      class="col-3 text-h5 text-weight-bold"
      color="negative"
      flat
      icon="backspace"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  numbersOnly: {
    type: Boolean,
    default: false
  },
  disableKeyboard: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['input', 'backspace', 'submit']);

const keys = computed(() => {
  return props.numbersOnly 
    ? [1, 2, 3, 4, 5, 6, 7, 8, 9, 0] 
    : [1, 2, 3, 4, 5, 6, 7, 8, 9, '.', 0];
});

const handleInput = (char) => {
  emit('input', char.toString());
};

const handleKeydown = (e) => {
  if (props.disableKeyboard) return;
  
  if (e.key >= '0' && e.key <= '9') {
    handleInput(e.key);
  } else if (e.key === '.' && !props.numbersOnly) {
    handleInput(e.key);
  } else if (e.key === 'Backspace') {
    emit('backspace');
  } else if (e.key === 'Enter') {
    emit('submit');
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>

<style scoped>
.bm-number-pad .q-btn {
  border-radius: 12px;
}
</style>
