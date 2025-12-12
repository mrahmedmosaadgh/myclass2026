<template>

        <!-- Zoom Controls -->
        <div class="zoom-controls flex items-center gap-2 mb-2">
      <q-btn @click="zoomOut" icon="remove" dense flat />
      <q-slider
        v-model="zoomLevel"
        :min="50"
        :max="150"
        :step="10"
        style="width: 200px"
      />
      <q-btn @click="zoomIn" icon="add" dense flat />
      <span class="text-sm ml-2">{{zoomLevel}}%</span>
    </div>


    <div class="relative h-[80vh] w-full">
      <!-- Move overflow-auto to wrap scrollable part only -->
      <div class="schedule-grid-container overflow-auto h-full w-full"

      style="width: 120%;height:80vh  ;"
      >
        <div
          class="schedule-grid"
          :style="{
            transform: `scale(${zoomLevel/100})`,
            transformOrigin: 'top left',
            width: `${100/zoomLevel*100}%`,
            height: `${100/zoomLevel*100}%`
          }"
          @contextmenu.prevent="handleRightClick"
        >
          <!-- Sticky Header Row -->
          <div class="overflow-auto h-[calc(80vh-3rem)] "
          style="width: 120%;height:80vh  "
          >

          <section id="HeaderRow" class="  z-50 bg-white shadow-md border-b border-gray-200" v-if="teachersWithEmptyRow.length > 0">
            <div class="flex p-0 w-max">
              <div class="w-[148px]   left-[30px] bg-white h-12 flex items-center px-3 font-medium text-gray-700 border-r border-gray-200">
                Teacher
              </div>
              <div class="flex flex-nowrap bg-gray-100">
                <div
                  v-for="(day, dayIndex) in props.days"
                  :key="dayIndex"
                  class="day-column flex-shrink-0"
                >
                  <div class="p-0 w-0.5 bg-gray-300"> </div>
                  <div class="p-0 absolute  top-0 z-50 bg-gray-300   text-sm text-center  w-full"
                  >
              {{  day}}
            </div>
                  <div
                    v-for="(period, periodIndex) in periods"
                    :key="periodIndex"
                     :class="period_code_selected==dayIndex +'-'+periodIndex +'-'+teacherIndex?'period-cell-selected':''"
                    class="period-cell-head mt-2 text-sm text-center w-[70px] h-12"
                  >
                    {{ period }}
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Scrollable Teacher Rows -->

          <div class="overflow-y-auto h-[calc(80vh-3rem)] w-full">
            <div
              v-for="(teacher, teacherIndex) in teachersWithEmptyRow"
              :key="teacherIndex"
              class="teacher-row flex flex-nowrap pl-2 p-0.5 min-w-max relative"
              :class="selected_period.teacher==teacherIndex ? 'bg-red-50 text-lg z-20' : 'hover:bg-gray-50'"
            >
              <div
                class="w-[140px]   h-12 flex items-center"
                :class="selected_period.teacher==teacherIndex ? 'text-yellow-100 bg-red-500' : ''"
              >
                {{ teacher.name }}
              </div>
              <!-- Period Grid Cells -->
              <div class="flex flex-nowrap">
                <div
                  v-for="(day, dayIndex) in props.days"
                  :key="dayIndex"
                  class="day-column flex-shrink-0"
                >
                  <div class="p-0 w-0.5 bg-gray-400"></div>
                  <div
                v-for="(period, periodIndex) in periods"
                :key="periodIndex"
                class="period-cell hover:scale-125"
                :class="period_code_selected==dayIndex +'-'+periodIndex +'-'+teacherIndex?'period-cell-selected':''"
                @click="handleCellClick(teacherIndex, dayIndex, periodIndex)"
                @mouseenter="set_hover_period(dayIndex,periodIndex)"
              >
                <div class="p-0 relative">
                  <div
                    :class="selected_period.day==dayIndex && selected_period.period==periodIndex ?'border-r-2 border-blue-700':'opacity-0'"
                    class="absolute top-0 bottom-0 w-12 h-12 left-0 right-0 pointer-events-none p-2 z-30 hover:opacity-100 transition-opacity duration-200"
                  >
                    <div class="-m-4 w-14 h-14 bg-red-400 opacity-25"></div>
                  </div>
                  <div
                    :class="hover_period.day==dayIndex && hover_period.period==periodIndex ?'bg-blue-500':'bg-transparent'"
                    class="absolute top-0 bottom-0 w-12 h-12 left-0 right-0 pointer-events-none p-2 z-50 opacity-10 hover:opacity-100 transition-opacity duration-200"
                  ></div>
                </div>

                <ScheduleGridControls
                  :locked="isLocked(teacherIndex, dayIndex, periodIndex)"
                  :disabled="isDisabled(teacherIndex, dayIndex, periodIndex)"
                  @toggle-lock="toggleLock(teacherIndex, dayIndex, periodIndex)"
                  @toggle-disable="toggleDisable(teacherIndex, dayIndex, periodIndex)"
                />
                {{periodIndex}}
                <slot
                  name="cell"
                  :teacherIndex="teacherIndex"
                  :dayIndex="dayIndex"
                  :periodIndex="periodIndex"
                  :item="getCellContent(teacherIndex, dayIndex, periodIndex)"
                />
              </div>
                </div>
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
// import period_label from './Schedule_display_grid_comp/ScheduleGrid_comp/period_label.vue';
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

// Zoom functionality
const zoomLevel = ref(100);
const zoomFactor = computed(() => zoomLevel.value / 100);

const zoomIn = () => {
  zoomLevel.value = Math.min(zoomLevel.value + 10, 150);
};

const zoomOut = () => {
  zoomLevel.value = Math.max(zoomLevel.value - 10, 50);
};

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
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  width: fit-content;
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
  width: 150px;
  height: 50px;
  min-height: 50px;

  background: #f8f9fa;
  border-right: 1px solid #e2e8f0;
  font-weight: 500;
  color: #334155;
  display: flex;
  align-items: center;
  font-size: 0.8rem;
}

.day-column {
  position: relative;
  flex: none;
  display: flex;
  flex-direction: row;
  border-right: 1px solid #e2e8f0;
  height: 50px;
  align-items: stretch;
  overflow: visible;
}

.period-cell {
  width: 40px;
  height: 50px;

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
  width: 40px;
  height: 50px;

  aspect-ratio: 1;
  box-sizing: border-box;
  border: 0.5px solid #e2e8f0;
  cursor: pointer;
  padding: 0.25rem;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  justify-content: center;

  overflow: hidden;
  font-size: 0.7rem;
  text-align: center;
}
.day-column::-webkit-scrollbar {
  height: 4px;
}

.day-column::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}

.period-cell:hover {
  background: #eff6ff;
  border-color: #3b82f6;
  z-index: 1;
  color:black;
  transition: all 0.2s ease;
  transition-delay: 0.2s;

}

.period-cell.disabled {
  opacity: 0.5;
  pointer-events: none;
}

.period-cell.locked {
  background-color: #f0f0f0;
}

.period-cell-selected  {
  background: #2b28f6;
  color:yellow;
  border-color: #5b82f6;
  border-width:2px;
  z-index: 1;
  transform: scale(1.25);
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
