<template>
    <div>
        <Head v-if="title" :title="title" />
        <div class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="row items-center justify-between">
                        <div>
                            <div class="text-h6">{{ headerTitle }}</div>
                            <div class="text-subtitle2">{{ headerSubtitle }}</div>
                        </div>
                        <div class="q-gutter-sm">
                            <q-btn 
                                v-if="backRoute"
                                flat 
                                color="grey-7" 
                                icon="arrow_back" 
                                label="Back" 
                                @click="$inertia.visit(backRoute)"
                            />
                            <q-btn 
                                v-if="templateRoute"
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
                    <slot name="instructions">
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
                                <slot name="prepare-table">
                                    <q-markup-table flat bordered>
                                        <thead>
                                            <tr>
                                                <th class="text-left" v-for="(c, idx) in previewHeaderCols" :key="idx">Column {{ String.fromCharCode(65 + idx) }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td v-for="(c, idx) in previewHeaderCols" :key="idx"><strong>{{ c }}</strong></td>
                                            </tr>
                                            <tr class="text-grey-7" v-for="sample in sampleRows" :key="sample[0]">
                                                <td v-for="(cell, i) in sample" :key="i">{{ cell }}</td>
                                            </tr>
                                        </tbody>
                                    </q-markup-table>
                                </slot>

                                <q-banner class="bg-blue-1 text-blue-8 q-mt-md" rounded>
                                    <template v-slot:avatar>
                                        <q-icon name="info" />
                                    </template>
                                    <div class="text-subtitle2">Important Notes:</div>
                                    <ul class="q-ma-none q-pl-md">
                                        <li v-for="(note, i) in notes" :key="i">{{ note }}</li>
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

                                <slot name="uploader">
                                    <ImportExcel
                                        :validate-url="validateUrl"
                                        :import-url="importUrl"
                                        :undo-url="undoUrl"
                                        :columns="importColumns"
                                        :button-text="buttonText"
                                        :preview-title="previewTitle"
                                        :confirm-button-text="confirmButtonText"
                                        @validation-success="onValidationSuccess"
                                        @validation-error="onValidationError"
                                        @imported="onImported"
                                    />
                                </slot>
                            </q-step>
                        </q-stepper>
                    </slot>
                </q-card-section>

                <!-- File Preview & Confirmation -->
                <q-card-section v-if="validationResults && !importStarted">
                    <div class="text-h6 q-mb-md">File Preview & Confirmation</div>
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
                    <div class="q-mb-md">
                        <div class="text-subtitle2 q-mb-sm">
                            Overall Progress: {{ processedRows }} / {{ totalRows }} rows
                        </div>
                        <div class="q-mb-md">
                            <q-btn size="sm" color="primary" flat icon="pause" v-if="!importPaused && importStarted" @click="pauseImport">Pause</q-btn>
                            <q-btn size="sm" color="primary" flat icon="play_arrow" v-if="importPaused" @click="resumeImport">Resume</q-btn>
                            <q-btn size="sm" color="negative" flat icon="stop" v-if="importStarted && !importStopped" @click="stopImport">Stop</q-btn>
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
                            @click="$inertia.visit(viewRoute || route('course-management.courses.index'))"
                        />
                    </div>
                </q-card-section>
            </q-card>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import ImportExcel from './ImportExcel.vue';

// Props
const props = defineProps({
    // Optional page title
    title: { type: String, default: '' },
    headerTitle: { type: String, default: 'Import Course Management Data' },
    headerSubtitle: { type: String, default: 'Upload Excel file to import courses, levels, sections, and lessons' },
    backRoute: { type: String, default: '' },
    templateRoute: { type: String, default: '' },
    viewRoute: { type: String, default: '' },

    // Endpoints
    validateUrl: { type: String, required: true },
    importUrl: { type: String, required: true },
    undoUrl: { type: String, default: '' },

    // Columns
    importColumns: { type: Array, required: true },
    fullPreviewColumns: { type: Array, required: true },

    // UI text
    buttonText: { type: String, default: 'Choose Excel File' },
    previewTitle: { type: String, default: 'Course Import Preview' },
    confirmButtonText: { type: String, default: 'Confirm & Start Import' },

    // Notes and samples
    notes: { type: Array, default: () => ['Course, Level, and Lesson columns are required', 'Section column is optional (will use "General" if empty)', 'Duplicate lessons will be skipped', "Courses, levels, and sections will be created if they don't exist", 'Order will be assigned automatically'] },
    sampleRows: { type: Array, default: () => ([['Grade 5 Math','Fractions','Add unlike fractions','Lesson A'], ['Grade 5 Math','Fractions','Add unlike fractions','Lesson B'], ['English Literature','Poetry','','Introduction to Poetry']]) },

    // Other
    backLabel: { type: String, default: 'Back' }
});

// Emits
const emit = defineEmits(['validation-success', 'validation-error', 'import-success', 'import-error', 'imported']);

// State
const step = ref(1);
const importCompleted = ref(false);
const importStarted = ref(false);
const importPaused = ref(false);
const importStopped = ref(false);
const validationResults = ref(null);
const importResults = ref(null);
const fileData = ref([]);
const results = ref({ success: [], errors: [] });
const rowStatuses = ref([]);
const processedRows = ref(0);
const totalRows = ref(0);

// Computed
const overallProgress = computed(() => {
    if (totalRows.value === 0) return 0;
    return processedRows.value / totalRows.value;
});

// Methods
const downloadTemplate = () => {
    if (props.templateRoute) {
        window.open(props.templateRoute, '_blank');
    }
    step.value = 2;
};

const onValidationSuccess = (response) => {
    validationResults.value = response;
    
    // Store the file data for preview
    if (response.fileData) {
        fileData.value = response.fileData.map((row, index) => ({
            index: index + 2,
            ...row
        }));
    }
    
    step.value = 3;
    emit('validation-success', response);
};

const onValidationError = (error) => {
    validationResults.value = null;
    emit('validation-error', error);
};

const onImported = (payload) => {
    // ImportExcel emits 'imported' after a successful import; map to onImportSuccess
    onImportSuccess(payload);
};

const onImportSuccess = (response) => {
    importResults.value = response.stats || response;
    importCompleted.value = true;
    emit('import-success', response);
};

const onImportError = (error) => {
    emit('import-error', error);
};

const cancelImport = () => {
    validationResults.value = null;
    fileData.value = [];
    step.value = 3;
    emit('import-cancelled');
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
        status: 'pending'
    }));

    processRowsSequentially();
};

const pauseImport = () => {
    importPaused.value = true;
};

const resumeImport = () => {
    importPaused.value = false;
};

const stopImport = () => {
    importStopped.value = true;
    importPaused.value = false;
    importStarted.value = false;
};

const processRowsSequentially = async () => {
    for (let i = 0; i < fileData.value.length; i++) {
        if (importStopped.value) break;

        // Pause loop when requested
        while (importPaused.value && !importStopped.value) {
            await new Promise(resolve => setTimeout(resolve, 300));
        }

        rowStatuses.value[i].status = 'processing';
        try {
            // Prepare payload: remove helper fields like index
            const rowCopy = { ...fileData.value[i] };
            if (rowCopy.index) delete rowCopy.index;

            const response = await fetch(props.importUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ data: [rowCopy], columns: props.importColumns || props.fullPreviewColumns })
            });

            const result = await response.json().catch(() => ({}));

            if (response.ok) {
                rowStatuses.value[i].status = 'success';
            } else {
                rowStatuses.value[i].status = 'error';
                // Capture server-side validation messages (422) or error message
                if (result && (result.errors || result.message)) {
                    const msg = result.message || JSON.stringify(result.errors);
                    results.value.errors.push(`Row ${i + 2}: ${msg}`);
                }
            }
        } catch (error) {
            rowStatuses.value[i].status = 'error';
            results.value.errors.push(`Row ${i + 2}: ${error.message || String(error)}`);
        }

        processedRows.value = i + 1;
        await new Promise(resolve => setTimeout(resolve, 100));
    }

    try {
        const response = await fetch(props.importUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ data: fileData.value })
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

// Status helpers
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
