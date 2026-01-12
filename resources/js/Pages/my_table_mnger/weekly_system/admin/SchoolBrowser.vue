<template>
 
    <div class="q-pa-md">
      <WeeklyPlanMenu />
      <!-- Header -->
      <div class="row items-center q-mb-md">
        <div class="col">
          <div class="text-h4 text-weight-bold text-primary">{{ t('weeklySystem.schoolBrowser.title') }}</div>
          <div class="text-subtitle2 text-grey-7">{{ t('weeklySystem.schoolBrowser.subtitle') }}</div>
        </div>
      </div>


      <!-- Loading State -->
      <div v-if="loading" class="row justify-center q-my-xl">
        <q-spinner-dots color="primary" size="50px" />
      </div>

      <!-- Content Tabs -->
      <div v-else-if="schoolDataStore.hasAllContext && schoolData">
        <q-tabs
          v-model="activeTab"
          dense
          class="text-grey-7 q-mb-md"
          active-color="primary"
          indicator-color="primary"
          align="left"
          narrow-indicator
        >
          <q-tab name="overview" icon="dashboard" :label="t('weeklySystem.schoolBrowser.overviewTab')" />
          <q-tab name="classrooms" icon="class" :label="t('weeklySystem.schoolBrowser.classroomsTab')" />
          <q-tab name="assignments" icon="assignment" :label="t('weeklySystem.schoolBrowser.assignmentsTab')" />
        </q-tabs>

        <q-separator class="q-mb-md" />

        <q-tab-panels v-model="activeTab" animated>
          <q-tab-panel name="overview">
            <SchoolOverview 
              :stats="schoolData.stats || {}" 
              :subjects="schoolData.subjects || []" 
              :teachers="schoolData.teachers || []" 
            />
          </q-tab-panel>

          <q-tab-panel name="classrooms">
            <ClassroomHierarchy 
              :hierarchy="schoolData.hierarchy || []" 
              :assignments="schoolData.assignments || []" 
            />
          </q-tab-panel>

          <q-tab-panel name="assignments">
            <AssignmentsTable :assignments="schoolData.assignments || []" />
          </q-tab-panel>
        </q-tab-panels>
      </div>

      <!-- Empty State -->
      <q-card v-else flat bordered class="q-pa-xl text-center">
        <q-icon name="school" size="80px" color="grey-5" />
        <div class="text-h6 text-grey-7 q-mt-md">{{ t('weeklySystem.schoolBrowser.selectSchool') }}</div>
        <div class="text-body2 text-grey-6">{{ t('weeklySystem.schoolBrowser.selectSchoolHint') }}</div>
      </q-card>
    </div>
 
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useSchoolDataStore } from '@/Stores/schoolData';
import { useQuasar } from 'quasar';
import axios from 'axios';
import { useI18n } from 'vue-i18n';
 
import WeeklyPlanMenu from '@/Pages/my_table_mnger/weekly_system/WeeklyPlanMenu.vue';
import SchoolOverview from './components/SchoolOverview.vue';
import ClassroomHierarchy from './components/ClassroomHierarchy.vue';
import AssignmentsTable from './components/AssignmentsTable.vue';

const { t } = useI18n();
const $q = useQuasar();
const schoolDataStore = useSchoolDataStore(); // Use the store

// State
const schoolData = ref(null);
const loading = ref(false);
const activeTab = ref('overview');

// Load school data
const loadSchoolData = async () => {
  if (!schoolDataStore.schoolId) {
    schoolData.value = null;
    return;
  }

  loading.value = true;
  try {
    const response = await axios.get(route('weekly-system.api.school-data'), {
      params: { school_id: schoolDataStore.schoolId }
    });
    schoolData.value = response.data;
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load school data',
      caption: error.response?.data?.message || error.message
    });
  } finally {
    loading.value = false;
  }
};

// Watch for changes in school context to reload data
watch(
  () => schoolDataStore.hasAllContext, 
  async (hasContext) => {
    if (hasContext) {
      await loadSchoolData();
    } else {
      schoolData.value = null;
    }
  },
  { immediate: true }
);

// Initialize
onMounted(() => {
  // The MainSchoolData component handles initialization
});
</script>

<style scoped>
/* Add any custom styles here if needed */
</style>