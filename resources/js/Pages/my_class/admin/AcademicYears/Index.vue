<template>
    <AppLayout :title="pageTitle">
      
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Academic Year
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
                                button-text="Import Academic Years"
                                preview-title="Preview Data"
                            />
                            <SecondaryButton @click="exportData">
                                Export
                            </SecondaryButton>
                        </div>
                    </div>

                    <DataTableV2
                        :items="items"
                        :columns="tableColumns"
                        :actions="actions"
                        :searchable="true"
                        :per-page="10"
                        @sort="handleSort"
                        @search="handleSearch"
                        @action="handleAction"
                    />

                    <Pagination v-if="pagination" :links="pagination" />
                </div>
            </div>

            <FormModalV2
            :show="modalOpen"
            :title="modelName"
            :fields="formFields"
            :editing="editing"
            @close="closeModal"
            @submitted="handleSubmit"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { h } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModalV2 from '@/Components/Common/FormModalV2.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import axios from 'axios';
import { exportToExcel } from '@/Utils/exportHelper';
import DangerButton from '@/Components/DangerButton.vue';
import DataTableV2 from '@/Components/Common/DataTableV2.vue';

const props = defineProps({
    records: Object,
    options: Object,
});

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const pageTitle = 'Academic Years Management';
const modelName = 'Academic Year';
const baseUrl = '/admin/academic-year';

const tableColumns = [
    {
        key: 'id',
        label: 'ID',
        sortable: true
    },
    {
        key: 'name',
        label: 'Name',
        sortable: true
    },
    {
        key: 'school.name',
        label: 'School',
        sortable: true
    },
    {
        key: 'active',
        label: 'Status',
        type: 'boolean',
        sortable: true
    }
];

// First, declare all required functions
const createFullYearCalendar = async (academicYear) => {
    if (!confirm(`Are you sure you want to generate a full year calendar for ${academicYear.name}?`)) return;

    try {
        const response = await axios.post(`${baseUrl}/${academicYear.id}/generate-calendar`);
        alert(response.data.message || 'Calendar generated successfully');
        router.reload();
    } catch (error) {
        console.error('Calendar generation error:', error);
        alert(error.response?.data?.message || 'An error occurred while generating the calendar');
    }
};

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const deleteRecord = async (record) => {
    if (!confirm('Are you sure you want to delete this record?')) return;

    try {
        await axios.delete(`${baseUrl}/${record.id}`);
        refreshData();
    } catch (error) {
        console.error('Deletion error:', error);
        alert('An error occurred while deleting the record.');
    }
};

const importColumns = [
    { key: 'name', label: 'Name' },
    { key: 'school_id', label: 'School ID' },
    { key: 'active', label: 'Status' }
];

const exportData = () => {
    exportToExcel({
        items: items.value,
        columns: [
            { key: 'id', label: 'ID' },
            { key: 'name', label: 'Name' },
            { key: 'school.name', label: 'School' },
            { key: 'active', label: 'Status' }
        ],
        fileName: 'academic_years',
        sheetName: 'Academic Years'
    });
};

// Then define the actions array
const actions = [
    {
        type: 'generate-calendar',
        label: 'Generate Calendar',
        icon: 'calendar',
        action: createFullYearCalendar,
        class: 'text-white bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded text-sm mr-2'
    },
    {
        type: 'edit',
        label: 'Edit',
        icon: 'pencil',
        action: openModal,
        class: 'text-blue-600 hover:text-blue-800'
    },
    {
        type: 'delete',
        label: 'Delete',
        icon: 'trash',
        action: deleteRecord,
        class: 'text-red-600 hover:text-red-800'
    }
];

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true
    },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: computed(() => props.options?.schools?.map(school => ({
            value: school.id,
            label: school.name
        })) || [])
    },
    {
        name: 'start_date',
        label: 'start_date',
        type: 'date',
        // options: props.options?.statusOptions || []
    },
       {
        name: 'active',
        label: 'Status',
        type: 'select',
        options: props.options?.statusOptions || []
    },
    {
        name: '_actions',
        label: '',
        type: 'custom',
        component: {
            render: (props) => {
                if (!props.editing) return null;
                return h(DangerButton, {
                    type: 'button',
                    class: 'mt-4',
                    onClick: () => createFullYearCalendar(props.editing),
                }, () => 'Generate Full Year Calendar');
            }
        }
    }
];

const modalOpen = ref(false);
const editing = ref(null);

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const refreshData = () => {
    router.reload({ only: ['records'] });
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;

    try {
        await axios.post(url, {
            ...(id && { _method: 'PUT' }),
            ...form
        });
        onSuccess();
        closeModal();
        refreshData();
    } catch (error) {
        onError(error.response.data.errors);
    }
};

const handleSort = ({ key, order }) => {
    // Implement sorting logic here
    console.log('Sorting by', key, 'in', order, 'order');
};

const handleSearch = (query) => {
    // Implement search logic here
    console.log('Searching for', query);
};

const handleAction = ({ type, item }) => {
    switch (type) {
        case 'generate-calendar':
            createFullYearCalendar(item);
            break;
        case 'edit':
            openModal(item);
            break;
        case 'delete':
            deleteRecord(item);
            break;
        default:
            console.warn(`Unhandled action type: ${type}`);
    }
};
</script>



















