<template>
  <div class="relative-position min-h-64">
    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner-gears color="primary" size="80px" />
      <div class="text-grey-7 q-mt-md text-weight-bold">Organizing Calendar Data...</div>
    </div>

    <div v-else-if="calendarData.length === 0" class="flex flex-center column q-pa-xl text-grey-5">
      <q-icon name="event_busy" size="100px" />
      <div class="text-h6 q-mt-md">No calendar data found for this year.</div>
      <div class="text-caption">Generate semester calendars to see results here.</div>
    </div>

    <div v-else class="column no-wrap q-gutter-y-lg">
      <!-- Semester Tabs -->
      <q-tabs
        v-model="selectedSemester"
        dense
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="left"
        narrow-indicator
      >
        <q-tab 
          v-for="sem in semesters" 
          :key="sem.id" 
          :name="sem.id" 
          :label="`${sem.name} (${sem.count}d)`" 
          class="text-weight-black"
        />
      </q-tabs>

      <!-- Calendar Grid Container -->
      <div class="q-pa-md bg-grey-1 rounded-xl border-grey-2 border">
        <div class="calendar-grid">
          <!-- Header -->
          <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" 
               class="text-center text-weight-black text-caption text-grey-5 uppercase q-py-sm">
            {{ day }}
          </div>

          <!-- Calendar Days -->
          <div
            v-for="(day, index) in filteredCalendar"
            :key="index"
            :class="[
              'day-cell rounded-lg q-pa-xs column items-center justify-center cursor-pointer transition-all',
              getDayStyle(day)
            ]"
            @click="showDayDetails(day)"
          >
            <div class="text-[10px] text-weight-bold opacity-60">{{ formatDay(day.date) }}</div>
            <div class="text-subtitle1 text-weight-black">{{ formatDate(day.date) }}</div>
            <div v-if="day.week_number" class="text-[9px] text-weight-bold opacity-40">W{{ day.week_number }}</div>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="row q-gutter-md q-px-sm">
        <div v-for="item in legend" :key="item.label" class="row items-center q-gutter-xs">
          <div :class="item.colorClass" class="w-3 h-3 rounded-sm border"></div>
          <span class="text-caption text-weight-bold text-grey-7">{{ item.label }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  yearId: Number,
});

const loading = ref(true);
const calendarData = ref([]);
const selectedSemester = ref(null);

const legend = [
  { label: 'Work Day', colorClass: 'bg-blue-1 border-blue-6' },
  { label: 'Day Off', colorClass: 'bg-grey-1 border-grey-4' },
  { label: 'Activity', colorClass: 'bg-green-1 border-green-6' },
  { label: 'Test', colorClass: 'bg-amber-1 border-amber-6' },
  { label: 'Final Exam', colorClass: 'bg-red-1 border-red-6' },
];

const semesters = computed(() => {
  const semesterMap = {};
  calendarData.value.forEach(day => {
    if (!semesterMap[day.semester_id]) {
      semesterMap[day.semester_id] = {
        id: day.semester_id,
        name: `S${day.semester?.semester_number || '?'}`,
        count: 0,
      };
    }
    semesterMap[day.semester_id].count++;
  });
  return Object.values(semesterMap);
});

const filteredCalendar = computed(() => {
  if (!selectedSemester.value) return calendarData.value;
  return calendarData.value.filter(d => d.semester_id === selectedSemester.value);
});

onMounted(async () => {
  try {
    const response = await axios.get(`/admin/academic-calendar/year/${props.yearId}/calendar-data`);
    calendarData.value = response.data;
    if (semesters.value.length > 0) {
      selectedSemester.value = semesters.value[0].id;
    }
  } catch (error) {
    console.error('Error loading calendar:', error);
  } finally {
    loading.value = false;
  }
});

const getDayStyle = (day) => {
  switch (day.status) {
    case 1: return 'bg-blue-1 border-blue-6 text-blue-10 border';
    case 0: return 'bg-grey-1 border-grey-4 text-grey-6 border';
    case 2: return 'bg-green-1 border-green-6 text-green-10 border';
    case 3: return 'bg-amber-1 border-amber-6 text-amber-10 border';
    case 4: return 'bg-red-1 border-red-6 text-red-10 border';
    default: return 'bg-white border-grey-3 text-grey-9 border';
  }
};

const formatDate = (dateStr) => new Date(dateStr).getDate();
const formatDay = (dateStr) => new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short' });

const showDayDetails = (day) => console.log('Day details:', day);
</script>

<style scoped>
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.day-cell {
  aspect-ratio: 1/1;
  min-height: 60px;
}

.day-cell:hover {
  transform: scale(1.05);
  z-index: 10;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.bg-blue-1 { background: #eef2ff; }
.bg-green-1 { background: #f0fdf4; }
.bg-amber-1 { background: #fffbeb; }
.bg-red-1 { background: #fef2f2; }
.bg-grey-1 { background: #f9fafb; }

.border-blue-6 { border-color: #4f46e5; }
.border-green-6 { border-color: #16a34a; }
.border-amber-6 { border-color: #d97706; }
.border-red-6 { border-color: #dc2626; }

.text-blue-10 { color: #1e1b4b; }
.text-green-10 { color: #052e16; }
.text-amber-10 { color: #451a03; }
.text-red-10 { color: #450a0a; }

.rounded-xl { border-radius: 12px; }
.w-3 { width: 12px; }
.h-3 { height: 12px; }
</style>
