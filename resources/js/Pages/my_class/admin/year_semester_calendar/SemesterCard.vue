<template>
  <q-card 
    flat 
    bordered 
    :class="[
      semester.active ? 'border-primary shadow-2' : 'border-grey-3',
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
      
      <div class="row items-center q-gutter-xs">
        <q-btn
          flat
          round
          dense
          icon="calendar_view_month"
          color="indigo-5"
          size="sm"
          @click="showTimeline = true"
        >
          <q-tooltip>View as Calendar Timeline</q-tooltip>
        </q-btn>

        <!-- Edit Mode Toggle -->
        <q-btn
          flat
          round
          dense
          :icon="editMode ? 'edit_off' : 'edit'"
          :color="editMode ? 'primary' : 'grey-5'"
          size="sm"
          @click="editMode = !editMode"
        >
          <q-tooltip>{{ editMode ? 'Exit Edit Mode' : 'Edit Semester' }}</q-tooltip>
        </q-btn>

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

    <!-- READ-ONLY VIEW -->
    <q-card-section v-if="!editMode" class="q-pa-md">
      <div class="row q-col-gutter-sm q-mb-md">
        <div class="col-6 column">
          <span class="text-caption text-grey-5 text-weight-bold uppercase">Start Date</span>
          <span class="text-weight-bold text-grey-8">{{ form.start_date || '—' }}</span>
        </div>
        <div class="col-6 column">
          <span class="text-caption text-grey-5 text-weight-bold uppercase">End Date</span>
          <span class="text-weight-bold text-grey-8">{{ form.end_date || '—' }}</span>
        </div>
        <div class="col-12 q-mt-xs">
          <q-chip dense color="grey-2" text-color="grey-7" icon="schedule" class="text-caption">
            {{ semester.calculated_days ? `${semester.calculated_days} days (${semester.calculated_weeks} weeks)` : 'No dates set' }}
          </q-chip>
        </div>
      </div>

      <!-- Status Section -->
      <div class="bg-grey-1 rounded-lg q-pa-sm border-grey-2 border">
        <div class="row items-center justify-between q-mb-xs">
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
        <div class="row q-col-gutter-sm text-caption text-grey-8">
          <div class="col">Records: <span class="text-weight-bold">{{ semester.calendar_count }} days</span></div>
          <div class="col text-right">Duration: <span class="text-weight-bold">{{ semester.calculated_days }}d ({{ semester.calculated_weeks }}w)</span></div>
        </div>
      </div>

      <!-- Inline Events List -->
      <q-expansion-item
        v-if="semester.calendar_count > 0"
        dense
        dense-toggle
        icon="event_note"
        label="Events & Special Days"
        header-class="text-caption text-weight-bold text-grey-7 q-px-none q-pt-sm"
        @before-show="loadEvents"
      >
        <div class="q-mt-sm">
          <q-spinner v-if="eventsLoading" size="sm" color="primary" class="q-ml-sm" />
          <template v-else>

            <!-- ── COLOR MAP LEGEND ─────────────────────────────────────── -->
            <div class="row q-gutter-xs q-mb-sm flex-wrap">
              <q-chip
                v-for="leg in colorLegend"
                :key="leg.label"
                dense
                square
                :color="leg.color"
                text-color="white"
                size="xs"
                class="text-weight-bold"
              >
                <q-icon :name="leg.icon" size="10px" class="q-mr-xs" />
                {{ leg.label }} ({{ leg.count }})
              </q-chip>
            </div>

            <!-- ── VACATION RANGES SUMMARY ─────────────────────────────── -->
            <div v-if="vacationRanges.length" class="q-mb-sm">
              <div class="text-caption text-weight-bold text-grey-6 q-mb-xs uppercase">Vacation Periods</div>
              <q-markup-table dense flat bordered class="text-caption" separator="cell">
                <thead>
                  <tr class="bg-grey-2">
                    <th class="text-left">Name</th>
                    <th class="text-center">From</th>
                    <th class="text-center">To</th>
                    <th class="text-center">Days</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="vr in vacationRanges" :key="vr.name + vr.from">
                    <td class="text-weight-bold text-grey-9">{{ vr.name || '—' }}</td>
                    <td class="text-center text-grey-7">{{ vr.from }}</td>
                    <td class="text-center text-grey-7">{{ vr.to }}</td>
                    <td class="text-center">
                      <q-badge color="grey-6" outline dense class="text-weight-black">{{ vr.days }}d</q-badge>
                    </td>
                  </tr>
                </tbody>
              </q-markup-table>
            </div>

            <!-- ── DAY FILTERS ─────────────────────────────────────────── -->
            <div class="row q-gutter-sm q-mb-xs">
              <q-toggle v-model="includeFriday"  label="Friday"   dense size="xs" color="teal" class="text-caption text-grey-7" />
              <q-toggle v-model="includeSaturday" label="Saturday" dense size="xs" color="teal" class="text-caption text-grey-7" />
            </div>

            <!-- ── DETAIL LIST ─────────────────────────────────────────── -->
            <div v-if="filteredEvents.length === 0" class="text-caption text-grey-5 q-py-xs text-center">
              No events match the current filters.
            </div>
            <q-list v-else dense separator class="rounded-borders">
              <q-item v-for="ev in filteredEvents" :key="ev.id" dense class="q-px-none q-py-xs">
                <q-item-section avatar>
                  <q-icon :name="statusIcon(ev.status)" :color="statusColor(ev.status)" size="xs" />
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-caption text-weight-bold">{{ ev.date }}</q-item-label>
                  <q-item-label v-if="ev.label" caption>{{ ev.label }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-badge :color="statusColor(ev.status)" dense outline class="text-weight-bold">{{ statusLabel(ev.status) }}</q-badge>
                </q-item-section>
              </q-item>
            </q-list>

          </template>
        </div>
      </q-expansion-item>
    </q-card-section>

    <!-- EDIT VIEW -->
    <q-card-section v-else class="q-pa-md">
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

        <div class="col-12">
          <q-input
            v-model="form.name"
            label="Semester Name"
            filled
            dense
            @change="saveChanges"
            class="text-caption"
          />
        </div>
      </div>

      <!-- Status Section -->
      <div class="bg-grey-1 rounded-lg q-pa-sm border-grey-2 border">
        <div class="row items-center justify-between q-mb-xs">
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
        <div class="row q-col-gutter-sm text-caption text-grey-8">
          <div class="col">Records: <span class="text-weight-bold">{{ semester.calendar_count }} days</span></div>
          <div class="col text-right">Duration: <span class="text-weight-bold">{{ semester.calculated_days }}d ({{ semester.calculated_weeks }}w)</span></div>
        </div>
      </div>
    </q-card-section>
  </q-card>

  <!-- Calendar Timeline Dialog -->
  <q-dialog v-model="showTimeline" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="column">
      <q-toolbar class="bg-indigo-8 text-white">
        <q-btn flat round dense icon="close" v-close-popup class="q-mr-sm" />
        <q-toolbar-title class="text-weight-bold">
          <q-icon name="calendar_view_month" class="q-mr-sm" />
          {{ semester.name }} — Calendar Timeline
        </q-toolbar-title>
        <span class="text-caption text-white-7">
          {{ semester.start_date?.split('T')[0] }} → {{ semester.end_date?.split('T')[0] }}
        </span>
      </q-toolbar>
      <q-card-section class="col scroll q-pa-md">
        <CalendarPreview
          v-if="showTimeline && props.yearId"
          :year-id="props.yearId"
          :semester-id="semester.id"
        />
        <div v-else class="flex flex-center q-pa-xl text-grey-5">
          <q-icon name="info" size="sm" class="q-mr-sm" />
          Year ID not available — cannot load timeline.
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import CalendarPreview from './CalendarPreview.vue';

const props = defineProps({
  semester: Object,
  yearId:   { type: Number, default: null },
});

const editMode    = ref(false);
const showTimeline = ref(false);
const eventsLoading = ref(false);
const events = ref([]);
const eventsLoaded = ref(false);
const includeFriday = ref(false);
const includeSaturday = ref(false);

// day_of_week: 0=Sun, 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat
const filteredEvents = computed(() =>
  events.value.filter(ev => {
    if (ev.day_of_week === 5 && !includeFriday.value) return false;
    if (ev.day_of_week === 6 && !includeSaturday.value) return false;
    return true;
  })
);

// Color legend: count per status across ALL events (no filter)
const STATUS_META = [
  { status: 0, label: 'Day Off',   icon: 'beach_access', color: 'grey-6'      },
  { status: 2, label: 'Activity',  icon: 'sports',       color: 'blue-7'      },
  { status: 3, label: 'Test',      icon: 'quiz',         color: 'orange-8'    },
  { status: 4, label: 'Exam',      icon: 'school',       color: 'deep-orange' },
  { status: 5, label: 'Holiday',   icon: 'celebration',  color: 'green-7'     },
];

const colorLegend = computed(() =>
  STATUS_META
    .map(m => ({
      ...m,
      count: events.value.filter(e => e.status === m.status).length,
    }))
    .filter(m => m.count > 0)
);

// Group consecutive vacation (status=0) days with the same label into ranges
const vacationRanges = computed(() => {
  const vacs = events.value
    .filter(e => e.status === 0 && e.label)
    .sort((a, b) => a.date.localeCompare(b.date));

  const ranges = [];
  let current = null;

  for (const ev of vacs) {
    if (
      current &&
      current.name === ev.label &&
      daysDiff(current.to, ev.date) === 1
    ) {
      current.to = ev.date;
      current.days++;
    } else {
      if (current) ranges.push(current);
      current = { name: ev.label, from: ev.date, to: ev.date, days: 1 };
    }
  }
  if (current) ranges.push(current);
  return ranges;
});

const daysDiff = (d1, d2) => {
  const ms = new Date(d2) - new Date(d1);
  return Math.round(ms / 86400000);
};

const form = useForm({
  name: props.semester.name,
  start_date: props.semester.start_date ? props.semester.start_date.split('T')[0] : '',
  end_date: props.semester.end_date ? props.semester.end_date.split('T')[0] : '',
  active: props.semester.active,
  total_weeks: props.semester.total_weeks || '',
});

const weeksCount = ref(props.semester.calculated_weeks || '');

const loadEvents = async () => {
  if (eventsLoaded.value) return;
  eventsLoading.value = true;
  try {
    const res = await axios.get(route('admin.academic_calendar.semester.events', { semester: props.semester.id }));
    events.value = res.data;
    eventsLoaded.value = true;
  } catch (e) {
    events.value = [];
  } finally {
    eventsLoading.value = false;
  }
};

const statusIcon  = (s) => ({ 0: 'beach_access', 1: 'work', 2: 'sports', 3: 'quiz', 4: 'school' }[s] ?? 'circle');
const statusColor = (s) => ({ 0: 'grey', 1: 'green', 2: 'blue', 3: 'purple', 4: 'deep-orange' }[s] ?? 'grey');
const statusLabel = (s) => ({ 0: 'Day Off', 1: 'Work Day', 2: 'Activity', 3: 'Test', 4: 'Exam' }[s] ?? 'Unknown');

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

watch(() => props.semester.calculated_weeks, (val) => {
  weeksCount.value = val;
});
</script>

<style scoped>
.rounded-xl { border-radius: 12px; }
.h-2 { height: 8px; }
.w-2 { width: 8px; }
</style>
