<script setup>
import { watchEffect } from 'vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: '' },
});

const emit = defineEmits(['close']);

function close() {
  emit('close');
}

watchEffect(() => {
  if (!props.show) return;
  document.body.style.overflow = 'hidden';
  return () => {
    document.body.style.overflow = '';
  };
});
</script>

<template>
  <div v-if="show" class="fs-backdrop" @click.self="close">
    <div class="fs-modal" role="dialog" aria-modal="true">
      <div class="fs-header">
        <div class="fs-title">{{ title }}</div>
        <button class="fs-close" @click="close" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      <div class="fs-body">
        <slot />
      </div>
    </div>
  </div>
</template>

<style scoped>
.fs-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 20000;
  backdrop-filter: blur(6px);
}

.fs-modal {
  width: min(1200px, 96vw);
  height: min(720px, 92vh);
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.fs-header {
  padding: 14px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #e5e7eb;
  background: #f8fafc;
}

.fs-title {
  font-weight: 900;
  color: #0f172a;
  font-size: 1rem;
}

.fs-close {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 6px;
  border-radius: 10px;
  color: #64748b;
}

.fs-close:hover {
  background: #eef2ff;
  color: #111827;
}

.fs-body {
  padding: 16px;
  flex: 1;
  overflow: auto;
}
</style>
