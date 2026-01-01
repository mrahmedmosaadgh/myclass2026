<template>
  <div>
    <q-card flat bordered>
      <q-card-section>
        <div class="text-h6 q-mb-md">Classroom-Subject-Teacher Assignments</div>
        
        <q-table
          :rows="assignments"
          :columns="columns"
          row-key="id"
          :filter="filter"
          :pagination="pagination"
          flat
          bordered
        >
          <template v-slot:top-right>
            <q-input
              v-model="filter"
              dense
              debounce="300"
              placeholder="Search assignments..."
              outlined
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
              <template v-slot:append>
                <q-icon
                  v-if="filter"
                  name="clear"
                  class="cursor-pointer"
                  @click="filter = ''"
                />
              </template>
            </q-input>
          </template>

          <template v-slot:body-cell-subject_name="props">
            <q-td :props="props">
              <q-chip
                :style="{
                  backgroundColor: props.row.subject_color_bg,
                  color: props.row.subject_color_text
                }"
                icon="menu_book"
                dense
              >
                {{ props.row.subject_name }}
              </q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-teacher_name="props">
            <q-td :props="props">
              <div class="row items-center">
                <q-avatar color="primary" text-color="white" size="32px" icon="person" class="q-mr-sm" />
                <span>{{ props.row.teacher_name }}</span>
              </div>
            </q-td>
          </template>

          <template v-slot:body-cell-classes_per_week="props">
            <q-td :props="props">
              <q-badge color="primary" :label="props.row.classes_per_week" />
            </q-td>
          </template>

          <template v-slot:no-data>
            <div class="full-width row flex-center q-gutter-sm q-pa-lg">
              <q-icon name="assignment" size="48px" color="grey-5" />
              <span class="text-grey-7">No assignments found</span>
            </div>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  assignments: {
    type: Array,
    required: true
  }
});

const filter = ref('');
const pagination = ref({
  rowsPerPage: 10,
  sortBy: 'classroom_name',
  descending: false
});

const columns = [
  {
    name: 'grade_name',
    label: 'Grade',
    field: 'grade_name',
    align: 'left',
    sortable: true
  },
  {
    name: 'classroom_name',
    label: 'Classroom',
    field: 'classroom_name',
    align: 'left',
    sortable: true
  },
  {
    name: 'subject_name',
    label: 'Subject',
    field: 'subject_name',
    align: 'left',
    sortable: true
  },
  {
    name: 'teacher_name',
    label: 'Teacher',
    field: 'teacher_name',
    align: 'left',
    sortable: true
  },
  {
    name: 'classes_per_week',
    label: 'Classes/Week',
    field: 'classes_per_week',
    align: 'center',
    sortable: true
  }
];
</script>

<style scoped>
/* Add any custom styles here if needed */
</style>
