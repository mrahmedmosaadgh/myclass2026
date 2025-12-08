<template>
  <div class="timing-manager">
    <!-- Time Slots Configuration -->

      <!-- Add this after the q-tab-panels -->
  <div class="preview-section q-mt-lg">
    <q-expansion-item
      icon="preview"
      label="Schedule Preview"
      default-opened
    >
      <SchedulePreview
        :timing="timing"
        :time-slots="timeSlots"
      />
    </q-expansion-item>
  </div>
    <div class="time-slots-config bg-grey-2 p-4 mb-4 rounded-borders">
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-subtitle1 q-my-none">Time Slots Configuration</h4>
        <div class="flex gap-2">
          <q-input
            v-model="defaultStartTime"
            type="time"
            dense
            label="Default Start"
            style="width: 120px"
          />
          <q-input
            v-model="defaultEndTime"
            type="time"
            dense
            label="Default End"
            style="width: 120px"
          />
          <q-btn
            flat
            dense
            icon="add"
            color="primary"
            @click="addTimeSlot"
          >
            <q-tooltip>Add Time Slot</q-tooltip>
          </q-btn>
        </div>
      </div>

      <div class="time-slots-list flex gap-4 flex-wrap">
        <q-chip
          v-for="(slot, index) in timeSlots"
          :key="index"
          removable
          @remove="removeTimeSlot(index)"
          :removable="index !== 0"
          color="primary"
          text-color="white"
        >
          Slot {{ index + 1 }}
          <q-tooltip>
            {{ slot.from }} - {{ slot.to }}
          </q-tooltip>
        </q-chip>
      </div>
    </div>

    <!-- Actions Bar -->
    <div class="flex justify-between mb-4">
      <h3 class="text-lg font-semibold">Schedule Timing Manager</h3>
      <div class="actions flex gap-2">
        <q-btn
          color="secondary"
          label="Copy Current Day to All"
          icon="content_copy"
          @click="copyCurrentDayToAll"
          :disable="!hasCurrentDayPeriods"
          v-if="activeDay=='sunday'"
        />
        <!-- activeDay:{{ activeDay=='sunday' }} -->
        <q-btn
          color="primary"
          @click="saveTiming"
          icon="save"
          label="Save"
        />
      </div>
    </div>

    <!-- Day Tabs -->
    <q-tabs
      v-model="activeDay"
      class="bg-grey-2"
      dense
      align="justify"
    >
      <q-tab
        v-for="day in days"
        :key="day"
        :name="day.toLowerCase()"
        :label="day"
      />
    </q-tabs>

    <!-- Day Panels -->
    <q-tab-panels v-model="activeDay" animated>
      <q-tab-panel
        v-for="(day, index) in days"
        :key="day"
        :name="day.toLowerCase()"
      >
        <DaySchedule
          :periods="timing[`d${index + 1}`] || []"
          :day-code="`d${index + 1}`"
          :time-slots="timeSlots"
          :default-start-time="defaultStartTime"
          :default-end-time="defaultEndTime"
          @update:periods="updateDayPeriods($event, index + 1)"
        />
      </q-tab-panel>
    </q-tab-panels>
  </div>
</template>

<script setup>
import { ref, computed ,onMounted} from 'vue';
import { useQuasar } from 'quasar';
import DaySchedule from './DaySchedule.vue';
import SchedulePreview from './SchedulePreview.vue';
import { useAppStore } from '@/Stores/AppStore';
import { storeToRefs } from 'pinia';

const $q = useQuasar();

// Get store methods and state
const appStore = useAppStore();
const { loading, error } = storeToRefs(appStore);
const { fetchData } = appStore;

// Update props to include school_id
const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  },

});

const emit = defineEmits(['update:modelValue']);

// Constants and refs
const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
const activeDay = ref('sunday');
const defaultStartTime = ref('08:00');
const defaultEndTime = ref('08:45');

const timeSlots = ref([{ id: 0, from: '08:00', to: '08:45',active:true }]);

// Initialize timing data
const timing = ref(props.modelValue || {
  d1: [], d2: [], d3: [], d4: [], d5: []
});

// Computed properties
const hasCurrentDayPeriods = computed(() => {
  const dayKey = `d${getCurrentDayIndex() + 1}`;
  return timing.value[dayKey]?.length > 0;
});

// Methods
const getCurrentDayIndex = () => {
  return days.findIndex(day =>
    day.toLowerCase() === activeDay.value.toLowerCase()
  );
};

const updateDayPeriods = (periods, dayNum) => {
  timing.value[`d${dayNum}`] = periods;
  emit('update:modelValue', timing.value);
};

const saveTiming = async () => {
  if (!validateTimingData()) {
    $q.notify({
      type: 'negative',
      message: 'Please check all required fields'
    });
    return;
  }
const school_id= localStorage.getItem('selected_school')
  if(!school_id){
    $q.notify({
      type: 'negative',
      message: 'Please check school_id'
    });
    return;
  }

const timingData = {
    school_id:school_id,
    // school_id: props.school_id,
    options: {
      timeSlots: timeSlots.value,
      defaultStartTime: defaultStartTime.value,
      defaultEndTime: defaultEndTime.value
    },
    timing: timing.value
  };

  await fetchData({
    endpoint: route('admin.schedules.timings.store'),
    method: 'POST',
    data: timingData,
    successMessage: 'Schedule timing saved successfully!',
    errorMessage: 'Failed to save schedule timing',
    onSuccess: (response) => {
      emit('update:modelValue', timing.value);
    },
    onError: (error) => {
      console.error('Save failed:', error);
    }
  });
};

const addTimeSlot = () => {
  const newSlot = {
    id: timeSlots.value.length,
    from: defaultStartTime.value,
    to: defaultEndTime.value
  };
  timeSlots.value.push(newSlot);
};

const removeTimeSlot = (index) => {
  if (index === 0) return;

  $q.dialog({
    title: 'Confirm Removal',
    message: 'Remove this time slot from all periods?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    timeSlots.value = timeSlots.value.filter((_, i) => i !== index);

    // Clean up time slot data from all periods
    Object.keys(timing.value).forEach(day => {
      timing.value[day].forEach(period => {
        delete period[`from${index + 1}`];
        delete period[`to${index + 1}`];
      });
    });
  });
};

const copyCurrentDayToAll = () => {
  const currentDayIndex = getCurrentDayIndex();
  const sourceDayKey = `d${currentDayIndex + 1}`;
  const sourcePeriods = timing.value[sourceDayKey];

  if (!sourcePeriods?.length) return;

  $q.dialog({
    title: 'Confirm Copy',
    message: 'Copy current day schedule to all other days?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    days.forEach((_, index) => {
      const targetDayKey = `d${index + 1}`;
      if (targetDayKey === sourceDayKey) return;

      timing.value[targetDayKey] = sourcePeriods.map(period => ({
        ...period,
        period_code: period.period_code.includes('break')
          ? period.period_code
          : period.period_code.replace(sourceDayKey, targetDayKey)
      }));
    });

    emit('update:modelValue', timing.value);
    $q.notify({
      type: 'positive',
      message: 'Schedule copied to all days successfully'
    });
  });
};

// Add validation method
const validateTimingData = () => {
  if (!timeSlots.value.length) {
    $q.notify({
      type: 'warning',
      message: 'At least one time slot is required'
    });
    return false;
  }

  // Validate timing data
  for (const dayKey of Object.keys(timing.value)) {
    const periods = timing.value[dayKey];
    if (!periods?.length) continue;

    for (const period of periods) {
      if (!period.label || !period.timeSlots?.length) {
        $q.notify({
          type: 'warning',
          message: `Invalid configuration in ${dayKey}`
        });
        return false;
      }

      // Check first time slot (required)
      const firstSlot = period.timeSlots[0];
      if (!firstSlot.from || !firstSlot.to || !firstSlot.active) {
        $q.notify({
          type: 'warning',
          message: `Primary time slot required in ${dayKey}`
        });
        return false;
      }
    }
  }

  return true;
};













// Update the loadTimingData function
const loadTimingData = async () => {
  const school_id = localStorage.getItem('selected_school');
  if (!school_id) {
    $q.notify({
      type: 'warning',
      message: 'No school selected'
    });
    return;
  }

  await fetchData({
    endpoint: '/admin/schedules/timings_show_data2',
    data:{ school_id },
    // endpoint: route('admin.schedules.timings.show_data', { school_id }),
    method: 'post',
    onSuccess: (response) => {
      console.log('Response:', response); // Debug log

      // Check if we have valid data
      if (response && response.data) {
        // Update time slots configuration
        if (response.data.options) {
          if (Array.isArray(response.data.options.timeSlots)) {
            timeSlots.value = response.data.options.timeSlots;
          }
          if (response.data.options.defaultStartTime) {
            defaultStartTime.value = response.data.options.defaultStartTime;
          }
          if (response.data.options.defaultEndTime) {
            defaultEndTime.value = response.data.options.defaultEndTime;
          }
        }

        // Update timing data if it exists
        if (response.data.timing) {
          timing.value = response.data.timing;
        }

        $q.notify({
          type: 'positive',
          message: 'Schedule timing loaded successfully'
        });
      } else {
        // Handle case when no data exists yet
        $q.notify({
          type: 'info',
          message: 'No existing schedule configuration found'
        });
      }
    },
    onError: (error) => {
      console.error('Failed to load timing data:', error);
      $q.notify({
        type: 'negative',
        message: 'Failed to load timing configuration'
      });
    }
  });
};

// Add a debug method
const debugTimingData = () => {
  const school_id = localStorage.getItem('selected_school');
  fetch(`/admin/schedules/timings_show_data/${school_id}`)
    .then(response => response.json())
    .then(data => {
      console.log('Debug - Raw Response:', data);
    })
    .catch(error => {
      console.error('Debug - Fetch Error:', error);
    });
};

// Update onMounted to include debug
onMounted(async () => {
  debugTimingData(); // Add this line to help debug
  await loadTimingData();
});
</script>
