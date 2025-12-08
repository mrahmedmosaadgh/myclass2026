<template>
  <div>
    <record-filters @filters-selected="onFilters" />
    <classroom-records-table
      v-if="filtersLoaded"
      :teacher-id="filters.teacher_id"
      :subject-id="filters.subject_id"
      :classroom-id="filters.classroom_id"
      :period-code="filters.period_code"
      :date="filters.date"
      @record-updated="onRecordUpdated"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import RecordFilters from './RecordFilters.vue';
import ClassroomRecordsTable from './ClassroomRecordsTable.vue';

const filters = ref({});
const filtersLoaded = ref(false);

function onFilters(payload){
  filters.value = payload;
  filtersLoaded.value = true;
}

function onRecordUpdated(row){
  // optional: show toast or sync other components
  // console.log('updated', row);
}
</script>
