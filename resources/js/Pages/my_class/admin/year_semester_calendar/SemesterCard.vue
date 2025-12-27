<template>
  <q-card 
    flat 
    bordered 
    :class="[
      semester.active ? 'border-primary shadow-2' : 'border-grey-3 hover:shadow-1',
      'transition-all duration-300 rounded-xl overflow-hidden'
    ]"
  >
    <!-- Header -->
    <q-card-section 
      :class="semester.active ? 'bg-blue-1' : 'bg-grey-1'" 
      class="row items-center justify-between q-py-sm"
    >
      <div class="row items-center q-gutter-sm">
        <q-avatar size="24px" color="primary" text-color="white" font-size="10px" class="text-weight-bold">
          S{{ semester.semester_number }}
        </q-avatar>
        <div class="column">
          <span class="text-weight-bold text-grey-9">{{ semester.name }}</span>
          <span class="text-caption text-grey-6 text-weight-medium">ID: #{{ semester.id }}</span>
        </div>
      </div>
      
      <div class="row items-center">
        <q-toggle
          :model-value="semester.active"
          @update:model-value="toggleActive"
          color="primary"
          size="md"
          label="ACTIVE"
          left-label
          class="text-weight-bold text-caption tracking-tighter"
        />
      </div>
    </q-card-section>

    <q-separator />

    <!-- Content -->
    <q-card-section class="q-pa-md">
      <div class="row q-col-gutter-sm q-mb-md">
        <div class="col-12">
          <q-input
            v-model="form.start_date"
            type="date"
            label="Start Date"
            filled
            dense
            @change="saveChanges"
            class="text-caption"
          />
        </div>
        
        <div class="col-12">
          <div class="row q-col-gutter-xs items-center">
            <div class="col">
              <q-input
                v-model="form.end_date"
                type="date"
                label="End Date"
                filled
                dense
                @change="saveChanges"
                class="text-caption"
              />
            </div>
            <div class="col-4">
              <q-input
                v-model="weeksCount"
                type="number"
                label="Weeks"
                filled
                dense
                @input="updateDateByWeeks"
                class="text-caption"
                suffix="W"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Status Section -->
      <div class="bg-grey-1 rounded-lg q-pa-sm border-grey-2 border">
        <div class="row items-center justify-between q-mb-sm">
          <div class="row items-center q-gutter-xs">
            <div :class="semester.calendar_count > 0 ? 'bg-green' : 'bg-amber'" class="h-2 w-2 rounded-full"></div>
            <span class="text-caption text-weight-bold text-grey-7">CALENDAR STATUS</span>
          </div>
          <q-badge 
            :color="semester.calendar_count > 0 ? 'green-1' : 'amber-1'" 
            :text-color="semester.calendar_count > 0 ? 'green-9' : 'amber-9'"
            class="text-weight-bold"
            dense
          >
            {{ semester.calendar_count > 0 ? 'CREATED' : 'MISSING' }}
          </q-badge>
        </div>

        <div class="row q-col-gutter-sm q-mb-sm text-caption text-grey-8 font-medium">
          <div class="col">Records: <span class="text-weight-bold">{{ semester.calendar_count }} days</span></div>
          <div class="col text-right">Duration: <span class="text-weight-bold">{{ semester.calculated_days }}d ({{ semester.calculated_weeks }}w)</span></div>
        </div>

        <q-banner v-if="needsCalendar" dense rounded class="bg-amber-1 text-amber-10 q-mb-sm text-caption">
          <template v-slot:avatar>
            <q-icon name="warning" color="amber-9" size="xs" />
          </template>
          Dates changed or missing. Generate calendar now.
        </q-banner>

        <q-btn 
          @click="generateCalendar"
          :loading="processing"
          :color="semester.calendar_count > 0 ? 'grey-2' : 'primary'"
          :text-color="semester.calendar_count > 0 ? 'grey-9' : 'white'"
          unelevated
          class="full-width text-weight-bold"
          size="sm"
          :label="semester.calendar_count > 0 ? 'Regenerate Calendar' : 'Create Calendar'"
          icon="event"
        />
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  semester: Object,
});

const form = useForm({
  name: props.semester.name,
  start_date: props.semester.start_date ? props.semester.start_date.split('T')[0] : '',
  end_date: props.semester.end_date ? props.semester.end_date.split('T')[0] : '',
  active: props.semester.active,
  total_weeks: props.semester.total_weeks || '',
});

const weeksCount = ref(props.semester.calculated_weeks || '');
const processing = ref(false);

const needsCalendar = computed(() => {
  return props.semester.calendar_count === 0 && form.start_date && form.end_date;
});

const updateDateByWeeks = () => {
  if (form.start_date && weeksCount.value) {
    const start = new Date(form.start_date);
    const end = new Date(start);
    end.setDate(start.getDate() + (weeksCount.value * 7) - 1);
    form.end_date = end.toISOString().split('T')[0];
    saveChanges();
  }
};

const saveChanges = () => {
  form.post(route('admin.academic_calendar.semester.update', props.semester.id), {
    preserveScroll: true,
  });
};

const toggleActive = () => {
  form.active = !form.active;
  saveChanges();
};

const generateCalendar = () => {
  processing.value = true;
  form.post(route('admin.academic_calendar.semester.generate', props.semester.id), {
    preserveScroll: true,
    onFinish: () => {
      processing.value = false;
    },
  });
};

// Sync internal count if props change
watch(() => props.semester.calculated_weeks, (val) => {
  weeksCount.value = val;
});
</script>

<style scoped>
.rounded-xl {
  border-radius: 12px;
}
.h-2 { height: 8px; }
.w-2 { width: 8px; }
</style>
