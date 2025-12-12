<template>
  <div class="schedule-preview">
    <!-- Time Slot Selector -->
    <div class="mb-4 bg-grey-2 p-4 rounded-borders">
      <div class="flex items-center justify-between">
        <h5 class="text-subtitle1 q-my-none">Time Slot Display Options</h5>
        <div class="flex gap-2">
          <q-select
            v-model="selectedSlots"
            :options="availableSlots"
            multiple
            dense
            options-dense
            label="Select Time Slots"
            style="min-width: 200px"
          >
            <template v-slot:option="{ opt }">
              <q-checkbox v-model="selectedSlots" :val="opt">
                Slot {{ opt + 1 }}
              </q-checkbox>
            </template>
          </q-select>
        </div>
      </div>
    </div>

    <!-- Schedule Preview Grid -->
    <div class="schedule-grid q-pa-md">
      <q-table
        flat
        :rows="scheduleRows"
        :columns="tableColumns"
        row-key="period_code"
        dense
      >
        <template v-slot:header="props">
          <q-tr :props="props">
            <q-th auto-width>Period</q-th>
            <q-th v-for="day in days" :key="day">{{ day }}</q-th>
          </q-tr>
        </template>

        <template v-slot:body="props">
          <q-tr :props="props">
            <q-td auto-width>{{ props.row.period_code }}</q-td>
            <q-td v-for="day in days" :key="day">
              <div v-for="slot in getActiveTimeSlots(props.row, day.toLowerCase())" :key="slot.from">
                {{ formatTimeSlot(slot) }}
              </div>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
// import SchedulePreview from './SchedulePreview.vue';

const props = defineProps({
  timing: {
    type: Object,
    required: true
  },
  timeSlots: {
    type: Array,
    required: true
  }
});

const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
const LOCAL_STORAGE_KEY = 'schedule-preview-slots';

// State
const selectedSlots = ref([0]); // Default to first slot

// Computed
const availableSlots = computed(() => {
  return Array.from({ length: props.timeSlots.length }, (_, i) => i);
});

const scheduleRows = computed(() => {
  const rows = [];
  const firstDay = props.timing.d1 || [];
  
  firstDay.forEach(period => {
    rows.push({
      period_code: period.period_code,
      ...days.reduce((acc, day, index) => {
        const dayKey = `d${index + 1}`;
        const periodData = props.timing[dayKey]?.find(p => 
          p.period_code === period.period_code
        );
        acc[day.toLowerCase()] = periodData;
        return acc;
      }, {})
    });
  });
  
  return rows;
});

const tableColumns = computed(() => [
  {
    name: 'period',
    label: 'Period',
    field: 'period_code',
    align: 'left'
  },
  ...days.map(day => ({
    name: day.toLowerCase(),
    label: day,
    field: day.toLowerCase(),
    align: 'left'
  }))
]);

// Methods
const getTimeSlot = (row, day, slotIndex) => {
  const period = row[day];
  if (!period) return '';
  
  const from = period[`from${slotIndex + 1}`];
  const to = period[`to${slotIndex + 1}`];
  
  return period;
  return from && to ? `${from} - ${to}` : '';
};

const getActiveTimeSlots = (row, day) => {
  const period = row[day];
  if (!period?.timeSlots) return [];
  return period.timeSlots.filter(slot => slot.active);
};

const formatTimeSlot = (slot) => {
  return slot.from && slot.to ? `${slot.from} - ${slot.to}` : '';
};

const saveSelectedSlots = () => {
  localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(selectedSlots.value));
};

const loadSelectedSlots = () => {
  const saved = localStorage.getItem(LOCAL_STORAGE_KEY);
  if (saved) {
    selectedSlots.value = JSON.parse(saved);
  }
};

// Watchers
watch(selectedSlots, () => {
  saveSelectedSlots();
}, { deep: true });

// Lifecycle
onMounted(() => {
  loadSelectedSlots();
});
</script>

<style scoped>
.schedule-preview {
  max-width: 100%;
  overflow-x: auto;
}

.q-table th, .q-table td {
  white-space: nowrap;
  padding: 8px;
}
</style>