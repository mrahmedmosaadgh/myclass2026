<template>
  <Head :title="pageTitle" />
  <div class="q-pa-md teacher-schedule-view">
    <!-- Header -->
    <div class="row items-center q-mb-lg justify-between print-hide">
      <div class="col-auto">
        <h4 class="q-ma-none text-weight-bold">
          <q-icon name="school" class="q-mr-sm" color="primary" />
          {{ teacher.name }}
        </h4>
        <div class="text-h6 text-grey-7 q-mt-sm">
          Teacher Schedule
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
             style="min-width: 180px"
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
             v-model="selectedClassrooms"
             :options="classrooms"
             option-value="id"
             option-label="name"
             multiple
             dense
             outlined
             label="Filter Classrooms"
             style="min-width: 180px"
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
             v-model="selectedDays"
             :options="dayOptions"
             multiple
             dense
             outlined
             label="Filter Days"
             style="min-width: 150px"
             emit-value
             map-options
           >
             <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
               <q-item v-bind="itemProps">
                 <q-item-section>
                   <q-item-label>{{ opt.label }}</q-item-label>
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

    <!-- Print Header -->
    <div class="print-only text-center q-mb-lg">
        <h3>{{ teacher.name }} - Schedule</h3>
        <p>Weekly Assignments</p>
    </div>

    <q-card flat bordered>
        <TimetableGrid 
           :schedules="schedules"
           :readonly="true"
           :show-classroom="true"
           :hide-teacher="true"
           :visible-days="visibleDays"
           :filter-subject-ids="selectedSubjects"
           :filter-classroom-ids="selectedClassrooms"
           :show-full-name="showFullName"
           class="teacher-grid"
           @open-reward="handleOpenReward"
        />
    </q-card>
    
    <!-- Reward System Full-Screen Dialog -->
    <q-dialog v-model="showRewardDialog" full-width full-height>
      <q-card class="flex flex-col">
        <q-toolbar class="bg-primary text-white">
          <q-icon name="stars" size="sm" class="q-mr-sm" />
          <q-toolbar-title>Reward System</q-toolbar-title>
          <q-btn flat round dense icon="minimize" @click="showRewardDialog = false" />
          <q-btn flat round dense icon="close" v-close-popup />
        </q-toolbar>
        
        <q-card-section class="flex-1 overflow-auto q-pa-none">
          <RewardSystemContent
            v-if="rewardContext"
            :classroom-id="rewardContext.classroomId"
            :subject-id="rewardContext.subjectId"
            :period="rewardContext.period"
            :date="rewardContext.date"
            :is-dialog="true"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
    
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import TimetableGrid from './components/timetable/TimetableGrid.vue'
import RewardSystemContent from '@/Pages/my_table_mnger/reward_sys/reward_sys.vue'

import { useI18n } from 'vue-i18n'

const props = defineProps({
  teacher: { type: Object, required: true },
  schedules: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  readonly: { type: Boolean, default: true }
})

const { t } = useI18n()

const pageTitle = computed(() => `${props.teacher.name} - Schedule`)

const selectedSubjects = ref([])
const selectedClassrooms = ref([])
const selectedDays = ref([])
const showFullName = ref(true)

// Days options (1-7)
const dayOptions = [
    { label: t('weeklyPlans.fullDays.1'), value: 1 },
    { label: t('weeklyPlans.fullDays.2'), value: 2 },
    { label: t('weeklyPlans.fullDays.3'), value: 3 },
    { label: t('weeklyPlans.fullDays.4'), value: 4 },
    { label: t('weeklyPlans.fullDays.5'), value: 5 },
    { label: t('weeklyPlans.fullDays.6'), value: 6 },
    { label: t('weeklyPlans.fullDays.7'), value: 7 }
]

const visibleDays = computed(() => {
    return selectedDays.value.length > 0 ? selectedDays.value : [1, 2, 3, 4, 5, 6, 7]
})

const printSchedule = () => {
    window.print()
}

// Reward System Dialog
const showRewardDialog = ref(false)
const rewardContext = ref(null)

const handleOpenReward = (data) => {
    rewardContext.value = data
    showRewardDialog.value = true
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
  
  .teacher-schedule-view {
    padding: 0 !important;
  }
}

.print-only {
    display: none;
}
</style>
