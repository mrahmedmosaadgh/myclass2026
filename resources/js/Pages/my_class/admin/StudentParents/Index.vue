<template>
    <AppLayout :title="pageTitle">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ pageTitle }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div v-if="$page.props.flash?.success"
                         class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ $page.props.flash.success }}
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Parent
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :undo-url="baseUrl + '/undo-import'"
                            />
                            <SecondaryButton @click="exportData">
                                Export
                            </SecondaryButton>
                        </div>
                    </div>

                    <DataTable
                        :items="items"
                        :columns="tableColumns"
                        @edit="openModal"
                        @delete="deleteRecord"
                    />

                    <div class="mt-4" v-if="pagination">
                        <Pagination :links="pagination" />
                    </div>
                </div>
            </div>
        </div>

        <FormModal
            :show="modalOpen"
            :title="modelName"
            :fields="formFields"
            :editing="editing"
            @close="closeModal"
            @submitted="handleSubmit"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import axios from 'axios';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';
import * as XLSX from 'xlsx';

const props = defineProps({
    records: {
        type: Object,
        required: true
    },
    schools: {
        type: Array,
        required: true
    }
});

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const pageTitle = 'Parents Management';
const modelName = 'Parent';
const baseUrl = '/admin/student-parent';

const tableColumns = [
    { key: 't_id', label: 'ID' },
    { key: 'name', label: 'Name' },
    { key: 'name_ar', label: 'Arabic Name' },
    { key: 'school.name', label: 'School' },
    {
        key: 'report',
        label: 'Enable Reports',
        type: 'boolean',
        formatter: (value) => value ? 'Enabled' : 'Disabled',
        class: (value) => value ? 'text-green-600' : 'text-red-600'
    }
];

const schoolOptions = computed(() =>
    props.schools.map(school => ({
        value: school.id,
        label: school.name
    }))
);

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true
    },
    {
        name: 'name_ar',
        label: 'Arabic Name',
        type: 'text'
    },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: schoolOptions
    },
    {
        name: 'report',
        label: 'Enable Reports',
        type: 'select',
        required: true,
        options: [
            { value: 1, label: 'Enabled' },
            { value: 0, label: 'Disabled' }
        ],
        default: 1 // Set default value to Enabled
    }
];

const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);

const openModal = (record = null) => {
    editing.value = record;
    if (record) {
        // For editing, ensure report value is explicitly set
        record.report = record.report ? 1 : 0;
    }
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const handleSubmit = ({ form, onSuccess, onError }) => {
    if (submitting.value) return;

    submitting.value = true;
    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;

    // Ensure report is properly formatted before submission
    const formData = {
        ...form,
        report: form.report ? 1 : 0 // Convert to integer
    };

    axios.post(url, {
        ...(id && { _method: 'PUT' }),
        ...formData
    })
        .then(response => {
            onSuccess();
            closeModal();
            refreshData();
        })
        .catch(error => {
            if (error.response?.data?.errors) {
                onError(error.response.data.errors);
            } else {
                onError({ error: ['An unexpected error occurred'] });
            }
        })
        .finally(() => {
            submitting.value = false;
        });
};

const deleteRecord = (record) => {
    if (!confirm('Are you sure you want to delete this record?')) return;

    axios.delete(`${baseUrl}/${record.id}`)
        .then(() => {
            refreshData();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the record.');
        });
};

const refreshData = () => {
    router.reload({
        only: ['records'],
        preserveScroll: true,
        preserveState: true
    });
};

const exportData = () => {
    try {
        // Create worksheet data
        const wsData = [
            // Headers
            ['ID', 'Name', 'Arabic Name', 'School', 'Enable Reports'],
            // Data rows
            ...items.value.map(item => [
                item.t_id || '',
                item.name,
                item.name_ar,
                item.school?.name || '',
                item.report ? 'Enabled' : 'Disabled'
            ])
        ];

        // Create worksheet
        const ws = XLSX.utils.aoa_to_sheet(wsData);

        // Create workbook
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Parents');

        // Generate file and trigger download
        XLSX.writeFile(wb, 'parents.xlsx');
    } catch (error) {
        console.error('Export failed:', error);
        alert('Failed to export data');
    }
};
</script>






