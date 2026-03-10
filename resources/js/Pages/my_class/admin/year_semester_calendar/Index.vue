// i will continue update calendar feacher later
<template>
  <Head title="Academic Calendar Management" />

  <div class="q-pa-lg">
    <!-- Header with Action Buttons -->
    <div class="q-mb-xl">
      <div class="row items-center q-mb-sm">
        <div class="col-auto">
          <div style="width: 6px; height: 40px; background: #1976d2; border-radius: 3px;" class="q-mr-md"></div>
        </div>
        <div class="col">
          <h1 class="text-h3 text-weight-bold q-ma-none">Academic Calendar</h1>
        </div>
        <div class="col-auto">
          <div class="row q-gutter-sm">
            <q-btn
              color="primary"
              icon="add"
              label="New Academic Year"
              @click="showYearDialog = true"
              unelevated
              size="md"
            />
            <q-btn
              color="secondary"
              icon="import_export"
              label="Import/Export"
              @click="showImportExportDialog = true"
              unelevated
              size="md"
            />
          </div>
        </div>
      </div>
      <p class="text-grey-7 text-weight-medium q-ma-none">Manage academic years, semesters, and daily calendar records with precision.</p>
    </div>

    <!-- New Academic Year Dialog -->
    <q-dialog v-model="showYearDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">Create New Academic Year</div>
        </q-card-section>

        <q-card-section>
          <YearForm @created="showYearDialog = false" />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Import/Export Dialog -->
    <q-dialog v-model="showImportExportDialog" maximized>
      <q-card>
        <q-card-section class="bg-secondary text-white row items-center">
          <div class="col">
            <div class="text-h6">Import/Export Calendar Data</div>
            <div class="text-caption">Upload Excel files to bulk import calendar records or export existing data</div>
          </div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-lg">
          <div class="q-mb-md">
            <q-banner class="bg-blue-1 text-blue-9 rounded-borders">
              <template v-slot:avatar>
                <q-icon name="info" color="blue-9" />
              </template>
              <strong>Semester Column:</strong> Use semester numbers only (1, 2, 3, 4) instead of "Semester 1", "Semester 2", etc.
            </q-banner>
          </div>

          <ExcelManager
            :export-data="exportTemplateData"
            export-file-name="calendar_data.xlsx"
            initial-tab="import"
            @imported-json="handleCalendarImport"
            @exported="handleExported"
          />
        </q-card-section>
      </q-card>
    </q-dialog>

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

              <!-- Active Semester Selector -->
              <div class="row items-center q-gutter-sm q-mt-sm">
                <q-icon name="bookmark" color="primary" size="xs" />
                <span class="text-caption text-weight-bold text-grey-6 uppercase">Active Semester:</span>
                <q-select
                  :model-value="year.semesters.find(s => s.active)?.id"
                  :options="year.semesters.filter(s => s.start_date && s.end_date).map(s => ({ label: s.name, value: s.id, semester: s }))"
                  option-value="value"
                  option-label="label"
                  emit-value
                  map-options
                  dense
                  borderless
                  class="text-weight-bold text-primary"
                  style="min-width: 140px"
                  @update:model-value="(semId) => setActiveSemester(year, semId)"
                />
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
                  @click="openAIDialog(year)"
                  color="secondary"
                  unelevated
                  icon="auto_awesome"
                  label="AI Setup"
                  class="text-weight-bold"
                  size="md"
                />
              </div>
            </div>
          </div>
        </q-card>

        <!-- Semesters Grid -->
        <div class="row q-col-gutter-md q-mb-lg">
          <div v-for="(item, index) in semestersWithGaps(year)" :key="item.id || index" class="col-12 col-md-6 col-xl-3">
            <template v-if="item.isGap">
              <UnassignedDaysCard :gap="item" class="h-full" />
            </template>
            <template v-else>
              <SemesterCard :semester="item" :year-id="year.id" class="h-full" />
            </template>
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

    <!-- AI Generator Dialog -->
    <CalendarAIGeneratorDialog
      v-if="selectedYearForAI"
      v-model="showAIDialog"
      :yearId="selectedYearForAI.id"
      :yearName="selectedYearForAI.name"
      :schoolId="selectedYearForAI.school_id"
      @success="handleAISuccess"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import YearForm from './YearForm.vue';
import SemesterCard from './SemesterCard.vue';
import UnassignedDaysCard from './UnassignedDaysCard.vue';
import MissingDaysList from './MissingDaysList.vue';
import CalendarAIGeneratorDialog from './CalendarAIGeneratorDialog.vue';
import ExcelManager from '@/Components/import_excel_sys/ExcelManager.vue';

const $q = useQuasar();
const page = usePage();

// Show backend flash messages (success / warning) as Quasar notifications
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      $q.notify({ message: flash.success, color: 'positive', icon: 'check_circle', position: 'top', timeout: 5000 });
    }
    if (flash?.warning) {
      $q.notify({ message: flash.warning, color: 'warning', icon: 'warning', position: 'top', timeout: 8000 });
    }
  },
  { immediate: true }
);

const props = defineProps({
  academicYears: Array,
});

// Dialog states
const showYearDialog = ref(false);
const showImportExportDialog = ref(false);
const showAIDialog = ref(false);
const selectedYearForAI = ref(null);

// Export template data
const exportTemplateData = ref([
  {
    date: '2025-09-01',
    semester: 1,
    status: 1,
    status_label: 'Work Day',
    week_number: 1,
    event: '',
    notes: '',
  },
  {
    date: '2025-09-02',
    semester: 1,
    status: 1,
    status_label: 'Work Day',
    week_number: 1,
    event: '',
    notes: '',
  },
  {
    date: '2025-09-03',
    semester: 1,
    status: 0,
    status_label: 'Day Off',
    week_number: 1,
    event: 'Weekend',
    notes: '',
  },
]);

const calculateTotalDays = (year) => {
  return year.semesters.reduce((acc, sem) => acc + sem.calendar_count, 0);
};

// Build the full-year timeline as a flat array of [SemesterCard | UnassignedDaysCard] items
const semestersWithGaps = (year) => {
  const result = [];

  const toDate = (str) => str ? new Date(str.split('T')[0]) : null;
  const toDaysDiff = (a, b) => Math.round((b - a) / 86400000);
  const formatDate = (d) => d.toISOString().split('T')[0];
  const addDays = (d, n) => { const r = new Date(d); r.setDate(r.getDate() + n); return r; };

  if (!year.semesters || year.semesters.length === 0) return [];

  const yearStart = toDate(year.start_date);
  const yearEnd   = toDate(year.end_date);

  if (!yearStart || !yearEnd) return year.semesters;

  // Semesters that have real dates (fully or partially defined)
  const positioned = year.semesters
    .filter(s => s.start_date && s.end_date)
    .sort((a, b) => toDate(a.start_date) - toDate(b.start_date));

  // Semesters with no dates - show at end
  const unscheduled = year.semesters.filter(s => !s.start_date || !s.end_date);

  let cursor = yearStart;

  for (const sem of positioned) {
    const semStart = toDate(sem.start_date);
    const semEnd   = toDate(sem.end_date);

    // Gap BEFORE this semester
    const gapDays = toDaysDiff(cursor, semStart);
    if (gapDays >= 1) {
      result.push({
        isGap: true,
        startDate: formatDate(cursor),
        endDate: formatDate(addDays(semStart, -1)),
        days: gapDays,
      });
    }

    // The semester itself
    result.push(sem);
    cursor = addDays(semEnd, 1);
  }

  // Gap AFTER last positioned semester (or entire year if none)
  const trailingDays = toDaysDiff(cursor, addDays(yearEnd, 1));
  if (trailingDays >= 1) {
    result.push({
      isGap: true,
      startDate: formatDate(cursor),
      endDate: formatDate(yearEnd),
      days: trailingDays,
    });
  }

  // Unscheduled semesters (no dates) appended at end
  result.push(...unscheduled);

  return result;
};

const setActiveSemester = (year, semesterId) => {
  const semester = year.semesters.find(s => s.id === semesterId);
  if (!semester) return;
  router.put(route('admin.academic_calendar.year.set_active_semester', { year: year.id, semester: semesterId }), {}, {
    preserveScroll: true,
  });
};

const toggleYearActive = (year) => {
  router.put(route('admin.academic_calendar.year.toggle', year.id), {}, {
    preserveScroll: true,
  });
};

const showCalendarDialog = ref(false);
const selectedYear = ref(null);

// AI Setup Handlers
const openAIDialog = (year) => {
  selectedYearForAI.value = year;
  showAIDialog.value = true;
};

const handleAISuccess = () => {
  showAIDialog.value = false;
  router.reload({ preserveScroll: true });
};

// Import/Export handlers
const downloadTemplate = async () => {
  try {
    const response = await axios.get(route('admin.academic_calendar.export_template'));
    exportTemplateData.value = response.data;
    $q.notify({
      message: 'Template data loaded. Switch to Export tab to download.',
      color: 'positive',
      position: 'top',
    });
  } catch (error) {
    $q.notify({
      message: 'Failed to load template: ' + error.message,
      color: 'negative',
      position: 'top',
    });
  }
};

const handleCalendarImport = async (data) => {
  try {
    const response = await axios.post(route('admin.academic_calendar.import'), { data });
    
    const results = response.data.results;
    const successCount = results.success?.length || 0;
    const errorCount = results.errors?.length || 0;
    
    if (successCount > 0) {
      $q.notify({
        message: `Successfully imported ${successCount} calendar records.`,
        color: 'positive',
        position: 'top',
      });
    }
    
    if (errorCount > 0) {
      $q.notify({
        message: `${errorCount} errors occurred during import. Check console for details.`,
        color: 'warning',
        position: 'top',
      });
      console.log('Import errors:', results.errors);
    }
    
    // Reload page to show updated data
    router.reload({ preserveScroll: true });
  } catch (error) {
    $q.notify({
      message: 'Import failed: ' + (error.response?.data?.message || error.message),
      color: 'negative',
      position: 'top',
    });
  }
};

const handleExported = (info) => {
  $q.notify({
    message: 'Calendar data exported successfully!',
    color: 'positive',
    position: 'top',
  });
};
</script>

<style scoped>
/* Custom fonts or styles if needed, mostly using Tailwind */
</style>
