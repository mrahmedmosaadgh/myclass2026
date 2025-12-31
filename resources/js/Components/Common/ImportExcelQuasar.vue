<template>
    <div>
        <div class="row items-center q-gutter-md">
            <q-file
                v-model="file"
                class="col-grow"
                outlined
                dense
                :label="buttonText"
                accept=".xlsx, .xls"
                @update:model-value="handleFileSelect"
                :loading="processingFile"
            >
                <template v-slot:prepend>
                    <q-icon name="attach_file" />
                </template>
            </q-file>
        </div>

        <!-- Preview Dialog -->
        <q-dialog v-model="showPreview" full-width>
            <q-card class="column full-height" style="max-height: 90vh;">
                <q-card-section>
                    <div class="text-h6">{{ previewTitle }}</div>
                    <div class="text-subtitle2 text-grey">Total Records: {{ previewData.length }}</div>
                </q-card-section>

                <q-card-section class="col q-pa-none">
                    <q-table
                        flat
                        bordered
                        :rows="previewData"
                        :columns="tableColumns"
                        row-key="index"
                        virtual-scroll
                        :rows-per-page-options="[0]"
                        class="sticky-header-table"
                        style="height: 100%"
                    >
                        <template v-slot:body-cell-status="props">
                            <q-td :props="props">
                                <q-badge :color="getStatusColor(props.value)">
                                    {{ props.value }}
                                </q-badge>
                            </q-td>
                        </template>
                    </q-table>
                </q-card-section>

                <q-card-actions align="right" class="bg-white text-primary">
                    <q-btn flat :label="cancelButtonText" v-close-popup />
                    <q-btn
                        flat
                        :label="isUploading ? processingText : confirmButtonText"
                        color="primary"
                        @click="confirmImport"
                        :loading="isUploading"
                        :disable="isUploading || !previewData.length"
                    />
                </q-card-actions>
            </q-card>
        </q-dialog>

        <!-- Results Dialog -->
        <q-dialog v-model="showResults">
            <q-card style="min-width: 350px">
                <q-card-section>
                    <div class="text-h6">{{ resultsTitle }}</div>
                </q-card-section>

                <q-card-section class="q-pt-none">
                    <div v-if="results.success.length" class="q-mb-md">
                        <div class="text-positive text-weight-medium q-mb-sm">Success ({{ results.success.length }})</div>
                        <q-list dense>
                            <q-item v-for="(message, index) in results.success" :key="index">
                                <q-item-section>
                                    <q-item-label caption>{{ message }}</q-item-label>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </div>

                    <div v-if="results.errors.length">
                        <div class="text-negative text-weight-medium q-mb-sm">Errors ({{ results.errors.length }})</div>
                        <q-list dense>
                            <q-item v-for="(message, index) in results.errors" :key="index">
                                <q-item-section>
                                    <q-item-label caption class="text-negative">{{ message }}</q-item-label>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </div>
                </q-card-section>

                <q-card-actions align="right">
                    <q-btn
                        v-if="canUndo"
                        flat
                        :label="isUndoing ? undoingText : undoButtonText"
                        color="secondary"
                        @click="undoImport"
                        :loading="isUndoing"
                        :disable="isUndoing"
                    />
                    <q-btn flat :label="closeButtonText" color="primary" @click="closeResults" />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import * as XLSX from 'xlsx/xlsx.mjs';

const props = defineProps({
    // URLs
    validateUrl: { type: String, required: true },
    importUrl: { type: String, required: true },
    undoUrl: { type: String, required: false, default: '' },
    // Column configuration
    columns: { type: Array, required: true },
    // Text customization
    buttonText: { type: String, default: 'Choose Excel File' },
    previewTitle: { type: String, default: 'Preview Import Data' },
    confirmButtonText: { type: String, default: 'Confirm Import' },
    cancelButtonText: { type: String, default: 'Cancel' },
    processingText: { type: String, default: 'Processing...' },
    resultsTitle: { type: String, default: 'Import Results' },
    undoButtonText: { type: String, default: 'Undo Import' },
    undoingText: { type: String, default: 'Undoing...' },
    closeButtonText: { type: String, default: 'Close' },
    // Additional data to send with import
    additionalPayload: { type: Object, default: () => ({}) }
});

const emit = defineEmits(['imported', 'validation-success']);

// Refs
const file = ref(null);
const processingFile = ref(false);
const isUploading = ref(false);
const isUndoing = ref(false);
const showPreview = ref(false);
const showResults = ref(false);
const previewData = ref([]);
const results = ref({ success: [], errors: [] });
const canUndo = ref(false);
const importId = ref(null);

// Computed for QTable columns
const tableColumns = computed(() => {
    const cols = props.columns
        .filter(col => !col.hidden)
        .map(col => ({
            name: col.key,
            label: col.label,
            field: row => row.data[col.key],
            align: 'left',
            sortable: true
        }));
    
    // Add status column
    cols.push({
        name: 'status',
        label: 'Status',
        field: 'status',
        align: 'left',
        sortable: true
    });
    
    return cols;
});

// Watch for column changes and update preview data if needed
// This logic parallels the original component which handles column reordering/definition changes dynamically
watch(() => props.columns, () => {
    if (previewData.value.length > 0) {
        previewData.value = previewData.value.map(row => {
            const newData = {};
            props.columns.forEach(column => {
                newData[column.key] = row.data[column.key] || '';
            });
            return {
                ...row,
                data: newData
            };
        });
    }
}, { deep: true });

const handleFileSelect = async (val) => {
    if (!val) return;
    
    processingFile.value = true;
    
    try {
        const buffer = await val.arrayBuffer();
        const workbook = XLSX.read(buffer, { type: 'array' });
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

        // Remove header row
        const headers = jsonData.shift();
        
        // Normalize headers for case-insensitive matching
        const normalizedHeaders = headers.map(h => String(h).trim().toLowerCase());

        // Map data to columns
        previewData.value = jsonData
            .filter(row => row.length)
            .map((row, idx) => {
                const rowData = {};
                
                props.columns.forEach((column, index) => {
                    // Try to find the column by label or key in headers
                    // We assume the user's Excel headers match the 'Label' or 'Key' of our defined columns
                    const searchLabels = [column.label, column.key].map(l => String(l).trim().toLowerCase());
                    let colIndex = normalizedHeaders.findIndex(h => searchLabels.includes(h));
                    
                    // Fallback to index if header matching fails (assuming strict order)
                    // But if strict order assumption is wrong, this is where it breaks.
                    // Let's prefer header matching. If not found, use index but maybe warn?
                    // For now, fallback to index to keep existing behavior if headers are totally different.
                    if (colIndex === -1 && index < row.length) {
                         colIndex = index;
                    }
                    
                    if (colIndex !== -1) {
                         rowData[column.key] = row[colIndex];
                    } else {
                         rowData[column.key] = ''; // Missing value
                    }
                });
                
                return {
                    index: idx, // Unique key for q-table
                    data: rowData,
                    status: 'new'
                };
            });

        await validatePreviewData();
        showPreview.value = true;
    } catch (e) {
        console.error('Error reading file', e);
        // Could add a q-notify here if desired
    } finally {
        processingFile.value = false;
        // Reset file input so same file can be selected again if cancelled
        // But q-file handles v-model, so we might keep it if preview is just hidden
    }
};

const validatePreviewData = async () => {
    try {
        const required_fields = props.columns
            .filter(col => col.required)
            .map(col => col.key);

        const response = await axios.post(props.validateUrl, {
            data: previewData.value.map(row => row.data),
            required_fields: required_fields,
            columns: props.columns
        });

        // Set status based on validation results
        // Warning if there are invalid rows or general errors
        const hasErrors = response.data.summary?.invalid_rows > 0 || 
                         response.data.summary?.errors?.length > 0;
        
        // This is a simplification; ideally we map row-specific errors if returned
        // The original component sets all rows to warning/valid based on global summary unless row-level detail is parsed differently.
        // Actually, looking at original code:
        // previewData.value = previewData.value.map((row, index) => ({... status: hasErrors ? 'warning' : 'valid' }));
        // So it marks ALL rows as warning if there's ANY error? That seems to be the original logic.
        
        previewData.value = previewData.value.map(row => ({
            ...row,
            status: hasErrors ? 'warning' : 'valid'
        }));
        
        emit('validation-success', response.data);
    } catch (error) {
        console.error('Validation error:', error);
        previewData.value = previewData.value.map(row => ({
            ...row,
            status: 'error'
        }));
    }
};

const confirmImport = async () => {
    isUploading.value = true;
    
    try {
        const response = await axios.post(props.importUrl, {
            data: previewData.value.map(row => row.data),
            columns: props.columns,
            ...props.additionalPayload
        });

        emit('imported', response.data);
        results.value = response.data.results || { success: [], errors: [] };
        // Handle slightly different response structures if necessary
        // Original: results.value = response.data.results;
        
        importId.value = response.data.importId;
        canUndo.value = !!props.undoUrl;
        showPreview.value = false;
        showResults.value = true;
        
        // Clear file input on success
        file.value = null;

    } catch (error) {
        console.error('Import error:', error);
        results.value = {
            success: [],
            errors: [error.response?.data?.message || 'An error occurred during import']
        };
        showResults.value = true;
    } finally {
        isUploading.value = false;
    }
};

const undoImport = async () => {
    if (!importId.value || !props.undoUrl) return;

    isUndoing.value = true;
    
    try {
        await axios.post(`${props.undoUrl}/${importId.value}`);
        canUndo.value = false;
        emit('imported');
        closeResults();
    } catch (error) {
        // q-notify would be better
        alert('Failed to undo the import');
    } finally {
        isUndoing.value = false;
    }
};

const getStatusColor = (status) => {
    switch(status) {
        case 'new': return 'warning';
        case 'update': return 'info';
        case 'valid': return 'positive';
        case 'error': return 'negative';
        case 'warning': return 'warning';
        default: return 'grey';
    }
};

const closeResults = () => {
    showResults.value = false;
    results.value = { success: [], errors: [] };
    importId.value = null;
    canUndo.value = false;
};
</script>

<style scoped>
.sticky-header-table {
  /* height or max-height is important */
  max-height: 70vh;
}
</style>
