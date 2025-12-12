<template>
  <div>
    <h3>Attendance History</h3>
    <ul>
      <li v-for="record in history" :key="record.id">
        {{ record.date }}: {{ record.status }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import AttendanceStatusBadge from './AttendanceStatusBadge.vue';

const props = defineProps({ history: Array });
const emit = defineEmits(['viewDetails']);

// Format date to a more readable format
function formatDate(dateString) {
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
}

// View details of a specific attendance record
function viewDetails(record) {
  emit('viewDetails', record);
}
</script>

<style scoped>
.history-container {
  max-height: 300px;
  overflow-y: auto;
}
</style>
