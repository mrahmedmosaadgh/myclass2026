<template>schoolDataStore.schoolId:{{ schoolDataStore.schoolId }}
  <q-card flat bordered class="q-mb-md bg-grey-1">
    <q-card-section class="q-pa-md">
      <div class="row items-center no-wrap">
        <div class="col-12 col-md-8">
          <div class="row items-center q-mb-sm">
            <q-icon name="school" color="primary" size="1.5em" class="q-mr-md" />
            <div class="text-h6 text-primary q-mr-md">Current School:</div>
            <div 
              v-if="schoolDataStore.schoolName" 
              class="text-h6 text-weight-bold ellipsis"
              style="max-width: 300px;"
            >
              {{ schoolDataStore.schoolName }}
            </div>
            <div v-else class="text-subtitle1 text-grey">
              No school selected
            </div>
          </div>
          
          <div class="q-gutter-sm">
            <q-badge 
              v-if="schoolDataStore.academicYearId" 
              color="blue-6" 
              class="q-px-md q-py-xs"
              style="height: 32px;"
            >
              <q-icon name="calendar_month" size="16px" class="q-mr-xs" />
              <span class="text-weight-medium">{{ schoolDataStore.academicYearName || 'N/A' }}</span>
            </q-badge>
            
            <q-badge 
              v-if="schoolDataStore.semesterId" 
              color="indigo-6" 
              class="q-px-md q-py-xs"
              style="height: 32px;"
            >
              <q-icon name="event" size="16px" class="q-mr-xs" />
              <span class="text-weight-medium">{{ schoolDataStore.semesterName || 'N/A' }}</span>
            </q-badge>
            
            <q-badge 
              v-if="schoolDataStore.scheduleCopyId" 
              color="teal-6" 
              class="q-px-md q-py-xs"
              style="height: 32px;"
            >
              <q-icon name="schedule" size="16px" class="q-mr-xs" />
              <span class="text-weight-medium">{{ schoolDataStore.scheduleCopyName || 'N/A' }}</span>
            </q-badge>
            
            <q-badge 
              v-if="!schoolDataStore.hasAllContext && schoolDataStore.schoolId"
              color="orange"
              class="q-px-md q-py-xs"
              style="height: 32px;"
            >
              <q-icon name="warning" size="16px" class="q-mr-xs" />
              <span class="text-weight-medium">Incomplete settings</span>
            </q-badge>
          </div>
        </div>
         
        <div class="absolute-top-right">
          <div class="row justify-end">
            <q-btn 
              v-if="schoolDataStore.schoolId" 
              icon="settings"
              flat=""
              round
              @click="openSettingsDialog"
            >
              <q-tooltip>Configure Settings</q-tooltip>
            </q-btn>
            <q-btn 
              v-else
              icon="school"
              color="primary" 
              round
              @click="openSettingsDialog"
            >
              <q-tooltip>Select School</q-tooltip>
            </q-btn>
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
  
  <!-- School Settings Dialog -->
  <SchoolSettingsDialog
    v-model="showSettingsDialog"
    :school-id="schoolDataStore.schoolId"
    @saved="onSettingsSaved"
    @cancel="onSettingsCancel"
  />
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useSchoolDataStore } from '@/Stores/schoolData';
import SchoolSettingsDialog from './SchoolSettingsDialog.vue';

const schoolDataStore = useSchoolDataStore();
const { schoolId, schools, loading, academicYearName, semesterName, scheduleCopyName } = storeToRefs(schoolDataStore);

// Reactive variables
const showSettingsDialog = ref(false);

// Watch for changes in store values to update local state
watch(() => schoolDataStore.schoolId, (newId) => {
  // Update as needed
});

// Initialize data when component mounts
onMounted(async () => {
  if (schoolDataStore.schools.length === 0) {
    await schoolDataStore.fetchSchools();
  } else if (!schoolDataStore.schoolId && schoolDataStore.schools.length > 0) {
    // If no school is selected but schools exist, select the first one
    schoolDataStore.setSchool(schoolDataStore.schools[0].id);
  }
});

// Event handlers
const openSettingsDialog = () => {
  showSettingsDialog.value = true;
};

const onSettingsSaved = () => {
  showSettingsDialog.value = false;
  // The store has already been updated, so no additional action needed
};

const onSettingsCancel = () => {
  showSettingsDialog.value = false;
};
</script>

<style scoped>
/* Add any custom styles here if needed */
</style>