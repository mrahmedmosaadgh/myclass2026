<template>
  <div>
    <q-btn label="Maximized" color="primary" @click="dialog = true" />
    <q-dialog
      v-model="dialog"
      persistent
      :maximized="maximizedToggle"
      transition-show="slide-up"
      transition-hide="slide-down"
    >
      <q-card class="  ">
        <q-bar>
          <q-space />
          <q-btn dense flat icon="minimize" @click="maximizedToggle = false" :disable="!maximizedToggle">
              <q-tooltip v-if="maximizedToggle" class="bg-white text-primary">Minimize</q-tooltip>
            </q-btn>
            <!-- <q-btn dense flat icon="crop_square" @click="maximizedToggle = true" :disable="maximizedToggle">
                <q-tooltip v-if="!maximizedToggle" class="bg-white text-primary">Maximize</q-tooltip>
            </q-btn> -->
            <q-btn dense flat icon="close" v-close-popup>
                <q-tooltip class="bg-white text-primary">Close</q-tooltip>
            </q-btn>
        </q-bar>
 

          <ScheduleGrid
            :schoolId="schoolId"
            :scheduleCopyId="scheduleCopyId"
            :teachers="teachers"
            :days="['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']"
            :periods="periods"
            :schedule-data="schedule"
            @cell-click="handleCellClick"
          >
            <template #cell="{ teacherIndex, dayIndex, periodIndex, item }">
              <div
                class="schedule-cell"
                :style="{ backgroundColor: getCellColor(item) }"
              >
                <div v-if="!item.empty">
                  <div>P{{ periodIndex + 1 }}: {{ item.subject || 'Free' }}</div>
                  <div>{{ item.class || '' }}</div>
                  {{teacherIndex    }}
                  {{  dayIndex  }}
                </div>
              </div>
            </template>
          </ScheduleGrid>


      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
// import draggable from 'vuedraggable';
import { ref, watch, defineProps, defineEmits } from 'vue';
import ScheduleGrid from './ScheduleGrid.vue';

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
    required: false,
    default: () => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']
  },
  periods: {
    type: Number,
    required: true,
    default: 8
  },
  initialSchedule: {
    type: Array,
    default: () => []
  }
});
const dialog = ref(true)
const maximizedToggle = ref(true)
const emit = defineEmits(['schedule-change']);
const schedule = ref([]);

const initializeSchedule = () => {
  if (props.initialSchedule?.length > 0) {


    schedule.value = props.teachers.map((_, teacherIndex) => {
      return props.days.map((_, dayIndex) => {
        const existingPeriods = props.initialSchedule[teacherIndex]?.[dayIndex] || [];
        return Array(props.periods).fill().map((_, periodIndex) => {
          return existingPeriods[periodIndex] || {
            id: `empty-${teacherIndex}-${dayIndex}-${periodIndex}`,
            empty: true
          };
        });
      });
    });
  } else {
    schedule.value = props.teachers.map((_, teacherIndex) => {
      return props.days.map((_, dayIndex) => {
        return Array(props.periods).fill().map((_, periodIndex) => ({
          id: `empty-${teacherIndex}-${dayIndex}-${periodIndex}`,
          empty: true
        }));
      });
    });
  }
};

const onChange = (teacherIndex, dayIndex) => {
  emit('schedule-change', schedule.value);
};

const handleCellClick = ({ teacherIndex, dayIndex, periodIndex }) => {
  // Handle cell click if needed
};

const getCellColor = (item) => {
  if (!item || item.empty) return '#f8f9fa';
  const colors = ['#e3f2fd', '#e8f5e9', '#fff3e0', '#fce4ec', '#f3e5f5'];
  const hash = item.subject ? item.subject.charCodeAt(0) % colors.length : 0;
  return colors[hash];
};

// Initialize
initializeSchedule();
watch(() => props.teachers, initializeSchedule);
watch(() => props.days, initializeSchedule);
watch(() => props.periods, initializeSchedule);
</script>

<style scoped>
.schedule-cell {
  height: 100%;
  padding: 4px;
}
</style>
