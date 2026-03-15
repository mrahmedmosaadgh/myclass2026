<template>
  <div class="loading-container" :style="{ height }">
    <div class="loading-content flex flex-center column">
      <q-spinner-dots color="primary" size="64px" />
      <div v-if="message" class="text-h6 text-grey-7 q-mt-md">
        {{ message }}
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  message: {
    type: String,
    default: 'Loading...'
  },
  height: {
    type: String,
    default: '300px'
  }
})
</script>

<style scoped>
.loading-container {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}
</style>
