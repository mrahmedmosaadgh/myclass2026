<template>
    <div>
        <input
            type="file"
            ref="fileInput"
            class="hidden"
            accept=".xlsx,.xls"
            @change="handleFileSelect"
        />

        <SecondaryButton
            @click="$refs.fileInput.click()"
            :disabled="isUploading"
            class="relative"
        >
            <span>{{ buttonText }}</span>
        </SecondaryButton>

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
                <div v-if="showPreview" class="overflow-x-auto mb-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th v-for="column in columns.filter(col => !col.hidden)"
                                    :key="column.key"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    {{ column.label }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(row, index) in previewData" :key="index">
                                <td v-for="column in columns.filter(col => !col.hidden)"
                                    :key="column.key"
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ row.data[column.key] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span :class="getStatusClass(row.status)">
                                        {{ row.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-2">
                    <SecondaryButton @click="closePreview">
                        {{ cancelButtonText }}
                    </SecondaryButton>
                    <PrimaryButton
                        @click="confirmImport"
                        :disabled="isUploading || !previewData.length"
                    >
                        {{ isUploading ? processingText : confirmButtonText }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Results Modal -->
        <Modal :show="showResults" @close="closeResults" maxWidth="2xl">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ resultsTitle }}</h3>

                <div v-if="results.success.length" class="mb-4">
                    <h4 class="text-green-600 font-medium mb-2">Success ({{ results.success.length }})</h4>
                    <ul class="text-sm text-gray-600">
                        <li v-for="(message, index) in results.success" :key="index">
                            {{ message }}
                        </li>
                    </ul>
                </div>

                <div v-if="results.errors.length" class="mb-4">
                    <h4 class="text-red-600 font-medium mb-2">Errors ({{ results.errors.length }})</h4>
                    <ul class="text-sm text-red-600">
                        <li v-for="(message, index) in results.errors" :key="index">
                            {{ message }}
                        </li>
                    </ul>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <SecondaryButton
                        v-if="canUndo"
                        @click="undoImport"
                        :disabled="isUndoing"
                    >
                        {{ isUndoing ? undoingText : undoButtonText }}
                    </SecondaryButton>
                    <PrimaryButton @click="closeResults">{{ closeButtonText }}</PrimaryButton>
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

const emit = defineEmits(['imported']);

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
const handleFileSelect = (event) => {
    const file = event.target.files[0];
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

const validatePreviewData = async () => {
    try {
        const required_fields = props.columns
            .filter(col => col.required)
            .map(col => col.key);

        const response = await axios.post(props.validateUrl, {
            data: previewData.value.map(row => row.data),
            required_fields: required_fields,
            columns: props.columns // Add this line to pass the full columns configuration
        });
console.log(response.data )
console.log(response.data.fileData)


        // Set status based on validation results
        previewData.value = previewData.value.map((row, index) => ({
            ...row,
            status: response.data.stats.invalid_rows > 0 ? 'warning' : 'valid'
        }));
        
        // Emit validation success with the response data
        emit('validation-success', response.data);
    } catch (error) {
        console.error('Validation error:', error);
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
    'text-yellow-600': status === 'new',
    'text-blue-600': status === 'update',
    'text-red-600': status === 'error'
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