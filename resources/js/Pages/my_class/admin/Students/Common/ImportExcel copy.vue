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

        <Modal :show="showPreview" @close="closePreview" maxWidth="4xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ previewTitle }}</h3>
                    <div class="text-sm text-gray-500">
                        Total Records: {{ previewData.length }}
                    </div>
                </div>

                <div v-if="showPreview" class="overflow-x-auto mb-4">
                    <slot name="preview" :data="previewData">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th 
                                        v-for="(column, index) in columns"
                                        :key="index"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ column.label }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(row, index) in previewData" :key="index">
                                    <td 
                                        v-for="(column, colIndex) in columns"
                                        :key="colIndex"
                                        class="px-6 py-4 whitespace-nowrap text-sm"
                                        :class="row.errors?.[column.key] ? 'text-red-600' : 'text-gray-900'"
                                    >
                                        {{ row.data[column.key] }}
                                        <p v-if="row.errors?.[column.key]" class="text-xs text-red-500">
                                            {{ row.errors[column.key] }}
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </slot>
                </div>

                <div class="flex justify-end">
                    <SecondaryButton @click="closePreview" class="mr-2">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton 
                        @click="emitData" 
                        :disabled="hasErrors"
                    >
                        Confirm Import
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';
import * as XLSX from 'xlsx';

const props = defineProps({
    buttonText: {
        type: String,
        default: 'Import Excel'
    },
    previewTitle: {
        type: String,
        default: 'Import Preview'
    },
    columns: {
        type: Array,
        default: () => []
    },
    validator: {
        type: Function,
        default: null
    }
});

const emit = defineEmits(['data']);

const fileInput = ref(null);
const isUploading = ref(false);
const showPreview = ref(false);
const previewData = ref([]);

const hasErrors = computed(() => {
    return previewData.value.some(row => Object.keys(row.errors || {}).length > 0);
});

const handleFileSelect = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    isUploading.value = true;
    
    try {
        const data = await readExcel(file);
        previewData.value = props.validator ? data.map(row => props.validator(row)) : data;
        showPreview.value = true;
    } catch (error) {
        console.error('Error reading file:', error);
    } finally {
        isUploading.value = false;
        event.target.value = ''; // Reset file input
    }
};

const readExcel = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet);
                
                resolve(jsonData.map(row => ({
                    data: row,
                    errors: {}
                })));
            } catch (error) {
                reject(error);
            }
        };
        
        reader.onerror = reject;
        reader.readAsArrayBuffer(file);
    });
};

const emitData = () => {
    emit('data', previewData.value);
    closePreview();
};

const closePreview = () => {
    showPreview.value = false;
    previewData.value = [];
};
</script>
