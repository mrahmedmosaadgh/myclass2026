// i will continue update calendar feacher later
<template>
  <Head title="Academic Calendar Management" />

  <div class="q-pa-lg">
    <!-- Header -->
    <div class="q-mb-xl">
      <div class="row items-center q-mb-sm">
        <div class="col-auto">
          <div style="width: 6px; height: 40px; background: #1976d2; border-radius: 3px;" class="q-mr-md"></div>
        </div>
        <div class="col">
          <h1 class="text-h3 text-weight-bold q-ma-none">Academic Calendar</h1>
        </div>
      </div>
      <p class="text-grey-7 text-weight-medium q-ma-none">Manage academic years, semesters, and daily calendar records with precision.</p>
    </div>

    <!-- Create Year Section -->
    <YearForm />

    <!-- Years & Semesters List -->
    <div v-if="academicYears.length > 0" class="q-gutter-y-xl">
      <div v-for="year in academicYears" :key="year.id">
        <!-- Year Header -->
        <q-card flat bordered class="q-pa-md q-mb-lg">
          <div class="row items-end justify-between q-mb-lg">
            <div class="col">
              <div class="row items-center q-mb-sm q-gutter-sm">
                <q-toggle
                  :model-value="year.active"
                  @update:model-value="toggleYearActive(year)"
                  color="primary"
                  size="lg"
                  checked-icon="check"
                  unchecked-icon="clear"
                />
                <q-badge 
                  :color="year.active ? 'primary' : 'grey-5'" 
                  :text-color="year.active ? 'white' : 'grey-8'"
                  class="text-weight-bold"
                >
                  {{ year.active ? '✓ ACTIVE' : 'INACTIVE' }} YEAR
                </q-badge>
              </div>
              
              <h2 class="text-h4 text-weight-black q-ma-none q-mb-sm">{{ year.name }}</h2>
              
              <div class="row items-center q-gutter-md text-grey-7">
                <div class="row items-center q-gutter-xs">
                  <q-icon name="event" size="sm" />
                  <span class="text-weight-bold">{{ year.start_date.split('T')[0] }}</span>
                </div>
                <q-icon name="arrow_forward" size="xs" />
                <div class="row items-center q-gutter-xs">
                  <span class="text-weight-bold">{{ year.end_date.split('T')[0] }}</span>
                </div>
              </div>
            </div>
            
            <div class="col-auto">
              <div class="row q-gutter-md">
                <q-card flat bordered class="q-pa-md text-center" style="min-width: 120px">
                  <div class="text-caption text-grey-6 text-weight-bold">SEMESTERS</div>
                  <div class="text-h4 text-weight-black">{{ year.semesters.length }}</div>
                </q-card>
                
                <q-card flat bordered class="q-pa-md text-center" style="min-width: 120px">
                  <div class="text-caption text-grey-6 text-weight-bold">TOTAL DAYS</div>
                  <div class="text-h4 text-weight-black">{{ calculateTotalDays(year) }}</div>
                </q-card>
                
                <q-btn
                  @click="previewCalendar(year)"
                  color="primary"
                  unelevated
                  icon="visibility"
                  label="Preview Calendar"
                  class="text-weight-bold"
                  size="md"
                />
              </div>
            </div>
          </div>
        </q-card>

        <!-- Semesters Grid -->
        <div class="row q-col-gutter-md q-mb-lg">
          <div v-for="semester in year.semesters" :key="semester.id" class="col-12 col-md-6 col-xl-3">
            <SemesterCard :semester="semester" />
          </div>
        </div>

        <!-- Missing Days Analysis -->
        <MissingDaysList :yearId="year.id" />
        
        <!-- Divider for Multiple Years -->
        <q-separator v-if="academicYears.length > 1" class="q-my-xl" />
      </div>
    </div>
    
    <!-- Empty State -->
    <q-card v-else flat bordered class="q-pa-xl text-center">
      <q-icon name="event" size="80px" color="grey-4" class="q-mb-md" />
      <h3 class="text-h5 text-weight-black q-mb-sm">No Academic Years Found</h3>
      <p class="text-grey-6 text-weight-medium q-mb-lg" style="max-width: 500px; margin-left: auto; margin-right: auto;">
        Start by creating an academic year. We'll automatically generate the standard four semesters for you.
      </p>
    </q-card>

    <!-- Calendar Preview Dialog -->
    <q-dialog v-model="showCalendarDialog" maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card>
        <q-card-section class="bg-primary text-white row items-center q-pb-md">
          <div class="col">
            <div class="text-h5 text-weight-bold">Calendar Preview</div>
            <div class="text-subtitle2 text-blue-2">{{ selectedYear?.name }}</div>
          </div>
          <q-btn flat round dense icon="close" @click="closeDialog" />
        </q-card-section>

        <q-card-section class="q-pa-lg" style="max-height: calc(100vh - 100px); overflow-y: auto;">
          <CalendarPreview v-if="selectedYear" :yearId="selectedYear.id" />
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import YearForm from './YearForm.vue';
import SemesterCard from './SemesterCard.vue';
import MissingDaysList from './MissingDaysList.vue';
import CalendarPreview from './CalendarPreview.vue';

const props = defineProps({
  academicYears: Array,
});

const calculateTotalDays = (year) => {
  return year.semesters.reduce((acc, sem) => acc + sem.calendar_count, 0);
};

const toggleYearActive = (year) => {
  router.put(route('admin.academic_calendar.year.toggle', year.id), {}, {
    preserveScroll: true,
  });
};

const showCalendarDialog = ref(false);
const selectedYear = ref(null);

const previewCalendar = (year) => {
  selectedYear.value = year;
  showCalendarDialog.value = true;
};

const closeDialog = () => {
  showCalendarDialog.value = false;
  selectedYear.value = null;
};
</script>

<style scoped>
/* Custom fonts or styles if needed, mostly using Tailwind */
</style>
