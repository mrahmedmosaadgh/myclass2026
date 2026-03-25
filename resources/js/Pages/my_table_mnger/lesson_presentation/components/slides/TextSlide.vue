<template>
  <div>
    <q-input v-model="content.text" type="textarea" autogrow label="Text Content" />
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Object, default: () => ({ text: '' }) }
});
const emit = defineEmits(['update:modelValue']);

const content = reactive({ text: props.modelValue?.text || '' });

watch(() => props.modelValue, (v) => {
  content.text = v?.text || '';
});

watch(() => content.text, (val) => {
  emit('update:modelValue', { text: val });
});
</script>

<style scoped>
</style>
