<template>
    <AppLayout :title="pageTitle">
        <template #header>
            <div class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-3xl font-bold text-gray-900">{{ pageTitle }}</h1>
                </div>
            </div>
        </template>

        <div class="q-pa-md">
            <div class="row justify-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <q-card>
                        <q-card-section>
                            <div class="text-h6">School and Academic Year Selection</div>
                        </q-card-section>

                        <q-card-section>
                            <div class="row q-col-gutter-md">
                                <div class="col-12 col-md-6">
                                    <q-select
                                        v-model="selectedSchoolId"
                                        :options="schools"
                                        option-value="id"
                                        option-label="name"
                                        label="Select School *"
                                        outlined
                                        emit-value
                                        map-options
                                        @update:model-value="onSchoolChange"
                                        :disable="loadingAcademicYear"
                                    />
                                </div>

                                <div class="col-12 col-md-6">
                                    <q-input
                                        :model-value="academicYearDisplay"
                                        label="Active Academic Year"
                                        outlined
                                        readonly
                                        :loading="loadingAcademicYear"
                                    >
                                        <template v-slot:append>
                                            <q-icon v-if="academicYear" name="check_circle" color="positive" />
                                            <q-icon v-else-if="selectedSchoolId && !loadingAcademicYear" name="warning" color="negative" />
                                        </template>
                                    </q-input>
                                </div>
                            </div>

                            <q-banner v-if="academicYearError" class="bg-red-1 text-negative q-mt-md rounded-borders">
                                <template v-slot:avatar>
                                    <q-icon name="error" color="negative" />
                                </template>
                                <div class="text-weight-bold">Academic Year Required</div>
                                {{ academicYearError }}
                            </q-banner>
                        </q-card-section>

                        <q-separator v-if="canShowImportSection" />

                        <q-card-section v-if="canShowImportSection">
                            <div class="text-h6 q-mb-md">Import Configuration</div>
                            
                            <div class="q-gutter-sm">
                                <q-radio v-model="syncMode" val="update_existing" label="Update Existing" color="primary">
                                    <q-tooltip>Update existing assignments and add new ones without removing others</q-tooltip>
                                </q-radio>
                                <q-radio v-model="syncMode" val="full_sync" label="Full Sync" color="primary">
                                    <q-tooltip>Replace all assignments for this school and academic year</q-tooltip>
                                </q-radio>
                            </div>
                            <div class="text-caption text-grey q-mt-xs q-ml-sm">
                                {{ syncMode === 'update_existing' 
                                    ? 'Update existing assignments and add new ones without removing other assignments' 
                                    : 'Replace all assignments for this school and academic year with the imported data' 
                                }}
                            </div>
                        </q-card-section>

                        <q-separator v-if="canShowImportSection" />

                        <q-card-section v-if="canShowImportSection">
                            <div class="text-h6 q-mb-md">Excel File Import</div>

                            <q-banner class="bg-blue-1 text-primary q-mb-md rounded-borders">
                                <template v-slot:avatar>
                                    <q-icon name="info" color="primary" />
                                </template>
                                <div class="text-weight-medium">Required Excel Columns</div>
                                <div class="row q-gutter-x-md text-caption">
                                    <div>• Classroom</div>
                                    <div>• Subject</div>
                                    <div>• Teacher Name</div>
                                    <div>• Periods_per_Week</div>
                                </div>
                                <div class="text-weight-medium q-mt-sm">Optional Columns</div>
                                <div class="row q-gutter-x-md text-caption">
                                    <div>• Teacher Email</div>
                                    <div>• Phone</div>
                                    <div>• National ID</div>
                                    <div>• Gender</div>
                                    <div>• Date of Birth</div>
                                </div>
                            </q-banner>

                            <ImportExcelQuasar
                                :validate-url="validateUrl"
                                :import-url="importUrl"
                                :columns="teacherColumns"
                                button-text="Choose Teacher Excel File"
                                preview-title="Preview Teacher Import Data"
                                confirm-button-text="Import Teachers"
                                :additional-payload="{
                                    school_id: selectedSchoolId,
                                    academic_year_id: academicYear?.id,
                                    sync_mode: syncMode
                                }"
                                @validation-success="handleValidationSuccess"
                                @imported="handleImportComplete"
                            />
                        </q-card-section>

                        <q-card-section v-if="importInProgress">
                            <div class="row items-center q-gutter-md">
                                <q-spinner color="primary" size="2em" />
                                <div>
                                    <div class="text-weight-bold text-primary">Import in Progress</div>
                                    <div class="text-caption">Processing your teacher data...</div>
                                </div>
                            </div>
                        </q-card-section>

                        <q-card-section v-if="importResults">
                            <div class="text-h6 q-mb-md">Import Results</div>
                            
                            <q-banner 
                                :class="importResults.success ? 'bg-green-1 text-positive' : 'bg-red-1 text-negative'"
                                class="rounded-borders q-mb-md"
                            >
                                <template v-slot:avatar>
                                    <q-icon :name="importResults.success ? 'check_circle' : 'error'" :color="importResults.success ? 'positive' : 'negative'" />
                                </template>
                                <div class="text-weight-bold">
                                    {{ importResults.success ? 'Import Completed Successfully' : 'Import Failed' }}
                                </div>
                                {{ importResults.message }}
                            </q-banner>

                            <q-card v-if="importResults.report" bordered flat class="q-mb-md">
                                <q-card-section class="bg-grey-1 q-py-sm">
                                    <div class="text-subtitle2">Import Summary</div>
                                </q-card-section>
                                <q-card-section>
                                    <div class="row q-col-gutter-md">
                                        <div class="col-6 col-sm-3">
                                            <div class="text-caption text-grey">Total Rows</div>
                                            <div class="text-body1">{{ importResults.report.summary?.total_rows || 0 }}</div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="text-caption text-grey">Processed</div>
                                            <div class="text-body1">{{ importResults.report.summary?.processed_rows || 0 }}</div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="text-caption text-grey">Teachers Created</div>
                                            <div class="text-body1 text-positive">{{ importResults.report.summary?.teachers_created || 0 }}</div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="text-caption text-grey">Assignments</div>
                                            <div class="text-body1 text-info">{{ (importResults.report.summary?.assignments_created || 0) + (importResults.report.summary?.assignments_updated || 0) }}</div>
                                        </div>
                                    </div>

                                    <div v-if="importResults.report.errors && importResults.report.errors.length > 0" class="q-mt-md">
                                        <div class="text-subtitle2 text-negative q-mb-sm">Errors ({{ importResults.report.errors.length }})</div>
                                        <q-scroll-area style="height: 150px; border: 1px solid #ddd; border-radius: 4px;">
                                            <q-list dense>
                                                <q-item v-for="(error, index) in importResults.report.errors" :key="index">
                                                    <q-item-section>
                                                        <q-item-label class="text-negative text-caption">
                                                            Row {{ error.row }}: {{ error.message }}
                                                        </q-item-label>
                                                    </q-item-section>
                                                </q-item>
                                            </q-list>
                                        </q-scroll-area>
                                    </div>
                                </q-card-section>
                            </q-card>

                            <div class="row justify-end q-gutter-sm">
                                <q-btn outline color="primary" label="Import Another File" @click="resetImport" />
                                <q-btn v-if="importResults.report" color="primary" label="Download Report" @click="downloadReport" icon="download" />
                            </div>
                        </q-card-section>
                    </q-card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ImportExcelQuasar from '@/Components/Common/ImportExcelQuasar.vue';
import axios from 'axios';

// Props
const props = defineProps({
    schools: {
        type: Array,
        required: true
    }
});

// Page data
const pageTitle = 'Teacher Excel Import';

// Reactive state
const selectedSchoolId = ref('');
const academicYear = ref(null);
const loadingAcademicYear = ref(false);
const academicYearError = ref('');
const syncMode = ref('update_existing');
const importInProgress = ref(false);
const importResults = ref(null);
const validationData = ref(null);

// Computed properties
const canShowImportSection = computed(() => {
    return selectedSchoolId.value && academicYear.value && !academicYearError.value;
});

const academicYearDisplay = computed(() => {
    if (loadingAcademicYear.value) return 'Loading...';
    if (academicYear.value) {
        return `${academicYear.value.name} (${academicYear.value.start_date} - ${academicYear.value.end_date})`;
    }
    if (selectedSchoolId.value && !academicYear.value) {
        return 'No active academic year found';
    }
    return 'Select a school first';
});

const validateUrl = computed(() => {
    if (!canShowImportSection.value) return '';
    return `/admin/teachers/import/validate`;
});

const importUrl = computed(() => {
    if (!canShowImportSection.value) return '';
    return `/admin/teachers/import/process`;
});

// Teacher columns configuration for ImportExcel component
const teacherColumns = [
    { key: 'classroom', label: 'Classroom', required: true },
    { key: 'subject', label: 'Subject', required: true },
    { key: 'teacher_name', label: 'Teacher', required: true },
    { key: 'periods_per_week', label: 'Periods_per_Week', required: true },
    { key: 'teacher_email', label: 'Teacher Email', required: false },
    { key: 'phone', label: 'Phone', required: false },
    { key: 'national_id', label: 'National ID', required: false },
    { key: 'gender', label: 'Gender', required: false },
    { key: 'date_of_birth', label: 'Date of Birth', required: false }
];

// Methods
const onSchoolChange = async () => {
    if (!selectedSchoolId.value) {
        academicYear.value = null;
        academicYearError.value = '';
        return;
    }

    loadingAcademicYear.value = true;
    academicYearError.value = '';
    
    try {
        const response = await axios.get(`/admin/teachers/import/academic-year/${selectedSchoolId.value}`);
        
        if (response.data.success) {
            academicYear.value = response.data.academic_year;
        } else {
            academicYear.value = null;
            academicYearError.value = response.data.message;
        }
    } catch (error) {
        academicYear.value = null;
        academicYearError.value = error.response?.data?.message || 'Failed to load academic year';
    } finally {
        loadingAcademicYear.value = false;
    }
};

const handleValidationSuccess = (data) => {
    validationData.value = data;
};

const handleImportComplete = (results) => {
    importResults.value = results;
};

const resetImport = () => {
    importResults.value = null;
    validationData.value = null;
};

const downloadReport = () => {
    if (!importResults.value?.report) return;
    
    const reportData = JSON.stringify(importResults.value.report, null, 2);
    const blob = new Blob([reportData], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `teacher-import-report-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};

// Watch for changes in school selection to reset import state
watch(selectedSchoolId, () => {
    resetImport();
});

// Watch for changes in academic year to reset import state
watch(academicYear, () => {
    resetImport();
});

onMounted(() => {
    if (props.schools && props.schools.length > 0) {
        selectedSchoolId.value = props.schools[0].id;
        onSchoolChange();
    }
});
</script>