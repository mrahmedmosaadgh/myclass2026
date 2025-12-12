<template>
    <Head title="Import Courses" />
    <AppLayout title="Import Courses">
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">Import Course Management Data</div>
                            <div class="text-subtitle2">Upload Excel file to import courses, levels, sections, and lessons</div>
                        </div>
                        <div class="q-gutter-sm">
                            <q-btn 
                                flat 
                                color="grey-7" 
                                icon="arrow_back" 
                                label="Back to Courses" 
                                @click="$inertia.visit(route('course-management.courses.index'))"
                            />
                            <q-btn 
                                color="primary" 
                                icon="download" 
                                label="Download Template" 
                                @click="downloadTemplate"
                            />
                        </div>
                    </div>
                </q-card-section>

                <q-separator />

                <!-- Instructions -->
                <q-card-section>
                    <div class="text-h6 q-mb-md">Import Instructions</div>
                    
                    <q-stepper
                        v-model="step"
                        color="primary"
                        animated
                        flat
                        bordered
                        header-nav
                    >
                        <q-step
                            :name="1"
                            title="Download Template"
                            icon="download"
                            :done="step > 1"
                        >
                            <div class="text-body1">
                                Download the Excel template to see the required format and sample data.
                            </div>
                            <div class="q-mt-md">
                                <q-btn 
                                    color="primary" 
                                    icon="download" 
                                    label="Download Template" 
                                    @click="downloadTemplate"
                                />
                            </div>
                        </q-step>

                        <q-step
                            :name="2"
                            title="Prepare Your Data"
                            icon="edit"
                            :done="step > 2"
                        >
                            <div class="text-body1 q-mb-md">
                                Fill in your course data using this exact column structure:
                            </div>
                            
                            <q-markup-table flat bordered>
                                <thead>
                                    <tr>
                                        <th class="text-left">Column A</th>
                                        <th class="text-left">Column B</th>
                                        <th class="text-left">Column C</th>
                                        <th class="text-left">Column D</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Course</strong></td>
                                        <td><strong>Level</strong></td>
                                        <td><strong>Section</strong> (Optional)</td>
                                        <td><strong>Lesson</strong></td>
                                    </tr>
                                    <tr class="text-grey-7">
                                        <td>Grade 5 Math</td>
                                        <td>Fractions</td>
                                        <td>Add unlike fractions</td>
                                        <td>Lesson A</td>
                                    </tr>
                                    <tr class="text-grey-7">
                                        <td>Grade 5 Math</td>
                                        <td>Fractions</td>
                                        <td>Add unlike fractions</td>
                                        <td>Lesson B</td>
                                    </tr>
                                    <tr class="text-grey-7">
                                        <td>English Literature</td>
                                        <td>Poetry</td>
                                        <td></td>
                                        <td>Introduction to Poetry</td>
                                    </tr>
                                </tbody>
                            </q-markup-table>

                            <q-banner class="bg-blue-1 text-blue-8 q-mt-md" rounded>
                                <template v-slot:avatar>
                                    <q-icon name="info" />
                                </template>
                                <div class="text-subtitle2">Important Notes:</div>
                                <ul class="q-ma-none q-pl-md">
                                    <li>Course, Level, and Lesson columns are required</li>
                                    <li>Section column is optional (will use "General" if empty)</li>
                                    <li>Duplicate lessons will be skipped</li>
                                    <li>Courses, levels, and sections will be created if they don't exist</li>
                                    <li>Order will be assigned automatically</li>
                                </ul>
                            </q-banner>

                            <div class="q-mt-md">
                                <q-btn 
                                    color="primary" 
                                    icon="upload" 
                                    label="Ready to Upload" 
                                    @click="step = 3"
                                />
                            </div>
                        </q-step>

                        <q-step
                            :name="3"
                            title="Upload & Import"
                            icon="upload"
                            :done="importCompleted"
                        >
                            <div class="text-body1 q-mb-md">
                                Upload your Excel file to import the course data.
                            </div>

                            <!-- Import Component -->
                            <ImportExcel
                                :validate-url="validateUrl"
                                :import-url="importUrl"
                                :columns="importColumns"
                                button-text="Choose Excel File"
                                preview-title="Course Import Preview"
                                confirm-button-text="Confirm & Start Import"
                                @validation-success="onValidationSuccess"
                                @validation-error="onValidationError"
                                @import-success="onImportSuccess"
                                @import-error="onImportError"
                            />
                        </q-step>
                    </q-stepper>
                </q-card-section>

                <!-- File Preview & Confirmation -->
                <q-card-section v-if="validationResults && !importStarted">
                    <div class="text-h6 q-mb-md">File Preview & Confirmation</div>
                    
                    <!-- Validation Stats -->
                    <div class="row q-col-gutter-md q-mb-md">
                        <div class="col-12 col-sm-6 col-md-3">
                            <q-card class="bg-blue-1 text-center">
                                <q-card-section>
                                    <div class="text-h5 text-bold text-blue-8">{{ validationResults.stats?.total_rows || 0 }}</div>
                                    <div class="text-subtitle2 text-grey-8">Total Rows</div>
                                </q-card-section>
                            </q-card>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <q-card class="bg-green-1 text-center">
                                <q-card-section>
                                    <div class="text-h5 text-bold text-green-8">{{ validationResults.stats?.valid_rows || 0 }}</div>
                                    <div class="text-subtitle2 text-grey-8">Valid Rows</div>
                                </q-card-section>
                            </q-card>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <q-card class="bg-red-1 text-center">
                                <q-card-section>
                                    <div class="text-h5 text-bold text-red-8">{{ validationResults.stats?.invalid_rows || 0 }}</div>
                                    <div class="text-subtitle2 text-grey-8">Invalid Rows</div>
                                </q-card-section>
                            </q-card>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <q-card class="bg-orange-1 text-center">
                                <q-card-section>
                                    <div class="text-h5 text-bold text-orange-8">{{ validationResults.errors?.length || 0 }}</div>
                                    <div class="text-subtitle2 text-grey-8">Errors</div>
                                </q-card-section>
                            </q-card>
                        </div>
                    </div>

                    <!-- Full Data Preview -->
                    <div v-if="fileData && fileData.length > 0">
                        <div class="text-subtitle1 q-mb-md">Complete Data Preview</div>
                        <q-table
                            :rows="fileData"
                            :columns="fullPreviewColumns"
                            row-key="index"
                            flat
                            bordered
                            dense
                            :pagination="{ rowsPerPage: 20 }"
                            class="q-mb-md"
                        />
                    </div>

                    <!-- Validation Errors -->
                    <div v-if="validationResults.errors && validationResults.errors.length > 0" class="q-mt-md">
                        <div class="text-subtitle1 q-mb-md text-negative">Validation Errors</div>
                        <q-list bordered class="q-mb-md">
                            <q-item v-for="(error, index) in validationResults.errors" :key="index">
                                <q-item-section avatar>
                                    <q-icon name="error" color="negative" />
                                </q-item-section>
                                <q-item-section>
                                    <q-item-label>{{ error }}</q-item-label>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </div>

                    <!-- Confirmation Actions -->
                    <div class="row justify-end q-gutter-sm">
                        <q-btn 
                            flat 
                            label="Cancel" 
                            color="grey-7" 
                            @click="cancelImport"
                        />
                        <q-btn 
                            color="primary" 
                            icon="upload" 
                            label="Confirm & Start Import" 
                            :disable="validationResults.stats?.valid_rows === 0"
                            @click="startImport"
                        />
                    </div>
                </q-card-section>

                <!-- Import Progress -->
                <q-card-section v-if="importStarted && !importCompleted">
                    <div class="text-h6 q-mb-md">Import Progress</div>
                    
                    <!-- Overall Progress -->
                    <div class="q-mb-md">
                        <div class="text-subtitle2 q-mb-sm">
                            Overall Progress: {{ processedRows }} / {{ totalRows }} rows
                        </div>
                        <q-linear-progress 
                            :value="overallProgress" 
                            size="20px" 
                            color="primary"
                            class="q-mb-md"
                        >
                            <div class="absolute-full flex flex-center">
                                <q-badge color="white" text-color="primary" :label="`${Math.round(overallProgress * 100)}%`" />
                            </div>
                        </q-linear-progress>
                    </div>

                    <!-- Row-by-row Status -->
                    <div class="text-subtitle1 q-mb-md">Row Status</div>
                    <q-list bordered>
                        <q-item 
                            v-for="(row, index) in rowStatuses" 
                            :key="index"
                            :class="getRowStatusClass(row.status)"
                        >
                            <q-item-section avatar>
                                <q-icon 
                                    :name="getRowStatusIcon(row.status)" 
                                    :color="getRowStatusColor(row.status)"
                                />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label>Row {{ index + 2 }}: {{ row.course }} → {{ row.level }} → {{ row.section }} → {{ row.lesson }}</q-item-label>
                                <q-item-label caption>{{ getRowStatusText(row.status) }}</q-item-label>
                            </q-item-section>
                            <q-item-section side v-if="row.status === 'processing'">
                                <q-spinner color="primary" size="20px" />
                            </q-item-section>
                        </q-item>
                    </q-list>
                </q-card-section>

                <!-- Import Results -->
                <q-card-section v-if="importResults">
                    <div class="text-h6 q-mb-md">Import Results</div>
                    
                    <div class="row q-col-gutter-md">
                        <div class="col-12 col-sm-6 col-md-3">
                            <q-card class="bg-blue-1 text-center">
                                <q-card-section>
                                    <div class="text-h5 text-bold text-blue-8">{{ importResults.courses_created || 0 }}</div>
                                    <div class="text-subtitle2 text-grey-8">Courses Created</div>
                                </q-card-section>
                            </q-card>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <q-card class="bg-green-1 text-center">
                                <q-card-section>
                                    <div class="text-h5 text-bold text-green-8">{{ importResults.levels_created || 0 }}</div>
                                    <div class="text-subtitle2 text-grey-8">Levels Created</div>
                                </q-card-section>
                            </q-card>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <q-card class="bg-orange-1 text-center">
                                <q-card-section>
                                    <div class="text-h5 text-bold text-orange-8">{{ importResults.sections_created || 0 }}</div>
                                    <div class="text-subtitle2 text-grey-8">Sections Created</div>
                                </q-card-section>
                            </q-card>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <q-card class="bg-purple-1 text-center">
                                <q-card-section>
                                    <div class="text-h5 text-bold text-purple-8">{{ importResults.lessons_created || 0 }}</div>
                                    <div class="text-subtitle2 text-grey-8">Lessons Created</div>
                                </q-card-section>
                            </q-card>
                        </div>
                    </div>

                    <q-banner class="bg-green-1 text-green-8 q-mt-md" rounded>
                        <template v-slot:avatar>
                            <q-icon name="check_circle" />
                        </template>
                        <div class="text-subtitle1">Import Completed Successfully!</div>
                        <div>{{ importResults.total_processed || 0 }} rows processed. {{ importResults.lessons_skipped || 0 }} duplicate lessons were skipped.</div>
                    </q-banner>

                    <div class="q-mt-md">
                        <q-btn 
                            color="primary" 
                            icon="visibility" 
                            label="View Courses" 
                            @click="$inertia.visit(route('course-management.courses.index'))"
                        />
                    </div>
                </q-card-section>
            </q-card>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ImportExcel from './ImportExcel.vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

// State
const step = ref(1);
const importCompleted = ref(false);
const importStarted = ref(false);
const validationResults = ref(null);
const importResults = ref(null);
const fileData = ref([]);
const rowStatuses = ref([]);
const processedRows = ref(0);
const totalRows = ref(0);

// URLs
const validateUrl = route('course-management.import.validate');
const importUrl = route('course-management.import.process');

// Required headers for validation
const requiredHeaders = ['Course', 'Level', 'Section', 'Lesson'];

// Import columns configuration for ImportExcel component
const importColumns = [
    { key: 'Course', label: 'Course', required: true },
    { key: 'Level', label: 'Level', required: true },
    { key: 'Section', label: 'Section (Optional)', required: false },
    { key: 'Lesson', label: 'Lesson', required: true },
];

// Table columns for preview
const previewColumns = [
    { name: 'row', label: 'Row', field: 'row', align: 'center' },
    { name: 'course', label: 'Course', field: 'course', align: 'left' },
    { name: 'level', label: 'Level', field: 'level', align: 'left' },
    { name: 'section', label: 'Section', field: 'section', align: 'left' },
    { name: 'lesson', label: 'Lesson', field: 'lesson', align: 'left' },
];

// Full preview columns
const fullPreviewColumns = [
    { name: 'index', label: 'Row', field: 'index', align: 'center' },
    { name: 'Course', label: 'Course', field: 'Course', align: 'left' },
    { name: 'Level', label: 'Level', field: 'Level', align: 'left' },
    { name: 'Section', label: 'Section', field: 'Section', align: 'left' },
    { name: 'Lesson', label: 'Lesson', field: 'Lesson', align: 'left' },
];

// Computed properties
const overallProgress = computed(() => {
    if (totalRows.value === 0) return 0;
    return processedRows.value / totalRows.value;
});

// Methods
const downloadTemplate = () => {
    window.open(route('course-management.import.template'), '_blank');
    step.value = 2;
};

const onValidationSuccess = (response) => {
    validationResults.value = response;
    
    // Store the file data for preview
    if (response.fileData) {
        fileData.value = response.fileData.map((row, index) => ({
            index: index + 2, // +2 for header row and 1-based indexing
            ...row
        }));
    }
    
    step.value = 3;
    
    $q.notify({
        type: 'positive',
        message: 'File validation successful!',
        caption: `${response.stats?.valid_rows || 0} valid rows found`,
        icon: 'check_circle'
    });
};

const onValidationError = (error) => {
    validationResults.value = null;
    
    $q.notify({
        type: 'negative',
        message: 'File validation failed',
        caption: error.message || 'Please check your file format',
        icon: 'error'
    });
};

const onImportSuccess = (response) => {
    importResults.value = response.stats;
    importCompleted.value = true;
    
    $q.notify({
        type: 'positive',
        message: 'Import completed successfully!',
        caption: `${response.stats?.lessons_created || 0} lessons imported`,
        icon: 'check_circle'
    });
};

const onImportError = (error) => {
    $q.notify({
        type: 'negative',
        message: 'Import failed',
        caption: error.message || 'Please try again',
        icon: 'error'
    });
};

// Additional methods for import process
const cancelImport = () => {
    validationResults.value = null;
    fileData.value = [];
    step.value = 3;
    
    $q.notify({
        type: 'info',
        message: 'Import cancelled',
        icon: 'info'
    });
};

const startImport = () => {
    importStarted.value = true;
    totalRows.value = fileData.value.length;
    processedRows.value = 0;
    
    // Initialize row statuses
    rowStatuses.value = fileData.value.map(row => ({
        course: row.Course,
        level: row.Level,
        section: row.Section,
        lesson: row.Lesson,
        status: 'pending' // pending, processing, success, error
    }));
    
    // Start processing rows one by one
    processRowsSequentially();
};

const processRowsSequentially = async () => {
    for (let i = 0; i < fileData.value.length; i++) {
        // Update status to processing
        rowStatuses.value[i].status = 'processing';
        
        try {
            // Process single row
            const response = await fetch(importUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    data: [fileData.value[i]]
                })
            });
            
            if (response.ok) {
                rowStatuses.value[i].status = 'success';
            } else {
                rowStatuses.value[i].status = 'error';
            }
        } catch (error) {
            rowStatuses.value[i].status = 'error';
        }
        
        processedRows.value = i + 1;
        
        // Small delay to show progress
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    
    // Process all data at once for final import
    try {
        const response = await fetch(importUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                data: fileData.value
            })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            onImportSuccess(result);
        } else {
            onImportError(result);
        }
    } catch (error) {
        onImportError({ message: error.message });
    }
};

// Status helper methods
const getRowStatusIcon = (status) => {
    switch (status) {
        case 'pending': return 'schedule';
        case 'processing': return 'sync';
        case 'success': return 'check_circle';
        case 'error': return 'error';
        default: return 'help';
    }
};

const getRowStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'grey';
        case 'processing': return 'primary';
        case 'success': return 'positive';
        case 'error': return 'negative';
        default: return 'grey';
    }
};

const getRowStatusClass = (status) => {
    switch (status) {
        case 'success': return 'bg-green-1';
        case 'error': return 'bg-red-1';
        case 'processing': return 'bg-blue-1';
        default: return '';
    }
};

const getRowStatusText = (status) => {
    switch (status) {
        case 'pending': return 'Waiting to process';
        case 'processing': return 'Processing...';
        case 'success': return 'Successfully imported';
        case 'error': return 'Failed to import';
        default: return 'Unknown status';
    }
};
</script>

<style scoped>
.q-stepper {
    box-shadow: none;
}
</style>