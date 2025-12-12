<template>
  <div class="schedule-container22  p-4    -my-8">
    <div v-if="loading" class="loading-indicator">Loading schedule...</div>

    <div v-else>
      <!-- Day filter controls -->
      <div class="filter-controls mb-2  ">
        <div class="flex items-center justify-between bg-gray-400">
          <div class="text-lg font-medium ">
              <div class="  bg-white relative">
                <!-- {{ teacherName }}'s Schedule -->


                <!-- textColor="white" -->
            </div>
        </div>
        
        </div>
      </div>

      <div class="schedule-grid22 slide-up-animation">
        <!-- Header row with days  's Schedule-->
        <div class="header-row">
          <div class="teacher-header   bg-blue-500 relative">
            <Indicator_left v-if="true"
class="  text-xl  "
               :label="`${teacherName}`"
               direction="left"
               position="top-right"
                label_class="w-fit  whitespace-nowrap    text-white px-4  -my-6  rounded  text-xs p-1    " 
                color="blue"
                />
              <q-btn
              color="primary"
              label="Today"
              size="sm"
              dense
              class="today-btn"
              @click="selectToday"
              :disable="!isTodayAvailable"
              />
              Period
          </div>
          <div v-for="(day,day_index) in filteredDays" :key="day.name"
          class="day-header22 relative overflow-visible"
:class="isToday(day) ? 'bg-blue-600' : 'bg-gray-400'"
@click="handleDaySelection(day_index)"
          >
          <q-btn>
              {{ day.name }}
            </q-btn>

            <TodayIndicator
              v-if="isToday(day)"
               label="Today"
              position="top-right"
              color="#19a232"
              textColor="white"
            />
          </div>
        </div>

        <!-- Schedule grid -->
        <div class="schedule-body22">
          <div v-for="periodIndex in periods" :key="periodIndex" class="period-row22">
            <div class="period-label">{{ periodIndex }}</div>
            <div v-for="day in filteredDays" :key="day.name" class="period-cell22 relative"
                 :class="[getCellClasses(day.originalIndex, periodIndex-1), { 'today-cell': isToday(day) }]">
              <div v-if="getCellContent(day.originalIndex, periodIndex-1)"
                   class="cell-content22"
                   :style="getCellStyles(day.originalIndex, periodIndex-1)">
                {{ getCellContent(day.originalIndex, periodIndex-1).subject_cute }}
                <div class="classroom"> {{ getCellContent(day.originalIndex, periodIndex-1).classroom }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useQuasar } from 'quasar';
import { useAppStore } from '@/Stores/AppStore';
import { storeToRefs } from 'pinia';
import TodayIndicator from '@/Components/Indicators/TodayIndicator.vue';
import Indicator_left from '@/Components/Indicators/Indicator_left.vue';

const props = defineProps({
  teacherId: {
    type: Number,
    required: true
  },
  schoolId: {
    type: Number,
    required: true
  },
  days: {
    type: Array,
    default: () => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'].map((day, index) => ({
      name: day,
      originalIndex: index
    }))
  },
  periods: {
    type: Number,
    default: 8
  }
});

const $q = useQuasar();
const appStore = useAppStore();
const { loading } = storeToRefs(appStore);
const { fetchData } = appStore;

const teacherData = ref(null);
const teacherName = ref('');
const showAllDays = ref(true);
const selectedDay = ref(null);

// Get current day of week (0 = Sunday, 1 = Monday, etc.)
const getCurrentDayOfWeek = () => {
  const dayOfWeek = new Date().getDay();
  return dayOfWeek; // Sunday is 0, Monday is 1, etc.
};

// Check if a day is today
const isToday = (day) => {
  return day.originalIndex === getCurrentDayOfWeek();
};

// Check if today is available in the schedule
const isTodayAvailable = computed(() => {
  const todayIndex = getCurrentDayOfWeek();
  return props.days.some(day => day.originalIndex === todayIndex);
});

// Select today's day

const selectToday = () => {
  const todayIndex = getCurrentDayOfWeek();
  const todayDay = props.days.find(day => day.originalIndex === todayIndex);

  if (todayDay) {
    selectedDay.value = todayDay.originalIndex;
    showAllDays.value = false;
    is_DaySelection.value=!is_DaySelection.value
    showAllDays.value = is_DaySelection.value?true:false;

    // $q.notify({
    //   type: 'positive',
    //   message: `Showing schedule for today (${todayDay.name})`
    // });
  } else {
    // $q.notify({
    //   type: 'negative',
    //   message: 'Today is not in the available schedule days'
    // });
  }
};

// Create day options for the dropdown
const dayOptions = computed(() => {
  return props.days.map(day => ({
    label: day.name,
    value: day.originalIndex
  }));
});

// Filtered days based on selection
const filteredDays = computed(() => {
  if (showAllDays.value) {
    return props.days;
  } else if (selectedDay.value !== null) {
    const selectedDayObj = props.days.find(day => day.originalIndex === selectedDay.value);
    return selectedDayObj ? [selectedDayObj] : props.days;
  } else {
    return props.days;
  }
});

// Handle day selection from dropdown
const is_DaySelection=ref(true)
const handleDaySelection = (dayIndex) => {
    is_DaySelection.value=!is_DaySelection.value
  if (dayIndex !== null) {
    selectedDay.value = dayIndex;
    showAllDays.value = is_DaySelection.value?true:false;
  }
};

// Handle toggle for showing all days
const handleShowAllToggle = (value) => {
  if (value) {
    // When toggling to show all, we keep the selected day but show all
    showAllDays.value = true;
  } else {
    // When toggling to show one day, we use the selected day or default to first day
    showAllDays.value = false;
    if (selectedDay.value === null) {
      selectedDay.value = props.days[0]?.originalIndex || 0;
    }
  }
};

// Watch for changes in teacherId or schoolId and reload data
watch(() => props.teacherId, (newTeacherId, oldTeacherId) => {
  if (newTeacherId && newTeacherId !== oldTeacherId) {
    fetchTeacherSchedule();
  }
});

const fetchTeacherSchedule = () => {
  if (!props.schoolId || !props.teacherId) {
    $q.notify({ type: 'negative', message: 'School ID and Teacher ID are required' });
    return;
  }

  loading.value = true;
  axios.get(route('teacher.schedule.data', {
    school_id: props.schoolId,
    teacher_id: props.teacherId
  }))
    .then(response => {
      teacherData.value = response.data.teacher_data;
      teacherName.value = response.data.teacher_name || `Teacher #${props.teacherId}`;
      $q.notify({
        type: 'positive',
        message: 'Schedule loaded successfully'
      });
    })
    .catch(error => {
      console.error('Error loading teacher schedule:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to load teacher schedule'
      });
    })
    .finally(() => {
      loading.value = false;
    });
};

// Get content for a specific cell
const getCellContent = (dayIndex, periodIndex) => {
  if (!teacherData.value) return null;

  const periodCode = `d${dayIndex + 1}p${periodIndex + 1}`;
  return teacherData.value[periodCode] || null;
};

// Get classes for a cell based on its content
const getCellClasses = (dayIndex, periodIndex) => {
  const content = getCellContent(dayIndex, periodIndex);
  return {
    'has-content': !!content,
    'empty-cell': !content
  };
};

// Get styles for a cell based on its content
const getCellStyles = (dayIndex, periodIndex) => {
  const content = getCellContent(dayIndex, periodIndex);
  if (!content) return {};

  return {
    backgroundColor: content.c_bg || '#dbeafe',
    color: content.c_text || '#000000'
  };
};

onMounted(() => {
  fetchTeacherSchedule();
  // Default selected day to first day
  selectedDay.value = props.days[0]?.originalIndex || 0;
});
</script>

<style scoped>
.schedule-container22 {
  width: 100%;
  overflow-x: auto;
  margin-bottom: 1rem;
  overflow: hidden; /* Ensure animation stays within container */
}

.filter-controls {
  padding: 0.5rem;
  /* background-color: #f9fafb; */
  color: #0096fa;
  border-radius: 0.5rem;
  margin-bottom: 0.75rem;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.today-btn {
  min-width: 70px;
}

.schedule-grid22 {
  display: flex;
  flex-direction: column;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: visible;
}

/* Animation for appearing from bottom */
.slide-up-animation {
  animation: slideUp 0.5s ease-out forwards;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.header-row {
  display: flex;
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 10;
}

.teacher-header {
  width: 100px;
  padding: 0.75rem;
  text-align: center;
  font-weight: 600;
  /* background: #eff6ff; */
  /* color: #1e40af; */
}

.day-header22 {
  flex: 1;
  padding: 0.75rem;
  text-align: center;
  font-weight: 600;
  /* background: #1984ef; */
  transition: background-color 0.3s ease;
  position: relative;

}

.day-header22:hover {
  background: #0096fa;
}

.today-indicator {
  position: absolute;
  top: 0;
  right: 0;
  background: #19a232;
  color: white;
  font-size: 0.6rem;
  padding: 0.1rem 0.3rem;
  border-radius: 0 0 0 0.25rem;
}

.schedule-body22 {
  display: flex;
  flex-direction: column;
}

.period-row22 {
  display: flex;
  border-bottom: 1px solid #e2e8f0;
}

.period-label {
  width: 100px;
  padding: 0.75rem;
  text-align: center;
  font-weight: 500;
  /* background: #f8f9fa; */
}

.period-cell22 {
  flex: 1;
  padding: 0.5rem;
  min-height: 60px;
  border-right: 1px solid #e2e8f0;
  font-size: 0.875rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  transition: all 0.2s ease;
  background-color: #e9e5e5;
}

.period-cell22:hover {
  transform: scale(1.02);
  z-index: 5;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}



.cell-content22 {
  text-align: center;
  font-weight: 500;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  border-radius: 0.25rem;
  padding: 0.25rem;
}

.empty-cell {
  background-color: #f9f9f9;
}

.classroom {
  font-size: 0.75rem;
  margin-top: 0.25rem;
  font-weight: normal;
}

.loading-indicator {
  padding: 2rem;
  text-align: center;
  color: #6b7280;
  font-weight: 500;
}

.today-cell {
  border: 1px solid #909090;
  /* border-left:  2px  #3f3f3f ;
  border-right:  2px  #767676 ; */
  background-color: #c7c7c7;
}
</style>











