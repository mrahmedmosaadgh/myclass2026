<template>
  <q-dialog v-model="showDialog" persistent maximized>
    <q-card style="max-width: 800px; margin: auto;" class="q-pa-lg">
      <q-card-section class="text-center">
        <div class="text-h5 text-primary q-mb-sm">School Configuration</div>
        <div class="text-subtitle1 text-grey">Set up school context for the weekly planning system</div>
      </q-card-section>

      <q-card-section>
        <q-form class="q-gutter-y-md">
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <q-select
                v-model="selectedSchool"
                :options="schoolDataStore.schools"
                option-value="id"
                option-label="name"
                label="Select School *"
                outlined
                dense
                emit-value
                map-options
                @update:model-value="onSchoolChange"
                :loading="schoolDataStore.loading"
                :rules="[val => !!val || 'School selection is required']"
                class="full-width"
              >
                <template v-slot:prepend>
                  <q-icon name="school" color="primary" />
                </template>
              </q-select>
            </div>
          </div>

          <div class="row q-col-gutter-md q-mt-none">
            <div class="col-12 col-md-4">
              <q-select
                v-model="selectedAcademicYear"
                :options="academicYears"
                option-value="id"
                option-label="name"
                label="Academic Year *"
                outlined
                dense
                map-options
                :rules="[val => !!val || 'Academic year is required']"
              >
                <template v-slot:prepend>
                  <q-icon name="calendar_month" color="blue" />
                </template>
              </q-select>
            </div>

            <div class="col-12 col-md-4">
              <q-select
                v-model="selectedSemester"
                :options="semesters"
                option-value="id"
                option-label="name"
                label="Semester *"
                outlined
                dense
                map-options
                :rules="[val => !!val || 'Semester is required']"
              >
                <template v-slot:prepend>
                  <q-icon name="event" color="indigo" />
                </template>
              </q-select>
            </div>
          </div>
        </q-form>
      </q-card-section>

      <q-separator class="q-my-md" />

      <q-card-section class="text-center">
        <div class="text-h6 text-weight-medium text-primary q-mb-md">Current Selection</div>
        <div class="row justify-center q-gutter-md">
          <q-badge 
            v-if="selectedSchool" 
            color="primary" 
            size="lg"
            class="q-px-lg q-py-xs"
            style="height: 40px;"
          >
            <q-icon name="school" size="18px" class="q-mr-xs" />
            <span class="text-weight-medium">{{ getSchoolName(selectedSchool) }}</span>
          </q-badge>
          
          <q-badge 
            v-if="selectedAcademicYear" 
            color="blue-6" 
            size="lg"
            class="q-px-lg q-py-xs"
            style="height: 40px;"
          >
            <q-icon name="calendar_month" size="18px" class="q-mr-xs" />
            <span class="text-weight-medium">{{ selectedAcademicYear.name }}</span>
          </q-badge>
          
          <q-badge 
            v-if="selectedSemester" 
            color="indigo-6" 
            size="lg"
            class="q-px-lg q-py-xs"
            style="height: 40px;"
          >
            <q-icon name="event" size="18px" class="q-mr-xs" />
            <span class="text-weight-medium">{{ selectedSemester.name }}</span>
          </q-badge>
        </div>
      </q-card-section>

      <q-card-actions align="right" class="q-pt-lg">
        <q-btn 
          label="Cancel" 
          color="grey" 
          flat 
          @click="$emit('cancel')" 
          class="q-px-md"
        />
        <q-btn 
          label="Save Settings" 
          icon="save" 
          color="primary" 
          @click="saveSettings" 
          :disable="!isFormValid"
          class="q-px-xl"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useSchoolDataStore } from '@/Stores/schoolData';
import { useQuasar } from 'quasar';
import axios from 'axios';

const $q = useQuasar();
const schoolDataStore = useSchoolDataStore();
const { schoolId, schools, loading } = storeToRefs(schoolDataStore);

// Props
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  schoolId: {
    type: Number,
    required: false,  // Make it not required so it can be null
    default: null
  }
});

// Emits
const emit = defineEmits(['update:modelValue', 'saved', 'cancel']);

// Reactive variables
const showDialog = ref(false);
const selectedSchool = ref(null);
const selectedAcademicYear = ref(null);
const selectedSemester = ref(null);
const academicYears = ref([]);
const semesters = ref([]);

// Watch for changes in modelValue to control dialog visibility
watch(() => props.modelValue, (value) => {
  showDialog.value = value;
  if (value) {
    // Set the initial selected school to the prop value if it exists
    if (props.schoolId) {
      selectedSchool.value = props.schoolId;
    }
    loadOptions();
  }
});

// Watch for changes in showDialog to emit updates
watch(showDialog, (value) => {
  emit('update:modelValue', value);
});

// Watch for changes in selected school to update options
watch(selectedSchool, async (newSchoolId) => {
  if (newSchoolId && showDialog.value) {
    await loadOptions();
  }
});

// Computed property to check if form is valid
const isFormValid = computed(() => {
  return selectedSchool.value && 
         selectedAcademicYear.value && 
         selectedSemester.value;
});

// Helper function to get school name by ID
const getSchoolName = (id) => {
  const school = schools.value.find(s => s.id === id);
  return school ? school.name : 'Unknown School';
};

// Event handler for school selection change
const onSchoolChange = async (schoolId) => {
  if (schoolId) {
    await schoolDataStore.setSchool(schoolId);
  }
};

// Load all options for the selects
const loadOptions = async () => {
  try {
    // Load from store if available
    academicYears.value = schoolDataStore.academicYears;
    semesters.value = schoolDataStore.semesters;

    // If store doesn't have the data, fetch it directly
    if (academicYears.value.length === 0) {
      const academicYearsResponse = await axios.get('/api/academic-years');
      academicYears.value = academicYearsResponse.data.data || academicYearsResponse.data;
    }

    if (semesters.value.length === 0) {
      const semestersResponse = await axios.get('/api/semesters');
      semesters.value = semestersResponse.data.data || semestersResponse.data;
    }

    // Load current school settings if a school is selected
    if (selectedSchool.value) {
      await loadCurrentSettings();
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load options',
      caption: error.response?.data?.message || error.message
    });
  }
};

// Load current school settings to pre-populate the form
const loadCurrentSettings = async () => {
  try {
    // Use store values if available
    if (schoolDataStore.academicYearId) {
      selectedAcademicYear.value = academicYears.value.find(ay => ay.id === schoolDataStore.academicYearId) || null;
    }
    if (schoolDataStore.semesterId) {
      selectedSemester.value = semesters.value.find(s => s.id === schoolDataStore.semesterId) || null;
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load current settings',
      caption: error.response?.data?.message || error.message
    });
  }
};

// Save the selected settings to the school
const saveSettings = async () => {
  if (!isFormValid.value) {
    $q.notify({
      type: 'warning',
      message: 'Please fill in all required fields',
      caption: 'All fields marked with * are required'
    });
    return;
  }

  try {
    await schoolDataStore.updateSchoolSettings({
      academic_year_id: selectedAcademicYear.value?.id || null,
      semester_id: selectedSemester.value?.id || null
    });

    $q.notify({
      type: 'positive',
      message: 'School settings saved successfully'
    });

    emit('saved');
    showDialog.value = false;
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to save settings',
      caption: error.response?.data?.message || error.message
    });
  }
};
</script>