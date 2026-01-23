<template>
  <Head :title="pageTitle" />
  <div class="q-pa-md student-schedule-view">
    <!-- Header -->
    <div class="row items-center q-mb-lg justify-between print-hide">
      <div class="col-auto">
        <h4 class="q-ma-none text-weight-bold">
          <q-icon name="meeting_room" class="q-mr-sm" color="primary" />
          {{ classroom.name }}
        </h4>
        <div class="text-h6 text-grey-7 q-mt-sm">
          {{ classroom.grade?.name || 'Grade' }}
          <span class="q-mx-sm">•</span>
          Weekly Schedule
        </div>
      </div>
      
      <div class="col-auto row q-gutter-sm print-hide">
         <!-- Filters -->
         <div class="row q-gutter-sm">
           <q-select
             v-model="selectedSubjects"
             :options="subjects"
             option-value="id"
             option-label="name"
             multiple
             dense
             outlined
             label="Filter Subjects"
             style="min-width: 200px"
             emit-value
             map-options
           >
             <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
               <q-item v-bind="itemProps">
                 <q-item-section>
                   <q-item-label>{{ opt.name }}</q-item-label>
                 </q-item-section>
                 <q-item-section side>
                   <q-checkbox :model-value="selected" @update:model-value="toggleOption(opt)" />
                 </q-item-section>
               </q-item>
             </template>
           </q-select>

           <q-select
             v-model="selectedTeachers"
             :options="teachers"
             option-value="id"
             option-label="name"
             multiple
             dense
             outlined
             label="Filter Teachers"
             style="min-width: 200px"
             emit-value
             map-options
           >
             <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
               <q-item v-bind="itemProps">
                 <q-item-section>
                   <q-item-label>{{ opt.name }}</q-item-label>
                 </q-item-section>
                 <q-item-section side>
                   <q-checkbox :model-value="selected" @update:model-value="toggleOption(opt)" />
                 </q-item-section>
               </q-item>
             </template>
           </q-select>

           <q-toggle
             v-model="showFullName"
             label="Full Names"
             dense
             color="primary"
           />
         </div>

         <q-btn 
            color="primary" 
            icon="print" 
            label="Print" 
            outline
            @click="printSchedule"
         />
      </div>
    </div>

    <!-- Print Header (Visible only when printing) -->
    <div class="print-only text-center q-mb-lg">
        <h3>{{ classroom.name }} - Schedule</h3>
        <p>{{ classroom.grade?.name }}</p>
    </div>

    <q-card flat bordered>
        <TimetableGrid 
           :schedules="schedules"
           :readonly="true"
           :filter-subject-ids="selectedSubjects"
           :filter-teacher-ids="selectedTeachers"
           :show-full-name="showFullName"
           class="student-grid"
        />
    </q-card>
    
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import TimetableGrid from './components/timetable/TimetableGrid.vue'

const props = defineProps({
  classroom: { type: Object, required: true },
  schedules: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  readonly: { type: Boolean, default: true }
})

const pageTitle = computed(() => `${props.classroom.name} - Schedule`)

const selectedSubjects = ref([])
const selectedTeachers = ref([])
const showFullName = ref(false)

const printSchedule = () => {
    window.print()
}
</script>

<style scoped>
@media print {
  .print-hide {
    display: none !important;
  }
  
  .print-only {
    display: block !important;
  }
  
  .q-card {
    border: none !important;
  }
  
  .student-schedule-view {
    padding: 0 !important;
  }
}

.print-only {
    display: none;
}
</style>
