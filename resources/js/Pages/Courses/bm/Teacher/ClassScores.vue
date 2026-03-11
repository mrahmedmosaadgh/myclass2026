<template>
  <Head title="Basic Math - Class Scores" />
  <div class="bm-teacher-class-scores q-pa-md bg-white">
    <div class="row justify-between items-center q-mb-md">
      <div class="text-h4 text-weight-bold text-primary">Class Scores</div>
      <q-btn flat icon="arrow_back" label="Back to Dashboard" :to="route('bm.teacher.dashboard')" />
    </div>

    <!-- Table -->
    <q-table
      flat
      bordered
      class="shadow-1 rounded-borders q-mt-lg"
      :rows="students"
      :columns="columns"
      row-key="id"
      :pagination="{ rowsPerPage: 15 }"
    >
      <template v-slot:body-cell-level="props">
        <q-td :props="props">
          <q-chip 
            :color="getLevelColor(props.row.level)" 
            text-color="white" 
            size="sm" 
            class="text-weight-bold shadow-1"
          >
            {{ props.row.level }}
          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="text-right">
          <q-btn 
            flat 
            color="primary" 
            icon="visibility" 
            dense 
            round
            :to="route('bm.teacher.student_detail', { studentId: props.row.id })"
          >
            <q-tooltip>View Details</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>
  </div>
</template>

<script setup>
defineOptions({ layout: BMLayout });
import BMLayout from "@/Layouts/BMLayout.vue";
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const columns = [
  { name: 'name', align: 'left', label: 'Student Name', field: 'name', sortable: true },
  { name: 'score', align: 'center', label: 'Score', field: 'score', sortable: true },
  { name: 'level', align: 'center', label: 'Proficiency Level', field: 'level', sortable: true },
  { name: 'last_active', align: 'right', label: 'Last Assessment', field: 'last_active', sortable: true },
  { name: 'actions', align: 'right', label: 'Actions', field: 'actions' }
];

const students = ref([
  { id: 1, name: 'Alice Smith', score: 92, level: 'Expert', last_active: '2026-03-10' },
  { id: 2, name: 'Bob Jones', score: 65, level: 'Proficient', last_active: '2026-03-09' },
  { id: 3, name: 'Charlie Brown', score: 40, level: 'Developing', last_active: '2026-03-08' },
  { id: 4, name: 'Diana Prince', score: 85, level: 'Advanced', last_active: '2026-03-11' },
]);

const getLevelColor = (level) => {
  switch (level) {
    case 'Expert': return 'positive';
    case 'Advanced': return 'info';
    case 'Proficient': return 'primary';
    case 'Developing': return 'warning';
    case 'Beginner': return 'negative';
    default: return 'grey';
  }
};
</script>
