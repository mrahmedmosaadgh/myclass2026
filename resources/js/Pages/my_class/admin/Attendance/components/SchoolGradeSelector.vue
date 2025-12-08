<template>
  <div>
    <bulk-attendance-actions @bulkAction="onBulkAction" />
    <student-attendance-table
      :students="students"
      :attendanceData="attendanceData"
      @statusChanged="onStatusChanged"
    />
    <attendance-history :history="attendanceHistory" />
  </div>
</template>

<script setup>
import { ref } from 'vue';

import BulkAttendanceActions from './BulkAttendanceActions.vue';
import StudentAttendanceTable from './StudentAttendanceTable.vue';
import AttendanceHistory from './AttendanceHistory.vue';

const students = ref([]);
const attendanceData = ref({});
const attendanceHistory = ref([]);

function onSelectionChanged(selection) {
  // TODO: fetch students and attendance data based on selection
}

function onBulkAction(action) {
  // TODO: apply bulk attendance action
}

function onStatusChanged({ studentId, status }) {
  // TODO: update individual attendance status
}
</script>

<style scoped>
/* Add your styles here */
</style>
