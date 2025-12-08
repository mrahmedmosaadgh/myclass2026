<template>
  <table>
    <thead>
      <tr>
        <th>Student Name</th>
        <th>Status</th>
        <th>Time</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="student in students" :key="student.id">
        <td>{{ student.name }}</td>
        <td>
          <attendance-status-badge :status="attendanceData[student.id]?.status" />
          <select v-model="attendanceData[student.id].status" @change="emitStatusChanged(student.id, attendanceData[student.id].status)">
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
            <option value="leftEarly">Left Early</option>
          </select>
        </td>
        <td>
          <time-calculator v-if="attendanceData[student.id]" :status="attendanceData[student.id].status" :time="attendanceData[student.id].time" />
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script setup>
import AttendanceStatusBadge from './AttendanceStatusBadge.vue';
import TimeCalculator from './TimeCalculator.vue';
const props = defineProps({
  students: Array,
  attendanceData: Object
});
const emit = defineEmits(['statusChanged']);
function emitStatusChanged(studentId, status) {
  emit('statusChanged', { studentId, status });
}
</script>

<style scoped>
table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

th {
  position: sticky;
  top: 0;
  z-index: 10;
  background-color: #f9fafb;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

th:first-child {
  border-top-left-radius: 0.5rem;
}

th:last-child {
  border-top-right-radius: 0.5rem;
}

tr:last-child td:first-child {
  border-bottom-left-radius: 0.5rem;
}

tr:last-child td:last-child {
  border-bottom-right-radius: 0.5rem;
}

.time-value {
  font-variant-numeric: tabular-nums;
}
</style>
