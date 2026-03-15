<template>
  <div class="empty-state-container q-pa-xl text-center" :style="{ height }">
    <q-icon :name="icon" :size="iconSize" :color="iconColor" />
    
    <div v-if="title" class="text-h6 text-weight-bold q-mt-md" :class="titleColor">
      {{ title }}
    </div>
    
    <div v-if="message" class="text-body1 text-grey-7 q-mt-sm">
      {{ message }}
    </div>
    
    <div v-if="$slots.actions" class="q-mt-lg">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  icon: {
    type: String,
    default: 'inbox'
  },
  iconSize: {
    type: String,
    default: '64px'
  },
  iconColor: {
    type: String,
    default: 'grey-5'
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  },
  titleColor: {
    type: String,
    default: 'grey-7'
  },
  height: {
    type: String,
    default: 'auto'
  }
})
</script>

<style scoped>
.empty-state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
}
</style>
