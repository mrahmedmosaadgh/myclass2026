<template>
  <div class="q-mt-lg">
    <q-banner rounded class="bg-amber-1 text-amber-10 border-amber-2 border">
      <template v-slot:avatar>
        <q-icon name="history" color="amber-8" size="md" />
      </template>
      
      <div class="column">
        <span class="text-subtitle1 text-weight-bold">Academic Year Gaps</span>
        <span class="text-caption text-weight-medium text-amber-8 uppercase tracking-wider">Missing Calendar Days Analysis</span>
      </div>

      <template v-slot:action>
        <q-btn 
          flat 
          color="amber-10" 
          label="Check for Gaps" 
          icon="manage_search"
          :loading="loading"
          @click="fetchMissingDays"
          class="text-weight-bold"
        />
      </template>
    </q-banner>

    <div v-if="missingDays.length > 0" class="q-mt-md">
      <q-list bordered separator rounded class="bg-white">
        <q-item v-for="(range, index) in missingDays" :key="index">
          <q-item-section avatar>
            <q-icon name="warning" color="amber-7" size="xs" />
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-mono text-weight-bold text-amber-10">{{ range }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </div>

    <div v-else-if="!loading && initialized" class="q-mt-md">
      <q-banner rounded class="bg-green-1 text-green-10 border-green-2 border">
        <template v-slot:avatar>
          <q-icon name="check_circle" color="green-8" size="sm" />
        </template>
        <span class="text-weight-bold">No gaps detected! Full year is covered by semester calendars.</span>
      </q-banner>
    </div>

    <div v-else-if="!initialized" class="q-mt-sm text-caption text-amber-7 text-italic q-px-md">
      Click "Check for Gaps" to analyze your academic calendar coverage across all semesters.
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  yearId: Number,
});

const missingDays = ref([]);
const loading = ref(false);
const initialized = ref(false);

const fetchMissingDays = async () => {
  loading.value = true;
  try {
    const response = await axios.get(route('admin.academic_calendar.year.missing_days', props.yearId));
    missingDays.value = response.data;
    initialized.value = true;
  } catch (error) {
    console.error('Error fetching missing days:', error);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.bg-amber-1 { background: #fffbeb; }
.text-amber-10 { color: #451a03; }
.border-amber-2 { border: 1px solid #fde68a; }
.bg-green-1 { background: #f0fdf4; }
.text-green-10 { color: #052e16; }
.border-green-2 { border: 1px solid #bbf7d0; }
</style>
