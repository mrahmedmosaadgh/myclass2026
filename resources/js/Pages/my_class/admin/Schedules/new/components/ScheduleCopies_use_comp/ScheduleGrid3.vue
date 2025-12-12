<template>
    <div class="relative h-[80vh] w-full">
      <div class="schedule-grid pt-16" @contextmenu.prevent="handleRightClick">

        <!-- Sticky Header with Periods -->
        <section v-if="teachersWithEmptyRow.length > 0" class="sticky top-0 z-40 bg-white shadow-md border-b border-gray-200">
          <div class="flex w-max">
            <!-- Sticky Teacher Header -->
            <div class="sticky left-0 z-50 w-[140px] h-12 flex items-center px-3 font-medium text-gray-700 bg-white border-r border-gray-200">
              Teacher
            </div>

            <!-- Periods Row -->
            <div class="flex flex-nowrap">
              <div
                v-for="(period, periodIndex) in periods"
                :key="periodIndex"
                class="w-[70px] h-12 flex items-center justify-center text-sm font-medium bg-gray-100 border-r border-gray-300"
              >
                {{ period }}
              </div>
            </div>
          </div>
        </section>

        <!-- Scrollable Teacher Rows -->
        <div class="overflow-auto h-[calc(80vh-3rem)]">
          <div
            v-for="(teacher, teacherIndex) in teachersWithEmptyRow"
            :key="teacherIndex"
            class="flex w-max"
            :class="selected_period.teacher === teacherIndex ? 'bg-red-50 z-20' : 'hover:bg-gray-50'"
          >
            <!-- Sticky Teacher Name -->
            <div
              class="sticky left-0 z-30 w-[140px] h-12 flex items-center px-2 border-r border-gray-200 bg-white"
              :class="selected_period.teacher === teacherIndex ? 'bg-red-500 text-white' : ''"
            >
              {{ teacher.name }}
            </div>

            <!-- All Periods per Row -->
            <div class="flex flex-nowrap">
              <div
                v-for="(period, periodIndex) in periods"
                :key="periodIndex"
                class="flex flex-col"
              >
                <div
                  v-for="(day, dayIndex) in props.days"
                  :key="dayIndex"
                  class="period-cell hover:scale-105 transition-all duration-200 ease-in-out"
                  @click="handleCellClick(teacherIndex, dayIndex, periodIndex)"
                >
                  {{ getCellContent(teacherIndex, dayIndex, periodIndex) }}
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </template>



<script setup>
import { ref, computed,onUnmounted } from 'vue';
import period_label from './Schedule_display_grid_comp/ScheduleGrid_comp/period_label.vue';
import ScheduleGridControls from './ScheduleGridControls.vue';

const props = defineProps({
  schoolId: {
    type: [String, Number],
    required: true
  },
  scheduleCopyId: {
    type: [String, Number],
    required: true
  },
  teachers: {
    type: Array,
    required: true,
    default: () => []
  },
  days: {
    type: Array,
    default: () => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']
  },
  periods: {
    type: Number,
    default: 8
  },
  scheduleData: {
    type: Array,
    default: () => []
  }
});



const teachersWithEmptyRow = computed(() => {
  return [
    { id: null, name: '' }, // Empty row
    ...props.teachers
  ];
});

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
  console.log(col_head_period.value)
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
    console.log(period_code_hover.value);
  }, 500);
};
const getCellContent = (teacherIndex, dayIndex, periodIndex) => {
  const item = props.scheduleData[teacherIndex]?.[dayIndex]?.[periodIndex];
  return item || { empty: true, id: `empty-${teacherIndex}-${dayIndex}-${periodIndex}` };
};

const handleCellClick = (teacherIndex, dayIndex, periodIndex) => {
  period_code_selected.value=dayIndex +'-'+periodIndex+'-'+teacherIndex
  console.log(period_code_selected.value)

  selected_period.value.day=dayIndex
  selected_period.value.period=periodIndex
  selected_period.value.teacher=teacherIndex

  emit('cell-click', { teacherIndex, dayIndex, periodIndex });
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
</script>
<style scoped>
.schedule-grid {
    display: flex;
    flex-direction: column;
    border-radius: 0.5rem;
    width: fit-content;
    min-width: 100%;
    position: relative;
  }

  .teacher-row {
    border-bottom: 1px solid #e2e8f0;
  }

  .day-column {
    flex: none;
    display: flex;
    flex-direction: column;
  }

  .period-cell {
  width: 70px;
  height: 50px;
  border: 0.5px solid #e2e8f0;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  text-align: center;
  cursor: pointer;
}
  .period-cell:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    z-index: 1;
  }

  .period-cell-selected {
    background: #2b28f6;
    color: yellow;
    border: 2px solid #5b82f6;
    z-index: 1;
    transform: scale(1.1);
  }

  .sticky {
    position: sticky;
  }
</style>
