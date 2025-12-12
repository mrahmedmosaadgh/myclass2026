<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DataTable from './DataTable5.vue';
import Pagination from '@/Components/Pagination.vue';
import FormModal from './FormModal5.vue';
import NProgress from 'nprogress';

const props = defineProps({
    records: Object,
    options: {
        type: Object,
        default: () => ({
            schools: [],
            academicYears: [],
            semesters: [],
            // ... other options
        })
    },
});

// Add local reactive state for table data
const tableData = ref({
    data: props.records.data,
    links: props.records.links
});

// Reactive state
const showModal = ref(false);
const editing = ref(null);
const formErrors = ref({});
const form = ref({
    school_id: '',
    name: '',
    description: '',
    active: true,
    copy_date: null,
    academic_year_id: '',
    semester_id: '',
    week_number: null,
    status: 'draft',
    metadata: null,
    notes: ''
});

// Add these refs at the top with other refs
const isSubmitting = ref(false);
const isDeleting = ref(false);

// Table configuration
const tableColumns = [
    { key: 'name', label: 'Name' },
    { key: 'school.name', label: 'School' },
    { key: 'academic_year.name', label: 'Academic Year' },
    { key: 'semester.name', label: 'Semester' },
    { key: 'week_number', label: 'Week' },
    { key: 'status', label: 'Status',
      formatter: (value) => value.charAt(0).toUpperCase() + value.slice(1) },
    { key: 'active', label: 'Active',
      formatter: (value) => value ? 'Yes' : 'No' },
    { key: 'copy_date', label: 'Copy Date',
      formatter: (value) => value ? new Date(value).toLocaleDateString() : '-' }
];

const tableActions = [
    {
        type: 'edit',
        label: 'Edit',
        icon: 'pencil',
        class: 'text-blue-600 hover:text-blue-800'
    },
    {
        type: 'delete',
        label: 'Delete',
        icon: 'trash',
        class: 'text-red-600 hover:text-red-800'
    }
];

// Form fields configuration
const formFields = [
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: props.options.schools
    },
    {
        name: 'academic_year_id',
        label: 'Academic Year',
        type: 'select',
        required: true,
        options: props.options.academicYears
    },
    {
        name: 'semester_id',
        label: 'Semester',
        type: 'select',
        options: props.options.semesters
    },
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true
    },
    {
        name: 'description',
        label: 'Description',
        type: 'textarea'
    },
    {
        name: 'week_number',
        label: 'Week Number',
        type: 'number',
        min: 1,
        max: 52
    },
    {
        name: 'copy_date',
        label: 'Copy Date',
        type: 'date'
    },
    {
        name: 'status',
        label: 'Status',
        type: 'select',
        required: true,
        options: [
            { value: 'draft', label: 'Draft' },
            { value: 'active', label: 'Active' },
            { value: 'archived', label: 'Archived' }
        ]
    },
    {
        name: 'active',
        label: 'Active',
        type: 'checkbox'
    },
    {
        name: 'notes',
        label: 'Notes',
        type: 'textarea'
    }
];

const modalTitle = computed(() =>
    editing.value ? 'Edit Schedule Copy' : 'Create Schedule Copy'
);

// Methods
const openModal = (record = null) => {
    editing.value = record;
    form.value = record
        ? { ...record }
        : {
            school_id: '',
            name: '',
            description: '',
            active: true,
            copy_date: null,
            academic_year_id: '',
            semester_id: '',
            week_number: null,
            status: 'draft',
            metadata: null,
            notes: ''
        };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editing.value = null;
    formErrors.value = {};
};

const handleSubmit = (event) => {
    isSubmitting.value = true;
    NProgress.start();

    if (editing.value) {
        // Change PUT to POST and add _method: 'PUT'
        axios.post(`/admin/schedule-copies/${editing.value.id}`, {
            ...event.form,
            _method: 'PUT'
        })
            .then((response) => {
                const index = tableData.value.data.findIndex(item => item.id === editing.value.id);
                if (index !== -1) {
                    tableData.value.data[index] = response.data.record;
                }
                closeModal();
            })
            .catch(error => {
                formErrors.value = error.response?.data?.errors || {};
            })
            .finally(() => {
                isSubmitting.value = false;
                NProgress.done();
            });
    } else {
        axios.post('/admin/schedule-copies', event.form)
            .then((response) => {
                tableData.value.data.unshift(response.data.record);
                closeModal();
            })
            .catch(error => {
                formErrors.value = error.response?.data?.errors || {};
            })
            .finally(() => {
                isSubmitting.value = false;
                NProgress.done();
            });
    }
};

const handleDelete = (record) => {
    if (confirm('Are you sure you want to delete this schedule copy?')) {
        isDeleting.value = true;
        NProgress.start();

        axios.post(`/admin/schedule-copies/${record.id}`, {
            _method: 'DELETE'
        })
            .then(() => {
                tableData.value.data = tableData.value.data.filter(item => item.id !== record.id);
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('Failed to delete schedule copy');
            })
            .finally(() => {
                isDeleting.value = false;
                NProgress.done();
            });
    }
};

const handleTableAction = ({ type, item }) => {
    switch (type) {
        case 'edit':
            openModal(item);
            break;
        case 'delete':
            handleDelete(item);
            break;
        default:
            console.warn(`Unhandled action type: ${type}`);
    }
};

// Add refreshData function to reload the data
const refreshData = () => {
    NProgress.start();

    axios.get('/admin/schedule-copies')
        .then(response => {
            if (response.data.records) {
                tableData.value = {
                    data: response.data.records.data,
                    links: response.data.records.links
                };
            }
        })
        .catch(error => {
            console.error('Error refreshing data:', error);
            alert('Failed to refresh data');
        })
        .finally(() => {
            NProgress.done();
        });
};
</script>

<template> 
    <AppLayout title="Schedule Copies">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Schedule Copies</h2>
                        <PrimaryButton @click="openModal()">
                            Create New Copy
                        </PrimaryButton>
                    </div>

                    <DataTable
                        :items="tableData.data"
                        :columns="tableColumns"
                        :actions="tableActions"
                        :loading="isDeleting"
                        @action="handleTableAction"
                    />

                    <div v-if="tableData.links" class="mt-4">
                        <Pagination :links="tableData.links" />
                    </div>

                    <FormModal
                        :show="showModal"
                        :title="modalTitle"
                        :fields="formFields"
                        :form="form"
                        :errors="formErrors"
                        :loading="isSubmitting"
                        :options="props.options"
                        @close="closeModal"
                        @submit="handleSubmit"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>


















