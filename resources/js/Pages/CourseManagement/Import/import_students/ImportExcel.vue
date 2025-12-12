<template>
    <div>
        <input
            type="file"
            ref="fileInput"
            class="hidden"
            accept=".xlsx,.xls"
            @change="handleFileSelect"
        />

        <div 
            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition-colors duration-200"
            :class="{ 'border-blue-500 bg-blue-50': isDragging }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleFileDrop"
            @click="$refs.fileInput.click()"
        >
            <div class="flex flex-col items-center justify-center space-y-3 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <div class="text-lg font-medium text-gray-700">{{ buttonText }}</div>
                <p class="text-sm text-gray-500">Drag and drop your Excel file here, or click to browse</p>
                <div class="text-xs text-gray-400">Supported formats: .xlsx, .xls</div>
            </div>
        </div>

        <!-- Preview Modal -->
        <Modal :show="showPreview" @close="closePreview" maxWidth="4xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ previewTitle }}</h3>
                    <div class="text-sm text-gray-500">
                        Total Records: {{ previewData.length }}
                    </div>
                </div>

                <!-- Preview Table -->
                <div v-if="showPreview" class="overflow-x-auto mb-6 rounded-lg border border-gray-200 shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-50">
                            <tr>
                                <th v-for="column in columns.filter(col => !col.hidden)"
                                    :key="column.key"
                                    class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider"
                                >
                                    {{ column.label }}
                                    <span v-if="column.required" class="text-red-500 ml-1">*</span>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(row, index) in previewData" :key="index" class="hover:bg-gray-50 transition-colors duration-150">
                                <td v-for="column in columns.filter(col => !col.hidden)"
                                    :key="column.key"
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    <span v-if="row.data[column.key]">{{ row.data[column.key] }}</span>
                                    <span v-else class="text-gray-400 italic">Empty</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span 
                                        :class="{
                                            'px-2 py-1 rounded-full text-xs font-medium': true,
                                            'bg-green-100 text-green-800': row.status === 'valid',
                                            'bg-yellow-100 text-yellow-800': row.status === 'warning' || row.status === 'new',
                                            'bg-red-100 text-red-800': row.status === 'error'
                                        }"
                                    >
                                        {{ row.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <button 
                        @click="closePreview"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                        {{ cancelButtonText }}
                    </button>
                    <button
                        @click="confirmImport"
                        :disabled="isUploading || !previewData.length"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                    >
                        <svg v-if="isUploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isUploading ? processingText : confirmButtonText }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Results Modal -->
        <Modal :show="showResults" @close="closeResults" maxWidth="2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">{{ resultsTitle }}</h3>
                    <div class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        Import Complete
                    </div>
                </div>

                <div v-if="results.success.length" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <h4 class="text-green-800 font-medium">Success ({{ results.success.length }})</h4>
                    </div>
                    <div class="pl-7">
                        <ul class="text-sm text-green-700 space-y-1 max-h-40 overflow-y-auto">
                            <li v-for="(message, index) in results.success" :key="index" class="flex items-start">
                                <span class="mr-1">•</span>
                                <span>{{ message }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-if="results.errors.length" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <h4 class="text-red-800 font-medium">Errors ({{ results.errors.length }})</h4>
                    </div>
                    <div class="pl-7">
                        <ul class="text-sm text-red-700 space-y-1 max-h-40 overflow-y-auto">
                            <li v-for="(message, index) in results.errors" :key="index" class="flex items-start">
                                <span class="mr-1">•</span>
                                <span>{{ message }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        v-if="canUndo"
                        @click="undoImport"
                        :disabled="isUndoing"
                        class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                    >
                        <svg v-if="isUndoing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-red-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="-ml-1 mr-2 h-4 w-4 text-red-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ isUndoing ? undoingText : undoButtonText }}
                    </button>
                    <button 
                        @click="closeResults"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        {{ closeButtonText }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import NProgress from 'nprogress';
import axios from 'axios';
import * as XLSX from 'xlsx/xlsx.mjs'; // Changed to browser-compatible import

const props = defineProps({
    // URLs
    validateUrl: {
        type: String,
        required: true
    },
    importUrl: {
        type: String,
        required: true
    },
    undoUrl: {
        type: String,
        required: false,
        default: ''
    },

    // Column configuration
    columns: {
        type: Array,
        required: true,
        // Example: [{ key: 'name', label: 'Name' }, { key: 'email', label: 'Email' }]
    },

    // Text customization
    buttonText: {
        type: String,
        default: 'Choose Excel File'
    },
    previewTitle: {
        type: String,
        default: 'Preview Import Data'
    },
    confirmButtonText: {
        type: String,
        default: 'Confirm Import'
    },
    cancelButtonText: {
        type: String,
        default: 'Cancel'
    },
    processingText: {
        type: String,
        default: 'Processing...'
    },
    resultsTitle: {
        type: String,
        default: 'Import Results'
    },
    undoButtonText: {
        type: String,
        default: 'Undo Import'
    },
    undoingText: {
        type: String,
        default: 'Undoing...'
    },
    closeButtonText: {
        type: String,
        default: 'Close'
    }
});

const emit = defineEmits(['imported', 'validation-success', 'validation-error']);

// Refs
const fileInput = ref(null);
const isUploading = ref(false);
const isUndoing = ref(false);
const showPreview = ref(false);
const showResults = ref(false);
const previewData = ref([]);
const results = ref({ success: [], errors: [] });
const canUndo = ref(false);
const importId = ref(null);
const isDragging = ref(false);

// Watch for column changes and update preview data if needed
watch(() => props.columns, () => {
    if (previewData.value.length > 0) {
        // Reorganize existing preview data based on new column order
        previewData.value = previewData.value.map(row => {
            const newData = {};
            props.columns.forEach(column => {
                newData[column.key] = row.data[column.key];
            });
            return {
                ...row,
                data: newData
            };
        });
    }
}, { deep: true });

// Methods
const processFile = (file) => {
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

        // Remove header row and process data
        const headers = jsonData.shift();

        previewData.value = jsonData
            .filter(row => row.length)
            .map(row => {
                const rowData = {};
                props.columns.forEach((column, index) => {
                    rowData[column.key] = row[index];
                });
                return {
                    data: rowData,
                    status: 'new'
                };
            });

        validatePreviewData();
        showPreview.value = true;
    };
    reader.readAsArrayBuffer(file);
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    processFile(file);
};

const handleFileDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    processFile(file);
};

const validatePreviewData = async () => {
    try {
        const required_fields = props.columns
            .filter(col => col.required)
            .map(col => col.key);

        // Normalize data: ensure all columns exist and values are strings (trimmed)
        const normalizedRows = previewData.value.map(row => {
            const obj = {};
            props.columns.forEach(col => {
                let v = row.data[col.key];
                if (v === undefined || v === null) v = '';
                obj[col.key] = String(v).trim();
            });
            return obj;
        });

        // Client-side pre-validation: ensure required fields are not empty to avoid 422 from server
        const localErrors = [];
        const rowStatuses = normalizedRows.map(() => 'valid');
        normalizedRows.forEach((r, idx) => {
            required_fields.forEach((rf) => {
                if (!r[rf] || String(r[rf]).trim() === '') {
                    localErrors.push(`Row ${idx + 1}: Missing required field '${rf}'`);
                    rowStatuses[idx] = 'error';
                }
            });
        });

        // Update previewData statuses to reflect local validation
        previewData.value = previewData.value.map((row, idx) => ({ ...row, status: rowStatuses[idx] }));

        if (localErrors.length) {
            // Show local validation errors in the results modal and do not call server
            results.value = { success: [], errors: localErrors };
            showResults.value = true;
            console.warn('Local validation prevented server call:', localErrors);
            return;
        }

        const payload = {
            data: normalizedRows,
            required_fields: required_fields,
            columns: props.columns // Add this line to pass the full columns configuration
        };
        console.debug('validatePreviewData payload:', payload);
        const response = await axios.post(props.validateUrl, payload);
        // Set status based on validation results
        previewData.value = previewData.value.map((row, index) => ({
            ...row,
            status: response.data.stats.invalid_rows > 0 ? 'warning' : 'valid'
        }));
        
        // Emit validation success with the response data
        emit('validation-success', response.data);
    } catch (error) {
        console.error('Validation error:', error);
        // Extract Laravel validation messages (422) if present and show them to the user
        const messages = [];
        if (error.response && error.response.data) {
            const data = error.response.data;
            if (data.message) messages.push(data.message);
            if (data.errors) {
                // data.errors can be object of arrays
                if (Array.isArray(data.errors)) {
                    data.errors.forEach(m => messages.push(m));
                } else {
                    Object.values(data.errors).forEach(arr => {
                        if (Array.isArray(arr)) arr.forEach(m => messages.push(m));
                        else messages.push(String(arr));
                    });
                }
            }
        } else {
            messages.push('Validation failed.');
        }

        // Show messages in results modal
        results.value = { success: [], errors: messages };
        showResults.value = true;
    }
};

const confirmImport = async () => {
    isUploading.value = true;
    NProgress.start();

    try {
        const response = await axios.post(props.importUrl, {
            data: previewData.value.map(row => row.data),
            columns: props.columns // Add columns configuration
        });

        emit('imported');
        results.value = response.data.results;
        importId.value = response.data.importId;
        canUndo.value = !!props.undoUrl;
        showPreview.value = false;
        showResults.value = true;

    } catch (error) {
        console.error('Import error:', error);
        results.value = {
            success: [],
            errors: [error.response?.data?.message || 'An error occurred during import']
        };
        showResults.value = true;
    } finally {
        isUploading.value = false;
        NProgress.done();
        fileInput.value.value = '';
    }
};

const undoImport = async () => {
    if (!importId.value || !props.undoUrl) return;

    isUndoing.value = true;
    NProgress.start();

    try {
        await axios.post(`${props.undoUrl}/${importId.value}`);
        canUndo.value = false;
        emit('imported');
        closeResults();
    } catch (error) {
        alert('Failed to undo the import');
    } finally {
        isUndoing.value = false;
        NProgress.done();
    }
};

const getStatusClass = (status) => ({
    'px-2 py-1 rounded-full text-xs font-medium': true,
    'bg-yellow-100 text-yellow-800': status === 'new',
    'bg-blue-100 text-blue-800': status === 'update',
    'bg-green-100 text-green-800': status === 'valid',
    'bg-yellow-100 text-yellow-800': status === 'warning',
    'bg-red-100 text-red-800': status === 'error'
});

const closePreview = () => {
    showPreview.value = false;
    previewData.value = [];
    fileInput.value.value = '';
};

const closeResults = () => {
    showResults.value = false;
    results.value = { success: [], errors: [] };
    importId.value = null;
    canUndo.value = false;
};
</script>

<style scoped>
/* Add smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Improve scrollbars for lists */
.max-h-40 {
    max-height: 10rem;
}

.overflow-y-auto {
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

/* Animation for spinner */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>