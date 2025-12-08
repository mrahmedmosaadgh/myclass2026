<template>
  <span :class="badgeClass" class="inline-flex items-center">
    <svg v-if="icon" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path v-if="status === 'present'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      <path v-else-if="status === 'absent'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      <path v-else-if="status === 'late'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      <path v-else-if="status === 'leftEarly'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0v-1m6 0H7"></path>
    </svg>
    {{ statusLabel }}
  </span>
</template>

<script setup>
const props = defineProps({
  status: String,
  icon: {
    type: Boolean,
    default: true
  }
});

const statusLabel = {
  present: 'Present',
  absent: 'Absent',
  late: 'Late',
  leftEarly: 'Left Early',
}[props.status] || 'Unknown';

const badgeClass = `badge badge-${props.status}`;
</script>

<style scoped>
.badge {
  padding: 5px 10px;
  border-radius: 9999px; /* Full rounded */
  color: #fff;
  font-size: 0.75rem;
  font-weight: 500;
  transition: all 0.2s ease;
}
.badge:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.badge-present { background: #4caf50; }
.badge-absent { background: #f44336; }
.badge-late { background: #ff9800; }
.badge-leftEarly { background: #2196f3; }
</style>
