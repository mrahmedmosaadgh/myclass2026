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
                    <div class="mb-6 flex justify-between">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Semester Test
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
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
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { exportToExcel } from '@/Utils/exportHelper';

const props = defineProps({
    records: Object,
    options: Object
});

const pageTitle = 'Semester Tests Management';
const modelName = 'Semester Test';
const baseUrl = '/admin/semester-test';

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const tableColumns = [
    { key: 'name', label: 'Name' },
    { key: 'semester_number', label: 'Semester Number' },
    { key: 'start_date', label: 'Start Date' },
    { key: 'end_date', label: 'End Date' },
    { key: 'school.name', label: 'School' },
    { key: 'academicYear.name', label: 'Academic Year' },
    { key: 'active', label: 'Status', type: 'status' }
];

const formFields = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'semester_number', label: 'Semester Number', type: 'number', required: true },
    { name: 'start_date', label: 'Start Date', type: 'date' },
    { name: 'end_date', label: 'End Date', type: 'date' },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: props.options?.schools?.map(school => ({
            value: school.id,
            label: school.name
        })) || []
    },
    {
        name: 'academic_year_id',
        label: 'Academic Year',
        type: 'select',
        required: true,
        options: props.options?.academicYears?.map(year => ({
            value: year.id,
            label: year.name
        })) || []
    },
    {
        name: 'active',
        label: 'Status',
        type: 'select',
        options: [
            { value: true, label: 'Active' },
            { value: false, label: 'Inactive' }
        ]
    }
];

const modalOpen = ref(false);
const editing = ref(null);

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const refreshData = () => {
    router.reload({ only: ['records'] });
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    const url = editing.value ? `${baseUrl}/${editing.value.id}` : baseUrl;

    try {
        await axios.post(url, {
            ...(editing.value && { _method: 'PUT' }),
            ...form
        });
        onSuccess();
        closeModal();
        refreshData();
    } catch (error) {
        onError(error);
    }
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

const exportData = () => {
    exportToExcel({
        items: items.value,
        columns: tableColumns,
        fileName: 'semester_tests.xlsx'
    });
};

const importColumns = [
    { key: 'name', label: 'Name' },
    { key: 'semester_number', label: 'Semester Number' },
    { key: 'start_date', label: 'Start Date' },
    { key: 'end_date', label: 'End Date' },
    { key: 'school_id', label: 'School ID' },
    { key: 'academic_year_id', label: 'Academic Year ID' },
    { key: 'active', label: 'Status' }
];
</script>
