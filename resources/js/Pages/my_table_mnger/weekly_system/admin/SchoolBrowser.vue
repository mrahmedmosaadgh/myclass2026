<template>
  <AdminLayout>
    <div class="q-pa-md">
      <!-- Header -->
      <div class="row items-center q-mb-md">
        <div class="col">
          <div class="text-h4 text-weight-bold text-primary">School Browser</div>
          <div class="text-subtitle2 text-grey-7">Browse schools, classrooms, subjects, and teachers</div>
        </div>
      </div>

      <!-- School Selector -->
      <q-card flat bordered class="q-mb-md">
        <q-card-section>
          <div class="row q-col-gutter-md items-center">
            <div class="col-12 col-md-6">
              <q-select
                v-model="selectedSchool"
                :options="schools"
                option-value="id"
                option-label="name"
                label="Select School"
                outlined
                dense
                emit-value
                map-options
                @update:model-value="loadSchoolData"
                :loading="loadingSchools"
              >
                <template v-slot:prepend>
                  <q-icon name="school" color="primary" />
                </template>
              </q-select>
            </div>
            <div class="col-12 col-md-6" v-if="schoolData">
              <div class="text-h6 text-primary">{{ schoolData.school.name }}</div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Loading State -->
      <div v-if="loading" class="row justify-center q-my-xl">
        <q-spinner-dots color="primary" size="50px" />
      </div>

      <!-- Content Tabs -->
      <div v-else-if="schoolData">
        <q-tabs
          v-model="activeTab"
          dense
          class="text-grey-7 q-mb-md"
          active-color="primary"
          indicator-color="primary"
          align="left"
          narrow-indicator
        >
          <q-tab name="overview" icon="dashboard" label="Overview" />
          <q-tab name="classrooms" icon="class" label="Classrooms" />
          <q-tab name="assignments" icon="assignment" label="Assignments" />
        </q-tabs>

        <q-separator class="q-mb-md" />

        <q-tab-panels v-model="activeTab" animated>
          <q-tab-panel name="overview">
            <SchoolOverview :stats="schoolData.stats" :subjects="schoolData.subjects" :teachers="schoolData.teachers" />
          </q-tab-panel>

          <q-tab-panel name="classrooms">
            <ClassroomHierarchy :hierarchy="schoolData.hierarchy" :assignments="schoolData.assignments" />
          </q-tab-panel>

          <q-tab-panel name="assignments">
            <AssignmentsTable :assignments="schoolData.assignments" />
          </q-tab-panel>
        </q-tab-panels>
      </div>

      <!-- Empty State -->
      <q-card v-else flat bordered class="q-pa-xl text-center">
        <q-icon name="school" size="80px" color="grey-5" />
        <div class="text-h6 text-grey-7 q-mt-md">Select a school to view details</div>
        <div class="text-body2 text-grey-6">Choose a school from the dropdown above to browse its structure</div>
      </q-card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SchoolOverview from './components/SchoolOverview.vue';
import ClassroomHierarchy from './components/ClassroomHierarchy.vue';
import AssignmentsTable from './components/AssignmentsTable.vue';

const $q = useQuasar();

// State
const schools = ref([]);
const selectedSchool = ref(null);
const schoolData = ref(null);
const loading = ref(false);
const loadingSchools = ref(false);
const activeTab = ref('overview');

// Load schools list
const loadSchools = async () => {
  loadingSchools.value = true;
  try {
    const response = await axios.get(route('weekly-system.api.school-data'));
    schools.value = response.data.schools;
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load schools',
      caption: error.response?.data?.message || error.message
    });
  } finally {
    loadingSchools.value = false;
  }
};

// Load school data
const loadSchoolData = async () => {
  if (!selectedSchool.value) {
    schoolData.value = null;
    return;
  }

  loading.value = true;
  try {
    const response = await axios.get(route('weekly-system.api.school-data'), {
      params: { school_id: selectedSchool.value }
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

// Initialize
onMounted(() => {
  loadSchools();
});
</script>

<style scoped>
/* Add any custom styles here if needed */
</style>
