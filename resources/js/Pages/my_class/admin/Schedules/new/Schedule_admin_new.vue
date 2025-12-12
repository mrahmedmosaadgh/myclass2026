<template>

overall_conflict_counter:{{ overall_conflict_counter }}
        <div class="controls flex items-center gap-2 mb-2">
  <q-btn @click="zoomOut" icon="remove" dense flat />
  <q-slider
    v-model="zoomLevel"
    :min="50"
    :max="150"
    :step="10"
    style="width: 200px"
  />
  <q-btn @click="zoomIn" icon="add" dense flat />
  <span class="text-sm ml-2 w-12 text-right">{{zoomLevel}}%</span>

  <!-- Fix Header Toggle -->
  <q-toggle
    v-model="isHeaderFixed"
    :icon="isHeaderFixed ? 'push_pin' : 'push_pin_outlined'"
    dense flat class="ml-4"
  >
    <q-tooltip>{{ isHeaderFixed ? 'Unfix Header' : 'Fix Header' }}</q-tooltip>
  </q-toggle>


  <q-toggle
    v-model="is_confirm"
    :icon="is_confirm ? 'done_all' : 'unpublished'"
    dense flat class="ml-4"
  >
    <!-- <q-tooltip>{{ is_confirm ? 'Unfix Header' : 'Fix Header' }}</q-tooltip> -->
  </q-toggle>

  <!-- Fix Teacher Column Toggle -->
  <q-toggle v-model="isTeacherColFixed" :icon="isTeacherColFixed ? 'lock' : 'lock_open'" dense flat class="ml-2">
  <q-tooltip>{{ isTeacherColFixed ? 'Unfix Teacher Column' : 'Fix Teacher Column' }}</q-tooltip>
  </q-toggle>

  <!-- Day Selector Tabs -->
  <q-tabs
    v-model="selectedDay"
    dense
    class="ml-4"
    active-color="primary"
    indicator-color="primary"
    align="left"
    narrow-indicator
  >
    <q-tab v-for="day in dayOptions" :key="day" :name="day" :label="day" />
  </q-tabs>
  <!-- Add Random Colors Button -->
  <q-btn @click="assignRandomColors" label="Assign Colors" color="secondary" dense class="ml-4" icon="palette" />
  <ScheduleFilters
      :schedule-data="schedule_data"
      :days="props.days"
      @filter-changed="handleFilterChange"
    />
</div>
<q-btn label="update_grid_fun" color="primary" @click="update_grid_fun()" />









<div class=" relative "   >



























<!-- Main Scroll Container -->
<div class="schedule-grid-scroll-container w-full px-2 overflow-auto" style="height: calc(100vh - 200px);"> <!-- Adjusted height, ensure this accounts for controls + bottom bar -->
  <!-- Zoomable Grid Content -->
    <!-- Intermediate div for scaling -->
    <div class="relative my-2 pb-10 pr-10"
      :style="{
        transform: `scale(${zoomLevel/100})`,
        transformOrigin: 'top left',
      }"
      @contextmenu.prevent="handleRightClick"
    >




    <div

        class="py-1 bg-gray-300   border-b border-gray-200 w-max"
        :class="{ 'sticky top-0 z-40': isHeaderFixed,'hidden':!isHeaderFixed }"
      >
        <div class="flex flex-nowrap"> <!-- Ensure header items don't wrap -->
          <div class="teacher-cell flex items-center justify-center font-medium text-gray-700 border-r border-gray-200 bg-gray-300"

          >
            Teacher

          </div>
          <div class="flex flex-nowrap bg-gray-100">
            <div
              v-for=" day in filteredDays"
              :key="day.originalIndex"
              class="day-column flex-shrink-0"
            >
              <!-- Day Name Header -->
              <div class="p-0 absolute top-0 z-30 bg-gray-300 text-sm text-center w-full border-b border-gray-400"
              >
                {{ day.name }}
              </div>
              <div
                v-for="(period, periodIndex) in props.periods"
                :key="`${day.originalIndex}-${periodIndex}`"
                 :class="period_code_selected==day.originalIndex +'-'+periodIndex ?'period-cell-selected':''"
                class="period-cell-head  mt-4 text-sm text-center w-[70px] h-12"
              >
                {{ period }}
              </div>
            </div>
          </div>
        </div>
      </div>








      <div class=" "> <!-- Add w-max here, remove positioning/padding -->



      <!-- Actual grid content, let it define its natural width -->
      <div class="schedule-grid w-max"> <!-- Add w-max here, remove positioning/padding -->
      <!-- Removed redundant overflow wrapper -->

      <div
        id="HeaderRow"
        class="py-1 bg-gray-300   border-b border-gray-200 w-max"
        :class="{ 'hidden top-0 z-40': isHeaderFixed }"
      >
        <div class="flex flex-nowrap"> <!-- Ensure header items don't wrap -->
          <div class="teacher-cell flex items-center justify-center font-medium text-gray-700 border-r border-gray-200 bg-gray-300"
               :class="{ 'sticky left-0 z-50': isTeacherColFixed }"
          >
            Teacher
          </div>
          <div class="flex flex-nowrap bg-gray-100">
            <div
              v-for=" day in filteredDays"
              :key="day.originalIndex"
              class="day-column flex-shrink-0"
            >
              <!-- Day Name Header -->
              <div class="p-0 absolute top-0 z-30 bg-gray-300 text-sm text-center w-full border-b border-gray-400"
              >
                {{ day.name }}
              </div>
              <div
                v-for="(period, periodIndex) in props.periods"
                :key="`${day.originalIndex}-${periodIndex}`"
                 :class="period_code_selected==day.originalIndex +'-'+periodIndex ?'period-cell-selected':''"
                class="period-cell-head mt-4 text-sm text-center w-[70px] h-12 "
              >
                {{ period }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scrollable Teacher Rows Container -->
      <div> <!-- Removed conditional padding-top -->





          <!-- v-for="(teacherData, teacherIndex) in schedule_data" -->

        <div
        v-for="(teacherData, teacherIndex) in (filteredScheduleData?.length ? filteredScheduleData : schedule_data) || []"
        :key="teacherIndex"
          class="teacher-row flex flex-nowrap min-w-max relative"
          :class="selected_period.teacher==teacherIndex ? 'bg-red-50 text-lg z-20' : 'hover:bg-gray-50'"

 v-show ="teacherData.teacher.show"
          >



         <div id="teacher-col"
            class="teacher-cell flex items-center"
            :class="{
              'sticky left-0 z-30 bg-white': isTeacherColFixed, // Apply sticky classes when fixed
              'bg-red-50': selected_period.teacher === teacherIndex && !isTeacherColFixed, // Selected background when not fixed
              'bg-gray-50': selected_period.teacher !== teacherIndex && !isTeacherColFixed // Default background when not fixed
            }">

          <div class="truncate px-2 text-lg flex items-center flex-nowrap overflow-visible rounded"
               :style="{ backgroundColor: teacherData.teacher?.c_bg, color: teacherData.teacher?.c_text }"

          >
          <div class="p-0 absolute top-0 right-10 scale-50 h-full w-8">

              <q-toggle v-model="teacherData.teacher.show" />
              <q-btn @click="teacherIdSelected=teacherData.teacher.id;dialog_teacher=true" icon="person" flat dense></q-btn>
            </div>
        <q-badge v-if="getEmptyPeriodCount(teacherData)?.empty>0"  class="  mx-1" rounded color="red" :label="getEmptyPeriodCount(teacherData)?.empty" />
            {{ teacherData.teacher?.teacher_cute }}
            <q-tooltip
            transition-show="rotate"
            transition-hide="rotate"
            anchor="top end" self="top left"
            :delay="500"
            class="text-body2  whitespace-nowrap">

            <q-badge class="w-12  mx-1"
          rounded
          :color="getEmptyPeriodCount(teacherData)?.empty==0? 'green':''"
          >
          {{ getEmptyPeriodCount(teacherData)?.val  }}

          <!-- :class="getEmptyPeriodCount(teacherData)?.empty==0?'bg-green-400':''" -->

        </q-badge>
    {{ teacherData.teacher?.name }}

</q-tooltip>
</div>

          </div>



          <!-- Period Grid Cells -->
          <div class="flex flex-nowrap ">
            <div
              v-for=" day  in filteredDays"
              :key="day.originalIndex"
              class="day-column flex-shrink-0  "
            >
              <!-- Vertical divider (optional, cell borders handle this) -->
              <!-- <div class="p-0 w-0.5 bg-gray-400"></div> -->
              <div
            v-for="(period, periodIndex) in periods"
            :key="`${day.originalIndex}-${periodIndex}`"
            class="period-cell hover:scale-125"
            :style="{
        backgroundColor: teacherData[`d${day.originalIndex+1}p${periodIndex+1}`]?.c_bg || '#ffffff',
        color: teacherData[`d${day.originalIndex+1}p${periodIndex+1}`]?.c_text || '#333333',
         ' border-l-2 border-gray-800   bg-black h-12 ': periodIndex==0,
      }"
            :class="period_code_selected==day.originalIndex +'-'+periodIndex +'-'+teacherIndex?'period-cell-selected':''"
            @click="handleCellClick(teacherIndex, day.originalIndex, periodIndex, teacherData[`d${day.originalIndex+1}p${periodIndex+1}`])"
            @mouseenter="set_hover_period(day.originalIndex,periodIndex)"
          >


<div class="p-0 cursor-default w-full h-full   relative        "


>




<div class="p-0 absolute -top-4 -left-1  w-2 h-12"
:class="periodIndex==0?' border-l-2 border-gray-800  ':''"
></div>
<!-- Ensure inner div fills cell -->




            <!-- Use the new ScheduleCell component -->
            <ScheduleCell
              :is_confirm="is_confirm"
              :teacher_data="teacherData"
              :day-index="day.originalIndex"
              :period-index="periodIndex"
              :period_code="`d${day.originalIndex+1}p${periodIndex+1}`"
              :selected-period="selected_period"
              :hover-period="hover_period"
              :conflicts="conflicts"
              :locked="isLocked(teacherIndex, day.originalIndex, periodIndex)"
              @toggle-lock="() => toggleLock(teacherIndex, day.originalIndex, periodIndex)"
              @toggle-disable="() => toggleDisable(teacherIndex, day.originalIndex, periodIndex)"
              @update_grid ="update_grid_fun"
              />
              <!-- :disabled="isDisabled(teacherIndex, day.originalIndex, periodIndex)" -->
</div>
          </div>
            </div>

        </div> <!-- Close teacher row v-for container -->
      </div> <!-- Close schedule-grid -->
        </div>
    </div>
</div>
  </div>
 </div>
 </div>
  <!-- Bottom Toolbar -->
  <q-bar class="fixed bottom-0 left-0 right-0 bg-primary text-white   z-50">

 <!-- Add Random Colors Button -->
 <q-btn @click="assignRandomColors" label="Assign Colors" color="secondary" dense class="ml-4" icon="palette" />

<!-- Save Random Colors to DB Button -->
<q-btn @click="saveRandomColorsToDb" label="Save Colors" color="positive" dense class="ml-2" :loading="isSavingColors" icon="save" />


    <q-btn flat round dense icon="account_balance">
      <q-tooltip>Institution Settings</q-tooltip>
    </q-btn>
    <q-btn flat round dense icon="person">
      <q-tooltip>Teacher Mode</q-tooltip>
    </q-btn>
    <q-btn flat round dense icon="school">
      <q-tooltip>Class Settings</q-tooltip>
    </q-btn>
    <q-btn flat round dense icon="book">
      <q-tooltip>Subject/Resource Manager</q-tooltip>
    </q-btn>
    <q-btn flat round dense icon="meeting_room">
      <q-tooltip>Room/Place Editor</q-tooltip>
    </q-btn>
    <q-btn flat round dense icon="settings">
      <q-tooltip>View/Export Settings</q-tooltip>
    </q-btn>
    <q-space />
  </q-bar>
  school_id:{{school_id}} <br>
  teacherIdSelected:{{teacherIdSelected}} <br>
  <br>
  <br>
  <br>
  <br>
  <br>
  <br>
  <br>
  <q-dialog
      v-model="dialog_teacher"
      persistent
       maximized
      transition-show="slide-up"
      transition-hide="slide-down"
    >
      <q-card class="bg-primary text-white">
        <q-bar>
          <q-space />



          <q-btn dense flat icon="close" v-close-popup>
            <q-tooltip class="bg-white text-primary">Close</q-tooltip>
          </q-btn>
        </q-bar>

        <teacher-schedule
      v-if="school_id && teacherIdSelected"
      :teacher-id="teacherIdSelected"
      :school-id="school_id"

    />


      </q-card>
    </q-dialog>

</template>


<script setup>
import { ref, computed,onUnmounted,watch  } from 'vue';
// import period_label from './Schedule_display_grid_comp/ScheduleGrid_comp/period_label.vue';
// import GridControls from './Schedule_admin_new_comp/GridControls.vue';
import ScheduleCell from './components/ScheduleCell.vue'; // Import the new component
import TeacherSchedule from './components/TeacherSchedule.vue';
// import NProgress from 'nprogress';
import { usePage } from '@inertiajs/vue3'
import { useQuasar } from 'quasar'
import { useAppStore } from '@/Stores/AppStore'
import { storeToRefs } from 'pinia'
import ScheduleFilters from './components/filtered_teachers.vue';
// Get store methods and state
const appStore = useAppStore()
const { loading, error } = storeToRefs(appStore)
const { fetchData } = appStore

const $q = useQuasar()


const props = defineProps({


days: {
type: Array,
default: () => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']
},
periods: {
type: Number,
default: 8
},
school_id: {
type: Number,

},

scheduleData: {
type: Array,
default: () => []
}
});



const page = usePage();
// Add these refs for filtered data
const filteredScheduleData = ref([]);

const scheduleNewData = computed(() => page.props.schedule_new_data)
const schedule_data = ref( page.props.schedule_new_data)

// State for fixed positions
const isHeaderFixed = ref(true);
const teacherIdSelected = ref(null);
const dialog_teacher = ref(false);

const conflicts = ref([]);
const overall_conflict_counter = ref(null);
const isTeacherColFixed = ref(true); // Default to true if you want it fixed initially
const is_confirm = ref(false); // Default to true if you want it fixed initially

// Zoom functionality
const selectedDay = ref('All'); // State for day filter

// Update the handleFilterChange function
// Update the handleFilterChange function
const handleFilterChange = ({ filteredTeachers, selectedDays }) => {
  // Initialize with current schedule data if no filtered teachers
  filteredScheduleData.value = filteredTeachers?.length ? filteredTeachers : schedule_data.value || [];

  // Update days filtering
  if (selectedDays?.length > 0) {
    filteredDays.value = selectedDays.map(day => ({
      name: day,
      originalIndex: props.days.indexOf(day)
    }));
  } else {
    // Reset to all days if nothing is selected
    filteredDays.value = props.days.map((day, index) => ({
      name: day,
      originalIndex: index
    }));
  }
};
// Add a watcher for selectedDay changes
watch(selectedDay, (newValue) => {
  if (newValue === 'All') {
    filteredDays.value = props.days.map((day, index) => ({
      name: day,
      originalIndex: index
    }));
  } else {
    const index = props.days.findIndex(day => day === newValue);
    if (index !== -1) {
      filteredDays.value = [{
        name: newValue,
        originalIndex: index
      }];
    }
  }
});
// Add a watcher to update filteredScheduleData when schedule_data changes
watch(() => schedule_data.value, (newValue) => {
  if (!filteredScheduleData.value.length) {
    filteredScheduleData.value = newValue || [];
  }
}, { immediate: true });


// Options for the day selector dropdown
const dayOptions = computed(() => ['All', ...props.days]);

// Computed property to get the days to display based on the filter
const filteredDays = ref(props.days.map((day, index) => ({
  name: day,
  originalIndex: index
})));


const zoomLevel = ref(100);
const isSavingColors = ref(false);

const zoomFactor = computed(() => zoomLevel.value / 100);

const zoomIn = () => {
zoomLevel.value = Math.min(zoomLevel.value + 10, 150);
};

const zoomOut = () => {
zoomLevel.value = Math.max(zoomLevel.value - 10, 50);
};

// Toggle functions for fixed positions
const toggleHeaderFix = () => {
  isHeaderFixed.value = !isHeaderFixed.value;
  // console.log("Header Fixed:", isHeaderFixed.value);
};

const toggleTeacherColFix = () => {
  isTeacherColFixed.value = !isTeacherColFixed.value;
};

// Function to assign random colors
const assignRandomColors = () => {
  schedule_data.value.forEach(item => {
    if (item.teacher) {
      // Generate random RGB values
      const r = Math.floor(Math.random() * 256);
      const g = Math.floor(Math.random() * 256);
      const b = Math.floor(Math.random() * 256);
      const bgColor = `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;

      // Calculate luminance to determine text color (black or white)
      // Formula: Y = 0.299*R + 0.587*G + 0.114*B
      const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
      const textColor = luminance > 0.5 ? '#000000' : '#FFFFFF'; // Use black for light bg, white for dark bg

      // Assign colors to the teacher object
      // Ensure c_bg and c_text properties exist or are added dynamically
      item.teacher.c_bg = bgColor;
      item.teacher.c_text = textColor;
    }
  });
};


const emit = defineEmits(['cell-click']);
const hover_period = ref({
day:null,period:null,teacher:null}
);
const selected_period = ref({
day:null,period:null,teacher:null}
);

const lockedCells = ref({});
const disabledCells = ref({});

const toggleLock = (teacherIndex, dayIndex, periodIndex) => {
const key = `${teacherIndex}-${dayIndex}-${periodIndex}`;
lockedCells.value[key] = !lockedCells.value[key];
};

const toggleDisable = (teacherIndex, dayIndex, periodIndex) => {
const key = `${teacherIndex}-${dayIndex}-${periodIndex}`;
disabledCells.value[key] = !disabledCells.value[key];
};

const isLocked = (teacherIndex, dayIndex, periodIndex) => {
const key = `${teacherIndex}-${dayIndex}-${periodIndex}`;
return lockedCells.value[key] || false;
};

const isDisabled = (teacherIndex, dayIndex, periodIndex) => {
const key = `${teacherIndex}-${dayIndex}-${periodIndex}`;
return disabledCells.value[key] || false;
};

const col_head_period = ref(null)
const  period_code_hover = ref(null)
const  period_code_selected = ref(null)
const set_col_head_hover_period = ( period ) => {
col_head_period.value =period
// console.log(col_head_period.value)
};
const hoverTimeout = ref(null);
const set_hover_period = (day1, period1) => {
if (hoverTimeout.value) {
clearTimeout(hoverTimeout.value);
}

hoverTimeout.value = setTimeout(() => {
hover_period.value.day = day1;
hover_period.value.period = period1;
period_code_hover.value = day1 + '-' + period1;
// console.log(period_code_hover.value);
}, 500);
};
const getCellContent = (teacherIndex, dayIndex, periodIndex) => {
const item = props.scheduleData[teacherIndex]?.[dayIndex]?.[periodIndex];
return item || { empty: true, id: `empty-${teacherIndex}-${dayIndex}-${periodIndex}` };
};

const handleCellClick = (teacherIndex, dayIndex, periodIndex, cellData) => {
period_code_selected.value=dayIndex +'-'+periodIndex+'-'+teacherIndex
// console.log('Clicked Cell:', period_code_selected.value, 'Data:', cellData)

selected_period.value.day=dayIndex
selected_period.value.period=periodIndex
selected_period.value.teacher=teacherIndex
getConflictsForPeriod (teacherIndex,dayIndex, periodIndex)
// Pass cellData along if needed by the parent
emit('cell-click', { teacherIndex, dayIndex, periodIndex });



};


// Function to get conflicts for a specific period
const getConflictsForPeriod = (teacherIndex,dayIndex, periodIndex) => {
  const periodCode = `d${dayIndex + 1}p${periodIndex + 1}`;
  const conflicts2 = [];

  if (!schedule_data.value) {
     conflicts.value=conflicts2
  } ; // Guard clause

  schedule_data.value.forEach((teacherData, index) => {
    if (index === teacherIndex) return;
    const scheduleItem = teacherData[periodCode];
    if (scheduleItem && scheduleItem.classroom) { // Check if there's a schedule item with a classroom
      conflicts2.push({
        teacher: teacherData.teacher?.teacher_cute || 'Unknown Teacher',
        classroom: scheduleItem.classroom,
        subject: scheduleItem.subject_cute || 'Unknown Subject',
        // Add any other relevant info you might want to display
      });
    }
  });
  conflicts.value=  conflicts2;
};








const handleRightClick = (event) => {
if (period_code_selected.value) {
period_code_selected.value = null;
selected_period.value = {
  day: null,
  period: null,
  teacher: null
};
emit('cell-click', {
  teacherIndex: null,
  dayIndex: null,
  periodIndex: null
});
}
};

onUnmounted(() => {
if (hoverTimeout.value) {
clearTimeout(hoverTimeout.value);
}
});




// Replace the old saveRandomColorsToDb function with:
const saveRandomColorsToDb = () => {
    if (!props.school_id) {
        $q.notify({ type: 'negative', message: 'Please select a school first.' })
        return
    }

    isSavingColors.value = true

    fetchData({
        endpoint: route('schedules.assign_colors'),
        method: 'post',
        data: { school_id: props.school_id },
        successMessage: 'Colors saved successfully!',
        errorMessage: 'Failed to save colors',
        onSuccess: (response) => {
            // Optional: Add any additional success handling
        },
        onError: (error) => {
            console.error("Error saving colors:", error)
        },
        onBefore: () => {
            isSavingColors.value = true
        }
    }).finally(() => {
        isSavingColors.value = false
    })
}





// Replace update_grid_fun with this new function
const update_grid_fun = () => {
    if (!props.school_id) {
        $q.notify({ type: 'negative', message: 'Please select a school first.' })
        return
    }

    fetchData({
        endpoint: route('admin.schedules.get_data', { school_id: props.school_id }),
        successMessage: 'Schedule data refreshed successfully!',
        errorMessage: 'Failed to refresh schedule data',
        onSuccess: (response) => {
            schedule_data.value = response?.teachers_data
            overall_conflict_counter.value = response?.overall_conflict_counter
        }
    })
}
update_grid_fun()

const getEmptyPeriodCount = (teacherData) => {
  if (!teacherData) return 0;
// console.log(teacherData)
  let filledCount = 0;
  const totalPeriods = props.days.length * props.periods;

  // Iterate through all possible period slots
  for (let d = 0; d < props.days.length; d++) {
    for (let p = 0; p < props.periods; p++) {
      const periodCode = `d${d + 1}p${p + 1}`;
      // Check if the period exists and has data (is not considered empty)
      if (teacherData[periodCode]) {

        filledCount++;
      }
    }
  }
  // Empty count is total possible slots minus the filled ones

  var empty=teacherData?.period_code_empty?.length
  var filled= filledCount
  var total= teacherData?.period_code_empty?.length + filledCount
  return  {
      empty: empty,
      filled: filled,

    total:  total,
    val: empty==0?   total: empty +' / ' + total

  }

};
</script>

<style scoped>
.sh1{
/* box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 1px 2px; */
box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px;

}



.sh1:hover{
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
}


.tttt{

    display: inline-block;
                outline: 0;
                cursor: pointer;
                border: 2px solid #000;
                border-radius: 3px;
                color: #000;
                background: #fff;
                font-size: 20px;
                font-weight: 600;
                line-height: 28px;
                padding: 12px 20px;
                text-align:center;
                transition-duration: .15s;
                transition-property: all;
                transition-timing-function: cubic-bezier(.4,0,.2,1);

}

.tttt:hover{
 background: rgb(251, 193, 245);

}


.schedule-grid {
display: flex;
flex-direction: column;
border: 1px solid #e2e8f0;
border-radius: 0.5rem;
overflow: hidden;
box-shadow: 0 1px 3px rgba(0,0,0,0.1);

min-width: 100%;
position: relative;
overflow-y: auto;
}

.header-row {
display: flex;
background: #f8fafc;
border-bottom: 2px solid #e2e8f0;
position: sticky;
top: 0;
z-index: 10;
background: white;
box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-cell {

text-align: center;
font-weight: 600;
color: #334155;
border-right: 1px solid #e2e8f0;
}

.teacher-header {
width: 150px;
height: 50px;
min-height: 50px;
background: #eff6ff;
color: #1e40af;
}

.day-header {
flex: 1;
min-width: 0;
background: #f8fafc;
}

.teacher-row {
display: flex;
border-bottom: 1px solid #e2e8f0;
transition: background 0.2s;
}

.teacher-row:hover {
background: #f8fafc;
}

.teacher-cell {


/* background: #f8f9fa; */ /* Base background is now handled by dynamic classes */
border-right: 1px solid #e2e8f0;
font-weight: 500;
color: #334155;
display: flex;

font-size: 0.8rem;
}

.day-column {
position: relative;
flex: none;
display: flex;
flex-direction: row;
border-right: 1px solid #e2e8f0;
/* height: 50px; */
align-items: stretch;
overflow: visible;
}

.period-cell {

    cursor: pointer;
aspect-ratio: 1;
box-sizing: border-box;
border: 0.5px solid #e2e8f0;
cursor: pointer;
padding: 0.25rem;
transition: all 0.2s;
display: flex;
flex-direction: column;
justify-content: center;
background: white;
overflow: hidden;
font-size: 0.7rem;
text-align: center;
}

.period-cell-head {


aspect-ratio: 1;
box-sizing: border-box;
border: 0.5px solid #e2e8f0;


transition: all 0.2s;
display: flex;
flex-direction: column;
justify-content: center;
overflow: hidden;
font-size: 0.7rem;
text-align: center;

}

.period-cell-head , .period-cell {

width: 90px;
height: 30px;

}
.teacher-cell {
width: 150px;
/* background-color: #2b28f6; */

/* margin: 0px    0px    0px 50px   ; */
/* height: 50px;
min-height: 50px; */
}

.day-column::-webkit-scrollbar {
height: 4px;
}

.day-column::-webkit-scrollbar-thumb {
background: #cbd5e1;
border-radius: 2px;
}

.period-cell:hover {
/* background: #eff6ff; */
/* border-color: #3b82f6; */
z-index: 1;
color:black;



}

.period-cell.disabled {
opacity: 0.5;
pointer-events: none;
}

.period-cell.locked {
background-color: #f0f0f0;
}

.period-cell-selected  {

/* color:yellow; */
 box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px;
border-width:1px;
z-index: 1;
/* transform: scale(1.25); */
}

/* .period-cell:active {
transform: scale(0.98);
} */

.sticky-div {
position: sticky;
top: 0; /* Distance from top when sticking */
z-index: 10; /* Ensure it stays above other content */
}
</style>
