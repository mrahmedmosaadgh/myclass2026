<template>
    <Head title="Import Courses" />
    <AppLayout title="Import Courses">
    <import-reusable
            :validate-url="validateUrl"
            :import-url="importUrl"
            :undo-url="''"
            :import-columns="importColumns"
            :full-preview-columns="fullPreviewColumns"
            :button-text="'Choose Excel File'"
            :preview-title="'Course Import Preview'"
            :confirm-button-text="'Confirm & Start Import'"
            :template-route="route('course-management.import.template')"
            :back-route="route('course-management.courses.index')"
            :view-route="route('course-management.courses.index')"
            :notes="notes"
            :sample-rows="sampleRows"
            @validation-success="onValidationSuccess"
            @validation-error="onValidationError"
            @import-success="onImportSuccess"
            @import-error="onImportError"
    >
    </import-reusable>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ImportReusable from './import_reusable.vue';
// expose component for template when using <script setup>
const import_reusable = ImportReusable;
// import ImportExcel from './ImportExcel.vue';
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

// Notes and sample rows (passed to reusable component)
const notes = [
    'Course, Level, and Lesson columns are required',
    'Section column is optional (will use "General" if empty)',
    'Duplicate lessons will be skipped',
    "Courses, levels, and sections will be created if they don't exist",
    'Order will be assigned automatically'
];

const sampleRows = [
    ['Grade 5 Math','Fractions','Add unlike fractions','Lesson A'],
    ['Grade 5 Math','Fractions','Add unlike fractions','Lesson B'],
    ['English Literature','Poetry','','Introduction to Poetry']
];

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