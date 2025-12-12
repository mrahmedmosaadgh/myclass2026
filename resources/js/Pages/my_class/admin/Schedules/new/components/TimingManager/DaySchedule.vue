<template>
  <div class="day-schedule">
    <div class="my-2">

    <q-btn 
                    icon="add_alarm" 
                    color="primary" 
                    class="m-2"
                      
                    dense 
                    @click="addTimeSlot()"
                  >
                    <q-tooltip>Add Time Slot</q-tooltip>
                  </q-btn>
                  <q-btn 
                   
                  icon="delete" 
                  color="negative" 
                  
                  dense 
                  @click="removeTimeSlot()"
                >
                  <q-tooltip>Remove Time Slot</q-tooltip>
                </q-btn>
    </div>

    <div class="periods-list">
        
      <q-list bordered separator> 
        <q-item v-for="(period, index) in localPeriods" :key="index">
          <q-item-section>
            <div class="flex flex-col gap-2">
              <!-- Period Header -->
              <div class="flex items-center gap-4">

                <q-input 
                  v-model="period.label" 
                  label="Label"
                  dense
                  class="w-48"
                  :rules="[val => !!val || 'Label is required']"
                >
                  <template v-slot:append>
                    <q-badge color="primary" class="text-white">
                      {{ period.period_code }}
                    </q-badge>
                  </template>
                </q-input>
              </div>
              
              <!-- Time Slots -->
              <div 
                v-for="(slot, slotIndex) in period.timeSlots" 
                :key="slotIndex"
                class="flex items-center gap-4 q-pl-md"
                :class="{ 'bg-grey-1 q-pa-sm rounded-borders': slotIndex > 0 }"
              >
                <div class="flex items-center gap-1 w-24">
                  <q-checkbox
                    v-model="slot.active"
                    dense
                    class="q-mr-sm"
                  />
                  <span class="text-caption text-grey-8">Slot {{ slotIndex + 1 }}</span>
                </div>

                <q-input 
                  v-model="slot.from" 
                  type="time" 
                  :label="`From`" 
                  dense 
                  class="w-32"
                  :rules="[val => slotIndex === 0 ? !!val || 'Required' : true]"
                  :disable="!slot.active"
                  outlined
                />
                <q-input 
                  v-model="slot.to" 
                  type="time" 
                  :label="`To`" 
                  dense 
                  class="w-32"
                  :rules="[val => slotIndex === 0 ? !!val || 'Required' : true]"
                  :disable="!slot.active"
                  outlined
                />
                

              </div>
              
              <!-- Sounds Section -->
              <div class="flex items-center gap-4 q-pl-md border-t q-pt-sm">
                <q-input 
                  v-model="period.sound_start" 
                  label="Start Sound" 
                  dense 
                  class="w-32"
                  outlined
                />
                <q-input 
                  v-model="period.sound_before_end_5_min" 
                  label="5min Before" 
                  dense 
                  class="w-32"
                  outlined
                />
                <q-input 
                  v-model="period.sound_end" 
                  label="End Sound" 
                  dense 
                  class="w-32"
                  outlined
                />
                
                <!-- Actions -->
                <div class="flex gap-2">

                  <q-btn 
                    icon="delete" 
                    color="negative" 
                    flat 
                    dense 
                    @click="removePeriod(index)"
                  >
                    <q-tooltip>Remove Period</q-tooltip>
                  </q-btn>
                </div>
              </div>
            </div>
          </q-item-section>
        </q-item>
      </q-list>
    </div>

    <div class="actions mt-4">
      <q-btn
        color="primary"
        label="Add Period"
        @click="addPeriod"
        icon="add"
      />
      <q-btn
        color="secondary"
        label="Add Break"
        @click="addBreak"
        icon="coffee"
        class="ml-2"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch,onMounted } from 'vue';

const props = defineProps({
  periods: {
    type: Array,
    default: () => []
  },
  dayCode: {
    type: String,
    required: true
  },
  timeSlots: {
    type: Array,
    required: true
  },
  defaultStartTime: {
    type: String,
    default: '08:00'
  },
  defaultEndTime: {
    type: String,
    default: '08:45'
  }
});

const emit = defineEmits(['update:periods']);

const localPeriods = ref([...props.periods]);

const toggleExtraTime = (period) => {
  if (!period.from2) {
    period.from2 = period.from;
    period.to2 = period.to;
  } else if (!period.from3) {
    period.from3 = period.from;
    period.to3 = period.to;
  }
  emitUpdate();
};

const addPeriod = () => {
  const periodNum = localPeriods.value.filter(p => !p.period_code.includes('break')).length + 1;
  localPeriods.value.push(createEmptyPeriod(`${props.dayCode}p${periodNum}`));
  emitUpdate();
};

const addBreak = () => {
  const breakNum = localPeriods.value.filter(p => p.period_code.includes('break')).length + 1;
  localPeriods.value.push(createEmptyPeriod(`break${breakNum}`));
  emitUpdate();
};

const removePeriod = (index) => {
  localPeriods.value.splice(index, 1);
  emitUpdate();
};

const emitUpdate = () => {
  emit('update:periods', localPeriods.value);
};

const createEmptyPeriod = (code) => ({
  label: code.includes('break') ? `Break ${code.replace('break', '')}` : `Period ${code.replace(props.dayCode + 'p', '')}`,
  period_code: code,
  sound_start: '',
  sound_before_end_5_min: '',
  sound_end: '',
  timeSlots: [
    { 
      from: props.defaultStartTime, 
      to: props.defaultEndTime, 
      active: true 
    }
  ]
});

// Remove the period parameter since we'll apply to all periods
const addTimeSlot = () => {
  // Add time slot to all periods
  localPeriods.value.forEach(period => {
    period.timeSlots.push({
      from: props.defaultStartTime,
      to: props.defaultEndTime,
      active: true
    });
  });
  emitUpdate();
};

// Remove the period parameter since we'll apply to all periods
const removeTimeSlot = () => {
  // Get the last time slot index
  const lastSlotIndex = localPeriods.value[0]?.timeSlots.length - 1;
  
  // Don't remove if only one slot remains
  if (lastSlotIndex === 0) {
    $q.notify({
      type: 'warning',
      message: 'Cannot remove the primary time slot'
    });
    return;
  }

  // Remove last time slot from all periods
  localPeriods.value.forEach(period => {
    period.timeSlots.splice(lastSlotIndex, 1);
  });
  
  emitUpdate();
};

watch(() => props.periods, (newVal) => {
  localPeriods.value = [...newVal];
}, { deep: true });









</script>

<style scoped>
.periods-list :deep(.q-badge) {
  font-size: 0.8em;
  font-family: monospace;
}

.periods-list :deep(.q-input) {
  font-size: 0.95em;
}

.periods-list :deep(.q-item) {
  padding: 12px;
}
</style>