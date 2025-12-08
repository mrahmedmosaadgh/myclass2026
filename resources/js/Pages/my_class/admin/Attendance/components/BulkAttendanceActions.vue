<template>
  <div>
    <button @click="markAllPresent">Mark All Present</button>
    <button @click="markAllAbsent">Mark All Absent</button>
    <button @click="markAllLate">Mark All Late</button>
    <button @click="markAllLeftEarly">Mark All Left Early</button>
  </div>
</template>

<script setup>
const emit = defineEmits(['bulkAction']);

function markAllPresent() {
  emit('bulkAction', 'present');
}
function markAllAbsent() {
  emit('bulkAction', 'absent');
}
function markAllLate() {
  emit('bulkAction', 'late');
}
function markAllLeftEarly() {
  emit('bulkAction', 'leftEarly');
}
</script>

<style scoped>
button { margin: 0 5px; }
</style>
