<template>
  <span v-if="status === 'late'">Late by {{ formattedTime }}</span>
  <span v-else-if="status === 'leftEarly'">Left early by {{ formattedTime }}</span>
  <span v-else>-</span>
</template>

<script setup>
const props = defineProps({
  status: String,
  time: Number // minutes
});
const formattedTime = props.time ? `${props.time} min` : '';
</script>

<style scoped>
.time-value {
  font-variant-numeric: tabular-nums;
  font-weight: 500;
}
</style>
