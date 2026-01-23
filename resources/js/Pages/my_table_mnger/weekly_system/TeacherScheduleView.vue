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
         <!-- Date and Week Selection (Required for Reward System) -->
         <div class="row q-gutter-sm items-center bg-amber-1 q-pa-sm rounded-borders q-mr-md">
           <q-icon name="event" color="amber-8" size="sm" />
           <q-input
             v-model="selectedDate"
             type="date"
             dense
             outlined
             label="Select Date"
             style="min-width: 150px"
             bg-color="white"
             @update:model-value="saveToLocalStorage"
           >
             <template v-slot:prepend>
               <q-icon name="event" />
             </template>
           </q-input>
           
           <q-select
             v-model="selectedWeek"
             :options="weekOptions"
             dense
             outlined
             label="Select Week"
             style="min-width: 120px"
             bg-color="white"
             emit-value
             map-options
             @update:model-value="saveToLocalStorage"
           >
             <template v-slot:prepend>
               <q-icon name="calendar_view_week" />
             </template>
           </q-select>
           
           <q-badge v-if="!isRewardSystemReady" color="warning" class="q-ml-sm">
             Select Date & Week
           </q-badge>
           <q-badge v-else color="positive" class="q-ml-sm">
             Ready
           </q-badge>
         </div>

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
           :reward-system-disabled="!isRewardSystemReady"
        />
    </q-card>
    
    <!-- Reward System Full-Screen Dialog -->
    <q-dialog 
      v-model="showRewardDialog" 
      maximized 
      :seamless="isMinimized"
      :no-backdrop-dismiss="true"
      transition-show="slide-up"
      transition-hide="slide-down"
      :persistent="true"
    >
      <q-card v-show="!isMinimized" class="flex flex-col">
        <q-toolbar class="bg-primary text-white shadow-2">
          <q-btn flat round dense icon="stars" class="q-mr-sm" />
          <q-toolbar-title class="flex items-center gap-2">
            <span class="text-weight-bold">Reward System</span>
            <span v-if="rewardContext" class="text-subtitle2 bg-white text-primary px-2 py-0.5 rounded shadow-sm opacity-90">
              {{ getContextTitle(rewardContext) }}
            </span>
          </q-toolbar-title>
          <q-space />
          <q-btn flat round dense icon="remove" @click="minimizeDialog">
            <q-tooltip>Minimize (Keep Session Active)</q-tooltip>
          </q-btn>
          <q-btn flat round dense icon="close" @click="confirmCloseSession" />
        </q-toolbar>
        
        <q-card-section class="flex-1 overflow-auto q-pa-none bg-blue-50">
          <!-- Keep component mounted once created, just toggle visibility -->
          <div v-show="rewardContext" class="w-full h-full">
            <RewardSystemContent
              v-if="rewardComponentCreated"
              :classroom-id="rewardContext?.classroomId || 0"
              :subject-id="rewardContext?.subjectId || 0"
              :period="rewardContext?.period || 0"
              :date="rewardContext?.date || ''"
              :week="rewardContext?.week || 0"
              :is-dialog="true"
            />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Floating Action Button for Minimized Session -->
    <div 
      v-show="rewardContext && isMinimized" 
      class="fixed-bottom-right q-ma-md"
      style="bottom: 20px; right: 20px; z-index: 9999; position: fixed;"
    >
      <q-btn
        fab
        color="amber"
        text-color="black"
        icon="stars"
        class="shadow-10"
        @click="restoreSession"
      >
        <q-tooltip anchor="center left" self="center right">
          Restore Session: {{ getContextTitle(rewardContext) }}
        </q-tooltip>
        <q-badge color="red" floating>Active</q-badge>
      </q-btn>
    </div>

    <!-- Close Confirmation Dialog -->
    <q-dialog v-model="showCloseConfirm" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="warning" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to close the current session? Unsaved changes might be lost.</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Close Session" color="negative" @click="closeSession" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Switch Session Confirmation Dialog -->
    <q-dialog v-model="showSwitchConfirm" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="swap_horiz" color="primary" text-color="white" />
          <span class="q-ml-sm">
            You have an active session for <strong>{{ getContextTitle(rewardContext) }}</strong>.
            <br>
            Do you want to end it and start a new one for <strong>{{ getContextTitle(pendingContext) }}</strong>?
          </span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Switch Session" color="primary" @click="switchSession" v-close-popup />
        </q-card-actions>
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

// Date and Week for Reward System
const selectedDate = ref(localStorage.getItem('teacher-schedule-selected-date') || new Date().toISOString().split('T')[0])
const selectedWeek = ref(parseInt(localStorage.getItem('teacher-schedule-selected-week')) || null)

// Week options (1-17)
const weekOptions = Array.from({ length: 17 }, (_, i) => ({
  label: `Week ${i + 1}`,
  value: i + 1
}))

// Check if reward system is ready (date and week selected)
const isRewardSystemReady = computed(() => {
  return selectedDate.value && selectedWeek.value
})

// Save to localStorage
const saveToLocalStorage = () => {
  if (selectedDate.value) {
    localStorage.setItem('teacher-schedule-selected-date', selectedDate.value)
  }
  if (selectedWeek.value) {
    localStorage.setItem('teacher-schedule-selected-week', selectedWeek.value.toString())
  }
}

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
const rewardComponentCreated = ref(false)
const isMinimized = ref(false)
const showCloseConfirm = ref(false)
const showSwitchConfirm = ref(false)
const pendingContext = ref(null)

import { useQuasar } from 'quasar'
const $q = useQuasar()

const handleOpenReward = (data) => {
    // Validate date and week are selected
    if (!selectedDate.value || !selectedWeek.value) {
        $q.notify({
            message: 'Please select both Date and Week before opening the Reward System',
            color: 'warning',
            position: 'top',
            icon: 'warning',
            timeout: 3000
        });
        return;
    }

    // Add date and week to the context
    const enrichedData = {
        ...data,
        date: selectedDate.value,
        week: selectedWeek.value
    };

    // If a session is already active
    if (rewardContext.value) {
        // If it's the exact same session (classroom + period + date), just open it
        if (
            rewardContext.value.classroomId === enrichedData.classroomId &&
            rewardContext.value.period === enrichedData.period &&
            rewardContext.value.date === enrichedData.date
        ) {
            showRewardDialog.value = true;
            isMinimized.value = false;
            return;
        }

        // If it's a different session, warn the user
        pendingContext.value = enrichedData;
        showSwitchConfirm.value = true;
        return;
    }

    // No active session, start new one
    startSession(enrichedData);
}

const startSession = (data) => {
    rewardContext.value = data
    rewardComponentCreated.value = true  // Mark as created
    showRewardDialog.value = true
    isMinimized.value = false
}

const minimizeDialog = () => {
    isMinimized.value = true;
    $q.notify({
        icon: 'stars',
        color: 'amber',
        textColor: 'black',
        message: 'Session minimized. Click the floating button to restore.',
        position: 'bottom-right',
        timeout: 2500
    });
}

const restoreSession = () => {
    isMinimized.value = false;
    showRewardDialog.value = true;
}

const confirmCloseSession = () => {
    showCloseConfirm.value = true;
}

const closeSession = () => {
    showRewardDialog.value = false;
    isMinimized.value = false;
    rewardContext.value = null; // Clear context to kill the session
    rewardComponentCreated.value = false; // Allow recreation next time
}

const switchSession = () => {
    rewardContext.value = null; // Kill old session
    // Small timeout to allow Vue to unmount the component and clear state
    setTimeout(() => {
        startSession(pendingContext.value);
        pendingContext.value = null;
    }, 100);
}

const getContextTitle = (ctx) => {
    if (!ctx) return '';
    const classroomName = ctx.schedule?.cst?.classroom?.name || 'Classroom';
    const subjectName = ctx.schedule?.cst?.subject?.name || 'Subject';
    return `${classroomName} - ${subjectName} (P${ctx.period})`;
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
